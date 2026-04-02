<?php
// src/controllers/TitheController.php

class TitheController {
    private function tableHasColumn(PDO $db, $table, $column) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        } else {
            $stmt = $db->query("PRAGMA table_info($table)");
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $col) {
                $name = isset($col['Field']) ? $col['Field'] : (isset($col['name']) ? $col['name'] : null);
                if ($name && strtolower($name) === strtolower($column)) {
                    return true;
                }
            }
            return false;
        }
    }
    private function getDefaultAccountSetId(PDO $db) {
        $hasCol = $this->tableHasColumn($db, 'account_sets', 'congregation_id');
        if (!$hasCol) {
            $stmt = $db->query("SELECT id FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['id'] : null;
        }
        $cid = $_SESSION['user_congregation_id'] ?? null;
        if ($cid) {
            $stmt = $db->prepare("SELECT id FROM account_sets WHERE active = 1 AND congregation_id = ? AND is_default = 1 LIMIT 1");
            $stmt->execute([$cid]);
            $id = (int)$stmt->fetchColumn();
            if ($id) return $id;
        }
        $stmt = $db->query("SELECT id FROM account_sets WHERE active = 1 AND congregation_id IS NULL AND is_default = 1 LIMIT 1");
        $id = (int)$stmt->fetchColumn();
        if ($id) return $id;
        $stmt = $db->query("SELECT id FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }
    private function hasAccountableField(PDO $db) {
        return $this->tableHasColumn($db, 'tithes', 'is_accountable');
    }
    private function isAccountableRequest() {
        return isset($_POST['is_accountable']) ? 1 : 0;
    }
    private function getFinancialClosureForDate(PDO $db, $congregationId, $date) {
        if (empty($date)) {
            return null;
        }

        if ($congregationId === null || $congregationId === '') {
            $stmt = $db->prepare("SELECT id, type, period FROM financial_closures WHERE congregation_id IS NULL AND status = 'Fechado' AND ? BETWEEN start_date AND end_date ORDER BY end_date DESC LIMIT 1");
            $stmt->execute([$date]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $stmt = $db->prepare("SELECT id, type, period FROM financial_closures WHERE congregation_id = ? AND status = 'Fechado' AND ? BETWEEN start_date AND end_date ORDER BY end_date DESC LIMIT 1");
        $stmt->execute([$congregationId, $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    private function blockClosedFinancialPeriod(PDO $db, $congregationId, $date, $redirectPath) {
        $closure = $this->getFinancialClosureForDate($db, $congregationId, $date);
        if (!$closure) {
            return false;
        }

        $_SESSION['flash_error'] = "Este lançamento pertence a um período financeiro já fechado ({$closure['type']} {$closure['period']}). Para alterar ou excluir, remova primeiro o fechamento correspondente.";
        redirect($redirectPath);
        return true;
    }
    public function index() {
        requirePermission('financial.view');
        $db = (new Database())->connect();
        
        $sql = "SELECT t.*, m.name as member_name, c.name as congregation_name, t.giver_name 
                FROM tithes t 
                LEFT JOIN members m ON t.member_id = m.id 
                LEFT JOIN congregations c ON t.congregation_id = c.id
                WHERE 1=1";
        $params = [];

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Base Query for Count
        $countSql = "SELECT COUNT(*) 
                     FROM tithes t 
                     LEFT JOIN members m ON t.member_id = m.id 
                     WHERE 1=1";
        $countParams = [];

        // Filters (Apply to both Count and Select)
        $whereClause = "";
        if (!empty($_SESSION['user_congregation_id'])) {
            $whereClause .= " AND t.congregation_id = ?";
            $countParams[] = $_SESSION['user_congregation_id'];
        } elseif (!empty($_GET['congregation_id'])) {
            $whereClause .= " AND t.congregation_id = ?";
            $countParams[] = $_GET['congregation_id'];
        }
        if (!empty($_GET['member_name'])) {
            $whereClause .= " AND (m.name LIKE ? OR t.giver_name LIKE ?)";
            $nameParam = '%' . $_GET['member_name'] . '%';
            $countParams[] = $nameParam;
            $countParams[] = $nameParam;
        }
        
        // Date Filter Defaults (Removed default filtering to show all history)
        if (!empty($_GET['start_date'])) {
            $whereClause .= " AND t.payment_date >= ?";
            $countParams[] = $_GET['start_date'];
        }
        if (!empty($_GET['end_date'])) {
            $whereClause .= " AND t.payment_date <= ?";
            $countParams[] = $_GET['end_date'];
        }
        
        if (!empty($_GET['type'])) {
            $whereClause .= " AND t.type = ?";
            $countParams[] = $_GET['type'];
        }

        // Main Query (No LIMIT/OFFSET to allow client-side pagination per tab)
        $sql = "SELECT t.*, m.name as member_name, c.name as congregation_name, t.giver_name 
                FROM tithes t 
                LEFT JOIN members m ON t.member_id = m.id 
                LEFT JOIN congregations c ON t.congregation_id = c.id
                WHERE 1=1" . $whereClause;
        
        $sql .= " ORDER BY t.payment_date DESC, t.created_at DESC";
        
        $params = $countParams; // Reuse params
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $tithes = $stmt->fetchAll();

        // Get congregations for filter dropdown
        $sql_cong = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql_cong .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql_cong .= " ORDER BY name ASC";
        $stmtCong = $db->query($sql_cong);
        $congregations = $stmtCong->fetchAll();

        view('admin/tithes/index', [
            'tithes' => $tithes, 
            'congregations' => $congregations,
            // 'currentPage' => $page, // Removed server-side pagination vars
            // 'totalPages' => $totalPages,
            // 'totalRecords' => $totalRecords
        ]);
    }

    public function create() {
        requirePermission('financial.manage');
        $db = (new Database())->connect();
        
        $sql = "SELECT * FROM members";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE congregation_id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        
        $membersDB = $db->query($sql)->fetchAll();
        
        // Fetch Historical Visitors
        $visitorsSql = "SELECT DISTINCT giver_name as name FROM tithes WHERE member_id IS NULL AND giver_name IS NOT NULL AND giver_name != ''";
        if (!empty($_SESSION['user_congregation_id'])) {
            $visitorsSql .= " AND congregation_id = " . $_SESSION['user_congregation_id'];
        }
        $visitorsSql .= " ORDER BY giver_name ASC";
        $visitorsDB = $db->query($visitorsSql)->fetchAll();
        
        // Merge Lists
        $members = [];
        foreach ($membersDB as $m) {
            $members[] = ['id' => $m['id'], 'name' => $m['name'], 'type' => 'member'];
        }
        foreach ($visitorsDB as $v) {
            $exists = false;
            foreach ($members as $m) {
                if (strcasecmp($m['name'], $v['name']) === 0) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $members[] = ['id' => null, 'name' => $v['name'], 'type' => 'visitor'];
            }
        }
        usort($members, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        // Get Congregations for Admin Selection
        $congregations = [];
        // Se usuário for admin (sem user_congregation_id) ou tiver permissão especial, buscar todas
        // Se tiver user_congregation_id, busca só a dele (para validar/exibir readonly)
        $sqlCong = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sqlCong .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sqlCong .= " ORDER BY name ASC";
        $congregations = $db->query($sqlCong)->fetchAll();
        
        // Get Banks and Chart of Accounts
        $bankAccounts = $db->query("SELECT * FROM bank_accounts WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $hasSetCol = $this->tableHasColumn($db, 'chart_of_accounts', 'account_set_id');
        if ($hasSetCol) {
            $setId = $this->getDefaultAccountSetId($db);
            $stmtCA = $db->prepare("SELECT * FROM chart_of_accounts WHERE account_set_id = ? AND type = 'income' AND status = 'active' ORDER BY code ASC");
            $stmtCA->execute([$setId]);
            $chartAccounts = $stmtCA->fetchAll();
        } else {
            $chartAccounts = $db->query("SELECT * FROM chart_of_accounts WHERE type = 'income' AND status = 'active' ORDER BY code ASC")->fetchAll();
        }
        
        view('admin/tithes/create', [
            'members' => $members, 
            'congregations' => $congregations,
            'bankAccounts' => $bankAccounts,
            'chartAccounts' => $chartAccounts,
            'hasAccountableField' => $this->hasAccountableField($db)
        ]);
    }

    public function store() {
        requirePermission('financial.manage');
        $member_id = !empty($_POST['member_id']) ? $_POST['member_id'] : null;
        $giver_name = $_POST['giver_name'] ?? null;
        
        $amount = $_POST['amount'];
        $payment_date = $_POST['payment_date'];
        $payment_method = $_POST['payment_method'];
        $type = $_POST['type'] ?? 'Dízimo';
        $notes = $_POST['notes'];
        $congregation_id = !empty($_POST['congregation_id']) ? $_POST['congregation_id'] : null;
        $bank_account_id = !empty($_POST['bank_account_id']) ? $_POST['bank_account_id'] : null;
        $chart_account_id = !empty($_POST['chart_account_id']) ? $_POST['chart_account_id'] : null;
        $db = (new Database())->connect();
        $is_accountable = $this->hasAccountableField($db) ? $this->isAccountableRequest() : 1;
        if (!$is_accountable) {
            $bank_account_id = null;
            $chart_account_id = null;
        }
        
        // Validação de Segurança para Congregação
        // Se usuário tem congregação fixa na sessão, força ela
        if (!empty($_SESSION['user_congregation_id'])) {
            $congregation_id = $_SESSION['user_congregation_id'];
        }
        
        // Se ainda for nulo (Admin lançando sem escolher), tenta pegar do membro
        if (empty($congregation_id) && $member_id) {
            // Fetch congregation_id from member
            $stmtMember = $db->prepare("SELECT congregation_id FROM members WHERE id = ?");
            $stmtMember->execute([$member_id]);
            $congregation_id = $stmtMember->fetchColumn();
        }
        
        // Se ainda nulo, erro ou assume alguma padrão (evitar erro de FK)
        // Mas como congregation_id pode ser NULL no banco (dependendo da estrutura), ok.
        // Geralmente Admin Global = NULL congregation_id em algumas lógicas, mas financeiro precisa de dono.
        if ($is_accountable && $this->blockClosedFinancialPeriod($db, $congregation_id, $payment_date, '/admin/tithes')) {
            return;
        }
        
        if ($this->hasAccountableField($db)) {
            $stmt = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, congregation_id, giver_name, bank_account_id, chart_account_id, is_accountable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$member_id, $amount, $payment_date, $payment_method, $type, $notes, $congregation_id, $giver_name, $bank_account_id, $chart_account_id, $is_accountable]);
        } else {
            $stmt = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, congregation_id, giver_name, bank_account_id, chart_account_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$member_id, $amount, $payment_date, $payment_method, $type, $notes, $congregation_id, $giver_name, $bank_account_id, $chart_account_id]);
        }

        if ($is_accountable && $bank_account_id) {
            $stmtUpdateBank = $db->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE id = ?");
            $stmtUpdateBank->execute([$amount, $bank_account_id]);
        }

        redirect('/admin/tithes');
    }

    public function receipt($id) {
        // Auth Check: Allow Admin OR Member
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['member_id'])) {
             redirect('/admin/login');
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT t.*, m.name as member_name, m.phone, c.name as congregation_name 
                              FROM tithes t 
                              LEFT JOIN members m ON t.member_id = m.id 
                              LEFT JOIN congregations c ON t.congregation_id = c.id
                              WHERE t.id = ?");
        $stmt->execute([$id]);
        $tithe = $stmt->fetch();

        if (!$tithe) {
            if (isset($_SESSION['member_id'])) {
                redirect('/portal/financial');
            }
            redirect('/admin/tithes');
        }

        // Access Control for Members: Can only view their own receipts
        if (isset($_SESSION['member_id']) && !isset($_SESSION['user_id'])) {
            if ($tithe['member_id'] != $_SESSION['member_id']) {
                // Member trying to access another member's receipt
                redirect('/portal/financial');
            }
        }

        view('admin/tithes/receipt', ['tithe' => $tithe]);
    }

    public function edit($id) {
        requirePermission('financial.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT t.*, m.name as member_name FROM tithes t LEFT JOIN members m ON t.member_id = m.id WHERE t.id = ?");
        $stmt->execute([$id]);
        $tithe = $stmt->fetch();

        if (!$tithe) {
            redirect('/admin/tithes');
        }

        if ((!isset($tithe['is_accountable']) || (int)$tithe['is_accountable'] === 1) && $this->blockClosedFinancialPeriod($db, $tithe['congregation_id'] ?? null, $tithe['payment_date'] ?? null, '/admin/tithes')) {
            return;
        }

        // Fetch Members and Visitors (Combined List for Autocomplete)
        $sql = "SELECT * FROM members";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE congregation_id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        $membersDB = $db->query($sql)->fetchAll();
        
        $visitorsSql = "SELECT DISTINCT giver_name as name FROM tithes WHERE member_id IS NULL AND giver_name IS NOT NULL AND giver_name != ''";
        if (!empty($_SESSION['user_congregation_id'])) {
            $visitorsSql .= " AND congregation_id = " . $_SESSION['user_congregation_id'];
        }
        $visitorsSql .= " ORDER BY giver_name ASC";
        $visitorsDB = $db->query($visitorsSql)->fetchAll();

        // Merge Lists
        $members = [];
        foreach ($membersDB as $m) {
            $members[] = ['id' => $m['id'], 'name' => $m['name'], 'type' => 'member'];
        }
        foreach ($visitorsDB as $v) {
            $exists = false;
            foreach ($members as $m) {
                if (strcasecmp($m['name'], $v['name']) === 0) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $members[] = ['id' => null, 'name' => $v['name'], 'type' => 'visitor'];
            }
        }
        usort($members, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        // Get Banks and Chart of Accounts
        $bankAccounts = $db->query("SELECT * FROM bank_accounts WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $hasSetCol = $this->tableHasColumn($db, 'chart_of_accounts', 'account_set_id');
        if ($hasSetCol) {
            $setId = $this->getDefaultAccountSetId($db);
            $stmtCA = $db->prepare("SELECT * FROM chart_of_accounts WHERE account_set_id = ? AND type = 'income' AND status = 'active' ORDER BY code ASC");
            $stmtCA->execute([$setId]);
            $chartAccounts = $stmtCA->fetchAll();
        } else {
            $chartAccounts = $db->query("SELECT * FROM chart_of_accounts WHERE type = 'income' AND status = 'active' ORDER BY code ASC")->fetchAll();
        }

        view('admin/tithes/edit', [
            'tithe' => $tithe, 
            'members' => $members,
            'bankAccounts' => $bankAccounts,
            'chartAccounts' => $chartAccounts,
            'hasAccountableField' => $this->hasAccountableField($db)
        ]);
    }

    public function update($id) {
        requirePermission('financial.manage');
        $member_id = !empty($_POST['member_id']) ? $_POST['member_id'] : null;
        $giver_name = $_POST['giver_name'] ?? null;
        $amount = $_POST['amount'];
        $payment_date = $_POST['payment_date'];
        $payment_method = $_POST['payment_method'];
        $type = $_POST['type'] ?? 'Dízimo';
        $notes = $_POST['notes'];
        $bank_account_id = !empty($_POST['bank_account_id']) ? $_POST['bank_account_id'] : null;
        $chart_account_id = !empty($_POST['chart_account_id']) ? $_POST['chart_account_id'] : null;

        $db = (new Database())->connect();
        $hasAccountableField = $this->hasAccountableField($db);
        $is_accountable = $hasAccountableField ? $this->isAccountableRequest() : 1;
        if (!$is_accountable) {
            $bank_account_id = null;
            $chart_account_id = null;
        }
        
        // Get old record to handle bank balance update
        $stmtOld = $db->prepare("SELECT amount, bank_account_id, payment_date, congregation_id" . ($hasAccountableField ? ", is_accountable" : "") . " FROM tithes WHERE id = ?");
        $stmtOld->execute([$id]);
        $oldRecord = $stmtOld->fetch();
        
        $congregation_id = null;
        if ($member_id) {
            $stmtMember = $db->prepare("SELECT congregation_id FROM members WHERE id = ?");
            $stmtMember->execute([$member_id]);
            $congregation_id = $stmtMember->fetchColumn();
        } else {
             // Keep existing congregation_id if editing, or default to user's
             $stmtCurrent = $db->prepare("SELECT congregation_id FROM tithes WHERE id = ?");
             $stmtCurrent->execute([$id]);
             $congregation_id = $stmtCurrent->fetchColumn();
             if (!$congregation_id) {
                 $congregation_id = $_SESSION['user_congregation_id'] ?? null;
             }
        }

        $oldIsAccountable = $hasAccountableField ? (int)($oldRecord['is_accountable'] ?? 1) : 1;
        if ($oldIsAccountable && $this->blockClosedFinancialPeriod($db, $oldRecord['congregation_id'] ?? null, $oldRecord['payment_date'] ?? null, '/admin/tithes')) {
            return;
        }
        if ($is_accountable && $this->blockClosedFinancialPeriod($db, $congregation_id, $payment_date, '/admin/tithes')) {
            return;
        }
        
        if ($hasAccountableField) {
            $stmt = $db->prepare("UPDATE tithes SET member_id = ?, amount = ?, payment_date = ?, payment_method = ?, type = ?, notes = ?, congregation_id = ?, giver_name = ?, bank_account_id = ?, chart_account_id = ?, is_accountable = ? WHERE id = ?");
            $stmt->execute([$member_id, $amount, $payment_date, $payment_method, $type, $notes, $congregation_id, $giver_name, $bank_account_id, $chart_account_id, $is_accountable, $id]);
        } else {
            $stmt = $db->prepare("UPDATE tithes SET member_id = ?, amount = ?, payment_date = ?, payment_method = ?, type = ?, notes = ?, congregation_id = ?, giver_name = ?, bank_account_id = ?, chart_account_id = ? WHERE id = ?");
            $stmt->execute([$member_id, $amount, $payment_date, $payment_method, $type, $notes, $congregation_id, $giver_name, $bank_account_id, $chart_account_id, $id]);
        }

        // Adjust Bank Balances
        if ($oldRecord['bank_account_id'] != $bank_account_id || $oldRecord['amount'] != $amount || $oldIsAccountable !== (int)$is_accountable) {
            // Revert old
            if ($oldIsAccountable && $oldRecord['bank_account_id']) {
                $db->prepare("UPDATE bank_accounts SET current_balance = current_balance - ? WHERE id = ?")->execute([$oldRecord['amount'], $oldRecord['bank_account_id']]);
            }
            // Apply new
            if ($is_accountable && $bank_account_id) {
                $db->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE id = ?")->execute([$amount, $bank_account_id]);
            }
        }

        redirect('/admin/tithes');
    }

    public function delete($id) {
        requireLogin();
        $db = (new Database())->connect();

        $stmtCheck = $db->prepare("SELECT payment_date, congregation_id, amount, bank_account_id" . ($this->hasAccountableField($db) ? ", is_accountable" : "") . " FROM tithes WHERE id = ?");
        $stmtCheck->execute([$id]);
        $record = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$record) {
            redirect('/admin/tithes');
        }
        $isAccountable = $this->hasAccountableField($db) ? (int)($record['is_accountable'] ?? 1) : 1;
        if ($isAccountable && $this->blockClosedFinancialPeriod($db, $record['congregation_id'] ?? null, $record['payment_date'] ?? null, '/admin/tithes')) {
            return;
        }
        if ($isAccountable && !empty($record['bank_account_id'])) {
            $db->prepare("UPDATE bank_accounts SET current_balance = current_balance - ? WHERE id = ?")->execute([$record['amount'], $record['bank_account_id']]);
        }
        
        $stmt = $db->prepare("DELETE FROM tithes WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect('/admin/tithes');
    }
}

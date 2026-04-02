<?php
// src/controllers/ExpenseController.php

class ExpenseController {
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
        return $this->tableHasColumn($db, 'expenses', 'is_accountable');
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
        
        // Pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;

        // Base Query for Count
        $countSql = "SELECT COUNT(*) FROM expenses e WHERE 1=1";
        $countParams = [];

        // Filters (Apply to both Count and Select)
        $whereClause = "";
        if (!empty($_SESSION['user_congregation_id'])) {
            $whereClause .= " AND e.congregation_id = ?";
            $countParams[] = $_SESSION['user_congregation_id'];
        } elseif (!empty($_GET['congregation_id'])) {
            $whereClause .= " AND e.congregation_id = ?";
            $countParams[] = $_GET['congregation_id'];
        }
        
        if (!empty($_GET['start_date'])) {
            $whereClause .= " AND e.expense_date >= ?";
            $countParams[] = $_GET['start_date'];
        }
        if (!empty($_GET['end_date'])) {
            $whereClause .= " AND e.expense_date <= ?";
            $countParams[] = $_GET['end_date'];
        }

        // Get Total Count
        $stmtCount = $db->prepare($countSql . $whereClause);
        $stmtCount->execute($countParams);
        $totalRecords = $stmtCount->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        // Calcular Total Geral (Soma de Valores com base nos filtros)
        $sumSql = "SELECT SUM(amount) FROM expenses e WHERE 1=1" . $whereClause;
        $stmtSum = $db->prepare($sumSql);
        $stmtSum->execute($countParams);
        $totalAmount = $stmtSum->fetchColumn() ?: 0;

        // Main Query (Remove limit/offset para permitir agrupamento e DataTables no frontend, igual a entradas)
        $sql = "SELECT e.*, c.name as congregation_name 
                FROM expenses e 
                LEFT JOIN congregations c ON e.congregation_id = c.id 
                WHERE 1=1" . $whereClause;

        $sql .= " ORDER BY e.expense_date DESC";
        
        $params = $countParams; // Reuse params
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $expenses = $stmt->fetchAll();

        // Congregations for filter
        $sql_cong = "SELECT * FROM congregations ORDER BY name ASC";
        $congregations = $db->query($sql_cong)->fetchAll();

        view('admin/expenses/index', [
            'expenses' => $expenses, 
            'congregations' => $congregations,
            // 'currentPage' => $page,
            // 'totalPages' => $totalPages,
            // 'totalRecords' => $totalRecords,
            'totalAmount' => $totalAmount,
            // 'limit' => $limit
        ]);
    }

    public function create() {
        requirePermission('financial.manage');
        $db = (new Database())->connect();
        
        $sql = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        $congregations = $db->query($sql)->fetchAll();
        
        // Get Banks and Chart of Accounts
        $bankAccounts = $db->query("SELECT * FROM bank_accounts WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $hasSetCol = $this->tableHasColumn($db, 'chart_of_accounts', 'account_set_id');
        if ($hasSetCol) {
            $setId = $this->getDefaultAccountSetId($db);
            $stmtCA = $db->prepare("SELECT * FROM chart_of_accounts WHERE account_set_id = ? AND type = 'expense' AND status = 'active' ORDER BY code ASC");
            $stmtCA->execute([$setId]);
            $chartAccounts = $stmtCA->fetchAll();
        } else {
            $chartAccounts = $db->query("SELECT * FROM chart_of_accounts WHERE type = 'expense' AND status = 'active' ORDER BY code ASC")->fetchAll();
        }
        
        view('admin/expenses/create', [
            'congregations' => $congregations,
            'bankAccounts' => $bankAccounts,
            'chartAccounts' => $chartAccounts,
            'hasAccountableField' => $this->hasAccountableField($db)
        ]);
    }

    public function store() {
        requirePermission('financial.manage');
        
        $description = $_POST['description'];
        $amount = str_replace(['.', ','], ['', '.'], $_POST['amount']); // Handle 1.000,00 -> 1000.00 if needed, but input type=number usually handles dots.
        // Assuming simple number input for now or handled by frontend
        $amount = $_POST['amount'];
        
        $expense_date = $_POST['expense_date'];
        $category = $_POST['category'];
        $notes = $_POST['notes'];
        $bank_account_id = !empty($_POST['bank_account_id']) ? $_POST['bank_account_id'] : null;
        $chart_account_id = !empty($_POST['chart_account_id']) ? $_POST['chart_account_id'] : null;
        
        $congregation_id = $_POST['congregation_id'] ?? null;
        if (empty($congregation_id) && !empty($_SESSION['user_congregation_id'])) {
            $congregation_id = $_SESSION['user_congregation_id'];
        }

        $db = (new Database())->connect();
        $is_accountable = $this->hasAccountableField($db) ? $this->isAccountableRequest() : 1;
        if (!$is_accountable) {
            $bank_account_id = null;
            $chart_account_id = null;
        }
        if ($is_accountable && $this->blockClosedFinancialPeriod($db, $congregation_id, $expense_date, '/admin/expenses')) {
            return;
        }
        if ($this->hasAccountableField($db)) {
            $stmt = $db->prepare("INSERT INTO expenses (description, amount, expense_date, category, congregation_id, notes, bank_account_id, chart_account_id, is_accountable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$description, $amount, $expense_date, $category, $congregation_id, $notes, $bank_account_id, $chart_account_id, $is_accountable]);
        } else {
            $stmt = $db->prepare("INSERT INTO expenses (description, amount, expense_date, category, congregation_id, notes, bank_account_id, chart_account_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$description, $amount, $expense_date, $category, $congregation_id, $notes, $bank_account_id, $chart_account_id]);
        }

        if ($is_accountable && $bank_account_id) {
            $stmtUpdateBank = $db->prepare("UPDATE bank_accounts SET current_balance = current_balance - ? WHERE id = ?");
            $stmtUpdateBank->execute([$amount, $bank_account_id]);
        }

        redirect('/admin/expenses');
    }

    public function edit($id) {
        requirePermission('financial.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM expenses WHERE id = ?");
        $stmt->execute([$id]);
        $expense = $stmt->fetch();

        if (!$expense) redirect('/admin/expenses');
        
        // Security check
        if (!empty($_SESSION['user_congregation_id']) && $expense['congregation_id'] != $_SESSION['user_congregation_id']) {
            redirect('/admin/expenses');
        }
        if ((!isset($expense['is_accountable']) || (int)$expense['is_accountable'] === 1) && $this->blockClosedFinancialPeriod($db, $expense['congregation_id'] ?? null, $expense['expense_date'] ?? null, '/admin/expenses')) {
            return;
        }

        $sql = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        $congregations = $db->query($sql)->fetchAll();

        // Get Banks and Chart of Accounts
        $bankAccounts = $db->query("SELECT * FROM bank_accounts WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $hasSetCol = $this->tableHasColumn($db, 'chart_of_accounts', 'account_set_id');
        if ($hasSetCol) {
            $setId = $this->getDefaultAccountSetId($db);
            $stmtCA = $db->prepare("SELECT * FROM chart_of_accounts WHERE account_set_id = ? AND type = 'expense' AND status = 'active' ORDER BY code ASC");
            $stmtCA->execute([$setId]);
            $chartAccounts = $stmtCA->fetchAll();
        } else {
            $chartAccounts = $db->query("SELECT * FROM chart_of_accounts WHERE type = 'expense' AND status = 'active' ORDER BY code ASC")->fetchAll();
        }

        view('admin/expenses/edit', [
            'expense' => $expense, 
            'congregations' => $congregations,
            'bankAccounts' => $bankAccounts,
            'chartAccounts' => $chartAccounts,
            'hasAccountableField' => $this->hasAccountableField($db)
        ]);
    }

    public function update($id) {
        requirePermission('financial.manage');
        $db = (new Database())->connect();
        
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $expense_date = $_POST['expense_date'];
        $category = $_POST['category'];
        $notes = $_POST['notes'];
        $congregation_id = $_POST['congregation_id'];
        $bank_account_id = !empty($_POST['bank_account_id']) ? $_POST['bank_account_id'] : null;
        $chart_account_id = !empty($_POST['chart_account_id']) ? $_POST['chart_account_id'] : null;
        $hasAccountableField = $this->hasAccountableField($db);
        $is_accountable = $hasAccountableField ? $this->isAccountableRequest() : 1;
        if (!$is_accountable) {
            $bank_account_id = null;
            $chart_account_id = null;
        }

        // Security check before update
        if (!empty($_SESSION['user_congregation_id'])) {
            $stmtCheck = $db->prepare("SELECT congregation_id FROM expenses WHERE id = ?");
            $stmtCheck->execute([$id]);
            $currentCong = $stmtCheck->fetchColumn();
            if ($currentCong != $_SESSION['user_congregation_id']) {
                redirect('/admin/expenses');
            }
            $congregation_id = $_SESSION['user_congregation_id']; // Force same scope
        }

        // Get old record to handle bank balance update
        $stmtOld = $db->prepare("SELECT amount, bank_account_id, expense_date, congregation_id" . ($hasAccountableField ? ", is_accountable" : "") . " FROM expenses WHERE id = ?");
        $stmtOld->execute([$id]);
        $oldRecord = $stmtOld->fetch();

        $oldIsAccountable = $hasAccountableField ? (int)($oldRecord['is_accountable'] ?? 1) : 1;
        if ($oldIsAccountable && $this->blockClosedFinancialPeriod($db, $oldRecord['congregation_id'] ?? null, $oldRecord['expense_date'] ?? null, '/admin/expenses')) {
            return;
        }
        if ($is_accountable && $this->blockClosedFinancialPeriod($db, $congregation_id, $expense_date, '/admin/expenses')) {
            return;
        }

        if ($hasAccountableField) {
            $stmt = $db->prepare("UPDATE expenses SET description=?, amount=?, expense_date=?, category=?, congregation_id=?, notes=?, bank_account_id=?, chart_account_id=?, is_accountable=? WHERE id=?");
            $stmt->execute([$description, $amount, $expense_date, $category, $congregation_id, $notes, $bank_account_id, $chart_account_id, $is_accountable, $id]);
        } else {
            $stmt = $db->prepare("UPDATE expenses SET description=?, amount=?, expense_date=?, category=?, congregation_id=?, notes=?, bank_account_id=?, chart_account_id=? WHERE id=?");
            $stmt->execute([$description, $amount, $expense_date, $category, $congregation_id, $notes, $bank_account_id, $chart_account_id, $id]);
        }

        // Adjust Bank Balances
        if ($oldRecord['bank_account_id'] != $bank_account_id || $oldRecord['amount'] != $amount || $oldIsAccountable !== (int)$is_accountable) {
            // Revert old
            if ($oldIsAccountable && $oldRecord['bank_account_id']) {
                $db->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE id = ?")->execute([$oldRecord['amount'], $oldRecord['bank_account_id']]);
            }
            // Apply new
            if ($is_accountable && $bank_account_id) {
                $db->prepare("UPDATE bank_accounts SET current_balance = current_balance - ? WHERE id = ?")->execute([$amount, $bank_account_id]);
            }
        }

        redirect('/admin/expenses');
    }

    public function delete($id) {
        requirePermission('financial.manage');
        $db = (new Database())->connect();
        
        // Security check
        if (!empty($_SESSION['user_congregation_id'])) {
            $stmtCheck = $db->prepare("SELECT congregation_id FROM expenses WHERE id = ?");
            $stmtCheck->execute([$id]);
            $currentCong = $stmtCheck->fetchColumn();
            if ($currentCong != $_SESSION['user_congregation_id']) {
                redirect('/admin/expenses');
            }
        }

        $stmtCheck = $db->prepare("SELECT expense_date, congregation_id, amount, bank_account_id" . ($this->hasAccountableField($db) ? ", is_accountable" : "") . " FROM expenses WHERE id = ?");
        $stmtCheck->execute([$id]);
        $record = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$record) {
            redirect('/admin/expenses');
        }
        $isAccountable = $this->hasAccountableField($db) ? (int)($record['is_accountable'] ?? 1) : 1;
        if ($isAccountable && $this->blockClosedFinancialPeriod($db, $record['congregation_id'] ?? null, $record['expense_date'] ?? null, '/admin/expenses')) {
            return;
        }
        if ($isAccountable && !empty($record['bank_account_id'])) {
            $db->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE id = ?")->execute([$record['amount'], $record['bank_account_id']]);
        }

        $stmt = $db->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->execute([$id]);

        redirect('/admin/expenses');
    }
}

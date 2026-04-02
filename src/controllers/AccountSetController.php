<?php
class AccountSetController {
    private $db;
    public function __construct() {
        $this->db = (new Database())->connect();
    }
    private function requireFinancePermission() {
        if (!isset($_SESSION['user_id'])) redirect('/admin/login');
        if (!hasPermission('financial_accounts.manage') && !hasPermission('admin.manage')) redirect('/admin/dashboard');
    }
    public function index() {
        $this->requireFinancePermission();
        $hasCongCol = $this->tableHasColumn($this->db, 'account_sets', 'congregation_id');
        if ($hasCongCol) {
            $sets = $this->db->query("SELECT s.*, c.name AS congregation_name FROM account_sets s LEFT JOIN congregations c ON s.congregation_id = c.id ORDER BY is_default DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $congregations = $this->db->query("SELECT id, name FROM congregations ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sets = $this->db->query("SELECT * FROM account_sets ORDER BY is_default DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $congregations = [];
        }
        view('admin/financial/account_sets/index', ['sets' => $sets, 'congregations' => $congregations]);
    }
    public function store() {
        $this->requireFinancePermission();
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $description = $_POST['description'] ?? null;
        $scope = $_POST['scope'] ?? 'general';
        $congregation_id = null;
        if ($scope === 'congregation' && $this->tableHasColumn($this->db, 'account_sets', 'congregation_id')) {
            $cid = (int)($_POST['congregation_id'] ?? 0);
            if ($cid > 0) $congregation_id = $cid;
        }
        if ($name === '') {
            redirect('/admin/financial/account-sets');
            return;
        }
        if ($this->tableHasColumn($this->db, 'account_sets', 'congregation_id')) {
            $stmt = $this->db->prepare("INSERT INTO account_sets (name, description, congregation_id, active, is_default) VALUES (?, ?, ?, 1, 0)");
            $stmt->execute([$name, $description, $congregation_id]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO account_sets (name, description, active, is_default) VALUES (?, ?, 1, 0)");
            $stmt->execute([$name, $description]);
        }
        redirect('/admin/financial/account-sets');
    }
    public function makeDefault($id) {
        $this->requireFinancePermission();
        $this->db->beginTransaction();
        try {
            if ($this->tableHasColumn($this->db, 'account_sets', 'congregation_id')) {
                $stmt = $this->db->prepare("SELECT congregation_id FROM account_sets WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $scopeCid = $row ? $row['congregation_id'] : null;
                if ($scopeCid === null) {
                    $this->db->exec("UPDATE account_sets SET is_default = 0 WHERE congregation_id IS NULL");
                    $stmt2 = $this->db->prepare("UPDATE account_sets SET is_default = 1 WHERE id = ?");
                    $stmt2->execute([$id]);
                } else {
                    $stmt0 = $this->db->prepare("UPDATE account_sets SET is_default = 0 WHERE congregation_id = ?");
                    $stmt0->execute([$scopeCid]);
                    $stmt2 = $this->db->prepare("UPDATE account_sets SET is_default = 1 WHERE id = ?");
                    $stmt2->execute([$id]);
                }
            } else {
                $this->db->exec("UPDATE account_sets SET is_default = 0");
                $stmt2 = $this->db->prepare("UPDATE account_sets SET is_default = 1 WHERE id = ?");
                $stmt2->execute([$id]);
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
        }
        redirect('/admin/financial/account-sets');
    }
    public function toggle($id) {
        $this->requireFinancePermission();
        $stmt = $this->db->prepare("UPDATE account_sets SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END WHERE id = ?");
        $stmt->execute([$id]);
        redirect('/admin/financial/account-sets');
    }
    public function delete($id) {
        $this->requireFinancePermission();
        $countAcc = $this->db->prepare("SELECT COUNT(*) FROM chart_of_accounts WHERE account_set_id = ?");
        $countAcc->execute([$id]);
        if ((int)$countAcc->fetchColumn() > 0) {
            redirect('/admin/financial/account-sets');
            return;
        }
        $stmt = $this->db->prepare("DELETE FROM account_sets WHERE id = ?");
        $stmt->execute([$id]);
        redirect('/admin/financial/account-sets');
    }
    
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
    
    public function edit($id) {
        $this->requireFinancePermission();
        $stmt = $this->db->prepare("SELECT * FROM account_sets WHERE id = ?");
        $stmt->execute([$id]);
        $set = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$set) {
            redirect('/admin/financial/account-sets');
            return;
        }
        $hasCongCol = $this->tableHasColumn($this->db, 'account_sets', 'congregation_id');
        $congregations = [];
        if ($hasCongCol) {
            $congregations = $this->db->query("SELECT id, name FROM congregations ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        }
        view('admin/financial/account_sets/edit', ['set' => $set, 'congregations' => $congregations, 'hasCongCol' => $hasCongCol]);
    }
    
    public function update($id) {
        $this->requireFinancePermission();
        verify_csrf();
        $stmt = $this->db->prepare("SELECT * FROM account_sets WHERE id = ?");
        $stmt->execute([$id]);
        $set = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$set) {
            redirect('/admin/financial/account-sets');
            return;
        }
        $name = trim($_POST['name'] ?? '');
        $description = $_POST['description'] ?? null;
        if ($name === '') {
            redirect('/admin/financial/account-sets/edit/'.$id);
            return;
        }
        $hasCongCol = $this->tableHasColumn($this->db, 'account_sets', 'congregation_id');
        if ($hasCongCol) {
            $scope = $_POST['scope'] ?? 'general';
            $congregation_id = null;
            if ($scope === 'congregation') {
                $cid = (int)($_POST['congregation_id'] ?? 0);
                if ($cid > 0) $congregation_id = $cid;
            }
            $upd = $this->db->prepare("UPDATE account_sets SET name = ?, description = ?, congregation_id = ? WHERE id = ?");
            $upd->execute([$name, $description, $congregation_id, $id]);
        } else {
            $upd = $this->db->prepare("UPDATE account_sets SET name = ?, description = ? WHERE id = ?");
            $upd->execute([$name, $description, $id]);
        }
        redirect('/admin/financial/account-sets');
    }
}

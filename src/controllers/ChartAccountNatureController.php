<?php

class ChartAccountNatureController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    private function requireFinancePermission() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }
        if (!hasPermission('financial_accounts.manage') && !hasPermission('admin.manage')) {
            redirect('/admin/dashboard');
        }
    }

    private function validateBaseType($baseType) {
        return in_array($baseType, ['asset', 'liability', 'income', 'expense'], true);
    }

    public function index() {
        $this->requireFinancePermission();
        $stmt = $this->db->query("
            SELECT *
            FROM chart_account_natures
            ORDER BY CASE base_type
                WHEN 'asset' THEN 1
                WHEN 'liability' THEN 2
                WHEN 'income' THEN 3
                WHEN 'expense' THEN 4
                ELSE 9
            END, name ASC
        ");
        $natures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        view('admin/financial/chart_account_natures/index', ['natures' => $natures]);
    }

    public function store() {
        $this->requireFinancePermission();
        verify_csrf();

        $name = trim($_POST['name'] ?? '');
        $baseType = $_POST['base_type'] ?? '';
        $status = $_POST['status'] ?? 'active';

        if ($name === '' || !$this->validateBaseType($baseType)) {
            $_SESSION['flash_error'] = 'Informe um nome e um grupo válido para a natureza.';
            redirect('/admin/financial/chart-account-natures');
            return;
        }

        $dup = $this->db->prepare("SELECT id FROM chart_account_natures WHERE LOWER(name) = LOWER(?)");
        $dup->execute([$name]);
        if ($dup->fetch()) {
            $_SESSION['flash_error'] = 'Já existe uma natureza com esse nome.';
            redirect('/admin/financial/chart-account-natures');
            return;
        }

        $stmt = $this->db->prepare("INSERT INTO chart_account_natures (name, base_type, status) VALUES (?, ?, ?)");
        $stmt->execute([$name, $baseType, $status]);

        $_SESSION['flash_success'] = 'Natureza cadastrada com sucesso!';
        redirect('/admin/financial/chart-account-natures');
    }

    public function update($id) {
        $this->requireFinancePermission();
        verify_csrf();

        $name = trim($_POST['name'] ?? '');
        $baseType = $_POST['base_type'] ?? '';
        $status = $_POST['status'] ?? 'active';

        if ($name === '' || !$this->validateBaseType($baseType)) {
            $_SESSION['flash_error'] = 'Informe um nome e um grupo válido para a natureza.';
            redirect('/admin/financial/chart-account-natures');
            return;
        }

        $dup = $this->db->prepare("SELECT id FROM chart_account_natures WHERE LOWER(name) = LOWER(?) AND id <> ?");
        $dup->execute([$name, $id]);
        if ($dup->fetch()) {
            $_SESSION['flash_error'] = 'Já existe uma natureza com esse nome.';
            redirect('/admin/financial/chart-account-natures');
            return;
        }

        $stmt = $this->db->prepare("UPDATE chart_account_natures SET name = ?, base_type = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $baseType, $status, $id]);

        $_SESSION['flash_success'] = 'Natureza atualizada com sucesso!';
        redirect('/admin/financial/chart-account-natures');
    }

    public function delete($id) {
        $this->requireFinancePermission();
        verify_csrf();

        $inUse = $this->db->prepare("SELECT COUNT(*) FROM chart_of_accounts WHERE nature_id = ?");
        $inUse->execute([$id]);
        if ((int)$inUse->fetchColumn() > 0) {
            $_SESSION['flash_error'] = 'Esta natureza está vinculada a uma ou mais contas contábeis e não pode ser removida.';
            redirect('/admin/financial/chart-account-natures');
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM chart_account_natures WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_success'] = 'Natureza removida com sucesso!';
        redirect('/admin/financial/chart-account-natures');
    }
}

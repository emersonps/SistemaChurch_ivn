<?php
require_once __DIR__ . '/../models/Role.php';

class BankAccountController {
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

    public function index() {
        $this->requireFinancePermission();
        
        $stmt = $this->db->query("SELECT * FROM bank_accounts ORDER BY name ASC");
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        view('admin/financial/bank_accounts/index', ['accounts' => $accounts]);
    }

    public function create() {
        $this->requireFinancePermission();
        view('admin/financial/bank_accounts/create');
    }

    public function store() {
        $this->requireFinancePermission();
        verify_csrf();

        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? 'conta_corrente';
        $bank_name = $_POST['bank_name'] ?? null;
        $agency = $_POST['agency'] ?? null;
        $account_number = $_POST['account_number'] ?? null;
        $initial_balance = (float) ($_POST['initial_balance'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        $stmt = $this->db->prepare("INSERT INTO bank_accounts (name, type, bank_name, agency, account_number, initial_balance, current_balance, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $type, $bank_name, $agency, $account_number, $initial_balance, $initial_balance, $status]);

        $_SESSION['flash_success'] = "Conta/Caixa cadastrada com sucesso!";
        redirect('/admin/financial/bank-accounts');
    }

    public function edit($id) {
        $this->requireFinancePermission();
        
        $stmt = $this->db->prepare("SELECT * FROM bank_accounts WHERE id = ?");
        $stmt->execute([$id]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) redirect('/admin/financial/bank-accounts');
        
        view('admin/financial/bank_accounts/edit', ['account' => $account]);
    }

    public function update($id) {
        $this->requireFinancePermission();
        verify_csrf();

        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? 'conta_corrente';
        $bank_name = $_POST['bank_name'] ?? null;
        $agency = $_POST['agency'] ?? null;
        $account_number = $_POST['account_number'] ?? null;
        $status = $_POST['status'] ?? 'active';

        $stmt = $this->db->prepare("UPDATE bank_accounts SET name = ?, type = ?, bank_name = ?, agency = ?, account_number = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $type, $bank_name, $agency, $account_number, $status, $id]);

        $_SESSION['flash_success'] = "Conta atualizada com sucesso!";
        redirect('/admin/financial/bank-accounts');
    }
}

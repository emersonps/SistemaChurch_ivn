<?php
// src/controllers/DonationController.php

class DonationController {
    private $pixKeyTypes = [
        'cpf' => 'CPF',
        'cnpj' => 'CNPJ',
        'email' => 'E-mail',
        'telefone' => 'Telefone',
        'aleatoria' => 'Chave Aleatória',
    ];

    public function index() {
        requirePermission('donations.view');
        $db = (new Database())->connect();
        $accounts = $db->query("SELECT * FROM donation_accounts ORDER BY display_order ASC, id ASC")->fetchAll();
        view('admin/donations/index', ['accounts' => $accounts, 'pixKeyTypes' => $this->pixKeyTypes]);
    }

    public function create() {
        requirePermission('donations.manage');
        $siteProfile = getChurchSiteProfileSettings();
        view('admin/donations/create', ['pixKeyTypes' => $this->pixKeyTypes, 'siteProfile' => $siteProfile]);
    }

    public function store() {
        requirePermission('donations.manage');

        $data = $this->collectInput();
        if ($data === null) {
            $_SESSION['flash_error'] = 'Preencha todos os campos obrigatórios.';
            redirect('/admin/donations/create');
            return;
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare("INSERT INTO donation_accounts (bank_name, beneficiary_name, beneficiary_city, pix_key, pix_key_type, agency, account_number, display_order, status)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['bank_name'], $data['beneficiary_name'], $data['beneficiary_city'],
            $data['pix_key'], $data['pix_key_type'], $data['agency'], $data['account_number'],
            $data['display_order'], $data['status']
        ]);

        $_SESSION['flash_success'] = 'Conta para doação cadastrada com sucesso.';
        redirect('/admin/donations');
    }

    public function edit($id) {
        requirePermission('donations.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM donation_accounts WHERE id = ?");
        $stmt->execute([$id]);
        $account = $stmt->fetch();
        if (!$account) {
            redirect('/admin/donations');
            return;
        }
        view('admin/donations/edit', ['account' => $account, 'pixKeyTypes' => $this->pixKeyTypes]);
    }

    public function update($id) {
        requirePermission('donations.manage');

        $data = $this->collectInput();
        if ($data === null) {
            $_SESSION['flash_error'] = 'Preencha todos os campos obrigatórios.';
            redirect('/admin/donations/edit/' . $id);
            return;
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare("UPDATE donation_accounts SET bank_name=?, beneficiary_name=?, beneficiary_city=?, pix_key=?, pix_key_type=?, agency=?, account_number=?, display_order=?, status=? WHERE id=?");
        $stmt->execute([
            $data['bank_name'], $data['beneficiary_name'], $data['beneficiary_city'],
            $data['pix_key'], $data['pix_key_type'], $data['agency'], $data['account_number'],
            $data['display_order'], $data['status'], $id
        ]);

        $_SESSION['flash_success'] = 'Conta para doação atualizada com sucesso.';
        redirect('/admin/donations');
    }

    public function delete($id) {
        requirePermission('donations.manage');
        $db = (new Database())->connect();
        $db->prepare("DELETE FROM donation_accounts WHERE id = ?")->execute([$id]);
        $_SESSION['flash_success'] = 'Conta para doação removida.';
        redirect('/admin/donations');
    }

    // Página pública com as chaves PIX para doação
    public function publicIndex() {
        $db = (new Database())->connect();
        $accounts = $db->query("SELECT * FROM donation_accounts WHERE status = 'active' ORDER BY display_order ASC, id ASC")->fetchAll();

        foreach ($accounts as &$account) {
            $account['pix_payload'] = buildPixBrCode(
                $account['pix_key'],
                $account['beneficiary_name'],
                $account['beneficiary_city']
            );
        }
        unset($account);

        view('public/donate', ['accounts' => $accounts, 'pixKeyTypes' => $this->pixKeyTypes]);
    }

    private function collectInput() {
        $bankName = trim($_POST['bank_name'] ?? '');
        $beneficiaryName = trim($_POST['beneficiary_name'] ?? '');
        $beneficiaryCity = trim($_POST['beneficiary_city'] ?? '');
        $pixKey = trim($_POST['pix_key'] ?? '');
        $pixKeyType = $_POST['pix_key_type'] ?? '';
        $agency = trim($_POST['agency'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        if ($bankName === '' || $beneficiaryName === '' || $beneficiaryCity === '' || $pixKey === '' || !isset($this->pixKeyTypes[$pixKeyType])) {
            return null;
        }

        return [
            'bank_name' => $bankName,
            'beneficiary_name' => $beneficiaryName,
            'beneficiary_city' => $beneficiaryCity,
            'pix_key' => $pixKey,
            'pix_key_type' => $pixKeyType,
            'agency' => $agency ?: null,
            'account_number' => $accountNumber ?: null,
            'display_order' => $displayOrder,
            'status' => $status,
        ];
    }
}

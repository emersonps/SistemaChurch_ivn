<?php
require_once __DIR__ . '/../models/Role.php';

class ChartOfAccountController {
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
    private function hasNatureFeature() {
        return $this->tableHasColumn($this->db, 'chart_of_accounts', 'nature_id');
    }
    private function getNatureOptions($includeId = null) {
        if (!$this->hasNatureFeature()) {
            return [];
        }

        $sql = "SELECT id, name, base_type, status FROM chart_account_natures";
        $params = [];
        if ($includeId) {
            $sql .= " WHERE status = 'active' OR id = ?";
            $params[] = $includeId;
        } else {
            $sql .= " WHERE status = 'active'";
        }
        $sql .= " ORDER BY CASE base_type
                    WHEN 'asset' THEN 1
                    WHEN 'liability' THEN 2
                    WHEN 'income' THEN 3
                    WHEN 'expense' THEN 4
                    ELSE 9
                  END, name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    private function resolveNatureSelection($natureId, $fallbackType = 'expense') {
        if (!$this->hasNatureFeature() || empty($natureId)) {
            return ['nature_id' => null, 'type' => $fallbackType];
        }

        $stmt = $this->db->prepare("SELECT id, base_type FROM chart_account_natures WHERE id = ?");
        $stmt->execute([(int)$natureId]);
        $nature = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$nature) {
            return null;
        }

        return [
            'nature_id' => (int)$nature['id'],
            'type' => $nature['base_type']
        ];
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

    public function index() {
        $this->requireFinancePermission();
        try {
        $hasCongCol = $this->tableHasColumn($this->db, 'account_sets', 'congregation_id');
        $cid = $_SESSION['user_congregation_id'] ?? null;
        if ($hasCongCol) {
            if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                $stmtSets = $this->db->prepare("SELECT id, name, is_default, congregation_id FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC");
                $stmtSets->execute();
                $sets = $stmtSets->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmtSets = $this->db->prepare("SELECT id, name, is_default, congregation_id FROM account_sets WHERE active = 1 AND (congregation_id IS NULL OR congregation_id = ?) ORDER BY is_default DESC, name ASC");
                $stmtSets->execute([$cid]);
                $sets = $stmtSets->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            $stmtSets = $this->db->prepare("SELECT id, name, is_default FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC");
            $stmtSets->execute();
            $sets = $stmtSets->fetchAll(PDO::FETCH_ASSOC);
        }
        $selectedSet = null;
        if (isset($_GET['set']) && $_GET['set'] !== '') {
            $selectedSet = (int)$_GET['set'];
        } else {
            foreach ($sets as $s) { if ((int)$s['is_default'] === 1) { $selectedSet = (int)$s['id']; break; } }
            if (!$selectedSet && !empty($sets)) $selectedSet = (int)$sets[0]['id'];
        }
        $hasNatureFeature = $this->hasNatureFeature();
        $sqlAccounts = "
            SELECT c.*, 
                   p.name as parent_name, 
                   p.code as parent_code," .
                   ($hasNatureFeature ? "
                   n.name as nature_name,
                   n.base_type as nature_base_type," : "
                   NULL as nature_name,
                   NULL as nature_base_type,") . "
                   (SELECT COUNT(*) FROM chart_of_accounts sc WHERE sc.parent_id = c.id) AS children_count
            FROM chart_of_accounts c 
            LEFT JOIN chart_of_accounts p ON c.parent_id = p.id " .
                   ($hasNatureFeature ? "LEFT JOIN chart_account_natures n ON c.nature_id = n.id " : "") . "
            WHERE c.account_set_id = ?
            ORDER BY c.code ASC
        ";
        $stmt = $this->db->prepare($sqlAccounts);
        $stmt->execute([$selectedSet]);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        view('admin/financial/chart_accounts/index', ['accounts' => $accounts, 'sets' => $sets, 'selectedSet' => $selectedSet]);
        } catch (PDOException $e) {
            view('admin/error', [
                'message' => 'Algumas migrações não foram aplicadas (colunas ausentes no banco).',
                'hint' => 'Por favor, abra o Gerenciador de Migrações e execute as pendentes.'
            ]);
        }
    }

    public function create() {
        $this->requireFinancePermission();
        $hasCongCol = $this->tableHasColumn($this->db, 'account_sets', 'congregation_id');
        $cid = $_SESSION['user_congregation_id'] ?? null;
        if ($hasCongCol) {
            if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                $stmtSets = $this->db->prepare("SELECT id, name, is_default, congregation_id FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC");
                $stmtSets->execute();
                $sets = $stmtSets->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmtSets = $this->db->prepare("SELECT id, name, is_default, congregation_id FROM account_sets WHERE active = 1 AND (congregation_id IS NULL OR congregation_id = ?) ORDER BY is_default DESC, name ASC");
                $stmtSets->execute([$cid]);
                $sets = $stmtSets->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            $stmtSets = $this->db->prepare("SELECT id, name, is_default FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC");
            $stmtSets->execute();
            $sets = $stmtSets->fetchAll(PDO::FETCH_ASSOC);
        }
        $selectedSet = null;
        if (isset($_GET['set']) && $_GET['set'] !== '') {
            $selectedSet = (int)$_GET['set'];
        } else {
            foreach ($sets as $s) { if ((int)$s['is_default'] === 1) { $selectedSet = (int)$s['id']; break; } }
            if (!$selectedSet && !empty($sets)) $selectedSet = (int)$sets[0]['id'];
        }
        $stmt = $this->db->prepare("SELECT id, code, name FROM chart_of_accounts WHERE account_set_id = ? ORDER BY code ASC");
        $stmt->execute([$selectedSet]);
        $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $natures = $this->getNatureOptions();
        view('admin/financial/chart_accounts/create', ['parents' => $parents, 'sets' => $sets, 'selectedSet' => $selectedSet, 'natures' => $natures, 'hasNatureFeature' => $this->hasNatureFeature()]);
    }

    public function store() {
        $this->requireFinancePermission();
        verify_csrf();

        $code = $_POST['code'] ?? '';
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? 'expense';
        $natureSelection = $this->resolveNatureSelection($_POST['nature_id'] ?? null, $type);
        if (isset($_POST['nature_id']) && $_POST['nature_id'] !== '' && !$natureSelection) {
            $_SESSION['flash_error'] = "A natureza selecionada não é válida.";
            redirect('/admin/financial/chart-accounts/create');
            return;
        }
        $nature_id = $natureSelection['nature_id'] ?? null;
        $type = $natureSelection['type'] ?? $type;
        $structure = $_POST['structure'] ?? 'analytic';
        $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        if ($structure === 'synthetic') {
            $parent_id = null;
        }
        $description = $_POST['description'] ?? null;
        $status = $_POST['status'] ?? 'active';
        $account_set_id = !empty($_POST['account_set_id']) ? (int)$_POST['account_set_id'] : null;
        if (!$account_set_id) {
            $sets = $this->db->query("SELECT id, is_default FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($sets as $s) { if ((int)$s['is_default'] === 1) { $account_set_id = (int)$s['id']; break; } }
            if (!$account_set_id && !empty($sets)) $account_set_id = (int)$sets[0]['id'];
        }
        if ($parent_id) {
            $chk = $this->db->prepare("SELECT id FROM chart_of_accounts WHERE id = ? AND account_set_id = ?");
            $chk->execute([$parent_id, $account_set_id]);
            if (!$chk->fetch()) {
                $_SESSION['flash_error'] = "A conta pai selecionada pertence a outro Plano de Contas.";
                redirect('/admin/financial/chart-accounts/create?set='.$account_set_id);
                return;
            }
        }
        if ($structure === 'analytic' && empty($parent_id)) {
            $_SESSION['flash_error'] = "Para marcar como Analítica (Filho), selecione uma Conta Pai.";
            redirect('/admin/financial/chart-accounts/create?set='.$account_set_id);
            return;
        }
        // Verificar duplicidade de código dentro do conjunto
        $dup = $this->db->prepare("SELECT id FROM chart_of_accounts WHERE account_set_id = ? AND code = ?");
        $dup->execute([$account_set_id, $code]);
        if ($dup->fetch()) {
            $_SESSION['flash_error'] = "Já existe uma conta com o código \"$code\" neste Plano de Contas.";
            redirect('/admin/financial/chart-accounts/create?set='.$account_set_id);
            return;
        }

        if ($this->hasNatureFeature()) {
            $stmt = $this->db->prepare("INSERT INTO chart_of_accounts (code, name, type, nature_id, parent_id, description, status, account_set_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $type, $nature_id, $parent_id, $description, $status, $account_set_id]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO chart_of_accounts (code, name, type, parent_id, description, status, account_set_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $type, $parent_id, $description, $status, $account_set_id]);
        }

        $_SESSION['flash_success'] = "Conta contábil cadastrada com sucesso!";
        redirect('/admin/financial/chart-accounts');
    }

    public function edit($id) {
        $this->requireFinancePermission();
        
        $stmt = $this->db->prepare("SELECT * FROM chart_of_accounts WHERE id = ?");
        $stmt->execute([$id]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) redirect('/admin/financial/chart-accounts');
        
        $hasCongCol = $this->tableHasColumn($this->db, 'account_sets', 'congregation_id');
        $cid = $_SESSION['user_congregation_id'] ?? null;
        if ($hasCongCol) {
            if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                $stmtSets = $this->db->prepare("SELECT id, name, is_default, congregation_id FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC");
                $stmtSets->execute();
                $sets = $stmtSets->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmtSets = $this->db->prepare("SELECT id, name, is_default, congregation_id FROM account_sets WHERE active = 1 AND (congregation_id IS NULL OR congregation_id = ?) ORDER BY is_default DESC, name ASC");
                $stmtSets->execute([$cid]);
                $sets = $stmtSets->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            $stmtSets = $this->db->prepare("SELECT id, name, is_default FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC");
            $stmtSets->execute();
            $sets = $stmtSets->fetchAll(PDO::FETCH_ASSOC);
        }
        $stmt = $this->db->prepare("SELECT id, code, name FROM chart_of_accounts WHERE id != ? AND account_set_id = ? ORDER BY code ASC");
        $stmt->execute([$id, $account['account_set_id']]);
        $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $natures = $this->getNatureOptions($account['nature_id'] ?? null);
        
        view('admin/financial/chart_accounts/edit', ['account' => $account, 'parents' => $parents, 'sets' => $sets, 'natures' => $natures, 'hasNatureFeature' => $this->hasNatureFeature()]);
    }

    public function update($id) {
        $this->requireFinancePermission();
        verify_csrf();

        $code = $_POST['code'] ?? '';
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? 'expense';
        $natureSelection = $this->resolveNatureSelection($_POST['nature_id'] ?? null, $type);
        if (isset($_POST['nature_id']) && $_POST['nature_id'] !== '' && !$natureSelection) {
            $_SESSION['flash_error'] = "A natureza selecionada não é válida.";
            redirect('/admin/financial/chart-accounts/edit/'.$id);
            return;
        }
        $nature_id = $natureSelection['nature_id'] ?? null;
        $type = $natureSelection['type'] ?? $type;
        $structure = $_POST['structure'] ?? 'analytic';
        $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        if ($structure === 'synthetic') {
            $parent_id = null;
        }
        $description = $_POST['description'] ?? null;
        $status = $_POST['status'] ?? 'active';
        $account_set_id = !empty($_POST['account_set_id']) ? (int)$_POST['account_set_id'] : null;
        if (!$account_set_id) {
            $stmtCur = $this->db->prepare("SELECT account_set_id FROM chart_of_accounts WHERE id = ?");
            $stmtCur->execute([$id]);
            $account_set_id = (int)$stmtCur->fetchColumn();
        }
        if ($parent_id) {
            $chk = $this->db->prepare("SELECT id FROM chart_of_accounts WHERE id = ? AND account_set_id = ?");
            $chk->execute([$parent_id, $account_set_id]);
            if (!$chk->fetch()) {
                $_SESSION['flash_error'] = "A conta pai selecionada pertence a outro Plano de Contas.";
                redirect('/admin/financial/chart-accounts/edit/'.$id);
                return;
            }
        }
        if ($structure === 'analytic' && empty($parent_id)) {
            $_SESSION['flash_error'] = "Para marcar como Analítica (Filho), selecione uma Conta Pai.";
            redirect('/admin/financial/chart-accounts/edit/'.$id);
            return;
        }
        // Verificar duplicidade de código dentro do conjunto (excluindo a própria conta)
        $dup = $this->db->prepare("SELECT id FROM chart_of_accounts WHERE account_set_id = ? AND code = ? AND id <> ?");
        $dup->execute([$account_set_id, $code, $id]);
        if ($dup->fetch()) {
            $_SESSION['flash_error'] = "Já existe uma conta com o código \"$code\" neste Plano de Contas.";
            redirect('/admin/financial/chart-accounts/edit/'.$id);
            return;
        }

        if ($this->hasNatureFeature()) {
            $stmt = $this->db->prepare("UPDATE chart_of_accounts SET code = ?, name = ?, type = ?, nature_id = ?, parent_id = ?, description = ?, status = ?, account_set_id = ? WHERE id = ?");
            $stmt->execute([$code, $name, $type, $nature_id, $parent_id, $description, $status, $account_set_id, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE chart_of_accounts SET code = ?, name = ?, type = ?, parent_id = ?, description = ?, status = ?, account_set_id = ? WHERE id = ?");
            $stmt->execute([$code, $name, $type, $parent_id, $description, $status, $account_set_id, $id]);
        }

        $_SESSION['flash_success'] = "Conta contábil atualizada com sucesso!";
        redirect('/admin/financial/chart-accounts');
    }
    
    public function delete($id) {
        $this->requireFinancePermission();
        
        $this->db->beginTransaction();
        try {
            $this->deleteCascade($id);
            $this->db->commit();
            $_SESSION['flash_success'] = "Conta(s) contábil(is) excluída(s) com sucesso!";
        } catch (Exception $e) {
            $this->db->rollBack();
        }
        redirect('/admin/financial/chart-accounts');
    }
    
    private function deleteCascade($id) {
        $stmt = $this->db->prepare("SELECT id FROM chart_of_accounts WHERE parent_id = ?");
        $stmt->execute([$id]);
        $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($children as $childId) {
            $this->deleteCascade($childId);
        }
        $del = $this->db->prepare("DELETE FROM chart_of_accounts WHERE id = ?");
        $del->execute([$id]);
    }
    
    public function import() {
        $this->requireFinancePermission();
        $sets = $this->db->query("SELECT id, name, is_default FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $selectedSet = null;
        foreach ($sets as $s) { if ((int)$s['is_default'] === 1) { $selectedSet = (int)$s['id']; break; } }
        if (!$selectedSet && !empty($sets)) $selectedSet = (int)$sets[0]['id'];
        view('admin/financial/chart_accounts/import', ['sets' => $sets, 'selectedSet' => $selectedSet, 'step' => 'upload']);
    }
    
    public function template() {
        $this->requireFinancePermission();
        $filename = "modelo_plano_contas.csv";
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $out = fopen("php://output", "w");
        // Cabeçalhos
        fputcsv($out, [
            'code',
            'name',
            'type',           // asset|liability|income|expense (ou Ativo|Passivo|Receita|Despesa)
            'parent_code',    // código da conta pai (opcional para Analítica)
            'structure',      // synthetic|analytic (ou Sintética|Analítica)
            'opening_balance',// saldo de implantação (opcional)
            'opening_date',   // data do saldo (YYYY-MM-DD) (opcional)
            'status'          // active|inactive
        ]);
        // Exemplos
        fputcsv($out, ['1', 'ATIVO', 'asset', '', 'synthetic', '', '', 'active']);
        fputcsv($out, ['1.1', 'ATIVO CIRCULANTE', 'asset', '1', 'synthetic', '', '', 'active']);
        fputcsv($out, ['1.1.1', 'CAIXA', 'asset', '1.1', 'analytic', '1500,00', '2026-01-01', 'active']);
        fputcsv($out, ['1.1.2', 'BANCOS', 'asset', '1.1', 'analytic', '3250,75', '2026-01-01', 'active']);
        fputcsv($out, ['2', 'PASSIVO', 'liability', '', 'synthetic', '', '', 'active']);
        fputcsv($out, ['3', 'RECEITAS', 'income', '', 'synthetic', '', '', 'active']);
        fputcsv($out, ['3.1', 'Dízimos', 'income', '3', 'analytic', '', '', 'active']);
        fputcsv($out, ['4', 'DESPESAS', 'expense', '', 'synthetic', '', '', 'active']);
        fputcsv($out, ['4.1', 'Despesas com Pessoal', 'expense', '4', 'analytic', '', '', 'active']);
        fclose($out);
        exit;
    }
    
    public function importPreview() {
        $this->requireFinancePermission();
        verify_csrf();
        $account_set_id = (int)($_POST['account_set_id'] ?? 0);
        $delimiter = $_POST['delimiter'] ?? ',';
        if ($delimiter !== ';' && $delimiter !== ',') $delimiter = ',';
        $file = $_FILES['csv'] ?? null;
        if (!$file || $file['error'] !== 0) {
            $_SESSION['flash_error'] = "Arquivo não enviado.";
            redirect('/admin/financial/chart-accounts/import');
            return;
        }
        $uploadDir = __DIR__ . '/../../public/uploads/imports/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = uniqid('coa_') . '.csv';
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $_SESSION['flash_error'] = "Falha ao salvar o arquivo.";
            redirect('/admin/financial/chart-accounts/import');
            return;
        }
        $path = $uploadDir . $filename;
        $fh = fopen($path, 'r');
        if (!$fh) {
            $_SESSION['flash_error'] = "Falha ao ler o arquivo.";
            redirect('/admin/financial/chart-accounts/import');
            return;
        }
        $headers = fgetcsv($fh, 0, $delimiter);
        if (!$headers) {
            fclose($fh);
            $_SESSION['flash_error'] = "Cabeçalho não encontrado.";
            redirect('/admin/financial/chart-accounts/import');
            return;
        }
        $rows = [];
        for ($i=0; $i<10; $i++) {
            $row = fgetcsv($fh, 0, $delimiter);
            if (!$row) break;
            $rows[] = $row;
        }
        fclose($fh);
        $suggest = $this->suggestMapping($headers);
        view('admin/financial/chart_accounts/import', [
            'step' => 'map',
            'headers' => $headers,
            'rows' => $rows,
            'file' => $filename,
            'delimiter' => $delimiter,
            'account_set_id' => $account_set_id,
            'suggest' => $suggest
        ]);
    }
    
    public function importCommit() {
        $this->requireFinancePermission();
        verify_csrf();
        $account_set_id = (int)($_POST['account_set_id'] ?? 0);
        $delimiter = $_POST['delimiter'] ?? ',';
        $file = $_POST['file'] ?? '';
        $uploadDir = __DIR__ . '/../../public/uploads/imports/';
        $path = $uploadDir . $file;
        if (!$account_set_id || !is_file($path)) {
            $_SESSION['flash_error'] = "Requisição inválida.";
            redirect('/admin/financial/chart-accounts/import');
            return;
        }
        $map = [
            'code' => $_POST['map_code'] ?? '',
            'name' => $_POST['map_name'] ?? '',
            'type' => $_POST['map_type'] ?? '',
            'parent_code' => $_POST['map_parent_code'] ?? '',
            'structure' => $_POST['map_structure'] ?? '',
            'opening_balance' => $_POST['map_opening_balance'] ?? '',
            'opening_date' => $_POST['map_opening_date'] ?? '',
            'status' => $_POST['map_status'] ?? ''
        ];
        if ($map['code'] === '' || $map['name'] === '') {
            $_SESSION['flash_error'] = "Mapeie pelo menos Código e Nome.";
            redirect('/admin/financial/chart-accounts/import');
            return;
        }
        $fh = fopen($path, 'r');
        $headers = fgetcsv($fh, 0, $delimiter);
        $idx = [];
        foreach ($map as $k => $colName) {
            $idx[$k] = $this->findHeaderIndex($headers, $colName);
        }
        $inserted = [];
        $this->db->beginTransaction();
        try {
            // Passo 1: inserir todas as contas sem pai
            while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
                $code = $this->getVal($row, $idx['code']);
                $name = $this->getVal($row, $idx['name']);
                if ($code === '' || $name === '') continue;
                $typeVal = $this->normalizeType($this->getVal($row, $idx['type']));
                if ($typeVal === null) $typeVal = 'expense';
                $statusVal = strtolower($this->getVal($row, $idx['status'])) === 'inactive' ? 'inactive' : 'active';
                // pular duplicados por (set, code)
                $chk = $this->db->prepare("SELECT id FROM chart_of_accounts WHERE account_set_id = ? AND code = ?");
                $chk->execute([$account_set_id, $code]);
                $existingId = $chk->fetchColumn();
                if ($existingId) {
                    $inserted[$code] = (int)$existingId;
                } else {
                    $stmt = $this->db->prepare("INSERT INTO chart_of_accounts (code, name, type, parent_id, description, status, account_set_id) VALUES (?, ?, ?, NULL, NULL, ?, ?)");
                    $stmt->execute([$code, $name, $typeVal, $statusVal, $account_set_id]);
                    $inserted[$code] = (int)$this->db->lastInsertId();
                }
            }
            // Passo 2: atribuir pais (por parent_code, estrutura, ou derivação por código)
            fseek($fh, 0);
            fgetcsv($fh, 0, $delimiter); // skip headers
            while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
                $code = $this->getVal($row, $idx['code']);
                if ($code === '') continue;
                $structureVal = $this->normalizeStructure($this->getVal($row, $idx['structure']));
                $parentCode = $this->getVal($row, $idx['parent_code']);
                if ($structureVal === 'synthetic') {
                    if (isset($inserted[$code])) {
                        $upd = $this->db->prepare("UPDATE chart_of_accounts SET parent_id = NULL WHERE id = ?");
                        $upd->execute([$inserted[$code]]);
                    }
                    continue;
                }
                $parentId = null;
                if ($parentCode !== '' && isset($inserted[$parentCode])) {
                    $parentId = $inserted[$parentCode];
                }
                if ($parentId === null) {
                    $derived = $this->deriveParentCode($code);
                    if ($derived !== null && isset($inserted[$derived])) {
                        $parentId = $inserted[$derived];
                    }
                }
                if ($parentId !== null && isset($inserted[$code])) {
                    $upd = $this->db->prepare("UPDATE chart_of_accounts SET parent_id = ? WHERE id = ?");
                    $upd->execute([$parentId, $inserted[$code]]);
                }
            }
            fclose($fh);
            // Passo 3: saldos de implantação (opcional)
            if (!empty($idx['opening_balance'])) {
                $fh2 = fopen($path, 'r');
                fgetcsv($fh2, 0, $delimiter);
                while (($row = fgetcsv($fh2, 0, $delimiter)) !== false) {
                    $code = $this->getVal($row, $idx['code']);
                    $bal = $this->parseMoney($this->getVal($row, $idx['opening_balance']));
                    $date = $this->parseDate($this->getVal($row, $idx['opening_date']));
                    if ($code !== '' && isset($inserted[$code]) && $bal !== null) {
                        $stmtB = $this->db->prepare("INSERT INTO account_opening_balances (account_set_id, account_id, balance, balance_date) VALUES (?, ?, ?, ?)");
                        $stmtB->execute([$account_set_id, $inserted[$code], $bal, $date ?? date('Y-m-d')]);
                    }
                }
                fclose($fh2);
            }
            $this->db->commit();
            $_SESSION['flash_success'] = "Importação concluída. Contas criadas: " . count($inserted);
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['flash_error'] = "Falha na importação: " . $e->getMessage();
        }
        redirect('/admin/financial/chart-accounts?set='.$account_set_id);
    }
    
    private function suggestMapping($headers) {
        $map = [];
        $map['code'] = $this->findHeader($headers, ['code','código','conta','account','account_code']);
        $map['name'] = $this->findHeader($headers, ['name','nome','account_name','descrição','descricao']);
        $map['type'] = $this->findHeader($headers, ['type','nature','natureza','grupo']);
        $map['parent_code'] = $this->findHeader($headers, ['parent','pai','parent_code','conta_pai','codigo_pai']);
        $map['structure'] = $this->findHeader($headers, ['structure','estrutura','synthetic','analytic']);
        $map['opening_balance'] = $this->findHeader($headers, ['opening_balance','saldo_implantacao','saldo','saldo_inicial']);
        $map['opening_date'] = $this->findHeader($headers, ['opening_date','data','data_saldo']);
        $map['status'] = $this->findHeader($headers, ['status','ativo','situacao']);
        return $map;
    }
    private function findHeader($headers, $candidates) {
        foreach ($headers as $h) {
            $hn = strtolower(trim($h));
            foreach ($candidates as $c) {
                if ($hn === strtolower($c)) return $h;
            }
        }
        return '';
    }
    private function findHeaderIndex($headers, $name) {
        foreach ($headers as $i=>$h) {
            if (strtolower(trim($h)) === strtolower(trim($name))) return $i;
        }
        return -1;
    }
    private function getVal($row, $idx) {
        if ($idx < 0) return '';
        return isset($row[$idx]) ? trim($row[$idx]) : '';
    }
    private function normalizeType($val) {
        $v = strtolower(trim($val));
        if ($v === '') return null;
        $map = [
            'ativo' => 'asset',
            'asset' => 'asset',
            'passivo' => 'liability',
            'liability' => 'liability',
            'receita' => 'income',
            'income' => 'income',
            'despesa' => 'expense',
            'expense' => 'expense',
        ];
        return $map[$v] ?? null;
    }
    private function normalizeStructure($val) {
        $v = strtolower(trim($val));
        if ($v === '') return null;
        $map = [
            'sintética' => 'synthetic',
            'sintetica' => 'synthetic',
            'synthetic' => 'synthetic',
            'pai' => 'synthetic',
            'analítica' => 'analytic',
            'analitica' => 'analytic',
            'analytic' => 'analytic',
            'filho' => 'analytic',
        ];
        return $map[$v] ?? null;
    }
    private function deriveParentCode($code) {
        $code = trim($code);
        if (strpos($code, '.') === false) return null;
        $parts = explode('.', $code);
        array_pop($parts);
        $parent = implode('.', $parts);
        return $parent !== '' ? $parent : null;
    }
    private function parseMoney($val) {
        if ($val === '' || $val === null) return null;
        $v = str_replace(['.', ' '], '', $val);
        $v = str_replace(',', '.', $v);
        if (!is_numeric($v)) return null;
        return (float)$v;
    }
    private function parseDate($val) {
        if (!$val) return null;
        $ts = strtotime($val);
        if ($ts === false) return null;
        return date('Y-m-d', $ts);
    }
    
    public function apiList() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        $type = $_GET['type'] ?? '';
        $congregation_id = isset($_GET['congregation_id']) ? (int)$_GET['congregation_id'] : null;
        $set_id = isset($_GET['set_id']) ? (int)$_GET['set_id'] : null;
        if (!in_array($type, ['income', 'expense'])) {
            $type = 'income';
        }
        header('Content-Type: application/json');
        try {
            $hasCongCol = $this->tableHasColumn($this->db, 'account_sets', 'congregation_id');
            $setId = $set_id ?: null;
            if (!$setId) {
                if ($hasCongCol && $congregation_id) {
                    $stmt = $this->db->prepare("SELECT id FROM account_sets WHERE active = 1 AND congregation_id = ? AND is_default = 1 LIMIT 1");
                    $stmt->execute([$congregation_id]);
                    $setId = (int)$stmt->fetchColumn();
                }
            }
            if (!$setId) {
                if ($hasCongCol) {
                    $stmt = $this->db->query("SELECT id FROM account_sets WHERE active = 1 AND congregation_id IS NULL AND is_default = 1 LIMIT 1");
                    $setId = (int)$stmt->fetchColumn();
                }
                if (!$setId) {
                    $stmt = $this->db->query("SELECT id FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC LIMIT 1");
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $setId = $row ? (int)$row['id'] : null;
                }
            }
            if ($this->tableHasColumn($this->db, 'chart_of_accounts', 'account_set_id') && $setId) {
                $stmtCA = $this->db->prepare("SELECT id, code, name FROM chart_of_accounts WHERE account_set_id = ? AND type = ? AND status = 'active' ORDER BY code ASC");
                $stmtCA->execute([$setId, $type]);
            } else {
                $stmtCA = $this->db->prepare("SELECT id, code, name FROM chart_of_accounts WHERE type = ? AND status = 'active' ORDER BY code ASC");
                $stmtCA->execute([$type]);
            }
            $list = $stmtCA->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['accounts' => $list]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
    
    public function apiSets() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        header('Content-Type: application/json');
        try {
            $congregation_id = isset($_GET['congregation_id']) ? (int)$_GET['congregation_id'] : null;
            $hasCongCol = $this->tableHasColumn($this->db, 'account_sets', 'congregation_id');
            if ($hasCongCol) {
                if ($congregation_id) {
                    $stmt = $this->db->prepare("SELECT id, name, is_default FROM account_sets WHERE active = 1 AND (congregation_id IS NULL OR congregation_id = ?) ORDER BY is_default DESC, name ASC");
                    $stmt->execute([$congregation_id]);
                } else {
                    $stmt = $this->db->prepare("SELECT id, name, is_default FROM account_sets WHERE active = 1 AND congregation_id IS NULL ORDER BY is_default DESC, name ASC");
                    $stmt->execute();
                }
            } else {
                $stmt = $this->db->prepare("SELECT id, name, is_default FROM account_sets WHERE active = 1 ORDER BY is_default DESC, name ASC");
                $stmt->execute();
            }
            $sets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sets' => $sets]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
}

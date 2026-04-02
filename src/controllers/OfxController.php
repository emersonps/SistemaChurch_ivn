<?php
require_once __DIR__ . '/../models/Role.php';

class OfxController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    private function requireFinancePermission() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }
        if (!hasPermission('financial_ofx.manage') && !hasPermission('admin.manage')) {
            redirect('/admin/dashboard');
        }
    }

    public function index() {
        $this->requireFinancePermission();
        
        $stmt = $this->db->query("
            SELECT o.*, b.name as bank_name 
            FROM ofx_imports o 
            JOIN bank_accounts b ON o.bank_account_id = b.id 
            ORDER BY o.import_date DESC
        ");
        $imports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->query("SELECT * FROM bank_accounts WHERE status = 'active' ORDER BY name ASC");
        $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('admin/financial/ofx/index', ['imports' => $imports, 'banks' => $banks]);
    }

    public function import() {
        $this->requireFinancePermission();
        verify_csrf();

        $bank_id = $_POST['bank_account_id'] ?? null;
        if (!$bank_id || empty($_FILES['ofx_file']['tmp_name'])) {
            $_SESSION['error'] = "Selecione a conta e o arquivo OFX.";
            redirect('/admin/financial/ofx');
        }

        $file = $_FILES['ofx_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Erro ao fazer upload do arquivo.";
            redirect('/admin/financial/ofx');
        }

        $content = file_get_contents($file['tmp_name']);
        $transactions = $this->parseOfx($content);

        if (empty($transactions)) {
            $_SESSION['error'] = "Nenhuma transação encontrada no arquivo OFX.";
            redirect('/admin/financial/ofx');
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO ofx_imports (bank_account_id, filename) VALUES (?, ?)");
            $stmt->execute([$bank_id, $file['name']]);
            $import_id = $this->db->lastInsertId();

            $stmtTx = $this->db->prepare("
                INSERT INTO ofx_transactions 
                (ofx_import_id, bank_account_id, transaction_id, transaction_date, amount, description, type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($transactions as $tx) {
                // Check if already exists to prevent duplicate FITID
                $check = $this->db->prepare("SELECT id FROM ofx_transactions WHERE transaction_id = ? AND bank_account_id = ?");
                $check->execute([$tx['fitid'], $bank_id]);
                if (!$check->fetch()) {
                    $type = $tx['amount'] >= 0 ? 'credit' : 'debit';
                    $stmtTx->execute([
                        $import_id, 
                        $bank_id, 
                        $tx['fitid'], 
                        $tx['date'], 
                        $tx['amount'], 
                        $tx['memo'], 
                        $type
                    ]);
                }
            }

            $this->db->commit();
            $_SESSION['flash_success'] = "Arquivo OFX importado com sucesso! Agora realize a conciliação.";
            redirect('/admin/financial/ofx/conciliate/' . $import_id);

        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erro ao importar: " . $e->getMessage();
            redirect('/admin/financial/ofx');
        }
    }

    public function conciliate($id) {
        $this->requireFinancePermission();
        
        $stmt = $this->db->prepare("SELECT * FROM ofx_imports WHERE id = ?");
        $stmt->execute([$id]);
        $import = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$import) redirect('/admin/financial/ofx');

        $stmt = $this->db->prepare("SELECT * FROM ofx_transactions WHERE ofx_import_id = ? ORDER BY transaction_date ASC");
        $stmt->execute([$id]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch charts for matching
        $stmt = $this->db->query("SELECT id, code, name FROM chart_of_accounts WHERE status = 'active' ORDER BY code ASC");
        $charts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Auto-Matching Algorithm ---
        $matches = [];
        
        // 1. Get recent system entries (tithes and expenses) around the OFX dates
        $minDate = null;
        $maxDate = null;
        foreach ($transactions as $tx) {
            if ($minDate === null || $tx['transaction_date'] < $minDate) $minDate = $tx['transaction_date'];
            if ($maxDate === null || $tx['transaction_date'] > $maxDate) $maxDate = $tx['transaction_date'];
        }
        
        if ($minDate && $maxDate) {
            // Expand window by 3 days for matching
            $searchStart = date('Y-m-d', strtotime($minDate . ' -3 days'));
            $searchEnd = date('Y-m-d', strtotime($maxDate . ' +3 days'));

            // Fetch Tithes (Credits) that are NOT linked to OFX yet
            $stmtTithes = $this->db->prepare("
                SELECT id, amount, payment_date as date, 'credit' as type, CONCAT('Dízimo/Oferta: ', IFNULL(notes, '')) as description 
                FROM tithes 
                WHERE payment_date BETWEEN ? AND ? 
                AND bank_account_id = ?
                AND id NOT IN (SELECT related_tithe_id FROM ofx_transactions WHERE related_tithe_id IS NOT NULL)
            ");
            $stmtTithes->execute([$searchStart, $searchEnd, $import['bank_account_id']]);
            $systemTithes = $stmtTithes->fetchAll(PDO::FETCH_ASSOC);

            // Fetch Expenses (Debits) that are NOT linked to OFX yet
            $stmtExpenses = $this->db->prepare("
                SELECT id, amount, expense_date as date, 'debit' as type, description 
                FROM expenses 
                WHERE expense_date BETWEEN ? AND ? 
                AND bank_account_id = ?
                AND id NOT IN (SELECT related_expense_id FROM ofx_transactions WHERE related_expense_id IS NOT NULL)
            ");
            $stmtExpenses->execute([$searchStart, $searchEnd, $import['bank_account_id']]);
            $systemExpenses = $stmtExpenses->fetchAll(PDO::FETCH_ASSOC);

            $systemEntries = array_merge($systemTithes, $systemExpenses);

            // Run matching
            foreach ($transactions as &$tx) {
                if ($tx['status'] !== 'pending') continue;
                
                $txDate = strtotime($tx['transaction_date']);
                $txAmount = abs($tx['amount']);
                
                foreach ($systemEntries as $sysKey => $sys) {
                    $sysDate = strtotime($sys['date']);
                    $sysAmount = abs($sys['amount']);
                    
                    // Match rules: Same type, exact same amount, date within 2 days
                    if ($tx['type'] === $sys['type'] && 
                        abs($txAmount - $sysAmount) < 0.01 && 
                        abs($txDate - $sysDate) <= (2 * 86400)) {
                        
                        $matches[$tx['id']] = [
                            'system_id' => $sys['id'],
                            'type' => $sys['type'],
                            'description' => $sys['description'],
                            'date' => $sys['date'],
                            'amount' => $sys['amount']
                        ];
                        
                        // Remove from pool to prevent double matching
                        unset($systemEntries[$sysKey]);
                        break;
                    }
                }
            }
        }

        view('admin/financial/ofx/conciliate', [
            'import' => $import, 
            'transactions' => $transactions,
            'charts' => $charts,
            'matches' => $matches
        ]);
    }

    public function saveConciliation($id) {
        $this->requireFinancePermission();
        verify_csrf();

        $actions = $_POST['action'] ?? [];
        $charts = $_POST['chart_id'] ?? [];
        
        $stmtTx = $this->db->prepare("SELECT * FROM ofx_transactions WHERE id = ? AND ofx_import_id = ?");
        $stmtUpdateTx = $this->db->prepare("UPDATE ofx_transactions SET status = 'conciliated', related_tithe_id = ?, related_expense_id = ? WHERE id = ?");
        $stmtIgnoreTx = $this->db->prepare("UPDATE ofx_transactions SET status = 'ignored' WHERE id = ?");

        $stmtInsertTithe = $this->db->prepare("INSERT INTO tithes (amount, payment_date, payment_method, notes, bank_account_id, chart_account_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtInsertExpense = $this->db->prepare("INSERT INTO expenses (description, amount, expense_date, category, notes, bank_account_id, chart_account_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmtUpdateBankBalance = $this->db->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE id = ?");

        $sysIds = $_POST['system_id'] ?? [];

        try {
            $this->db->beginTransaction();

            foreach ($actions as $tx_id => $action) {
                if ($action === 'ignore') {
                    $stmtIgnoreTx->execute([$tx_id]);
                    continue;
                }
                if ($action === 'add' || $action === 'link') {
                    $stmtTx->execute([$tx_id, $id]);
                    $tx = $stmtTx->fetch(PDO::FETCH_ASSOC);
                    if ($tx && $tx['status'] == 'pending') {
                        $chart_id = !empty($charts[$tx_id]) ? $charts[$tx_id] : null;
                        
                        if ($action === 'link' && !empty($sysIds[$tx_id])) {
                            // Link to existing system record
                            $sysId = $sysIds[$tx_id];
                            if ($tx['type'] == 'credit') {
                                $stmtUpdateTx->execute([$sysId, null, $tx_id]);
                            } else {
                                $stmtUpdateTx->execute([null, $sysId, $tx_id]);
                            }
                            // Note: We don't update bank balance here because it's assumed the manual entry already updated it, 
                            // or it will be handled by a global recount feature.
                        } else {
                            // Add as new
                            if ($tx['type'] == 'credit') {
                                $stmtInsertTithe->execute([
                                    $tx['amount'], $tx['transaction_date'], 'Transferência/OFX', $tx['description'], $tx['bank_account_id'], $chart_id
                                ]);
                                $new_id = $this->db->lastInsertId();
                                $stmtUpdateTx->execute([$new_id, null, $tx_id]);
                                
                                // Update bank balance (+)
                                $stmtUpdateBankBalance->execute([abs($tx['amount']), $tx['bank_account_id']]);
                                
                            } else {
                                $stmtInsertExpense->execute([
                                    $tx['description'], abs($tx['amount']), $tx['transaction_date'], 'OFX', 'Importado via OFX', $tx['bank_account_id'], $chart_id
                                ]);
                                $new_id = $this->db->lastInsertId();
                                $stmtUpdateTx->execute([null, $new_id, $tx_id]);
                                
                                // Update bank balance (-)
                                $stmtUpdateBankBalance->execute([-abs($tx['amount']), $tx['bank_account_id']]);
                            }
                        }
                    }
                }
            }

            // Check if all are processed
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM ofx_transactions WHERE ofx_import_id = ? AND status = 'pending'");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() == 0) {
                $this->db->prepare("UPDATE ofx_imports SET status = 'completed' WHERE id = ?")->execute([$id]);
            }

            $this->db->commit();
            $_SESSION['flash_success'] = "Conciliação salva com sucesso!";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erro na conciliação: " . $e->getMessage();
        }

        redirect('/admin/financial/ofx/conciliate/' . $id);
    }

    private function parseOfx($content) {
        $transactions = [];
        
        // Very basic OFX regex parser
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/is', $content, $matches);
        if (empty($matches[0])) {
            // Some OFX files don't have closing tags for inner elements, but usually STMTTRN is closed.
            // If not, let's try to split by <STMTTRN>
            $parts = explode('<STMTTRN>', $content);
            array_shift($parts); // remove header
            $matches[1] = $parts;
        }

        foreach ($matches[1] as $block) {
            $tx = [];
            
            if (preg_match('/<TRNTYPE>([^<]+)/', $block, $m)) $tx['type'] = trim($m[1]);
            if (preg_match('/<DTPOSTED>([0-9]{8})/', $block, $m)) {
                $d = $m[1];
                $tx['date'] = substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2);
            }
            if (preg_match('/<TRNAMT>([^<]+)/', $block, $m)) $tx['amount'] = (float) trim($m[1]);
            if (preg_match('/<FITID>([^<]+)/', $block, $m)) $tx['fitid'] = trim($m[1]);
            if (preg_match('/<MEMO>([^<]+)/', $block, $m)) {
                $tx['memo'] = trim($m[1]);
            } else {
                if (preg_match('/<NAME>([^<]+)/', $block, $m)) $tx['memo'] = trim($m[1]);
                else $tx['memo'] = 'Transação';
            }

            if (isset($tx['amount']) && isset($tx['fitid']) && isset($tx['date'])) {
                $transactions[] = $tx;
            }
        }
        return $transactions;
    }
}

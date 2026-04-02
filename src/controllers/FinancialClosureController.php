<?php
// src/controllers/FinancialClosureController.php

class FinancialClosureController {
    private function tableHasColumn(PDO $db, $table, $column) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        }

        $stmt = $db->query("PRAGMA table_info($table)");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            if (($col['name'] ?? '') === $column) {
                return true;
            }
        }
        return false;
    }
    
    public function index() {
        requirePermission('financial.view');
        $db = (new Database())->connect();
        
        $sql = "SELECT fc.*, c.name as congregation_name, u.username as creator_name 
                FROM financial_closures fc 
                LEFT JOIN congregations c ON fc.congregation_id = c.id 
                LEFT JOIN users u ON fc.created_by = u.id
                WHERE 1=1";
        $params = [];

        // Scope
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " AND fc.congregation_id = ?";
            $params[] = $_SESSION['user_congregation_id'];
        }

        $sql .= " ORDER BY fc.period DESC, fc.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $closures = $stmt->fetchAll();

        // Congregations for create modal/filter
        $sql_cong = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql_cong .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql_cong .= " ORDER BY name ASC";
        $congregations = $db->query($sql_cong)->fetchAll();

        view('admin/financial/closures', ['closures' => $closures, 'congregations' => $congregations]);
    }

    public function create() {
        requirePermission('financial.manage');
        // This is mainly for the POST logic, but if we need a separate page, we can add it.
        // For now, let's assume index handles the modal or we redirect here.
    }

    public function store() {
        requirePermission('financial.manage');
        $db = (new Database())->connect();
        
        $congregation_id = $_POST['congregation_id'];
        $type = $_POST['type']; // Mensal, Anual
        $period = $_POST['period']; // YYYY-MM or YYYY
        
        // Validation
        if (empty($congregation_id) || empty($type) || empty($period)) {
            // Handle error
            redirect('/admin/financial/closures');
        }

        // Determine Start/End Dates
        if ($type == 'Mensal') {
            $start_date = $period . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));
        } else {
            $start_date = $period . '-01-01';
            $end_date = $period . '-12-31';
        }

        // --- Calculate Totals ---
        
        // Entries
        $sqlEntries = "SELECT type, amount FROM tithes WHERE congregation_id = ? AND payment_date BETWEEN ? AND ?";
        if ($this->tableHasColumn($db, 'tithes', 'is_accountable')) {
            $sqlEntries .= " AND is_accountable = 1";
        }
        $stmtEntries = $db->prepare($sqlEntries);
        $stmtEntries->execute([$congregation_id, $start_date, $end_date]);
        $entries = $stmtEntries->fetchAll();
        
        $total_tithes = 0;
        $total_offerings = 0;
        foreach ($entries as $e) {
            if ($e['type'] == 'Dízimo') $total_tithes += $e['amount'];
            else $total_offerings += $e['amount'];
        }
        $total_entries = $total_tithes + $total_offerings;

        // Expenses
        $sqlExpenses = "SELECT amount FROM expenses WHERE congregation_id = ? AND expense_date BETWEEN ? AND ?";
        if ($this->tableHasColumn($db, 'expenses', 'is_accountable')) {
            $sqlExpenses .= " AND is_accountable = 1";
        }
        $stmtExpenses = $db->prepare($sqlExpenses);
        $stmtExpenses->execute([$congregation_id, $start_date, $end_date]);
        $expenses = $stmtExpenses->fetchAll();
        
        $total_expenses = 0;
        foreach ($expenses as $ex) {
            $total_expenses += $ex['amount'];
        }

        $balance = $total_entries - $total_expenses;

        // --- Get Previous Balance ---
        // Find the most recent closure for this congregation BEFORE this period
        $sqlPrev = "SELECT final_balance FROM financial_closures WHERE congregation_id = ? AND start_date < ? ORDER BY start_date DESC LIMIT 1";
        $stmtPrev = $db->prepare($sqlPrev);
        $stmtPrev->execute([$congregation_id, $start_date]);
        $previous_balance = $stmtPrev->fetchColumn() ?: 0;

        $final_balance = $previous_balance + $balance;

        // Check if closure already exists for this period
        $check = $db->prepare("SELECT id FROM financial_closures WHERE congregation_id = ? AND type = ? AND period = ?");
        $check->execute([$congregation_id, $type, $period]);
        if ($check->fetch()) {
            // Error: Already closed
            // You might want to redirect with an error message
            redirect('/admin/financial/closures');
        }

        // Insert
        $sqlInsert = "INSERT INTO financial_closures (
            congregation_id, type, period, start_date, end_date, 
            total_entries, total_tithes, total_offerings, total_expenses, 
            balance, previous_balance, final_balance, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Fechado', ?)";
        
        $stmtInsert = $db->prepare($sqlInsert);
        $stmtInsert->execute([
            $congregation_id, $type, $period, $start_date, $end_date,
            $total_entries, $total_tithes, $total_offerings, $total_expenses,
            $balance, $previous_balance, $final_balance, $_SESSION['user_id']
        ]);

        redirect('/admin/financial/closures');
    }

    public function delete($id) {
        requirePermission('financial.manage');
        // Only allow deleting the MOST RECENT closure to maintain integrity? 
        // For simplicity, just delete for now.
        $db = (new Database())->connect();
        $db->prepare("DELETE FROM financial_closures WHERE id = ?")->execute([$id]);
        redirect('/admin/financial/closures');
    }
    
    public function show($id) {
        requirePermission('financial.view');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT fc.*, c.name as congregation_name, u.username as creator_name 
                              FROM financial_closures fc 
                              LEFT JOIN congregations c ON fc.congregation_id = c.id 
                              LEFT JOIN users u ON fc.created_by = u.id
                              WHERE fc.id = ?");
        $stmt->execute([$id]);
        $closure = $stmt->fetch();
        
        if (!$closure) redirect('/admin/financial/closures');
        
        // Scope check
        if (!empty($_SESSION['user_congregation_id']) && $closure['congregation_id'] != $_SESSION['user_congregation_id']) {
            redirect('/admin/financial/closures');
        }
        
        view('admin/financial/closure_details', ['closure' => $closure]);
    }
}

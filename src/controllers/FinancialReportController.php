<?php
// src/controllers/FinancialReportController.php

class FinancialReportController {
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

    private function normalizeDateYmd($value, $defaultYmd) {
        if ($value === null || $value === '') {
            return $defaultYmd;
        }

        $value = trim((string)$value);
        if ($value === '') {
            return $defaultYmd;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            $dt = DateTimeImmutable::createFromFormat($format, $value);
            if ($dt && $dt->format($format) === $value) {
                return $dt->format('Y-m-d');
            }
        }

        $ts = strtotime($value);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return $defaultYmd;
    }

    private function resolveDateRangeYmd() {
        $defaultStart = date('Y-m-01');
        $defaultEnd = date('Y-m-t');

        $startYmd = $this->normalizeDateYmd($_GET['start_date'] ?? null, $defaultStart);
        $endYmd = $this->normalizeDateYmd($_GET['end_date'] ?? null, $defaultEnd);

        $start = new DateTimeImmutable($startYmd);
        $end = new DateTimeImmutable($endYmd);

        if ($end < $start) {
            $startYear = (int)$start->format('Y');
            $endYear = (int)$end->format('Y');
            $startMd = (int)$start->format('md');
            $endMd = (int)$end->format('md');

            if ($startYear === $endYear && $endMd < $startMd) {
                $end = $end->modify('+1 year');
            } else {
                $tmp = $start;
                $start = $end;
                $end = $tmp;
            }
        }

        return [$start->format('Y-m-d'), $end->format('Y-m-d')];
    }
    
    public function index() {
        requirePermission('financial.view');
        $db = (new Database())->connect();
        $hasAccountableField = $this->tableHasColumn($db, 'tithes', 'is_accountable');
        $hasExpenseAccountableField = $this->tableHasColumn($db, 'expenses', 'is_accountable');
        
        // Default Filters
        [$start_date, $end_date] = $this->resolveDateRangeYmd();
        $congregation_id = $_GET['congregation_id'] ?? null;
        
        // Scope Check
        if (!empty($_SESSION['user_congregation_id'])) {
            $congregation_id = $_SESSION['user_congregation_id'];
        }

        // --- Fetch Entries (Tithes/Offerings) ---
        $sqlEntries = "SELECT t.*, m.name as member_name, c.name as congregation_name 
                       FROM tithes t 
                       LEFT JOIN members m ON t.member_id = m.id 
                       LEFT JOIN congregations c ON t.congregation_id = c.id
                       WHERE t.payment_date BETWEEN ? AND ?";
        $paramsEntries = [$start_date, $end_date];
        if ($hasAccountableField) {
            $sqlEntries .= " AND t.is_accountable = 1";
        }
        
        if ($congregation_id) {
            $sqlEntries .= " AND t.congregation_id = ?";
            $paramsEntries[] = $congregation_id;
        }
        $sqlEntries .= " ORDER BY t.payment_date ASC";
        $entries = $db->prepare($sqlEntries);
        $entries->execute($paramsEntries);
        $entries = $entries->fetchAll();

        // --- Fetch Expenses ---
        $sqlExpenses = "SELECT e.*, c.name as congregation_name 
                        FROM expenses e 
                        LEFT JOIN congregations c ON e.congregation_id = c.id 
                        WHERE e.expense_date BETWEEN ? AND ?";
        $paramsExpenses = [$start_date, $end_date];
        if ($hasExpenseAccountableField) {
            $sqlExpenses .= " AND e.is_accountable = 1";
        }
        
        if ($congregation_id) {
            $sqlExpenses .= " AND e.congregation_id = ?";
            $paramsExpenses[] = $congregation_id;
        }
        $sqlExpenses .= " ORDER BY e.expense_date ASC";
        $expenses = $db->prepare($sqlExpenses);
        $expenses->execute($paramsExpenses);
        $expenses = $expenses->fetchAll();

        // --- Calculate Totals ---
        $total_entries = 0;
        $total_tithes = 0;
        $total_offerings = 0;
        foreach ($entries as $e) {
            $total_entries += $e['amount'];
            if ($e['type'] == 'Dízimo') $total_tithes += $e['amount'];
            else $total_offerings += $e['amount'];
        }

        $total_expenses = 0;
        $expenses_by_category = [];
        foreach ($expenses as $ex) {
            $total_expenses += $ex['amount'];
            if (!isset($expenses_by_category[$ex['category']])) {
                $expenses_by_category[$ex['category']] = 0;
            }
            $expenses_by_category[$ex['category']] += $ex['amount'];
        }

        $balance = $total_entries - $total_expenses;

        // --- Congregations List for Filter ---
        $congregations = [];
        if (empty($_SESSION['user_congregation_id'])) {
            $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        } else {
            $congregations = $db->query("SELECT * FROM congregations WHERE id = " . $_SESSION['user_congregation_id'])->fetchAll();
        }

        view('admin/financial/report', [
            'entries' => $entries,
            'expenses' => $expenses,
            'total_entries' => $total_entries,
            'total_tithes' => $total_tithes,
            'total_offerings' => $total_offerings,
            'total_expenses' => $total_expenses,
            'expenses_by_category' => $expenses_by_category,
            'balance' => $balance,
            'congregations' => $congregations,
            'filters' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'congregation_id' => $congregation_id
            ]
        ]);
    }

    public function export($type) {
        requirePermission('financial.view');
        $db = (new Database())->connect();
        $hasAccountableField = $this->tableHasColumn($db, 'tithes', 'is_accountable');
        $hasExpenseAccountableField = $this->tableHasColumn($db, 'expenses', 'is_accountable');
        
        [$start_date, $end_date] = $this->resolveDateRangeYmd();
        $congregation_id = $_GET['congregation_id'] ?? null;
        
        if (!empty($_SESSION['user_congregation_id'])) {
            $congregation_id = $_SESSION['user_congregation_id'];
        }

        // Fetch Data for Export (Joined format)
        $sql = "
            SELECT 'Receita' as natureza, t.payment_date as data, t.amount as valor, 
                   IFNULL(ca.code, '3') as conta_contabil, 
                   CONCAT('Dízimo/Oferta - ', 
                       IFNULL(m.name, 
                           IFNULL(t.giver_name, 
                               CASE 
                                   WHEN t.payment_method = 'Transferência/OFX' AND t.notes IS NOT NULL THEN CONCAT('OFX: ', t.notes)
                                   WHEN t.notes IS NOT NULL THEN CONCAT('Obs: ', t.notes)
                                   ELSE 'Não identificado' 
                               END
                           )
                       )
                   ) as historico
            FROM tithes t
            LEFT JOIN members m ON t.member_id = m.id
            LEFT JOIN chart_of_accounts ca ON t.chart_account_id = ca.id
            WHERE t.payment_date BETWEEN ? AND ?
        ";
        $params = [$start_date, $end_date];
        if ($hasAccountableField) {
            $sql .= " AND t.is_accountable = 1";
        }
        if ($congregation_id) { $sql .= " AND t.congregation_id = ?"; $params[] = $congregation_id; }

        $sql .= " UNION ALL ";

        $sql .= "
            SELECT 'Despesa' as natureza, e.expense_date as data, e.amount as valor, 
                   IFNULL(ca.code, '4') as conta_contabil, 
                   e.description as historico
            FROM expenses e
            LEFT JOIN chart_of_accounts ca ON e.chart_account_id = ca.id
            WHERE e.expense_date BETWEEN ? AND ?
        ";
        $params[] = $start_date;
        $params[] = $end_date;
        if ($hasExpenseAccountableField) {
            $sql .= " AND e.is_accountable = 1";
        }
        if ($congregation_id) { $sql .= " AND e.congregation_id = ?"; $params[] = $congregation_id; }

        $sql .= " ORDER BY data ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "Exportacao_Contabil_" . date('Ymd_Hi');

        if ($type === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            $output = fopen('php://output', 'w');
            
            // BOM for Excel compatibility (UTF-8)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($output, ['Data', 'Conta Contábil', 'Histórico', 'Débito', 'Crédito'], ';');
            
            foreach ($records as $r) {
                $debito = $r['natureza'] == 'Despesa' ? number_format($r['valor'], 2, ',', '') : '0,00';
                $credito = $r['natureza'] == 'Receita' ? number_format($r['valor'], 2, ',', '') : '0,00';
                
                fputcsv($output, [
                    date('d/m/Y', strtotime($r['data'])),
                    $r['conta_contabil'],
                    $r['historico'],
                    $debito,
                    $credito
                ], ';');
            }
            fclose($output);
            exit;
        } 
        elseif ($type === 'excel') {
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename.xls\"");
            
            // Excel needs the meta charset tag to properly render UTF-8 in old .xls format
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Relatorio</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            echo '</head>';
            echo '<body>';
            
            // --- Adicionando Legenda Explicativa ---
            echo '<table border="1" cellpadding="5">';
            echo '<tr><th colspan="2" style="background-color: #e9ecef; text-align: left;">LEGENDA - PLANO DE CONTAS E PARTIDAS DOBRADAS</th></tr>';
            echo '<tr><td><b>1</b></td><td>Ativo (Bens e Direitos)</td></tr>';
            echo '<tr><td><b>2</b></td><td>Passivo (Obrigações/Dívidas)</td></tr>';
            echo '<tr><td><b>3</b></td><td>Receitas (Dízimos e Ofertas - Entram como Crédito)</td></tr>';
            echo '<tr><td><b>4</b></td><td>Despesas (Contas, salários, compras - Entram como Débito)</td></tr>';
            echo '<tr><td colspan="2"></td></tr>'; // Empty row for spacing
            echo '</table>';

            echo '<table border="1" cellpadding="5">';
            echo '<tr style="background-color: #0d6efd; color: white;"><th>Data</th><th>Conta Contábil</th><th>Histórico</th><th>Débito</th><th>Crédito</th></tr>';
            foreach ($records as $r) {
                $debito = $r['natureza'] == 'Despesa' ? number_format($r['valor'], 2, ',', '.') : '0,00';
                $credito = $r['natureza'] == 'Receita' ? number_format($r['valor'], 2, ',', '.') : '0,00';
                echo "<tr>";
                echo "<td>" . date('d/m/Y', strtotime($r['data'])) . "</td>";
                echo "<td>{$r['conta_contabil']}</td>";
                echo "<td>" . htmlspecialchars($r['historico']) . "</td>";
                echo "<td>$debito</td>";
                echo "<td>$credito</td>";
                echo "</tr>";
            }
            echo '</table>';
            echo '</body></html>';
            exit;
        }
    }
}

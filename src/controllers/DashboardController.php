<?php
// src/controllers/DashboardController.php

class DashboardController {
    private function tableHasColumn(PDO $db, $table, $column) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
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
        requirePermission('dashboard.view');
        
        $db = (new Database())->connect();
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        // Scope by Congregation
        $congregation_id = $_SESSION['user_congregation_id'] ?? null;
        $congregation_filter_sql = "";
        $congregation_filter_sql_t = ""; // for table alias t
        $congregation_filter_sql_m = ""; // for table alias m
        $congregation_filter_sql_c = ""; // for table alias c
        
        if ($congregation_id) {
            $congregation_filter_sql = " AND congregation_id = $congregation_id";
            $congregation_filter_sql_t = " AND t.member_id IN (SELECT id FROM members WHERE congregation_id = $congregation_id)";
            // For congregation stats query
            $congregation_filter_sql_c = " WHERE c.id = $congregation_id";
        }

        // Filter Date
        // Default to current month/year if not provided
        $selected_month = $_GET['month'] ?? date('m');
        $selected_year = $_GET['year'] ?? date('Y');
        
        $filter_date = "$selected_year-$selected_month";

        // SQL Compatibility Helpers
        if ($driver === 'sqlite') {
            $date_format_ym = "strftime('%Y-%m', payment_date)"; // YYYY-MM
            $date_format_ym_expense = "strftime('%Y-%m', expense_date)"; // YYYY-MM
            $date_format_m = "strftime('%m', birth_date)"; // MM
            $current_date = "date('now')";
        } else { // mysql
            $date_format_ym = "DATE_FORMAT(payment_date, '%Y-%m')";
            $date_format_ym_expense = "DATE_FORMAT(expense_date, '%Y-%m')";
            $date_format_m = "DATE_FORMAT(birth_date, '%m')";
            $current_date = "CURDATE()";
        }

        // Stats
        $members_sql = "SELECT COUNT(*) FROM members WHERE 1=1";
        if ($congregation_id) $members_sql .= " AND congregation_id = $congregation_id";
        $members_count = $db->query($members_sql)->fetchColumn();
        
        // Financial Stats (Filtered)
        $hasAccountableField = $this->tableHasColumn($db, 'tithes', 'is_accountable');
        $financial_where = "WHERE $date_format_ym = '$filter_date'";
        if ($congregation_id) {
            // Use tithes.congregation_id which is now populated (including anonymous/visitor offerings)
            $financial_where .= " AND congregation_id = $congregation_id";
        }
        if ($hasAccountableField) {
            $financial_where .= " AND is_accountable = 1";
        }
        
        $tithes_sum = $db->query("SELECT SUM(amount) FROM tithes $financial_where AND type = 'Dízimo'")->fetchColumn();
        $offerings_sum = $db->query("SELECT SUM(amount) FROM tithes $financial_where AND type IN ('Oferta', 'Oferta Missionária')")->fetchColumn();
        $total_financial = $tithes_sum + $offerings_sum;

        // Previous month total (for the mobile "vs mês anterior" trend badge) — same
        // filters/scope as $total_financial above, just shifted back one month.
        $prev_period = DateTimeImmutable::createFromFormat('Y-n-j', "$selected_year-$selected_month-1")->modify('-1 month');
        $prev_filter_date = $prev_period->format('Y-m');
        $prev_financial_where = "WHERE $date_format_ym = '$prev_filter_date'";
        if ($congregation_id) {
            $prev_financial_where .= " AND congregation_id = $congregation_id";
        }
        if ($hasAccountableField) {
            $prev_financial_where .= " AND is_accountable = 1";
        }
        $prev_total_financial = (float)$db->query("SELECT SUM(amount) FROM tithes $prev_financial_where AND type IN ('Dízimo', 'Oferta', 'Oferta Missionária')")->fetchColumn();
        $financial_trend_pct = $prev_total_financial > 0
            ? (int)round((($total_financial - $prev_total_financial) / $prev_total_financial) * 100)
            : null;
        $prev_month = $prev_period->format('m');

        // Next Events - Check Permission
        $next_events = [];
        if (hasPermission('events.view')) {
            $next_events = $db->query("SELECT * FROM events WHERE event_date >= $current_date ORDER BY event_date ASC LIMIT 5")->fetchAll();
        }
        
        // Birthdays - Check Permission and Scope
        $birthdays = [];
        if (hasPermission('members.view')) {
            $birthdays_sql = "SELECT m.*, c.name as congregation_name FROM members m LEFT JOIN congregations c ON m.congregation_id = c.id WHERE $date_format_m = '$selected_month'";
            if ($congregation_id) $birthdays_sql .= " AND m.congregation_id = $congregation_id";
            $birthdays = $db->query($birthdays_sql)->fetchAll();
        }

        $today_birthdays = [];
        if (hasPermission('members.view')) {
            $today_month = date('m');
            $today_day = date('d');
            if ($driver === 'sqlite') {
                $date_format_d = "strftime('%d', birth_date)";
            } else {
                $date_format_d = "DATE_FORMAT(birth_date, '%d')";
            }
            $today_birthdays_sql = "SELECT m.*, c.name as congregation_name FROM members m LEFT JOIN congregations c ON m.congregation_id = c.id WHERE $date_format_m = '$today_month' AND $date_format_d = '$today_day'";
            if ($congregation_id) $today_birthdays_sql .= " AND m.congregation_id = $congregation_id";
            $today_birthdays = $db->query($today_birthdays_sql)->fetchAll();
        }

        // Stats by Congregation
        // Note: This query calculates member count (total) and financial sum (filtered by date)
        // Grouping by congregation name.
        
        // Use placeholders for date format in complex query
        if ($driver === 'sqlite') {
             $cong_date_filter = "strftime('%Y-%m', t.payment_date)";
        } else {
             $cong_date_filter = "DATE_FORMAT(t.payment_date, '%Y-%m')";
        }

        $sql = "
            SELECT
                c.id as id,
                c.name as congregation_name,
                (SELECT COUNT(*) FROM members m2 WHERE m2.congregation_id = c.id) as member_count,
                SUM(CASE WHEN t.type = 'Dízimo' AND $cong_date_filter = '$filter_date'" . ($hasAccountableField ? " AND t.is_accountable = 1" : "") . " THEN t.amount ELSE 0 END) as tithe_sum,
                SUM(CASE WHEN t.type IN ('Oferta', 'Oferta Missionária') AND $cong_date_filter = '$filter_date'" . ($hasAccountableField ? " AND t.is_accountable = 1" : "") . " THEN t.amount ELSE 0 END) as offering_sum
            FROM congregations c
            LEFT JOIN tithes t ON t.congregation_id = c.id
            $congregation_filter_sql_c
            GROUP BY c.id, c.name
            ORDER BY c.name ASC
        ";
        
        $congregation_stats = $db->query($sql)->fetchAll();

        // Expenses (Saídas) for the same filtered month/scope, mirroring the tithes_sum query above.
        $expenses_where = "WHERE $date_format_ym_expense = '$filter_date'";
        if ($congregation_id) {
            $expenses_where .= " AND congregation_id = $congregation_id";
        }
        if ($this->tableHasColumn($db, 'expenses', 'is_accountable')) {
            $expenses_where .= " AND is_accountable = 1";
        }
        $expenses_sum = (float)$db->query("SELECT SUM(amount) FROM expenses $expenses_where")->fetchColumn();

        // Health score: income vs. expenses this month (same formula as PortalController::financialHealth()).
        if ($expenses_sum <= 0 && $total_financial <= 0) {
            $health_pct = null;
            $health_tier = 'none';
        } elseif ($expenses_sum <= 0) {
            $health_pct = 100;
            $health_tier = 'positive';
        } else {
            $health_pct = min(100, (int)round(($total_financial / $expenses_sum) * 100));
            $health_tier = $health_pct >= 100 ? 'positive' : ($health_pct >= 80 ? 'stable' : 'attention');
        }

        $congregations_count = count($congregation_stats);

        // EBD students (only when the user can see EBD)
        $ebd_students_count = null;
        if (hasPermission('ebd.view') || hasPermission('ebd.manage')) {
            $ebd_sql = "SELECT COUNT(*) FROM ebd_students s JOIN ebd_classes c ON s.class_id = c.id WHERE s.status = 'active'";
            if ($congregation_id) $ebd_sql .= " AND c.congregation_id = $congregation_id";
            $ebd_students_count = (int)$db->query($ebd_sql)->fetchColumn();
        }

        // Studies (only when the user can see them)
        $studies_count = null;
        if (hasPermission('studies.view')) {
            $studies_sql = "SELECT COUNT(*) FROM studies WHERE 1=1";
            if ($congregation_id) $studies_sql .= " AND (congregation_id = $congregation_id OR congregation_id IS NULL)";
            $studies_count = (int)$db->query($studies_sql)->fetchColumn();
        }

        // Whether the current month is already closed for this congregation (financial_closures period = 'YYYY-MM').
        $closure_pending = null;
        if ($congregation_id && hasPermission('financial.view')) {
            $closure_check = $db->prepare("SELECT id FROM financial_closures WHERE congregation_id = ? AND type = 'Mensal' AND period = ?");
            $closure_check->execute([$congregation_id, date('Y-m')]);
            $closure_pending = !$closure_check->fetch();
        }

        // Lightweight real alerts (no notifications table exists) for the mobile launcher bell.
        $alerts = [];
        if (!empty($today_birthdays)) {
            $alerts[] = [
                'icon' => 'fa-cake-candles',
                'text' => count($today_birthdays) === 1
                    ? '1 aniversariante hoje'
                    : count($today_birthdays) . ' aniversariantes hoje',
                'href' => '/admin?view=aniversariantes',
            ];
        }
        if ($closure_pending === true) {
            $alerts[] = [
                'icon' => 'fa-lock',
                'text' => 'Fechamento mensal pendente',
                'href' => '/admin/financial/closures',
            ];
        }

        view('admin/dashboard', [
            'members_count' => $members_count,
            'tithes_sum' => $tithes_sum ?: 0,
            'offerings_sum' => $offerings_sum ?: 0,
            'total_financial' => $total_financial ?: 0,
            'financial_trend_pct' => $financial_trend_pct,
            'prev_month' => $prev_month,
            'expenses_sum' => $expenses_sum ?: 0,
            'health_pct' => $health_pct,
            'health_tier' => $health_tier,
            'selected_month' => $selected_month,
            'selected_year' => $selected_year,
            'congregation_stats' => $congregation_stats,
            'congregations_count' => $congregations_count,
            'ebd_students_count' => $ebd_students_count,
            'studies_count' => $studies_count,
            'closure_pending' => $closure_pending,
            'alerts' => $alerts,
            'next_events' => $next_events,
            'birthdays' => $birthdays,
            'today_birthdays' => $today_birthdays
        ]);
    }
}

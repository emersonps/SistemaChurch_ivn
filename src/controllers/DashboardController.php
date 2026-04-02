<?php
// src/controllers/DashboardController.php

class DashboardController {
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
            $date_format_m = "strftime('%m', birth_date)"; // MM
            $current_date = "date('now')";
        } else { // mysql
            $date_format_ym = "DATE_FORMAT(payment_date, '%Y-%m')";
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

        // Next Events - Check Permission
        $next_events = [];
        if (hasPermission('events.view')) {
            $next_events = $db->query("SELECT * FROM events WHERE event_date >= $current_date ORDER BY event_date ASC LIMIT 5")->fetchAll();
        }
        
        // Birthdays - Check Permission and Scope
        $birthdays = [];
        if (hasPermission('members.view')) {
            $birthdays_sql = "SELECT * FROM members WHERE $date_format_m = '$selected_month'";
            if ($congregation_id) $birthdays_sql .= " AND congregation_id = $congregation_id";
            $birthdays = $db->query($birthdays_sql)->fetchAll();
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

        view('admin/dashboard', [
            'members_count' => $members_count,
            'tithes_sum' => $tithes_sum ?: 0,
            'offerings_sum' => $offerings_sum ?: 0,
            'total_financial' => $total_financial ?: 0,
            'selected_month' => $selected_month,
            'selected_year' => $selected_year,
            'congregation_stats' => $congregation_stats,
            'next_events' => $next_events,
            'birthdays' => $birthdays
        ]);
    }
}

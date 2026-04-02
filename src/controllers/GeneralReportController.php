<?php
// src/controllers/GeneralReportController.php

class GeneralReportController {
    
    public function index() {
        requirePermission('general_reports.view');
        $db = (new Database())->connect();
        
        // --- Filters ---
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        $congregation_id = $_GET['congregation_id'] ?? null;
        
        // Security: Restrict congregation if user is limited
        if (!empty($_SESSION['user_congregation_id'])) {
            $congregation_id = $_SESSION['user_congregation_id'];
        }

        // --- 1. Service Reports Stats (People Actions) ---
        // Aggregate by Action Type (Visitante, Aceitou Jesus, etc)
        $sqlPeople = "SELECT spa.action_type, COUNT(*) as total, GROUP_CONCAT(spa.name SEPARATOR ', ') as names 
                      FROM service_people_actions spa
                      JOIN service_reports sr ON spa.service_report_id = sr.id
                      WHERE sr.date BETWEEN ? AND ? AND spa.action_type IS NOT NULL AND spa.action_type != ''";
        $paramsPeople = [$start_date, $end_date];
        
        if ($congregation_id) {
            $sqlPeople .= " AND sr.congregation_id = ?";
            $paramsPeople[] = $congregation_id;
        }
        
        $sqlPeople .= " GROUP BY spa.action_type ORDER BY total DESC";
        $peopleStats = $db->prepare($sqlPeople);
        $peopleStats->execute($paramsPeople);
        $peopleStats = $peopleStats->fetchAll();

        // --- 2. Service Attendance Stats ---
        // Aggregate Attendance Numbers
        $sqlAttendance = "SELECT SUM(attendance_men) as total_men,
                                 SUM(attendance_women) as total_women,
                                 SUM(attendance_youth) as total_youth,
                                 SUM(attendance_children) as total_children,
                                 SUM(attendance_visitors) as total_visitors,
                                 COUNT(*) as total_services
                          FROM service_reports sr
                          WHERE sr.date BETWEEN ? AND ?";
        $paramsAttendance = [$start_date, $end_date];
        
        if ($congregation_id) {
            $sqlAttendance .= " AND sr.congregation_id = ?";
            $paramsAttendance[] = $congregation_id;
        }
        
        $attendanceStats = $db->prepare($sqlAttendance);
        $attendanceStats->execute($paramsAttendance);
        $attendanceStats = $attendanceStats->fetch();

        // --- 3. EBD Stats (Classes & Enrollment) ---
        // Just a snapshot of current active classes/students as historical EBD attendance might not be fully tracked yet
        $sqlEbd = "SELECT 
                    (SELECT COUNT(*) FROM ebd_classes) as total_classes,
                    (SELECT COUNT(*) FROM ebd_students WHERE status = 'active') as total_students,
                    (SELECT COUNT(*) FROM ebd_teachers WHERE status = 'active') as total_teachers";
        
        // EBD data is usually global or linked via members, but classes might belong to congregation if structure allows
        // For now, global snapshot or simple count
        $ebdStats = $db->query($sqlEbd)->fetch();
        
        // --- 4. Groups/Cells Stats ---
        // 'groups' é palavra reservada em alguns SQLs, usar backticks ou alias com cuidado
        $sqlGroups = "SELECT COUNT(*) as total_groups, SUM(members_count) as total_members FROM (
                        SELECT g.id, (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id) as members_count
                        FROM `groups` g
                        WHERE 1=1 " . ($congregation_id ? "AND g.congregation_id = $congregation_id" : "") . "
                      ) as sub";
        $groupStats = $db->query($sqlGroups)->fetch();

        // --- Congregations for Filter ---
        $congregations = [];
        if (empty($_SESSION['user_congregation_id'])) {
            $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        } else {
            $congregations = $db->query("SELECT * FROM congregations WHERE id = " . $_SESSION['user_congregation_id'])->fetchAll();
        }

        view('admin/reports/general', [
            'peopleStats' => $peopleStats,
            'attendanceStats' => $attendanceStats,
            'ebdStats' => $ebdStats,
            'groupStats' => $groupStats,
            'congregations' => $congregations,
            'filters' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'congregation_id' => $congregation_id
            ]
        ]);
    }
}

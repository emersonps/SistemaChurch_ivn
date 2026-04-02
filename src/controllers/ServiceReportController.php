<?php
// src/controllers/ServiceReportController.php

class ServiceReportController {
    
    public function getVisitors($id) {
        requirePermission('service_reports.view');
        header('Content-Type: application/json');
        
        $db = (new Database())->connect();
        
        // Security Check: Ensure user can access this report
        if (!empty($_SESSION['user_congregation_id'])) {
            $stmtCheck = $db->prepare("SELECT congregation_id FROM service_reports WHERE id = ?");
            $stmtCheck->execute([$id]);
            $reportCong = $stmtCheck->fetchColumn();
            
            if ($reportCong != $_SESSION['user_congregation_id']) {
                echo json_encode(['error' => 'Acesso negado']);
                exit;
            }
        }
        
        $stmt = $db->prepare("SELECT * FROM service_people_actions WHERE service_report_id = ? AND action_type = 'Visitante'");
        $stmt->execute([$id]);
        $visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure no null values break the JSON encoding
        $safeVisitors = [];
        foreach ($visitors as $v) {
            $safeVisitors[] = [
                'id' => $v['id'],
                'name' => $v['name'] ?? '',
                'observation' => $v['observation'] ?? ''
            ];
        }
        
        echo json_encode($safeVisitors);
        exit;
    }

    public function show($id) {
        requirePermission('service_reports.view');
        $db = (new Database())->connect();
        
        // Fetch Report
        $stmt = $db->prepare("SELECT sr.*, c.name as congregation_name, u.username as creator_name 
                              FROM service_reports sr 
                              LEFT JOIN congregations c ON sr.congregation_id = c.id
                              LEFT JOIN users u ON sr.created_by = u.id
                              WHERE sr.id = ?");
        $stmt->execute([$id]);
        $report = $stmt->fetch();
        
        if (!$report) {
            redirect('/admin/service_reports');
        }
        
        // Check access
        if (!empty($_SESSION['user_congregation_id']) && $report['congregation_id'] != $_SESSION['user_congregation_id']) {
            redirect('/admin/service_reports');
        }
        
        // Fetch Financials
        $stmtFin = $db->prepare("SELECT t.*, m.name as member_name 
                                 FROM tithes t 
                                 LEFT JOIN members m ON t.member_id = m.id 
                                 WHERE t.service_report_id = ?");
        $stmtFin->execute([$id]);
        $financials = $stmtFin->fetchAll();
        
        // Fetch People Actions
        $stmtPeople = $db->prepare("SELECT * FROM service_people_actions WHERE service_report_id = ?");
        $stmtPeople->execute([$id]);
        $allPeople = $stmtPeople->fetchAll();
        
        // Filter Visitors vs Others
        $visitors = array_filter($allPeople, function($p) {
            return $p['action_type'] === 'Visitante';
        });
        
        $otherActions = array_filter($allPeople, function($p) {
            return $p['action_type'] !== 'Visitante';
        });
        
        view('admin/service_reports/show', [
            'report' => $report, 
            'financials' => $financials, 
            'visitors' => $visitors,
            'otherActions' => $otherActions
        ]);
    }
        
    public function index() {
        requirePermission('service_reports.view');
        $db = (new Database())->connect();
        
        $sql = "SELECT sr.*, c.name as congregation_name, u.username as creator_name 
                FROM service_reports sr 
                LEFT JOIN congregations c ON sr.congregation_id = c.id
                LEFT JOIN users u ON sr.created_by = u.id";
        
        $params = [];
        
        // Filter by user's congregation
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE sr.congregation_id = ?";
            $params[] = $_SESSION['user_congregation_id'];
        }
        
        $sql .= " ORDER BY sr.date DESC, sr.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll();
        
        view('admin/service_reports/index', ['reports' => $reports]);
    }

    public function create() {
        requirePermission('service_reports.manage');
        $db = (new Database())->connect();
        
        $sql = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        $congregations = $db->query($sql)->fetchAll();
        
        $membersSql = "SELECT id, name FROM members";
        // Permitir buscar membros de TODAS as congregações para facilitar preenchimento
        // if (!empty($_SESSION['user_congregation_id'])) {
        //    $membersSql .= " WHERE congregation_id = " . $_SESSION['user_congregation_id'];
        // }
        $membersSql .= " ORDER BY name ASC";
        $membersDB = $db->query($membersSql)->fetchAll();
        
        // Fetch Historical Visitors
        $visitorsSql = "SELECT DISTINCT giver_name as name FROM tithes WHERE member_id IS NULL AND giver_name IS NOT NULL AND giver_name != '' ORDER BY giver_name ASC";
        $visitorsDB = $db->query($visitorsSql)->fetchAll();
        
        // Merge Lists
        $members = [];
        foreach ($membersDB as $m) {
            $members[] = ['id' => $m['id'], 'name' => $m['name'], 'type' => 'member'];
        }
        foreach ($visitorsDB as $v) {
            // Check duplicates
            $exists = false;
            foreach ($members as $m) {
                if (strcasecmp($m['name'], $v['name']) === 0) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $members[] = ['id' => null, 'name' => $v['name'], 'type' => 'visitor'];
            }
        }
        // Sort merged list
        usort($members, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        view('admin/service_reports/create', ['congregations' => $congregations, 'members' => $members]);
    }

    public function edit($id) {
        requirePermission('service_reports.manage');
        $db = (new Database())->connect();
        
        // Fetch Report
        $stmt = $db->prepare("SELECT * FROM service_reports WHERE id = ?");
        $stmt->execute([$id]);
        $report = $stmt->fetch();
        
        if (!$report) {
            redirect('/admin/service_reports');
        }

        // Security check for congregation scope
        if (!empty($_SESSION['user_congregation_id']) && $report['congregation_id'] != $_SESSION['user_congregation_id']) {
            redirect('/admin/service_reports');
        }

        // Fetch Related Data
        $stmtTithes = $db->prepare("SELECT t.*, m.name as member_name FROM tithes t LEFT JOIN members m ON t.member_id = m.id WHERE t.service_report_id = ?");
        $stmtTithes->execute([$id]);
        $tithes = $stmtTithes->fetchAll();

        $stmtPeople = $db->prepare("SELECT * FROM service_people_actions WHERE service_report_id = ?");
        $stmtPeople->execute([$id]);
        $people = $stmtPeople->fetchAll();
        
        // Congregations List
        $sql = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        $congregations = $db->query($sql)->fetchAll();
        
        // Members List (All + Visitors)
        $membersDB = $db->query("SELECT id, name FROM members ORDER BY name ASC")->fetchAll();
        $visitorsDB = $db->query("SELECT DISTINCT giver_name as name FROM tithes WHERE member_id IS NULL AND giver_name IS NOT NULL AND giver_name != '' ORDER BY giver_name ASC")->fetchAll();
        
        $members = [];
        foreach ($membersDB as $m) {
            $members[] = ['id' => $m['id'], 'name' => $m['name'], 'type' => 'member'];
        }
        foreach ($visitorsDB as $v) {
            $exists = false;
            foreach ($members as $m) {
                if (strcasecmp($m['name'], $v['name']) === 0) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $members[] = ['id' => null, 'name' => $v['name'], 'type' => 'visitor'];
            }
        }
        usort($members, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        view('admin/service_reports/edit', [
            'report' => $report, 
            'congregations' => $congregations, 
            'members' => $members,
            'tithes' => $tithes,
            'people' => $people
        ]);
    }

    public function update($id) {
        requirePermission('service_reports.manage');
        
        $congregation_id = $_POST['congregation_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $leader_name = $_POST['leader_name'];
        $preacher_name = $_POST['preacher_name'];
        
        $attendance_men = (int)$_POST['attendance_men'];
        $attendance_women = (int)$_POST['attendance_women'];
        $attendance_youth = (int)$_POST['attendance_youth'];
        $attendance_children = (int)$_POST['attendance_children'];
        $attendance_visitors = (int)$_POST['attendance_visitors'];
        $total_attendance = $attendance_men + $attendance_women + $attendance_youth + $attendance_children + $attendance_visitors;
        
        $notes = $_POST['notes'];
        
        $db = (new Database())->connect();
        
        // Security check
        if (!empty($_SESSION['user_congregation_id'])) {
            $stmtCheck = $db->prepare("SELECT congregation_id FROM service_reports WHERE id = ?");
            $stmtCheck->execute([$id]);
            $currentCong = $stmtCheck->fetchColumn();
            if ($currentCong != $_SESSION['user_congregation_id']) {
                die("Acesso negado");
            }
        }
        
        try {
            $db->beginTransaction();
            
            // Update Report
            $stmt = $db->prepare("UPDATE service_reports SET congregation_id=?, date=?, time=?, leader_name=?, preacher_name=?, attendance_men=?, attendance_women=?, attendance_youth=?, attendance_children=?, attendance_visitors=?, total_attendance=?, notes=? WHERE id=?");
            $stmt->execute([$congregation_id, $date, $time, $leader_name, $preacher_name, $attendance_men, $attendance_women, $attendance_youth, $attendance_children, $attendance_visitors, $total_attendance, $notes, $id]);
            
            // Update Financials (Delete all and Re-insert)
            $db->prepare("DELETE FROM tithes WHERE service_report_id = ?")->execute([$id]);
            
            if (isset($_POST['financials']) && is_array($_POST['financials'])) {
                $stmtFinancial = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, service_report_id, giver_name, congregation_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($_POST['financials'] as $fin) {
                    if (!empty($fin['amount']) && (!empty($fin['member_id']) || !empty($fin['giver_name']))) {
                        $memberId = !empty($fin['member_id']) ? $fin['member_id'] : null;
                        $amount = str_replace(['R$', '.', ','], ['', '', '.'], $fin['amount']); 
                        $paymentMethod = 'Dinheiro';
                        $type = $fin['type'];
                        $giverName = $fin['giver_name'];
                        
                        $stmtFinancial->execute([$memberId, $fin['amount'], $date, $paymentMethod, $type, 'Via Relatório de Culto', $id, $giverName, $congregation_id]);
                    }
                }
            }
            
            // Update People Actions (Delete all and Re-insert)
            $db->prepare("DELETE FROM service_people_actions WHERE service_report_id = ?")->execute([$id]);
            
            if (isset($_POST['people']) && is_array($_POST['people'])) {
                $stmtPeople = $db->prepare("INSERT INTO service_people_actions (service_report_id, name, action_type, observation) VALUES (?, ?, ?, ?)");
                
                foreach ($_POST['people'] as $person) {
                    if (!empty($person['name']) && !empty($person['action_type'])) {
                        $stmtPeople->execute([$id, $person['name'], $person['action_type'], $person['observation'] ?? '']);
                    }
                }
            }
            
            $db->commit();
            redirect('/admin/service_reports');
            
        } catch (Exception $e) {
            $db->rollBack();
            echo "Erro ao atualizar relatório: " . $e->getMessage();
            exit;
        }
    }

    public function store() {
        requirePermission('service_reports.manage');
        
        $congregation_id = $_POST['congregation_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $leader_name = $_POST['leader_name'];
        $preacher_name = $_POST['preacher_name'];
        
        $attendance_men = (int)$_POST['attendance_men'];
        $attendance_women = (int)$_POST['attendance_women'];
        $attendance_youth = (int)$_POST['attendance_youth'];
        $attendance_children = (int)$_POST['attendance_children'];
        $attendance_visitors = (int)$_POST['attendance_visitors'];
        $total_attendance = $attendance_men + $attendance_women + $attendance_youth + $attendance_children + $attendance_visitors;
        
        $notes = $_POST['notes'];
        $created_by = $_SESSION['user_id'];

        $db = (new Database())->connect();
        
        try {
            $db->beginTransaction();
            
            // Insert Report
            $stmt = $db->prepare("INSERT INTO service_reports (congregation_id, date, time, leader_name, preacher_name, attendance_men, attendance_women, attendance_youth, attendance_children, attendance_visitors, total_attendance, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$congregation_id, $date, $time, $leader_name, $preacher_name, $attendance_men, $attendance_women, $attendance_youth, $attendance_children, $attendance_visitors, $total_attendance, $notes, $created_by]);
            $reportId = $db->lastInsertId();
            
            // Process Financials (Tithes/Offerings)
            if (isset($_POST['financials']) && is_array($_POST['financials'])) {
                $stmtFinancial = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, service_report_id, giver_name, congregation_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($_POST['financials'] as $fin) {
                    if (!empty($fin['amount']) && (!empty($fin['member_id']) || !empty($fin['giver_name']))) {
                        $memberId = !empty($fin['member_id']) ? $fin['member_id'] : null;
                        $amount = str_replace(['R$', '.', ','], ['', '', '.'], $fin['amount']); // Basic cleanup if needed, but assuming HTML5 number input
                        $paymentMethod = 'Dinheiro'; // Default or add field if needed
                        $type = $fin['type'];
                        $giverName = $fin['giver_name'];
                        
                        $stmtFinancial->execute([$memberId, $fin['amount'], $date, $paymentMethod, $type, 'Via Relatório de Culto', $reportId, $giverName, $congregation_id]);
                    }
                }
            }
            
            // Process People Actions
            if (isset($_POST['people']) && is_array($_POST['people'])) {
                $stmtPeople = $db->prepare("INSERT INTO service_people_actions (service_report_id, name, action_type, observation) VALUES (?, ?, ?, ?)");
                
                foreach ($_POST['people'] as $person) {
                    if (!empty($person['name']) && !empty($person['action_type'])) {
                        $stmtPeople->execute([$reportId, $person['name'], $person['action_type'], $person['observation'] ?? '']);
                    }
                }
            }
            
            $db->commit();
            redirect('/admin/service_reports');
            
        } catch (Exception $e) {
            $db->rollBack();
            // Log error
            echo "Erro ao salvar relatório: " . $e->getMessage();
            exit;
        }
    }

    public function delete($id) {
        requirePermission('service_reports.manage');
        $db = (new Database())->connect();
        
        try {
            $db->beginTransaction();

            // Delete related tithes
            $stmtTithes = $db->prepare("DELETE FROM tithes WHERE service_report_id = ?");
            $stmtTithes->execute([$id]);

            // Delete related people actions
            $stmtPeople = $db->prepare("DELETE FROM service_people_actions WHERE service_report_id = ?");
            $stmtPeople->execute([$id]);

            // Delete the report
            $stmtReport = $db->prepare("DELETE FROM service_reports WHERE id = ?");
            $stmtReport->execute([$id]);

            $db->commit();
            redirect('/admin/service_reports');

        } catch (Exception $e) {
            $db->rollBack();
            echo "Erro ao excluir relatório: " . $e->getMessage();
            exit;
        }
    }
}

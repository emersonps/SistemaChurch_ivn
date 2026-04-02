<?php
// src/controllers/PortalController.php

class PortalController {
    
    private function requireMemberLogin() {
        if (!isset($_SESSION['member_id'])) {
            redirect('/portal/login');
        }
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
        $this->requireMemberLogin();
        $member_id = $_SESSION['member_id'];
        
        $db = (new Database())->connect();
        $current_date_sql = ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') ? "date('now')" : "CURDATE()";
        
        // Member Data
        $member = $db->query("SELECT m.*, c.name as congregation_name FROM members m LEFT JOIN congregations c ON m.congregation_id = c.id WHERE m.id = $member_id")->fetch();
        
        // Last Tithes
        $last_tithes = $db->query("SELECT * FROM tithes WHERE member_id = $member_id ORDER BY payment_date DESC LIMIT 5")->fetchAll();
        
        // Next Events (General or Congregation specific, include internos permitidos)
        $congregation_id = $member['congregation_id'];
        $sql_events = "SELECT * FROM events 
                       WHERE event_date >= $current_date_sql 
                       AND (
                            (type != 'interno' AND (congregation_id IS NULL OR congregation_id = ?))
                            OR (type = 'interno' AND (
                                EXISTS(SELECT 1 FROM event_allowed_members am WHERE am.event_id = events.id AND am.member_id = ?)
                                OR EXISTS(SELECT 1 FROM event_allowed_congregations ac WHERE ac.event_id = events.id AND ac.congregation_id = ?)
                            ))
                       )
                       ORDER BY event_date ASC LIMIT 5";
        $stmt = $db->prepare($sql_events);
        $stmt->execute([$congregation_id, $member_id, $congregation_id]);
        $next_events = $stmt->fetchAll();
        
        // Recent Studies
        $sql_studies = "SELECT * FROM studies 
                        WHERE congregation_id IS NULL OR congregation_id = ? 
                        ORDER BY created_at DESC LIMIT 3";
        $stmt = $db->prepare($sql_studies);
        $stmt->execute([$congregation_id]);
        $recent_studies = $stmt->fetchAll();

        // Congregation leader
        $leaderName = null;
        if (!empty($member['congregation_id'])) {
            $hasLeaderId = $this->tableHasColumn($db, 'congregations', 'leader_member_id');
            if ($hasLeaderId) {
                $stmtLeader = $db->prepare("SELECT COALESCE(m.name, c.leader_name) AS leader_name 
                                            FROM congregations c 
                                            LEFT JOIN members m ON m.id = c.leader_member_id 
                                            WHERE c.id = ?");
                $stmtLeader->execute([$member['congregation_id']]);
                $rowLeader = $stmtLeader->fetch();
                $leaderName = $rowLeader ? $rowLeader['leader_name'] : null;
            } else {
                $stmtLeader = $db->prepare("SELECT leader_name FROM congregations WHERE id = ?");
                $stmtLeader->execute([$member['congregation_id']]);
                $leaderName = $stmtLeader->fetchColumn();
            }
        }
        
        // Obreiros da congregação (roles diferentes de 'Membro')
        $workers = [];
        if (!empty($member['congregation_id'])) {
            $stmtW = $db->prepare("SELECT name, role, photo FROM members WHERE congregation_id = ? AND role IS NOT NULL AND TRIM(role) != '' AND LOWER(role) != 'membro' ORDER BY name ASC");
            $stmtW->execute([$member['congregation_id']]);
            $workers = $stmtW->fetchAll();
        }
        
        view('portal/dashboard', [
            'member' => $member,
            'last_tithes' => $last_tithes,
            'next_events' => $next_events,
            'recent_studies' => $recent_studies,
            'leaderName' => $leaderName,
            'workers' => $workers
        ]);
    }

    public function profile() {
        $this->requireMemberLogin();
        $member_id = $_SESSION['member_id'];
        
        $db = (new Database())->connect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update logic - allowing only safe fields to be updated by user
            // e.g. Phone, Email, Address
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $address = $_POST['address'];
            $address_number = $_POST['address_number'];
            $neighborhood = $_POST['neighborhood'];
            $city = $_POST['city'];
            $state = $_POST['state'];
            $zip_code = $_POST['zip_code'];
            
            // Photo upload
            $photoSql = "";
            $params = [$phone, $email, $address, $address_number, $neighborhood, $city, $state, $zip_code];
            
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/members/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                    $photoSql = ", photo = ?";
                    $params[] = $filename;
                }
            }
            if (isset($_POST['remove_photo'])) {
                $stmtCur = $db->prepare("SELECT photo FROM members WHERE id = ?");
                $stmtCur->execute([$member_id]);
                $cur = $stmtCur->fetch();
                if (!empty($cur['photo'])) {
                    $fs = __DIR__ . '/../../public/uploads/members/' . $cur['photo'];
                    if (file_exists($fs)) {
                        unlink($fs);
                    }
                }
                $photoSql = ", photo = NULL";
            }
            
            $params[] = $member_id;
            
            $stmt = $db->prepare("UPDATE members SET phone=?, email=?, address=?, address_number=?, neighborhood=?, city=?, state=?, zip_code=? $photoSql WHERE id=?");
            $stmt->execute($params);
            
            redirect('/portal/profile?success=1');
        }
        
        $member = $db->query("SELECT m.*, c.name as congregation_name FROM members m LEFT JOIN congregations c ON m.congregation_id = c.id WHERE m.id = $member_id")->fetch();
        
        // Buscar Grupos
        $stmtGroups = $db->prepare("SELECT g.name, g.id, gm.role 
                                    FROM `groups` g 
                                    JOIN group_members gm ON g.id = gm.group_id 
                                    WHERE gm.member_id = ?");
        $stmtGroups->execute([$member_id]);
        $groups = $stmtGroups->fetchAll();
        
        // Buscar EBD (Aluno)
        $stmtEbdStudent = $db->prepare("SELECT c.name, c.id, s.enrolled_at 
                                        FROM ebd_classes c 
                                        JOIN ebd_students s ON c.id = s.class_id 
                                        WHERE s.member_id = ? AND s.status = 'active'");
        $stmtEbdStudent->execute([$member_id]);
        $ebdStudentClasses = $stmtEbdStudent->fetchAll();
        
        // Buscar EBD (Professor)
        $stmtEbdTeacher = $db->prepare("SELECT c.name, c.id 
                                        FROM ebd_classes c 
                                        JOIN ebd_teachers t ON c.id = t.class_id 
                                        WHERE t.member_id = ? AND t.status = 'active'");
        $stmtEbdTeacher->execute([$member_id]);
        $ebdTeacherClasses = $stmtEbdTeacher->fetchAll();

        // Buscar Usuários do Sistema vinculados
        $stmtSysUsers = $db->prepare("SELECT u.username, u.role 
                                      FROM users u 
                                      JOIN user_members um ON u.id = um.user_id 
                                      WHERE um.member_id = ?");
        $stmtSysUsers->execute([$member_id]);
        $systemUsers = $stmtSysUsers->fetchAll();
        
        // Fallback para coluna antiga se não achar na nova
        if (empty($systemUsers)) {
            $stmtSysUsersOld = $db->prepare("SELECT username, role FROM users WHERE member_id = ?");
            $stmtSysUsersOld->execute([$member_id]);
            $systemUsers = $stmtSysUsersOld->fetchAll();
        }
        
        $stmtDocs = $db->prepare("SELECT id, title, type, file_path, created_at FROM member_documents WHERE member_id = ? ORDER BY created_at DESC");
        $stmtDocs->execute([$member_id]);
        $memberDocuments = $stmtDocs->fetchAll();

        view('portal/profile', [
            'member' => $member,
            'groups' => $groups,
            'ebdStudentClasses' => $ebdStudentClasses,
            'ebdTeacherClasses' => $ebdTeacherClasses,
            'systemUsers' => $systemUsers,
            'memberDocuments' => $memberDocuments
        ]);
    }

    public function financial() {
        $this->requireMemberLogin();
        $member_id = $_SESSION['member_id'];
        $db = (new Database())->connect();
        
        $sql = "SELECT * FROM tithes WHERE member_id = ?";
        $params = [$member_id];
        
        // Filters
        if (!empty($_GET['start_date'])) {
            $sql .= " AND payment_date >= ?";
            $params[] = $_GET['start_date'];
        }
        if (!empty($_GET['end_date'])) {
            $sql .= " AND payment_date <= ?";
            $params[] = $_GET['end_date'];
        }
        if (!empty($_GET['type'])) {
            $sql .= " AND type = ?";
            $params[] = $_GET['type'];
        }
        
        $sql .= " ORDER BY payment_date DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $tithes = $stmt->fetchAll();
        
        view('portal/financial', ['tithes' => $tithes]);
    }

    public function card() {
        $this->requireMemberLogin();
        $member_id = $_SESSION['member_id'];
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT m.*, c.name as congregation_name, c.address as congregation_address, c.city as congregation_city, c.state as congregation_state 
                            FROM members m 
                            LEFT JOIN congregations c ON m.congregation_id = c.id 
                            WHERE m.id = ?");
        $stmt->execute([$member_id]);
        $member = $stmt->fetch();
        
        // Fetch Signatures (President) just like in Admin
        $signature = null;
        try {
            $stmtSig = $db->query("SELECT * FROM signatures WHERE slug = 'president' OR slug LIKE '%presidente%' LIMIT 1");
            if ($stmtSig) {
                $signature = $stmtSig->fetch();
            }
        } catch (Exception $e) {
            // Se a tabela não existir ainda ou der erro, segue sem assinatura
        }
        
        // Reuse the admin card view but maybe with a different layout wrapper if needed, 
        // or just include the card content. For simplicity, we can reuse the logic but render a portal-specific view
        // that extends the portal layout.
        view('portal/card', ['member' => $member, 'signature' => $signature]);
    }

    public function agenda() {
        $this->requireMemberLogin();
        $db = (new Database())->connect();
        $member_id = $_SESSION['member_id'];
        
        // Auto-disable expired events (Clean up) - Same logic as EventController
        try {
            $now = date('Y-m-d H:i:s');
            $db->prepare("UPDATE events SET status = 'inactive' WHERE type != 'culto' AND event_date < ? AND event_date > '1971-01-01' AND status = 'active'")->execute([$now]);
        } catch (Exception $e) {}

        $current_date_sql = ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') ? "date('now')" : "CURDATE()";
        
        $stmtMember = $db->prepare("SELECT congregation_id FROM members WHERE id = ?");
        $stmtMember->execute([$member_id]);
        $congregation_id = $stmtMember->fetchColumn();

        // Get future ACTIVE events: only those that are global (NULL) or match the user's congregation
        $sql = "SELECT e.*, c.name as congregation_name 
                FROM events e 
                LEFT JOIN congregations c ON e.congregation_id = c.id
                WHERE e.event_date >= $current_date_sql AND e.status = 'active'
                AND (
                    (e.type != 'interno' AND (e.congregation_id IS NULL OR e.congregation_id = ?))
                    OR (e.type = 'interno' AND (
                        EXISTS(SELECT 1 FROM event_allowed_members am WHERE am.event_id = e.id AND am.member_id = ?)
                        OR EXISTS(SELECT 1 FROM event_allowed_congregations ac WHERE ac.event_id = e.id AND ac.congregation_id = ?)
                    ))
                )
                ORDER BY e.event_date ASC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$congregation_id, $member_id, $congregation_id]);
        $events = $stmt->fetchAll();
        
        view('portal/agenda', ['events' => $events]);
    }
    
    public function documents() {
        $this->requireMemberLogin();
        $db = (new Database())->connect();
        $member_id = $_SESSION['member_id'];
        
        $stmtDocs = $db->prepare("SELECT id, title, type, file_path, created_at FROM member_documents WHERE member_id = ? ORDER BY created_at DESC");
        $stmtDocs->execute([$member_id]);
        $docs = $stmtDocs->fetchAll();
        
        view('portal/documents', ['docs' => $docs]);
    }
    
    public function openDocument($docId) {
        $this->requireMemberLogin();
        $db = (new Database())->connect();
        $member_id = $_SESSION['member_id'];
        
        $stmt = $db->prepare("SELECT id, member_id, title, type, file_path, created_at FROM member_documents WHERE id = ?");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch();
        
        if (!$doc || $doc['member_id'] != $member_id) {
            view('portal/document_missing', [
                'title' => 'Documento não encontrado',
                'message' => 'Este documento não existe ou você não tem permissão para acessá-lo.'
            ]);
            return;
        }
        
        $path = __DIR__ . '/../../public/uploads/members_docs/' . $doc['file_path'];
        if (!is_file($path)) {
            view('portal/document_missing', [
                'title' => 'Arquivo indisponível',
                'message' => 'O arquivo deste documento não foi encontrado. Entre em contato com a administração para reenviar.'
            ]);
            return;
        }
        
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg','jpeg'])) $mime = 'image/jpeg';
        elseif ($ext === 'png') $mime = 'image/png';
        elseif ($ext === 'gif') $mime = 'image/gif';
        elseif ($ext === 'pdf') $mime = 'application/pdf';
        
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        readfile($path);
        exit;
    }
}

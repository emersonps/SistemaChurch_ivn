<?php
// src/controllers/EventController.php

class EventController {
    public function index() {
        requirePermission('events.view');
        $db = (new Database())->connect();
        
        // Auto-disable expired events (Clean up)
        // Checks for non-recurring events that have passed their date
        // Use PHP date to ensure timezone correctness instead of database server time
        // Applies ONLY to specific event types, EXCLUDING 'culto' (which might be recurring or permanent schedule)
        try {
            $now = date('Y-m-d H:i:s');
            // Update events where event_date is strictly less than NOW
            // Only for types that are NOT 'culto' (e.g., 'evento', 'convite', 'outro', 'aniversario')
            // AND ensure it's not a generic recurring event (using 1970 date)
            // Fix: We should also check if recurring_days is NULL or empty to not disable recurring events
            $stmtUpdate = $db->prepare("UPDATE events SET status = 'inactive' WHERE type != 'culto' AND (recurring_days IS NULL OR recurring_days = '[]') AND event_date < ? AND event_date > '1971-01-01' AND status = 'active'");
            $stmtUpdate->execute([$now]);
        } catch (Exception $e) {
            // Ignore errors here, just a cleanup task
        }
        
        $sql = "SELECT * FROM events WHERE 1=1";
        $params = [];
        
        // Se não for admin, filtra pela congregação do usuário
        // Assumindo que o nome da congregação está salvo em events.location
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            // Primeiro, pegamos o nome da congregação do usuário
            $stmt = $db->prepare("SELECT name FROM congregations WHERE id = ?");
            $stmt->execute([$_SESSION['user_congregation_id']]);
            $userCongregation = $stmt->fetchColumn();
            
            if ($userCongregation) {
                $sql .= " AND location = ?";
                $params[] = $userCongregation;
            }
        }
        
        $sql .= " ORDER BY event_date DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll();
        
        view('admin/events/index', ['events' => $events]);
    }

    public function create() {
        requirePermission('events.manage');
        $db = (new Database())->connect();
        
        $sql = "SELECT * FROM congregations";
        $params = [];
        
        // Se não for admin, mostra apenas a congregação do usuário
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = ?";
            $params[] = $_SESSION['user_congregation_id'];
        } else {
            $sql .= " ORDER BY name ASC";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $congregations = $stmt->fetchAll();
        
        $sqlMembers = "SELECT m.id, m.name, m.congregation_id, c.name AS congregation_name FROM members m LEFT JOIN congregations c ON c.id = m.congregation_id";
        $paramsMembers = [];
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            $sqlMembers .= " WHERE m.congregation_id = ?";
            $paramsMembers[] = $_SESSION['user_congregation_id'];
        }
        $sqlMembers .= " ORDER BY m.name ASC";
        $stmtM = $db->prepare($sqlMembers);
        $stmtM->execute($paramsMembers);
        $members = $stmtM->fetchAll();
        
        view('admin/events/create', ['congregations' => $congregations, 'members' => $members]);
    }

    public function store() {
        requirePermission('events.manage');
        $db = (new Database())->connect();

        $title = $_POST['title'];
        $description = $_POST['description'];
        $location = $_POST['location'];
        
        // Validação de Segurança: Se não for admin, força a congregação do usuário
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            $stmt = $db->prepare("SELECT name FROM congregations WHERE id = ?");
            $stmt->execute([$_SESSION['user_congregation_id']]);
            $userCongregationName = $stmt->fetchColumn();
            
            // Sobrescreve o location enviado com o da congregação do usuário
            if ($userCongregationName) {
                $location = $userCongregationName;
            }
        }
        
        $event_date = null;
        if (!empty($_POST['event_date_only'])) {
            $date = $_POST['event_date_only'];
            $time = !empty($_POST['event_time_only']) ? $_POST['event_time_only'] : '00:00';
            $event_date = $date . ' ' . $time;
        } elseif (!empty($_POST['event_time_only'])) {
             $event_date = '1970-01-01 ' . $_POST['event_time_only'];
        }

        $recurring_days = isset($_POST['recurring_days']) ? json_encode($_POST['recurring_days']) : null;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
        $type = $_POST['type'];
        
        // New Fields
        $address = $_POST['address'] ?? null;
        $contact_email = $_POST['contact_email'] ?? null;
        $contact_phone = $_POST['contact_phone'] ?? null;
        
        // Handle Banner Upload
        $banner_path = null;
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/banners/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExt, $allowed)) {
                $fileName = uniqid('event_') . '.' . $fileExt;
                if (move_uploaded_file($_FILES['banner']['tmp_name'], $uploadDir . $fileName)) {
                    $banner_path = '/uploads/banners/' . $fileName;
                }
            }
        }

        // Tentar encontrar o congregation_id baseado no nome do local
        $stmtFindCong = $db->prepare("SELECT id FROM congregations WHERE name = ?");
        $stmtFindCong->execute([$location]);
        $congregation_id = $stmtFindCong->fetchColumn() ?: null;

        $stmt = $db->prepare("INSERT INTO events (title, description, event_date, location, type, status, recurring_days, end_time, banner_path, address, contact_email, contact_phone, congregation_id) VALUES (?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $event_date, $location, $type, $recurring_days, $end_time, $banner_path, $address, $contact_email, $contact_phone, $congregation_id]);
        
        $eventId = $db->lastInsertId();
        if (strtolower($type) === 'interno' && $eventId) {
            $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
            $insIgnoreMember = "INSERT IGNORE INTO event_allowed_members (event_id, member_id) VALUES (?, ?)";
            $insIgnoreCong = "INSERT IGNORE INTO event_allowed_congregations (event_id, congregation_id) VALUES (?, ?)";
            if ($driver !== 'mysql') {
                $insIgnoreMember = "INSERT OR IGNORE INTO event_allowed_members (event_id, member_id) VALUES (?, ?)";
                $insIgnoreCong = "INSERT OR IGNORE INTO event_allowed_congregations (event_id, congregation_id) VALUES (?, ?)";
            }
            $allowedMembers = isset($_POST['allowed_members']) && is_array($_POST['allowed_members']) ? array_filter($_POST['allowed_members']) : [];
            $allowedCongregations = isset($_POST['allowed_congregations']) && is_array($_POST['allowed_congregations']) ? array_filter($_POST['allowed_congregations']) : [];
            if (!empty($allowedMembers)) {
                $allowedCongregations = []; // exclusividade: membros selecionados anulam congregações
            }
            if (!empty($allowedMembers)) {
                $insM = $db->prepare($insIgnoreMember);
                foreach ($allowedMembers as $mid) {
                    if (!empty($mid)) {
                        $insM->execute([$eventId, $mid]);
                    }
                }
            }
            if (!empty($allowedCongregations)) {
                $insC = $db->prepare($insIgnoreCong);
                foreach ($allowedCongregations as $cid) {
                    if (!empty($cid)) {
                        $insC->execute([$eventId, $cid]);
                    }
                }
            }
        }

        redirect('/admin/events');
    }

    public function edit($id) {
        requirePermission('events.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();

        if (!$event) {
            redirect('/admin/events');
        }
        
        // Verificar permissão de edição (se não for admin, só pode editar da sua congregação)
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            $stmt = $db->prepare("SELECT name FROM congregations WHERE id = ?");
            $stmt->execute([$_SESSION['user_congregation_id']]);
            $userCongregationName = $stmt->fetchColumn();
            
            if ($event['location'] !== $userCongregationName) {
                redirect('/admin/events'); // Redireciona silenciosamente ou poderia mostrar erro
            }
        }

        $sql = "SELECT * FROM congregations";
        $params = [];
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = ?";
            $params[] = $_SESSION['user_congregation_id'];
        } else {
            $sql .= " ORDER BY name ASC";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $congregations = $stmt->fetchAll();
        
        $sqlMembers = "SELECT m.id, m.name, m.congregation_id, c.name AS congregation_name FROM members m LEFT JOIN congregations c ON c.id = m.congregation_id";
        $paramsMembers = [];
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            $sqlMembers .= " WHERE m.congregation_id = ?";
            $paramsMembers[] = $_SESSION['user_congregation_id'];
        }
        $sqlMembers .= " ORDER BY m.name ASC";
        $stmtM = $db->prepare($sqlMembers);
        $stmtM->execute($paramsMembers);
        $members = $stmtM->fetchAll();
        
        $allowedMembers = $db->prepare("SELECT member_id FROM event_allowed_members WHERE event_id = ?");
        $allowedMembers->execute([$id]);
        $allowedMemberIds = $allowedMembers->fetchAll(PDO::FETCH_COLUMN);
        
        $allowedCongs = $db->prepare("SELECT congregation_id FROM event_allowed_congregations WHERE event_id = ?");
        $allowedCongs->execute([$id]);
        $allowedCongIds = $allowedCongs->fetchAll(PDO::FETCH_COLUMN);
        
        view('admin/events/edit', ['event' => $event, 'congregations' => $congregations, 'members' => $members, 'allowedMemberIds' => $allowedMemberIds, 'allowedCongIds' => $allowedCongIds]);
    }

    public function update($id) {
        requirePermission('events.manage');
        $db = (new Database())->connect();
        
        // Verificar permissão antes de atualizar
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            $stmtCheck = $db->prepare("SELECT location FROM events WHERE id = ?");
            $stmtCheck->execute([$id]);
            $currentLocation = $stmtCheck->fetchColumn();
            
            $stmtCong = $db->prepare("SELECT name FROM congregations WHERE id = ?");
            $stmtCong->execute([$_SESSION['user_congregation_id']]);
            $userCongregationName = $stmtCong->fetchColumn();
            
            if ($currentLocation !== $userCongregationName) {
                redirect('/admin/events');
                return;
            }
        }

        $title = $_POST['title'];
        $description = $_POST['description'];
        
        $event_date = null;
        if (!empty($_POST['event_date_only'])) {
            $date = $_POST['event_date_only'];
            $time = !empty($_POST['event_time_only']) ? $_POST['event_time_only'] : '00:00';
            $event_date = $date . ' ' . $time;
        } elseif (!empty($_POST['event_time_only'])) {
             $event_date = '1970-01-01 ' . $_POST['event_time_only'];
        }

        $recurring_days = isset($_POST['recurring_days']) ? json_encode($_POST['recurring_days']) : null;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
        $location = $_POST['location'];
        $type = $_POST['type'];
        $status = $_POST['status'];
        
        // New Fields
        $address = $_POST['address'] ?? null;
        $contact_email = $_POST['contact_email'] ?? null;
        $contact_phone = $_POST['contact_phone'] ?? null;
        
        // Segurança no Update também
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
             $stmtCong = $db->prepare("SELECT name FROM congregations WHERE id = ?");
             $stmtCong->execute([$_SESSION['user_congregation_id']]);
             $userCongregationName = $stmtCong->fetchColumn();
             if ($userCongregationName) {
                 $location = $userCongregationName;
             }
        }
        
        // Handle Banner Upload
        $banner_path = null;
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/banners/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExt, $allowed)) {
                $fileName = uniqid('event_') . '.' . $fileExt;
                if (move_uploaded_file($_FILES['banner']['tmp_name'], $uploadDir . $fileName)) {
                    $banner_path = '/uploads/banners/' . $fileName;
                }
            }
        }

        // Tentar encontrar o congregation_id baseado no nome do local
        $stmtFindCong = $db->prepare("SELECT id FROM congregations WHERE name = ?");
        $stmtFindCong->execute([$location]);
        $congregation_id = $stmtFindCong->fetchColumn() ?: null;

        $removeBanner = isset($_POST['remove_banner']);
        if ($removeBanner) {
            $stmtCur = $db->prepare("SELECT banner_path FROM events WHERE id = ?");
            $stmtCur->execute([$id]);
            $cur = $stmtCur->fetch();
            if (!empty($cur['banner_path'])) {
                $p = $cur['banner_path'];
                if (strpos($p, '/uploads/banners/') === 0) {
                    $fs = __DIR__ . '/../../public' . $p;
                    if (file_exists($fs)) {
                        unlink($fs);
                    }
                }
            }
        }
        
        if ($banner_path) {
            $stmt = $db->prepare("UPDATE events SET title=?, description=?, event_date=?, location=?, type=?, status=?, recurring_days=?, end_time=?, banner_path=?, address=?, contact_email=?, contact_phone=?, congregation_id=? WHERE id=?");
            $stmt->execute([$title, $description, $event_date, $location, $type, $status, $recurring_days, $end_time, $banner_path, $address, $contact_email, $contact_phone, $congregation_id, $id]);
        } elseif ($removeBanner) {
            $stmt = $db->prepare("UPDATE events SET title=?, description=?, event_date=?, location=?, type=?, status=?, recurring_days=?, end_time=?, banner_path=NULL, address=?, contact_email=?, contact_phone=?, congregation_id=? WHERE id=?");
            $stmt->execute([$title, $description, $event_date, $location, $type, $status, $recurring_days, $end_time, $address, $contact_email, $contact_phone, $congregation_id, $id]);
        } else {
            $stmt = $db->prepare("UPDATE events SET title=?, description=?, event_date=?, location=?, type=?, status=?, recurring_days=?, end_time=?, address=?, contact_email=?, contact_phone=?, congregation_id=? WHERE id=?");
            $stmt->execute([$title, $description, $event_date, $location, $type, $status, $recurring_days, $end_time, $address, $contact_email, $contact_phone, $congregation_id, $id]);
        }
        
        if (strtolower($type) === 'interno') {
            $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
            $insIgnoreMember = "INSERT IGNORE INTO event_allowed_members (event_id, member_id) VALUES (?, ?)";
            $insIgnoreCong = "INSERT IGNORE INTO event_allowed_congregations (event_id, congregation_id) VALUES (?, ?)";
            if ($driver !== 'mysql') {
                $insIgnoreMember = "INSERT OR IGNORE INTO event_allowed_members (event_id, member_id) VALUES (?, ?)";
                $insIgnoreCong = "INSERT OR IGNORE INTO event_allowed_congregations (event_id, congregation_id) VALUES (?, ?)";
            }
            $db->prepare("DELETE FROM event_allowed_members WHERE event_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM event_allowed_congregations WHERE event_id = ?")->execute([$id]);
            $allowedMembers = isset($_POST['allowed_members']) && is_array($_POST['allowed_members']) ? array_filter($_POST['allowed_members']) : [];
            $allowedCongregations = isset($_POST['allowed_congregations']) && is_array($_POST['allowed_congregations']) ? array_filter($_POST['allowed_congregations']) : [];
            if (!empty($allowedMembers)) {
                $allowedCongregations = []; // exclusividade: membros selecionados anulam congregações
            }
            if (!empty($allowedMembers)) {
                $insM = $db->prepare($insIgnoreMember);
                foreach ($allowedMembers as $mid) {
                    if (!empty($mid)) {
                        $insM->execute([$id, $mid]);
                    }
                }
            }
            if (!empty($allowedCongregations)) {
                $insC = $db->prepare($insIgnoreCong);
                foreach ($allowedCongregations as $cid) {
                    if (!empty($cid)) {
                        $insC->execute([$id, $cid]);
                    }
                }
            }
        } else {
            $db->prepare("DELETE FROM event_allowed_members WHERE event_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM event_allowed_congregations WHERE event_id = ?")->execute([$id]);
        }

        redirect('/admin/events');
    }

    public function delete($id) {
        requirePermission('events.manage');
        $db = (new Database())->connect();
        
        // Verificar permissão antes de deletar
        if ($_SESSION['user_role'] !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            $stmtCheck = $db->prepare("SELECT location FROM events WHERE id = ?");
            $stmtCheck->execute([$id]);
            $currentLocation = $stmtCheck->fetchColumn();
            
            $stmtCong = $db->prepare("SELECT name FROM congregations WHERE id = ?");
            $stmtCong->execute([$_SESSION['user_congregation_id']]);
            $userCongregationName = $stmtCong->fetchColumn();
            
            if ($currentLocation !== $userCongregationName) {
                redirect('/admin/events');
                return;
            }
        }
        
        $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect('/admin/events');
    }

    public function toggleStatus($id) {
        requirePermission('events.manage');
        $db = (new Database())->connect();
        
        // RBAC: Allow toggle for cultos e recorrentes; block apenas eventos passados não recorrentes
        $stmtCheck = $db->prepare("SELECT event_date, type, recurring_days FROM events WHERE id = ?");
        $stmtCheck->execute([$id]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $eventDate = $row['event_date'] ?? null;
        $type = strtolower($row['type'] ?? '');
        $recurringDays = $row['recurring_days'] ?? null;
        
        $isRecurring = !empty($recurringDays) && $recurringDays !== '[]';
        if ($type !== 'culto' && !$isRecurring && $eventDate && strtotime($eventDate) < strtotime('today')) {
            // Event is in the past, prevent toggle
            $_SESSION['error'] = "Não é possível alterar o status de eventos passados.";
            redirect('/admin/events');
            return;
        }
        
        $stmt = $db->prepare("UPDATE events SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
        $stmt->execute([$id]);
        redirect('/admin/events');
    }
    
    // ==========================================
    // ATTENDANCE (CHAMADA VIA QR CODE)
    // ==========================================
    
    // Lista de eventos para controle de presença
    public function attendanceList() {
        requirePermission('events.view');
        $db = (new Database())->connect();
        
        $congregationId = $_SESSION['user_congregation_id'] ?? null;
        
        // Query to get events and attendance count
        $sql = "SELECT e.*, COUNT(a.id) as attendance_count 
                FROM events e 
                LEFT JOIN event_attendance a ON e.id = a.event_id 
                WHERE e.has_attendance_list = 1";
        $params = [];
        
        if ($congregationId) {
            $sql .= " AND (e.congregation_id = ? OR e.location = (SELECT name FROM congregations WHERE id = ?))";
            $params[] = $congregationId;
            $params[] = $congregationId;
        }
        
        $sql .= " GROUP BY e.id ORDER BY e.event_date DESC, e.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll();
        
        // Fetch all future/recent events that DON'T have attendance list yet, for the "Select Event" modal
        // We use LOWER(type) to ensure case-insensitivity
        $sqlAvailable = "SELECT id, title, event_date, location FROM events WHERE (has_attendance_list = 0 OR has_attendance_list IS NULL) AND LOWER(type) = 'evento'";
        $paramsAvailable = [];
        
        if ($congregationId) {
            $sqlAvailable .= " AND (congregation_id = ? OR location = (SELECT name FROM congregations WHERE id = ?))";
            $paramsAvailable[] = $congregationId;
            $paramsAvailable[] = $congregationId;
        }
        
        // Remove the 30 days limitation to ensure ALL 'evento' types appear, or at least expand it to 1 year back
        // The user specifically requested: "Todos os eventos que ainda nao passaram da data (EVENTOS APENAS)"
        // However, if an event is happening today, its time might be in the past (e.g. 10:00 AM, and it's 11:00 AM now).
        // Let's ensure anything from the current day onwards is visible, regardless of hours.
        $todayStart = date('Y-m-d 00:00:00');
        $sqlAvailable .= " AND event_date >= ? ORDER BY event_date ASC";
        $paramsAvailable[] = $todayStart;
        
        $stmtAvail = $db->prepare($sqlAvailable);
        $stmtAvail->execute($paramsAvailable);
        $availableEvents = $stmtAvail->fetchAll();
        
        view('admin/events/attendance_list', ['events' => $events, 'availableEvents' => $availableEvents]);
    }
    
    // Activate attendance list for an event
    public function enableAttendance($eventId) {
        requirePermission('events.manage');
        $db = (new Database())->connect();
        
        try {
            $stmt = $db->prepare("UPDATE events SET has_attendance_list = 1 WHERE id = ?");
            $stmt->execute([$eventId]);
            
            $_SESSION['success'] = "Lista de presença ativada para o evento!";
        } catch (Exception $e) {
            // Caso a coluna has_attendance_list não exista no banco (migration não rodou)
            if (strpos($e->getMessage(), 'has_attendance_list') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
                $_SESSION['error'] = "Erro: A estrutura do banco de dados está desatualizada. Por favor, vá em Developer > Migrations e rode as migrações pendentes.";
            } else {
                $_SESSION['error'] = "Erro ao ativar lista: " . $e->getMessage();
            }
        }
        
        redirect('/admin/attendance');
    }
    
    // Desativar e limpar lista de presença de um evento
    public function deleteAttendanceList($eventId) {
        requirePermission('events.manage');
        $db = (new Database())->connect();
        
        try {
            // Primeiro apaga os registros de presença
            $stmtDel = $db->prepare("DELETE FROM event_attendance WHERE event_id = ?");
            $stmtDel->execute([$eventId]);
            
            // Depois desativa a flag no evento
            $stmtUpd = $db->prepare("UPDATE events SET has_attendance_list = 0 WHERE id = ?");
            $stmtUpd->execute([$eventId]);
            
            $_SESSION['success'] = "Controle de presença excluído com sucesso!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erro ao excluir controle: " . $e->getMessage();
        }
        
        redirect('/admin/attendance');
    }

    public function attendance($eventId) {
        requirePermission('events.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch();
        
        if (!$event) {
            redirect('/admin/events');
        }
        
        // Buscar membros já registrados
        $stmtAtt = $db->prepare("
            SELECT a.id as attendance_id, a.scanned_at, m.name, m.photo, m.unique_id, c.name as congregation_name
            FROM event_attendance a
            JOIN members m ON a.member_id = m.id
            LEFT JOIN congregations c ON m.congregation_id = c.id
            WHERE a.event_id = ?
            ORDER BY a.scanned_at DESC
        ");
        $stmtAtt->execute([$eventId]);
        $attendees = $stmtAtt->fetchAll();
        
        view('admin/events/attendance', ['event' => $event, 'attendees' => $attendees]);
    }
    
    public function printAttendance($eventId) {
        requirePermission('events.view');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch();
        
        if (!$event) {
            redirect('/admin/events');
        }
        
        // Buscar membros já registrados com mais detalhes para impressão
        $stmtAtt = $db->prepare("
            SELECT a.id as attendance_id, a.scanned_at, m.name, m.role, m.unique_id, c.name as congregation_name
            FROM event_attendance a
            JOIN members m ON a.member_id = m.id
            LEFT JOIN congregations c ON m.congregation_id = c.id
            WHERE a.event_id = ?
            ORDER BY m.name ASC
        ");
        $stmtAtt->execute([$eventId]);
        $attendees = $stmtAtt->fetchAll();
        
        view('admin/events/print_attendance', ['event' => $event, 'attendees' => $attendees]);
    }
    
    public function registerAttendance($eventId) {
        requirePermission('events.manage');
        header('Content-Type: application/json');
        
        $db = (new Database())->connect();
        $qrData = $_POST['qr_data'] ?? '';
        
        if (empty($qrData)) {
            echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido.']);
            exit;
        }
        
        // Parse QR Data. Format expected: "IEADSENA_MEMBER:123" or "IEADSENA_MEMBER:UUID"
        $parts = explode(':', $qrData);
        if (count($parts) !== 2 || $parts[0] !== 'IEADSENA_MEMBER') {
            echo json_encode(['success' => false, 'message' => 'QR Code inválido ou não pertence a esta congregação.']);
            exit;
        }
        
        $memberIdentifier = trim($parts[1]);
        
        // Find Member (try unique_id first, then id)
        $stmt = $db->prepare("SELECT id, name, photo FROM members WHERE unique_id = ? OR id = ? LIMIT 1");
        $stmt->execute([$memberIdentifier, $memberIdentifier]);
        $member = $stmt->fetch();
        
        if (!$member) {
            echo json_encode(['success' => false, 'message' => 'Membro não encontrado no banco de dados.']);
            exit;
        }
        
        // Check if already registered
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM event_attendance WHERE event_id = ? AND member_id = ?");
        $stmtCheck->execute([$eventId, $member['id']]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Membro já registrou presença neste evento!',
                'member' => $member
            ]);
            exit;
        }
        
        // Register Attendance
        try {
            $stmtInsert = $db->prepare("INSERT INTO event_attendance (event_id, member_id) VALUES (?, ?)");
            $stmtInsert->execute([$eventId, $member['id']]);
            
            $scannedAt = date('d/m/Y H:i:s');
            
            echo json_encode([
                'success' => true, 
                'message' => 'Presença registrada com sucesso!',
                'member' => [
                    'name' => $member['name'],
                    'photo' => $member['photo'],
                    'scanned_at' => $scannedAt
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar: ' . $e->getMessage()]);
        }
    }
}

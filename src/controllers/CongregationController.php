<?php
// src/controllers/CongregationController.php

class CongregationController {
    
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
        requirePermission('congregations.view');
        $db = (new Database())->connect();
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        view('admin/congregations/index', ['congregations' => $congregations]);
    }

    public function create() {
        requirePermission('congregations.manage');
        view('admin/congregations/create');
    }

    public function store() {
        requirePermission('congregations.manage');
        $name = $_POST['name'];
        $opening_date = !empty($_POST['opening_date']) ? $_POST['opening_date'] : null;
        $leader_name = $_POST['leader_name'];
        $leader_member_id = !empty($_POST['leader_member_id']) ? (int)$_POST['leader_member_id'] : null;
        $transfer_leader = !empty($_POST['transfer_leader']) && $_POST['transfer_leader'] == '1';
        $cnpj = $_POST['cnpj'] ?? null;
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $zip_code = $_POST['zip_code'] ?? null;
        $city = $_POST['city'] ?? null;
        $state = $_POST['state'] ?? null;
        
        // Process schedule
        $schedule = [];
        if (isset($_POST['schedule']) && is_array($_POST['schedule'])) {
            foreach ($_POST['schedule'] as $item) {
                if (!empty($item['day']) && (!empty($item['start_time']) || !empty($item['end_time']))) {
                    $schedule[] = [
                        'day' => $item['day'],
                        'name' => $item['name'] ?? '',
                        'start_time' => $item['start_time'],
                        'end_time' => $item['end_time']
                    ];
                }
            }
        }
        $service_schedule = !empty($schedule) ? json_encode($schedule) : null;
        
        // Default type to 'congregation' for new entries unless specified otherwise
        $type = 'congregation'; 
        
        $photo = null;
        // Check for photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $uploadDir = __DIR__ . '/../../public/uploads/congregations/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $photo = $filename;
            }
        }

        $db = (new Database())->connect();
        if ($leader_member_id) {
            $stmtLM = $db->prepare("SELECT name FROM members WHERE id = ?");
            $stmtLM->execute([$leader_member_id]);
            $mrow = $stmtLM->fetch();
            if ($mrow && !empty($mrow['name'])) {
                $leader_name = $mrow['name'];
            }
        }
        $db->beginTransaction();
        try {
            $hasCnpj = $this->tableHasColumn($db, 'congregations', 'cnpj');
            $hasLeaderId = $this->tableHasColumn($db, 'congregations', 'leader_member_id');
            if ($hasCnpj && $hasLeaderId) {
                $stmt = $db->prepare("INSERT INTO congregations (name, opening_date, leader_name, leader_member_id, address, phone, email, cnpj, type, zip_code, city, state, photo, service_schedule) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $opening_date, $leader_name, $leader_member_id, $address, $phone, $email, $cnpj, $type, $zip_code, $city, $state, $photo, $service_schedule]);
            } elseif ($hasLeaderId) {
                $stmt = $db->prepare("INSERT INTO congregations (name, opening_date, leader_name, leader_member_id, address, phone, email, type, zip_code, city, state, photo, service_schedule) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $opening_date, $leader_name, $leader_member_id, $address, $phone, $email, $type, $zip_code, $city, $state, $photo, $service_schedule]);
            } elseif ($hasCnpj) {
                $stmt = $db->prepare("INSERT INTO congregations (name, opening_date, leader_name, address, phone, email, cnpj, type, zip_code, city, state, photo, service_schedule) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $opening_date, $leader_name, $address, $phone, $email, $cnpj, $type, $zip_code, $city, $state, $photo, $service_schedule]);
            } else {
                $stmt = $db->prepare("INSERT INTO congregations (name, opening_date, leader_name, address, phone, email, type, zip_code, city, state, photo, service_schedule) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $opening_date, $leader_name, $address, $phone, $email, $type, $zip_code, $city, $state, $photo, $service_schedule]);
            }

        $newId = $db->lastInsertId();
        if ($newId && $leader_member_id && $transfer_leader) {
            $up = $db->prepare("UPDATE members SET congregation_id = ? WHERE id = ?");
            $up->execute([$newId, $leader_member_id]);
        }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            redirect('/admin/congregations?error=save_failed');
            return;
        }
        redirect('/admin/congregations');
    }

    public function edit($id) {
        requirePermission('congregations.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM congregations WHERE id = ?");
        $stmt->execute([$id]);
        $congregation = $stmt->fetch();

        if (!$congregation) {
            redirect('/admin/congregations');
        }

        view('admin/congregations/edit', ['congregation' => $congregation]);
    }

    public function update($id) {
        requirePermission('congregations.manage');
        $name = $_POST['name'];
        $opening_date = !empty($_POST['opening_date']) ? $_POST['opening_date'] : null;
        $leader_name = $_POST['leader_name'];
        $leader_member_id = !empty($_POST['leader_member_id']) ? (int)$_POST['leader_member_id'] : null;
        $transfer_leader = !empty($_POST['transfer_leader']) && $_POST['transfer_leader'] == '1';
        $cnpj = $_POST['cnpj'] ?? null;
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $zip_code = $_POST['zip_code'] ?? null;
        $city = $_POST['city'] ?? null;
        $state = $_POST['state'] ?? null;

        $db = (new Database())->connect();
        $prevLeaderCongId = null;
        if (!$leader_member_id && !empty($leader_name)) {
            $stmtFind = $db->prepare("SELECT id, congregation_id FROM members WHERE LOWER(name) = LOWER(?) LIMIT 1");
            $stmtFind->execute([$leader_name]);
            $found = $stmtFind->fetch();
            if ($found) {
                $leader_member_id = (int)$found['id'];
                $prevLeaderCongId = $found['congregation_id'] ?? null;
            }
        }
        if ($leader_member_id) {
            $stmtLM = $db->prepare("SELECT name FROM members WHERE id = ?");
            $stmtLM->execute([$leader_member_id]);
            $mrow = $stmtLM->fetch();
            if ($mrow && !empty($mrow['name'])) {
                $leader_name = $mrow['name'];
            }
        }
        
        // Process schedule
        $schedule = [];
        if (isset($_POST['schedule']) && is_array($_POST['schedule'])) {
            foreach ($_POST['schedule'] as $item) {
                if (!empty($item['day']) && (!empty($item['start_time']) || !empty($item['end_time']))) {
                    $schedule[] = [
                        'day' => $item['day'],
                        'name' => $item['name'] ?? '',
                        'start_time' => $item['start_time'],
                        'end_time' => $item['end_time']
                    ];
                }
            }
        }
        $service_schedule = !empty($schedule) ? json_encode($schedule) : null;
        
        // Check for new photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $uploadDir = __DIR__ . '/../../public/uploads/congregations/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $stmt = $db->prepare("UPDATE congregations SET photo=? WHERE id=?");
                $stmt->execute([$filename, $id]);
            }
        }

        $db->beginTransaction();
        try {
            $hasCnpj = $this->tableHasColumn($db, 'congregations', 'cnpj');
            $hasLeaderId = $this->tableHasColumn($db, 'congregations', 'leader_member_id');
            if ($hasCnpj && $hasLeaderId) {
                $stmt = $db->prepare("UPDATE congregations SET name=?, opening_date=?, leader_name=?, leader_member_id=?, address=?, phone=?, email=?, cnpj=?, zip_code=?, city=?, state=?, service_schedule=? WHERE id=?");
                $stmt->execute([$name, $opening_date, $leader_name, $leader_member_id, $address, $phone, $email, $cnpj, $zip_code, $city, $state, $service_schedule, $id]);
            } elseif ($hasLeaderId) {
                $stmt = $db->prepare("UPDATE congregations SET name=?, opening_date=?, leader_name=?, leader_member_id=?, address=?, phone=?, email=?, zip_code=?, city=?, state=?, service_schedule=? WHERE id=?");
                $stmt->execute([$name, $opening_date, $leader_name, $leader_member_id, $address, $phone, $email, $zip_code, $city, $state, $service_schedule, $id]);
            } elseif ($hasCnpj) {
                $stmt = $db->prepare("UPDATE congregations SET name=?, opening_date=?, leader_name=?, address=?, phone=?, email=?, cnpj=?, zip_code=?, city=?, state=?, service_schedule=? WHERE id=?");
                $stmt->execute([$name, $opening_date, $leader_name, $address, $phone, $email, $cnpj, $zip_code, $city, $state, $service_schedule, $id]);
            } else {
                $stmt = $db->prepare("UPDATE congregations SET name=?, opening_date=?, leader_name=?, address=?, phone=?, email=?, zip_code=?, city=?, state=?, service_schedule=? WHERE id=?");
                $stmt->execute([$name, $opening_date, $leader_name, $address, $phone, $email, $zip_code, $city, $state, $service_schedule, $id]);
            }
            
            if ($leader_member_id && $transfer_leader) {
                $up = $db->prepare("UPDATE members SET congregation_id = ? WHERE id = ?");
                $up->execute([$id, $leader_member_id]);
            } elseif ($leader_member_id && $prevLeaderCongId !== null && (int)$prevLeaderCongId !== (int)$id) {
                $up = $db->prepare("UPDATE members SET congregation_id = ? WHERE id = ?");
                $up->execute([$id, $leader_member_id]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            redirect('/admin/congregations/edit/'.$id.'?error=save_failed');
            return;
        }

        redirect('/admin/congregations');
    }

    public function delete($id) {
        requireLogin();
        $db = (new Database())->connect();
        
        // Check if there are members associated
        $stmt = $db->prepare("SELECT COUNT(*) FROM members WHERE congregation_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            // Could add an error message here, but for now just redirect
            redirect('/admin/congregations?error=has_members'); 
            return;
        }

        $stmt = $db->prepare("DELETE FROM congregations WHERE id = ?");
        $stmt->execute([$id]);
        redirect('/admin/congregations');
    }
}

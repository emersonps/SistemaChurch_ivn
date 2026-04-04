<?php
// src/controllers/MemberController.php

class MemberController {
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
    private function normalizeCsvHeader($header) {
        $header = trim((string)$header);
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        $header = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $header);
        $header = strtolower($header);
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        return trim($header, '_');
    }
    private function detectCsvDelimiter($line) {
        $delimiters = [',', ';', "\t"];
        $bestDelimiter = ',';
        $bestCount = -1;
        foreach ($delimiters as $delimiter) {
            $count = count(str_getcsv($line, $delimiter));
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }
        return $bestDelimiter;
    }
    private function parseImportDate($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y'];
        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $value);
            if ($dt && $dt->format($format) === $value) {
                return $dt->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
    private function parseImportBool($value) {
        $value = strtolower(trim((string)$value));
        if ($value === '') {
            return 0;
        }
        return in_array($value, ['1', 'sim', 's', 'yes', 'y', 'true', 'ativo', 'ok'], true) ? 1 : 0;
    }
    private function mapMemberImportRow(array $headers, array $row) {
        $data = [];
        foreach ($this->getMembersImportColumns() as $column) {
            $data[$column] = '';
        }
        foreach ($headers as $index => $header) {
            $data[$header] = isset($row[$index]) ? trim((string)$row[$index]) : '';
        }
        return $data;
    }
    private function resolveImportCongregationId(PDO $db, $selectedCongregationId = null) {
        if (!empty($_SESSION['user_congregation_id'])) {
            return (int)$_SESSION['user_congregation_id'];
        }

        return !empty($selectedCongregationId) ? (int)$selectedCongregationId : null;
    }
    private function getMembersImportColumns() {
        return [
            'name',
            'email',
            'phone',
            'birth_date',
            'gender',
            'cpf',
            'rg',
            'address',
            'address_number',
            'neighborhood',
            'zip_code',
            'state',
            'city',
            'role',
            'nationality',
            'birthplace',
            'father_name',
            'mother_name',
            'children_count',
            'profession',
            'admission_date'
        ];
    }
    public function index() {
        requirePermission('members.view');
        $db = (new Database())->connect();
        
        $hasLeaderId = $this->tableHasColumn($db, 'congregations', 'leader_member_id');
        if ($hasLeaderId) {
            $sql = "SELECT m.*, c.name as congregation_name,
                           CASE WHEN c.leader_member_id = m.id OR LOWER(c.leader_name) = LOWER(m.name) THEN 1 ELSE 0 END AS is_leader
                    FROM members m 
                    LEFT JOIN congregations c ON m.congregation_id = c.id";
        } else {
            $sql = "SELECT m.*, c.name as congregation_name,
                           CASE WHEN LOWER(c.leader_name) = LOWER(m.name) THEN 1 ELSE 0 END AS is_leader
                    FROM members m 
                    LEFT JOIN congregations c ON m.congregation_id = c.id";
        }
        $params = [];
        
        // Filter by user's congregation
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE m.congregation_id = ?";
            $params[] = $_SESSION['user_congregation_id'];
        } elseif (isset($_GET['congregation_id']) && !empty($_GET['congregation_id'])) {
            $sql .= " WHERE m.congregation_id = ?";
            $params[] = $_GET['congregation_id'];
        }
        
        $sql .= " ORDER BY c.name ASC, m.name ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $members = $stmt->fetchAll();
        
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        view('admin/members/index', ['members' => $members, 'congregations' => $congregations]);
    }

    public function import() {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        $sql = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = " . (int)$_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        $congregations = $db->query($sql)->fetchAll();
        view('admin/members/import', ['congregations' => $congregations, 'columns' => $this->getMembersImportColumns()]);
    }

    public function importTemplate() {
        requirePermission('members.manage');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="modelo_importacao_membros.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, $this->getMembersImportColumns(), ';');
        fputcsv($out, [
            'João da Silva',
            'joao@email.com',
            '(11)99999-0001',
            '1988-05-10',
            'Masculino',
            '123.456.789-00',
            '12.345.678-9',
            'Rua Central',
            '100',
            'Centro',
            '01000-000',
            'SP',
            'São Paulo',
            'Membro',
            'Brasileira',
            'São Paulo',
            'José da Silva',
            'Maria da Silva',
            '2',
            'Pedreiro',
            date('Y-m-d')
        ], ';');
        fclose($out);
        exit;
    }

    public function importProcess() {
        requirePermission('members.manage');
        verify_csrf();

        $selectedCongregationId = null;
        if (!empty($_SESSION['user_congregation_id'])) {
            $selectedCongregationId = (int)$_SESSION['user_congregation_id'];
        } elseif (!empty($_POST['congregation_id'])) {
            $selectedCongregationId = (int)$_POST['congregation_id'];
        }

        if (empty($selectedCongregationId)) {
            $_SESSION['flash_error'] = 'Selecione a congregação para a qual a lista de membros será importada.';
            redirect('/admin/members/import');
            return;
        }

        $db = (new Database())->connect();
        $stmtCong = $db->prepare("SELECT id FROM congregations WHERE id = ?");
        $stmtCong->execute([$selectedCongregationId]);
        if (!$stmtCong->fetchColumn()) {
            $_SESSION['flash_error'] = 'A congregação selecionada é inválida.';
            redirect('/admin/members/import');
            return;
        }

        if (!isset($_FILES['spreadsheet']) || ($_FILES['spreadsheet']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Selecione um arquivo CSV para importar.';
            redirect('/admin/members/import');
            return;
        }

        $handle = fopen($_FILES['spreadsheet']['tmp_name'], 'r');
        if (!$handle) {
            $_SESSION['flash_error'] = 'Não foi possível ler o arquivo enviado.';
            redirect('/admin/members/import');
            return;
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            $_SESSION['flash_error'] = 'Arquivo vazio.';
            redirect('/admin/members/import');
            return;
        }

        $delimiter = $this->detectCsvDelimiter($firstLine);
        rewind($handle);
        $headersRaw = fgetcsv($handle, 0, $delimiter);
        $headers = array_map([$this, 'normalizeCsvHeader'], $headersRaw ?: []);

        if (empty($headers) || !in_array('name', $headers, true)) {
            fclose($handle);
            $_SESSION['flash_error'] = 'A planilha precisa ter pelo menos a coluna "name".';
            redirect('/admin/members/import');
            return;
        }

        $inserted = 0;
        $skipped = 0;
        $errors = [];

        $sql = "INSERT INTO members (
            name, email, phone, birth_date, congregation_id, is_baptized, baptism_date,
            gender, cpf, rg, marital_status, address, address_number, neighborhood, complement,
            reference_point, zip_code, state, city, role, nationality, birthplace, father_name,
            mother_name, children_count, profession, church_origin, admission_method, admission_date,
            exit_date, is_tither, is_ebd_teacher, is_new_convert, accepted_jesus_at, reconciled_at, status, photo
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";
        $stmtInsert = $db->prepare($sql);
        $stmtLink = $db->prepare("UPDATE tithes SET member_id = ?, giver_name = NULL WHERE member_id IS NULL AND giver_name = ?");
        $stmtCpf = $db->prepare("SELECT id FROM members WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?");

        $lineNumber = 1;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;
            if (count(array_filter($row, fn($value) => trim((string)$value) !== '')) === 0) {
                continue;
            }

            $rowData = $this->mapMemberImportRow($headers, $row);
            $name = trim($rowData['name'] ?? '');
            if ($name === '') {
                $skipped++;
                $errors[] = "Linha {$lineNumber}: nome não informado.";
                continue;
            }

            $congregationId = $this->resolveImportCongregationId($db, $selectedCongregationId);
            if (empty($congregationId)) {
                $skipped++;
                $errors[] = "Linha {$lineNumber}: congregação não localizada.";
                continue;
            }

            $cpf = trim($rowData['cpf'] ?? '');
            if ($cpf !== '') {
                $cpfClean = preg_replace('/[^0-9]/', '', $cpf);
                $stmtCpf->execute([$cpfClean]);
                if ($stmtCpf->fetchColumn()) {
                    $skipped++;
                    $errors[] = "Linha {$lineNumber}: CPF já cadastrado.";
                    continue;
                }
            }

            $data = [
                $name,
                ($rowData['email'] ?? '') !== '' ? $rowData['email'] : null,
                ($rowData['phone'] ?? '') !== '' ? $rowData['phone'] : null,
                $this->parseImportDate($rowData['birth_date'] ?? ''),
                $congregationId,
                0,
                null,
                ($rowData['gender'] ?? '') !== '' ? $rowData['gender'] : null,
                $cpf !== '' ? $cpf : null,
                ($rowData['rg'] ?? '') !== '' ? $rowData['rg'] : null,
                ($rowData['marital_status'] ?? '') !== '' ? $rowData['marital_status'] : null,
                ($rowData['address'] ?? '') !== '' ? $rowData['address'] : null,
                ($rowData['address_number'] ?? '') !== '' ? $rowData['address_number'] : null,
                ($rowData['neighborhood'] ?? '') !== '' ? $rowData['neighborhood'] : null,
                ($rowData['complement'] ?? '') !== '' ? $rowData['complement'] : null,
                ($rowData['reference_point'] ?? '') !== '' ? $rowData['reference_point'] : null,
                ($rowData['zip_code'] ?? '') !== '' ? $rowData['zip_code'] : null,
                ($rowData['state'] ?? '') !== '' ? $rowData['state'] : null,
                ($rowData['city'] ?? '') !== '' ? $rowData['city'] : null,
                ($rowData['role'] ?? '') !== '' ? $rowData['role'] : null,
                ($rowData['nationality'] ?? '') !== '' ? $rowData['nationality'] : 'Brasileira',
                ($rowData['birthplace'] ?? '') !== '' ? $rowData['birthplace'] : null,
                ($rowData['father_name'] ?? '') !== '' ? $rowData['father_name'] : null,
                ($rowData['mother_name'] ?? '') !== '' ? $rowData['mother_name'] : null,
                (int)(($rowData['children_count'] ?? '') !== '' ? $rowData['children_count'] : 0),
                ($rowData['profession'] ?? '') !== '' ? $rowData['profession'] : null,
                ($rowData['church_origin'] ?? '') !== '' ? $rowData['church_origin'] : null,
                ($rowData['admission_method'] ?? '') !== '' ? $rowData['admission_method'] : null,
                $this->parseImportDate($rowData['admission_date'] ?? '') ?: date('Y-m-d'),
                null,
                0,
                0,
                0,
                null,
                null,
                'active',
                null
            ];

            try {
                $stmtInsert->execute($data);
                $newMemberId = $db->lastInsertId();
                if ($newMemberId) {
                    $stmtLink->execute([$newMemberId, $name]);
                }
                $inserted++;
            } catch (Exception $e) {
                $skipped++;
                $errors[] = "Linha {$lineNumber}: " . $e->getMessage();
            }
        }

        fclose($handle);

        $_SESSION['flash_success'] = "Importação concluída. Inseridos: {$inserted}. Ignorados: {$skipped}.";
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' | ', array_slice($errors, 0, 5)) . (count($errors) > 5 ? ' | ...' : '');
        }
        redirect('/admin/members');
    }

    public function create() {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        
        $sql = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        
        $congregations = $db->query($sql)->fetchAll();
        view('admin/members/create', ['congregations' => $congregations]);
    }

    public function store() {
        requirePermission('members.manage');
        
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'birth_date' => $_POST['birth_date'] ?: null,
            'congregation_id' => $_POST['congregation_id'],
            'is_baptized' => isset($_POST['is_baptized']) ? 1 : 0,
            'baptism_date' => $_POST['baptism_date'] ?: null,
            'gender' => $_POST['gender'] ?? null,
            'cpf' => $_POST['cpf'] ?? null,
            'rg' => $_POST['rg'] ?? null,
            'marital_status' => $_POST['marital_status'] ?? null,
            'address' => $_POST['address'] ?? null,
            'address_number' => $_POST['address_number'] ?? null,
            'neighborhood' => $_POST['neighborhood'] ?? null,
            'complement' => $_POST['complement'] ?? null,
            'reference_point' => $_POST['reference_point'] ?? null,
            'zip_code' => $_POST['zip_code'] ?? null,
            'state' => $_POST['state'] ?? null,
            'city' => $_POST['city'] ?? null,
            'role' => $_POST['role'] ?? null,
            'nationality' => $_POST['nationality'] ?? 'Brasileira',
            'birthplace' => $_POST['birthplace'] ?? null,
            'father_name' => $_POST['father_name'] ?? null,
            'mother_name' => $_POST['mother_name'] ?? null,
            'children_count' => (int)($_POST['children_count'] ?? 0),
            'profession' => $_POST['profession'] ?? null,
            'church_origin' => $_POST['church_origin'] ?? null,
            'admission_method' => $_POST['admission_method'] ?? null,
            'admission_date' => $_POST['admission_date'] ?: date('Y-m-d'),
            'exit_date' => $_POST['exit_date'] ?: null,
            'is_tither' => isset($_POST['is_tither']) ? 1 : 0,
            'is_ebd_teacher' => isset($_POST['is_ebd_teacher']) ? 1 : 0,
            'is_new_convert' => isset($_POST['is_new_convert']) ? 1 : 0,
            'accepted_jesus_at' => $_POST['accepted_jesus_at'] ?: null,
            'reconciled_at' => $_POST['reconciled_at'] ?: null,
            // 'is_founder' removed temporarily to fix crash
            'status' => $_POST['status'] ?? 'active',
            'photo' => null
        ];
        
        $db = (new Database())->connect();

        // Validate CPF Uniqueness
        if (!empty($data['cpf'])) {
            // Remove non-numeric characters for comparison
            $cpfClean = preg_replace('/[^0-9]/', '', $data['cpf']);
            
            // Check against database (comparing cleaned versions)
            // Note: This assumes DB might store with or without formatting. 
            // Ideally we should store clean, but to be safe we compare clean against clean.
            $checkCpf = $db->prepare("SELECT m.id, c.name as congregation_name 
                                    FROM members m 
                                    LEFT JOIN congregations c ON m.congregation_id = c.id 
                                    WHERE REPLACE(REPLACE(REPLACE(m.cpf, '.', ''), '-', ''), ' ', '') = ?");
            $checkCpf->execute([$cpfClean]);
            $existingMember = $checkCpf->fetch();
            
            if ($existingMember) {
                // CPF already exists
                $congregationName = $existingMember['congregation_name'] ?? 'Congregação não identificada';
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'CPF Duplicado',
                            text: 'Este CPF já está cadastrado para um membro na congregação: " . addslashes($congregationName) . "',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Entendi'
                        }).then((result) => {
                            window.history.back();
                        });
                    });
                </script>";
                return;
            }
        }
        
        // Handle Photo Upload (File or Webcam Base64)
        if (!empty($_POST['webcam_photo'])) {
            // Process Base64 from Webcam
            $data['photo'] = $this->saveBase64Image($_POST['webcam_photo']);
        } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            // Process File Upload
            $uploadDir = __DIR__ . '/../../public/uploads/members/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $data['photo'] = $filename;
            }
        }

        $db = (new Database())->connect();
            $sql = "INSERT INTO members (
                name, email, phone, birth_date, congregation_id, is_baptized, baptism_date,
                gender, cpf, rg, marital_status, address, address_number, neighborhood, complement,
                reference_point, zip_code, state, city, role, nationality, birthplace, father_name,
                mother_name, children_count, profession, church_origin, admission_method, admission_date,
                exit_date, is_tither, is_ebd_teacher, is_new_convert, accepted_jesus_at, reconciled_at, status, photo
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(array_values($data));
        
        // Auto-link existing tithes/offerings with this name
        $newMemberId = $db->lastInsertId();
        $name = $data['name'];
        
        if ($newMemberId && !empty($name)) {
            $stmtLink = $db->prepare("UPDATE tithes SET member_id = ?, giver_name = NULL WHERE member_id IS NULL AND giver_name = ?");
            $stmtLink->execute([$newMemberId, $name]);
        }
        
        if (!empty($_POST['admission_method']) && $_POST['admission_method'] === 'Transferido') {
            $docPath = null;
            if (!empty($_POST['transfer_letter_webcam'])) {
                $docPath = $this->saveBase64Doc($_POST['transfer_letter_webcam']);
            } elseif (isset($_FILES['transfer_letter']) && $_FILES['transfer_letter']['error'] == 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/members_docs/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
                $ext = pathinfo($_FILES['transfer_letter']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('doc_') . '.' . $ext;
                if (move_uploaded_file($_FILES['transfer_letter']['tmp_name'], $uploadDir . $filename)) {
                    $docPath = $filename;
                }
            }
            if ($docPath) {
                $stmtDoc = $db->prepare("INSERT INTO member_documents (member_id, title, type, file_path) VALUES (?, ?, ?, ?)");
                $stmtDoc->execute([$newMemberId, 'Carta de Transferência', 'transfer_letter', $docPath]);
            }
        }

        redirect('/admin/members');
    }

    public function delete($id) {
        requirePermission('members.manage');
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['flash_error'] = 'Apenas administradores podem excluir membros.';
            redirect('/admin/members/show/' . $id);
            return;
        }
        $db = (new Database())->connect();
        
        // Fetch member data
        $stmt = $db->prepare("SELECT photo, name, congregation_id FROM members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();

        if ($member) {
            $db->beginTransaction();
            try {
                // Clear leader references in congregations safely
                $db->prepare("UPDATE congregations SET leader_member_id = NULL, leader_name = NULL WHERE leader_member_id = ?")->execute([$id]);
                if (!empty($member['name']) && !empty($member['congregation_id'])) {
                    $db->prepare("UPDATE congregations SET leader_name = NULL WHERE id = ? AND leader_member_id IS NULL AND LOWER(leader_name) = LOWER(?)")->execute([$member['congregation_id'], $member['name']]);
                }

                // Clear relations that may keep stale references in event/member selectors
                $db->prepare("DELETE FROM event_allowed_members WHERE member_id = ?")->execute([$id]);
                $db->prepare("DELETE FROM event_attendance WHERE member_id = ?")->execute([$id]);
                $db->prepare("DELETE FROM group_members WHERE member_id = ?")->execute([$id]);
                $db->prepare("DELETE FROM user_members WHERE member_id = ?")->execute([$id]);
                $db->prepare("UPDATE users SET member_id = NULL WHERE member_id = ?")->execute([$id]);
                
                // Delete photo if exists
                if (!empty($member['photo'])) {
                    $photoPath = __DIR__ . '/../../public/uploads/members/' . $member['photo'];
                    if (file_exists($photoPath)) {
                        @unlink($photoPath);
                    }
                }
                
                // Delete member
                $stmtDel = $db->prepare("DELETE FROM members WHERE id = ?");
                $stmtDel->execute([$id]);
                
                $db->commit();
                $_SESSION['flash_success'] = 'Membro excluído com sucesso.';
            } catch (Exception $e) {
                $db->rollBack();
                $_SESSION['flash_error'] = 'Não foi possível excluir o membro. Verifique se ainda existem vínculos ativos.';
                redirect('/admin/members/show/' . $id);
                return;
            }
        }
        
        // Redirect back to previous page (to keep filters) or default to list
        $redirect = $_SERVER['HTTP_REFERER'] ?? '/admin/members';
        redirect($redirect);
    }

    public function card($id) {
        requirePermission('members.view');
        $db = (new Database())->connect();
        
        // Fetch Member details with Congregation info
        $stmt = $db->prepare("SELECT m.*, c.name as congregation_name, c.address as congregation_address, c.city as congregation_city, c.state as congregation_state 
                            FROM members m 
                            LEFT JOIN congregations c ON m.congregation_id = c.id 
                            WHERE m.id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();

        if (!$member) {
            redirect('/admin/members');
        }
        
        // Generate unique_id if not exists
        if (empty($member['unique_id'])) {
            $uniqueId = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 7);
            $db->prepare("UPDATE members SET unique_id = ? WHERE id = ?")->execute([$uniqueId, $id]);
            $member['unique_id'] = $uniqueId;
        }

        // Fetch System User info linked to this member (agora pode ser múltiplos ou vindo da tabela nova)
        // Primeiro tenta pela tabela de relacionamento
        $stmtUser = $db->prepare("SELECT u.username, u.role FROM user_members um JOIN users u ON um.user_id = u.id WHERE um.member_id = ?");
        $stmtUser->execute([$id]);
        $systemUser = $stmtUser->fetch();
        
        // Fallback para a coluna antiga se não achar na nova
        if (!$systemUser) {
            $stmtUserOld = $db->prepare("SELECT username, role FROM users WHERE member_id = ?");
            $stmtUserOld->execute([$id]);
            $systemUser = $stmtUserOld->fetch();
        }
        
        // Fetch Signatures (President)
        $signature = $db->query("SELECT * FROM signatures WHERE slug = 'president' OR slug LIKE '%presidente%' LIMIT 1")->fetch();

        view('admin/members/card', ['member' => $member, 'systemUser' => $systemUser, 'signature' => $signature]);
    }

    public function show($id) {
        requirePermission('members.view');
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT m.*, c.name as congregation_name FROM members m LEFT JOIN congregations c ON m.congregation_id = c.id WHERE m.id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$member) {
            redirect('/admin/members');
        }
        $stmtDoc = $db->prepare("SELECT id, title, type, file_path, created_at FROM member_documents WHERE member_id = ? AND type = 'transfer_letter' ORDER BY id DESC LIMIT 1");
        $stmtDoc->execute([$id]);
        $transferLetter = $stmtDoc->fetch(PDO::FETCH_ASSOC);
        view('admin/members/show', ['member' => $member, 'transferLetter' => $transferLetter]);
    }

    private function saveBase64Image($base64String) {
        $uploadDir = __DIR__ . '/../../public/uploads/members/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $image_parts = explode(";base64,", $base64String);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        
        $filename = uniqid() . '.' . $image_type;
        file_put_contents($uploadDir . $filename, $image_base64);
        
        return $filename;
    }
    
    private function saveBase64Doc($base64String) {
        $uploadDir = __DIR__ . '/../../public/uploads/members_docs/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $parts = explode(";base64,", $base64String);
        $typeAux = explode("image/", $parts[0]);
        $ext = isset($typeAux[1]) ? $typeAux[1] : 'jpg';
        $data = base64_decode($parts[1]);
        $filename = uniqid('doc_') . '.' . $ext;
        file_put_contents($uploadDir . $filename, $data);
        return $filename;
    }

    // API Endpoint for autocomplete
    public function listApi() {
        header('Content-Type: application/json');
        
        // Check permission if strict, or allow logged in users
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $db = (new Database())->connect();
            // Select only necessary fields, limit results
            $stmt = $db->query("SELECT id, name FROM members ORDER BY name ASC");
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($members);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function edit($id) {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();

        if (!$member) {
            redirect('/admin/members');
        }
        
        // Check access
        if (!empty($_SESSION['user_congregation_id']) && $member['congregation_id'] != $_SESSION['user_congregation_id']) {
            redirect('/admin/members');
        }

        $sql = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        
        $congregations = $db->query($sql)->fetchAll();
        
        $stmtDocs = $db->prepare("SELECT id, title, type, file_path, created_at FROM member_documents WHERE member_id = ? ORDER BY created_at DESC");
        $stmtDocs->execute([$id]);
        $documents = $stmtDocs->fetchAll();
        
        view('admin/members/edit', ['member' => $member, 'congregations' => $congregations, 'documents' => $documents]);
    }

    public function update($id) {
        requirePermission('members.manage');
        
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'birth_date' => $_POST['birth_date'] ?: null,
            'congregation_id' => $_POST['congregation_id'],
            'is_baptized' => isset($_POST['is_baptized']) ? 1 : 0,
            'baptism_date' => $_POST['baptism_date'] ?: null,
            'gender' => $_POST['gender'] ?? null,
            'cpf' => $_POST['cpf'] ?? null,
            'rg' => $_POST['rg'] ?? null,
            'marital_status' => $_POST['marital_status'] ?? null,
            'address' => $_POST['address'] ?? null,
            'address_number' => $_POST['address_number'] ?? null,
            'neighborhood' => $_POST['neighborhood'] ?? null,
            'complement' => $_POST['complement'] ?? null,
            'reference_point' => $_POST['reference_point'] ?? null,
            'zip_code' => $_POST['zip_code'] ?? null,
            'state' => $_POST['state'] ?? null,
            'city' => $_POST['city'] ?? null,
            'role' => $_POST['role'] ?? null,
            'nationality' => $_POST['nationality'] ?? 'Brasileira',
            'birthplace' => $_POST['birthplace'] ?? null,
            'father_name' => $_POST['father_name'] ?? null,
            'mother_name' => $_POST['mother_name'] ?? null,
            'children_count' => (int)($_POST['children_count'] ?? 0),
            'profession' => $_POST['profession'] ?? null,
            'church_origin' => $_POST['church_origin'] ?? null,
            'admission_method' => $_POST['admission_method'] ?? null,
            'admission_date' => $_POST['admission_date'] ?: null,
            'exit_date' => $_POST['exit_date'] ?: null,
            'is_tither' => isset($_POST['is_tither']) ? 1 : 0,
            'is_ebd_teacher' => isset($_POST['is_ebd_teacher']) ? 1 : 0,
            'is_new_convert' => isset($_POST['is_new_convert']) ? 1 : 0,
            'accepted_jesus_at' => $_POST['accepted_jesus_at'] ?: null,
            'reconciled_at' => $_POST['reconciled_at'] ?: null,
            // 'is_founder' removed temporarily
            'status' => $_POST['status'] ?? 'active',
            'id' => $id
        ];
        
        $photoUpdateSql = "";
        $photoParams = [];
        $removePhoto = isset($_POST['remove_photo']);
        if ($removePhoto) {
            $dbTmp = (new Database())->connect();
            $stmtCur = $dbTmp->prepare("SELECT photo FROM members WHERE id = ?");
            $stmtCur->execute([$id]);
            $cur = $stmtCur->fetch();
            if (!empty($cur['photo'])) {
                $photoPath = __DIR__ . '/../../public/uploads/members/' . $cur['photo'];
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
            $photoUpdateSql = ", photo=NULL";
        } elseif (!empty($_POST['webcam_photo'])) {
            $filename = $this->saveBase64Image($_POST['webcam_photo']);
            $photoUpdateSql = ", photo=?";
            $photoParams[] = $filename;
        } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $uploadDir = __DIR__ . '/../../public/uploads/members/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $photoUpdateSql = ", photo=?";
                $photoParams[] = $filename;
            }
        }

        $db = (new Database())->connect();
        
        // Validate CPF Uniqueness (excluding current member)
        if (!empty($data['cpf'])) {
            // Remove non-numeric characters for comparison
            $cpfClean = preg_replace('/[^0-9]/', '', $data['cpf']);
            
            $checkCpf = $db->prepare("SELECT m.id, c.name as congregation_name 
                                    FROM members m 
                                    LEFT JOIN congregations c ON m.congregation_id = c.id 
                                    WHERE REPLACE(REPLACE(REPLACE(m.cpf, '.', ''), '-', ''), ' ', '') = ? 
                                    AND m.id != ?");
            $checkCpf->execute([$cpfClean, $id]);
            $existingMember = $checkCpf->fetch();
            
            if ($existingMember) {
                $congregationName = $existingMember['congregation_name'] ?? 'Congregação não identificada';
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'CPF Duplicado',
                            text: 'Este CPF já está cadastrado para um membro na congregação: " . addslashes($congregationName) . "',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Entendi'
                        }).then((result) => {
                            window.history.back();
                        });
                    });
                </script>";
                return;
            }
        }
        
        $sql = "UPDATE members SET 
            name=?, email=?, phone=?, birth_date=?, congregation_id=?, is_baptized=?, baptism_date=?,
            gender=?, cpf=?, rg=?, marital_status=?, address=?, address_number=?, neighborhood=?, complement=?,
            reference_point=?, zip_code=?, state=?, city=?, role=?, nationality=?, birthplace=?, father_name=?,
            mother_name=?, children_count=?, profession=?, church_origin=?, admission_method=?, admission_date=?,
            exit_date=?, is_tither=?, is_ebd_teacher=?, is_new_convert=?, accepted_jesus_at=?, reconciled_at=?, status=? $photoUpdateSql
            WHERE id=?";
            
        // $data has 'id' at the end, so we take everything except the last element (id)
        $dataValues = array_values($data);
        array_pop($dataValues); // Remove id
        
        // Merge: data values + photo params (if any) + id
        $params = array_merge($dataValues, $photoParams, [$id]);
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        // Update tithes names if changed
        if (!empty($data['name'])) {
            $db->prepare("UPDATE tithes SET giver_name = ? WHERE member_id = ?")->execute([$data['name'], $id]);
        }
        
        if (!empty($_POST['admission_method']) && $_POST['admission_method'] === 'Transferido') {
            $docPath = null;
            if (!empty($_POST['transfer_letter_webcam'])) {
                $docPath = $this->saveBase64Doc($_POST['transfer_letter_webcam']);
            } elseif (isset($_FILES['transfer_letter']) && $_FILES['transfer_letter']['error'] == 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/members_docs/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
                $ext = pathinfo($_FILES['transfer_letter']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('doc_') . '.' . $ext;
                if (move_uploaded_file($_FILES['transfer_letter']['tmp_name'], $uploadDir . $filename)) {
                    $docPath = $filename;
                }
            }
            if ($docPath) {
                $stmtCheck = $db->prepare("SELECT id, file_path FROM member_documents WHERE member_id = ? AND type = 'transfer_letter' ORDER BY id DESC LIMIT 1");
                $stmtCheck->execute([$id]);
                $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    $oldPath = __DIR__ . '/../../public/uploads/members_docs/' . $existing['file_path'];
                    if (is_file($oldPath)) { @unlink($oldPath); }
                    $upd = $db->prepare("UPDATE member_documents SET title = ?, file_path = ? WHERE id = ?");
                    $upd->execute(['Carta de Transferência', $docPath, $existing['id']]);
                } else {
                    $stmtDoc = $db->prepare("INSERT INTO member_documents (member_id, title, type, file_path) VALUES (?, ?, ?, ?)");
                    $stmtDoc->execute([$id, 'Carta de Transferência', 'transfer_letter', $docPath]);
                }
            }
        }

        // Verificar se o status mudou para algo que não seja 'Congregando'
        if ($data['status'] !== 'Congregando') {
            // Verificar se o membro é Líder ou Anfitrião de algum grupo
            $checkLeader = $db->prepare("SELECT id, name FROM `groups` WHERE leader_id = ? OR host_id = ?");
            $checkLeader->execute([$id, $id]);
            $groupsAffected = $checkLeader->fetchAll();

            if ($groupsAffected) {
                // Remover liderança/hospedagem
                // Usando lógica compatível: setar NULL onde for igual ao ID
                $stmtRemove = $db->prepare("UPDATE `groups` SET leader_id = CASE WHEN leader_id = ? THEN NULL ELSE leader_id END, host_id = CASE WHEN host_id = ? THEN NULL ELSE host_id END WHERE leader_id = ? OR host_id = ?");
                $stmtRemove->execute([$id, $id, $id, $id]);
                
                // Também remover da lista de membros do grupo (group_members) para garantir consistência
                $stmtRemoveMember = $db->prepare("DELETE FROM group_members WHERE member_id = ?");
                $stmtRemoveMember->execute([$id]);

                // Preparar mensagem de aviso
                $msg = "Atenção: O membro foi removido da liderança/hospedagem dos seguintes grupos: ";
                $groupNames = array_column($groupsAffected, 'name');
                $msg .= implode(", ", $groupNames) . ". Por favor, selecione novos líderes.";
                
                redirect('/admin/members/show/' . $id . '?warning=' . urlencode($msg));
                return;
            }
        }

        redirect('/admin/members/show/' . $id);
    }
    
    public function deleteDocument($docId) {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT id, member_id, file_path FROM member_documents WHERE id = ?");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch();
        if ($doc) {
            $file = __DIR__ . '/../../public/uploads/members_docs/' . $doc['file_path'];
            if (!empty($doc['file_path']) && file_exists($file)) {
                @unlink($file);
            }
            $del = $db->prepare("DELETE FROM member_documents WHERE id = ?");
            $del->execute([$docId]);
            $memberId = $doc['member_id'];
            redirect('/admin/members/edit/' . $memberId);
        } else {
            redirect('/admin/members');
        }
    }
    
    public function infoApi($id) {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        try {
            $db = (new Database())->connect();
            $stmt = $db->prepare("SELECT m.id, m.name, m.congregation_id, c.name as congregation_name FROM members m LEFT JOIN congregations c ON m.congregation_id = c.id WHERE m.id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                http_response_code(404);
                echo json_encode(['error' => 'Not found']);
                return;
            }
            echo json_encode($row);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function history($memberId) {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        
        $stmtM = $db->prepare("SELECT id, name, congregation_id FROM members WHERE id = ?");
        $stmtM->execute([$memberId]);
        $member = $stmtM->fetch();
        if (!$member) {
            redirect('/admin/members');
        }
        if (!empty($_SESSION['user_congregation_id']) && $member['congregation_id'] != $_SESSION['user_congregation_id']) {
            redirect('/admin/members');
        }
        
        $stmt = $db->prepare("SELECT h.*, u.username 
                              FROM member_history h 
                              LEFT JOIN users u ON h.user_id = u.id 
                              WHERE h.member_id = ? 
                              ORDER BY h.created_at DESC, h.id DESC");
        $stmt->execute([$memberId]);
        $items = $stmt->fetchAll();
        
        view('admin/members/history', ['member' => $member, 'items' => $items]);
    }
    
    public function historyStore($memberId) {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        
        $stmtM = $db->prepare("SELECT id, congregation_id FROM members WHERE id = ?");
        $stmtM->execute([$memberId]);
        $member = $stmtM->fetch();
        if (!$member) {
            redirect('/admin/members');
        }
        if (!empty($_SESSION['user_congregation_id']) && $member['congregation_id'] != $_SESSION['user_congregation_id']) {
            redirect('/admin/members');
        }
        
        $category = $_POST['category'] ?? 'Observação';
        $note = trim($_POST['note'] ?? '');
        if ($note === '') {
            $_SESSION['error'] = 'Preencha a observação.';
            redirect('/admin/members/history/' . $memberId);
            return;
        }
        $userId = $_SESSION['user_id'] ?? 0;
        $stmt = $db->prepare("INSERT INTO member_history (member_id, user_id, category, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([$memberId, $userId, $category, $note]);
        
        redirect('/admin/members/history/' . $memberId);
    }
    
    public function historyDelete($id) {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT id, member_id FROM member_history WHERE id = ?");
        $stmt->execute([$id]);
        $hist = $stmt->fetch();
        if ($hist) {
            $memberId = $hist['member_id'];
            $db->prepare("DELETE FROM member_history WHERE id = ?")->execute([$id]);
            redirect('/admin/members/history/' . $memberId);
        } else {
            redirect('/admin/members');
        }
    }
    
    public function historyUpdate($id) {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT id, member_id FROM member_history WHERE id = ?");
        $stmt->execute([$id]);
        $hist = $stmt->fetch();
        if (!$hist) {
            redirect('/admin/members');
        }
        $memberId = $hist['member_id'];
        $stmtM = $db->prepare("SELECT congregation_id FROM members WHERE id = ?");
        $stmtM->execute([$memberId]);
        $member = $stmtM->fetch();
        if (!empty($_SESSION['user_congregation_id']) && $member && $member['congregation_id'] != $_SESSION['user_congregation_id']) {
            redirect('/admin/members');
        }
        $category = $_POST['category'] ?? 'Observação';
        $note = trim($_POST['note'] ?? '');
        if ($note === '') {
            $_SESSION['error'] = 'Preencha a observação.';
            redirect('/admin/members/history/' . $memberId);
            return;
        }
        $upd = $db->prepare("UPDATE member_history SET category = ?, note = ? WHERE id = ?");
        $upd->execute([$category, $note, $id]);
        redirect('/admin/members/history/' . $memberId);
    }
    
    public function historySeed() {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        $userId = $_SESSION['user_id'] ?? null;
        $categories = ['Observação','Atendimento Pastoral','Participação','Disciplina','Financeiro','Saúde','Família','Ministério'];
        $notes = [
            'Participação constante em cultos e EBD.',
            'Necessita acompanhamento pastoral.',
            'Apoia atividades do ministério de louvor.',
            'Passou por tratamento de saúde recente.',
            'Contribuinte frequente e engajado.',
            'Dificuldades familiares em andamento.',
            'Disponível para servir em eventos.',
            'Evolução espiritual perceptível.'
        ];
        $limit = 10;
        $stmtMembers = $db->prepare("SELECT id FROM members ORDER BY id ASC LIMIT ?");
        $stmtMembers->bindValue(1, $limit, PDO::PARAM_INT);
        $stmtMembers->execute();
        $members = $stmtMembers->fetchAll(PDO::FETCH_COLUMN);
        $ins = $db->prepare("INSERT INTO member_history (member_id, user_id, category, note) VALUES (?, ?, ?, ?)");
        foreach ($members as $mid) {
            $count = rand(2, 4);
            for ($i = 0; $i < $count; $i++) {
                $cat = $categories[array_rand($categories)];
                $note = $notes[array_rand($notes)];
                $ins->execute([$mid, $userId, $cat, $note]);
            }
        }
        redirect('/admin/members');
    }
    
    public function historySeedFor($memberId) {
        requirePermission('members.manage');
        $db = (new Database())->connect();
        $stmtM = $db->prepare("SELECT id FROM members WHERE id = ?");
        $stmtM->execute([$memberId]);
        $m = $stmtM->fetch();
        if (!$m) {
            redirect('/admin/members');
        }
        $userId = $_SESSION['user_id'] ?? null;
        $categories = ['Observação','Atendimento Pastoral','Participação','Disciplina','Financeiro','Saúde','Família','Ministério'];
        $notes = [
            'Participação constante em cultos e EBD.',
            'Necessita acompanhamento pastoral.',
            'Apoia atividades do ministério de louvor.',
            'Passou por tratamento de saúde recente.',
            'Contribuinte frequente e engajado.',
            'Dificuldades familiares em andamento.',
            'Disponível para servir em eventos.',
            'Evolução espiritual perceptível.'
        ];
        $ins = $db->prepare("INSERT INTO member_history (member_id, user_id, category, note) VALUES (?, ?, ?, ?)");
        $count = rand(3, 5);
        for ($i = 0; $i < $count; $i++) {
            $cat = $categories[array_rand($categories)];
            $note = $notes[array_rand($notes)];
            $ins->execute([$memberId, $userId, $cat, $note]);
        }
        redirect('/admin/members/history/' . $memberId);
    }
}

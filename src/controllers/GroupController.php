<?php
// src/controllers/GroupController.php

class GroupController {
    
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
        requirePermission('groups.view');
        $db = (new Database())->connect();
        
        // Filtros (Congregação, Busca)
        $congregation_id = $_GET['congregation_id'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $sql = "SELECT g.*, 
                c.name as congregation_name, 
                m.name as leader_name,
                (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as total_members
                FROM `groups` g 
                LEFT JOIN congregations c ON g.congregation_id = c.id 
                LEFT JOIN members m ON g.leader_id = m.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($congregation_id) {
            $sql .= " AND g.congregation_id = ?";
            $params[] = $congregation_id;
        }
        
        if ($search) {
            $sql .= " AND (g.name LIKE ? OR m.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY g.name ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $groups = $stmt->fetchAll();
        
        // Congregations for filter
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name")->fetchAll();
        
        view('admin/groups/index', ['groups' => $groups, 'congregations' => $congregations]);
    }

    public function create() {
        requirePermission('groups.manage');
        $db = (new Database())->connect();
        
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name")->fetchAll();
        
        // Buscar membros potenciais para liderança (excluir quem já tem grupo e inativos)
        $sqlM = "SELECT m.id, m.name, m.congregation_id, c.name as congregation_name 
                 FROM members m 
                 LEFT JOIN congregations c ON m.congregation_id = c.id 
                 WHERE (m.status = 'Congregando' OR m.status = 'active')
                 AND m.id NOT IN (SELECT member_id FROM group_members WHERE member_id IS NOT NULL)
                 AND m.id NOT IN (SELECT leader_id FROM `groups` WHERE leader_id IS NOT NULL)
                 AND m.id NOT IN (SELECT host_id FROM `groups` WHERE host_id IS NOT NULL)
                 ORDER BY m.name";
                 
        $members = $db->query($sqlM)->fetchAll();
        
        view('admin/groups/create', ['congregations' => $congregations, 'members' => $members]);
    }

    public function store() {
        requirePermission('groups.manage');
        
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $leader_id = !empty($_POST['leader_id']) ? $_POST['leader_id'] : null;
        $address = $_POST['address'] ?? '';
        $meeting_day = $_POST['meeting_day'] ?? '';
        $meeting_time = !empty($_POST['meeting_time']) ? $_POST['meeting_time'] : null;
        $congregation_id = !empty($_POST['congregation_id']) ? $_POST['congregation_id'] : null;
        
        $host_input = trim($_POST['host_name'] ?? '');
        $host_id = null;
        $host_name = null;
        
        $db = (new Database())->connect();

        // Verificar se o host_input corresponde a algum membro existente
        if (!empty($host_input)) {
            $stmtHost = $db->prepare("SELECT id FROM members WHERE name = ? LIMIT 1");
            $stmtHost->execute([$host_input]);
            $found_host_id = $stmtHost->fetchColumn();
            
            if ($found_host_id) {
                $host_id = $found_host_id;
            } else {
                $host_name = $host_input;
            }
        }

        $hasHostNameColumn = $this->tableHasColumn($db, 'groups', 'host_name');
        if ($hasHostNameColumn) {
            $stmt = $db->prepare("INSERT INTO `groups` (name, description, leader_id, host_id, host_name, address, meeting_day, meeting_time, congregation_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $leader_id, $host_id, $host_name, $address, $meeting_day, $meeting_time, $congregation_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO `groups` (name, description, leader_id, host_id, address, meeting_day, meeting_time, congregation_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $leader_id, $host_id, $address, $meeting_day, $meeting_time, $congregation_id]);
        }
        
        $group_id = $db->lastInsertId();
        
        // Inserir líder e anfitrião como membros do grupo imediatamente
        $joined_at = date('Y-m-d');
        if ($leader_id) {
            $db->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, 'leader', ?)")->execute([$group_id, $leader_id, $joined_at]);
        }
        if ($host_id && $host_id != $leader_id) {
            $db->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, 'host', ?)")->execute([$group_id, $host_id, $joined_at]);
        }
        
        redirect('/admin/groups');
    }

    public function show($id) {
        requirePermission('groups.view');
        $db = (new Database())->connect();
        
        // Dados do Grupo
        $stmt = $db->prepare("SELECT g.*, c.name as congregation_name, l.name as leader_name, 
                              COALESCE(h.name, g.host_name) as host_name_display 
                              FROM `groups` g 
                              LEFT JOIN congregations c ON g.congregation_id = c.id 
                              LEFT JOIN members l ON g.leader_id = l.id 
                              LEFT JOIN members h ON g.host_id = h.id 
                              WHERE g.id = ?");
        $stmt->execute([$id]);
        $group = $stmt->fetch();
        
        if (!$group) redirect('/admin/groups');
        
        // Membros do Grupo
        $stmtM = $db->prepare("SELECT gm.*, m.name, m.phone, m.email, m.is_new_convert, m.accepted_jesus_at, m.reconciled_at 
                               FROM group_members gm 
                               JOIN members m ON gm.member_id = m.id 
                               WHERE gm.group_id = ? 
                               ORDER BY gm.role DESC, m.name ASC"); // role DESC puts 'host'/'leader' usually first if alphabetical, but enum order matters
        $stmtM->execute([$id]);
        $members = $stmtM->fetchAll();
        
        // Membros disponíveis para adicionar (não estão no grupo)
        // Traz todos os membros ativos, independente da congregação, mas com info da congregação
        // FIX: Excluir membros que JÁ estão em QUALQUER grupo
        $sqlAvail = "SELECT m.id, m.name, m.congregation_id, c.name as congregation_name 
                     FROM members m 
                     LEFT JOIN congregations c ON m.congregation_id = c.id
                     WHERE (m.status = 'Congregando' OR m.status = 'active')
                     AND m.id NOT IN (SELECT member_id FROM group_members WHERE member_id IS NOT NULL)
                     AND m.id NOT IN (SELECT leader_id FROM `groups` WHERE leader_id IS NOT NULL)
                     AND m.id NOT IN (SELECT host_id FROM `groups` WHERE host_id IS NOT NULL) 
                     ORDER BY m.name ASC";
        
        $stmtAvail = $db->prepare($sqlAvail);
        $stmtAvail->execute(); // No params needed now
        $available_members = $stmtAvail->fetchAll();
        
        // Buscar todos os grupos para o modal de transferência (exceto o atual)
        $all_groups = $db->query("SELECT id, name FROM `groups` WHERE id != $id ORDER BY name ASC")->fetchAll();
        
        view('admin/groups/show', [
            'group' => $group, 
            'members' => $members, 
            'available_members' => $available_members,
            'all_groups' => $all_groups
        ]);
    }

    public function edit($id) {
        requirePermission('groups.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM `groups` WHERE id = ?");
        $stmt->execute([$id]);
        $group = $stmt->fetch();
        
        if (!$group) redirect('/admin/groups');
        
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name")->fetchAll();
        
        // Buscar membros potenciais (excluir quem já tem OUTRO grupo)
        $sqlM = "SELECT m.id, m.name, m.congregation_id, c.name as congregation_name 
                 FROM members m 
                 LEFT JOIN congregations c ON m.congregation_id = c.id 
                 WHERE m.status = 'Congregando'
                 AND m.id NOT IN (SELECT member_id FROM group_members WHERE group_id != ? AND member_id IS NOT NULL)
                 AND m.id NOT IN (SELECT leader_id FROM `groups` WHERE id != ? AND leader_id IS NOT NULL)
                 AND m.id NOT IN (SELECT host_id FROM `groups` WHERE id != ? AND host_id IS NOT NULL)
                 ORDER BY m.name";
                 
        $stmtM = $db->prepare($sqlM);
        $stmtM->execute([$id, $id, $id]);
        $members = $stmtM->fetchAll();
        
        // IDs dos membros atuais para evitar que o filtro de congregação os esconda na edição
        $currentMemberIds = $db->query("SELECT member_id FROM group_members WHERE group_id = $id")->fetchAll(PDO::FETCH_COLUMN);

        view('admin/groups/edit', [
            'group' => $group, 
            'congregations' => $congregations, 
            'members' => $members,
            'currentMemberIds' => $currentMemberIds
        ]);
    }

    public function update($id) {
        requirePermission('groups.manage');
        
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $leader_id = !empty($_POST['leader_id']) ? $_POST['leader_id'] : null;
        $address = $_POST['address'] ?? '';
        $meeting_day = $_POST['meeting_day'] ?? '';
        $meeting_time = !empty($_POST['meeting_time']) ? $_POST['meeting_time'] : null;
        $congregation_id = !empty($_POST['congregation_id']) ? $_POST['congregation_id'] : null;
        
        $host_input = trim($_POST['host_name'] ?? '');
        $host_id = null;
        $host_name = null;

        $db = (new Database())->connect();

        // Verificar se o host_input corresponde a algum membro existente
        if (!empty($host_input)) {
            $stmtHost = $db->prepare("SELECT id FROM members WHERE name = ? LIMIT 1");
            $stmtHost->execute([$host_input]);
            $found_host_id = $stmtHost->fetchColumn();
            
            if ($found_host_id) {
                $host_id = $found_host_id;
            } else {
                $host_name = $host_input;
            }
        }
        
        // Obter dados antigos para comparação e sincronização de roles
        $stmtOld = $db->prepare("SELECT leader_id, host_id FROM `groups` WHERE id = ?");
        $stmtOld->execute([$id]);
        $oldGroup = $stmtOld->fetch();

        $hasHostNameColumn = $this->tableHasColumn($db, 'groups', 'host_name');
        if ($hasHostNameColumn) {
            $stmt = $db->prepare("UPDATE `groups` SET name=?, description=?, leader_id=?, host_id=?, host_name=?, address=?, meeting_day=?, meeting_time=?, congregation_id=? WHERE id=?");
            $stmt->execute([$name, $description, $leader_id, $host_id, $host_name, $address, $meeting_day, $meeting_time, $congregation_id, $id]);
        } else {
            $stmt = $db->prepare("UPDATE `groups` SET name=?, description=?, leader_id=?, host_id=?, address=?, meeting_day=?, meeting_time=?, congregation_id=? WHERE id=?");
            $stmt->execute([$name, $description, $leader_id, $host_id, $address, $meeting_day, $meeting_time, $congregation_id, $id]);
        }
        
        // Sincronizar Role de Líder
        if ($leader_id != $oldGroup['leader_id']) {
            // Rebaixar antigo líder
            if ($oldGroup['leader_id']) {
                // Se ele virou o novo anfitrião, atualiza para 'host'
                if ($oldGroup['leader_id'] == $host_id) {
                     $db->prepare("UPDATE group_members SET role = 'host' WHERE group_id = ? AND member_id = ?")->execute([$id, $oldGroup['leader_id']]);
                } else {
                     // Senão vira membro comum
                     $db->prepare("UPDATE group_members SET role = 'member' WHERE group_id = ? AND member_id = ?")->execute([$id, $oldGroup['leader_id']]);
                }
            }
            
            // Promover novo líder
            if ($leader_id) {
                $check = $db->prepare("SELECT 1 FROM group_members WHERE group_id = ? AND member_id = ?");
                $check->execute([$id, $leader_id]);
                if ($check->fetch()) {
                    $db->prepare("UPDATE group_members SET role = 'leader' WHERE group_id = ? AND member_id = ?")->execute([$id, $leader_id]);
                } else {
                    $joined_at = date('Y-m-d');
                    $db->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, 'leader', ?)")->execute([$id, $leader_id, $joined_at]);
                }
            }
        }
        
        // Sincronizar Role de Anfitrião
        if ($host_id != $oldGroup['host_id']) {
            // Rebaixar antigo anfitrião (se ele não for o novo líder)
            if ($oldGroup['host_id'] && $oldGroup['host_id'] != $leader_id) {
                 $db->prepare("UPDATE group_members SET role = 'member' WHERE group_id = ? AND member_id = ?")->execute([$id, $oldGroup['host_id']]);
            }
            
            // Promover novo anfitrião (se não for líder, pois líder tem precedência de role)
            if ($host_id && $host_id != $leader_id) {
                 $check = $db->prepare("SELECT 1 FROM group_members WHERE group_id = ? AND member_id = ?");
                 $check->execute([$id, $host_id]);
                 if ($check->fetch()) {
                     $db->prepare("UPDATE group_members SET role = 'host' WHERE group_id = ? AND member_id = ?")->execute([$id, $host_id]);
                 } else {
                     $joined_at = date('Y-m-d');
                     $db->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, 'host', ?)")->execute([$id, $host_id, $joined_at]);
                 }
            }
        }
        
        redirect("/admin/groups/show/$id");
    }

    public function delete($id) {
        requirePermission('groups.manage');
        $db = (new Database())->connect();
        
        // Remove members first manually to ensure they are freed up
        // (SQLite constraints might not be enabled or reliable for cleanup)
        $db->prepare("DELETE FROM group_members WHERE group_id = ?")->execute([$id]);
        
        $stmt = $db->prepare("DELETE FROM `groups` WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect('/admin/groups');
    }

    // --- Membership Logic ---

    public function addMember() {
        requirePermission('groups.manage');
        $group_id = $_POST['group_id'];
        $member_id = $_POST['member_id'];
        $member_name = $_POST['member_name'] ?? '';
        $role = $_POST['role'] ?? 'member';
        $joined_at = date('Y-m-d');
        
        $db = (new Database())->connect();
        
        // Se member_id estiver vazio, mas tiver nome, cria novo membro visitante
         if (empty($member_id) && !empty($member_name)) {
             // Buscar congregation_id do grupo para atribuir ao novo membro visitante
             $stmtG = $db->prepare("SELECT congregation_id FROM `groups` WHERE id = ?");
             $stmtG->execute([$group_id]);
             $groupInfo = $stmtG->fetch();
             $congregation_id = $groupInfo['congregation_id'] ?? null;

             // Cria novo membro básico
             $stmt = $db->prepare("INSERT INTO members (name, status, admission_date, congregation_id) VALUES (?, 'Congregando', ?, ?)");
             $stmt->execute([$member_name, $joined_at, $congregation_id]);
             $member_id = $db->lastInsertId();
             
             // Novos criados por aqui são sempre visitantes
             $role = 'visitor';
         }
        
        if (empty($member_id)) {
             redirect("/admin/groups/show/$group_id?error=member_required");
             return;
        }
        
        // Validate: Check if member is already in ANY group
        $check = $db->prepare("SELECT group_id FROM group_members WHERE member_id = ?");
        $check->execute([$member_id]);
        if ($check->fetch()) {
             redirect("/admin/groups/show/$group_id?error=already_in_group");
             return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$group_id, $member_id, $role, $joined_at]);
        } catch (Exception $e) {
            // Duplicate entry ignored or handled
        }
        
        redirect("/admin/groups/show/$group_id");
    }

    public function removeMember() {
        requirePermission('groups.manage');
        $group_id = $_POST['group_id'];
        $member_id = $_POST['member_id'];
        
        $db = (new Database())->connect();
        $stmt = $db->prepare("DELETE FROM group_members WHERE group_id = ? AND member_id = ?");
        $stmt->execute([$group_id, $member_id]);
        
        redirect("/admin/groups/show/$group_id");
    }

    public function transferMember() {
        requirePermission('groups.manage');
        
        $member_id = $_POST['member_id'];
        $from_group_id = $_POST['from_group_id'];
        $to_group_id = $_POST['to_group_id'];
        $role = $_POST['role'] ?? 'member';
        $joined_at = date('Y-m-d');
        
        $db = (new Database())->connect();
        
        try {
            $db->beginTransaction();
            
            // 1. Remove from old group
            $stmt = $db->prepare("DELETE FROM group_members WHERE group_id = ? AND member_id = ?");
            $stmt->execute([$from_group_id, $member_id]);
            
            // 2. Add to new group
            $stmt = $db->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$to_group_id, $member_id, $role, $joined_at]);
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }
        
        redirect("/admin/groups/show/$from_group_id?success=transferred");
    }

    public function convertVisitor() {
        requirePermission('groups.manage');
        
        $group_id = $_POST['group_id'];
        $member_id = $_POST['member_id'];
        $conversion_type = $_POST['conversion_type'];
        $date = $_POST['date'] ?: date('Y-m-d');
        
        $db = (new Database())->connect();
        
        try {
            $db->beginTransaction();
            
            // 1. Update role in group_members to 'member'
            $stmt = $db->prepare("UPDATE group_members SET role = 'member' WHERE group_id = ? AND member_id = ?");
            $stmt->execute([$group_id, $member_id]);
            
            // 2. Update member details based on conversion type
            if ($conversion_type === 'accepted_jesus') {
                $stmtM = $db->prepare("UPDATE members SET is_new_convert = 1, accepted_jesus_at = ? WHERE id = ?");
                $stmtM->execute([$date, $member_id]);
            } elseif ($conversion_type === 'reconciled') {
                $stmtM = $db->prepare("UPDATE members SET reconciled_at = ? WHERE id = ?");
                $stmtM->execute([$date, $member_id]);
            }
            // 'became_member' just changes role, no extra member updates needed
            
            $db->commit();
            redirect("/admin/groups/show/$group_id?success=converted");
        } catch (Exception $e) {
            $db->rollBack();
            redirect("/admin/groups/show/$group_id?error=conversion_failed");
        }
    }

    public function report($id) {
        requirePermission('groups.view');
        $db = (new Database())->connect();
        
        // Dados do Grupo
        $stmt = $db->prepare("SELECT g.*, c.name as congregation_name, l.name as leader_name 
                              FROM `groups` g 
                              LEFT JOIN congregations c ON g.congregation_id = c.id 
                              LEFT JOIN members l ON g.leader_id = l.id 
                              WHERE g.id = ?");
        $stmt->execute([$id]);
        $group = $stmt->fetch();
        
        if (!$group) redirect('/admin/groups');
        
        // Membros e Status
        $stmtM = $db->prepare("SELECT gm.*, m.name, m.phone, m.is_new_convert, m.accepted_jesus_at, m.reconciled_at 
                               FROM group_members gm 
                               JOIN members m ON gm.member_id = m.id 
                               WHERE gm.group_id = ? 
                               ORDER BY m.name ASC");
        $stmtM->execute([$id]);
        $members = $stmtM->fetchAll();
        
        // Stats
        $stats = [
            'total' => count($members),
            'new_converts' => 0,
            'accepted_jesus' => 0,
            'reconciled' => 0
        ];
        
        foreach ($members as $m) {
            if ($m['is_new_convert']) $stats['new_converts']++;
            if ($m['accepted_jesus_at']) $stats['accepted_jesus']++;
            if ($m['reconciled_at']) $stats['reconciled']++;
        }
        
        view('admin/groups/report', ['group' => $group, 'members' => $members, 'stats' => $stats]);
    }
}

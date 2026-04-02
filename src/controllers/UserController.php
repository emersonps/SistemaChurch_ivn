<?php
// src/controllers/UserController.php

class UserController {
    public function index() {
        requirePermission('users.manage');
        $db = (new Database())->connect();
        
        $whereClause = "";
        if (($_SESSION['user_role'] ?? '') !== 'developer') {
            $whereClause = "WHERE u.role != 'developer'";
        }

        // Exclude 'developer' role from the list if not a developer
        $users = $db->query("
            SELECT u.*, 
                   (SELECT GROUP_CONCAT(m.name SEPARATOR ', ') 
                    FROM user_members um 
                    JOIN members m ON um.member_id = m.id 
                    WHERE um.user_id = u.id) as linked_members 
            FROM users u 
            $whereClause 
            ORDER BY u.username ASC
        ")->fetchAll();
        
        view('admin/users/index', ['users' => $users]);
    }

    // AJAX Endpoint: Get members by congregation
    public function getMembersByCongregation($congregationId) {
        // Allow access to logged users (or refine permission)
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $db = (new Database())->connect();
        
        if ($congregationId === 'all' || empty($congregationId)) {
            $stmt = $db->query("SELECT id, name FROM members ORDER BY name ASC");
        } else {
            $stmt = $db->prepare("SELECT id, name FROM members WHERE congregation_id = ? ORDER BY name ASC");
            $stmt->execute([$congregationId]);
        }
        
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($members);
        exit;
    }

    public function create() {
        requirePermission('users.manage');
        $db = (new Database())->connect();
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        $permissions = $db->query("SELECT * FROM permissions ORDER BY label ASC")->fetchAll();
        if (($_SESSION['user_role'] ?? '') !== 'developer') {
            $permissions = array_values(array_filter($permissions, function($p) {
                return ($p['slug'] ?? '') !== 'developer.access';
            }));
        }
        $members = $db->query("SELECT id, name FROM members ORDER BY name ASC")->fetchAll();
        $rbac = require __DIR__ . '/../../config/rbac.php';
        
        $roles = $rbac['roles'];
        if (($_SESSION['user_role'] ?? '') !== 'developer') {
            unset($roles['developer']);
        }

        $permissionGroups = buildPermissionGroups($permissions);
        $isDeveloperEditor = ($_SESSION['user_role'] ?? '') === 'developer';

        view('admin/users/create', [
            'roles' => $roles,
            'congregations' => $congregations,
            'permissions' => $permissions,
            'permissionGroups' => $permissionGroups,
            'isDeveloperEditor' => $isDeveloperEditor,
            'adminEditablePermissions' => getAdminEditablePermissionSlugs(),
            'members' => $members
        ]);
    }

    public function store() {
        requirePermission('users.manage');
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        $congregation_id = !empty($_POST['congregation_id']) ? $_POST['congregation_id'] : null;
        // member_id agora vem como array de IDs da tabela dinâmica
        $member_ids = !empty($_POST['member_ids']) ? json_decode($_POST['member_ids'], true) : [];
        $custom_permissions = normalizePermissionSelection(isset($_POST['permissions']) ? $_POST['permissions'] : []);
        $permission_mode = $_POST['permission_mode'] ?? 'additive';
        $isDeveloperEditor = ($_SESSION['user_role'] ?? '') === 'developer';

        if (empty($username) || empty($password) || empty($role)) {
            redirect('/admin/users/create');
        }

        // Security: Somente developer pode criar usuário com perfil developer
        if ($role === 'developer' && ($_SESSION['user_role'] ?? '') !== 'developer') {
            redirect('/admin/users/create');
        }

        if (!$isDeveloperEditor) {
            $permission_mode = 'additive';
            $custom_permissions = array_values(array_intersect($custom_permissions, getAdminEditablePermissionSlugs()));
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $db = (new Database())->connect();
        
        // Check if username exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            redirect('/admin/users/create');
        }

        try {
            $db->beginTransaction();
            // Mantém member_id na tabela users como fallback (o primeiro selecionado) ou null
            $primary_member_id = !empty($member_ids) ? $member_ids[0] : null;
            
            $stmt = $db->prepare("INSERT INTO users (username, password, role, congregation_id, member_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $role, $congregation_id, $primary_member_id]);
            $userId = $db->lastInsertId();
            
            // Save user_members relationship
            if (!empty($member_ids)) {
                $stmtMember = $db->prepare("INSERT INTO user_members (user_id, member_id) VALUES (?, ?)");
                foreach ($member_ids as $mId) {
                    // Evita duplicatas
                    $check = $db->prepare("SELECT 1 FROM user_members WHERE user_id = ? AND member_id = ?");
                    $check->execute([$userId, $mId]);
                    if (!$check->fetch()) {
                        $stmtMember->execute([$userId, $mId]);
                    }
                }
            }
            
            // Save custom permissions
            if ($permission_mode === 'override' && $role !== 'admin') {
                array_unshift($custom_permissions, '__override__');
                $custom_permissions = array_values(array_unique($custom_permissions));
            }

            if (!empty($custom_permissions)) {
                $stmtPerm = $db->prepare("INSERT INTO user_permissions (user_id, permission_slug) VALUES (?, ?)");
                foreach ($custom_permissions as $slug) {
                    $stmtPerm->execute([$userId, $slug]);
                }
            }
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            // handle error
        }

        redirect('/admin/users');
    }

    public function edit($id) {
        requirePermission('users.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            redirect('/admin/users');
        }
        
        // Security: Apenas developer pode acessar a tela de edição de outro developer
        if ($user['role'] === 'developer' && ($_SESSION['user_role'] ?? '') !== 'developer') {
            redirect('/admin/users');
        }
        
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        $permissions = $db->query("SELECT * FROM permissions ORDER BY label ASC")->fetchAll();
        if (($_SESSION['user_role'] ?? '') !== 'developer') {
            $permissions = array_values(array_filter($permissions, function($p) {
                return ($p['slug'] ?? '') !== 'developer.access';
            }));
        }
        $members = $db->query("SELECT id, name FROM members ORDER BY name ASC")->fetchAll();
        
        // Get user's current custom permissions
        $stmtUserPerms = $db->prepare("SELECT permission_slug FROM user_permissions WHERE user_id = ?");
        $stmtUserPerms->execute([$id]);
        $userPermissions = $stmtUserPerms->fetchAll(PDO::FETCH_COLUMN);
        $isOverride = in_array('__override__', $userPermissions, true);
        $userPermissions = array_values(array_filter($userPermissions, function($permission) {
            return $permission !== '__override__';
        }));
        
        // Get user's linked members
        $stmtMembers = $db->prepare("SELECT m.id, m.name FROM user_members um JOIN members m ON um.member_id = m.id WHERE um.user_id = ?");
        $stmtMembers->execute([$id]);
        $linkedMembers = $stmtMembers->fetchAll();
        
        // Fallback: se não tiver na tabela nova, mas tiver na antiga coluna member_id
        if (empty($linkedMembers) && !empty($user['member_id'])) {
            $stmtM = $db->prepare("SELECT id, name FROM members WHERE id = ?");
            $stmtM->execute([$user['member_id']]);
            $linkedMembers = $stmtM->fetchAll();
        }
        
        $rbac = require __DIR__ . '/../../config/rbac.php';
        $roles = $rbac['roles'];
        if (($_SESSION['user_role'] ?? '') !== 'developer') {
            unset($roles['developer']);
        }

        $permissionGroups = buildPermissionGroups($permissions);
        $isDeveloperEditor = ($_SESSION['user_role'] ?? '') === 'developer';
        $permissionsLockedByOverride = !$isDeveloperEditor && $isOverride;

        view('admin/users/edit', [
            'user' => $user, 
            'roles' => $roles, 
            'congregations' => $congregations,
            'permissions' => $permissions,
            'permissionGroups' => $permissionGroups,
            'userPermissions' => $userPermissions,
            'isOverride' => $isOverride,
            'isDeveloperEditor' => $isDeveloperEditor,
            'adminEditablePermissions' => getAdminEditablePermissionSlugs(),
            'permissionsLockedByOverride' => $permissionsLockedByOverride,
            'members' => $members,
            'linkedMembers' => $linkedMembers
        ]);
    }

    public function update($id) {
        requirePermission('users.manage');
        $db = (new Database())->connect();
        $username = $_POST['username'];
        $role = $_POST['role'];
        $password = $_POST['password']; 
        $congregation_id = !empty($_POST['congregation_id']) ? $_POST['congregation_id'] : null;
        $member_ids = !empty($_POST['member_ids']) ? json_decode($_POST['member_ids'], true) : [];
        $custom_permissions = normalizePermissionSelection(isset($_POST['permissions']) ? $_POST['permissions'] : []);
        $permission_mode = $_POST['permission_mode'] ?? 'additive';
        $isDeveloperEditor = ($_SESSION['user_role'] ?? '') === 'developer';

        $stmtCheck = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmtCheck->execute([$id]);
        $targetUser = $stmtCheck->fetch();
        
        if (!$targetUser) {
            redirect('/admin/users');
        }
        
        // Security: Apenas developer pode alterar dados de outro developer
        if ($targetUser['role'] === 'developer' && ($_SESSION['user_role'] ?? '') !== 'developer') {
            redirect('/admin/users');
        }

        // Security: Somente developer pode alterar role ou permissões de um admin
        if ($targetUser['role'] === 'admin' && $_SESSION['user_role'] !== 'developer') {
            $role = 'admin'; // Força manter como admin
            
            // Mantém as permissões atuais do banco de dados ignorando o POST
            $stmtUserPerms = $db->prepare("SELECT permission_slug FROM user_permissions WHERE user_id = ?");
            $stmtUserPerms->execute([$id]);
            $custom_permissions = $stmtUserPerms->fetchAll(PDO::FETCH_COLUMN);
        }

        // Security: Somente developer pode atribuir a função de developer
        if ($role === 'developer' && ($_SESSION['user_role'] ?? '') !== 'developer') {
            $role = $targetUser['role']; // Reverte para a função original
        }

        $stmtCurrentPerms = $db->prepare("SELECT permission_slug FROM user_permissions WHERE user_id = ?");
        $stmtCurrentPerms->execute([$id]);
        $currentStoredPermissions = $stmtCurrentPerms->fetchAll(PDO::FETCH_COLUMN);

        if (!$isDeveloperEditor && $targetUser['role'] !== 'admin') {
            if (in_array('__override__', $currentStoredPermissions, true)) {
                $custom_permissions = $currentStoredPermissions;
                $permission_mode = 'override';
            } else {
                $allowedAdminPermissions = getAdminEditablePermissionSlugs();
                $settingsSelected = array_values(array_intersect($custom_permissions, $allowedAdminPermissions));
                $preservedDeveloperPermissions = array_values(array_filter($currentStoredPermissions, function($slug) use ($allowedAdminPermissions) {
                    return !in_array($slug, $allowedAdminPermissions, true) && $slug !== '__override__';
                }));
                $custom_permissions = array_values(array_unique(array_merge($preservedDeveloperPermissions, $settingsSelected)));
                $permission_mode = 'additive';
            }
        }

        try {
            $db->beginTransaction();

            // Mantém member_id na tabela users como fallback (o primeiro selecionado) ou null
            $primary_member_id = !empty($member_ids) ? $member_ids[0] : null;

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET username = ?, password = ?, role = ?, congregation_id = ?, member_id = ? WHERE id = ?");
                $stmt->execute([$username, $hashed_password, $role, $congregation_id, $primary_member_id, $id]);
            } else {
                $stmt = $db->prepare("UPDATE users SET username = ?, role = ?, congregation_id = ?, member_id = ? WHERE id = ?");
                $stmt->execute([$username, $role, $congregation_id, $primary_member_id, $id]);
            }
            
            // Update user_members relationship: delete all then re-add
            $db->prepare("DELETE FROM user_members WHERE user_id = ?")->execute([$id]);
            
            if (!empty($member_ids)) {
                $stmtMember = $db->prepare("INSERT INTO user_members (user_id, member_id) VALUES (?, ?)");
                foreach ($member_ids as $mId) {
                    // Evita duplicatas
                    $check = $db->prepare("SELECT 1 FROM user_members WHERE user_id = ? AND member_id = ?");
                    $check->execute([$id, $mId]);
                    if (!$check->fetch()) {
                        $stmtMember->execute([$id, $mId]);
                    }
                }
            }
            
            // Update permissions: delete all then re-add
            $db->prepare("DELETE FROM user_permissions WHERE user_id = ?")->execute([$id]);
            
            if ($permission_mode === 'override' && $role !== 'admin' && $isDeveloperEditor) {
                array_unshift($custom_permissions, '__override__');
                $custom_permissions = array_values(array_unique($custom_permissions));
            }

            if (!empty($custom_permissions)) {
                $stmtPerm = $db->prepare("INSERT INTO user_permissions (user_id, permission_slug) VALUES (?, ?)");
                foreach ($custom_permissions as $slug) {
                    $stmtPerm->execute([$id, $slug]);
                }
            }
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            // handle error
        }

        redirect('/admin/users');
    }

    public function delete($id) {
        requirePermission('users.manage');
        
        // Prevent deleting own account
        if ($_SESSION['user_id'] == $id) {
            redirect('/admin/users');
        }

        $db = (new Database())->connect();
        
        // Prevent deleting developer account
        $stmtCheck = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmtCheck->execute([$id]);
        $user = $stmtCheck->fetch();
        
        if ($user && $user['role'] === 'developer') {
            // Optionally set an error message in session
            redirect('/admin/users');
        }

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        redirect('/admin/users');
    }

    public function permissions() {
        requirePermission('users.manage');
        $db = (new Database())->connect();
        
        $rolesConfig = require __DIR__ . '/../../config/rbac.php';
        $roles = $rolesConfig['roles'];
        
        // Remove developer role from display for safety, unless the user IS a developer
        if (($_SESSION['user_role'] ?? '') !== 'developer') {
            if (isset($roles['developer'])) {
                unset($roles['developer']);
            }
        }

        $permissions = $db->query("SELECT * FROM permissions ORDER BY label ASC")->fetchAll(PDO::FETCH_ASSOC);
        $permissionGroups = buildPermissionGroups($permissions);
        
        view('admin/users/permissions', ['roles' => $roles, 'permissions' => $permissions, 'permissionGroups' => $permissionGroups]);
    }
}

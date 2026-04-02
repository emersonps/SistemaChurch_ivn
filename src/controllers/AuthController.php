<?php
// src/controllers/AuthController.php

class AuthController {
    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer') {
                redirect('/developer/dashboard');
            } else {
                $rbac = require __DIR__ . '/../../config/rbac.php';
                $role = $_SESSION['user_role'] ?? 'guest';
                $rolePerms = $rbac['roles'][$role]['permissions'] ?? [];
                
                if (in_array('dashboard.view', $rolePerms) || in_array('admin.manage', $rolePerms)) {
                    redirect('/admin/dashboard');
                } elseif (in_array('financial.view', $rolePerms) || in_array('financial.manage', $rolePerms)) {
                    redirect('/admin/financial/bank-accounts');
                } elseif (in_array('members.view', $rolePerms)) {
                    redirect('/admin/members');
                } else {
                    redirect('/admin/dashboard');
                }
            }
        }
        view('admin/login');
    }

    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent fixation attacks
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username']; // Armazena o nome de usuário na sessão
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role']; // Save role to session
            $_SESSION['user_congregation_id'] = $user['congregation_id']; // Save congregation restriction
            
            // Redirect based on role
            if ($user['role'] === 'developer') {
                redirect('/developer/dashboard');
            } else {
                // Verificar qual a primeira página que ele tem acesso se não puder ver o dashboard
                $rbac = require __DIR__ . '/../../config/rbac.php';
                $rolePerms = $rbac['roles'][$user['role']]['permissions'] ?? [];
                
                if (in_array('dashboard.view', $rolePerms) || in_array('admin.manage', $rolePerms)) {
                    redirect('/admin/dashboard');
                } elseif (in_array('financial.view', $rolePerms) || in_array('financial.manage', $rolePerms)) {
                    redirect('/admin/financial/bank-accounts');
                } elseif (in_array('members.view', $rolePerms)) {
                    redirect('/admin/members');
                } else {
                    // Fallback genérico (se bater no dashboard e for bloqueado, o helpers.php joga pro logout)
                    redirect('/admin/dashboard');
                }
            }
            
        } else {
            view('admin/login', ['error' => 'Usuário ou senha inválidos']);
        }
    }

    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        redirect('/admin/login');
    }

    public function changePassword() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            $user_id = $_SESSION['user_id'];

            if ($new_password !== $confirm_password) {
                view('admin/auth/change_password', ['error' => 'A nova senha e a confirmação não conferem.']);
                return;
            }

            $db = (new Database())->connect();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!password_verify($current_password, $user['password'])) {
                view('admin/auth/change_password', ['error' => 'Senha atual incorreta.']);
                return;
            }

            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $user_id]);

            view('admin/auth/change_password', ['success' => 'Senha alterada com sucesso!']);
        } else {
            view('admin/auth/change_password');
        }
    }
}

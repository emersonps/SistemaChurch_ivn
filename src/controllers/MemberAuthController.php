<?php
// src/controllers/MemberAuthController.php

class MemberAuthController {
    public function showLogin() {
        if (isset($_SESSION['member_id'])) {
            redirect('/portal/dashboard');
        }
        view('portal/login');
    }

    public function login() {
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $password = $_POST['password'];

        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM members WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?");
        $stmt->execute([$cpf]);
        $member = $stmt->fetch();

        if ($member && !empty($member['password']) && password_verify($password, $member['password'])) {
            $_SESSION['member_id'] = $member['id'];
            $_SESSION['member_name'] = $member['name'];
            $_SESSION['member_congregation'] = $member['congregation_id'];
            redirect('/portal/dashboard');
        } else {
            view('portal/login', ['error' => 'CPF ou senha inválidos. Se é seu primeiro acesso, faça o cadastro.']);
        }
    }

    public function showRegister() {
        if (isset($_SESSION['member_id'])) {
            redirect('/portal/dashboard');
        }
        view('portal/register');
    }

    public function register() {
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $birth_date = $_POST['birth_date'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            view('portal/register', ['error' => 'As senhas não conferem.']);
            return;
        }

        $db = (new Database())->connect();
        
        // Find member by CPF
        $stmt = $db->prepare("SELECT * FROM members WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?");
        $stmt->execute([$cpf]);
        $member = $stmt->fetch();

        if (!$member) {
             view('portal/register', ['error' => 'CPF não encontrado na base de membros. Entre em contato com a secretaria.']);
             return;
        }
        
        // Validate Birth Date
        // Handle DD/MM/YYYY to Y-m-d
        if (strpos($birth_date, '/') !== false) {
            $birth_date = str_replace('/', '-', $birth_date);
        }
        
        // Normalize dates to Y-m-d to ensure correct comparison
        $dbDate = date('Y-m-d', strtotime($member['birth_date']));
        $inputDate = date('Y-m-d', strtotime($birth_date));
        
        if ($dbDate !== $inputDate) {
             view('portal/register', ['error' => 'Data de nascimento não confere com o cadastro.']);
             return;
        }

        // Check if already has password
        if (!empty($member['password'])) {
             view('portal/register', ['error' => 'Você já possui cadastro. Faça login.']);
             return;
        }

        // Set Password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE members SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $member['id']]);

        // Auto login
        $_SESSION['member_id'] = $member['id'];
        $_SESSION['member_name'] = $member['name'];
        $_SESSION['member_congregation'] = $member['congregation_id'];
        
        redirect('/portal/dashboard');
    }

    public function logout() {
        unset($_SESSION['member_id']);
        unset($_SESSION['member_name']);
        unset($_SESSION['member_congregation']);
        redirect('/portal/login');
    }
}

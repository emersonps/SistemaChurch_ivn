<?php
// src/controllers/MigrationController.php

require_once __DIR__ . '/../database/MigrationRunner.php';

class MigrationController {
    
    private function requireDeveloper() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }
        
        // Check for admin role or developer permission
        // Assuming admin has full access, or check specific permission if implemented
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return;
        }
        
        if (function_exists('hasPermission') && hasPermission('developer.access')) {
            return;
        }

        // If neither, redirect to dashboard or login
        redirect('/admin/dashboard');
    }

    public function index() {
        $this->requireDeveloper();
        $runner = new MigrationRunner();
        $history = $runner->getHistory();
        
        view('developer/migrations', ['history' => $history]);
    }

    public function run() {
        $this->requireDeveloper();
        $runner = new MigrationRunner();
        $log = $runner->run();
        
        $_SESSION['migration_log'] = $log;
        redirect('/developer/migrations');
    }

    public function rollback($filename) {
        $this->requireDeveloper();
        try {
            $runner = new MigrationRunner();
            $msg = $runner->rollback($filename);
            $_SESSION['success'] = $msg;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/developer/migrations');
    }
}

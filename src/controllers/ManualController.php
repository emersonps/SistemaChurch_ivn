<?php
// src/controllers/ManualController.php

class ManualController {
    public function index() {
        // Verifica se usuário está logado
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }

        // Definir seções disponíveis baseadas nas permissões
        $sections = [
            'intro' => [
                'title' => 'Introdução',
                'allowed' => true,
                'icon' => 'fas fa-book-open'
            ],
            'dashboard' => [
                'title' => 'Painel Principal',
                'allowed' => true,
                'icon' => 'fas fa-tachometer-alt'
            ],
            'members' => [
                'title' => 'Gestão de Membros',
                'allowed' => hasPermission('members.view'),
                'icon' => 'fas fa-users'
            ],
            'financial' => [
                'title' => 'Financeiro (Entradas)',
                'allowed' => hasPermission('financial.view'),
                'icon' => 'fas fa-hand-holding-usd'
            ],
            'expenses' => [
                'title' => 'Saídas (Despesas)',
                'allowed' => hasPermission('financial.view'),
                'icon' => 'fas fa-file-invoice-dollar'
            ],
            'closures' => [
                'title' => 'Fechamentos',
                'allowed' => hasPermission('financial.view'),
                'icon' => 'fas fa-lock'
            ],
            'congregations' => [
                'title' => 'Congregações',
                'allowed' => hasPermission('congregations.view'),
                'icon' => 'fas fa-church'
            ],
            'groups' => [
                'title' => 'Grupos e Células',
                'allowed' => hasPermission('groups.view'),
                'icon' => 'fas fa-users-cog'
            ],
            'events' => [
                'title' => 'Eventos',
                'allowed' => hasPermission('events.view'),
                'icon' => 'fas fa-calendar-alt'
            ],
            'service_reports' => [
                'title' => 'Relatórios de Culto',
                'allowed' => hasPermission('service_reports.view'),
                'icon' => 'fas fa-clipboard-list'
            ],
            'ebd' => [
                'title' => 'Escola Bíblica (EBD)',
                'allowed' => hasPermission('ebd.view'),
                'icon' => 'fas fa-bible'
            ],
            'system_payments' => [
                'title' => 'Pagamento do Sistema',
                'allowed' => hasPermission('system_payments.view'),
                'icon' => 'fas fa-credit-card'
            ],
            'users' => [
                'title' => 'Usuários e Permissões',
                'allowed' => hasPermission('users.view'),
                'icon' => 'fas fa-user-shield'
            ],
            'password' => [
                'title' => 'Alterar Senha',
                'allowed' => true,
                'icon' => 'fas fa-key'
            ]
        ];

        view('admin/manual/index', ['sections' => $sections]);
    }
}

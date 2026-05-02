<?php
$siteProfile = getChurchSiteProfileSettings();
// Bloquear acesso de desenvolvedores ao painel admin
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer') {
    // Se tentar acessar qualquer página admin, redireciona para o painel de dev
    // EXCEÇÃO: Permitir acesso ao gerenciamento de usuários e logout
    if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0 && 
        strpos($_SERVER['REQUEST_URI'], '/admin/logout') === false && 
        strpos($_SERVER['REQUEST_URI'], '/admin/users') === false) {
        header("Location: /developer/dashboard");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteProfile['name'] ?? 'Igreja Vida Nova') ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    <!-- PWA / Web App Manifest -->
    <link rel="manifest" href="<?= htmlspecialchars(getChurchManifestUrl($siteProfile)) ?>">
    <meta name="theme-color" content="#b30000">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .nav-link.active { background-color: #b30000; color: white !important; } /* Red for admin active */
        /* Fix for Tabs collision */
        .nav-tabs .nav-link.active {
            color: #b30000 !important;
            background-color: #fff !important;
            border-color: #dee2e6 #dee2e6 #fff !important;
        }
        .bg-primary { background-color: #b30000 !important; } /* Red admin header */
        .text-primary { color: #b30000 !important; }
        .btn-primary { background-color: #b30000; border-color: #b30000; }
        .btn-primary:hover { background-color: #800000; border-color: #800000; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn > i,
        .btn > span {
            line-height: 1;
        }
        .sidebar-brand-logo { max-height: 42px; max-width: 100%; object-fit: contain; }
        .mobile-launcher { }
        .dataTables_wrapper .dataTables_filter {
            width: 100%;
            text-align: left;
            float: none;
            margin-bottom: .5rem;
        }
        .dataTables_wrapper .dataTables_length {
            margin-bottom: .5rem;
        }
        .dataTables_wrapper .row {
            --bs-gutter-y: .5rem;
        }
        .dataTables_wrapper .dataTables_filter label {
            width: 100%;
            margin: 0;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
        }
        @keyframes menuCornerGlow {
            0% { box-shadow: 0 0 0 rgba(255,255,255,0); border-color: rgba(255,255,255,.65); }
            50% { box-shadow: 0 0 0 4px rgba(255,255,255,.25), 0 0 18px rgba(255,255,255,.45); border-color: rgba(255,255,255,1); }
            100% { box-shadow: 0 0 0 rgba(255,255,255,0); border-color: rgba(255,255,255,.65); }
        }
        .menu-attention-glow {
            animation: menuCornerGlow 1.05s ease-in-out 0s 4;
        }
        @media (prefers-reduced-motion: reduce) {
            .menu-attention-glow { animation: none; }
        }
        @media (max-width: 991.98px) {
            .app-form-bottom-actions {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1020;
                background: #fff;
                border-top: 1px solid rgba(0,0,0,0.12);
                padding: .75rem .75rem calc(.75rem + env(safe-area-inset-bottom));
            }
            .app-form-with-bottom-actions {
                padding-bottom: calc(86px + env(safe-area-inset-bottom));
            }
            .d-flex.justify-content-between.flex-wrap.flex-md-nowrap.align-items-center.pt-3.pb-2.mb-3.border-bottom > .btn-toolbar {
                width: 100%;
                display: flex;
                gap: .5rem;
            }
            .d-flex.justify-content-between.flex-wrap.flex-md-nowrap.align-items-center.pt-3.pb-2.mb-3.border-bottom > .btn-toolbar .btn {
                flex: 1 1 0;
                width: 100%;
                justify-content: center;
            }
        }
        @media (max-width: 767.98px) {
            body.mobile-launcher-page .app-page-content { display: none; }
            .dataTables_wrapper .dataTables_paginate .pagination {
                flex-wrap: nowrap;
                justify-content: center;
                gap: .25rem;
                white-space: nowrap;
            }
            .dataTables_wrapper .dataTables_paginate {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .dataTables_wrapper .dataTables_paginate .page-link {
                padding: .3rem .45rem;
                font-size: .85rem;
            }
            .dataTables_wrapper .dataTables_paginate .page-item:first-child .page-link,
            .dataTables_wrapper .dataTables_paginate .page-item:last-child .page-link {
                padding-left: .6rem;
                padding-right: .6rem;
            }
        }
    </style>
</head>
<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isAdminOrDevArea = isLoggedIn() && (strpos($currentUri, '/admin') === 0 || strpos($currentUri, '/developer') === 0);
$isAdminArea = isLoggedIn() && strpos($currentUri, '/admin') === 0;
$isMobileLauncherPage = $isAdminArea && (($_GET['launcher'] ?? '') === '1');
$bodyClass = $isMobileLauncherPage ? 'mobile-launcher-page' : '';
$mobileHomeHref = strpos($currentUri, '/developer') === 0 ? '/developer/dashboard' : '/admin';
$mobileLauncherHref = '/admin?launcher=1';
?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
<?php if ($isAdminOrDevArea): ?>
    <nav class="navbar navbar-dark bg-primary mb-2 d-md-none">
        <div class="container-fluid">
            <?php 
            $navTitle = ($siteProfile['alias'] ?? 'IVN') . ' Admin';
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'secretary') {
                $navTitle = ($siteProfile['alias'] ?? 'IVN') . ' Secretaria';
            }
            ?>
            <?php if (!$isMobileLauncherPage): ?>
                <a class="btn btn-sm btn-outline-light me-2 menu-attention-glow" href="<?= htmlspecialchars($mobileLauncherHref) ?>">
                    <i class="fas fa-th-large me-1"></i> Menu
                </a>
            <?php endif; ?>
            <a class="navbar-brand flex-grow-1" href="<?= htmlspecialchars($mobileHomeHref) ?>"><?= htmlspecialchars($navTitle) ?></a>
            <a class="btn btn-sm btn-outline-light" href="/admin/logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="px-3 pb-3 border-bottom text-center">
                        <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>" class="sidebar-brand-logo mb-2">
                        <h4 class="text-danger mb-0"><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></h4>
                    </div>
                    <ul class="nav flex-column">
                        <!-- Principal -->
                        <?php if (hasPermission('dashboard.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/admin' || $_SERVER['REQUEST_URI'] === '/admin/' ? 'active' : 'text-dark' ?>" href="/admin">
                                <i class="fas fa-home me-2"></i> Painel
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Secretaria -->
                        <?php if (hasPermission('members.view') || hasPermission('congregations.view') || hasPermission('events.view') || hasPermission('service_reports.view') || hasPermission('groups.view')): ?>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-muted text-uppercase small">
                            <span>Secretaria</span>
                        </h6>
                        <?php endif; ?>

                        <?php if (hasPermission('members.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/members') !== false ? 'active' : 'text-dark' ?>" href="/admin/members">
                                <i class="fas fa-users me-2"></i> Membros
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('congregations.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/congregations') !== false ? 'active' : 'text-dark' ?>" href="/admin/congregations">
                                <i class="fas fa-church me-2"></i> Congregações
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('events.view') || hasPermission('events.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/events') !== false ? 'active' : 'text-dark' ?>" href="/admin/events">
                                <i class="fas fa-calendar-alt me-2"></i> Eventos / Cultos
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('service_reports.view') || hasPermission('service_reports.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/service_reports') !== false ? 'active' : 'text-dark' ?>" href="/admin/service_reports">
                                <i class="fas fa-clipboard-list me-2"></i> Relatórios de Culto
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('general_reports.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/reports/general') !== false ? 'active' : 'text-dark' ?>" href="/admin/reports/general">
                                <i class="fas fa-chart-pie me-2"></i> Estatísticas Gerais
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('signatures.view') || hasPermission('signatures.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/signatures') !== false ? 'active' : 'text-dark' ?>" href="/admin/signatures">
                                <i class="fas fa-file-signature me-2"></i> Assinaturas
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('groups.view') || hasPermission('groups.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/groups') !== false ? 'active' : 'text-dark' ?>" href="/admin/groups">
                                <i class="fas fa-users-cog me-2"></i> Grupos/Células
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('gallery.view') || hasPermission('gallery.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/gallery') !== false ? 'active' : 'text-dark' ?>" href="/admin/gallery">
                                <i class="fas fa-images me-2"></i> Galeria
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('banners.view') || hasPermission('banners.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/banners') !== false ? 'active' : 'text-dark' ?>" href="/admin/banners">
                                <i class="fas fa-image me-2"></i> Banners
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Financeiro -->
                        <?php if (hasPermission('financial.view')): ?>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-muted text-uppercase small">
                            <span>Financeiro</span>
                        </h6>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center" href="#financeiroSubmenu" data-bs-toggle="collapse" aria-expanded="false" aria-controls="financeiroSubmenu">
                                <span><i class="fas fa-chart-line me-2"></i> Gestão Financeira e Contábil</span>
                                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
                            </a>
                            <ul class="collapse flex-column ms-3 nav" id="financeiroSubmenu">
                                <?php if (hasPermission('financial_accounts.manage')): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/financial/bank-accounts') !== false ? 'active' : '' ?>" href="/admin/financial/bank-accounts">
                                        <i class="fas fa-university me-2"></i> Contas e Caixas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/financial/chart-accounts') !== false ? 'active' : '' ?>" href="/admin/financial/chart-accounts">
                                        <i class="fas fa-sitemap me-2"></i> Plano de Contas
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (hasPermission('financial.manage')): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/tithes') !== false ? 'active' : '' ?>" href="/admin/tithes">
                                        <i class="fas fa-arrow-up text-success me-2"></i> Entradas (Dízimos/Ofertas)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/expenses') !== false ? 'active' : '' ?>" href="/admin/expenses">
                                        <i class="fas fa-arrow-down text-danger me-2"></i> Saídas (Despesas)
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (hasPermission('financial.view')): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/financial/report') !== false ? 'active' : '' ?>" href="/admin/financial/report">
                                        <i class="fas fa-file-pdf me-2"></i> Relatório / Balancete
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (hasPermission('financial_ofx.manage')): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/financial/ofx') !== false ? 'active' : '' ?>" href="/admin/financial/ofx">
                                        <i class="fas fa-sync-alt me-2"></i> Conciliação OFX
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (hasPermission('financial.manage')): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/financial/closures') !== false ? 'active' : '' ?>" href="/admin/financial/closures">
                                        <i class="fas fa-lock me-2"></i> Fechamento de Caixa
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('system_payments.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/system-payments') !== false ? 'active' : 'text-dark' ?>" href="/admin/system-payments">
                                <i class="fas fa-credit-card me-2"></i> Pagamento Sistema
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Ensino / EBD -->
                        <?php if (hasPermission('ebd.view') || hasPermission('studies.view')): ?>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-muted text-uppercase small">
                            <span>Ensino</span>
                        </h6>
                        <?php endif; ?>

                        <?php if (hasPermission('ebd.view') || hasPermission('ebd.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/ebd') !== false ? 'active' : 'text-dark' ?>" href="/admin/ebd/classes">
                                <i class="fas fa-book-reader me-2"></i> Escola Bíblica
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('studies.view') || hasPermission('studies.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/studies') !== false ? 'active' : 'text-dark' ?>" href="/admin/studies">
                                <i class="fas fa-bible me-2"></i> Estudos
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Sistema -->
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-muted text-uppercase small">
                            <span>Sistema</span>
                        </h6>

                        <?php if (hasPermission('users.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/permissions') !== false ? 'active' : 'text-dark' ?>" href="#usuariosSubmenu" data-bs-toggle="collapse" aria-expanded="<?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/permissions') !== false ? 'true' : 'false' ?>" aria-controls="usuariosSubmenu">
                                <span><i class="fas fa-users-cog me-2"></i> Contas/Usuários</span>
                                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
                            </a>
                            <ul class="collapse flex-column ms-3 nav <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/permissions') !== false ? 'show' : '' ?>" id="usuariosSubmenu">
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false && strpos($_SERVER['REQUEST_URI'], 'permissions') === false ? 'active text-primary' : 'text-dark' ?>" href="/admin/users">
                                        <i class="fas fa-user me-2"></i> Usuários
                                    </a>
                                </li>
                                <?php if (hasPermission('permissions.manage') || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer')): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/permissions') !== false ? 'active text-primary' : 'text-dark' ?>" href="/admin/permissions">
                                        <i class="fas fa-key me-2"></i> Permissões (RBAC)
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('settings.view')): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/site-settings') !== false ? 'active' : 'text-dark' ?>" href="#settingsSubmenu" data-bs-toggle="collapse" aria-expanded="<?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/site-settings') !== false ? 'true' : 'false' ?>" aria-controls="settingsSubmenu">
                                <span><i class="fas fa-cogs me-2"></i> Configurações</span>
                                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
                            </a>
                            <ul class="collapse flex-column ms-3 nav <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/site-settings') !== false ? 'show' : '' ?>" id="settingsSubmenu">
                                <?php if (hasPermission('settings.system.view')): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false && strpos($_SERVER['REQUEST_URI'], 'card-layout') === false && strpos($_SERVER['REQUEST_URI'], 'site-settings') === false && strpos($_SERVER['REQUEST_URI'], 'whatsapp') === false ? 'active text-primary' : 'text-dark' ?>" href="/admin/settings">
                                        <i class="fas fa-sliders-h me-2"></i> Sistema Geral
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (hasPermission('settings.layout.view')): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/site-settings') !== false ? 'active text-primary' : 'text-dark' ?>" href="/admin/site-settings">
                                        <i class="fas fa-desktop me-2"></i> Layout do Site
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (hasPermission('settings.card.view')): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings/card-layout') !== false ? 'active text-primary' : 'text-dark' ?>" href="/admin/settings/card-layout">
                                        <i class="fas fa-id-card me-2"></i> Layout da Carteirinha
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- Menu Dev (Migration) -->
                        <?php 
                        $isDev = false;
                        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer') {
                            $isDev = true;
                        } elseif (isset($_SESSION['user_id'])) {
                            try {
                                $db = (new Database())->connect();
                                $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $role = $stmt->fetchColumn();
                                if ($role === 'developer') {
                                    $isDev = true;
                                    $_SESSION['user_role'] = 'developer';
                                }
                            } catch (Exception $e) {}
                        }
                        
                        if ($isDev): 
                        ?>
                        <li class="nav-item">
                            <a class="nav-link text-primary fw-bold" href="/developer/dashboard">
                                <i class="fas fa-code me-2"></i> Painel Dev
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-primary" href="/migrate.php" target="_blank">
                                <i class="fas fa-database me-2"></i> Atualizar Banco
                            </a>
                        </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/manual') !== false ? 'active' : 'text-dark' ?>" href="/admin/manual">
                                <i class="fas fa-book me-2"></i> Manual / Ajuda
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/change-password') !== false ? 'active' : 'text-dark' ?>" href="/admin/change-password">
                                <i class="fas fa-key me-2"></i> Alterar Senha
                            </a>
                        </li>
                        <li class="nav-item mt-3 mb-3">
                            <a class="nav-link text-danger" href="/admin/logout">
                                <i class="fas fa-sign-out-alt me-2"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-2 py-md-4">
                <?php
                $loggedUserName = $_SESSION['user_name'] ?? $_SESSION['username'] ?? '';
                $loggedUserRole = $_SESSION['user_role'] ?? '';
                ?>
                <div class="d-none d-md-flex justify-content-end mb-1">
                    <span class="small text-muted">
                        Usuário: <strong><?= htmlspecialchars((string)$loggedUserName) ?></strong>
                        <span class="ms-2 badge bg-secondary"><?= htmlspecialchars((string)$loggedUserRole) ?></span>
                    </span>
                </div>

                <?php if ($isMobileLauncherPage): ?>
                    <div class="d-md-none mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="small text-muted">
                                        Usuário: <strong><?= htmlspecialchars((string)$loggedUserName) ?></strong>
                                        <?php if ((string)$loggedUserRole !== ''): ?>
                                            <span class="ms-2 badge bg-secondary"><?= htmlspecialchars((string)$loggedUserRole) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mobile-launcher">
                                    <?php if (hasPermission('dashboard.view')): ?>
                                        <div class="text-muted small fw-bold mb-2">Principal</div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <a class="btn btn-primary btn-sm w-100" href="<?= htmlspecialchars($mobileHomeHref) ?>">
                                                    <i class="fas fa-home me-2"></i>Painel
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (hasPermission('members.view') || hasPermission('congregations.view') || hasPermission('events.view') || hasPermission('service_reports.view') || hasPermission('general_reports.view') || hasPermission('signatures.view') || hasPermission('signatures.manage') || hasPermission('groups.view') || hasPermission('groups.manage') || hasPermission('gallery.view') || hasPermission('banners.view')): ?>
                                        <div class="text-muted small fw-bold mb-2">Secretaria</div>
                                        <div class="row g-2 mb-3">
                                            <?php if (hasPermission('members.view')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/members"><i class="fas fa-users me-2"></i>Membros</a></div>
                                            <?php endif; ?>
                                            <?php if (hasPermission('congregations.view')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/congregations"><i class="fas fa-church me-2"></i>Congregações</a></div>
                                            <?php endif; ?>
                                            <?php if (hasPermission('events.view') || hasPermission('events.manage')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/events"><i class="fas fa-calendar-alt me-2"></i>Eventos</a></div>
                                            <?php endif; ?>
                                            <?php if (hasPermission('service_reports.view')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/service_reports"><i class="fas fa-clipboard-list me-2"></i>Relatórios</a></div>
                                            <?php endif; ?>
                                            <?php if (hasPermission('general_reports.view')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/reports/general"><i class="fas fa-chart-pie me-2"></i>Estatísticas</a></div>
                                            <?php endif; ?>
                                            <?php if (hasPermission('signatures.view') || hasPermission('signatures.manage')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/signatures"><i class="fas fa-file-signature me-2"></i>Assinaturas</a></div>
                                            <?php endif; ?>
                                            <?php if (hasPermission('groups.view') || hasPermission('groups.manage')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/groups"><i class="fas fa-users-cog me-2"></i>Grupos</a></div>
                                            <?php endif; ?>
                                            <?php if (hasPermission('gallery.view')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/gallery"><i class="fas fa-images me-2"></i>Galeria</a></div>
                                            <?php endif; ?>
                                            <?php if (hasPermission('banners.view')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/banners"><i class="fas fa-image me-2"></i>Banners</a></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (hasPermission('financial.view')): ?>
                                        <div class="text-muted small fw-bold mb-2">Financeiro</div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/tithes"><i class="fas fa-hand-holding-usd me-2"></i>Entradas</a></div>
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/expenses"><i class="fas fa-file-invoice-dollar me-2"></i>Saídas</a></div>
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/financial/report"><i class="fas fa-chart-line me-2"></i>Relatório</a></div>
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/financial/closures"><i class="fas fa-lock me-2"></i>Fechamentos</a></div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (hasPermission('ebd.view') || hasPermission('ebd.manage') || hasPermission('studies.view')): ?>
                                        <div class="text-muted small fw-bold mb-2">Ensino</div>
                                        <div class="row g-2 mb-3">
                                            <?php if (hasPermission('ebd.view') || hasPermission('ebd.manage')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/ebd/classes"><i class="fas fa-book-open me-2"></i>EBD</a></div>
                                            <?php endif; ?>
                                            <?php if (hasPermission('studies.view')): ?>
                                                <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/studies"><i class="fas fa-book me-2"></i>Estudos</a></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="text-muted small fw-bold mb-2">Sistema</div>
                                    <div class="row g-2">
                                        <?php if (hasPermission('users.manage')): ?>
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/users"><i class="fas fa-user me-2"></i>Usuários</a></div>
                                        <?php endif; ?>
                                        <?php if (hasPermission('permissions.manage') || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer')): ?>
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/permissions"><i class="fas fa-key me-2"></i>Permissões</a></div>
                                        <?php endif; ?>
                                        <?php if (hasPermission('settings.system.view')): ?>
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/settings"><i class="fas fa-sliders-h me-2"></i>Sistema</a></div>
                                        <?php endif; ?>
                                        <?php if (hasPermission('settings.layout.view')): ?>
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/site-settings"><i class="fas fa-paint-roller me-2"></i>Layout</a></div>
                                        <?php endif; ?>
                                        <?php if (hasPermission('settings.card.view')): ?>
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/settings/card-layout"><i class="fas fa-id-card me-2"></i>Carteirinha</a></div>
                                        <?php endif; ?>
                                        <?php if (hasPermission('system_payments.view')): ?>
                                            <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/system-payments"><i class="fas fa-credit-card me-2"></i>Mensalidade</a></div>
                                        <?php endif; ?>
                                        <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/manual"><i class="fas fa-question-circle me-2"></i>Manual</a></div>
                                        <div class="col-6"><a class="btn btn-primary btn-sm w-100" href="/admin/change-password"><i class="fas fa-key me-2"></i>Senha</a></div>
                                        <div class="col-12"><a class="btn btn-danger w-100" href="/admin/logout"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            
            <?php
            if (isLoggedIn() && $isAdminArea && function_exists('hasPermission') && hasPermission('members.view')) {
                $forceTodayBirthdayModal = !empty($_SESSION['show_today_birthdays_modal']);
                unset($_SESSION['show_today_birthdays_modal']);
                $todayBirthdays = [];
                try {
                    $db = (new Database())->connect();
                    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
                    $today_month = date('m');
                    $today_day = date('d');
                    if ($driver === 'sqlite') {
                        $date_format_m = "strftime('%m', birth_date)";
                        $date_format_d = "strftime('%d', birth_date)";
                    } else {
                        $date_format_m = "DATE_FORMAT(birth_date, '%m')";
                        $date_format_d = "DATE_FORMAT(birth_date, '%d')";
                    }
                    $sql = "SELECT * FROM members WHERE $date_format_m = '$today_month' AND $date_format_d = '$today_day'";
                    $congregation_id = $_SESSION['user_congregation_id'] ?? null;
                    if ($congregation_id) {
                        $sql .= " AND congregation_id = " . (int)$congregation_id;
                    }
                    $todayBirthdays = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $todayBirthdays = [];
                }
            }
            ?>

            <?php if (!empty($todayBirthdays ?? [])): ?>
                <div class="modal fade" id="todayBirthdaysModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0">
                                <h5 class="modal-title text-warning">
                                    <i class="fas fa-birthday-cake me-2"></i> Aniversariantes de Hoje
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-0">
                                <ul class="list-group list-group-flush">
                                    <?php foreach (($todayBirthdays ?? []) as $b): ?>
                                        <?php $memberName = (string)($b['name'] ?? ''); ?>
                                        <?php
                                        $parts = preg_split('/\s+/', trim($memberName));
                                        $memberShort = $memberName;
                                        if (is_array($parts) && count($parts) >= 2) {
                                            $memberShort = $parts[0] . ' ' . $parts[count($parts) - 1];
                                        } elseif (is_array($parts) && count($parts) === 1) {
                                            $memberShort = $parts[0];
                                        }
                                        ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light border-start border-warning border-4">
                                            <div class="fw-semibold text-truncate me-2" style="min-width: 0;">
                                                <span class="d-inline d-sm-none"><?= htmlspecialchars($memberShort) ?></span>
                                                <span class="d-none d-sm-inline"><?= htmlspecialchars($memberName) ?></span>
                                                <span class="badge bg-warning text-dark ms-2">Hoje!</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                <span class="badge bg-info rounded-pill">
                                                    <?= date('d/m', strtotime($b['birth_date'])) ?>
                                                </span>
                                                <a class="btn btn-sm btn-outline-success" href="/admin/dashboard?birthday_card=<?= urlencode($memberName) ?>" title="Gerar Cartão">
                                                    <i class="fas fa-gift"></i>
                                                </a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var modalEl = document.getElementById('todayBirthdaysModal');
                        if (!modalEl) return;
                        var force = <?= !empty($forceTodayBirthdayModal ?? false) ? 'true' : 'false' ?>;
                        var key = 'today_birthdays_modal_shown_' + <?= (int)($_SESSION['user_id'] ?? 0) ?> + '_' + '<?= date('Y-m-d') ?>';
                        if (!force) {
                            if (localStorage.getItem(key) === '1') return;
                            localStorage.setItem(key, '1');
                        }

                        var tries = 0;
                        function tryShow() {
                            tries++;
                            if (window.bootstrap && bootstrap.Modal) {
                                try {
                                    new bootstrap.Modal(modalEl).show();
                                } catch (e) {
                                }
                                return;
                            }
                            if (tries < 80) {
                                setTimeout(tryShow, 50);
                            }
                        }
                        tryShow();
                    });
                </script>
            <?php endif; ?>

            <!-- System Payment Alert Modal Logic -->
            <?php
            // Simple logic to check if we need to show the modal (only on admin pages)
            // Ideally this should be passed from a global controller or middleware, 
            // but for simplicity we can do a quick check here if user is logged in.
            
            // Check status only if we are in admin panel
            if (isLoggedIn() && strpos($_SERVER['REQUEST_URI'], '/admin') === 0) {
                // We need to check payment status.
                // Reusing logic from SystemPaymentController essentially.
                // To avoid DB calls on every page load, maybe use session?
                // But user wants "always updated". DB call is safer.
                
                try {
                    $billingSyncService = new CentralBillingSyncService();
                    if ($billingSyncService->hasRemoteConfig()) {
                        $billingSyncService->syncFromCentral();
                    }

                    $db = (new Database())->connect();
                    $currentMonth = date('Y-m');
                    
                    // Check payment status
                    $hasDueDateColumn = false;
                    if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                        $stmtCol = $db->prepare("SHOW COLUMNS FROM `system_payments` LIKE ?");
                        $stmtCol->execute(['due_date']);
                        $hasDueDateColumn = (bool)$stmtCol->fetch();
                    } else {
                        $stmtCol = $db->query("PRAGMA table_info(system_payments)");
                        $cols = $stmtCol->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($cols as $col) {
                            if (($col['name'] ?? '') === 'due_date') {
                                $hasDueDateColumn = true;
                                break;
                            }
                        }
                    }

                    $select = $hasDueDateColumn
                        ? "SELECT reference_month, status, due_date, payment_date FROM system_payments WHERE status <> 'paid' ORDER BY reference_month ASC"
                        : "SELECT reference_month, status, payment_date FROM system_payments WHERE status <> 'paid' ORDER BY reference_month ASC";
                    $stmt = $db->query($select);
                    $systemPaymentAlertRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $systemPaymentShowAlert = false;
                    $systemPaymentAlertType = '';
                    $systemPaymentDueDateText = '05/' . date('m/Y');
                    
                    $systemPaymentAlertCurrent = null;
                    $systemPaymentClosestDaysRemaining = null;
                    foreach ($systemPaymentAlertRows as $candidate) {
                        $candidateDueDateRaw = $candidate['due_date'] ?? ($candidate['payment_date'] ?? (($candidate['reference_month'] ?? $currentMonth) . '-05 00:00:00'));
                        $candidateDueDate = date('Y-m-d', strtotime($candidateDueDateRaw));
                        $candidateDaysRemaining = (int)floor((strtotime($candidateDueDate) - strtotime(date('Y-m-d'))) / 86400);

                        if ($systemPaymentAlertCurrent === null || $candidateDaysRemaining < $systemPaymentClosestDaysRemaining) {
                            $systemPaymentAlertCurrent = $candidate;
                            $systemPaymentClosestDaysRemaining = $candidateDaysRemaining;
                            $systemPaymentDueDateText = date('d/m/Y', strtotime($candidateDueDateRaw));
                        }
                    }

                    if ($systemPaymentAlertCurrent) {
                        $systemPaymentDaysRemaining = $systemPaymentClosestDaysRemaining;
                        if ($systemPaymentDaysRemaining < 0) {
                            $systemPaymentShowAlert = true;
                            $systemPaymentAlertType = 'overdue';
                        } elseif ($systemPaymentDaysRemaining === 0) {
                            $systemPaymentShowAlert = true;
                            $systemPaymentAlertType = 'today';
                        } elseif ($systemPaymentDaysRemaining <= 2 && $systemPaymentDaysRemaining > 0) {
                            $systemPaymentShowAlert = true;
                            $systemPaymentAlertType = 'alert';
                        }
                    }
                    
                    if ($systemPaymentShowAlert):
            ?>
                <!-- Modal -->
                <div class="modal fade" id="paymentAlertModal" tabindex="-1" aria-labelledby="paymentAlertLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-<?= $systemPaymentAlertType == 'overdue' ? 'danger' : 'warning' ?> text-white">
                        <h5 class="modal-title" id="paymentAlertLabel">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <?= $systemPaymentAlertType == 'overdue' ? 'Pagamento Atrasado!' : ($systemPaymentAlertType == 'today' ? 'Pagamento Vence Hoje' : 'Lembrete de Pagamento') ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="fs-5">
                            <?= $systemPaymentAlertType == 'overdue' 
                                ? 'O pagamento da hospedagem/domínio venceu em ' . $systemPaymentDueDateText . ' e ainda não consta no sistema.' 
                                : ($systemPaymentAlertType == 'today'
                                    ? 'O pagamento da hospedagem/domínio vence hoje (' . $systemPaymentDueDateText . ').'
                                    : 'O pagamento da hospedagem/domínio vence em ' . $systemPaymentDueDateText . '.') ?>
                        </p>
                        <p><?= $systemPaymentAlertType == 'overdue'
                            ? 'Por favor, regularize a situação.'
                            : ($systemPaymentAlertType == 'today'
                                ? 'Hoje é a data de vencimento. Se possível, realize o pagamento para evitar atraso.'
                                : 'Se desejar, já pode se programar para realizar o pagamento dentro do prazo.') ?></p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <a href="/admin/system-payments" class="btn btn-primary">Ir para Pagamento</a>
                      </div>
                    </div>
                  </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var modalElement = document.getElementById('paymentAlertModal');
                        var myModal = new bootstrap.Modal(modalElement);
                        var alertType = '<?= $systemPaymentAlertType ?>';
                        var storageKey = 'system_payment_alert_last_shown';
                        var sessionKey = 'system_payment_alert_session_shown_<?= (int)($_SESSION["user_id"] ?? 0) ?>_<?= session_id() ?>';
                        var now = Date.now();
                        
                        if (alertType === 'overdue' || alertType === 'today') {
                            myModal.show();
                            return;
                        }

                        if (!sessionStorage.getItem(sessionKey)) {
                            myModal.show();
                            sessionStorage.setItem(sessionKey, '1');
                            localStorage.setItem(storageKey, String(now));
                            return;
                        }

                        var lastShownAt = parseInt(localStorage.getItem(storageKey) || '0', 10);
                        var tenMinutes = 10 * 60 * 1000;

                        if (!lastShownAt || (now - lastShownAt) >= tenMinutes) {
                            myModal.show();
                            localStorage.setItem(storageKey, String(now));
                        }
                    });
                </script>
            <?php 
                    endif;
                } catch (Exception $e) {
                    // Silent fail
                }
            }
            ?>
            <div class="app-page-content">
<?php else: // Member/Public View ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>" style="height: 32px; width: auto; object-fit: contain;">
                <span><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/">Início</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
<?php endif; ?>

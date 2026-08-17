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
        .sidebar { min-height: 100vh; box-shadow: 2px 0 5px rgba(0,0,0,0.1); background: #fff; }
        .sidebar-brand {
            padding: 1.5rem 1.25rem 1.25rem;
        }
        .sidebar-brand h4 {
            font-weight: 800;
            letter-spacing: .02em;
        }
        .sidebar .nav {
            padding: 0 .7rem;
        }
        .sidebar .nav-item {
            margin-bottom: .15rem;
        }
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            border-radius: 10px;
            padding: .55rem .8rem;
            font-size: .9rem;
            font-weight: 600;
            transition: background-color .15s ease, color .15s ease;
        }
        .sidebar .nav-link i.fas,
        .sidebar .nav-link i.fab {
            width: 20px;
            flex: 0 0 auto;
            text-align: center;
            font-size: .92rem;
            margin-right: .7rem !important;
        }
        .sidebar .nav-link:hover:not(.active) {
            background-color: rgba(179,0,0,0.06);
            color: #b30000 !important;
        }
        .sidebar .nav-link.active {
            box-shadow: 0 2px 6px rgba(179,0,0,0.25);
        }
        .sidebar .nav-link[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }
        .sidebar .nav-link .fa-chevron-down {
            transition: transform .2s ease;
        }
        .sidebar-heading {
            font-size: .72rem !important;
            font-weight: 800 !important;
            letter-spacing: .08em !important;
            color: #adb5bd !important;
            padding-left: .8rem !important;
            padding-right: .8rem !important;
            margin-top: 1.4rem !important;
        }
        .sidebar .nav ul.collapse {
            padding-left: 0;
            margin-top: .15rem;
            margin-bottom: .25rem;
            margin-left: 1.35rem;
            border-left: 2px solid rgba(0,0,0,0.07);
        }
        .sidebar .nav ul.collapse .nav-link {
            padding: .45rem .8rem;
            margin-left: .5rem;
            font-size: .84rem;
            font-weight: 500;
        }
        .sidebar .nav ul.collapse .nav-link.active {
            background-color: rgba(179,0,0,0.10);
            color: #b30000 !important;
            box-shadow: none;
            font-weight: 700;
        }
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
        @media (max-width: 991.98px) {
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
    <?php if (!$isMobileLauncherPage && empty($suppressMobileTopbar)): ?>
        <nav class="navbar navbar-dark bg-primary mb-2 d-lg-none">
            <div class="container-fluid">
                <?php
                $navTitle = ($siteProfile['alias'] ?? 'IVN') . ' Admin';
                if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'secretary') {
                    $navTitle = ($siteProfile['alias'] ?? 'IVN') . ' Secretaria';
                }
                ?>
                <button type="button" id="mobileBackBtn" class="btn btn-sm btn-outline-light me-2" aria-label="Voltar" data-fallback="<?= htmlspecialchars($mobileLauncherHref) ?>">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <a class="btn btn-sm btn-outline-light me-2 menu-attention-glow" href="<?= htmlspecialchars($mobileLauncherHref) ?>" aria-label="Menu">
                    <i class="fas fa-th-large"></i>
                </a>
                <a class="navbar-brand flex-grow-1" href="<?= htmlspecialchars($mobileHomeHref) ?>"><?= htmlspecialchars($navTitle) ?></a>
                <a class="btn btn-sm btn-outline-light" href="/admin/logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </nav>
        <script>
            (function () {
                var btn = document.getElementById('mobileBackBtn');
                if (!btn) return;
                btn.addEventListener('click', function () {
                    var cameFromSameSite = document.referrer && document.referrer.indexOf(window.location.origin) === 0;
                    if (cameFromSameSite && window.history.length > 1) {
                        window.history.back();
                    } else {
                        window.location.href = btn.getAttribute('data-fallback');
                    }
                });
            })();
        </script>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-lg-2 d-lg-block bg-white sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="sidebar-brand px-3 pb-3 border-bottom text-center">
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

                        <?php if (hasPermission('donations.view') || hasPermission('donations.manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/donations') !== false ? 'active' : 'text-dark' ?>" href="/admin/donations">
                                <i class="fas fa-hand-holding-heart me-2"></i> Doações (PIX)
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Financeiro -->
                        <?php if (hasPermission('financial.view')): ?>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-muted text-uppercase small">
                            <span>Financeiro</span>
                        </h6>
                        <?php
                        $inFinanceiroSubmenu = strpos($_SERVER['REQUEST_URI'], '/admin/financial') !== false
                            || strpos($_SERVER['REQUEST_URI'], '/admin/tithes') !== false
                            || strpos($_SERVER['REQUEST_URI'], '/admin/expenses') !== false;
                        ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center <?= $inFinanceiroSubmenu ? 'active' : 'text-dark' ?>" href="#financeiroSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $inFinanceiroSubmenu ? 'true' : 'false' ?>" aria-controls="financeiroSubmenu">
                                <span><i class="fas fa-chart-line me-2"></i> Gestão Financeira e Contábil</span>
                                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
                            </a>
                            <ul class="collapse flex-column ms-3 nav <?= $inFinanceiroSubmenu ? 'show' : '' ?>" id="financeiroSubmenu">
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
            <main class="ms-sm-auto col-lg-10 px-lg-4 py-2 py-lg-4">
                <?php
                $loggedUserName = $_SESSION['user_name'] ?? $_SESSION['username'] ?? '';
                $loggedUserRole = $_SESSION['user_role'] ?? '';

                $topbarRoleLabels = ['admin' => 'Administrador', 'secretary' => 'Secretária(o)', 'accountant' => 'Contador'];
                $topbarRoleLabel = $topbarRoleLabels[$loggedUserRole] ?? ucfirst((string)$loggedUserRole);

                $topbarWeekDays = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
                $topbarMonthNames = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                $topbarDateFormatted = $topbarWeekDays[(int)date('w')] . ', ' . (int)date('j') . ' de ' . $topbarMonthNames[(int)date('n') - 1] . ' de ' . date('Y');

                $topbarNameParts = preg_split('/\s+/', trim((string)$loggedUserName));
                $topbarInitials = '';
                if (!empty($topbarNameParts[0])) $topbarInitials .= mb_substr($topbarNameParts[0], 0, 1);
                if (count($topbarNameParts) > 1) $topbarInitials .= mb_substr(end($topbarNameParts), 0, 1);
                $topbarInitials = mb_strtoupper($topbarInitials !== '' ? $topbarInitials : '?');
                ?>
                <style>
                    .app-topbar {
                        position: sticky;
                        top: 0;
                        z-index: 1025;
                        background: #fff;
                        border: 1px solid rgba(0,0,0,0.07);
                        border-radius: 14px;
                        padding: .65rem 1.1rem;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
                    }
                    .app-topbar-mark {
                        width: 38px;
                        height: 38px;
                        border-radius: 10px;
                        background: linear-gradient(135deg, #b30000, #7a0000);
                        color: #fff;
                        font-weight: 800;
                        font-size: .85rem;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        letter-spacing: .02em;
                        flex: 0 0 auto;
                    }
                    .app-topbar-product {
                        font-weight: 800;
                        font-size: .92rem;
                        color: #1a1a1a;
                        line-height: 1.1;
                    }
                    .app-topbar-tagline {
                        font-size: .7rem;
                        color: #adb5bd;
                        font-weight: 600;
                        letter-spacing: .01em;
                    }
                    .app-topbar-date {
                        font-size: .85rem;
                        font-weight: 600;
                        color: #495057;
                    }
                    .app-topbar-date i { color: #b30000; }
                    .app-topbar-user-btn {
                        display: flex;
                        align-items: center;
                        gap: .6rem;
                        border: 1px solid rgba(0,0,0,0.08);
                        border-radius: 999px;
                        padding: .3rem .6rem .3rem .3rem;
                        background: #fafafa;
                    }
                    .app-topbar-user-btn:hover { background: #f1f1f1; }
                    .app-topbar-user-btn::after { display: none; }
                    .app-topbar-avatar {
                        width: 32px;
                        height: 32px;
                        border-radius: 50%;
                        background: rgba(179,0,0,0.10);
                        color: #b30000;
                        font-weight: 800;
                        font-size: .82rem;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex: 0 0 auto;
                    }
                    .app-topbar-user-name {
                        font-size: .84rem;
                        font-weight: 700;
                        color: #1a1a1a;
                        line-height: 1.1;
                    }
                    .app-topbar-user-btn .fa-chevron-down {
                        transition: transform .2s ease;
                        color: #6c757d;
                        font-size: .7rem;
                    }
                    .dropdown.show .app-topbar-user-btn .fa-chevron-down {
                        transform: rotate(180deg);
                    }
                    .app-topbar .role-pill {
                        display: inline-block;
                        padding: .1rem .5rem;
                        border-radius: 999px;
                        font-size: .66rem;
                        font-weight: 700;
                        margin-top: .1rem;
                    }
                    .app-topbar .role-pill.role-admin { background: rgba(179,0,0,0.10); color: #b30000; }
                    .app-topbar .role-pill.role-secretary { background: rgba(13,110,253,0.10); color: #0d6efd; }
                    .app-topbar .role-pill.role-accountant { background: rgba(25,135,84,0.10); color: #198754; }
                </style>
                <div class="app-topbar d-none d-lg-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="app-topbar-mark">SC</div>
                        <div>
                            <div class="app-topbar-product">SistemaChurch</div>
                            <div class="app-topbar-tagline">PAINEL DE GESTÃO ECLESIÁSTICA</div>
                        </div>
                    </div>
                    <div class="app-topbar-date d-none d-lg-flex align-items-center gap-2">
                        <i class="far fa-calendar-alt"></i>
                        <span><?= htmlspecialchars($topbarDateFormatted) ?></span>
                    </div>
                    <div class="dropdown">
                        <button class="btn app-topbar-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="app-topbar-avatar"><?= htmlspecialchars($topbarInitials) ?></span>
                            <span class="d-none d-lg-flex flex-column align-items-start">
                                <span class="app-topbar-user-name"><?= htmlspecialchars((string)$loggedUserName) ?></span>
                                <span class="role-pill role-<?= htmlspecialchars((string)$loggedUserRole) ?>"><?= htmlspecialchars($topbarRoleLabel) ?></span>
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="/admin/change-password"><i class="fas fa-key me-2 text-muted"></i> Alterar Senha</a></li>
                            <li><a class="dropdown-item" href="/admin/manual"><i class="fas fa-book me-2 text-muted"></i> Manual / Ajuda</a></li>
                            <li><a class="dropdown-item" href="/" target="_blank"><i class="fas fa-arrow-up-right-from-square me-2 text-muted"></i> Ver Site</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/admin/logout"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                        </ul>
                    </div>
                </div>

                <?php if ($isMobileLauncherPage): ?>
                    <div class="d-lg-none">
                        <?php include __DIR__ . '/mobile_launcher.php'; ?>
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
                    $sql = "SELECT m.*, cong.name as congregation_name FROM members m LEFT JOIN congregations cong ON cong.id = m.congregation_id WHERE $date_format_m = '$today_month' AND $date_format_d = '$today_day'";
                    $congregation_id = $_SESSION['user_congregation_id'] ?? null;
                    if ($congregation_id) {
                        $sql .= " AND m.congregation_id = " . (int)$congregation_id;
                    }
                    $todayBirthdays = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $todayBirthdays = [];
                }
            }
            ?>

            <?php if (!empty($todayBirthdays ?? [])): ?>
                <!-- Desktop: celebratory centered modal -->
                <style>
                    .bdayd-modal .modal-dialog { max-width: 420px; }
                    .bdayd-modal .modal-content { border: none; border-radius: 22px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,.25); }
                    .bdayd-body { position: relative; padding: 34px 30px 28px; text-align: center; overflow: hidden; }
                    .bdayd-close { position: absolute; top: 16px; right: 16px; width: 30px; height: 30px; border-radius: 50%; border: none; background: #f1f2f5; color: #5b6472; display: flex; align-items: center; justify-content: center; font-size: .85rem; z-index: 2; }
                    .bdayd-confetti { position: absolute; border-radius: 50%; }
                    .bdayd-confetti.c1 { top: 20px; left: 30px; width: 8px; height: 8px; background: #ffc107; }
                    .bdayd-confetti.c2 { top: 55px; left: 16px; width: 5px; height: 5px; background: #3b6fef; }
                    .bdayd-confetti.c3 { top: 14px; left: 80px; width: 4px; height: 4px; background: #7c4fd1; }
                    .bdayd-confetti.c4 { top: 24px; right: 74px; width: 6px; height: 6px; background: #e0533c; }
                    .bdayd-confetti.c5 { top: 60px; right: 30px; width: 5px; height: 5px; background: #18a558; }
                    .bdayd-confetti.c6 { top: 10px; right: 110px; width: 4px; height: 4px; background: #ffc107; }
                    .bdayd-icon { width: 76px; height: 76px; margin: 6px auto 16px; border-radius: 22px; background: linear-gradient(135deg, #ffd76a, #ffb238); display: flex; align-items: center; justify-content: center; font-size: 2rem; box-shadow: 0 10px 22px rgba(255,178,56,.35); }
                    .bdayd-title { font-weight: 800; font-size: 1.3rem; color: #101828; margin-bottom: .3rem; }
                    .bdayd-subtitle { font-size: .9rem; color: #8b93a3; margin-bottom: 22px; }
                    .bdayd-cards { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 22px; max-height: 320px; overflow-y: auto; text-align: left; }
                    .bdayd-card { display: flex; align-items: center; gap: .8rem; border: 1px solid #eef1f5; border-radius: 16px; padding: .85rem 1rem; }
                    .bdayd-avatar { flex: 0 0 auto; width: 46px; height: 46px; border-radius: 50%; background: linear-gradient(135deg, #ff6fa5, #b06fff); color: #fff; font-weight: 800; font-size: .85rem; display: flex; align-items: center; justify-content: center; }
                    .bdayd-info { flex: 1 1 auto; min-width: 0; }
                    .bdayd-name { font-weight: 800; font-size: .95rem; color: #101828; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                    .bdayd-meta { font-size: .78rem; color: #8b93a3; margin-top: 2px; display: flex; align-items: center; gap: .35rem; }
                    .bdayd-meta-dot { width: 6px; height: 6px; border-radius: 50%; background: #18a558; flex: 0 0 auto; }
                    .bdayd-gift-btn { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: rgba(24,165,88,.12); color: #18a558; border: none; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
                    .bdayd-actions { display: flex; gap: .7rem; margin-bottom: 16px; }
                    .bdayd-btn { flex: 1 1 0; border: none; border-radius: 999px; padding: .75rem 0; font-weight: 700; font-size: .88rem; color: #fff; display: flex; align-items: center; justify-content: center; gap: .4rem; }
                    .bdayd-btn.is-card { background: linear-gradient(135deg, #ffd76a, #ffb238); color: #7a4a00; }
                    .bdayd-btn.is-whatsapp { background: #18a558; }
                    .bdayd-viewall-link { display: block; font-size: .86rem; font-weight: 700; color: #3b6fef; text-decoration: none; }
                </style>
                <div class="modal fade bdayd-modal" id="todayBirthdaysModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="bdayd-body">
                                <button type="button" class="bdayd-close" data-bs-dismiss="modal" aria-label="Fechar"><i class="fas fa-times"></i></button>
                                <span class="bdayd-confetti c1"></span><span class="bdayd-confetti c2"></span><span class="bdayd-confetti c3"></span>
                                <span class="bdayd-confetti c4"></span><span class="bdayd-confetti c5"></span><span class="bdayd-confetti c6"></span>
                                <div class="bdayd-icon"><i class="fas fa-birthday-cake"></i></div>
                                <?php
                                    $bdayDCount = count($todayBirthdays ?? []);
                                    $bdayDFirst = $bdayDCount === 1 ? (string)(($todayBirthdays[0]['name'] ?? '')) : '';
                                    $bdayDFirstName = trim(explode(' ', trim($bdayDFirst))[0] ?? '');
                                ?>
                                <?php if ($bdayDCount === 1): ?>
                                    <div class="bdayd-title">Feliz aniversário, <?= htmlspecialchars($bdayDFirstName) ?>!</div>
                                    <div class="bdayd-subtitle">Hoje é dia de celebrar 🎉</div>
                                <?php else: ?>
                                    <div class="bdayd-title">Aniversariantes de Hoje</div>
                                    <div class="bdayd-subtitle"><?= $bdayDCount ?> pessoas fazendo aniversário hoje</div>
                                <?php endif; ?>

                                <div class="bdayd-cards">
                                    <?php foreach (($todayBirthdays ?? []) as $b): ?>
                                        <?php
                                        $memberName = (string)($b['name'] ?? '');
                                        $dParts = preg_split('/\s+/', trim($memberName));
                                        $dInitials = mb_strtoupper(mb_substr($dParts[0], 0, 1) . (count($dParts) > 1 ? mb_substr(end($dParts), 0, 1) : ''), 'UTF-8');
                                        ?>
                                        <div class="bdayd-card">
                                            <span class="bdayd-avatar"><?= htmlspecialchars($dInitials) ?></span>
                                            <div class="bdayd-info">
                                                <div class="bdayd-name"><?= htmlspecialchars($memberName) ?></div>
                                                <?php
                                                    $dMesesPt = [1=>'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
                                                    $dBirthTs = strtotime($b['birth_date']);
                                                    $dDateLabel = date('d', $dBirthTs) . ' de ' . $dMesesPt[(int)date('n', $dBirthTs)];
                                                ?>
                                                <div class="bdayd-meta">
                                                    <span class="bdayd-meta-dot"></span>
                                                    <?= $dDateLabel ?><?= !empty($b['congregation_name']) ? ' • ' . htmlspecialchars($b['congregation_name']) : '' ?> • Hoje
                                                </div>
                                            </div>
                                            <?php if ($bdayDCount > 1): ?>
                                                <button type="button" class="bdayd-gift-btn bdayd-open-card" data-birthday-name="<?= htmlspecialchars($memberName, ENT_QUOTES) ?>" title="Gerar Cartão"><i class="fas fa-gift"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($bdayDCount === 1): ?>
                                    <div class="bdayd-actions">
                                        <button type="button" class="bdayd-btn is-card bdayd-download-card" data-birthday-name="<?= htmlspecialchars($bdayDFirst, ENT_QUOTES) ?>"><i class="fas fa-gift"></i> Baixar Cartão</button>
                                        <button type="button" class="bdayd-btn is-whatsapp bdayd-whatsapp-card" data-birthday-name="<?= htmlspecialchars($bdayDFirst, ENT_QUOTES) ?>"><i class="fab fa-whatsapp"></i> WhatsApp</button>
                                    </div>
                                <?php endif; ?>

                                <a href="/admin?view=aniversariantes" class="bdayd-viewall-link">Ver lista completa do mês</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile: celebratory bottom sheet -->
                <style>
                    .bday-sheet.offcanvas-bottom { border-top-left-radius: 24px; border-top-right-radius: 24px; height: auto; max-height: 88vh; background: #fff; }
                    .bday-sheet-inner { position: relative; padding: 14px 22px 24px; overflow: hidden; }
                    .bday-drag-handle { width: 36px; height: 4px; background: #e3e7ee; border-radius: 2px; margin: 0 auto 8px; }
                    .bday-close-btn { position: absolute; top: 14px; right: 18px; width: 30px; height: 30px; border-radius: 50%; border: none; background: #f1f2f5; color: #5b6472; display: flex; align-items: center; justify-content: center; font-size: .85rem; z-index: 2; }
                    .bday-confetti { position: absolute; border-radius: 50%; }
                    .bday-confetti.c1 { top: 18px; left: 22px; width: 8px; height: 8px; background: #ffc107; }
                    .bday-confetti.c2 { top: 46px; left: 12px; width: 5px; height: 5px; background: #3b6fef; }
                    .bday-confetti.c3 { top: 10px; left: 62px; width: 4px; height: 4px; background: #7c4fd1; }
                    .bday-confetti.c4 { top: 20px; right: 60px; width: 6px; height: 6px; background: #e0533c; }
                    .bday-confetti.c5 { top: 52px; right: 26px; width: 5px; height: 5px; background: #18a558; }
                    .bday-confetti.c6 { top: 8px; right: 90px; width: 4px; height: 4px; background: #ffc107; }
                    .bday-icon-circle { width: 72px; height: 72px; margin: 8px auto 14px; border-radius: 50%; background: linear-gradient(135deg, #ffd76a, #ffb238); display: flex; align-items: center; justify-content: center; font-size: 1.9rem; box-shadow: 0 8px 18px rgba(255,178,56,.35); }
                    .bday-title { text-align: center; font-weight: 800; font-size: 1.15rem; color: #101828; }
                    .bday-subtitle { text-align: center; font-size: .84rem; color: #8b93a3; margin-bottom: 18px; }
                    .bday-cards { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 18px; max-height: 42vh; overflow-y: auto; }
                    .bday-card { display: flex; align-items: center; gap: .7rem; border: 1px solid #eef1f5; border-radius: 16px; padding: .75rem .85rem; }
                    .bday-avatar { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #ff6fa5, #b06fff); color: #fff; font-weight: 800; font-size: .8rem; display: flex; align-items: center; justify-content: center; }
                    .bday-info { flex: 1 1 auto; min-width: 0; }
                    .bday-name { font-weight: 800; font-size: .9rem; color: #101828; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                    .bday-meta { font-size: .74rem; color: #9aa4b2; margin-top: 1px; }
                    .bday-right { flex: 0 0 auto; text-align: right; }
                    .bday-pill-today { display: inline-block; background: #ffc107; color: #7a4a00; font-size: .64rem; font-weight: 800; padding: .15rem .55rem; border-radius: 999px; margin-bottom: 3px; }
                    .bday-date { font-size: .76rem; font-weight: 700; color: #101828; line-height: 1.1; }
                    .bday-date-sub { display: block; font-size: .62rem; font-weight: 600; color: #9aa4b2; }
                    .bday-gift-btn { flex: 0 0 auto; width: 34px; height: 34px; border-radius: 50%; background: rgba(24,165,88,.12); color: #18a558; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: .95rem; }
                    .bday-viewall-link { display: block; text-align: center; font-size: .84rem; font-weight: 700; color: #3b6fef; text-decoration: none; }
                </style>
                <div class="offcanvas offcanvas-bottom bday-sheet" tabindex="-1" id="todayBirthdaysSheetMobile">
                    <div class="bday-sheet-inner">
                        <div class="bday-drag-handle"></div>
                        <button type="button" class="bday-close-btn" data-bs-dismiss="offcanvas" aria-label="Fechar"><i class="fas fa-times"></i></button>
                        <span class="bday-confetti c1"></span><span class="bday-confetti c2"></span><span class="bday-confetti c3"></span>
                        <span class="bday-confetti c4"></span><span class="bday-confetti c5"></span><span class="bday-confetti c6"></span>
                        <div class="bday-icon-circle"><i class="fas fa-birthday-cake"></i></div>
                        <div class="bday-title">Aniversariantes de Hoje</div>
                        <div class="bday-subtitle">
                            <?php $bdayCount = count($todayBirthdays ?? []); ?>
                            <?= $bdayCount ?> pessoa<?= $bdayCount === 1 ? '' : 's' ?> fazendo aniversário hoje
                        </div>
                        <div class="bday-cards">
                            <?php foreach (($todayBirthdays ?? []) as $b): ?>
                                <?php
                                $memberName = (string)($b['name'] ?? '');
                                $mParts = preg_split('/\s+/', trim($memberName));
                                $mInitials = mb_strtoupper(mb_substr($mParts[0], 0, 1) . (count($mParts) > 1 ? mb_substr(end($mParts), 0, 1) : ''), 'UTF-8');
                                ?>
                                <div class="bday-card">
                                    <span class="bday-avatar"><?= htmlspecialchars($mInitials) ?></span>
                                    <div class="bday-info">
                                        <div class="bday-name"><?= htmlspecialchars($memberName) ?></div>
                                        <div class="bday-meta">Aniversário<?= !empty($b['congregation_name']) ? ' • ' . htmlspecialchars($b['congregation_name']) : '' ?></div>
                                    </div>
                                    <div class="bday-right">
                                        <span class="bday-pill-today">Hoje</span>
                                        <div class="bday-date"><?= date('d/m', strtotime($b['birth_date'])) ?><span class="bday-date-sub">hoje</span></div>
                                    </div>
                                    <a class="bday-gift-btn" href="/admin/dashboard?birthday_card=<?= urlencode($memberName) ?>" data-birthday-name="<?= htmlspecialchars($memberName, ENT_QUOTES) ?>" title="Gerar Cartão"><i class="fas fa-gift"></i></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="/admin?view=aniversariantes" class="bday-viewall-link">Ver todos os aniversariantes do mês</a>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        // A birthday-card link (gift button, own sheet/modal) already targets one
                        // specific person and opens its own #birthdayModal on load — showing the
                        // general "aniversariantes de hoje" reminder on top of that at the same
                        // time stacks two Bootstrap overlays (two backdrops, competing body
                        // scroll-lock), which can leave the page looking broken instead of
                        // showing either one cleanly. Skip the reminder whenever we've just
                        // navigated somewhere to show a specific person's card.
                        if (new URLSearchParams(window.location.search).has('birthday_card')) return;

                        var isDesktop = window.matchMedia('(min-width: 992px)').matches;
                        var elId = isDesktop ? 'todayBirthdaysModal' : 'todayBirthdaysSheetMobile';
                        var el = document.getElementById(elId);
                        if (!el) return;
                        var force = <?= !empty($forceTodayBirthdayModal ?? false) ? 'true' : 'false' ?>;
                        var key = 'today_birthdays_modal_shown_' + <?= (int)($_SESSION['user_id'] ?? 0) ?> + '_' + '<?= date('Y-m-d') ?>';
                        if (!force) {
                            if (localStorage.getItem(key) === '1') return;
                            localStorage.setItem(key, '1');
                        }

                        var tries = 0;
                        function tryShow() {
                            tries++;
                            if (window.bootstrap && bootstrap.Modal && bootstrap.Offcanvas) {
                                try {
                                    if (isDesktop) {
                                        new bootstrap.Modal(el).show();
                                    } else {
                                        new bootstrap.Offcanvas(el).show();
                                    }
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

                    // The gift button's href is a fallback (/admin/dashboard?birthday_card=NAME)
                    // for whichever admin page doesn't happen to already render dashboard.php's
                    // #birthdayModal underneath it. Most of the time it does (e.g. the launcher,
                    // which is dashboard.php with the launcher grid drawn on top via CSS), so open
                    // the card modal in place instead of navigating away just to show a popup.
                    document.addEventListener('click', function (e) {
                        var btn = e.target.closest('.bday-gift-btn');
                        if (!btn || typeof window.openBirthdayCard !== 'function') return;
                        e.preventDefault();

                        // Force-close the reminder sheet synchronously — don't wait on its
                        // hidden.bs.offcanvas event before opening the card modal. That event
                        // depends on the CSS transition actually completing, and if it doesn't
                        // fire (interrupted transition, reduced-motion, etc.) the modal never
                        // opens and the user is left staring at a stray backdrop. Bootstrap's
                        // hide() plus stripping the classes/backdrop by hand guarantees the
                        // sheet is gone right away regardless of animation state.
                        var sheetEl = document.getElementById('todayBirthdaysSheetMobile');
                        if (sheetEl) {
                            var sheetInst = window.bootstrap ? bootstrap.Offcanvas.getInstance(sheetEl) : null;
                            if (sheetInst) {
                                try { sheetInst.hide(); } catch (err) {}
                            }
                            sheetEl.classList.remove('show', 'showing', 'hiding');
                        }
                        document.querySelectorAll('.offcanvas-backdrop').forEach(function (bd) { bd.remove(); });

                        window.openBirthdayCard(btn.getAttribute('data-birthday-name') || '');
                    });

                    // Desktop reminder modal actions: reuse the exact same card-generator
                    // modal/functions the gift button already opens rather than re-implementing
                    // image capture or WhatsApp sharing here.
                    function bdaydCloseReminder() {
                        var el = document.getElementById('todayBirthdaysModal');
                        var inst = el && window.bootstrap ? bootstrap.Modal.getInstance(el) : null;
                        if (inst) { try { inst.hide(); } catch (err) {} }
                        if (el) el.classList.remove('show');
                        document.querySelectorAll('.modal-backdrop').forEach(function (bd) { bd.remove(); });
                    }

                    document.addEventListener('click', function (e) {
                        var openBtn = e.target.closest('.bdayd-open-card');
                        if (openBtn && typeof window.openBirthdayCard === 'function') {
                            bdaydCloseReminder();
                            window.openBirthdayCard(openBtn.getAttribute('data-birthday-name') || '');
                            return;
                        }

                        var dlBtn = e.target.closest('.bdayd-download-card');
                        if (dlBtn && typeof window.openBirthdayCard === 'function') {
                            bdaydCloseReminder();
                            window.openBirthdayCard(dlBtn.getAttribute('data-birthday-name') || '');
                            // Give the modal/background image a moment to render before
                            // html2canvas captures it.
                            setTimeout(function () {
                                if (typeof window.downloadCard === 'function') window.downloadCard();
                            }, 450);
                            return;
                        }

                        var waBtn = e.target.closest('.bdayd-whatsapp-card');
                        if (waBtn && typeof window.openBirthdayCard === 'function') {
                            bdaydCloseReminder();
                            window.openBirthdayCard(waBtn.getAttribute('data-birthday-name') || '');
                            if (typeof window.shareWhatsApp === 'function') window.shareWhatsApp();
                            return;
                        }
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

                    try {
                        $usersSyncService = new CentralUsersSyncService();
                        if ($usersSyncService->hasRemoteConfig()) {
                            $usersSyncService->syncSilently();
                        }
                    } catch (Throwable $e) {
                        // Never block page render on a users-sync failure —
                        // catches Error too (e.g. class/file missing), not
                        // just Exception, since either would otherwise crash
                        // every admin page load with a blank white screen.
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
                <!-- Banner de pagamento (topo da tela, substitui o antigo modal) -->
                <?php
                    $paymentAlertIsDanger = $systemPaymentAlertType == 'overdue';
                    $paymentAlertTitle = $paymentAlertIsDanger ? 'Mensalidade em Aberto' : ($systemPaymentAlertType == 'today' ? 'Mensalidade Vence Hoje' : 'Lembrete de Mensalidade');
                    $paymentAlertIcon = $paymentAlertIsDanger ? 'fa-exclamation-circle' : ($systemPaymentAlertType == 'today' ? 'fa-exclamation-triangle' : 'fa-clock');
                    $paymentAlertMainText = $paymentAlertIsDanger
                        ? 'Sua mensalidade venceu em ' . $systemPaymentDueDateText . ' e ainda não identificamos o pagamento.'
                        : ($systemPaymentAlertType == 'today'
                            ? 'Sua mensalidade vence hoje (' . $systemPaymentDueDateText . ').'
                            : 'Sua mensalidade vence em ' . $systemPaymentDueDateText . '.');
                ?>
                <div class="alert <?= $paymentAlertIsDanger ? 'alert-danger' : 'alert-warning' ?> d-flex align-items-center justify-content-between flex-wrap gap-3 shadow-sm mb-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas <?= $paymentAlertIcon ?> mt-1"></i>
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($paymentAlertTitle) ?></div>
                            <div class="small"><?= htmlspecialchars($paymentAlertMainText) ?></div>
                        </div>
                    </div>
                    <a href="/admin/system-payments" class="btn btn-sm <?= $paymentAlertIsDanger ? 'btn-danger' : 'btn-dark' ?> fw-semibold text-nowrap">Ir para Pagamento</a>
                </div>
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

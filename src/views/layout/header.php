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
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>?v=1">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>?v=1">
    <!-- PWA / Web App Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
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
        .sidebar-brand-logo { max-height: 42px; max-width: 100%; object-fit: contain; }
    </style>
</head>
<body>
<?php if (isLoggedIn() && (strpos($_SERVER['REQUEST_URI'], '/admin') === 0 || strpos($_SERVER['REQUEST_URI'], '/developer') === 0)): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 d-md-none">
        <div class="container-fluid">
            <?php 
            $navTitle = ($siteProfile['alias'] ?? 'IVN') . ' Admin';
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'secretary') {
                $navTitle = ($siteProfile['alias'] ?? 'IVN') . ' Secretaria';
            }
            ?>
            <a class="navbar-brand" href="/admin"><?= $navTitle ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- Principal -->
                    <?php if (hasPermission('dashboard.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/admin' || $_SERVER['REQUEST_URI'] === '/admin/' ? 'active' : '' ?>" href="/admin"><i class="fas fa-home me-2"></i> Painel</a></li>
                    <?php endif; ?>
                    
                    <!-- Secretaria -->
                    <?php if (hasPermission('members.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/members') !== false ? 'active' : '' ?>" href="/admin/members"><i class="fas fa-users me-2"></i> Membros</a></li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('congregations.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/congregations') !== false ? 'active' : '' ?>" href="/admin/congregations"><i class="fas fa-church me-2"></i> Congregações</a></li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('events.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/events') !== false ? 'active' : '' ?>" href="/admin/events"><i class="fas fa-calendar-alt me-2"></i> Eventos / Cultos</a></li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('service_reports.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/service_reports') !== false ? 'active' : '' ?>" href="/admin/service_reports"><i class="fas fa-clipboard-list me-2"></i> Relatórios de Culto</a></li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('general_reports.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/reports/general') !== false ? 'active' : '' ?>" href="/admin/reports/general"><i class="fas fa-chart-pie me-2"></i> Estatísticas Gerais</a></li>
                    <?php endif; ?>

                    <?php if (hasPermission('signatures.view') || hasPermission('signatures.manage')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/signatures') !== false ? 'active' : '' ?>" href="/admin/signatures"><i class="fas fa-file-signature me-2"></i> Assinaturas</a></li>
                    <?php endif; ?>

                    <?php if (hasPermission('groups.view') || hasPermission('groups.manage')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/groups') !== false ? 'active' : '' ?>" href="/admin/groups"><i class="fas fa-users-cog me-2"></i> Grupos/Células</a></li>
                    <?php endif; ?>

                    <?php if (hasPermission('gallery.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/gallery') !== false ? 'active' : '' ?>" href="/admin/gallery"><i class="fas fa-images me-2"></i> Galeria</a></li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('banners.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/banners') !== false ? 'active' : '' ?>" href="/admin/banners"><i class="fas fa-image me-2"></i> Banners</a></li>
                    <?php endif; ?>

                    <!-- Financeiro -->
                    <?php if (hasPermission('financial.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/tithes') !== false ? 'active' : '' ?>" href="/admin/tithes"><i class="fas fa-hand-holding-usd me-2"></i> Entradas</a></li>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/expenses') !== false ? 'active' : '' ?>" href="/admin/expenses"><i class="fas fa-file-invoice-dollar me-2"></i> Saídas</a></li>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/financial/report') !== false ? 'active' : '' ?>" href="/admin/financial/report"><i class="fas fa-chart-line me-2"></i> Relatório Financeiro</a></li>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/financial/closures') !== false ? 'active' : '' ?>" href="/admin/financial/closures"><i class="fas fa-lock me-2"></i> Fechamentos</a></li>
                    <?php endif; ?>
                    
                    <!-- Ensino -->
                    <?php if (hasPermission('ebd.view') || hasPermission('ebd.manage')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/ebd') !== false ? 'active' : '' ?>" href="/admin/ebd/classes"><i class="fas fa-book-open me-2"></i> Escola Bíblica (EBD)</a></li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('studies.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/studies') !== false ? 'active' : '' ?>" href="/admin/studies"><i class="fas fa-book me-2"></i> Estudos</a></li>
                    <?php endif; ?>
                    
                    <!-- Configurações -->
                    <?php if (hasPermission('users.manage')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/permissions') !== false ? 'active' : '' ?> d-flex align-items-center justify-content-between" href="#submenuUsuariosMobile" data-bs-toggle="collapse" aria-expanded="<?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/permissions') !== false ? 'true' : 'false' ?>">
                            <span><i class="fas fa-users-cog me-2"></i> Contas/Usuários</span>
                            <i class="fas fa-chevron-down submenu-icon"></i>
                        </a>
                        <ul class="collapse list-unstyled ms-3 <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/permissions') !== false ? 'show' : '' ?>" id="submenuUsuariosMobile">
                            <li><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false && strpos($_SERVER['REQUEST_URI'], 'permissions') === false ? 'active text-primary' : '' ?>" href="/admin/users"><i class="fas fa-user ms-2"></i> Usuários</a></li>
                            <?php if (hasPermission('permissions.manage') || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer')): ?>
                            <li><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/permissions') !== false ? 'active text-primary' : '' ?>" href="/admin/permissions"><i class="fas fa-key ms-2"></i> Permissões (RBAC)</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('settings.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/site-settings') !== false ? 'active' : '' ?> d-flex align-items-center justify-content-between" href="#submenuSettings" data-bs-toggle="collapse" aria-expanded="<?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/site-settings') !== false ? 'true' : 'false' ?>">
                            <span><i class="fas fa-cogs me-2"></i> Configurações</span>
                            <i class="fas fa-chevron-down submenu-icon"></i>
                        </a>
                        <ul class="collapse list-unstyled ms-3 <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/site-settings') !== false ? 'show' : '' ?>" id="submenuSettings">
                            <?php if (hasPermission('settings.layout.view')): ?>
                            <li><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/site-settings') !== false ? 'active text-primary' : '' ?>" href="/admin/site-settings"><i class="fas fa-paint-roller ms-2"></i> Layout do Site</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission('settings.card.view')): ?>
                            <li><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings/card-layout') !== false ? 'active text-primary' : '' ?>" href="/admin/settings/card-layout"><i class="fas fa-id-card ms-2"></i> Modelo Carteirinha</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission('settings.system.view')): ?>
                            <li><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false && strpos($_SERVER['REQUEST_URI'], 'card-layout') === false && strpos($_SERVER['REQUEST_URI'], 'site-settings') === false ? 'active text-primary' : '' ?>" href="/admin/settings"><i class="fas fa-sliders-h ms-2"></i> Sistema Geral</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('system_payments.view')): ?>
                        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/system-payments') !== false ? 'active' : '' ?>" href="/admin/system-payments"><i class="fas fa-credit-card me-2"></i> Pagamento Sistema</a></li>
                    <?php endif; ?>
                    
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
                    <li class="nav-item"><a class="nav-link text-warning" href="/migrate.php" target="_blank"><i class="fas fa-database me-2"></i> Atualizar Banco</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/manual') !== false ? 'active' : '' ?>" href="/admin/manual"><i class="fas fa-question-circle me-2"></i> Manual</a></li>
                    <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/change-password') !== false ? 'active' : '' ?>" href="/admin/change-password"><i class="fas fa-key me-2"></i> Mudar Senha</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="/admin/logout"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                </ul>
            </div>
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
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

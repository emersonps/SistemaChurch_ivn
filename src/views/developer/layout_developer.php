<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Desenvolvedor - Sistema Igreja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; display: block; padding: 10px 15px; }
        .sidebar a:hover { color: white; background-color: rgba(255,255,255,.1); }
        .sidebar .active { background-color: #0d6efd; color: white; }
        /* Mobile Adjustments */
        @media (max-width: 767.98px) {
            .sidebar { min-height: auto; }
            .sidebar-collapse { display: none; }
            .sidebar-collapse.show { display: block; }
        }
    </style>
</head>
<body>
    <!-- Mobile Navbar -->
    <nav class="navbar navbar-dark bg-dark d-md-none mb-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Painel do Dev</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#devSidebarMenu" aria-controls="devSidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="devSidebarMenu">
                <?php
                // Check for pending migrations
                $pendingCount = 0;
                try {
                    require_once __DIR__ . '/../../database/MigrationRunner.php';
                    $runner = new MigrationRunner();
                    $pendingCount = $runner->getPendingCount();
                } catch (Exception $e) { }
                ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/developer/dashboard') !== false || $_SERVER['REQUEST_URI'] === '/developer') && strpos($_SERVER['REQUEST_URI'], '/developer/payments') === false ? 'active' : '' ?>" href="/developer/dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i> Painel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/logs') !== false ? 'active' : '' ?>" href="/developer/logs">
                            <i class="fas fa-satellite-dish me-2"></i> Acessos e Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/system-payments') !== false || strpos($_SERVER['REQUEST_URI'], '/developer/payments') !== false ? 'active' : '' ?>" href="/developer/payments">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Gerenciar Pagamentos
                        </a>
                    </li>
                    <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/migrations') !== false ? 'active' : '' ?>" href="/developer/migrations">
                                <i class="fas fa-database me-2"></i> Migrações
                                <?php if ($pendingCount > 0): ?>
                                    <span class="badge bg-danger ms-2"><?= $pendingCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/users') !== false || strpos($_SERVER['REQUEST_URI'], '/developer/roles') !== false ? 'active' : '' ?>" href="/developer/users">
                            <i class="fas fa-users-cog me-2"></i> Gerenciar Permissões (Roles)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/backups') !== false ? 'active' : '' ?>" href="/developer/backups">
                            <i class="fas fa-box-archive me-2"></i> Backups do Banco
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/manuals') !== false ? 'active' : '' ?>" href="/developer/manuals">
                            <i class="fas fa-circle-play me-2"></i> Gerenciar Manuais
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/manual-sync') !== false ? 'active' : '' ?>" href="/developer/manual-sync">
                            <i class="fas fa-arrows-rotate me-2"></i> Sincronização da Central
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/import') !== false && strpos($_SERVER['REQUEST_URI'], '/developer/import/expenses') === false ? 'active' : '' ?>" href="/developer/import">
                            <i class="fas fa-file-import me-2"></i> Importar Entradas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/import/expenses') !== false ? 'active' : '' ?>" href="/developer/import/expenses">
                            <i class="fas fa-file-export me-2"></i> Importar Saídas
                        </a>
                    </li>
                    <li class="nav-item border-top mt-2 pt-2">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/settings') !== false ? 'active' : '' ?>" href="/developer/settings">
                            <i class="fas fa-paint-roller me-2"></i> Ajustes do Site (White Label)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/admin/logout">
                            <i class="fas fa-sign-out-alt me-2"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Desktop Sidebar -->
            <nav class="col-md-3 col-lg-2 d-none d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <h4 class="px-3 mb-4 text-white">Painel do Dev</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/developer/dashboard') !== false || $_SERVER['REQUEST_URI'] === '/developer') && strpos($_SERVER['REQUEST_URI'], '/developer/payments') === false ? 'active' : '' ?>" href="/developer/dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i> Painel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/logs') !== false ? 'active' : '' ?>" href="/developer/logs">
                                <i class="fas fa-satellite-dish me-2"></i> Acessos e Logs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/system-payments') !== false || strpos($_SERVER['REQUEST_URI'], '/developer/payments') !== false ? 'active' : '' ?>" href="/developer/payments">
                                <i class="fas fa-file-invoice-dollar me-2"></i> Gerenciar Pagamentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/migrations') !== false ? 'active' : '' ?>" href="/developer/migrations">
                                <i class="fas fa-database me-2"></i> Migrações
                                <?php if ($pendingCount > 0): ?>
                                    <span class="badge bg-danger ms-2"><?= $pendingCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/users') !== false || strpos($_SERVER['REQUEST_URI'], '/developer/roles') !== false ? 'active' : '' ?>" href="/developer/users">
                                <i class="fas fa-users-cog me-2"></i> Gerenciar Permissões (Roles)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/backups') !== false ? 'active' : '' ?>" href="/developer/backups">
                                <i class="fas fa-box-archive me-2"></i> Backups do Banco
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/manuals') !== false ? 'active' : '' ?>" href="/developer/manuals">
                                <i class="fas fa-circle-play me-2"></i> Gerenciar Manuais
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/manual-sync') !== false ? 'active' : '' ?>" href="/developer/manual-sync">
                                <i class="fas fa-arrows-rotate me-2"></i> Sincronização da Central
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/import') !== false && strpos($_SERVER['REQUEST_URI'], '/developer/import/expenses') === false ? 'active' : '' ?>" href="/developer/import">
                                <i class="fas fa-file-import me-2"></i> Importar Entradas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/import/expenses') !== false ? 'active' : '' ?>" href="/developer/import/expenses">
                                <i class="fas fa-file-export me-2"></i> Importar Saídas
                            </a>
                        </li>
                        <li class="nav-item border-top mt-2 pt-2">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/developer/settings') !== false ? 'active' : '' ?>" href="/developer/settings">
                                <i class="fas fa-paint-roller me-2"></i> Ajustes do Site (White Label)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="/admin/logout">
                                <i class="fas fa-sign-out-alt me-2"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Membro - IVN</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/img/logo.png?v=1">
    <link rel="apple-touch-icon" href="/assets/img/logo.png?v=1">
    <!-- PWA / Web App Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#dc3545">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); }
        .sidebar .nav-link { color: #333; font-weight: 500; }
        .sidebar .nav-link.active { color: #dc3545; background-color: #f8f9fa; }
        .sidebar .nav-link:hover { color: #dc3545; }
        @media (max-width: 767.98px) {
            .sidebar { min-height: auto; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-danger sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/portal/dashboard"><i class="fas fa-church"></i> IVN Membro</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="nav-link text-white">Olá, <?= htmlspecialchars($_SESSION['member_name']) ?></span>
                </li>
                <li class="nav-item"><a class="nav-link" href="/portal/logout"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] == '/portal/dashboard' ? 'active' : '' ?>" href="/portal/dashboard">
                            <i class="fas fa-home me-2"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] == '/portal/profile' ? 'active' : '' ?>" href="/portal/profile">
                            <i class="fas fa-user-edit me-2"></i> Meus Dados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] == '/portal/change-password' ? 'active' : '' ?>" href="/portal/change-password">
                            <i class="fas fa-key me-2"></i> Alterar Senha
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] == '/portal/manual' ? 'active' : '' ?>" href="/portal/manual">
                            <i class="fas fa-circle-play me-2"></i> Manual
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] == '/portal/financial' ? 'active' : '' ?>" href="/portal/financial">
                            <i class="fas fa-hand-holding-usd me-2"></i> Meus Dízimos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] == '/portal/card' ? 'active' : '' ?>" href="/portal/card">
                            <i class="fas fa-id-card me-2"></i> Minha Carteirinha
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] == '/portal/agenda' ? 'active' : '' ?>" href="/portal/agenda">
                            <i class="fas fa-calendar-alt me-2"></i> Agenda da Igreja
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] == '/portal/documents' ? 'active' : '' ?>" href="/portal/documents">
                            <i class="fas fa-file-alt me-2"></i> Meus Documentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/portal/logout">
                            <i class="fas fa-sign-out-alt me-2"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

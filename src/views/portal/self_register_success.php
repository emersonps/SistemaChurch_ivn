<?php $siteProfile = getChurchSiteProfileSettings(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Realizado - <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>?v=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-red: #b30000; }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .simple-header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-container { display: flex; justify-content: space-between; align-items: center; }
        .brand-logo-header {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand-logo-header img { height: 40px; width: auto; }
        .nav-link-home {
            color: #333;
            font-weight: 600;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s;
        }
        .nav-link-home:hover { background-color: var(--primary-red); color: white; }
        .login-card { max-width: 450px; margin: 60px auto; border-radius: 15px; overflow: hidden; }
        .success-icon { font-size: 4rem; color: #198754; }
    </style>
</head>
<body>

<header class="simple-header">
    <div class="container header-container">
        <a href="/" class="brand-logo-header">
            <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?> Logo">
            <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>
        </a>
        <a href="/" class="nav-link-home">
            <i class="fas fa-home me-1"></i> Início
        </a>
    </div>
</header>

<div class="container">
    <div class="card login-card shadow-lg border-0 text-center">
        <div class="card-body p-4">
            <i class="fas fa-check-circle success-icon mb-3"></i>
            <h4 class="mb-2">Cadastro realizado com sucesso!</h4>
            <p class="text-muted mb-4">Agora crie sua senha de acesso para acessar a área do membro.</p>
            <div class="d-grid gap-2">
                <a href="/portal/register" class="btn btn-danger">
                    <i class="fas fa-user-plus me-1"></i> Fazer Primeiro Acesso
                </a>
                <a href="/" class="btn btn-outline-secondary">Voltar para o site</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>

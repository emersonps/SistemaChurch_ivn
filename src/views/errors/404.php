<?php
try {
    $siteProfile = getChurchSiteProfileSettings();
    $logoUrl = getChurchLogoUrl($siteProfile, true);
    $brandingName = getChurchBrandingName($siteProfile);
} catch (Throwable $e) {
    $logoUrl = '/assets/img/logo.png';
    $brandingName = 'Sistema Church';
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$isBackoffice = strpos($requestUri, '/admin') === 0 || strpos($requestUri, '/developer') === 0;
$homeUrl = strpos($requestUri, '/developer') === 0 ? '/developer/migrations' : ($isBackoffice ? '/admin/dashboard' : '/');
$homeLabel = $isBackoffice ? 'Voltar ao Painel' : 'Voltar ao Início';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - <?= htmlspecialchars($brandingName) ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #1f2937 0%, #0b0f19 65%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding: 1.5rem;
        }
        .nf-card {
            max-width: 480px;
            width: 100%;
            text-align: center;
            color: #f8f9fa;
        }
        .nf-logo {
            height: 40px;
            margin-bottom: 2rem;
            object-fit: contain;
        }
        .nf-code {
            font-size: 6.5rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #6366f1, #38bdf8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -2px;
        }
        .nf-icon {
            font-size: 1.4rem;
            color: #64748b;
            margin-bottom: .75rem;
        }
        .nf-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: .5rem;
        }
        .nf-subtitle {
            color: #94a3b8;
            font-size: .95rem;
            margin-bottom: 2rem;
        }
        .nf-actions .btn {
            border-radius: 999px;
            padding: .6rem 1.6rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="nf-card">
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="" class="nf-logo" onerror="this.style.display='none'">
        <div class="nf-icon"><i class="fas fa-compass"></i></div>
        <div class="nf-code">404</div>
        <div class="nf-title">Página não encontrada</div>
        <p class="nf-subtitle">O endereço acessado não existe ou foi movido. Confira o link ou volte para um lugar seguro.</p>
        <div class="nf-actions d-flex gap-2 justify-content-center flex-wrap">
            <a href="<?= htmlspecialchars($homeUrl) ?>" class="btn btn-primary"><i class="fas fa-house me-2"></i><?= htmlspecialchars($homeLabel) ?></a>
            <a href="javascript:history.back()" class="btn btn-outline-light"><i class="fas fa-arrow-left me-2"></i>Página Anterior</a>
        </div>
    </div>
</body>
</html>

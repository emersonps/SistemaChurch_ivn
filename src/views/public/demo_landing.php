<?php
// Shown at / instead of the normal homepage when demo_landing_enabled is
// on for this instance. $demo (array from DemoLandingService::getDisplayCredentials())
// and $demoConfig (from getConfig()) are passed in by HomeController.
$siteProfile = getChurchSiteProfileSettings();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteProfile['name'] ?? 'IgrejaBR') ?> — Ambiente Demonstrativo</title>
    <link rel="icon" href="/assets/img/demo-landing-logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(160deg, #eef4ff 0%, #f7fbff 45%, #eefaf6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.25rem;
            color: #101828;
        }
        .demo-card {
            max-width: 640px;
            width: 100%;
            text-align: center;
        }
        .demo-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            margin-bottom: .75rem;
        }
        .demo-brand {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: .35rem;
        }
        .demo-brand .accent { color: #16a34a; }
        .demo-tag {
            display: inline-block;
            letter-spacing: .12em;
            font-size: .7rem;
            font-weight: 700;
            color: #667085;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
        }
        .demo-headline {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 .5rem;
            color: #0b1220;
        }
        .demo-subheadline {
            color: #475467;
            font-size: 1.05rem;
            margin: 0 0 1.75rem;
        }
        .demo-access-box {
            background: #fff;
            border: 1px solid #e4e9f2;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1rem 2.5rem rgba(16, 24, 40, .06);
            margin-bottom: 1.75rem;
        }
        .demo-access-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #eafff3;
            color: #067647;
            font-weight: 700;
            font-size: .75rem;
            padding: .3rem .75rem;
            border-radius: 999px;
            margin-bottom: .75rem;
        }
        .demo-access-text {
            color: #475467;
            font-size: .95rem;
            margin: 0 0 .9rem;
        }
        .demo-access-link {
            font-size: 1.3rem;
            font-weight: 800;
            color: #2563eb;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }
        .demo-access-link:hover { text-decoration: underline; }
        .demo-credentials {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .85rem;
            margin-bottom: 1rem;
        }
        @media (max-width: 620px) {
            .demo-credentials { grid-template-columns: 1fr; }
        }
        .demo-cred-card {
            background: #fff;
            border: 1px solid #e4e9f2;
            border-radius: .85rem;
            padding: 1rem;
            text-align: left;
        }
        .demo-cred-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #eef4ff;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            margin-bottom: .5rem;
        }
        .demo-cred-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #667085;
            margin-bottom: .4rem;
        }
        .demo-cred-row {
            font-size: .78rem;
            color: #667085;
            margin-bottom: .1rem;
        }
        .demo-cred-value {
            font-family: 'Courier New', monospace;
            background: #f2f4f7;
            border-radius: .35rem;
            padding: .1rem .4rem;
            font-weight: 700;
            color: #101828;
        }
        .demo-rotation-note {
            font-size: .82rem;
            color: #b54708;
            background: #fffaeb;
            border: 1px solid #fedf89;
            border-radius: .6rem;
            padding: .6rem .9rem;
            margin-bottom: 1.75rem;
        }
        .demo-footer-text {
            color: #667085;
            font-size: .9rem;
            margin-bottom: .75rem;
        }
        .demo-footer-tags {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #98a2b3;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/partials/example_content_banner.php'; ?>
    <div class="demo-card">
        <img src="/assets/img/demo-landing-logo.png" alt="<?= htmlspecialchars($siteProfile['name'] ?? 'IgrejaBR') ?>" class="demo-logo">
        <div class="demo-brand"><?= htmlspecialchars($siteProfile['name'] ?? 'IgrejaBR') ?></div>
        <div class="demo-tag">Ambiente Demonstrativo</div>

        <h1 class="demo-headline">Conheça o <?= htmlspecialchars($siteProfile['name'] ?? 'IgrejaBR') ?> na prática!</h1>
        <p class="demo-subheadline">Sistema completo de gestão para igrejas com site integrado.</p>

        <div class="demo-access-box">
            <div class="demo-access-pill"><i class="fas fa-rocket"></i> Acesso liberado · portal demonstrativo</div>
            <p class="demo-access-text">Acesse o portal demonstrativo e explore todos os recursos da plataforma, e veja como o produto real será entregue para você/sua igreja.</p>
            <?php if (!empty($demoConfig['public_url'])): ?>
                <a href="/site" target="_blank" rel="noopener noreferrer" class="demo-access-link">
                    <i class="fas fa-globe"></i> <?= htmlspecialchars(preg_replace('#^https?://#', '', $demoConfig['public_url'])) ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($demo['credentials'])): ?>
            <div class="demo-credentials">
                <?php
                $icons = ['Administrador' => 'fa-user-shield', 'Secretaria' => 'fa-clipboard', 'Membro' => 'fa-user'];
                ?>
                <?php foreach ($demo['credentials'] as $cred): ?>
                    <div class="demo-cred-card">
                        <div class="demo-cred-icon"><i class="fas <?= $icons[$cred['label']] ?? 'fa-user' ?>"></i></div>
                        <div class="demo-cred-label"><?= htmlspecialchars($cred['label']) ?></div>
                        <div class="demo-cred-row">Usuário: <span class="demo-cred-value"><?= htmlspecialchars($cred['username']) ?></span></div>
                        <div class="demo-cred-row">Senha: <span class="demo-cred-value"><?= htmlspecialchars($cred['password']) ?></span></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="demo-rotation-note">
                <i class="fas fa-rotate me-1"></i>
                Por segurança, essas senhas são renovadas automaticamente a cada <?= (int)$demo['rotation_days'] ?> dias — se elas pararem de funcionar, é só recarregar esta página para ver as novas.
            </div>
        <?php endif; ?>

        <p class="demo-footer-text">Teste à vontade. Explore os recursos e conheça tudo o que o <?= htmlspecialchars($siteProfile['name'] ?? 'IgrejaBR') ?> pode oferecer à sua igreja.</p>
        <div class="demo-footer-tags">Gestão completa · Site integrado · Identidade própria</div>
    </div>
</body>
</html>

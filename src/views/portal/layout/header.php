<?php
$siteProfile = getChurchSiteProfileSettings();

// Lightweight member lookup for the topbar (photo + role). Session only stores id/name.
$portalTopbarPhoto = null;
$portalTopbarRole = 'Membro';
if (isset($_SESSION['member_id'])) {
    try {
        $portalDb = (new Database())->connect();
        $stmtTopbar = $portalDb->prepare("SELECT photo, role FROM members WHERE id = ?");
        $stmtTopbar->execute([$_SESSION['member_id']]);
        $topbarRow = $stmtTopbar->fetch(PDO::FETCH_ASSOC);
        if ($topbarRow) {
            $portalTopbarPhoto = $topbarRow['photo'] ?? null;
            $portalTopbarRole = !empty($topbarRow['role']) ? $topbarRow['role'] : 'Membro';
        }
    } catch (Exception $e) {
        // Keep defaults
    }
}

$portalMemberName = $_SESSION['member_name'] ?? 'Membro';
$portalNameParts = preg_split('/\s+/', trim((string)$portalMemberName));
$portalInitials = '';
if (!empty($portalNameParts[0])) $portalInitials .= mb_substr($portalNameParts[0], 0, 1);
if (count($portalNameParts) > 1) $portalInitials .= mb_substr(end($portalNameParts), 0, 1);
$portalInitials = mb_strtoupper($portalInitials !== '' ? $portalInitials : '?');
$portalFirstName = $portalNameParts[0] ?? 'Membro';

// Shared destination list — used by the bottom "Mais" sheet, the quick-jump search, and the dashboard launcher grid.
$portalNavGroups = [
    'Minha Conta' => [
        ['label' => 'Meus Dados', 'subtitle' => 'Editar cadastro', 'icon' => 'fa-user-pen', 'href' => '/portal/profile', 'color' => 'blue'],
        ['label' => 'Minha Carteirinha', 'subtitle' => 'Credencial digital', 'icon' => 'fa-id-card', 'href' => '/portal/card', 'color' => 'purple'],
        ['label' => 'Alterar Senha', 'subtitle' => 'Segurança da conta', 'icon' => 'fa-key', 'href' => '/portal/change-password', 'color' => 'slate'],
        ['label' => 'Meus Documentos', 'subtitle' => 'Arquivos pessoais', 'icon' => 'fa-folder-open', 'href' => '/portal/documents', 'color' => 'gray'],
    ],
    'Igreja' => [
        ['label' => 'Agenda da Igreja', 'subtitle' => 'Cultos e eventos', 'icon' => 'fa-calendar-days', 'href' => '/portal/agenda', 'color' => 'orange'],
        ['label' => 'Estudos e Esboços', 'subtitle' => 'Materiais em PDF', 'icon' => 'fa-book-bible', 'href' => '/portal/studies', 'color' => 'teal'],
    ],
    'Financeiro' => [
        ['label' => 'Meus Dízimos', 'subtitle' => 'Contribuições e ofertas', 'icon' => 'fa-hand-holding-dollar', 'href' => '/portal/financial', 'color' => 'green'],
        ['label' => 'Saúde Financeira', 'subtitle' => 'Como cuidamos da nossa casa', 'icon' => 'fa-heart-pulse', 'href' => '/portal/financial-health', 'color' => 'red'],
        ['label' => 'Campanhas', 'subtitle' => 'Metas de arrecadação', 'icon' => 'fa-bullseye', 'href' => '/portal/campaigns', 'color' => 'purple'],
    ],
    'Ajuda' => [
        ['label' => 'Manual / Ajuda', 'subtitle' => 'Vídeos e suporte', 'icon' => 'fa-circle-question', 'href' => '/portal/manual', 'color' => 'cyan'],
    ],
];
$portalCurrentUri = $_SERVER['REQUEST_URI'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Membro - <?= htmlspecialchars(getChurchBrandingName($siteProfile)) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    <!-- PWA / Web App Manifest -->
    <link rel="manifest" href="<?= htmlspecialchars(getChurchManifestUrl($siteProfile)) ?>">
    <meta name="theme-color" content="#b30000">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --portal-primary: #b30000;
            --portal-primary-dark: #7a0000;
            --portal-bg: #f5f6fb;
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body {
            background: var(--portal-bg);
            padding-bottom: 84px;
        }

        /* ---------- Topbar ---------- */
        .portal-topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: .6rem 0;
        }
        .portal-topbar-inner {
            max-width: 1080px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }
        .portal-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            text-decoration: none;
            min-width: 0;
        }
        .portal-brand-mark {
            flex: 0 0 auto;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .portal-brand-mark img { width: 100%; height: 100%; object-fit: contain; }
        .portal-brand-name {
            font-weight: 800;
            font-size: .95rem;
            color: #1a1a1a;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .portal-brand-tag {
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .06em;
            color: var(--portal-primary);
            text-transform: uppercase;
        }
        .portal-topbar-actions {
            display: flex;
            align-items: center;
            gap: .4rem;
            flex: 0 0 auto;
        }
        .portal-icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid rgba(0,0,0,0.08);
            background: #fff;
            color: #495057;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .portal-icon-btn:hover { background: #f8f9fa; color: var(--portal-primary); }
        .portal-icon-btn .dot {
            position: absolute;
            top: 6px;
            right: 7px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #dc3545;
            border: 1.5px solid #fff;
        }
        .portal-avatar-btn {
            display: flex;
            align-items: center;
            gap: .55rem;
            border: 1px solid rgba(0,0,0,0.08);
            background: #fafafa;
            border-radius: 999px;
            padding: .25rem .7rem .25rem .25rem;
        }
        .portal-avatar-btn:hover { background: #f1f1f1; }
        .portal-avatar-btn::after { display: none; }
        .portal-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(179,0,0,0.10);
            color: var(--portal-primary);
            font-weight: 800;
            font-size: .78rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            overflow: hidden;
        }
        .portal-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .portal-avatar-name {
            font-size: .82rem;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.15;
        }
        .portal-avatar-role {
            font-size: .68rem;
            color: #868e96;
            font-weight: 600;
        }

        /* ---------- Content shell ---------- */
        .portal-content {
            max-width: 1080px;
            margin: 0 auto;
            padding: 1.25rem 1rem 1rem;
        }
        .portal-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 18px;
            box-shadow: 0 2px 10px rgba(17,17,17,0.04);
        }
        .portal-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .portal-card-title {
            font-weight: 800;
            font-size: 1rem;
            color: #1a1a1a;
            margin: 0;
            display: flex;
            align-items: center;
        }
        .portal-page-title {
            font-weight: 800;
            font-size: 1.4rem;
            color: #1a1a1a;
            margin-bottom: .25rem;
        }
        .portal-breadcrumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .8rem;
            margin-bottom: .9rem;
        }
        .portal-breadcrumb-link {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            color: #868e96;
            text-decoration: none;
            font-weight: 600;
        }
        .portal-breadcrumb-link:hover { color: var(--portal-primary); }
        .portal-breadcrumb-sep { font-size: .55rem; color: #ced4da; }
        .portal-breadcrumb-current { color: #1a1a1a; font-weight: 700; }
        .portal-pill {
            display: inline-block;
            padding: .22rem .7rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
        }
        .portal-pill-red { background: rgba(179,0,0,0.10); color: var(--portal-primary); }
        .portal-pill-green { background: rgba(25,135,84,0.10); color: #198754; }
        .portal-pill-gray { background: #eef0f2; color: #495057; }
        .portal-pill-warning { background: rgba(255,193,7,0.18); color: #997404; }

        /* ---------- Launcher grid ---------- */
        .portal-section-label {
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #868e96;
            margin: 1.4rem 0 .65rem 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .portal-launcher-card {
            display: flex;
            align-items: center;
            gap: .85rem;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 16px;
            padding: .9rem 1rem;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(17,17,17,0.03);
            transition: transform .12s ease, box-shadow .12s ease;
            height: 100%;
        }
        .portal-launcher-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(17,17,17,0.08);
        }
        .portal-launcher-icon {
            flex: 0 0 auto;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }
        .portal-launcher-title { font-weight: 700; font-size: .88rem; color: #1a1a1a; }
        .portal-launcher-subtitle { font-size: .74rem; color: #868e96; }
        .portal-launcher-chevron { margin-left: auto; color: #ced4da; }
        .plc-blue .portal-launcher-icon { background: rgba(13,110,253,.12); color: #0d6efd; }
        .plc-purple .portal-launcher-icon { background: rgba(111,66,193,.12); color: #6f42c1; }
        .plc-slate .portal-launcher-icon { background: rgba(73,80,87,.10); color: #495057; }
        .plc-orange .portal-launcher-icon { background: rgba(253,126,20,.14); color: #c2600f; }
        .plc-teal .portal-launcher-icon { background: rgba(32,201,151,.16); color: #12896f; }
        .plc-gray .portal-launcher-icon { background: rgba(108,117,125,.12); color: #6c757d; }
        .plc-green .portal-launcher-icon { background: rgba(25,135,84,.12); color: #198754; }
        .plc-cyan .portal-launcher-icon { background: rgba(13,202,240,.16); color: #087990; }
        .plc-red .portal-launcher-icon { background: rgba(179,0,0,.10); color: var(--portal-primary); }

        /* ---------- Bottom app nav ---------- */
        .portal-bottom-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1035;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(0,0,0,0.07);
            box-shadow: 0 -2px 12px rgba(17,17,17,0.05);
            padding: .35rem .5rem calc(.35rem + env(safe-area-inset-bottom));
        }
        .portal-bottom-nav-inner {
            max-width: 560px;
            margin: 0 auto;
            display: flex;
            align-items: stretch;
            justify-content: space-between;
        }
        .portal-bottom-nav-item {
            flex: 1 1 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .2rem;
            padding: .4rem .2rem;
            border-radius: 12px;
            color: #868e96;
            text-decoration: none;
            font-size: .66rem;
            font-weight: 700;
            border: none;
            background: none;
        }
        .portal-bottom-nav-item i { font-size: 1.05rem; }
        .portal-bottom-nav-item.active { color: var(--portal-primary); }
        .portal-bottom-nav-item.active .portal-bottom-nav-icon-wrap {
            background: rgba(179,0,0,0.10);
        }
        .portal-bottom-nav-icon-wrap {
            width: 34px;
            height: 26px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ---------- More sheet ---------- */
        #portalMoreSheet .offcanvas-header { border-bottom: 1px solid rgba(0,0,0,0.06); }
        #portalMoreSheet { border-top-left-radius: 20px; border-top-right-radius: 20px; max-height: 85vh; }
        .portal-sheet-item {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .75rem .25rem;
            text-decoration: none;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .portal-sheet-item:last-child { border-bottom: none; }
        .portal-sheet-icon {
            flex: 0 0 auto;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .portal-sheet-title { font-weight: 700; font-size: .88rem; color: #1a1a1a; }
        .portal-sheet-subtitle { font-size: .74rem; color: #868e96; }

        /* ---------- Quick search modal ---------- */
        #portalSearchModal .modal-content { border-radius: 18px; border: none; }
        #portalSearchResults { max-height: 50vh; overflow-y: auto; }
        .portal-search-result {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem .5rem;
            border-radius: 10px;
            text-decoration: none;
            color: inherit;
        }
        .portal-search-result:hover { background: #f8f9fa; }
        .portal-search-result.d-none { display: none !important; }

        @media (min-width: 992px) {
            body { padding-bottom: 0; }
            .portal-bottom-nav { display: none; }
        }
    </style>
</head>
<body>

<header class="portal-topbar">
    <div class="portal-topbar-inner">
        <a class="portal-brand" href="/portal/dashboard">
            <span class="portal-brand-mark">
                <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>">
            </span>
            <span>
                <span class="portal-brand-name d-block"><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></span>
                <span class="portal-brand-tag">Portal do Membro</span>
            </span>
        </a>
        <div class="portal-topbar-actions">
            <button type="button" class="portal-icon-btn" data-bs-toggle="modal" data-bs-target="#portalSearchModal" title="Buscar">
                <i class="fas fa-search"></i>
            </button>
            <a href="/portal/agenda" class="portal-icon-btn" title="Agenda">
                <i class="far fa-bell"></i>
            </a>
            <div class="dropdown">
                <button class="btn portal-avatar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="portal-avatar">
                        <?php if (!empty($portalTopbarPhoto)): ?>
                            <img src="/uploads/members/<?= htmlspecialchars($portalTopbarPhoto) ?>" alt="">
                        <?php else: ?>
                            <?= htmlspecialchars($portalInitials) ?>
                        <?php endif; ?>
                    </span>
                    <span class="d-none d-sm-flex flex-column align-items-start">
                        <span class="portal-avatar-name"><?= htmlspecialchars($portalFirstName) ?></span>
                        <span class="portal-avatar-role"><?= htmlspecialchars($portalTopbarRole) ?></span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="/portal/profile"><i class="fas fa-user-pen me-2 text-muted"></i> Meus Dados</a></li>
                    <li><a class="dropdown-item" href="/portal/change-password"><i class="fas fa-key me-2 text-muted"></i> Alterar Senha</a></li>
                    <li><a class="dropdown-item" href="/portal/manual"><i class="fas fa-circle-question me-2 text-muted"></i> Manual / Ajuda</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/portal/logout"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<!-- Quick Search Modal -->
<div class="modal fade" id="portalSearchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-3">
                <div class="input-group mb-2">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="portalSearchInput" class="form-control border-start-0" placeholder="Ir para..." autocomplete="off">
                </div>
                <div id="portalSearchResults" class="d-grid gap-1">
                    <?php foreach ($portalNavGroups as $groupName => $items): ?>
                        <?php foreach ($items as $item): ?>
                            <a href="<?= htmlspecialchars($item['href']) ?>" class="portal-search-result" data-term="<?= htmlspecialchars(mb_strtolower($item['label'] . ' ' . $groupName, 'UTF-8')) ?>">
                                <span class="portal-sheet-icon plc-<?= $item['color'] ?>"><i class="fas <?= $item['icon'] ?>"></i></span>
                                <span>
                                    <span class="portal-sheet-title d-block"><?= htmlspecialchars($item['label']) ?></span>
                                    <span class="portal-sheet-subtitle"><?= htmlspecialchars($groupName) ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <a href="/portal/dashboard" class="portal-search-result" data-term="inicio painel dashboard">
                        <span class="portal-sheet-icon plc-slate"><i class="fas fa-house"></i></span>
                        <span>
                            <span class="portal-sheet-title d-block">Início</span>
                            <span class="portal-sheet-subtitle">Painel principal</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="portal-content">

<?php
$portalBreadcrumbMap = [];
foreach ($portalNavGroups as $groupName => $items) {
    foreach ($items as $item) {
        $portalBreadcrumbMap[$item['href']] = $item['label'];
    }
}
$portalCurrentPath = parse_url($portalCurrentUri, PHP_URL_PATH) ?: $portalCurrentUri;
$portalIsHome = in_array($portalCurrentPath, ['/portal', '/portal/', '/portal/dashboard'], true);
$portalBreadcrumbLabel = $portalBreadcrumbMap[$portalCurrentPath] ?? null;
?>
<?php if (!$portalIsHome && $portalBreadcrumbLabel): ?>
    <nav class="portal-breadcrumb" aria-label="breadcrumb">
        <a href="/portal/dashboard" class="portal-breadcrumb-link"><i class="fas fa-house"></i> Painel</a>
        <i class="fas fa-chevron-right portal-breadcrumb-sep"></i>
        <span class="portal-breadcrumb-current"><?= htmlspecialchars($portalBreadcrumbLabel) ?></span>
    </nav>
<?php endif; ?>

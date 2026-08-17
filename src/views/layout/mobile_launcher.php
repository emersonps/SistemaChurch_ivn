<?php
// src/views/layout/mobile_launcher.php
// New app-like mobile/tablet launcher for /admin?launcher=1.
// Included from header.php inside the $isMobileLauncherPage block, so every
// variable extracted for the current view (DashboardController::index()) is
// already in scope here — but this block is technically reachable from any
// /admin/* URL with ?launcher=1, so every dashboard-only variable is read
// defensively with `?? null` / `?? []`.

$ml2Members = $members_count ?? null;
$ml2Congregations = $congregations_count ?? null;
$ml2TotalFinancial = $total_financial ?? 0;
$ml2ExpensesSum = $expenses_sum ?? 0;
$ml2HealthPct = $health_pct ?? null;
$ml2HealthTier = $health_tier ?? 'none';
$ml2EbdCount = $ebd_students_count ?? null;
$ml2StudiesCount = $studies_count ?? null;
$ml2ClosurePending = $closure_pending ?? null;
$ml2Alerts = $alerts ?? [];
$ml2CongregationName = null;
if (!empty($_SESSION['user_congregation_id']) && !empty($congregation_stats[0]['congregation_name'])) {
    $ml2CongregationName = $congregation_stats[0]['congregation_name'];
}

$ml2Money = function ($v) {
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
};

// ---- Flat, permission-gated destination lists (identical checks to the classic button list) ----
$ml2Secretaria = [];
if (hasPermission('members.view')) {
    $ml2Secretaria[] = ['label' => 'Membros', 'subtitle' => $ml2Members !== null ? number_format($ml2Members, 0, ',', '.') . ' cadastrados' : 'Gerenciar', 'icon' => 'fa-users', 'color' => 'blue', 'href' => '/admin/members'];
}
if (hasPermission('congregations.view')) {
    $ml2Secretaria[] = ['label' => 'Congregações', 'subtitle' => $ml2Congregations !== null ? $ml2Congregations . ' cadastradas' : 'Gerenciar', 'icon' => 'fa-church', 'color' => 'orange', 'href' => '/admin/congregations'];
}
if (hasPermission('events.view') || hasPermission('events.manage')) {
    $ml2Secretaria[] = ['label' => 'Eventos', 'subtitle' => 'Cultos e agenda', 'icon' => 'fa-calendar-alt', 'color' => 'purple', 'href' => '/admin/events'];
}
if (hasPermission('service_reports.view')) {
    $ml2Secretaria[] = ['label' => 'Relatórios', 'subtitle' => 'Relatórios de culto', 'icon' => 'fa-clipboard-list', 'color' => 'teal', 'href' => '/admin/service_reports'];
}
if (hasPermission('general_reports.view')) {
    $ml2Secretaria[] = ['label' => 'Estatísticas', 'subtitle' => 'Visão geral', 'icon' => 'fa-chart-pie', 'color' => 'cyan', 'href' => '/admin/reports/general'];
}
if (hasPermission('signatures.view') || hasPermission('signatures.manage')) {
    $ml2Secretaria[] = ['label' => 'Assinaturas', 'subtitle' => 'Gerenciar', 'icon' => 'fa-file-signature', 'color' => 'slate', 'href' => '/admin/signatures'];
}
if (hasPermission('groups.view') || hasPermission('groups.manage')) {
    $ml2Secretaria[] = ['label' => 'Grupos', 'subtitle' => 'Grupos e células', 'icon' => 'fa-users-cog', 'color' => 'indigo', 'href' => '/admin/groups'];
}
if (hasPermission('gallery.view')) {
    $ml2Secretaria[] = ['label' => 'Galeria', 'subtitle' => 'Fotos', 'icon' => 'fa-images', 'color' => 'pink', 'href' => '/admin/gallery'];
}
if (hasPermission('banners.view')) {
    $ml2Secretaria[] = ['label' => 'Banners', 'subtitle' => 'Gerenciar', 'icon' => 'fa-image', 'color' => 'gray', 'href' => '/admin/banners'];
}
if (hasPermission('donations.view') || hasPermission('donations.manage')) {
    $ml2Secretaria[] = ['label' => 'Doações', 'subtitle' => 'Gerenciar', 'icon' => 'fa-hand-holding-heart', 'color' => 'red', 'href' => '/admin/donations'];
}

$ml2Financeiro = [];
$ml2HasFinanceiro = hasPermission('financial.view');
if ($ml2HasFinanceiro) {
    $ml2Financeiro[] = ['label' => 'Entradas', 'subtitle' => $ml2Money($ml2TotalFinancial) . ' no mês', 'icon' => 'fa-hand-holding-usd', 'color' => 'green', 'href' => '/admin/tithes'];
    $ml2Financeiro[] = ['label' => 'Saídas', 'subtitle' => $ml2Money($ml2ExpensesSum) . ' no mês', 'icon' => 'fa-file-invoice-dollar', 'color' => 'red', 'href' => '/admin/expenses'];
    $ml2Financeiro[] = ['label' => 'Relatório Fin.', 'subtitle' => 'Ver detalhes', 'icon' => 'fa-chart-line', 'color' => 'blue', 'href' => '/admin/financial/report'];
    $ml2Financeiro[] = ['label' => 'Fechamentos', 'subtitle' => $ml2ClosurePending === true ? 'Mês pendente' : ($ml2ClosurePending === false ? 'Mês fechado' : 'Ver detalhes'), 'icon' => 'fa-lock', 'color' => 'purple', 'href' => '/admin/financial/closures'];
}

$ml2Ensino = [];
$ml2HasEnsino = hasPermission('ebd.view') || hasPermission('ebd.manage') || hasPermission('studies.view');
if (hasPermission('ebd.view') || hasPermission('ebd.manage')) {
    $ml2Ensino[] = ['label' => 'EBD', 'subtitle' => $ml2EbdCount !== null ? $ml2EbdCount . ' alunos ativos' : 'Gerenciar', 'icon' => 'fa-book-open', 'color' => 'orange', 'href' => '/admin/ebd/classes'];
}
if (hasPermission('studies.view')) {
    $ml2Ensino[] = ['label' => 'Estudos', 'subtitle' => $ml2StudiesCount !== null ? $ml2StudiesCount . ' materiais' : 'Gerenciar', 'icon' => 'fa-book', 'color' => 'teal', 'href' => '/admin/studies'];
}

$ml2Sistema = [];
if (hasPermission('users.manage')) {
    $ml2Sistema[] = ['label' => 'Usuários', 'subtitle' => 'Contas do sistema', 'icon' => 'fa-user', 'color' => 'blue', 'href' => '/admin/users'];
}
if (hasPermission('permissions.manage') || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer')) {
    $ml2Sistema[] = ['label' => 'Permissões', 'subtitle' => 'Papéis e acessos', 'icon' => 'fa-key', 'color' => 'purple', 'href' => '/admin/permissions'];
}
if (hasPermission('settings.system.view')) {
    $ml2Sistema[] = ['label' => 'Sistema', 'subtitle' => 'Configurações', 'icon' => 'fa-sliders-h', 'color' => 'slate', 'href' => '/admin/settings'];
}
if (hasPermission('settings.layout.view')) {
    $ml2Sistema[] = ['label' => 'Layout', 'subtitle' => 'Aparência do site', 'icon' => 'fa-paint-roller', 'color' => 'pink', 'href' => '/admin/site-settings'];
}
if (hasPermission('settings.card.view')) {
    $ml2Sistema[] = ['label' => 'Carteirinha', 'subtitle' => 'Modelo de cartão', 'icon' => 'fa-id-card', 'color' => 'cyan', 'href' => '/admin/settings/card-layout'];
}
if (hasPermission('system_payments.view')) {
    $ml2Sistema[] = ['label' => 'Mensalidade', 'subtitle' => 'Pagamentos do sistema', 'icon' => 'fa-credit-card', 'color' => 'green', 'href' => '/admin/system-payments'];
}
$ml2Sistema[] = ['label' => 'Manual', 'subtitle' => 'Ajuda e vídeos', 'icon' => 'fa-question-circle', 'color' => 'gray', 'href' => '/admin/manual'];
$ml2Sistema[] = ['label' => 'Senha', 'subtitle' => 'Alterar senha', 'icon' => 'fa-key', 'color' => 'gray', 'href' => '/admin/change-password'];

$ml2HasSecretaria = count($ml2Secretaria) > 0;

// Which bottom-tab destinations are worth showing (skip empty categories)
$ml2Tabs = [
    ['id' => 'inicio', 'label' => 'Início', 'icon' => 'fa-house', 'always' => true],
    ['id' => 'membros', 'label' => 'Secretaria', 'icon' => 'fa-users', 'always' => $ml2HasSecretaria],
    ['id' => 'financas', 'label' => 'Finanças', 'icon' => 'fa-wallet', 'always' => $ml2HasFinanceiro],
    ['id' => 'ensino', 'label' => 'Ensino', 'icon' => 'fa-book-open', 'always' => $ml2HasEnsino],
    ['id' => 'perfil', 'label' => 'Perfil', 'icon' => 'fa-user', 'always' => true],
];

$ml2FirstName = explode(' ', trim((string)$loggedUserName))[0] ?? '';

function ml2Card($item) {
    $badge = '';
    echo '<a href="' . htmlspecialchars($item['href']) . '" class="ml2-card" data-term="' . htmlspecialchars(mb_strtolower($item['label'] . ' ' . $item['subtitle'], 'UTF-8')) . '">';
    echo '<span class="ml2-card-icon ml2c-' . htmlspecialchars($item['color']) . '"><i class="fas ' . htmlspecialchars($item['icon']) . '"></i></span>';
    echo '<span class="ml2-card-label">' . htmlspecialchars($item['label']) . '</span>';
    echo '<span class="ml2-card-subtitle">' . htmlspecialchars($item['subtitle']) . '</span>';
    echo '</a>';
}
function ml2Section($title, $items) {
    if (empty($items)) return;
    echo '<div class="ml2-section-label">' . htmlspecialchars($title) . '</div>';
    echo '<div class="ml2-grid mb-1">';
    foreach ($items as $item) { ml2Card($item); }
    echo '</div>';
}
?>
<style>
    :root {
        --ml2-primary: #2563eb;
        --ml2-primary-dark: #1d4ed8;
        --ml2-bg: #f3f6fc;
    }
    .ml2-shell { background: var(--ml2-bg); margin: -1rem -0.75rem -1rem; padding: .9rem .9rem calc(90px + env(safe-area-inset-bottom)); min-height: calc(100vh - 56px); }
    .ml2-header { display: flex; align-items: center; justify-content: space-between; gap: .6rem; margin-bottom: .9rem; }
    .ml2-brand { display: flex; align-items: center; gap: .6rem; min-width: 0; }
    .ml2-logo { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 13px; background: #fff; border: 1px solid rgba(17,24,39,.07); box-shadow: 0 2px 8px rgba(17,24,39,.05); display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .ml2-logo img { width: 100%; height: 100%; object-fit: contain; }
    .ml2-brand-text { min-width: 0; }
    .ml2-brand-name { font-size: .92rem; font-weight: 800; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ml2-brand-tag { font-size: .66rem; font-weight: 700; color: var(--ml2-primary); text-transform: uppercase; letter-spacing: .05em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ml2-header-actions { display: flex; align-items: center; gap: .5rem; flex: 0 0 auto; }
    .ml2-avatar-sm { width: 34px; height: 34px; border-radius: 50%; background: rgba(37,99,235,.12); color: var(--ml2-primary); font-weight: 800; font-size: .74rem; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .ml2-bell { position: relative; width: 40px; height: 40px; border-radius: 50%; background: #fff; border: 1px solid rgba(17,24,39,.08); color: #495057; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .ml2-bell .dot { position: absolute; top: 6px; right: 7px; min-width: 15px; height: 15px; padding: 0 3px; border-radius: 999px; background: #dc3545; color: #fff; font-size: .6rem; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 1.5px solid #fff; }
    .ml2-greeting { font-size: 1.25rem; font-weight: 800; color: #16213e; margin-bottom: .1rem; }
    .ml2-date { font-size: .82rem; color: #8b93a7; font-weight: 600; margin-bottom: .9rem; }
    .ml2-hero { background: linear-gradient(135deg, var(--ml2-primary) 0%, #1e3a8a 100%); border-radius: 18px; padding: 1.1rem 1.2rem; color: #fff; position: relative; overflow: hidden; margin-bottom: .9rem; }
    .ml2-hero::after { content: ''; position: absolute; top: -50px; right: -50px; width: 170px; height: 170px; border-radius: 50%; background: rgba(255,255,255,.08); }
    .ml2-hero-eyebrow { font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; opacity: .85; display: flex; align-items: center; gap: .4rem; }
    .ml2-hero-title { font-size: 1rem; font-weight: 700; margin: .2rem 0 .7rem; }
    .ml2-hero-bar { background: rgba(255,255,255,.25); border-radius: 999px; height: 8px; overflow: hidden; position: relative; z-index: 1; }
    .ml2-hero-bar-fill { height: 100%; border-radius: 999px; background: #fff; transition: width .4s ease; }
    .ml2-hero-pct { position: absolute; right: 0; top: -22px; font-size: .8rem; font-weight: 800; }
    .ml2-hero-link { display: inline-block; margin-top: .55rem; font-size: .74rem; font-weight: 700; color: #fff; opacity: .9; text-decoration: none; }
    .ml2-search { position: relative; margin-bottom: 1.1rem; }
    .ml2-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #fff; border-radius: 14px; padding: .65rem .9rem .65rem 2.4rem; font-size: .86rem; }
    .ml2-search input:focus { outline: none; border-color: var(--ml2-primary); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .ml2-search i { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .ml2-section-label { font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #8b93a7; margin: 1.1rem 0 .55rem 2px; }
    .ml2-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .6rem; }
    @media (min-width: 560px) { .ml2-grid { grid-template-columns: repeat(3, 1fr); } }
    .ml2-card { display: flex; flex-direction: column; align-items: flex-start; gap: .45rem; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .85rem .85rem; text-decoration: none; box-shadow: 0 2px 8px rgba(17,17,17,.03); }
    .ml2-card:active { transform: scale(.98); }
    .ml2-card-icon { width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .ml2-card-label { font-weight: 700; font-size: .84rem; color: #16213e; }
    .ml2-card-subtitle { font-size: .72rem; color: #8b93a7; margin-top: -.3rem; }
    .ml2c-blue { background: rgba(37,99,235,.12); color: #2563eb; }
    .ml2c-indigo { background: rgba(79,70,229,.12); color: #4f46e5; }
    .ml2c-purple { background: rgba(124,58,237,.12); color: #7c3aed; }
    .ml2c-teal { background: rgba(13,148,136,.14); color: #0d9488; }
    .ml2c-orange { background: rgba(234,88,12,.13); color: #ea580c; }
    .ml2c-green { background: rgba(22,163,74,.13); color: #16a34a; }
    .ml2c-red { background: rgba(220,38,38,.11); color: #dc2626; }
    .ml2c-gray { background: rgba(100,116,139,.13); color: #64748b; }
    .ml2c-slate { background: rgba(71,85,105,.13); color: #475569; }
    .ml2c-cyan { background: rgba(8,145,178,.13); color: #0891b2; }
    .ml2c-pink { background: rgba(219,39,119,.12); color: #db2777; }
    .ml2-stat-row { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: .3rem; }
    .ml2-stat { background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .8rem .9rem; }
    .ml2-stat-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; margin-bottom: .25rem; }
    .ml2-stat-value { font-size: 1.05rem; font-weight: 800; color: #16213e; }
    .ml2-stat-value.up { color: #16a34a; }
    .ml2-stat-value.down { color: #dc2626; }
    .ml2-balance-hero { background: #16213e; border-radius: 18px; padding: 1.1rem 1.2rem; color: #fff; margin-bottom: .8rem; }
    .ml2-balance-label { font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; opacity: .7; }
    .ml2-balance-value { font-size: 1.55rem; font-weight: 800; margin: .2rem 0 .5rem; }
    .ml2-pill { display: inline-block; padding: .22rem .65rem; border-radius: 999px; font-size: .68rem; font-weight: 700; }
    .ml2-pill-warn { background: rgba(255,193,7,.2); color: #fde68a; }
    .ml2-pill-ok { background: rgba(34,197,94,.2); color: #86efac; }
    .ml2-list-row { display: flex; align-items: center; justify-content: space-between; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 14px; padding: .8rem .95rem; margin-bottom: .55rem; text-decoration: none; color: inherit; }
    .ml2-list-row-title { font-weight: 700; font-size: .85rem; color: #16213e; }
    .ml2-list-row-sub { font-size: .72rem; color: #8b93a7; }
    .ml2-list-row i.chev { color: #ced4da; }
    .ml2-quick-group { display: flex; gap: .55rem; margin-bottom: 1rem; }
    .ml2-quick-btn { flex: 1 1 0; display: flex; flex-direction: column; align-items: center; gap: .4rem; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 14px; padding: .75rem .4rem; text-decoration: none; text-align: center; }
    .ml2-quick-icon { width: 36px; height: 36px; border-radius: 50%; background: rgba(37,99,235,.1); color: var(--ml2-primary); display: flex; align-items: center; justify-content: center; font-size: .95rem; }
    .ml2-quick-label { font-size: .68rem; font-weight: 700; color: #16213e; line-height: 1.2; }
    .ml2-profile-head { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 1.2rem 0 1rem; }
    .ml2-profile-avatar { width: 72px; height: 72px; border-radius: 50%; background: rgba(37,99,235,.12); color: var(--ml2-primary); font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; justify-content: center; margin-bottom: .6rem; }
    .ml2-profile-name { font-weight: 800; font-size: 1.05rem; color: #16213e; }
    .ml2-profile-sub { font-size: .8rem; color: #8b93a7; margin-bottom: .4rem; }
    .ml2-tabpanel { display: none; }
    .ml2-tabpanel.active { display: block; }
    .ml2-bottomnav { position: fixed; left: 0; right: 0; bottom: 0; z-index: 1035; background: rgba(255,255,255,.94); backdrop-filter: blur(10px); border-top: 1px solid rgba(17,24,39,.07); box-shadow: 0 -2px 12px rgba(17,17,17,.06); padding: .3rem .4rem calc(.3rem + env(safe-area-inset-bottom)); }
    .ml2-bottomnav-inner { max-width: 640px; margin: 0 auto; display: flex; align-items: stretch; justify-content: space-between; }
    .ml2-navbtn { flex: 1 1 0; display: flex; flex-direction: column; align-items: center; gap: .2rem; padding: .4rem .2rem; border-radius: 12px; color: #8b93a7; text-decoration: none; font-size: .64rem; font-weight: 700; border: none; background: none; }
    .ml2-navbtn i { font-size: 1.05rem; }
    .ml2-navbtn.active { color: var(--ml2-primary); }
    .ml2-navbtn.active .ml2-navbtn-icon { background: rgba(37,99,235,.12); }
    .ml2-navbtn-icon { width: 34px; height: 26px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    #ml2AlertsSheet .offcanvas-header { border-bottom: 1px solid rgba(0,0,0,.06); }
    #ml2AlertsSheet { border-top-left-radius: 20px; border-top-right-radius: 20px; }
    @media (min-width: 992px) { .ml2-bottomnav { display: none; } }
</style>

<div class="ml2-shell" id="ml2Shell">

    <div class="ml2-header">
        <div class="ml2-brand">
            <span class="ml2-logo">
                <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>">
            </span>
            <div class="ml2-brand-text">
                <div class="ml2-brand-name"><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></div>
                <div class="ml2-brand-tag"><?= htmlspecialchars($topbarRoleLabel ?? '') ?></div>
            </div>
        </div>
        <div class="ml2-header-actions">
            <span class="ml2-avatar-sm" title="<?= htmlspecialchars((string)$loggedUserName) ?>"><?= htmlspecialchars($topbarInitials ?? '?') ?></span>
            <button type="button" class="ml2-bell" data-bs-toggle="offcanvas" data-bs-target="#ml2AlertsSheet" aria-label="Alertas">
                <i class="far fa-bell"></i>
                <?php if (count($ml2Alerts) > 0): ?>
                    <span class="dot"><?= count($ml2Alerts) ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>

    <div class="ml2-greeting">Olá, <?= htmlspecialchars($ml2FirstName ?: 'Usuário') ?>! 👋</div>
    <div class="ml2-date"><?= htmlspecialchars($topbarDateFormatted ?? date('d/m/Y')) ?></div>

    <?php if ($ml2HealthPct !== null): ?>
        <div class="ml2-hero">
            <div class="ml2-hero-eyebrow"><i class="fas fa-shield-heart"></i> SAÚDE DA CASA</div>
            <div class="ml2-hero-title">Nossa casa está <?= $ml2HealthPct >= 80 ? 'em dia' : 'precisando de atenção' ?></div>
            <div class="ml2-hero-bar"><div class="ml2-hero-bar-fill" style="width: <?= (int)$ml2HealthPct ?>%;"></div></div>
            <div style="display:flex; justify-content:flex-end;"><span class="ml2-hero-pct"><?= (int)$ml2HealthPct ?>%</span></div>
            <?php if ($ml2HasFinanceiro): ?>
                <a href="/admin/financial/report" class="ml2-hero-link">Ver relatório financeiro <i class="fas fa-arrow-right ms-1"></i></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="ml2-search">
        <i class="fas fa-search"></i>
        <input type="text" id="ml2SearchInput" placeholder="Buscar..." autocomplete="off">
    </div>

    <?php if (hasPermission('dashboard.view')): ?>
        <div class="ml2-quick-group">
            <a href="<?= htmlspecialchars(($mobileHomeHref ?? '/admin') . '?view=overview') ?>" class="ml2-quick-btn">
                <span class="ml2-quick-icon"><i class="fas fa-city"></i></span>
                <span class="ml2-quick-label">Financeiro</span>
            </a>
            <?php if (hasPermission('members.view')): ?>
                <a href="<?= htmlspecialchars(($mobileHomeHref ?? '/admin') . '?view=aniversariantes') ?>" class="ml2-quick-btn">
                    <span class="ml2-quick-icon"><i class="fas fa-cake-candles"></i></span>
                    <span class="ml2-quick-label">Aniversariantes</span>
                </a>
            <?php endif; ?>
            <?php if (hasPermission('events.view')): ?>
                <a href="<?= htmlspecialchars(($mobileHomeHref ?? '/admin') . '?view=eventos') ?>" class="ml2-quick-btn">
                    <span class="ml2-quick-icon"><i class="fas fa-calendar-alt"></i></span>
                    <span class="ml2-quick-label">Próx. Eventos</span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ===================== INÍCIO ===================== -->
    <div class="ml2-tabpanel active" data-ml2-panel="inicio">
        <?php
        // Secretaria has its own dedicated tab (below) when $ml2HasSecretaria is
        // true, so it's intentionally not repeated here to avoid Início and that
        // tab showing identical content.
        ml2Section('Financeiro', $ml2Financeiro);
        ml2Section('Ensino', $ml2Ensino);
        ?>
    </div>

    <!-- ===================== MEMBROS ===================== -->
    <?php if ($ml2HasSecretaria): ?>
    <div class="ml2-tabpanel" data-ml2-panel="membros">
        <?php ml2Section('Secretaria', $ml2Secretaria); ?>
    </div>
    <?php endif; ?>

    <!-- ===================== FINANÇAS ===================== -->
    <?php if ($ml2HasFinanceiro): ?>
    <div class="ml2-tabpanel" data-ml2-panel="financas">
        <div class="ml2-balance-hero">
            <div class="ml2-balance-label">Saldo do mês</div>
            <div class="ml2-balance-value"><?= htmlspecialchars($ml2Money($ml2TotalFinancial - $ml2ExpensesSum)) ?></div>
            <?php if ($ml2ClosurePending !== null): ?>
                <span class="ml2-pill <?= $ml2ClosurePending ? 'ml2-pill-warn' : 'ml2-pill-ok' ?>">
                    <?= $ml2ClosurePending ? 'Fechamento pendente' : 'Mês fechado' ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="ml2-stat-row">
            <div class="ml2-stat">
                <div class="ml2-stat-label"><i class="fas fa-arrow-trend-up me-1"></i>Entradas</div>
                <div class="ml2-stat-value up"><?= htmlspecialchars($ml2Money($ml2TotalFinancial)) ?></div>
            </div>
            <div class="ml2-stat">
                <div class="ml2-stat-label"><i class="fas fa-arrow-trend-down me-1"></i>Saídas</div>
                <div class="ml2-stat-value down"><?= htmlspecialchars($ml2Money($ml2ExpensesSum)) ?></div>
            </div>
        </div>
        <?php ml2Section('Módulos financeiros', $ml2Financeiro); ?>
    </div>
    <?php endif; ?>

    <!-- ===================== ENSINO ===================== -->
    <?php if ($ml2HasEnsino): ?>
    <div class="ml2-tabpanel" data-ml2-panel="ensino">
        <?php ml2Section('EBD &amp; Estudos', $ml2Ensino); ?>
    </div>
    <?php endif; ?>

    <!-- ===================== PERFIL ===================== -->
    <div class="ml2-tabpanel" data-ml2-panel="perfil">
        <div class="ml2-profile-head">
            <span class="ml2-profile-avatar"><?= htmlspecialchars($topbarInitials ?? '?') ?></span>
            <div class="ml2-profile-name"><?= htmlspecialchars((string)$loggedUserName) ?></div>
            <div class="ml2-profile-sub"><?= htmlspecialchars($topbarRoleLabel ?? '') ?></div>
        </div>

        <button type="button" class="ml2-list-row w-100 text-start border-0" data-bs-toggle="offcanvas" data-bs-target="#ml2AlertsSheet">
            <span>
                <span class="ml2-list-row-title">Notificações</span>
                <span class="ml2-list-row-sub d-block"><?= count($ml2Alerts) ?> alerta(s)</span>
            </span>
            <i class="fas fa-chevron-right chev"></i>
        </button>

        <?php if ($ml2CongregationName): ?>
            <div class="ml2-list-row">
                <span>
                    <span class="ml2-list-row-title">Congregação</span>
                    <span class="ml2-list-row-sub d-block"><?= htmlspecialchars($ml2CongregationName) ?></span>
                </span>
            </div>
        <?php endif; ?>

        <?php ml2Section('Sistema', $ml2Sistema); ?>

        <a href="/admin/logout" class="btn btn-outline-danger w-100 mt-3 rounded-3 fw-semibold">
            <i class="fas fa-sign-out-alt me-2"></i>Sair da conta
        </a>
    </div>

</div>

<!-- Alerts sheet -->
<div class="offcanvas offcanvas-bottom" tabindex="-1" id="ml2AlertsSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Notificações</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <?php if (empty($ml2Alerts)): ?>
            <p class="text-muted text-center mb-0 py-3">Nenhum alerta no momento.</p>
        <?php else: ?>
            <?php foreach ($ml2Alerts as $alert): ?>
                <a href="<?= htmlspecialchars($alert['href']) ?>" class="ml2-list-row">
                    <span>
                        <span class="ml2-list-row-title"><i class="fas <?= htmlspecialchars($alert['icon']) ?> me-2 text-primary"></i><?= htmlspecialchars($alert['text']) ?></span>
                    </span>
                    <i class="fas fa-chevron-right chev"></i>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Bottom tab bar -->
<nav class="ml2-bottomnav">
    <div class="ml2-bottomnav-inner">
        <?php foreach ($ml2Tabs as $tab): if (!$tab['always']) continue; ?>
            <button type="button" class="ml2-navbtn <?= $tab['id'] === 'inicio' ? 'active' : '' ?>" data-ml2-tab="<?= htmlspecialchars($tab['id']) ?>">
                <span class="ml2-navbtn-icon"><i class="fas <?= htmlspecialchars($tab['icon']) ?>"></i></span>
                <?= htmlspecialchars($tab['label']) ?>
            </button>
        <?php endforeach; ?>
    </div>
</nav>

<script>
(function () {
    var shell = document.getElementById('ml2Shell');
    if (!shell) return;

    // Tab switching
    var navButtons = document.querySelectorAll('.ml2-navbtn');
    navButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-ml2-tab');
            navButtons.forEach(function (b) { b.classList.toggle('active', b === btn); });
            shell.querySelectorAll('.ml2-tabpanel').forEach(function (panel) {
                panel.classList.toggle('active', panel.getAttribute('data-ml2-panel') === target);
            });
            shell.scrollIntoView({ behavior: 'instant', block: 'start' });
        });
    });

    // Search filter (diacritic-insensitive), scoped to whichever panel is active
    var searchInput = document.getElementById('ml2SearchInput');
    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var term = normalize(searchInput.value.trim());
            shell.querySelectorAll('.ml2-card').forEach(function (card) {
                var haystack = normalize(card.getAttribute('data-term'));
                card.style.display = (term === '' || haystack.indexOf(term) !== -1) ? '' : 'none';
            });
            shell.querySelectorAll('.ml2-section-label').forEach(function (label) {
                var grid = label.nextElementSibling;
                if (!grid || !grid.classList.contains('ml2-grid')) return;
                var anyVisible = Array.prototype.some.call(grid.querySelectorAll('.ml2-card'), function (c) {
                    return c.style.display !== 'none';
                });
                label.style.display = anyVisible ? '' : 'none';
                grid.style.display = anyVisible ? '' : 'none';
            });
        });
    }
})();
</script>

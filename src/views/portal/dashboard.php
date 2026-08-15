<?php include __DIR__ . '/layout/header.php'; ?>

<?php
function portalWorkerHonorific($role) {
    $map = [
        'pastor' => 'Pr.', 'pastora' => 'Pra.',
        'diacono' => 'Dc.', 'diácono' => 'Dc.', 'diaconisa' => 'Dca.',
        'presbitero' => 'Presb.', 'presbítero' => 'Presb.', 'presbitera' => 'Presb.', 'presbítera' => 'Presb.',
        'evangelista' => 'Evang.',
        'cooperador' => 'Coop.', 'cooperadora' => 'Coop.',
        'missionario' => 'Miss.', 'missionário' => 'Miss.', 'missionaria' => 'Miss.', 'missionária' => 'Miss.',
        'obreiro' => 'Ob.', 'obreira' => 'Ob.',
        'secretario' => 'Sec.', 'secretário' => 'Sec.', 'secretaria' => 'Sec.', 'secretária' => 'Sec.',
        'tesoureiro' => 'Tes.', 'tesoureira' => 'Tes.',
    ];
    return $map[mb_strtolower(trim((string)$role), 'UTF-8')] ?? '';
}
?>

<style>
    .portal-hero-banner {
        background: linear-gradient(135deg, var(--portal-primary) 0%, var(--portal-primary-dark) 100%);
        border-radius: 20px;
        padding: 1.5rem 1.6rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .portal-hero-banner::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .portal-hero-icon {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        background: rgba(255,255,255,0.16);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .portal-hero-badge {
        background: rgba(255,255,255,0.92);
        color: var(--portal-primary);
        font-weight: 800;
        font-size: .72rem;
        padding: .3rem .8rem;
        border-radius: 999px;
    }
    .portal-stat {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 16px;
        padding: 1rem 1.1rem;
        height: 100%;
    }
    .portal-stat-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 700;
        color: #868e96;
        margin-bottom: .25rem;
    }
    .portal-stat-value { font-size: 1.3rem; font-weight: 800; color: #1a1a1a; }
    .portal-quick-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .4rem;
        text-decoration: none;
        color: #1a1a1a;
    }
    .portal-quick-action-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }
    .portal-quick-action span.label { font-size: .74rem; font-weight: 700; }
    .portal-worker {
        display: flex;
        align-items: center;
        gap: .6rem;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 12px;
        padding: .5rem .65rem;
        height: 100%;
    }
    .portal-worker-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        flex: 0 0 auto;
    }
    .portal-worker-avatar-fallback {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(179,0,0,0.10);
        color: var(--portal-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .8rem;
        flex: 0 0 auto;
    }

    /* ---------- Workers modal ---------- */
    .pwm-modal-content { border-radius: 20px; border: none; overflow: hidden; }
    .pwm-modal-header { border-bottom: 1px solid rgba(0,0,0,.06); align-items: flex-start; }
    .pwm-eyebrow {
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--portal-primary);
        margin-bottom: .15rem;
    }
    .pwm-title { font-weight: 800; font-size: 1.15rem; color: #1a1a1a; }
    .pwm-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 16px;
        padding: 1.1rem .9rem;
        text-align: center;
        height: 100%;
    }
    .pwm-avatar, .pwm-avatar-photo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin: 0 auto .65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.15rem;
        object-fit: cover;
    }
    .pwm-yellow { background: #fdf0c4; color: #a17c00; }
    .pwm-orange { background: #fbe0c8; color: #b05f11; }
    .pwm-green { background: #cdeede; color: #1f8f68; }
    .pwm-lavender { background: #dcdcf7; color: #5b52c9; }
    .pwm-pink { background: #f9d9e6; color: #c23d78; }
    .pwm-blue { background: #d6e6fb; color: #2464b0; }
    .pwm-name { font-weight: 700; font-size: .88rem; color: #1a1a1a; }
    .pwm-role { font-size: .76rem; color: #868e96; margin-top: .1rem; }
    .pwm-caption {
        font-size: .68rem;
        color: #adb5bd;
        margin-top: .6rem;
        padding-top: .5rem;
        border-top: 1px dashed rgba(0,0,0,.08);
    }
    .pwm-footnote {
        background: rgba(179,0,0,.05);
        border: 1px solid rgba(179,0,0,.12);
        border-radius: 14px;
        padding: .75rem 1rem;
        text-align: center;
        font-size: .78rem;
        color: #6c757d;
    }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <div class="portal-page-title">Olá, <span style="color: var(--portal-primary);"><?= htmlspecialchars(explode(' ', trim($member['name']))[0]) ?></span></div>
        <p class="text-muted mb-0">Bem-vindo(a) ao portal. Congregação: <?= htmlspecialchars($member['congregation_name'] ?? 'Sede') ?>.</p>
    </div>
    <span class="portal-pill portal-pill-green"><i class="fas fa-circle" style="font-size:.5rem; vertical-align:middle;"></i> Sistema online</span>
</div>

<div class="portal-hero-banner">
    <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 1;">
        <div class="d-flex gap-3 align-items-start">
            <div class="portal-hero-icon"><i class="fas fa-grip"></i></div>
            <div>
                <h4 class="fw-bold mb-1">Painel Principal</h4>
                <p class="mb-3 opacity-75" style="max-width: 420px;">Acompanhe seus dízimos, a agenda da igreja e seus dados em um só lugar.</p>
                <a href="/portal/agenda" class="btn btn-light btn-sm rounded-pill fw-semibold px-3" style="color: var(--portal-primary);">Ver Agenda <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
        <div class="d-none d-sm-flex flex-column align-items-end gap-2">
            <span class="portal-hero-badge">Bem-vindo</span>
        </div>
    </div>
</div>

<?php
$internalEvents = array_filter($next_events ?? [], function($e) {
    return strtolower($e['type'] ?? '') === 'interno';
});
if (!empty($internalEvents)):
?>
<div class="portal-card mb-3" style="border-color: rgba(255,193,7,0.35); background: rgba(255,193,7,0.06);">
    <div class="portal-card-header" style="border-bottom-color: rgba(255,193,7,0.25);">
        <div class="portal-card-title"><i class="fas fa-triangle-exclamation text-warning me-2"></i> Atenção: Eventos Internos</div>
    </div>
    <div class="p-3">
        <?php $count = 0; foreach ($internalEvents as $e): $count++; if ($count > 3) break; ?>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 <?= $count > 1 ? 'border-top' : '' ?>" style="border-color: rgba(0,0,0,0.05) !important;">
                <div>
                    <span class="portal-pill portal-pill-warning me-2">Interno</span>
                    <strong><?= htmlspecialchars($e['title']) ?></strong>
                    <div class="small text-muted mt-1">
                        <?php $dateBadges = eventGetDateBadges($e); ?>
                        <i class="far fa-clock"></i> <?= htmlspecialchars($e['_next_occurrence'] ?? ($dateBadges[0]['date'] ?? '') . ' ' . ($dateBadges[0]['time'] ?? '')) ?>
                        <?php if (!empty($e['location'])): ?> · <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($e['location']) ?><?php endif; ?>
                    </div>
                </div>
                <a href="/portal/agenda" class="btn btn-sm btn-outline-warning rounded-pill">Ver na Agenda</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-1">
    <div class="col-4">
        <div class="portal-stat">
            <div class="portal-stat-label">Contribuições (<?= date('Y') ?>)</div>
            <div class="portal-stat-value">R$ <?= number_format($ytd_total, 2, ',', '.') ?></div>
        </div>
    </div>
    <div class="col-4">
        <div class="portal-stat">
            <div class="portal-stat-label">Próx. Eventos</div>
            <div class="portal-stat-value"><?= count($next_events) ?></div>
        </div>
    </div>
    <div class="col-4">
        <div class="portal-stat">
            <div class="portal-stat-label">Estudos Novos</div>
            <div class="portal-stat-value"><?= count($recent_studies) ?></div>
        </div>
    </div>
</div>

<div class="portal-section-label"><span>Atalhos Rápidos</span></div>
<div class="row g-3 text-center mb-2">
    <div class="col-3">
        <a href="/portal/card" class="portal-quick-action">
            <span class="portal-quick-action-icon plc-purple"><i class="fas fa-id-card"></i></span>
            <span class="label">Carteira</span>
        </a>
    </div>
    <div class="col-3">
        <a href="/portal/financial" class="portal-quick-action">
            <span class="portal-quick-action-icon plc-green"><i class="fas fa-hand-holding-dollar"></i></span>
            <span class="label">Dízimos</span>
        </a>
    </div>
    <div class="col-3">
        <a href="/portal/agenda" class="portal-quick-action">
            <span class="portal-quick-action-icon plc-orange"><i class="fas fa-calendar-days"></i></span>
            <span class="label">Agenda</span>
        </a>
    </div>
    <div class="col-3">
        <a href="/portal/documents" class="portal-quick-action">
            <span class="portal-quick-action-icon plc-gray"><i class="fas fa-folder-open"></i></span>
            <span class="label">Documentos</span>
        </a>
    </div>
</div>

<?php
$portalNavGroupsDashboard = $portalNavGroups;
if (isset($portalNavGroupsDashboard['Igreja'])) {
    $portalNavGroupsDashboard['Igreja'][] = [
        'label' => 'Obreiros da Congregação',
        'subtitle' => 'Liderança e equipe',
        'icon' => 'fa-users',
        'href' => '#portalWorkersModal',
        'color' => 'blue',
        'modal' => true,
    ];
}
?>
<?php foreach ($portalNavGroupsDashboard as $groupName => $items): ?>
    <div class="portal-section-label">
        <span><?= htmlspecialchars($groupName) ?></span>
        <span class="portal-pill portal-pill-gray"><?= count($items) ?> <?= count($items) === 1 ? 'item' : 'itens' ?></span>
    </div>
    <div class="row g-3 mb-2">
        <?php foreach ($items as $item): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <?php if (!empty($item['modal'])): ?>
                    <a href="#" data-bs-toggle="modal" data-bs-target="<?= htmlspecialchars($item['href']) ?>" class="portal-launcher-card plc-<?= $item['color'] ?>">
                <?php else: ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="portal-launcher-card plc-<?= $item['color'] ?>">
                <?php endif; ?>
                    <span class="portal-launcher-icon"><i class="fas <?= $item['icon'] ?>"></i></span>
                    <span>
                        <span class="portal-launcher-title d-block"><?= htmlspecialchars($item['label']) ?></span>
                        <span class="portal-launcher-subtitle"><?= htmlspecialchars($item['subtitle']) ?></span>
                    </span>
                    <i class="fas fa-chevron-right portal-launcher-chevron"></i>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<!-- Obreiros da Congregação Modal -->
<div class="modal fade" id="portalWorkersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content pwm-modal-content">
            <div class="modal-header pwm-modal-header">
                <div>
                    <div class="pwm-eyebrow">Nossa Família</div>
                    <h5 class="modal-title pwm-title mb-0">
                        <?= !empty($member['congregation_name']) ? 'Obreiros ' . htmlspecialchars($member['congregation_name']) : 'Obreiros da Congregação' ?>
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($workers)): ?>
                    <p class="text-muted text-center mb-0 py-3">Nenhum obreiro cadastrado.</p>
                <?php else: ?>
                    <?php $pwmPalette = ['pwm-yellow', 'pwm-orange', 'pwm-green', 'pwm-lavender', 'pwm-pink', 'pwm-blue']; ?>
                    <div class="row g-3">
                        <?php foreach ($workers as $wi => $w): ?>
                            <?php
                            $honorific = portalWorkerHonorific($w['role'] ?? '');
                            $paletteClass = $pwmPalette[$wi % count($pwmPalette)];
                            $nameParts = preg_split('/\s+/', trim((string)($w['name'] ?? '')));
                            $initials = mb_strtoupper(mb_substr($nameParts[0] ?? '', 0, 1), 'UTF-8');
                            if (count($nameParts) > 1) $initials .= mb_strtoupper(mb_substr(end($nameParts), 0, 1), 'UTF-8');
                            ?>
                            <div class="col-6 col-lg-4">
                                <div class="pwm-card">
                                    <?php if (!empty($w['photo'])): ?>
                                        <img src="/uploads/members/<?= htmlspecialchars($w['photo']) ?>" class="pwm-avatar-photo" alt="">
                                    <?php else: ?>
                                        <div class="pwm-avatar <?= $paletteClass ?>"><?= htmlspecialchars($initials) ?></div>
                                    <?php endif; ?>
                                    <div class="pwm-name"><?= htmlspecialchars(trim($honorific . ' ' . ($w['name'] ?? ''))) ?></div>
                                    <div class="pwm-role"><?= htmlspecialchars($w['role']) ?></div>
                                    <div class="pwm-caption">Servindo com amor</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="pwm-footnote mt-3">
                        Nossa equipe está sempre disponível para te receber com um abraço.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

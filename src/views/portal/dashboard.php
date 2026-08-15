<?php include __DIR__ . '/layout/header.php'; ?>

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
    .portal-hero-bubble {
        background: #fff;
        color: var(--portal-primary);
        font-weight: 800;
        font-size: .82rem;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
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
            <div class="d-flex gap-1">
                <span class="portal-hero-bubble" title="Próximos eventos"><?= count($next_events) ?></span>
                <span class="portal-hero-bubble" title="Estudos recentes"><?= count($recent_studies) ?></span>
            </div>
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

<?php foreach ($portalNavGroups as $groupName => $items): ?>
    <div class="portal-section-label">
        <span><?= htmlspecialchars($groupName) ?></span>
        <span class="portal-pill portal-pill-gray"><?= count($items) ?> <?= count($items) === 1 ? 'item' : 'itens' ?></span>
    </div>
    <div class="row g-3 mb-2">
        <?php foreach ($items as $item): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?= htmlspecialchars($item['href']) ?>" class="portal-launcher-card plc-<?= $item['color'] ?>">
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

<div class="portal-section-label"><span>Atividade Recente</span></div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="portal-card h-100">
            <div class="portal-card-header">
                <div class="portal-card-title"><i class="fas fa-hand-holding-dollar text-success me-2"></i> Últimas Contribuições</div>
            </div>
            <div class="p-3">
                <?php if (empty($last_tithes)): ?>
                    <p class="text-muted text-center mb-0 py-3">Nenhum registro recente.</p>
                <?php else: ?>
                    <?php foreach ($last_tithes as $t): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(0,0,0,0.05) !important;">
                            <div>
                                <span class="portal-pill <?= ($t['type'] ?? 'Dízimo') == 'Dízimo' ? 'portal-pill-red' : 'portal-pill-green' ?> me-2"><?= htmlspecialchars($t['type'] ?? 'Dízimo') ?></span>
                                <span class="text-muted small"><?= date('d/m/Y', strtotime($t['payment_date'])) ?></span>
                            </div>
                            <strong>R$ <?= number_format($t['amount'], 2, ',', '.') ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3 text-end">
                        <a href="/portal/financial" class="btn btn-sm btn-outline-danger rounded-pill">Ver Histórico Completo</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="portal-card h-100">
            <div class="portal-card-header">
                <div class="portal-card-title"><i class="fas fa-calendar-days text-primary me-2"></i> Próximos Eventos</div>
            </div>
            <div class="p-3">
                <?php if (empty($next_events)): ?>
                    <p class="text-muted text-center mb-0 py-3">Nenhum evento agendado.</p>
                <?php else: ?>
                    <?php foreach ($next_events as $e): ?>
                        <div class="py-2 border-bottom" style="border-color: rgba(0,0,0,0.05) !important;">
                            <div class="fw-bold">
                                <?= htmlspecialchars($e['title']) ?>
                                <?php if (strtolower($e['type'] ?? '') === 'interno'): ?>
                                    <span class="portal-pill portal-pill-warning ms-1">Interno</span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted">
                                <?php $dateBadges = eventGetDateBadges($e); ?>
                                <i class="far fa-clock"></i> <?= htmlspecialchars($e['_next_occurrence'] ?? ($dateBadges[0]['date'] ?? '') . ' ' . ($dateBadges[0]['time'] ?? '')) ?>
                                <?php if (!empty($e['location'])): ?> · <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($e['location']) ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3 text-end">
                        <a href="/portal/agenda" class="btn btn-sm btn-outline-primary rounded-pill">Ver Agenda Completa</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="portal-card h-100">
            <div class="portal-card-header">
                <div class="portal-card-title"><i class="fas fa-users text-primary me-2"></i> Obreiros da Congregação</div>
            </div>
            <div class="p-3">
                <?php if (empty($workers)): ?>
                    <p class="text-muted text-center mb-0 py-3">Nenhum obreiro cadastrado.</p>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($workers as $w): ?>
                            <div class="col-sm-6 col-lg-4">
                                <div class="portal-worker">
                                    <?php if (!empty($w['photo'])): ?>
                                        <img src="/uploads/members/<?= htmlspecialchars($w['photo']) ?>" class="portal-worker-avatar" alt="">
                                    <?php else: ?>
                                        <div class="portal-worker-avatar-fallback"><?= htmlspecialchars(mb_strtoupper(mb_substr($w['name'], 0, 1))) ?></div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1 text-truncate">
                                        <div class="fw-bold text-truncate small"><?= htmlspecialchars($w['name']) ?></div>
                                        <div class="text-muted text-truncate" style="font-size:.72rem;"><?= htmlspecialchars($w['role']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="portal-card h-100">
            <div class="portal-card-header">
                <div class="portal-card-title"><i class="fas fa-book-open text-danger me-2"></i> Estudos</div>
            </div>
            <div class="p-3">
                <?php if (empty($recent_studies)): ?>
                    <p class="text-muted text-center mb-0 py-3">Nenhum estudo publicado recentemente.</p>
                <?php else: ?>
                    <?php foreach ($recent_studies as $s): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(0,0,0,0.05) !important;">
                            <div class="text-truncate me-2">
                                <div class="fw-bold text-truncate small" title="<?= htmlspecialchars($s['title']) ?>"><?= htmlspecialchars($s['title']) ?></div>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($s['created_at'])) ?></small>
                            </div>
                            <a href="/portal/studies" class="btn btn-sm btn-outline-danger icon-btn" style="width:32px;height:32px;padding:0;border-radius:50%;">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3 text-end">
                        <a href="/portal/studies" class="btn btn-sm btn-outline-danger rounded-pill w-100">Ver Todos</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

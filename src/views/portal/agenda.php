<?php include __DIR__ . '/layout/header.php'; ?>

<style>
    .portal-event-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.06);
        border-left: 4px solid var(--portal-primary);
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(17,17,17,0.03);
        padding: 1.1rem 1.25rem;
        height: 100%;
    }
    .portal-congregation-heading {
        font-weight: 800;
        color: var(--portal-primary);
        font-size: 1rem;
        margin: 1.2rem 0 .75rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
    }
</style>

<div class="portal-page-title">Agenda da Igreja</div>
<p class="text-muted mb-3">Cultos, eventos e programações.</p>

<div class="row">
    <?php if (empty($events)): ?>
        <div class="col-12">
            <div class="portal-card text-center py-5 text-muted">
                <i class="fas fa-calendar-xmark fa-2x mb-3 opacity-50"></i>
                <p class="mb-0">Nenhum evento agendado no momento.</p>
            </div>
        </div>
    <?php else: ?>
        <?php
        $current_congregation = null;
        foreach ($events as $e):
            $congregation_name = $e['congregation_name'] ?? 'Eventos Gerais / Sede';
            if ($congregation_name !== $current_congregation):
                $current_congregation = $congregation_name;
        ?>
            <div class="col-12">
                <div class="portal-congregation-heading"><i class="fas fa-church me-2"></i> <?= htmlspecialchars($current_congregation) ?></div>
            </div>
        <?php endif; ?>

        <div class="col-md-6 mb-3">
            <div class="portal-event-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($e['title']) ?></h5>
                    <?php
                        $type = strtolower($e['type'] ?? '');
                        if ($type === 'culto'): ?>
                        <span class="portal-pill" style="background: rgba(13,110,253,.10); color:#0d6efd;">Culto</span>
                    <?php elseif ($type === 'interno'): ?>
                        <span class="portal-pill portal-pill-warning">Interno</span>
                    <?php else: ?>
                        <span class="portal-pill portal-pill-gray"><?= htmlspecialchars(ucfirst($e['type'])) ?></span>
                    <?php endif; ?>
                </div>

                <?php $dateBadges = eventGetDateBadges($e); ?>
                <div class="mb-2">
                    <i class="far fa-clock text-muted me-1"></i>
                    <?php if (empty($dateBadges)): ?>
                        <span class="text-muted small">Data a confirmar</span>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <?php foreach ($dateBadges as $b): ?>
                                <?php
                                $badgeText = ($b['weekday'] ?? '') . ' • ' . ($b['date'] ?? '');
                                if (!empty($b['time'])) {
                                    $badgeText .= ' ' . $b['time'];
                                }
                                ?>
                                <span class="portal-pill portal-pill-gray"><?= htmlspecialchars(trim($badgeText)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($e['location'])): ?>
                    <p class="mb-2 small"><i class="fas fa-map-marker-alt me-2 text-secondary"></i> <?= htmlspecialchars($e['location']) ?></p>
                <?php endif; ?>

                <?php if (!empty($e['description'])): ?>
                    <p class="mt-2 text-muted small"><?= nl2br(htmlspecialchars($e['description'])) ?></p>
                <?php endif; ?>

                <?php if (strtolower($e['type'] ?? '') === 'interno'): ?>
                    <div class="mt-2 p-2 rounded-3" style="background: rgba(255,193,7,0.12); font-size:.82rem;">
                        <i class="fas fa-lock me-2"></i> Evento interno — visível apenas para o seu grupo/credenciais.
                    </div>
                <?php endif; ?>

                <?php if (!empty($e['banner_path'])): ?>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill w-100" data-bs-toggle="modal" data-bs-target="#bannerModal" data-img-src="<?= htmlspecialchars($e['banner_path']) ?>" data-title="<?= htmlspecialchars($e['title']) ?>">
                            <i class="fas fa-image me-2"></i> Ver Banner
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal de Banner -->
<div class="modal fade" id="bannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header">
                <h5 class="modal-title" id="bannerModalLabel">Banner do Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" id="bannerImage" class="img-fluid" style="max-height: 80vh; width: 100%; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var bannerModal = document.getElementById('bannerModal');
        bannerModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var imgSrc = button.getAttribute('data-img-src');
            var title = button.getAttribute('data-title');

            var modalImg = bannerModal.querySelector('#bannerImage');
            var modalTitle = bannerModal.querySelector('.modal-title');

            modalImg.src = imgSrc;
            modalTitle.textContent = title || 'Banner do Evento';
        });
    });
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>

<?php include __DIR__ . '/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Agenda da Igreja</h1>
</div>

<div class="row">
    <?php if (empty($events)): ?>
        <div class="col-12">
            <div class="alert alert-info">Nenhum evento agendado no momento.</div>
        </div>
    <?php else: ?>
        <?php 
        $current_congregation = null;
        foreach ($events as $e): 
            $congregation_name = $e['congregation_name'] ?? 'Eventos Gerais / Sede';
            if ($congregation_name !== $current_congregation):
                $current_congregation = $congregation_name;
        ?>
            <div class="col-12 mt-4 mb-2">
                <h4 class="border-bottom pb-2 text-danger"><i class="fas fa-church me-2"></i> <?= htmlspecialchars($current_congregation) ?></h4>
            </div>
        <?php endif; ?>

        <div class="col-md-6 mb-4">
            <div class="card h-100 border-start border-4 border-danger shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($e['title']) ?></h5>
                        <?php 
                            $type = strtolower($e['type'] ?? '');
                            if ($type === 'culto'): ?>
                            <span class="badge bg-primary">Culto</span>
                        <?php elseif ($type === 'interno'): ?>
                            <span class="badge bg-warning text-dark">Interno</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= ucfirst($e['type']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <h6 class="text-muted mb-3">
                        <i class="far fa-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($e['event_date'])) ?>
                        <?php if (!empty($e['end_time'])): ?>
                            - <?= date('H:i', strtotime($e['end_time'])) ?>
                        <?php endif; ?>
                    </h6>

                    <?php if (!empty($e['location'])): ?>
                        <p class="mb-2"><i class="fas fa-map-marker-alt me-2 text-secondary"></i> <?= htmlspecialchars($e['location']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($e['description'])): ?>
                        <p class="card-text mt-3 text-secondary small"><?= nl2br(htmlspecialchars($e['description'])) ?></p>
                    <?php endif; ?>
                    
                    <?php if (strtolower($e['type'] ?? '') === 'interno'): ?>
                        <div class="mt-2 alert alert-warning py-2 px-3">
                            <i class="fas fa-lock me-2"></i> Evento interno — visível apenas para o seu grupo/credenciais.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($e['recurring_days'])): ?>
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted">Recorrente: <?= implode(', ', json_decode($e['recurring_days'], true)) ?></small>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($e['banner_path'])): ?>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#bannerModal" data-img-src="<?= htmlspecialchars($e['banner_path']) ?>" data-title="<?= htmlspecialchars($e['title']) ?>">
                                <i class="fas fa-image me-2"></i> Ver Banner
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal de Banner -->
<div class="modal fade" id="bannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
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
            if (title) {
                modalTitle.textContent = title;
            } else {
                modalTitle.textContent = 'Banner do Evento';
            }
        });
    });
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>

<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Controle de Presença</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/events" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#selectEventModal">
            <i class="fas fa-plus-circle"></i> Selecionar Evento
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (empty($events)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhuma lista de presença ativa.</h5>
                <p class="text-muted">Clique em "Selecionar Evento" para começar.</p>
                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#selectEventModal">
                    Selecionar Evento
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Evento</th>
                            <th>Local</th>
                            <th>Presentes</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $e): ?>
                            <tr>
                                <td>
                                    <?php
                                    $now = new DateTimeImmutable('now');
                                    $next = eventNextOccurrence($e, $now);
                                    $dateBadges = eventGetDateBadges($e);
                                    $primary = $next ? $next->format('d/m/Y H:i') : (!empty($dateBadges) ? ($dateBadges[0]['date'] . ' ' . $dateBadges[0]['time']) : '-');
                                    ?>
                                    <div class="fw-bold"><?= htmlspecialchars($primary) ?></div>
                                    <?php if (count($dateBadges) > 1): ?>
                                        <div class="small text-muted">+ <?= count($dateBadges) - 1 ?> datas</div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($e['title']) ?></td>
                                <td><?= htmlspecialchars($e['location']) ?></td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill">
                                        <i class="fas fa-users"></i> <?= $e['attendance_count'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($e['status'] ?? 'active') == 'active'): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="/admin/events/attendance/<?= $e['id'] ?>" class="btn btn-sm btn-primary" title="Abrir Lista / Check-in">
                                        <i class="fas fa-qrcode me-1"></i> Abrir Lista
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger ms-1" onclick="confirmDelete(<?= $e['id'] ?>)" title="Excluir Lista">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Selecionar Evento -->
<div class="modal fade" id="selectEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecionar Evento para Chamada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($availableEvents)): ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle mb-2 fa-2x"></i>
                        <p class="mb-0">Não há eventos disponíveis para ativar a lista de presença no momento.</p>
                        <small class="text-muted">Cadastre um evento com data futura e tente novamente.</small>
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($availableEvents as $ae): ?>
                            <a href="/admin/events/attendance/enable/<?= $ae['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($ae['title']) ?></div>
                                    <small class="text-muted">
                                        <?php
                                        $now = new DateTimeImmutable('now');
                                        $next = eventNextOccurrence($ae, $now);
                                        $dateBadges = eventGetDateBadges($ae);
                                        $primary = $next ? $next->format('d/m/Y H:i') : (!empty($dateBadges) ? ($dateBadges[0]['date'] . ' ' . $dateBadges[0]['time']) : '-');
                                        ?>
                                        <?= htmlspecialchars($primary) ?>
                                        <?php if (count($dateBadges) > 1): ?>
                                            <span class="ms-1 badge bg-light text-dark border">+<?= count($dateBadges) - 1 ?> datas</span>
                                        <?php endif; ?>
                                        - <?= htmlspecialchars($ae['location']) ?>
                                    </small>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Isso apagará todo o registro de presença deste evento! A ação não pode ser desfeita.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/admin/events/attendance/delete/' + id;
        }
    });
}
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

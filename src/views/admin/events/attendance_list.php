<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/events" class="text-decoration-none">Eventos</a></li>
                <li class="breadcrumb-item active">Controle de Presença</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Controle de Presença</h1>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/events" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
        <button type="button" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#selectEventModal">
            <i class="fas fa-plus-circle me-1"></i> Selecionar Evento
        </button>
    </div>
</div>

<style>
    .member-form-topbar {
        position: sticky;
        top: 0;
        z-index: 1030;
        background: #f8f9fa;
        padding-bottom: .85rem;
    }
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .attendance-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .attendance-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .3rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }
    .status-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        flex: 0 0 auto;
    }
    .status-pill.is-active { background: rgba(25,135,84,0.12); color: #198754; }
    .status-pill.is-inactive { background: rgba(0,0,0,0.06); color: #6c757d; }
    .count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .65rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    .modal-content { border-radius: 16px; border: none; }
    .modal-header { border-bottom: 1px solid rgba(0,0,0,0.07); }
    .available-event-item {
        border: 1px solid rgba(0,0,0,0.08) !important;
        border-radius: 12px !important;
        margin-bottom: .5rem;
    }
    .available-event-item:hover {
        border-color: rgba(179,0,0,0.3) !important;
        background: rgba(179,0,0,0.03);
    }
</style>

<div class="member-form-card">
    <div class="p-3">
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
                <button type="button" class="btn btn-primary rounded-pill fw-semibold px-4 mt-2" data-bs-toggle="modal" data-bs-target="#selectEventModal">
                    Selecionar Evento
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover attendance-table align-middle" style="width:100%">
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
                                    <span class="count-pill">
                                        <i class="fas fa-users"></i> <?= $e['attendance_count'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill <?= (($e['status'] ?? 'active') == 'active') ? 'is-active' : 'is-inactive' ?>">
                                        <?= (($e['status'] ?? 'active') == 'active') ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="/admin/events/attendance/<?= $e['id'] ?>" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3" title="Abrir Lista / Check-in">
                                        <i class="fas fa-qrcode me-1"></i> Abrir Lista
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger icon-btn ms-1" onclick="confirmDelete(<?= $e['id'] ?>)" title="Excluir Lista">
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
                <h5 class="modal-title fw-bold">Selecionar Evento para Chamada</h5>
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
                    <div class="list-group list-group-flush">
                        <?php foreach ($availableEvents as $ae): ?>
                            <a href="/admin/events/attendance/enable/<?= $ae['id'] ?>" class="available-event-item list-group-item list-group-item-action d-flex justify-content-between align-items-center">
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

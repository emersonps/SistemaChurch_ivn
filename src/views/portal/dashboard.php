<?php include __DIR__ . '/layout/header.php'; ?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card bg-danger text-white shadow">
            <div class="card-body">
                <h4 class="card-title">Bem-vindo(a), <?= htmlspecialchars($member['name']) ?>!</h4>
                <p class="card-text">Congregação: <?= htmlspecialchars($member['congregation_name'] ?? 'Sede') ?></p>
                <?php if (!empty($leaderName)): ?>
                    <p class="card-text mb-0"><span class="badge bg-warning text-dark me-2">Dirigente</span> <?= htmlspecialchars($leaderName) ?></p>
                <?php else: ?>
                    <p class="card-text mb-0"><span class="badge bg-secondary">Sem dirigente</span></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
// Destaque de Eventos Internos (acima de tudo)
$internalEvents = array_filter($next_events ?? [], function($e) {
    return strtolower($e['type'] ?? '') === 'interno';
});
if (!empty($internalEvents)): 
?>
<div class="row">
    <div class="col-12 mb-4">
        <div class="card border-0 shadow">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Atenção: Eventos Internos</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php $count = 0; foreach ($internalEvents as $e): $count++; if ($count > 3) break; ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-warning text-dark me-2">Interno</span>
                                    <strong><?= htmlspecialchars($e['title']) ?></strong>
                                    <small class="text-muted ms-2">
                                        <i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($e['event_date'])) ?>
                                        <?php if (!empty($e['location'])): ?> | <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($e['location']) ?><?php endif; ?>
                                    </small>
                                </div>
                                <a href="/portal/agenda" class="btn btn-sm btn-outline-warning">Ver na Agenda</a>
                            </div>
                            <?php if (!empty($e['description'])): ?>
                                <div class="mt-2 small text-secondary"><?= nl2br(htmlspecialchars($e['description'])) ?></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-hand-holding-usd text-success me-2"></i> Últimas Contribuições</h5>
            </div>
            <div class="card-body">
                <?php if (empty($last_tithes)): ?>
                    <p class="text-muted">Nenhum registro recente.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($last_tithes as $t): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-<?= ($t['type'] ?? 'Dízimo') == 'Dízimo' ? 'primary' : 'success' ?> me-2"><?= $t['type'] ?? 'Dízimo' ?></span>
                                    <?= date('d/m/Y', strtotime($t['payment_date'])) ?>
                                </div>
                                <strong>R$ <?= number_format($t['amount'], 2, ',', '.') ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-3 text-end">
                        <a href="/portal/financial" class="btn btn-sm btn-outline-primary">Ver Histórico Completo</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i> Próximos Eventos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($next_events)): ?>
                    <p class="text-muted">Nenhum evento agendado.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($next_events as $e): ?>
                            <li class="list-group-item">
                                <div class="fw-bold">
                                    <?= htmlspecialchars($e['title']) ?>
                                    <?php if (strtolower($e['type'] ?? '') === 'interno'): ?>
                                        <span class="badge bg-warning text-dark ms-2">Interno</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($e['event_date'])) ?>
                                    <?php if($e['location']): ?> | <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($e['location']) ?><?php endif; ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-3 text-end">
                        <a href="/portal/agenda" class="btn btn-sm btn-outline-primary">Ver Agenda Completa</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-users text-primary me-2"></i> Obreiros da Congregação</h5>
            </div>
            <div class="card-body">
                <?php if (empty($workers)): ?>
                    <p class="text-muted">Nenhum obreiro cadastrado.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($workers as $w): ?>
                            <div class="col-sm-6 col-md-4 col-lg-3">
                                <div class="d-flex align-items-center border rounded p-2">
                                    <?php if (!empty($w['photo'])): ?>
                                        <img src="/uploads/members/<?= htmlspecialchars($w['photo']) ?>" class="rounded-circle me-2" style="width:40px; height:40px; object-fit:cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white small me-2" style="width:40px; height:40px;">
                                            <?= strtoupper(substr($w['name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-truncate"><?= htmlspecialchars($w['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($w['role']) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <i class="fas fa-id-card fa-3x text-info mb-3"></i>
                <h5 class="card-title">Carteirinha Digital</h5>
                <p class="card-text text-muted">Acesse sua credencial de membro.</p>
                <a href="/portal/card" class="btn btn-info text-white">Visualizar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <i class="fas fa-user-edit fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Atualizar Dados</h5>
                <p class="card-text text-muted">Mantenha seu cadastro atualizado.</p>
                <a href="/portal/profile" class="btn btn-warning text-white">Editar Perfil</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white text-center">
                <h5 class="mb-0"><i class="fas fa-book-open text-danger me-2"></i> Estudos e Esboços</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_studies)): ?>
                    <p class="text-muted text-center mt-3">Nenhum estudo publicado recentemente.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($recent_studies as $s): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-truncate me-2">
                                        <div class="fw-bold text-truncate" title="<?= htmlspecialchars($s['title']) ?>"><?= htmlspecialchars($s['title']) ?></div>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($s['created_at'])) ?></small>
                                    </div>
                                    <a href="/uploads/studies/<?= $s['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="text-center mt-auto">
                        <a href="/portal/studies" class="btn btn-sm btn-outline-danger w-100">Ver Todos</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

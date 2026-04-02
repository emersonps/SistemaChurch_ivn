<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório do Grupo: <?= htmlspecialchars($group['name']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <a href="/admin/groups/show/<?= $group['id'] ?>" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Resumo -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body text-center">
                <h3 class="display-4"><?= $stats['total'] ?></h3>
                <p class="mb-0">Total de Membros</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center">
                <h3 class="display-4"><?= $stats['new_converts'] ?></h3>
                <p class="mb-0">Novos Convertidos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body text-center">
                <h3 class="display-4"><?= $stats['accepted_jesus'] ?></h3>
                <p class="mb-0">Aceitaram a Jesus</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body text-center">
                <h3 class="display-4"><?= $stats['reconciled'] ?></h3>
                <p class="mb-0">Reconciliados</p>
            </div>
        </div>
    </div>
</div>

<!-- Detalhes do Grupo -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <strong>Detalhes do Grupo</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Líder:</strong> <?= htmlspecialchars($group['leader_name'] ?? 'Não definido') ?></p>
                <p><strong>Anfitrião:</strong> <?= htmlspecialchars($group['host_name'] ?? 'Não definido') ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Dia/Horário:</strong> <?= htmlspecialchars((string)$group['meeting_day']) ?> às <?= !empty($group['meeting_time']) ? substr($group['meeting_time'], 0, 5) : '' ?></p>
                <p><strong>Endereço:</strong> <?= htmlspecialchars((string)$group['address']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Membros -->
<div class="card">
    <div class="card-header bg-light">
        <strong>Lista de Participantes</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Função</th>
                        <th>Status Espiritual</th>
                        <th>Telefone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                        <tr><td colspan="4" class="text-center py-3">Nenhum participante.</td></tr>
                    <?php else: ?>
                        <?php foreach ($members as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td>
                                <?php 
                                    $roleLabel = [
                                        'leader' => 'Líder',
                                        'host' => 'Anfitrião',
                                        'assistant' => 'Auxiliar',
                                        'member' => 'Membro',
                                        'visitor' => 'Convidado'
                                    ];
                                    echo $roleLabel[$m['role']] ?? ucfirst($m['role']);
                                ?>
                            </td>
                            <td>
                                <?php 
                                    $statuses = [];
                                    if ($m['is_new_convert']) $statuses[] = '<span class="badge bg-success">Novo Convertido</span>';
                                    if ($m['accepted_jesus_at']) $statuses[] = '<span class="badge bg-primary">Aceitou Jesus (' . date('d/m/Y', strtotime($m['accepted_jesus_at'])) . ')</span>';
                                    if ($m['reconciled_at']) $statuses[] = '<span class="badge bg-info text-dark">Reconciliado (' . date('d/m/Y', strtotime($m['reconciled_at'])) . ')</span>';
                                    
                                    echo empty($statuses) ? '-' : implode(' ', $statuses);
                                ?>
                            </td>
                            <td>
                                <?php if ($m['phone']): ?>
                                    <?= htmlspecialchars($m['phone']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/groups" class="text-decoration-none">Grupos e Células</a></li>
                <li class="breadcrumb-item"><a href="/admin/groups/show/<?= $group['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($group['name']) ?></a></li>
                <li class="breadcrumb-item active">Relatório</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Relatório: <?= htmlspecialchars($group['name']) ?></h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimir
        </button>
        <a href="/admin/groups/show/<?= $group['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .member-form-card-header {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
    }
    .member-form-card-body { padding: 1.25rem; }

    .kpi-tile {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        padding: 1.1rem 1.25rem;
        height: 100%;
        display: flex;
        align-items: center;
        gap: .9rem;
    }
    .kpi-tile .kpi-icon {
        flex: 0 0 auto;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }
    .kpi-tile .kpi-value {
        font-weight: 800;
        font-size: 1.6rem;
        color: #1a1a1a;
        line-height: 1.1;
    }
    .kpi-tile .kpi-label {
        font-size: .78rem;
        color: #868e96;
        font-weight: 600;
    }
    .kpi-tile.kpi-primary .kpi-icon { background: rgba(179,0,0,0.10); color: #b30000; }
    .kpi-tile.kpi-success .kpi-icon { background: rgba(25,135,84,0.12); color: #198754; }
    .kpi-tile.kpi-info .kpi-icon { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .kpi-tile.kpi-warning .kpi-icon { background: rgba(212,175,55,0.18); color: #a6790a; }

    .info-field .info-label {
        font-size: .76rem;
        color: #868e96;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .info-field .info-value {
        font-weight: 600;
        color: #212529;
    }

    .reports-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .reports-table td {
        vertical-align: middle;
        padding-top: .6rem;
        padding-bottom: .6rem;
    }
    .role-pill {
        display: inline-block;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }
    .status-mini-pill {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .15rem .45rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .status-mini-pill.sp-success { background: rgba(25,135,84,0.12); color: #198754; }
    .status-mini-pill.sp-primary { background: rgba(179,0,0,0.10); color: #b30000; }
    .status-mini-pill.sp-info { background: rgba(13,110,253,0.10); color: #0d6efd; }

    @media print {
        .btn-toolbar { display: none !important; }
        .member-form-card, .kpi-tile { border: 1px solid #ccc !important; box-shadow: none !important; }
    }
</style>

<!-- Resumo -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-primary">
            <div class="kpi-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="kpi-value"><?= $stats['total'] ?></div>
                <div class="kpi-label">Total de Membros</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-success">
            <div class="kpi-icon"><i class="fas fa-seedling"></i></div>
            <div>
                <div class="kpi-value"><?= $stats['new_converts'] ?></div>
                <div class="kpi-label">Novos Convertidos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-info">
            <div class="kpi-icon"><i class="fas fa-cross"></i></div>
            <div>
                <div class="kpi-value"><?= $stats['accepted_jesus'] ?></div>
                <div class="kpi-label">Aceitaram a Jesus</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-warning">
            <div class="kpi-icon"><i class="fas fa-undo"></i></div>
            <div>
                <div class="kpi-value"><?= $stats['reconciled'] ?></div>
                <div class="kpi-label">Reconciliados</div>
            </div>
        </div>
    </div>
</div>

<!-- Detalhes do Grupo -->
<div class="member-form-card mb-4">
    <div class="member-form-card-header">Detalhes do Grupo</div>
    <div class="member-form-card-body">
        <div class="row g-3">
            <div class="col-md-6 info-field">
                <div class="info-label">Líder</div>
                <div class="info-value"><?= htmlspecialchars($group['leader_name'] ?? 'Não definido') ?></div>
            </div>
            <div class="col-md-6 info-field">
                <div class="info-label">Anfitrião</div>
                <div class="info-value"><?= htmlspecialchars($group['host_name'] ?? 'Não definido') ?></div>
            </div>
            <div class="col-md-6 info-field">
                <div class="info-label">Dia/Horário</div>
                <div class="info-value"><?= htmlspecialchars((string)$group['meeting_day']) ?> às <?= !empty($group['meeting_time']) ? substr($group['meeting_time'], 0, 5) : '' ?></div>
            </div>
            <div class="col-md-6 info-field">
                <div class="info-label">Endereço</div>
                <div class="info-value"><?= htmlspecialchars((string)$group['address']) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Membros -->
<div class="member-form-card">
    <div class="member-form-card-header">Lista de Participantes</div>
    <div class="p-2">
        <div class="table-responsive">
            <table class="table table-hover reports-table mb-0">
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
                        <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum participante.</td></tr>
                    <?php else: ?>
                        <?php foreach ($members as $m): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($m['name']) ?></td>
                            <td>
                                <?php
                                    $roleLabel = [
                                        'leader' => 'Líder',
                                        'host' => 'Anfitrião',
                                        'assistant' => 'Auxiliar',
                                        'member' => 'Membro',
                                        'visitor' => 'Convidado'
                                    ];
                                ?>
                                <span class="role-pill"><?= $roleLabel[$m['role']] ?? ucfirst($m['role']) ?></span>
                            </td>
                            <td>
                                <?php if ($m['is_new_convert']): ?><span class="status-mini-pill sp-success"><i class="fas fa-seedling"></i> Novo Convertido</span><?php endif; ?>
                                <?php if ($m['accepted_jesus_at']): ?><span class="status-mini-pill sp-primary"><i class="fas fa-cross"></i> Aceitou Jesus (<?= date('d/m/Y', strtotime($m['accepted_jesus_at'])) ?>)</span><?php endif; ?>
                                <?php if ($m['reconciled_at']): ?><span class="status-mini-pill sp-info"><i class="fas fa-undo"></i> Reconciliado (<?= date('d/m/Y', strtotime($m['reconciled_at'])) ?>)</span><?php endif; ?>
                                <?php if (!$m['is_new_convert'] && !$m['accepted_jesus_at'] && !$m['reconciled_at']): ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
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

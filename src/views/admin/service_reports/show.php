<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/service_reports" class="text-decoration-none">Relatórios de Culto</a></li>
                <li class="breadcrumb-item active">Detalhes</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Detalhes do Relatório de Culto</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if (hasPermission('service_reports.manage')): ?>
        <a href="/admin/service_reports/edit/<?= $report['id'] ?>" class="btn btn-sm btn-dark rounded-pill fw-semibold px-3 me-2">
            <i class="fas fa-edit me-1"></i> Editar
        </a>
        <?php endif; ?>
        <a href="/admin/service_reports" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
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
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-badge {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #eef0f2;
        color: #212529;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .95rem;
    }
    .member-form-card-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
        line-height: 1.2;
    }
    .member-form-card-subtitle {
        font-size: .82rem;
        color: #868e96;
        margin-top: .1rem;
    }
    .member-form-card-body { padding: 1.25rem; }

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

    .stat-tile {
        background: #fafafa;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        padding: .75rem .5rem;
    }
    .stat-tile .stat-label {
        font-size: .72rem;
        color: #868e96;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .stat-tile .stat-value {
        font-weight: 800;
        font-size: 1.35rem;
        color: #212529;
    }
    .stat-tile.total {
        background: rgba(179,0,0,0.06);
        border-color: rgba(179,0,0,0.15);
    }
    .stat-tile.total .stat-value { color: #b30000; }

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
    .action-pill {
        display: inline-block;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }
</style>

<div class="row g-3">
    <!-- 1. Dados do Culto -->
    <div class="col-md-6">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-badge">1</div>
                <div>
                    <div class="member-form-card-title">Dados do Culto</div>
                    <div class="member-form-card-subtitle">Congregação, data e responsáveis.</div>
                </div>
            </div>
            <div class="member-form-card-body">
                <div class="row g-3">
                    <div class="col-12 info-field">
                        <div class="info-label">Congregação</div>
                        <div class="info-value"><?= htmlspecialchars($report['congregation_name']) ?></div>
                    </div>
                    <div class="col-6 info-field">
                        <div class="info-label">Data</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($report['date'])) ?></div>
                    </div>
                    <div class="col-6 info-field">
                        <div class="info-label">Horário</div>
                        <div class="info-value"><?= date('H:i', strtotime($report['time'])) ?></div>
                    </div>
                    <div class="col-6 info-field">
                        <div class="info-label">Dirigente</div>
                        <div class="info-value"><?= htmlspecialchars($report['leader_name']) ?></div>
                    </div>
                    <div class="col-6 info-field">
                        <div class="info-label">Pregador</div>
                        <div class="info-value"><?= htmlspecialchars($report['preacher_name']) ?></div>
                    </div>
                    <div class="col-12 info-field">
                        <div class="info-label">Criado por</div>
                        <div class="info-value"><?= htmlspecialchars($report['creator_name']) ?> em <?= date('d/m/Y H:i', strtotime($report['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Presença -->
    <div class="col-md-6">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-badge">2</div>
                <div>
                    <div class="member-form-card-title">Presença</div>
                    <div class="member-form-card-subtitle">Quantas pessoas participaram do culto.</div>
                </div>
            </div>
            <div class="member-form-card-body">
                <div class="row text-center g-2">
                    <div class="col-4 col-sm-2">
                        <div class="stat-tile">
                            <div class="stat-label">Homens</div>
                            <div class="stat-value"><?= $report['attendance_men'] ?></div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2">
                        <div class="stat-tile">
                            <div class="stat-label">Mulheres</div>
                            <div class="stat-value"><?= $report['attendance_women'] ?></div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2">
                        <div class="stat-tile">
                            <div class="stat-label">Jovens</div>
                            <div class="stat-value"><?= $report['attendance_youth'] ?></div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2">
                        <div class="stat-tile">
                            <div class="stat-label">Crianças</div>
                            <div class="stat-value"><?= $report['attendance_children'] ?></div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2">
                        <div class="stat-tile">
                            <div class="stat-label">Visitantes</div>
                            <div class="stat-value"><?= $report['attendance_visitors'] ?></div>
                        </div>
                    </div>
                    <div class="col-8 col-sm-2">
                        <div class="stat-tile total">
                            <div class="stat-label">Total</div>
                            <div class="stat-value"><?= $report['total_attendance'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Visitantes -->
    <div class="col-12">
        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-badge">3</div>
                <div>
                    <div class="member-form-card-title">Visitantes</div>
                    <div class="member-form-card-subtitle">Visitantes registrados nominalmente.</div>
                </div>
            </div>
            <div class="p-2">
                <div class="table-responsive">
                    <table class="table table-hover reports-table mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visitors as $v): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($v['name']) ?></td>
                                    <td><?= htmlspecialchars($v['observation']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($visitors)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">Nenhum visitante registrado nominalmente.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Decisões e Outros Registros -->
    <div class="col-12">
        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-badge">4</div>
                <div>
                    <div class="member-form-card-title">Decisões e Outros Registros</div>
                    <div class="member-form-card-subtitle">Aceitação, reconciliação, disciplina e desligamentos.</div>
                </div>
            </div>
            <div class="p-2">
                <div class="table-responsive">
                    <table class="table table-hover reports-table mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Ação/Situação</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($otherActions as $p): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($p['name']) ?></td>
                                    <td><span class="action-pill"><?= htmlspecialchars($p['action_type']) ?></span></td>
                                    <td><?= htmlspecialchars($p['observation']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($otherActions)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Nenhum outro registro de pessoas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Observações -->
    <?php if (!empty($report['notes'])): ?>
    <div class="col-12">
        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-sticky-note"></i></div>
                <div>
                    <div class="member-form-card-title">Observações Gerais</div>
                </div>
            </div>
            <div class="member-form-card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($report['notes'])) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

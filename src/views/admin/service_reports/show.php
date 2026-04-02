<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes do Relatório de Culto</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/service_reports" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- Informações Gerais -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Dados do Culto</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 30%">Congregação:</th>
                        <td><?= htmlspecialchars($report['congregation_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Data:</th>
                        <td><?= date('d/m/Y', strtotime($report['date'])) ?> (<?= date('H:i', strtotime($report['time'])) ?>)</td>
                    </tr>
                    <tr>
                        <th>Dirigente:</th>
                        <td><?= htmlspecialchars($report['leader_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Pregador:</th>
                        <td><?= htmlspecialchars($report['preacher_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Criado por:</th>
                        <td><?= htmlspecialchars($report['creator_name']) ?> em <?= date('d/m/Y H:i', strtotime($report['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Presença -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Presença</h5>
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-4 col-sm-2">
                        <div class="border rounded p-2">
                            <small class="d-block text-muted">Homens</small>
                            <strong><?= $report['attendance_men'] ?></strong>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2">
                        <div class="border rounded p-2">
                            <small class="d-block text-muted">Mulheres</small>
                            <strong><?= $report['attendance_women'] ?></strong>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2">
                        <div class="border rounded p-2">
                            <small class="d-block text-muted">Jovens</small>
                            <strong><?= $report['attendance_youth'] ?></strong>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2">
                        <div class="border rounded p-2">
                            <small class="d-block text-muted">Crianças</small>
                            <strong><?= $report['attendance_children'] ?></strong>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2">
                        <div class="border rounded p-2">
                            <small class="d-block text-muted">Visitantes</small>
                            <strong><?= $report['attendance_visitors'] ?></strong>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="alert alert-primary mb-0 py-2">
                            Total de Presentes: <strong><?= $report['total_attendance'] ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financeiro (Removido) -->
    <!--
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Entradas Financeiras</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Doador</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalFinancial = 0;
                            foreach ($financials as $fin): 
                                $totalFinancial += $fin['amount'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($fin['type']) ?></td>
                                    <td><?= htmlspecialchars($fin['member_name'] ?? $fin['giver_name']) ?></td>
                                    <td>R$ <?= number_format($fin['amount'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($financials)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Nenhuma entrada registrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end fw-bold">TOTAL:</td>
                                <td class="fw-bold text-success">R$ <?= number_format($totalFinancial, 2, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    -->

    <!-- Visitantes (Destacado) -->
    <div class="col-md-12 mb-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-user-friends me-2"></i>Visitantes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visitors as $v): ?>
                                <tr>
                                    <td><?= htmlspecialchars($v['name']) ?></td>
                                    <td><?= htmlspecialchars($v['observation']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($visitors)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Nenhum visitante registrado nominalmente.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Outros Registros de Pessoas -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Decisões e Outros Registros</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
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
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p['action_type']) ?></span></td>
                                    <td><?= htmlspecialchars($p['observation']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($otherActions)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Nenhum outro registro de pessoas.</td>
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
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Observações Gerais</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($report['notes'])) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

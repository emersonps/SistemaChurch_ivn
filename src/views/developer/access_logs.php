<?php
$pageTitle = 'Logs de Acesso e Atividades';
require_once __DIR__ . '/layout_developer.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-satellite-dish text-primary me-2"></i> Monitoramento de Acessos</h1>
        <a href="/developer/dashboard" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Voltar ao Painel</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6 col-xl-4 mb-3">
            <div class="card shadow border-left-success h-100">
                <div class="card-body">
                    <div class="text-muted small">Usuários Logados Online</div>
                    <div class="h3 mb-0"><?= count($onlineUsers ?? []) ?></div>
                    <div class="small text-muted">Conta usuários reais por login, sem duplicar páginas abertas.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 mb-3">
            <div class="card shadow border-left-secondary h-100">
                <div class="card-body">
                    <div class="text-muted small">Visitantes Ativos</div>
                    <div class="h3 mb-0"><?= count($activeVisitors ?? []) ?></div>
                    <div class="small text-muted">Sessões sem login vistas nos últimos 5 minutos.</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 mb-3">
            <div class="card shadow border-left-info h-100">
                <div class="card-body">
                    <div class="text-muted small">Leitura Mais Confiável</div>
                    <div class="small">A lista abaixo mostra apenas a última atividade de cada usuário autenticado, evitando múltiplas linhas do mesmo usuário local.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuários Online Agora -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-left-success">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-users"></i> Usuários Logados Online (Últimos 5 min)</h6>
                    <span class="badge bg-success rounded-pill"><?= count($onlineUsers ?? []) ?> Online</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuário</th>
                                    <th>Tipo</th>
                                    <th>IP</th>
                                    <th>Página Atual</th>
                                    <th>Última Atividade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($onlineUsers)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Nenhum usuário online no momento.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($onlineUsers as $ou): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-circle text-success small me-1"></i> 
                                                <?= htmlspecialchars($ou['user_name'] ?? 'Usuário') ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $typeClass = 'secondary';
                                                    if($ou['user_type'] == 'admin') $typeClass = 'danger';
                                                    if($ou['user_type'] == 'member') $typeClass = 'primary';
                                                ?>
                                                <?php 
                                                    $typeLabel = $ou['user_type'];
                                                    if ($typeLabel === 'admin') $typeLabel = 'Administrador';
                                                    elseif ($typeLabel === 'member') $typeLabel = 'Membro';
                                                    elseif ($typeLabel === 'visitor') $typeLabel = 'Visitante';
                                                ?>
                                                <span class="badge bg-<?= $typeClass ?>"><?= htmlspecialchars($typeLabel) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($ou['ip_address']) ?></td>
                                            <td><code><?= htmlspecialchars($ou['requested_url']) ?></code></td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($ou['last_activity'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-left-secondary">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-user-secret"></i> Visitantes Ativos (Últimos 5 min)</h6>
                    <span class="badge bg-secondary rounded-pill"><?= count($activeVisitors ?? []) ?> Sessões</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Identificação</th>
                                    <th>IP</th>
                                    <th>Página Atual</th>
                                    <th>Última Atividade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activeVisitors)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Nenhum visitante ativo no momento.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activeVisitors as $visitor): ?>
                                        <tr>
                                            <td>Visitante</td>
                                            <td><?= htmlspecialchars($visitor['ip_address'] ?? '-') ?></td>
                                            <td><code><?= htmlspecialchars($visitor['requested_url'] ?? '-') ?></code></td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($visitor['last_activity'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico Completo de Logs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history"></i> Histórico de Acessos (Últimos 1000)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="logsTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>Tipo</th>
                            <th>IP</th>
                            <th>URL Acessada</th>
                            <th>Método</th>
                            <th>Navegador/Dispositivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($logs)): foreach ($logs as $log): ?>
                            <tr>
                                <td data-sort="<?= $log['last_activity'] ?>"><?= date('d/m/Y H:i:s', strtotime($log['last_activity'])) ?></td>
                                <td><?= htmlspecialchars($log['user_name'] ?? 'Visitante') ?></td>
                                <td>
                                    <?php 
                                        $typeClass = 'secondary';
                                        if($log['user_type'] == 'admin') $typeClass = 'danger';
                                        if($log['user_type'] == 'member') $typeClass = 'primary';
                                    ?>
                                    <span class="badge bg-<?= $typeClass ?>"><?= htmlspecialchars($log['user_type']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                <td><span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= htmlspecialchars($log['requested_url']) ?>"><?= htmlspecialchars($log['requested_url']) ?></span></td>
                                <td>
                                    <?php
                                        $mClass = $log['request_method'] == 'POST' ? 'warning' : 'info';
                                    ?>
                                    <span class="badge bg-<?= $mClass ?>"><?= htmlspecialchars($log['request_method']) ?></span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block small" style="max-width: 200px;" title="<?= htmlspecialchars($log['user_agent']) ?>">
                                        <?= htmlspecialchars($log['user_agent']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS/JS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#logsTable').DataTable({
        "order": [[ 0, "desc" ]], // Ordenar por Data decrescente
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
        },
        "pageLength": 25
    });
});
</script>

<?php require_once __DIR__ . '/layout_footer.php'; ?>

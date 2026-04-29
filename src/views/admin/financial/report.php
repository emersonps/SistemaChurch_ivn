<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php $siteProfile = getChurchSiteProfileSettings(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório Financeiro</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4 d-print-none">
    <div class="card-body">
        <form class="row g-3" method="GET">
            <div class="col-6 col-md-3">
                <label class="form-label">Data Início</label>
                <input type="date" name="start_date" class="form-control" value="<?= $filters['start_date'] ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Data Fim</label>
                <input type="date" name="end_date" class="form-control" value="<?= $filters['end_date'] ?>">
            </div>
            <?php if (empty($_SESSION['user_congregation_id']) || $_SESSION['user_congregation_id'] == 0): ?>
            <div class="col-md-3">
                <label class="form-label">Congregação</label>
                <select name="congregation_id" class="form-select">
                    <option value="">Todas (Geral)</option>
                    <?php foreach ($congregations as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($filters['congregation_id'] == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <!-- Botões de Ação / Exportação -->
            <div class="col-md-3 d-flex align-items-end d-print-none">
                <div class="dropdown w-100">
                    <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-filter"></i> Filtrar</button>
                    <button class="btn btn-outline-success w-100 dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export"></i> Exportar Contabilidade
                    </button>
                    <ul class="dropdown-menu w-100" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="/admin/financial/export/csv?start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&congregation_id=<?= $filters['congregation_id'] ?>"><i class="fas fa-file-csv text-success"></i> CSV (Para Sistemas)</a></li>
                        <li><a class="dropdown-item" href="/admin/financial/export/excel?start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&congregation_id=<?= $filters['congregation_id'] ?>"><i class="fas fa-file-excel text-success"></i> Excel (.xls)</a></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Report Content -->
<div class="card">
    <div class="card-body">
        <div class="text-center mb-4">
            <h4>Relatório Financeiro - <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></h4>
            <p class="text-muted">
                Período: <?= date('d/m/Y', strtotime($filters['start_date'])) ?> a <?= date('d/m/Y', strtotime($filters['end_date'])) ?>
                <br>
                <?= $filters['congregation_id'] ? 'Congregação Específica' : 'Visão Geral (Todas as Congregações)' ?>
            </p>
        </div>

        <!-- Resumo -->
        <div class="row mb-4 text-center">
            <div class="col-md-4">
                <div class="p-3 bg-light border rounded">
                    <h5 class="text-success">Entradas</h5>
                    <h3>R$ <?= number_format($total_entries, 2, ',', '.') ?></h3>
                    <small>Dízimos: R$ <?= number_format($total_tithes, 2, ',', '.') ?> | Ofertas: R$ <?= number_format($total_offerings, 2, ',', '.') ?></small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light border rounded">
                    <h5 class="text-danger">Saídas</h5>
                    <h3>R$ <?= number_format($total_expenses, 2, ',', '.') ?></h3>
                    <small><?= count($expenses) ?> lançamentos</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light border rounded">
                    <h5 class="text-primary">Saldo</h5>
                    <h3>R$ <?= number_format($balance, 2, ',', '.') ?></h3>
                    <small><?= $balance >= 0 ? 'Positivo' : 'Negativo' ?></small>
                </div>
            </div>
        </div>

        <hr>

        <!-- Detalhamento Saídas -->
        <h5 class="mt-4 mb-3">Detalhamento de Saídas</h5>
        <?php if (count($expenses) > 0): ?>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th>Congregação</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $e): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($e['expense_date'])) ?></td>
                            <td><?= htmlspecialchars($e['description']) ?></td>
                            <td><?= htmlspecialchars($e['category']) ?></td>
                            <td><?= htmlspecialchars($e['congregation_name'] ?? 'Geral') ?></td>
                            <td class="text-end text-danger">- R$ <?= number_format($e['amount'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Resumo por Categoria -->
            <div class="row">
                <div class="col-md-6">
                    <h6>Resumo por Categoria de Despesa</h6>
                    <ul class="list-group">
                        <?php foreach ($expenses_by_category as $cat => $amount): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($cat) ?>
                            <span class="badge bg-secondary rounded-pill">R$ <?= number_format($amount, 2, ',', '.') ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

        <?php else: ?>
            <p class="text-center text-muted">Nenhuma saída registrada neste período.</p>
        <?php endif; ?>

        <hr>

        <!-- Detalhamento Entradas -->
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <h5 class="mb-0">Detalhamento de Entradas</h5>
            <button class="btn btn-sm btn-outline-secondary d-print-none" onclick="toggleTithes()" title="Exibir/Ocultar valores de Dízimos">
                <i class="fas fa-eye" id="toggleTithesIcon"></i> Valores de Dízimos
            </button>
        </div>
        
        <?php if (count($entries) > 0): ?>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Nome (Membro/Doador)</th>
                            <th>Congregação</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $en): 
                            // Determine name display logic
                            $displayName = $en['member_name'] ?? $en['giver_name'];
                            if (empty($displayName)) {
                                if ($en['payment_method'] === 'Transferência/OFX' && !empty($en['notes'])) {
                                    $displayName = 'OFX: ' . $en['notes'];
                                } elseif (!empty($en['notes'])) {
                                    $displayName = 'Obs: ' . mb_strimwidth($en['notes'], 0, 30, '...');
                                } else {
                                    $displayName = 'Não Identificado (' . $en['payment_method'] . ')';
                                }
                            }
                        ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($en['payment_date'])) ?></td>
                            <td><?= htmlspecialchars((string)$en['type']) ?></td>
                            <td><?= htmlspecialchars((string)$displayName) ?></td>
                            <td><?= htmlspecialchars((string)($en['congregation_name'] ?? 'Geral')) ?></td>
                            <td class="text-end text-success">
                                <?php 
                                    // Robust check for Dízimo (case insensitive, UTF-8 safe)
                                    $isTithe = preg_match('/dízimo/iu', $en['type']) || preg_match('/dizimo/iu', $en['type']);
                                    if ($isTithe): 
                                ?>
                                    <span class="tithe-value d-none">+ R$ <?= number_format($en['amount'], 2, ',', '.') ?></span>
                                    <span class="tithe-mask">****</span>
                                <?php else: ?>
                                    + R$ <?= number_format($en['amount'], 2, ',', '.') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">TOTAL ENTRADAS:</td>
                            <td class="text-end text-success">R$ <?= number_format($total_entries, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Nenhuma entrada registrada neste período.</p>
        <?php endif; ?>

        <script>
        function toggleTithes() {
            var values = document.querySelectorAll('.tithe-value');
            var masks = document.querySelectorAll('.tithe-mask');
            var icon = document.getElementById('toggleTithesIcon');
            
            // Toggle visibility
            values.forEach(function(el) { el.classList.toggle('d-none'); });
            masks.forEach(function(el) { el.classList.toggle('d-none'); });
            
            // Toggle Icon
            if (icon.classList.contains('fa-eye')) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        </script>

    </div>
</div>

<style>
@media print {
    @page { size: A4; margin: 10mm; }
    body { background: white; -webkit-print-color-adjust: exact; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header, .btn, .d-print-none, nav, footer { display: none !important; }
    .table { width: 100% !important; border-collapse: collapse !important; }
    .table td, .table th { border: 1px solid #ddd !important; padding: 4px !important; }
    .badge { border: 1px solid #000; color: #000; }
    h4, h5, h6 { color: #000 !important; }
    .text-success { color: #000 !important; font-weight: bold; } /* Force black for contrast or keep color if printer supports it */
    .text-danger { color: #000 !important; font-weight: bold; }
    .bg-light { background-color: #f8f9fa !important; }
}
</style>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

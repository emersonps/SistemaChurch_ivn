<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php $siteProfile = getChurchSiteProfileSettings(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório Financeiro</h1>
    <div class="btn-toolbar mb-2 mb-md-0 d-print-none">
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimir
        </button>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
    .stat-box {
        border-radius: 14px;
        padding: 1.1rem;
        text-align: center;
        border: 1px solid rgba(0,0,0,0.06);
    }
    .stat-box.stat-entries { background: rgba(25,135,84,0.06); }
    .stat-box.stat-expenses { background: rgba(220,53,69,0.06); }
    .stat-box.stat-balance { background: rgba(13,110,253,0.06); }
    .stat-box .stat-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 700;
        margin-bottom: .3rem;
    }
    .stat-box .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
    }
    .stat-box .stat-sub {
        font-size: .78rem;
        color: #6c757d;
    }
    .report-table thead th {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
        background: #fafafa;
    }
    .report-table td { vertical-align: middle; }
    .section-title {
        font-weight: 800;
        font-size: 1rem;
        color: #1a1a1a;
        margin-bottom: .85rem;
    }
    @media print {
        @page { size: A4; margin: 10mm; }
        body { background: white; -webkit-print-color-adjust: exact; }
        .member-form-card { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
        .btn, .d-print-none, nav, footer { display: none !important; }
        .table { width: 100% !important; border-collapse: collapse !important; }
        .table td, .table th { border: 1px solid #ddd !important; padding: 4px !important; }
        .badge { border: 1px solid #000; color: #000; }
        h4, h5, h6 { color: #000 !important; }
        .text-success { color: #000 !important; font-weight: bold; }
        .text-danger { color: #000 !important; font-weight: bold; }
        .stat-box { background: #fff !important; border: 1px solid #000 !important; }
    }
</style>

<!-- Filters -->
<div class="member-form-card filter-card mb-3 d-print-none">
    <div class="p-3">
        <form class="row g-3" method="GET">
            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold">Data Início</label>
                <input type="date" name="start_date" class="form-control" value="<?= $filters['start_date'] ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold">Data Fim</label>
                <input type="date" name="end_date" class="form-control" value="<?= $filters['end_date'] ?>">
            </div>
            <?php if (empty($_SESSION['user_congregation_id']) || $_SESSION['user_congregation_id'] == 0): ?>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Congregação</label>
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
            <div class="col-md-3 d-flex align-items-end">
                <div class="dropdown w-100">
                    <button type="submit" class="btn btn-dark rounded-pill fw-semibold w-100 mb-2"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <button class="btn btn-outline-success rounded-pill fw-semibold w-100 dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i> Exportar Contabilidade
                    </button>
                    <ul class="dropdown-menu w-100" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="/admin/financial/export/csv?start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&congregation_id=<?= $filters['congregation_id'] ?>"><i class="fas fa-file-csv text-success me-1"></i> CSV (Para Sistemas)</a></li>
                        <li><a class="dropdown-item" href="/admin/financial/export/excel?start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&congregation_id=<?= $filters['congregation_id'] ?>"><i class="fas fa-file-excel text-success me-1"></i> Excel (.xls)</a></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Report Content -->
<div class="member-form-card">
    <div class="p-4">
        <div class="text-center mb-4">
            <h4>Relatório Financeiro - <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></h4>
            <p class="text-muted mb-0">
                Período: <?= date('d/m/Y', strtotime($filters['start_date'])) ?> a <?= date('d/m/Y', strtotime($filters['end_date'])) ?>
                <br>
                <?= $filters['congregation_id'] ? 'Congregação Específica' : 'Visão Geral (Todas as Congregações)' ?>
            </p>
        </div>

        <!-- Resumo -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="stat-box stat-entries">
                    <div class="stat-label text-success">Entradas</div>
                    <div class="stat-value">R$ <?= number_format($total_entries, 2, ',', '.') ?></div>
                    <div class="stat-sub">Dízimos: R$ <?= number_format($total_tithes, 2, ',', '.') ?> | Ofertas: R$ <?= number_format($total_offerings, 2, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box stat-expenses">
                    <div class="stat-label text-danger">Saídas</div>
                    <div class="stat-value">R$ <?= number_format($total_expenses, 2, ',', '.') ?></div>
                    <div class="stat-sub"><?= count($expenses) ?> lançamentos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box stat-balance">
                    <div class="stat-label text-primary">Saldo</div>
                    <div class="stat-value">R$ <?= number_format($balance, 2, ',', '.') ?></div>
                    <div class="stat-sub"><?= $balance >= 0 ? 'Positivo' : 'Negativo' ?></div>
                </div>
            </div>
        </div>

        <hr>

        <!-- Detalhamento Saídas -->
        <div class="section-title mt-4">Detalhamento de Saídas</div>
        <?php if (count($expenses) > 0): ?>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-hover report-table">
                    <thead>
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
                            <td class="text-end text-danger fw-bold">- R$ <?= number_format($e['amount'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Resumo por Categoria -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="section-title" style="font-size: .92rem;">Resumo por Categoria de Despesa</div>
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
            <div class="section-title mb-0">Detalhamento de Entradas</div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold d-print-none" onclick="toggleTithes()" title="Exibir/Ocultar valores de Dízimos">
                <i class="fas fa-eye" id="toggleTithesIcon"></i> Valores de Dízimos
            </button>
        </div>

        <?php if (count($entries) > 0): ?>
            <div class="table-responsive mb-2">
                <table class="table table-sm table-hover report-table">
                    <thead>
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
                            <td class="text-end text-success fw-bold">
                                <?php
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

            values.forEach(function(el) { el.classList.toggle('d-none'); });
            masks.forEach(function(el) { el.classList.toggle('d-none'); });

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

<?php include __DIR__ . '/../../layout/footer.php'; ?>

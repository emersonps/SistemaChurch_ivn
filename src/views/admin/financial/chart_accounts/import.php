<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Importar Plano de Contas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/chart-accounts" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (($step ?? 'upload') === 'upload'): ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="/admin/financial/chart-accounts/import/preview" method="POST" enctype="multipart/form-data" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-md-4">
                    <label class="form-label">Plano de Contas</label>
                    <select name="account_set_id" class="form-select">
                        <?php foreach (($sets ?? []) as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($selectedSet ?? 0) == $s['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?> <?= (int)$s['is_default'] === 1 ? '(Padrão)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Arquivo CSV</label>
                    <input type="file" name="csv" class="form-control" accept=".csv" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Separador</label>
                    <select name="delimiter" class="form-select">
                        <option value=",">Vírgula (,)</option>
                        <option value=";">Ponto e vírgula (;)</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Enviar</button>
                    <a href="/admin/financial/chart-accounts/template" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-download"></i> Baixar Modelo CSV
                    </a>
                    <a href="#" class="btn btn-outline-secondary ms-2" onclick="alert('Para XLSX: abra o modelo CSV no Excel e salve como .xlsx. Podemos habilitar exportação XLSX nativa adicionando uma biblioteca específica.'); return false;">
                        <i class="fas fa-file-excel"></i> Dica para XLSX
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="/admin/financial/chart-accounts/import/commit" method="POST" class="row g-3">
                <?= csrf_field() ?>
                <input type="hidden" name="account_set_id" value="<?= (int)$account_set_id ?>">
                <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
                <input type="hidden" name="delimiter" value="<?= htmlspecialchars($delimiter) ?>">
                <div class="col-12">
                    <h5 class="mb-3">Mapeamento de Colunas</h5>
                    <div class="row g-3">
                        <?php 
                            $targets = [
                                'map_code' => 'Código (obrigatório)',
                                'map_name' => 'Nome (obrigatório)',
                                'map_type' => 'Natureza (Ativo/Passivo/Receita/Despesa)',
                                'map_parent_code' => 'Código Pai (para contas Filhas)',
                                'map_structure' => 'Estrutura (Sintética/Analítica)',
                                'map_opening_balance' => 'Saldo de Implantação',
                                'map_opening_date' => 'Data do Saldo',
                                'map_status' => 'Status (active/inactive)'
                            ];
                        ?>
                        <?php foreach ($targets as $key => $label): ?>
                            <div class="col-md-6">
                                <label class="form-label"><?= $label ?></label>
                                <select name="<?= $key ?>" class="form-select">
                                    <option value="">-- Não usar --</option>
                                    <?php foreach ($headers as $h): ?>
                                        <option value="<?= htmlspecialchars($h) ?>" <?= (($suggest[$key === 'map_code' ? 'code' : ($key === 'map_name' ? 'name' : str_replace('map_','',$key))] ?? '') === $h) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($h) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12">
                    <h5 class="mt-3">Amostra</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <?php foreach ($headers as $h): ?>
                                        <th><?= htmlspecialchars($h) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($rows ?? []) as $r): ?>
                                    <tr>
                                        <?php foreach ($r as $c): ?>
                                            <td><?= htmlspecialchars($c) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-success"><i class="fas fa-play"></i> Importar</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

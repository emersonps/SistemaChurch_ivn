<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Importar Plano de Contas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/chart-accounts" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
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
    .member-form-card-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .member-form-card-body .form-control,
    .member-form-card-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus,
    .member-form-card-body .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (($step ?? 'upload') === 'upload'): ?>
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-file-import"></i></div>
            <div>
                <div class="member-form-card-title">Enviar Planilha CSV</div>
                <div class="member-form-card-subtitle">Selecione o plano de contas de destino e o arquivo a importar.</div>
            </div>
        </div>
        <div class="member-form-card-body">
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
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-dark rounded-pill fw-semibold px-4"><i class="fas fa-upload me-2"></i> Enviar</button>
                    <a href="/admin/financial/chart-accounts/template" class="btn btn-outline-primary rounded-pill fw-semibold px-4">
                        <i class="fas fa-download me-2"></i> Baixar Modelo CSV
                    </a>
                    <button type="button" id="btnXlsxTip" class="btn btn-outline-secondary rounded-pill fw-semibold px-4">
                        <i class="fas fa-file-excel me-2"></i> Dica para XLSX
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-table-columns"></i></div>
            <div>
                <div class="member-form-card-title">Mapeamento de Colunas</div>
                <div class="member-form-card-subtitle">Relacione as colunas do arquivo com os campos do sistema.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <form action="/admin/financial/chart-accounts/import/commit" method="POST" class="row g-3">
                <?= csrf_field() ?>
                <input type="hidden" name="account_set_id" value="<?= (int)$account_set_id ?>">
                <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
                <input type="hidden" name="delimiter" value="<?= htmlspecialchars($delimiter) ?>">
                <div class="col-12">
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
                    <h6 class="fw-bold text-uppercase text-muted small mt-2 mb-2">Amostra</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
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
                    <button type="submit" class="btn btn-success rounded-pill fw-semibold px-4"><i class="fas fa-play me-2"></i> Importar</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

<script>
    const xlsxBtn = document.getElementById('btnXlsxTip');
    if (xlsxBtn) {
        xlsxBtn.addEventListener('click', function () {
            Swal.fire({
                title: 'Importar de XLSX',
                text: 'Para XLSX: abra o modelo CSV no Excel e salve como .xlsx. Podemos habilitar exportação XLSX nativa adicionando uma biblioteca específica.',
                icon: 'info',
                confirmButtonColor: '#b30000',
                confirmButtonText: 'Entendi'
            });
        });
    }
</script>

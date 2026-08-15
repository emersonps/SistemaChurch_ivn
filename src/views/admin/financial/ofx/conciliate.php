<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Conciliar Transações</h1>
        <p class="text-muted mb-0">Arquivo: <?= htmlspecialchars($import['filename']) ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/ofx" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">Voltar</a>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .ofx-hint-card {
        background: linear-gradient(135deg, rgba(255,193,7,0.10), rgba(179,0,0,0.06));
        border: 1px solid rgba(0,0,0,0.07);
        border-radius: 16px;
        padding: 1.1rem 1.25rem;
    }
    .match-count-pill {
        display: inline-block;
        padding: .35rem .9rem;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 700;
        background: rgba(25,135,84,0.10);
        color: #198754;
    }
    .ofx-table thead th {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #fff;
        font-weight: 700;
        background: #212529;
        border: none;
    }
    .ofx-table td { vertical-align: middle; }
    .ofx-row-matched { background: rgba(255,193,7,0.08); }
    .ofx-row-done { background: #fafafa; opacity: .6; }
    .suggestion-box {
        border: 1px solid rgba(25,135,84,0.35);
        border-radius: 10px;
        background: #fff;
        padding: .6rem .75rem;
    }
    .suggestion-badge {
        display: inline-block;
        padding: .18rem .55rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        background: rgba(25,135,84,0.12);
        color: #198754;
        margin-bottom: .3rem;
    }
    .processed-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
        background: #eef0f2;
        color: #6c757d;
    }
    .ofx-table .form-select {
        border-radius: 8px;
    }
</style>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="ofx-hint-card mb-3">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="mb-1"><i class="fas fa-magic text-warning me-1"></i> Conciliação Inteligente</h5>
            <p class="text-muted mb-0 small">O sistema procura automaticamente lançamentos no seu banco de dados (Dízimos ou Despesas) com o <strong>mesmo valor</strong> e <strong>data próxima</strong> para sugerir vínculos.</p>
        </div>
        <div class="col-md-4 text-md-end mt-2 mt-md-0">
            <span class="match-count-pill"><?= count($matches ?? []) ?> sugestões encontradas</span>
        </div>
    </div>
</div>

<form action="/admin/financial/ofx/save/<?= $import['id'] ?>" method="POST">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 ofx-table" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 35%">Dados do Banco (OFX)</th>
                        <th style="width: 35%">Sugestão / Sistema</th>
                        <th style="width: 15%">Plano de Contas</th>
                        <th style="width: 15%">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx):
                        $isMatch = isset($matches[$tx['id']]);
                        $match = $isMatch ? $matches[$tx['id']] : null;
                    ?>
                        <tr class="<?= $tx['status'] !== 'pending' ? 'ofx-row-done' : ($isMatch ? 'ofx-row-matched' : '') ?>">
                            <td class="border-end">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-bold"><?= date('d/m/Y', strtotime($tx['transaction_date'])) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($tx['description']) ?></div>
                                    </div>
                                    <div class="text-end fw-bold fs-5 <?= $tx['type'] === 'credit' ? 'text-success' : 'text-danger' ?>">
                                        <?= $tx['type'] === 'credit' ? '+' : '-' ?> R$ <?= number_format(abs($tx['amount']), 2, ',', '.') ?>
                                    </div>
                                </div>
                            </td>

                            <td class="border-end">
                                <?php if ($tx['status'] !== 'pending'): ?>
                                    <div class="text-success"><i class="fas fa-check-circle"></i> Já conciliado</div>
                                <?php elseif ($isMatch): ?>
                                    <div class="suggestion-box">
                                        <span class="suggestion-badge">Sugestão Encontrada</span>
                                        <div class="small fw-bold"><?= date('d/m/Y', strtotime($match['date'])) ?> - <?= htmlspecialchars($match['description']) ?></div>
                                        <input type="hidden" name="system_id[<?= $tx['id'] ?>]" value="<?= $match['system_id'] ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small fst-italic">
                                        Nenhum lançamento compatível encontrado no sistema. Será criado como novo.
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($tx['status'] === 'pending'): ?>
                                    <select name="chart_id[<?= $tx['id'] ?>]" class="form-select form-select-sm">
                                        <option value="">-- Categoria --</option>
                                        <?php foreach ($charts as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= $c['code'] ?> - <?= htmlspecialchars($c['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($tx['status'] === 'pending'): ?>
                                    <select name="action[<?= $tx['id'] ?>]" class="form-select form-select-sm <?= $isMatch ? 'border-success fw-bold' : '' ?>">
                                        <?php if ($isMatch): ?>
                                            <option value="link" selected>Vincular Sugestão</option>
                                            <option value="add">Criar Novo Lançamento</option>
                                        <?php else: ?>
                                            <option value="add" selected>Criar Novo Lançamento</option>
                                        <?php endif; ?>
                                        <option value="ignore">Ignorar (Não importar)</option>
                                        <option value="">Deixar Pendente</option>
                                    </select>
                                <?php else: ?>
                                    <span class="processed-pill">Processado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($import['status'] === 'pending'): ?>
        <div class="p-3 text-end border-top">
            <button type="submit" class="btn btn-dark btn-lg rounded-pill fw-semibold px-4"><i class="fas fa-check-double me-2"></i> Processar Conciliação</button>
        </div>
        <?php endif; ?>
    </div>
</form>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

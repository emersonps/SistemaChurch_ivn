<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Conciliar Transações</h1>
        <p class="text-muted mb-0">Arquivo: <?= htmlspecialchars($import['filename']) ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/ofx" class="btn btn-sm btn-outline-secondary">Voltar</a>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body bg-light">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1"><i class="fas fa-magic text-warning"></i> Conciliação Inteligente</h5>
                <p class="text-muted mb-0 small">O sistema procura automaticamente lançamentos no seu banco de dados (Dízimos ou Despesas) com o <strong>mesmo valor</strong> e <strong>data próxima</strong> para sugerir vínculos.</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-success fs-6"><?= count($matches ?? []) ?> sugestões encontradas</span>
            </div>
        </div>
    </div>
</div>

<form action="/admin/financial/ofx/save/<?= $import['id'] ?>" method="POST">
    <?= csrf_field() ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
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
                                <tr class="<?= $tx['status'] !== 'pending' ? 'table-light opacity-50' : ($isMatch ? 'table-warning' : '') ?>">
                                    <!-- Coluna OFX -->
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
                                    
                                    <!-- Coluna Sistema -->
                                    <td class="border-end">
                                        <?php if ($tx['status'] !== 'pending'): ?>
                                            <div class="text-success"><i class="fas fa-check-circle"></i> Já conciliado</div>
                                        <?php elseif ($isMatch): ?>
                                            <div class="p-2 border border-success rounded bg-white">
                                                <span class="badge bg-success mb-1">Sugestão Encontrada</span>
                                                <div class="small fw-bold"><?= date('d/m/Y', strtotime($match['date'])) ?> - <?= htmlspecialchars($match['description']) ?></div>
                                                <input type="hidden" name="system_id[<?= $tx['id'] ?>]" value="<?= $match['system_id'] ?>">
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small fst-italic">
                                                Nenhum lançamento compatível encontrado no sistema. Será criado como novo.
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Coluna Plano de Contas -->
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

                                    <!-- Coluna Ação -->
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
                                            <span class="badge bg-secondary">Processado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($import['status'] === 'pending'): ?>
                <div class="card-footer text-end bg-white p-3">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-check-double"></i> Processar Conciliação</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

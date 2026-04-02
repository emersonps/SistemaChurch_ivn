<?php include __DIR__ . '/layout_developer.php'; ?>

<h1 class="h2 mb-4">Gerenciar Pagamentos do Sistema</h1>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Operação realizada com sucesso.</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Gerar/Atualizar Cobrança</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/developer/payments/generate">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Mês de Referência</label>
                    <input type="month" name="month" class="form-control" required value="<?= date('Y-m') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dia Venc.</label>
                    <input type="number" name="due_day" class="form-control" value="5" min="1" max="31" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor (R$)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="59.99" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pending">Pendente (Gerar Cobrança)</option>
                        <option value="paid">Pago (Baixa Manual)</option>
                        <option value="overdue">Atrasado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100">Executar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Histórico Completo</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mês Ref.</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Data Vencimento</th>
                    <th>Data Pagamento</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="7" class="text-center">Nenhum registro encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach($payments as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= $p['reference_month'] ?></td>
                        <td>R$ <?= number_format($p['amount'] ?? 59.99, 2, ',', '.') ?></td>
                        <td>
                            <span class="badge bg-<?= $p['status'] == 'paid' ? 'success' : ($p['status'] == 'overdue' ? 'danger' : 'warning') ?>">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </td>
                        <td><?= !empty($p['due_date']) ? date('d/m/Y', strtotime($p['due_date'])) : (!empty($p['payment_date']) && $p['status'] !== 'paid' ? date('d/m/Y', strtotime($p['payment_date'])) : '-') ?></td>
                        <td><?= $p['status'] === 'paid' && !empty($p['payment_date']) ? date('d/m/Y H:i', strtotime($p['payment_date'])) : '-' ?></td>
                        <td>
                            <a href="/developer/payments/delete?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?');">
                                <i class="fas fa-trash"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editStatusModal<?= $p['id'] ?>" <?= $p['status'] === 'paid' ? 'disabled title="Pagamento já quitado"' : '' ?>>
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <!-- Modal Edit Status -->
                            <div class="modal fade" id="editStatusModal<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Alterar Status (ID: <?= $p['id'] ?>)</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="/developer/payments/update-status" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Mês de Referência</label>
                                                    <input type="month" name="reference_month" class="form-control" value="<?= htmlspecialchars($p['reference_month']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Novo Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="pending" <?= $p['status'] == 'pending' ? 'selected' : '' ?>>Pendente</option>
                                                        <option value="paid" <?= $p['status'] == 'paid' ? 'selected' : '' ?>>Pago</option>
                                                        <option value="overdue" <?= $p['status'] == 'overdue' ? 'selected' : '' ?>>Atrasado</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Valor (R$)</label>
                                                    <input type="number" step="0.01" name="amount" class="form-control" value="<?= htmlspecialchars((string)($p['amount'] ?? 59.99)) ?>" required>
                                                </div>
                                                <div>
                                                    <label class="form-label">Data de Vencimento</label>
                                                    <input type="date" name="due_date" class="form-control" value="<?= !empty($p['due_date']) ? date('Y-m-d', strtotime($p['due_date'])) : (!empty($p['payment_date']) && $p['status'] !== 'paid' ? date('Y-m-d', strtotime($p['payment_date'])) : '') ?>">
                                                    <div class="form-text">Usado para pagamentos pendentes ou atrasados. Se marcar como pago, o sistema registra a data atual do pagamento.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Salvar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/layout_footer.php'; ?>

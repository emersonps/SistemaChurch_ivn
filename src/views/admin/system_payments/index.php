<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pagamento do Sistema</h1>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Situação da Cobrança do Sistema</h5>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                <?php if ($status == 'paid' && !empty($latestPaidPayment)): ?>
                    <div class="text-success mb-3">
                        <i class="fas fa-check-circle fa-5x"></i>
                    </div>
                    <h3 class="text-success">PAGO</h3>
                    <p class="text-muted">
                        Último pagamento registrado em <?= htmlspecialchars($latestPaidPayment['paid_at_display'] ?? '-') ?>
                        referente a <?= htmlspecialchars(date('m/Y', strtotime(($latestPaidPayment['reference_month'] ?? date('Y-m')) . '-01'))) ?>.
                    </p>
                <?php else: ?>
                    <?php if ($status == 'overdue'): ?>
                        <div class="text-danger mb-3">
                            <i class="fas fa-exclamation-circle fa-5x"></i>
                        </div>
                        <h3 class="text-danger">ATRASADO</h3>
                        <p class="text-danger fw-bold">O vencimento foi em <?= htmlspecialchars($dueDateDisplay ?? ('05/' . date('m/Y'))) ?>!</p>
                    <?php elseif ($status == 'today'): ?>
                        <div class="text-warning mb-3">
                            <i class="fas fa-exclamation-triangle fa-5x"></i>
                        </div>
                        <h3 class="text-warning">VENCE HOJE</h3>
                        <p class="fw-bold">A fatura vence hoje (<?= htmlspecialchars($dueDateDisplay ?? ('05/' . date('m/Y'))) ?>).</p>
                    <?php elseif ($status == 'alert'): ?>
                        <div class="text-warning mb-3">
                            <i class="fas fa-clock fa-5x"></i>
                        </div>
                        <h3 class="text-warning">VENCE EM BREVE</h3>
                        <p class="fw-bold">Faltam <?= $daysRemaining ?> dias para o vencimento (<?= htmlspecialchars($dueDateDisplay ?? ('05/' . date('m/Y'))) ?>).</p>
                    <?php elseif ($status == 'pending'): ?>
                        <div class="text-info mb-3">
                            <i class="fas fa-file-invoice-dollar fa-5x"></i>
                        </div>
                        <h3 class="text-info">AGUARDANDO PAGAMENTO</h3>
                        <p class="text-muted">Fatura gerada. Vencimento: <?= htmlspecialchars($dueDateDisplay ?? ('05/' . date('m/Y'))) ?></p>
                        <?php if (isset($nextPendingPayment['amount'])): ?>
                            <p class="fw-bold fs-4">Valor: R$ <?= number_format((float)($nextPendingPayment['amount'] ?? 0), 2, ',', '.') ?></p>
                        <?php endif; ?>
                    <?php elseif ($status == 'no_charge'): ?>
                        <div class="text-success mb-3">
                            <i class="fas fa-smile fa-5x"></i>
                        </div>
                        <h3 class="text-success">TUDO EM DIA</h3>
                        <p class="text-muted">Nenhuma cobrança gerada para este mês ainda.</p>
                    <?php else: ?>
                        <div class="text-secondary mb-3">
                            <i class="fas fa-calendar-alt fa-5x"></i>
                        </div>
                        <h3 class="text-secondary">PRÓXIMA FATURA</h3>
                        <p class="text-muted">Vencimento: Dia <?= str_pad($dueDay ?? 5, 2, '0', STR_PAD_LEFT) ?></p>
                    <?php endif; ?>

                    <hr class="w-100">
                    
                    <?php if (($_SESSION['user_role'] ?? '') === 'developer'): ?>
                        <form method="POST" action="/admin/system-payments/pay" onsubmit="return confirm('Confirmar que o pagamento foi realizado?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="month" value="<?= $currentMonth ?>">
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-check"></i> Confirmar Pagamento
                            </button>
                        </form>
                    <?php else: ?>
                         <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> O pagamento deve ser realizado via PIX. A baixa será dada pelo administrador do sistema.
                         </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Dados para Pagamento (PIX)</h5>
            </div>
            <div class="card-body text-center">
                <?php if (isset($billToPay) && $billToPay): ?>
                    <p class="mb-2">Escaneie o QR Code abaixo para pagar a fatura de <strong><?= date('m/Y', strtotime($billToPay['reference_month'] . '-01')) ?></strong>:</p>
                <?php else: ?>
                    <p class="mb-2">Escaneie o QR Code abaixo ou use a chave PIX:</p>
                <?php endif; ?>
                
                <div class="mb-3 p-2 border rounded d-inline-block bg-white">
                    <!-- QR Code Container -->
                    <div id="qrcode"></div>
                </div>
                
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="text" class="form-control text-center fw-bold" value="<?= htmlspecialchars($pixPayload) ?>" id="pixPayload" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyPixPayload()">
                        <i class="fas fa-copy"></i> Copiar Código PIX
                    </button>
                </div>
                
                <p class="small text-muted">
                    Beneficiário: EMERSON PINHEIRO DE SOUZA<br>
                    Banco: Santander<br>
                    <?php if (isset($billToPay['amount'])): ?>
                        <strong>Valor: R$ <?= number_format((float)($billToPay['amount'] ?? 0), 2, ',', '.') ?></strong>
                    <?php else: ?>
                        <strong>Valor: R$ 59,99</strong>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($latestPaidPayment) || !empty($nextPendingPayment)): ?>
<div class="row mb-4">
    <?php if (!empty($latestPaidPayment)): ?>
    <div class="col-md-6">
        <div class="card shadow-sm border-success h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Último Pagamento Confirmado</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Referência:</strong> <?= htmlspecialchars(date('m/Y', strtotime(($latestPaidPayment['reference_month'] ?? date('Y-m')) . '-01'))) ?></p>
                <p class="mb-2"><strong>Valor:</strong> R$ <?= number_format((float)($latestPaidPayment['amount'] ?? 0), 2, ',', '.') ?></p>
                <p class="mb-2"><strong>Vencimento:</strong> <?= htmlspecialchars($latestPaidPayment['due_date_display'] ?? '-') ?></p>
                <p class="mb-0"><strong>Pago em:</strong> <?= htmlspecialchars($latestPaidPayment['paid_at_display'] ?? '-') ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($nextPendingPayment)): ?>
    <div class="col-md-6">
        <div class="card shadow-sm border-warning h-100">
            <div class="card-header bg-warning">
                <h5 class="mb-0">Próxima Cobrança</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Referência:</strong> <?= htmlspecialchars(date('m/Y', strtotime(($nextPendingPayment['reference_month'] ?? date('Y-m')) . '-01'))) ?></p>
                <p class="mb-2"><strong>Valor:</strong> R$ <?= number_format((float)($nextPendingPayment['amount'] ?? 0), 2, ',', '.') ?></p>
                <p class="mb-2"><strong>Vencimento:</strong> <?= htmlspecialchars($nextPendingPayment['due_date_display'] ?? '-') ?></p>
                <p class="mb-0"><strong>Status:</strong> 
                    <?php if (($nextPendingPayment['display_status'] ?? $nextPendingPayment['status']) === 'overdue'): ?>
                        <span class="badge bg-danger">Atrasado</span>
                    <?php elseif (($nextPendingPayment['display_status'] ?? $nextPendingPayment['status']) === 'today'): ?>
                        <span class="badge bg-warning text-dark">Vence Hoje</span>
                    <?php elseif (($nextPendingPayment['display_status'] ?? $nextPendingPayment['status']) === 'alert'): ?>
                        <span class="badge bg-info text-dark">Vence em Breve</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Pendente</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0">Histórico de Pagamentos</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Mês de Referência</th>
                    <th>Data de Vencimento</th>
                    <th>Valor</th>
                    <th>Data do Pagamento</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-3">Nenhum pagamento registrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($payments as $p): ?>
                            <?php
                            $historyDueDateText = !empty($p['history_due_date_display'])
                                ? $p['history_due_date_display']
                                : (!empty($p['due_date_display'])
                                    ? $p['due_date_display']
                                    : (!empty($p['due_date'])
                                        ? date('d/m/Y', strtotime($p['due_date']))
                                        : (!empty($p['reference_month'])
                                            ? '05/' . date('m/Y', strtotime($p['reference_month'] . '-01'))
                                            : '-')));
                            $historyPaymentDateText = !empty($p['history_payment_date_display'])
                                ? $p['history_payment_date_display']
                                : (!empty($p['paid_at_display'])
                                    ? $p['paid_at_display']
                                    : (!empty($p['payment_date'])
                                        ? date('d/m/Y H:i', strtotime($p['payment_date']))
                                        : '-'));
                            $historyAmountValue = isset($p['amount']) && $p['amount'] !== '' && $p['amount'] !== null
                                ? (float)$p['amount']
                                : 59.99;
                            ?>
                            <tr>
                                 
                                <td><?= htmlspecialchars(date('m/Y', strtotime($p['reference_month'] . '-01'))) ?></td>
                                <td><?= htmlspecialchars($historyDueDateText) ?></td>
                                <td>R$ <?= number_format($historyAmountValue, 2, ',', '.') ?></td>
                                <td><?= !empty($p['is_paid']) ? htmlspecialchars($historyPaymentDateText) : '-' ?></td>
                                <td>
                                    <?php if (($p['display_status'] ?? $p['status']) == 'paid'): ?>
                                        <span class="badge bg-success">Pago</span>
                                    <?php elseif (($p['display_status'] ?? $p['status']) == 'overdue'): ?>
                                        <span class="badge bg-danger">Atrasado</span>
                                    <?php elseif (($p['display_status'] ?? $p['status']) == 'today'): ?>
                                        <span class="badge bg-warning text-dark">Vence Hoje</span>
                                    <?php elseif (($p['display_status'] ?? $p['status']) == 'alert'): ?>
                                        <span class="badge bg-info text-dark">Vence em Breve</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // Generate QR Code
    // Use the Payload generated in PHP
    const pixPayload = "<?= $pixPayload ?>";
    
    new QRCode(document.getElementById("qrcode"), {
        text: pixPayload,
        width: 150,
        height: 150
    });

    function copyPixPayload() {
        var copyText = document.getElementById("pixPayload");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        alert("Código PIX copiado: " + copyText.value);
    }
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

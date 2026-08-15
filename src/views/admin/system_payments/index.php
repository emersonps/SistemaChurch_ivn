<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pagamento do Sistema</h1>
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
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-card-header-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        gap: .55rem;
    }
    .member-form-card-body { padding: 1.25rem; }

    /* Hero de status */
    .payment-hero {
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.08);
        padding: 1.75rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        height: 100%;
    }
    .payment-hero-icon {
        flex: 0 0 auto;
        width: 74px;
        height: 74px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }
    .payment-hero-eyebrow {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #868e96;
        margin-bottom: .15rem;
    }
    .payment-hero-title {
        font-weight: 800;
        font-size: 1.6rem;
        margin-bottom: .2rem;
        line-height: 1.1;
    }
    .payment-hero-desc {
        font-size: .9rem;
        color: #495057;
        margin-bottom: 0;
    }
    .payment-hero.hero-success { background: rgba(25,135,84,0.06); }
    .payment-hero.hero-success .payment-hero-icon { background: rgba(25,135,84,0.14); color: #198754; }
    .payment-hero.hero-success .payment-hero-title { color: #198754; }
    .payment-hero.hero-danger { background: rgba(220,53,69,0.06); }
    .payment-hero.hero-danger .payment-hero-icon { background: rgba(220,53,69,0.14); color: #dc3545; }
    .payment-hero.hero-danger .payment-hero-title { color: #dc3545; }
    .payment-hero.hero-warning { background: rgba(212,175,55,0.10); }
    .payment-hero.hero-warning .payment-hero-icon { background: rgba(212,175,55,0.22); color: #a6790a; }
    .payment-hero.hero-warning .payment-hero-title { color: #a6790a; }
    .payment-hero.hero-info { background: rgba(13,110,253,0.06); }
    .payment-hero.hero-info .payment-hero-icon { background: rgba(13,110,253,0.14); color: #0d6efd; }
    .payment-hero.hero-info .payment-hero-title { color: #0d6efd; }
    .payment-hero.hero-neutral { background: #f8f9fa; }
    .payment-hero.hero-neutral .payment-hero-icon { background: #eef0f2; color: #6c757d; }
    .payment-hero.hero-neutral .payment-hero-title { color: #495057; }

    /* PIX */
    .pix-key-box { text-align: left; }
    .pix-key-label {
        display: block;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #868e96;
        margin-bottom: .25rem;
    }
    .pix-key-box .pix-key-input {
        border-radius: 8px 0 0 8px;
        background: #f8f9fa;
        font-family: 'Courier New', monospace;
        font-size: .82rem;
    }
    .btn-copy-pix {
        border-radius: 0 8px 8px 0;
        background: #b30000;
        border-color: #b30000;
        color: #fff;
        font-weight: 600;
    }
    .btn-copy-pix:hover { background: #8a0000; border-color: #8a0000; color: #fff; }
    .qrcode-frame {
        display: inline-flex;
        padding: 10px;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
    }
    .beneficiary-info-box {
        background: #f8f9fa;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 10px;
        padding: .75rem 1rem;
        font-size: .85rem;
        color: #495057;
        text-align: left;
    }
    .amount-highlight {
        font-size: 1.35rem;
        font-weight: 800;
        color: #212529;
    }

    /* Info cards */
    .info-accent-card {
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.08);
        overflow: hidden;
        height: 100%;
    }
    .info-accent-header {
        padding: .85rem 1.25rem;
        font-weight: 800;
        font-size: .92rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .info-accent-card.accent-success .info-accent-header { background: rgba(25,135,84,0.10); color: #198754; }
    .info-accent-card.accent-warning .info-accent-header { background: rgba(212,175,55,0.16); color: #a6790a; }
    .info-field-row {
        display: flex;
        justify-content: space-between;
        padding: .6rem 1.25rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        font-size: .88rem;
    }
    .info-field-row .label { color: #868e96; }
    .info-field-row .value { font-weight: 700; color: #212529; }

    /* Status pills */
    .status-pill {
        display: inline-block;
        padding: .25rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }
    .status-pill.st-paid { background: rgba(25,135,84,0.12); color: #198754; }
    .status-pill.st-overdue { background: rgba(220,53,69,0.12); color: #dc3545; }
    .status-pill.st-today { background: rgba(212,175,55,0.20); color: #a6790a; }
    .status-pill.st-alert { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .status-pill.st-pending { background: rgba(212,175,55,0.20); color: #a6790a; }

    .history-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .history-table td {
        vertical-align: middle;
        padding-top: .6rem;
        padding-bottom: .6rem;
    }
</style>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i> Pagamento confirmado com sucesso.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php
$heroMap = [
    'paid' => ['class' => 'hero-success', 'icon' => 'fa-check-circle', 'title' => 'PAGO'],
    'overdue' => ['class' => 'hero-danger', 'icon' => 'fa-exclamation-circle', 'title' => 'ATRASADO'],
    'today' => ['class' => 'hero-warning', 'icon' => 'fa-exclamation-triangle', 'title' => 'VENCE HOJE'],
    'alert' => ['class' => 'hero-warning', 'icon' => 'fa-clock', 'title' => 'VENCE EM BREVE'],
    'pending' => ['class' => 'hero-info', 'icon' => 'fa-file-invoice-dollar', 'title' => 'AGUARDANDO PAGAMENTO'],
    'no_charge' => ['class' => 'hero-success', 'icon' => 'fa-smile', 'title' => 'TUDO EM DIA'],
];
$hero = ($status === 'paid' && !empty($latestPaidPayment)) ? $heroMap['paid'] : ($heroMap[$status] ?? ['class' => 'hero-neutral', 'icon' => 'fa-calendar-alt', 'title' => 'PRÓXIMA FATURA']);
?>

<div class="row g-3 mb-3">
    <!-- Hero de Status -->
    <div class="col-lg-5">
        <div class="payment-hero <?= $hero['class'] ?>">
            <div class="payment-hero-icon"><i class="fas <?= $hero['icon'] ?>"></i></div>
            <div>
                <div class="payment-hero-eyebrow">Situação da Cobrança</div>
                <div class="payment-hero-title"><?= $hero['title'] ?></div>
                <?php if ($status == 'paid' && !empty($latestPaidPayment)): ?>
                    <p class="payment-hero-desc">
                        Último pagamento registrado em <?= htmlspecialchars($latestPaidPayment['paid_at_display'] ?? '-') ?>
                        referente a <?= htmlspecialchars(date('m/Y', strtotime(($latestPaidPayment['reference_month'] ?? date('Y-m')) . '-01'))) ?>.
                    </p>
                <?php elseif ($status == 'overdue'): ?>
                    <p class="payment-hero-desc fw-bold">O vencimento foi em <?= htmlspecialchars($dueDateDisplay ?? ('05/' . date('m/Y'))) ?>!</p>
                <?php elseif ($status == 'today'): ?>
                    <p class="payment-hero-desc fw-bold">A fatura vence hoje (<?= htmlspecialchars($dueDateDisplay ?? ('05/' . date('m/Y'))) ?>).</p>
                <?php elseif ($status == 'alert'): ?>
                    <p class="payment-hero-desc fw-bold">Faltam <?= $daysRemaining ?> dia(s) para o vencimento (<?= htmlspecialchars($dueDateDisplay ?? ('05/' . date('m/Y'))) ?>).</p>
                <?php elseif ($status == 'pending'): ?>
                    <p class="payment-hero-desc">Fatura gerada. Vencimento: <?= htmlspecialchars($dueDateDisplay ?? ('05/' . date('m/Y'))) ?></p>
                    <?php if (isset($nextPendingPayment['amount'])): ?>
                        <p class="amount-highlight mb-0 mt-1">R$ <?= number_format((float)($nextPendingPayment['amount'] ?? 0), 2, ',', '.') ?></p>
                    <?php endif; ?>
                <?php elseif ($status == 'no_charge'): ?>
                    <p class="payment-hero-desc">Nenhuma cobrança gerada para este mês ainda.</p>
                <?php else: ?>
                    <p class="payment-hero-desc">Vencimento: Dia <?= str_pad($dueDay ?? 5, 2, '0', STR_PAD_LEFT) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!($status == 'paid' && !empty($latestPaidPayment))): ?>
            <?php if (!$billingManagedByCentral && ($_SESSION['user_role'] ?? '') === 'developer'): ?>
                <form method="POST" action="/admin/system-payments/pay" id="confirmPaymentForm" class="mt-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="month" value="<?= $currentMonth ?>">
                    <button type="button" id="btnConfirmPayment" class="btn btn-success btn-lg w-100 rounded-pill fw-semibold">
                        <i class="fas fa-check me-1"></i> Confirmar Pagamento
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- PIX -->
    <div class="col-lg-7">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-card-header-title"><i class="fas fa-qrcode"></i> Dados para Pagamento (PIX)</div>
            </div>
            <div class="member-form-card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-5 text-center">
                        <?php if (isset($billToPay) && $billToPay): ?>
                            <p class="small text-muted mb-2">Fatura de <strong><?= date('m/Y', strtotime($billToPay['reference_month'] . '-01')) ?></strong></p>
                        <?php endif; ?>
                        <div class="qrcode-frame">
                            <div id="qrcode"></div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="pix-key-box mb-3">
                            <label class="pix-key-label">Código PIX Copia e Cola</label>
                            <div class="input-group">
                                <input type="text" class="form-control pix-key-input" value="<?= htmlspecialchars($pixPayload) ?>" id="pixPayload" readonly>
                                <button class="btn btn-copy-pix" type="button" id="btnCopyPix">
                                    <i class="fas fa-copy me-1"></i> Copiar
                                </button>
                            </div>
                        </div>
                        <div class="beneficiary-info-box">
                            <div><span class="text-muted">Beneficiário:</span> <strong>EMERSON PINHEIRO DE SOUZA</strong></div>
                            <div><span class="text-muted">Banco:</span> <strong>Santander</strong></div>
                            <div class="mt-1">
                                <span class="text-muted">Valor:</span>
                                <span class="amount-highlight">
                                    R$ <?= number_format((float)($billToPay['amount'] ?? 59.99), 2, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($latestPaidPayment) || !empty($nextPendingPayment)): ?>
<div class="row g-3 mb-3">
    <?php if (!empty($latestPaidPayment)): ?>
    <div class="col-md-6">
        <div class="info-accent-card accent-success">
            <div class="info-accent-header"><i class="fas fa-check-circle"></i> Último Pagamento Confirmado</div>
            <div class="info-field-row"><span class="label">Referência</span><span class="value"><?= htmlspecialchars(date('m/Y', strtotime(($latestPaidPayment['reference_month'] ?? date('Y-m')) . '-01'))) ?></span></div>
            <div class="info-field-row"><span class="label">Valor</span><span class="value">R$ <?= number_format((float)($latestPaidPayment['amount'] ?? 0), 2, ',', '.') ?></span></div>
            <div class="info-field-row"><span class="label">Vencimento</span><span class="value"><?= htmlspecialchars($latestPaidPayment['due_date_display'] ?? '-') ?></span></div>
            <div class="info-field-row"><span class="label">Pago em</span><span class="value"><?= htmlspecialchars($latestPaidPayment['paid_at_display'] ?? '-') ?></span></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($nextPendingPayment)): ?>
    <div class="col-md-6">
        <div class="info-accent-card accent-warning">
            <div class="info-accent-header"><i class="fas fa-hourglass-half"></i> Próxima Cobrança</div>
            <div class="info-field-row"><span class="label">Referência</span><span class="value"><?= htmlspecialchars(date('m/Y', strtotime(($nextPendingPayment['reference_month'] ?? date('Y-m')) . '-01'))) ?></span></div>
            <div class="info-field-row"><span class="label">Valor</span><span class="value">R$ <?= number_format((float)($nextPendingPayment['amount'] ?? 0), 2, ',', '.') ?></span></div>
            <div class="info-field-row"><span class="label">Vencimento</span><span class="value"><?= htmlspecialchars($nextPendingPayment['due_date_display'] ?? '-') ?></span></div>
            <div class="info-field-row">
                <span class="label">Status</span>
                <span class="value">
                    <?php
                        $npStatus = $nextPendingPayment['display_status'] ?? $nextPendingPayment['status'];
                        $npLabels = ['overdue' => 'Atrasado', 'today' => 'Vence Hoje', 'alert' => 'Vence em Breve'];
                        $npClass = ['overdue' => 'st-overdue', 'today' => 'st-today', 'alert' => 'st-alert'];
                    ?>
                    <span class="status-pill <?= $npClass[$npStatus] ?? 'st-pending' ?>"><?= $npLabels[$npStatus] ?? 'Pendente' ?></span>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="member-form-card">
    <div class="member-form-card-header">
        <div class="member-form-card-header-title"><i class="fas fa-history"></i> Histórico de Pagamentos</div>
    </div>
    <div class="p-2">
        <div class="table-responsive">
            <table class="table table-hover history-table mb-0">
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
                            <td colspan="5" class="text-center py-4 text-muted">Nenhum pagamento registrado.</td>
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
                                $rowStatus = $p['display_status'] ?? $p['status'];
                                $rowLabels = ['paid' => 'Pago', 'overdue' => 'Atrasado', 'today' => 'Vence Hoje', 'alert' => 'Vence em Breve'];
                                $rowClass = ['paid' => 'st-paid', 'overdue' => 'st-overdue', 'today' => 'st-today', 'alert' => 'st-alert'];
                                ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars(date('m/Y', strtotime($p['reference_month'] . '-01'))) ?></td>
                                    <td><?= htmlspecialchars($historyDueDateText) ?></td>
                                    <td>R$ <?= number_format($historyAmountValue, 2, ',', '.') ?></td>
                                    <td><?= !empty($p['is_paid']) ? htmlspecialchars($historyPaymentDateText) : '-' ?></td>
                                    <td><span class="status-pill <?= $rowClass[$rowStatus] ?? 'st-pending' ?>"><?= $rowLabels[$rowStatus] ?? 'Pendente' ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const pixPayload = "<?= $pixPayload ?>";

    new QRCode(document.getElementById("qrcode"), {
        text: pixPayload,
        width: 150,
        height: 150
    });

    document.getElementById('btnCopyPix').addEventListener('click', function () {
        const btn = this;
        const copyText = document.getElementById("pixPayload");
        const original = btn.innerHTML;

        function showCopied() {
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Copiado!';
            setTimeout(function () { btn.innerHTML = original; }, 2000);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(copyText.value).then(showCopied, function () {
                copyText.removeAttribute('readonly');
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                try { document.execCommand('copy'); showCopied(); } catch (e) {}
                copyText.setAttribute('readonly', 'readonly');
            });
        } else {
            copyText.removeAttribute('readonly');
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            try { document.execCommand('copy'); showCopied(); } catch (e) {}
            copyText.setAttribute('readonly', 'readonly');
        }
    });

    const btnConfirmPayment = document.getElementById('btnConfirmPayment');
    if (btnConfirmPayment) {
        btnConfirmPayment.addEventListener('click', function () {
            Swal.fire({
                title: 'Confirmar pagamento?',
                text: 'Confirmar que o pagamento foi realizado?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('confirmPaymentForm').submit();
                }
            });
        });
    }
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

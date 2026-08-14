<?php $seo_title = 'Doação - ' . (($siteProfile['alias'] ?? null) ?: 'Igreja'); ?>
<?php include __DIR__ . '/layout/header.php'; ?>

<section class="py-5" style="background:#f8f9fa; min-height: 60vh;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="section-title">Doação</h1>
            <p class="text-muted">Escolha uma das chaves PIX abaixo, escaneie o QR Code ou copie a chave para contribuir. O valor é livre, digite o quanto desejar doar no seu aplicativo do banco.</p>
        </div>

        <?php if (empty($accounts)): ?>
            <div class="alert alert-info text-center">Nenhuma chave PIX disponível no momento.</div>
        <?php else: ?>
        <div class="row g-4 justify-content-center">
            <?php foreach ($accounts as $a): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm text-center donation-card">
                        <div class="card-body d-flex flex-column align-items-center">
                            <span class="pix-type-pill mb-2"><?= htmlspecialchars($pixKeyTypes[$a['pix_key_type']] ?? $a['pix_key_type']) ?></span>
                            <h5 class="mb-1"><i class="fas fa-university me-2 text-red"></i><?= htmlspecialchars($a['bank_name']) ?></h5>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($a['beneficiary_name']) ?></p>

                            <div class="qrcode-box mb-3" data-pix-payload="<?= htmlspecialchars($a['pix_payload']) ?>"></div>

                            <div class="pix-key-box w-100 mb-2">
                                <label class="pix-key-label">Chave PIX</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm pix-key-input" value="<?= htmlspecialchars($a['pix_key']) ?>" readonly>
                                    <button class="btn btn-sm btn-copy-pix" type="button" data-pix-key="<?= htmlspecialchars($a['pix_key']) ?>">
                                        <i class="fas fa-copy me-1"></i> Copiar
                                    </button>
                                </div>
                            </div>

                            <?php if (!empty($a['agency']) || !empty($a['account_number'])): ?>
                                <div class="transfer-info-box w-100 mt-1">
                                    <div class="transfer-info-title">Ou transferência tradicional</div>
                                    <?php if (!empty($a['agency'])): ?><div>Agência: <strong><?= htmlspecialchars($a['agency']) ?></strong></div><?php endif; ?>
                                    <?php if (!empty($a['account_number'])): ?><div>Conta: <strong><?= htmlspecialchars($a['account_number']) ?></strong></div><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
    .donation-card {
        border-top: 4px solid var(--primary-gold);
        border-radius: 14px;
        overflow: hidden;
    }
    .qrcode-box {
        padding: 10px;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        display: inline-flex;
    }
    .qrcode-box img, .qrcode-box canvas { max-width: 100%; height: auto; }

    .pix-type-pill {
        display: inline-block;
        padding: .25rem .7rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
        background: rgba(179,0,0,0.08);
        color: var(--primary-red);
    }

    .pix-key-box { text-align: left; }
    .pix-key-label {
        display: block;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #868e96;
        margin-bottom: .2rem;
    }
    .pix-key-box .pix-key-input {
        border-radius: 8px 0 0 8px;
        background: #f8f9fa;
        font-family: 'Courier New', monospace;
    }
    .btn-copy-pix {
        border-radius: 0 8px 8px 0;
        background: var(--primary-gold);
        border-color: var(--primary-gold);
        color: #fff;
        font-weight: 600;
    }
    .btn-copy-pix:hover {
        background: var(--primary-gold-dark);
        border-color: var(--primary-gold-dark);
        color: #fff;
    }

    .transfer-info-box {
        background: #f8f9fa;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 10px;
        padding: .6rem .8rem;
        font-size: .82rem;
        color: #495057;
        text-align: left;
    }
    .transfer-info-title {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        margin-bottom: .2rem;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.querySelectorAll('.qrcode-box').forEach(function (box) {
        var payload = box.getAttribute('data-pix-payload');
        new QRCode(box, {
            text: payload,
            width: 200,
            height: 200,
            correctLevel: QRCode.CorrectLevel.M
        });
    });

    document.querySelectorAll('.btn-copy-pix').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.getAttribute('data-pix-key');
            var input = btn.closest('.input-group').querySelector('.pix-key-input');

            function showCopied() {
                var original = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                setTimeout(function () { btn.innerHTML = original; }, 2000);
            }

            function fallbackCopy() {
                input.removeAttribute('readonly');
                input.focus();
                input.select();
                input.setSelectionRange(0, key.length);
                try {
                    if (document.execCommand('copy')) {
                        showCopied();
                    }
                } catch (e) {
                    // Ignora: usuário pode copiar manualmente pelo campo selecionado
                }
                input.setAttribute('readonly', 'readonly');
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(key).then(showCopied, fallbackCopy);
            } else {
                fallbackCopy();
            }
        });
    });
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>

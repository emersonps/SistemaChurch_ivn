<?php $siteProfile = getChurchSiteProfileSettings(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de <?= htmlspecialchars($tithe['type'] ?? 'Dízimo') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f0f0;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
        }
        .receipt-copy {
            border: 1px solid rgba(0,0,0,0.14);
            border-radius: 14px;
            padding: 0;
            margin-bottom: 15px;
            background: #fff;
            page-break-inside: avoid;
            overflow: hidden;
        }
        .receipt-copy:last-child {
            margin-bottom: 0;
        }
        .copy-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #fff;
            background: #b30000;
            text-align: center;
            padding: 3px 0;
        }
        .receipt-body { padding: 14px 16px; }
        .logo-img {
            max-width: 54px;
            max-height: 54px;
            object-fit: contain;
        }
        .receipt-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            border-bottom: 2px solid #b30000;
            padding-bottom: 10px;
        }
        .receipt-header h5 {
            font-weight: 800;
            color: #1a1a1a;
        }
        .receipt-header small {
            font-weight: 700;
            color: #b30000;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .receipt-number {
            margin-left: auto;
            text-align: right;
        }
        .receipt-number .num-label {
            font-size: 9px;
            color: #868e96;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .receipt-number .num-value {
            font-size: 16px;
            font-weight: 800;
            color: #b30000;
            font-family: 'Courier New', monospace;
        }
        .receipt-title {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 8px 0 10px;
            text-align: center;
            color: #1a1a1a;
        }
        .receipt-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 18px;
            font-size: 11px;
            color: #495057;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dashed rgba(0,0,0,0.15);
        }
        .receipt-meta strong { color: #1a1a1a; }
        .receipt-narrative {
            font-size: 13px;
            line-height: 1.5;
            margin: 8px 0;
            color: #212529;
        }
        .receipt-narrative strong { color: #1a1a1a; }
        .receipt-amount {
            font-size: 17px;
            font-weight: 800;
            text-align: center;
            margin: 10px 0;
            padding: 8px;
            background: rgba(179,0,0,0.06);
            border: 1px solid rgba(179,0,0,0.2);
            border-radius: 10px;
            color: #b30000;
        }
        .receipt-signature {
            margin-top: 14px;
            text-align: center;
        }
        .receipt-signature p {
            margin-top: 28px;
            border-top: 1px solid #000;
            padding-top: 4px;
            font-size: 11px;
        }
        .cut-line {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #adb5bd;
            font-size: 10px;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin: 4px 0 15px;
        }
        .cut-line::before,
        .cut-line::after {
            content: '';
            flex: 1;
            border-top: 1px dashed #ced4da;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
                font-size: 12px;
            }
            .receipt-container {
                max-width: 100%;
                padding: 0;
            }
            .no-print { display: none !important; }
            .cut-line { display: none !important; }
            .receipt-copy {
                border: 1.5px solid #000;
                border-radius: 8px;
                page-break-inside: avoid;
                padding: 0;
                margin-bottom: 10px;
            }
            .receipt-body { padding: 8px 12px; }
            .logo-img {
                max-width: 42px;
                max-height: 42px;
            }
            .receipt-header {
                margin-bottom: 6px;
                padding-bottom: 6px;
                gap: 8px;
            }
            .receipt-title {
                font-size: 13px;
                margin: 5px 0 7px;
            }
            .receipt-meta {
                font-size: 9px;
                margin-bottom: 6px;
                padding-bottom: 5px;
            }
            .receipt-narrative {
                font-size: 11px;
                margin: 5px 0;
            }
            .receipt-amount {
                font-size: 13px;
                margin: 6px 0;
                padding: 5px;
            }
            .receipt-signature {
                margin-top: 8px;
            }
            .receipt-signature p {
                margin-top: 14px;
                font-size: 10px;
            }
            .receipt-signature img {
                max-height: 32px !important;
            }
        }
        @page {
            size: A4;
            margin: 6mm;
        }
        body.hide-second-via #viaDuplicata {
            display: none !important;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="receipt-container">
        <?php
        $renderCopy = function ($copyId, $copyLabel) use ($siteProfile, $tithe, $receiptSignatures) {
        ?>
        <div class="receipt-copy" id="<?= $copyId ?>">
            <div class="copy-label"><?= $copyLabel ?></div>
            <div class="receipt-body">
                <div class="receipt-header">
                    <?php if (!empty($siteProfile['logo_url'])): ?>
                        <img src="<?= htmlspecialchars(getChurchLogoUrl($siteProfile)) ?>" alt="Logo" class="logo-img">
                    <?php else: ?>
                        <i class="fas fa-church fa-2x text-danger"></i>
                    <?php endif; ?>
                    <div>
                        <h5 class="mb-0"><?= htmlspecialchars($siteProfile['name'] ?? 'Igreja Vida Nova') ?></h5>
                        <small><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></small>
                    </div>
                    <div class="receipt-number">
                        <div class="num-label">Recibo Nº</div>
                        <div class="num-value">#<?= str_pad($tithe['id'], 6, '0', STR_PAD_LEFT) ?></div>
                    </div>
                </div>

                <div class="receipt-title">Recibo de <?= htmlspecialchars($tithe['type'] ?? 'Dízimo') ?></div>

                <div class="receipt-meta">
                    <span><strong>Data:</strong> <?= date('d/m/Y', strtotime($tithe['payment_date'])) ?></span>
                    <span><strong>Congregação:</strong> <?= htmlspecialchars($tithe['congregation_name'] ?? 'Igreja Sede') ?></span>
                    <?php if (!empty($siteProfile['phone'])): ?><span><strong>Tel:</strong> <?= htmlspecialchars($siteProfile['phone']) ?></span><?php endif; ?>
                    <?php if (!empty($siteProfile['email'])): ?><span><strong>E-mail:</strong> <?= htmlspecialchars($siteProfile['email']) ?></span><?php endif; ?>
                </div>
                <?php if (!empty($siteProfile['address'])): ?>
                    <div class="receipt-meta" style="border-bottom:none; padding-bottom:0; margin-bottom: 6px;">
                        <span><strong>Endereço:</strong> <?= htmlspecialchars($siteProfile['address']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="receipt-narrative">
                    Recebemos de <strong><?= htmlspecialchars($tithe['member_name'] ?? '') ?></strong>
                    a importância de <strong>R$ <?= number_format($tithe['amount'], 2, ',', '.') ?></strong>
                    referente a <strong><?= htmlspecialchars($tithe['type'] ?? 'Dízimo') ?></strong>.
                </div>

                <div class="receipt-amount">
                    R$ <?= number_format($tithe['amount'], 2, ',', '.') ?>
                </div>

                <div class="receipt-signature">
                    <?php if (!empty($receiptSignatures)): ?>
                        <div class="d-flex justify-content-around flex-wrap" style="margin-top: 18px; gap: 15px;">
                            <?php foreach ($receiptSignatures as $sig): ?>
                                <div class="text-center">
                                    <?php if (!empty($sig['image_path'])): ?>
                                        <img src="/uploads/signatures/<?= htmlspecialchars($sig['image_path']) ?>" style="max-height: 45px; max-width: 140px;" alt="Assinatura"><br>
                                    <?php endif; ?>
                                    <span style="display:inline-block; border-top: 1px solid #000; padding-top: 3px; font-size: 11px; min-width: 160px;">
                                        <?= htmlspecialchars($sig['name'] ?? $sig['role_label']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>___________________________________<br>Tesouraria</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        };
        $renderCopy('viaOriginal', '1ª Via · Original');
        ?>

        <div class="cut-line"><i class="fas fa-scissors"></i> corte aqui</div>

        <?php $renderCopy('viaDuplicata', '2ª Via · Duplicata'); ?>

        <div class="no-print mt-4 d-grid gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                <i class="fas fa-print"></i> Imprimir Recibo (2 Vias)
            </button>

            <button onclick="printSingleVia()" class="btn btn-outline-primary rounded-pill fw-semibold">
                <i class="fas fa-file-pdf"></i> Imprimir/Salvar 1 Via (para WhatsApp)
            </button>

            <?php
                $type = $tithe['type'] ?? 'Dízimo';
                $msg = "Olá " . ($tithe['member_name'] ?? '') . ", recebemos seu/sua " . strtolower($type) . " no valor de R$ " . number_format($tithe['amount'], 2, ',', '.') . " em " . date('d/m/Y', strtotime($tithe['payment_date'])) . ". Deus abençoe!";
                $phone = preg_replace('/[^0-9]/', '', $tithe['phone'] ?? '');
                $wa_link = "https://wa.me/$phone?text=" . urlencode($msg);
            ?>

            <?php if (!empty($phone)): ?>
                <a href="<?= $wa_link ?>" target="_blank" class="btn btn-success rounded-pill fw-semibold">
                    <i class="fab fa-whatsapp"></i> Enviar Comprovante via WhatsApp
                </a>
            <?php else: ?>
                <button class="btn btn-secondary rounded-pill fw-semibold" disabled>Sem telefone cadastrado para WhatsApp</button>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function printSingleVia() {
    document.body.classList.add('hide-second-via');
    window.print();
}
window.addEventListener('afterprint', function () {
    document.body.classList.remove('hide-second-via');
});
</script>

</body>
</html>

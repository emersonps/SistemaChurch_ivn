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
        }
        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
        }
        .receipt-copy {
            border: 2px solid #000;
            padding: 12px;
            margin-bottom: 15px;
            background: #fff;
            page-break-inside: avoid;
        }
        .receipt-copy:last-child {
            margin-bottom: 0;
        }
        .logo-img {
            max-width: 60px;
            max-height: 60px;
            object-fit: contain;
        }
        .receipt-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0;
            text-align: center;
        }
        .receipt-info {
            margin: 10px 0;
            font-size: 13px;
        }
        .receipt-info p {
            margin: 3px 0;
        }
        .receipt-amount {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            padding: 8px;
            background: #f8f8f8;
            border: 1px solid #ddd;
        }
        .receipt-signature {
            margin-top: 20px;
            text-align: center;
        }
        .receipt-signature p {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 11px;
        }
        .copy-label {
            font-size: 9px;
            color: #666;
            text-align: right;
            margin-bottom: 3px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt-container {
                max-width: 100%;
                padding: 0;
            }
            .no-print { display: none !important; }
            .receipt-copy {
                border: 2px solid #000;
                page-break-inside: avoid;
                padding: 8px;
                margin-bottom: 10px;
            }
            .logo-img {
                max-width: 50px;
                max-height: 50px;
            }
            .receipt-header {
                margin-bottom: 8px;
                padding-bottom: 8px;
                gap: 8px;
            }
            .receipt-title {
                font-size: 16px;
                margin: 8px 0;
            }
            .receipt-info {
                margin: 6px 0;
                font-size: 11px;
            }
            .receipt-info p {
                margin: 2px 0;
            }
            .receipt-amount {
                font-size: 14px;
                margin: 8px 0;
                padding: 6px;
            }
            .receipt-signature {
                margin-top: 15px;
            }
        }
        @page {
            margin: 8mm;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="receipt-container">
        <!-- 1ª Via - Original -->
        <div class="receipt-copy">
            <div class="copy-label">1ª VIA - ORIGINAL</div>
            <div class="receipt-header">
                <?php if (!empty($siteProfile['logo_url'])): ?>
                    <img src="<?= htmlspecialchars(getChurchLogoUrl($siteProfile)) ?>" alt="Logo" class="logo-img">
                <?php else: ?>
                    <i class="fas fa-church fa-2x"></i>
                <?php endif; ?>
                <div>
                    <h5 class="mb-0"><?= htmlspecialchars($siteProfile['name'] ?? 'Igreja Vida Nova') ?></h5>
                    <small class="text-muted"><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></small>
                </div>
            </div>
            
            <div class="receipt-info">
                <p><strong>Endereço:</strong> <?= htmlspecialchars($siteProfile['address'] ?? '') ?></p>
                <p><strong>Telefone:</strong> <?= htmlspecialchars($siteProfile['phone'] ?? '') ?></p>
                <p><strong>E-mail:</strong> <?= htmlspecialchars($siteProfile['email'] ?? '') ?></p>
            </div>
            
            <div class="receipt-title">RECIBO</div>
            
            <div class="receipt-info">
                <p><strong>Nº:</strong> <?= $tithe['id'] ?></p>
                <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($tithe['payment_date'])) ?></p>
                <p><strong>Congregação:</strong> <?= htmlspecialchars($tithe['congregation_name'] ?? 'Igreja Sede') ?></p>
            </div>
            
            <div class="receipt-info">
                <p>Recebemos de <strong><?= htmlspecialchars($tithe['member_name'] ?? '') ?></strong></p>
                <p>a importância de <strong>R$ <?= number_format($tithe['amount'], 2, ',', '.') ?></strong></p>
                <p>Referente a <strong><?= htmlspecialchars($tithe['type'] ?? 'Dízimo') ?></strong></p>
            </div>
            
            <div class="receipt-amount">
                R$ <?= number_format($tithe['amount'], 2, ',', '.') ?>
            </div>
            
            <div class="receipt-signature">
                <?php if (!empty($receiptSignatures)): ?>
                    <div class="d-flex justify-content-around flex-wrap" style="margin-top: 20px; gap: 15px;">
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

        <!-- 2ª Via - Duplicata -->
        <div class="receipt-copy">
            <div class="copy-label">2ª VIA - DUPLICATA</div>
            <div class="receipt-header">
                <?php if (!empty($siteProfile['logo_url'])): ?>
                    <img src="<?= htmlspecialchars(getChurchLogoUrl($siteProfile)) ?>" alt="Logo" class="logo-img">
                <?php else: ?>
                    <i class="fas fa-church fa-2x"></i>
                <?php endif; ?>
                <div>
                    <h5 class="mb-0"><?= htmlspecialchars($siteProfile['name'] ?? 'Igreja Vida Nova') ?></h5>
                    <small class="text-muted"><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></small>
                </div>
            </div>
            
            <div class="receipt-info">
                <p><strong>Endereço:</strong> <?= htmlspecialchars($siteProfile['address'] ?? '') ?></p>
                <p><strong>Telefone:</strong> <?= htmlspecialchars($siteProfile['phone'] ?? '') ?></p>
                <p><strong>E-mail:</strong> <?= htmlspecialchars($siteProfile['email'] ?? '') ?></p>
            </div>
            
            <div class="receipt-title">RECIBO</div>
            
            <div class="receipt-info">
                <p><strong>Nº:</strong> <?= $tithe['id'] ?></p>
                <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($tithe['payment_date'])) ?></p>
                <p><strong>Congregação:</strong> <?= htmlspecialchars($tithe['congregation_name'] ?? 'Igreja Sede') ?></p>
            </div>
            
            <div class="receipt-info">
                <p>Recebemos de <strong><?= htmlspecialchars($tithe['member_name'] ?? '') ?></strong></p>
                <p>a importância de <strong>R$ <?= number_format($tithe['amount'], 2, ',', '.') ?></strong></p>
                <p>Referente a <strong><?= htmlspecialchars($tithe['type'] ?? 'Dízimo') ?></strong></p>
            </div>
            
            <div class="receipt-amount">
                R$ <?= number_format($tithe['amount'], 2, ',', '.') ?>
            </div>
            
            <div class="receipt-signature">
                <?php if (!empty($receiptSignatures)): ?>
                    <div class="d-flex justify-content-around flex-wrap" style="margin-top: 20px; gap: 15px;">
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

        <div class="no-print mt-4 d-grid gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="fas fa-print"></i> Imprimir Recibo (2 Vias)
            </button>
            
            <?php
                $type = $tithe['type'] ?? 'Dízimo';
                $msg = "Olá " . ($tithe['member_name'] ?? '') . ", recebemos seu/sua " . strtolower($type) . " no valor de R$ " . number_format($tithe['amount'], 2, ',', '.') . " em " . date('d/m/Y', strtotime($tithe['payment_date'])) . ". Deus abençoe!";
                $phone = preg_replace('/[^0-9]/', '', $tithe['phone'] ?? '');
                $wa_link = "https://wa.me/$phone?text=" . urlencode($msg);
            ?>
            
            <?php if (!empty($phone)): ?>
                <a href="<?= $wa_link ?>" target="_blank" class="btn btn-success">
                    <i class="fab fa-whatsapp"></i> Enviar Comprovante via WhatsApp
                </a>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>Sem telefone cadastrado para WhatsApp</button>
            <?php endif; ?>
            
            <?php
            // Check if user is logged in as admin or member
            $backLink = '/admin/tithes';
            if (isset($_SESSION['member_id'])) {
                $backLink = '/portal/financial';
            }
            ?>
            <a href="<?= $backLink ?>" class="btn btn-link">Voltar</a>
        </div>
    </div>
</div>

</body>
</html>

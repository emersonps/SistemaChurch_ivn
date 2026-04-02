<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de <?= htmlspecialchars($tithe['type'] ?? 'Dízimo') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .receipt-box {
            border: 2px dashed #ccc;
            padding: 20px;
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
        }
        @media print {
            .no-print { display: none; }
            .receipt-box { border: 2px solid #000; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <div class="receipt-box text-center shadow-sm">
        <h3><i class="fas fa-church"></i> Igreja Vida Nova</h3>
        <h5 class="text-muted"><?= htmlspecialchars($tithe['congregation_name'] ?? 'Igreja Sede') ?></h5>
        <hr>
        
        <h2 class="my-4">RECIBO</h2>
        
        <p class="lead">
            Recebemos de <strong><?= htmlspecialchars($tithe['member_name']) ?></strong><br>
            a importância de <strong>R$ <?= number_format($tithe['amount'], 2, ',', '.') ?></strong>
        </p>
        
        <p>
            Referente a <?= htmlspecialchars($tithe['type'] ?? 'Dízimo') ?><br>
            Data: <?= date('d/m/Y', strtotime($tithe['payment_date'])) ?>
        </p>
        
        <div class="mt-5 text-muted" style="font-size: 0.9em;">
            <p>___________________________________<br>Tesouraria</p>
        </div>

        <div class="no-print mt-4 d-grid gap-2">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Imprimir
            </button>
            
            <?php 
                $type = $tithe['type'] ?? 'Dízimo';
                $msg = "Olá " . $tithe['member_name'] . ", recebemos seu/sua " . strtolower($type) . " no valor de R$ " . number_format($tithe['amount'], 2, ',', '.') . " em " . date('d/m/Y', strtotime($tithe['payment_date'])) . ". Deus abençoe!";
                $phone = preg_replace('/[^0-9]/', '', $tithe['phone']);
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

<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
    <h1 class="h2">Carteirinha do Membro</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if (!empty($systemUser)): ?>
            <div class="me-3 d-flex align-items-center">
                <span class="badge bg-info text-dark">
                    <i class="fas fa-user-shield me-1"></i> Usuário do Sistema: <?= ucfirst($systemUser['role']) ?> (<?= $systemUser['username'] ?>)
                </span>
            </div>
        <?php endif; ?>
        <button id="btnDownload" class="btn btn-sm btn-success me-2">
            <i class="fas fa-download"></i> Baixar Imagem
        </button>
        <button id="btnWhatsapp" class="btn btn-sm btn-success me-2">
            <i class="fab fa-whatsapp"></i> Enviar WhatsApp
        </button>
        <button onclick="window.print()" class="btn btn-sm btn-secondary me-2">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <a href="/admin/members" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<style>
@media print {
    body {
        background-color: white !important;
        margin: 0;
        padding: 0;
    }
    .no-print {
        display: none !important;
    }
    .d-flex.flex-column.align-items-center.mt-4.gap-0 {
        margin-top: 0 !important;
        padding: 0 !important;
    }
    #member-card-front, #member-card-back {
        border: 1px dashed #ccc !important;
        box-shadow: none !important;
    }
}
</style>

<div class="d-flex flex-column align-items-center mt-4 gap-0">
    <?php 
    $layoutKey = getSystemSetting('card_layout', 'model_1');
    $siglaColor = getSystemSetting('card_sigla_color', '#0d6efd');
    $layouts = getCardLayouts();
    $currentLayout = $layouts[$layoutKey] ?? $layouts['model_1'];
    $isImageLayout = ($currentLayout['type'] ?? 'color') === 'image';
    
    // Use selected custom color for image layouts, otherwise use the layout's default left color
    $finalSiglaColor = $isImageLayout ? $siglaColor : $currentLayout['left'];
    ?>
    <!-- FRENTE -->
    <div id="member-card-front" class="position-relative bg-white shadow-sm overflow-hidden" 
         style="width: 85.6mm; height: 53.98mm; border: 1px dashed #ccc; border-radius: 0px; font-family: 'Arial', sans-serif;">
        
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: <?= $currentLayout['bg'] ?>; z-index: 0;"></div>
        <?php if (!$isImageLayout): ?>
            <div style="position: absolute; top: 0; left: 0; width: 6px; height: 100%; background-color: <?= $currentLayout['left'] ?>; z-index: 1;"></div>
            <div style="position: absolute; top: 0; right: 0; width: 100%; height: 60px; background: <?= $currentLayout['top'] ?>; z-index: 0;"></div>
        <?php endif; ?>
        
        <!-- Conteúdo -->
        <div class="d-flex h-100 position-relative" style="z-index: 2; padding: 12px 10px 12px 15px; align-items: flex-start;">
            <!-- Lado Esquerdo: Foto e Logo -->
            <div class="d-flex flex-column align-items-center justify-content-start pt-1" style="width: 32%;">
                <div class="mb-2">
                    <img src="/assets/img/logo.png" alt="Logo" style="height: 54px; width: auto; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.1));">
                </div>
                <div class="border border-2 rounded-3 overflow-hidden shadow-sm" style="width: 72px; height: 95px; background-color: #fff; border-color: <?= $currentLayout['left'] ?> !important;">
                    <?php if (!empty($member['photo'])): ?>
                        <img src="/uploads/members/<?= $member['photo'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center h-100">
                            <i class="fas fa-user text-secondary fa-2x"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lado Direito: Informações -->
            <div class="ps-2 d-flex flex-column" style="width: 68%; margin-top: -2px;">
                <div class="mb-2 pe-1">
                    <h6 class="m-0 fw-bold text-uppercase" style="color: <?= $finalSiglaColor ?>; font-size: 16px; letter-spacing: 0.5px;">IVN</h6>
                    <div class="fw-bold text-uppercase" style="font-size: 7px; line-height: 1.1; color: #333; max-width: 170px;">Igreja Vida Nova</div>
                </div>

                <div class="mb-2 pe-1">
                    <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                        <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Nome do Membro</label>
                        <div class="fw-bold" style="font-size: 11px; color: #333; padding-bottom: 1px; max-width: 170px; line-height: 1.1;"><?= mb_convert_case($member['name'], MB_CASE_TITLE, "UTF-8") ?></div>
                    </div>
                </div>

                <div class="d-flex mb-2 pe-1">
                    <div class="me-2">
                        <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                            <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Cargo/Função</label>
                            <div class="fw-bold" style="font-size: 10px; color: #444; max-width: 80px; line-height: 1.1;"><?= mb_convert_case($member['role'] ?? 'Membro', MB_CASE_TITLE, "UTF-8") ?></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                            <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Congregação</label>
                            <div class="fw-bold" style="font-size: 10px; color: #444; max-width: 100px; line-height: 1.1;"><?= mb_convert_case($member['congregation_name'], MB_CASE_TITLE, "UTF-8") ?></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex pe-1">
                    <div class="me-2">
                        <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                            <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Data de Batismo</label>
                            <div class="fw-bold" style="font-size: 9px; color: #555;">
                                <?= !empty($member['baptism_date']) ? date('d/m/Y', strtotime($member['baptism_date'])) : '-' ?>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                            <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">ID Único</label>
                            <div class="fw-bold" style="font-size: 9px; color: #555; font-family: monospace;">
                                <?= $member['unique_id'] ?? str_pad($member['id'], 7, '0', STR_PAD_LEFT) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Faixa inferior -->
        <?php if (!$isImageLayout): ?>
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; background-color: <?= $currentLayout['bottom'] ?>; z-index: 1;"></div>
        <?php endif; ?>
    </div>
    
    <!-- VERSO -->
    <div id="member-card-back" class="position-relative bg-white shadow-sm overflow-hidden" 
         style="width: 85.6mm; height: 53.98mm; border: 1px dashed #ccc; border-top: none !important; border-radius: 0px; font-family: 'Arial', sans-serif; <?= $isImageLayout ? 'background-color: #f8f9fa !important;' : '' ?>">
        
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: <?= $isImageLayout ? '#f8f9fa' : '#fff' ?>; z-index: 0;"></div>
        
        <!-- Marca d'água no verso -->
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.05; z-index: 0;">
            <img src="/assets/img/logo.png" style="width: 150px; height: auto;">
        </div>

        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 20px; background-color: <?= $currentLayout['back_top'] ?>; z-index: 1; display: flex; align-items: center; justify-content: center; border-bottom: <?= $isImageLayout ? '1px solid #dee2e6' : 'none' ?>;">
            <span style="color: <?= $currentLayout['text_top'] ?>; font-size: 8px; font-weight: bold; letter-spacing: 1px;">CREDENCIAL DE MEMBRO - USO PESSOAL E INTRANSFERÍVEL</span>
        </div>
        
        <div class="d-flex h-100 position-relative flex-column" style="z-index: 2; padding: 25px 15px 10px 15px;">
            <div class="d-flex w-100">
                <div style="width: 65%;">
                    <div class="mb-1">
                        <label class="d-block text-muted" style="font-size: 6px; margin-bottom: 0;">Identidade (RG) / CPF</label>
                        <div class="fw-bold" style="font-size: 9px; color: #333;">
                            <?= !empty($member['rg']) ? $member['rg'] : '-' ?> / <?= !empty($member['cpf']) ? $member['cpf'] : '-' ?>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="d-block text-muted" style="font-size: 6px; margin-bottom: 0;">Data de Nascimento</label>
                        <div class="fw-bold" style="font-size: 9px; color: #333;">
                            <?= !empty($member['birth_date']) ? date('d/m/Y', strtotime($member['birth_date'])) : '-' ?>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="d-block text-muted" style="font-size: 6px; margin-bottom: 0;">Filiação</label>
                        <div class="fw-bold text-truncate" style="font-size: 8px; color: #333; max-width: 160px;">
                            <?= !empty($member['father_name']) ? mb_convert_case($member['father_name'], MB_CASE_TITLE, "UTF-8") : '-' ?><br>
                            <?= !empty($member['mother_name']) ? mb_convert_case($member['mother_name'], MB_CASE_TITLE, "UTF-8") : '-' ?>
                        </div>
                    </div>
                    <div style="font-size: 6px; color: #666; margin-top: 60px; text-align: justify; line-height: 1.1;">
                        Reconhecemos o portador desta como membro em plena <br>comunhão com nossa igreja. Solicitamos às autoridades<br> civis e militares que lhe garantam o livre trânsito.
                    </div>
                </div>
                
                <div class="d-flex flex-column align-items-center justify-content-center" style="width: 35%;">
                    <!-- Container do QR Code -->
                    <div id="qrcode" class="border bg-white shadow-sm mb-1 d-flex align-items-center justify-content-center" style="width: 96px; height: 96px; padding: 2px;"></div>
                    <div style="font-size: 6px; color: #666; text-align: center; margin-top: 1px;">Validação Digital</div>
                    
                    <!-- Assinatura -->
                    <div class="mt-2 w-100 px-1 text-center">
                        <?php if (!empty($signature) && !empty($signature['image_path'])): ?>
                            <div class="mb-1 d-flex justify-content-center">
                                <img src="/uploads/signatures/<?= $signature['image_path'] ?>" style="max-height: 25px; max-width: 100%;">
                            </div>
                        <?php else: ?>
                            <div style="height: 25px;"></div>
                        <?php endif; ?>
                        
                        <div style="border-top: 1px solid #000; text-align: center; padding-top: 1px;">
                            <div style="font-size: 5px; font-weight: bold; color: #000; text-transform: uppercase;">
                                <?= !empty($signature) ? $signature['name'] : 'Pr. Presidente' ?>
                            </div>
                            <?php if (!empty($signature)): ?>
                            <div style="font-size: 4px; color: #444; text-transform: uppercase;">
                                <?= $signature['role_label'] ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; background-color: <?= $isImageLayout ? '#212529' : '#ffc107' ?>; z-index: 1;"></div>
    </div>
</div>

<div class="text-center mt-3 text-muted no-print">
    <small>A carteirinha é gerada no tamanho padrão CR-80 (85.6mm x 53.98mm).</small>
</div>

<!-- Include QRCode.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Generate QR Code with Member's Unique ID or Database ID for future system scanning
document.addEventListener('DOMContentLoaded', function() {
    const memberIdForQR = '<?= $member['unique_id'] ?? $member['id'] ?>';
    // Em um cenário real de aplicativo, esse QR code poderia apontar para um link do sistema
    // Ex: https://seu-sistema.com/validar?id=ABC1234
    const qrData = 'IEADSENA_MEMBER:' + memberIdForQR;
    
    new QRCode(document.getElementById("qrcode"), {
        text: qrData,
        width: 90,
        height: 90,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
});

document.getElementById('btnDownload').addEventListener('click', function() {
    generateCardImage(function(canvas) {
        var link = document.createElement('a');
        link.download = 'carteirinha-<?= $member['id'] ?>.png';
        link.href = canvas.toDataURL("image/png");
        link.click();
    });
});

document.getElementById('btnWhatsapp').addEventListener('click', function() {
    var phone = prompt("Digite o número do WhatsApp (com DDD, apenas números):", "<?= preg_replace('/[^0-9]/', '', $member['phone'] ?? '') ?>");
    if (phone) {
        generateCardImage(function(canvas) {
            // Force download first
            var link = document.createElement('a');
            link.download = 'carteirinha-<?= $member['id'] ?>.png';
            link.href = canvas.toDataURL("image/png");
            link.click();
            
            // Show instruction modal or alert
            setTimeout(function() {
                var message = "Olá, segue minha carteirinha digital de membro.";
                var whatsappUrl = `https://web.whatsapp.com/send?phone=55${phone}&text=${encodeURIComponent(message)}`;
                
                if (confirm("A imagem da carteirinha foi baixada para seu dispositivo.\n\nClique em OK para abrir o WhatsApp Web e anexar a imagem manualmente.")) {
                    window.open(whatsappUrl, '_blank');
                }
            }, 500);
        });
    }
});

function generateCardImage(callback) {
    // Para capturar frente e verso juntos, vamos capturar o container pai que tem a classe d-flex flex-column
    var element = document.querySelector(".d-flex.flex-column.align-items-center.mt-4.gap-4");
    
    // Temporariamente ajustar o estilo para garantir que a imagem gerada fique boa
    const originalGap = element.style.gap;
    element.style.gap = '20px'; // Espaçamento entre frente e verso na imagem gerada
    element.style.backgroundColor = '#ffffff'; // Fundo branco na imagem final
    element.style.padding = '20px';
    element.style.borderRadius = '10px';

    html2canvas(element, {
        scale: 4, // Higher scale for better quality
        useCORS: true,
        backgroundColor: '#ffffff'
    }).then(function(canvas) {
        // Restaurar estilos originais
        element.style.gap = originalGap;
        element.style.backgroundColor = '';
        element.style.padding = '';
        element.style.borderRadius = '';
        
        callback(canvas);
    });
}
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
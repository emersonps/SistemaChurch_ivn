<?php include __DIR__ . '/layout/header.php'; ?>

<style>
    .portal-doc-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1.25rem;
        border-top: 1px solid rgba(0,0,0,0.05);
    }
    .portal-doc-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(108,117,125,.10);
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
</style>

<div class="portal-page-title">Meus Documentos</div>
<p class="text-muted mb-3">Arquivos pessoais anexados à sua ficha.</p>

<div class="portal-card">
    <?php if (empty($docs)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-folder-open fa-2x mb-3 opacity-50"></i>
            <p class="mb-0">Você ainda não possui documentos anexados.</p>
        </div>
    <?php else: ?>
        <?php foreach ($docs as $d):
            $type = strtolower($d['type'] ?? '');
            $label = $type;
            if ($type === 'transfer_letter') $label = 'Carta de Transferência';
            elseif ($type === 'rg') $label = 'RG';
            elseif ($type === 'cpf') $label = 'CPF';
            elseif ($type === 'other') $label = 'Outro';
        ?>
            <div class="portal-doc-row">
                <div class="d-flex align-items-center gap-3">
                    <span class="portal-doc-icon"><i class="fas fa-file-lines"></i></span>
                    <div>
                        <div class="fw-bold small"><?= htmlspecialchars($d['title']) ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($label) ?> · <?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></div>
                    </div>
                </div>
                <a class="btn btn-sm btn-outline-primary rounded-pill" target="_blank" href="/portal/documents/open/<?= $d['id'] ?>">
                    <i class="fas fa-download me-1"></i> Abrir
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

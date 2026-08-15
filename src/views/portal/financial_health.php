<?php include __DIR__ . '/layout/header.php'; ?>

<?php
function financialCategoryMeta($label) {
    $norm = mb_strtolower(trim((string)$label), 'UTF-8');
    $rules = [
        ['keywords' => ['aluguel'], 'icon' => 'fa-house', 'tagline' => 'Nossa casa de oração'],
        ['keywords' => ['luz', 'energia'], 'icon' => 'fa-bolt', 'tagline' => 'Luz e conforto'],
        ['keywords' => ['agua', 'água'], 'icon' => 'fa-droplet', 'tagline' => 'Água e limpeza'],
        ['keywords' => ['manutencao', 'manutenção'], 'icon' => 'fa-screwdriver-wrench', 'tagline' => 'Cuidando do templo'],
        ['keywords' => ['ebd', 'escola biblica', 'escola bíblica'], 'icon' => 'fa-book-open', 'tagline' => 'Escola Bíblica'],
        ['keywords' => ['missao', 'missão', 'missoes', 'missões'], 'icon' => 'fa-hand-holding-heart', 'tagline' => 'Alcançando pessoas'],
        ['keywords' => ['contas fixas', 'sistema', 'internet', 'assinatura'], 'icon' => 'fa-file-invoice', 'tagline' => 'Compromissos fixos'],
    ];
    foreach ($rules as $rule) {
        foreach ($rule['keywords'] as $kw) {
            if (mb_strpos($norm, $kw) !== false) {
                return ['icon' => $rule['icon'], 'tagline' => $rule['tagline']];
            }
        }
    }
    return ['icon' => 'fa-circle-dot', 'tagline' => 'Cuidados diversos'];
}

$fhTierMeta = [
    'positive'  => ['label' => 'Positivo', 'pill' => 'portal-pill-green', 'title' => 'Nossa casa está em dia'],
    'stable'    => ['label' => 'Estável',  'pill' => 'portal-pill-warning', 'title' => 'Nossa casa está equilibrada'],
    'attention' => ['label' => 'Atenção',  'pill' => 'portal-pill-red', 'title' => 'Nossa casa precisa de atenção'],
    'none'      => ['label' => '', 'pill' => '', 'title' => 'Ainda sem lançamentos este mês'],
];
$fhTier = $fhTierMeta[$healthTier];

if ($healthTier === 'none') {
    $fhMessage = null;
} elseif ($totalExpense <= 0) {
    $fhMessage = 'Nenhuma despesa lançada em ' . $monthLabel . ' até agora. Suas contribuições seguem fortalecendo a igreja.';
} elseif ($healthPct >= 100) {
    $fhMessage = 'Com as contribuições deste mês, todas as despesas registradas foram cobertas.';
} else {
    $fhMessage = 'As contribuições deste mês cobriram ' . $healthPct . '% das despesas registradas até agora.';
}

// Card view: top 5 categories, remainder rolled up into "Outros".
$fhTopCategories = array_slice($categories, 0, 5);
$fhRestCategories = array_slice($categories, 5);
if (!empty($fhRestCategories)) {
    $restAmount = array_sum(array_column($fhRestCategories, 'amount'));
    $merged = false;
    foreach ($fhTopCategories as &$tc) {
        if (mb_strtolower(trim($tc['label']), 'UTF-8') === 'outros') {
            $tc['amount'] += $restAmount;
            $tc['pct'] = $totalExpense > 0 ? (int)round(($tc['amount'] / $totalExpense) * 100) : 0;
            $merged = true;
            break;
        }
    }
    unset($tc);
    if (!$merged) {
        $fhTopCategories[] = [
            'label' => 'Outros',
            'amount' => $restAmount,
            'pct' => $totalExpense > 0 ? (int)round(($restAmount / $totalExpense) * 100) : 0,
        ];
    }
}
?>

<style>
    .fh-hero-title { font-weight: 800; font-size: 1.1rem; color: #1a1a1a; }
    .fh-shield {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .fh-shield-positive { background: rgba(25,135,84,.10); color: #198754; }
    .fh-shield-stable { background: rgba(255,193,7,.16); color: #997404; }
    .fh-shield-attention { background: rgba(220,53,69,.10); color: #dc3545; }
    .fh-shield-none { background: #eef0f2; color: #868e96; }
    .fh-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 700;
        color: #868e96;
    }
    .fh-score { font-size: 2.1rem; font-weight: 800; color: #1a1a1a; line-height: 1; }
    .fh-progress { background: #eef0f2; border-radius: 999px; height: 8px; overflow: hidden; }
    .fh-progress-bar { height: 100%; border-radius: 999px; background: #1a1a1a; transition: width .3s ease; }
    .fh-bar-positive { background: #198754; }
    .fh-bar-stable { background: #ffc107; }
    .fh-bar-attention { background: #dc3545; }
    .fh-message {
        background: #f8f9fa;
        border-radius: 12px;
        padding: .75rem 1rem;
        font-size: .84rem;
        color: #495057;
    }
    .fh-footnote { font-size: .72rem; color: #adb5bd; }
    .fh-cat-row { padding: .9rem 0; border-bottom: 1px solid rgba(0,0,0,.05); }
    .fh-cat-row:last-child { border-bottom: none; }
    .fh-cat-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #f4f5f7;
        color: #495057;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: .85rem;
    }
    .fh-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #198754;
        margin-right: .35rem;
    }
    .fh-modal-content { border-radius: 20px; border: none; overflow: hidden; }
    .fh-modal-header { border-bottom: 1px solid rgba(0,0,0,.06); align-items: flex-start; }
    .fh-info-box {
        background: #f8f9fa;
        border-radius: 12px;
        padding: .85rem 1rem;
    }
    .fh-modal-footer { border-top: 1px solid rgba(0,0,0,.06); }
</style>

<div class="portal-page-title">Saúde Financeira</div>
<p class="text-muted mb-3">Como cuidamos da nossa casa, sem expor valores.</p>

<div class="portal-card fh-hero mb-3 p-3">
    <div class="d-flex justify-content-between align-items-start gap-2">
        <div>
            <div class="fh-hero-title">🙏 <?= htmlspecialchars($fhTier['title']) ?></div>
            <div class="text-muted small">Consulta direta ao módulo da secretaria</div>
        </div>
        <span class="fh-shield fh-shield-<?= $healthTier ?>"><i class="fas fa-shield-heart"></i></span>
    </div>

    <?php if ($healthPct !== null): ?>
        <div class="mt-3">
            <div class="fh-label">Saúde financeira</div>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <div class="fh-score"><?= $healthPct ?>%</div>
                <span class="portal-pill <?= $fhTier['pill'] ?>"><?= htmlspecialchars($fhTier['label']) ?></span>
            </div>
            <div class="fh-progress mt-2">
                <div class="fh-progress-bar fh-bar-<?= $healthTier ?>" style="width: <?= $healthPct ?>%;"></div>
            </div>
        </div>
        <div class="fh-message mt-3"><?= htmlspecialchars($fhMessage) ?></div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <span class="portal-pill portal-pill-gray"><i class="fas fa-receipt me-1"></i><?= $expenseCount ?> <?= $expenseCount === 1 ? 'despesa lançada' : 'despesas lançadas' ?></span>
            <span class="portal-pill portal-pill-gray"><i class="fas fa-layer-group me-1"></i><?= $categoryCount ?> <?= $categoryCount === 1 ? 'categoria' : 'categorias' ?></span>
        </div>
    <?php else: ?>
        <div class="text-center text-muted py-3 mt-2">
            <i class="fas fa-circle-info fa-lg mb-2"></i>
            <p class="mb-0">Ainda não há lançamentos financeiros em <?= htmlspecialchars($monthLabel) ?>.</p>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-3 fh-footnote">
        <span><i class="fas fa-circle text-success" style="font-size:.4rem;"></i> Dados consultados automaticamente</span>
        <span>sem exibir R$</span>
    </div>
</div>

<div class="portal-card mb-2">
    <div class="p-3 pb-0">
        <div class="fw-bold">Como cuidamos da nossa casa</div>
        <div class="text-muted small">Sem valores em R$ — só % dos gastos e status real</div>
    </div>
    <div class="px-3">
        <?php if (empty($fhTopCategories)): ?>
            <p class="text-muted text-center py-4 mb-0">Nenhuma despesa lançada em <?= htmlspecialchars($monthLabel) ?> até agora.</p>
        <?php else: ?>
            <?php foreach ($fhTopCategories as $cat): $meta = financialCategoryMeta($cat['label']); ?>
                <div class="fh-cat-row">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex gap-2 align-items-start">
                            <span class="fh-cat-icon"><i class="fas <?= $meta['icon'] ?>"></i></span>
                            <div>
                                <div class="fw-bold small"><?= htmlspecialchars($cat['label']) ?></div>
                                <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($meta['tagline']) ?></div>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="fh-dot"></span>
                            <span class="portal-pill portal-pill-green">Pago</span>
                        </div>
                    </div>
                    <div class="fh-progress mt-2">
                        <div class="fh-progress-bar" style="width: <?= $cat['pct'] ?>%;"></div>
                    </div>
                    <div class="text-end text-muted mt-1" style="font-size:.7rem;"><?= $cat['pct'] ?>% do mês</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="p-3">
        <?php if (!empty($categories)): ?>
            <button type="button" class="btn btn-dark rounded-pill fw-semibold w-100" data-bs-toggle="modal" data-bs-target="#fhDetailsModal">
                Ver detalhes dos custos <i class="fas fa-arrow-up-right-from-square ms-1"></i>
            </button>
        <?php endif; ?>
        <div class="text-center text-muted mt-2" style="font-size:.72rem;">Transparente sem fofoca • Sem R$ para todos</div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <span class="text-muted small">Última atualização: dados da secretaria em <?= htmlspecialchars($lastUpdated) ?></span>
    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="window.location.reload();"><i class="fas fa-rotate me-1"></i> Atualizar</button>
</div>

<!-- Detalhes dos custos Modal -->
<div class="modal fade" id="fhDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content fh-modal-content">
            <div class="modal-header fh-modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Detalhes dos custos</h5>
                    <div class="text-muted small">Transparência inteligente • consulta ao banco</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="fh-info-box mb-3">
                    <div class="fw-bold small mb-1"><?= htmlspecialchars(mb_strtoupper($monthLabel, 'UTF-8')) ?></div>
                    <div class="text-muted small">Todos os compromissos foram mapeados diretamente do módulo secretaria. Sem exibir valores, só proporção e status real — evita fofoca, mantém confiança.</div>
                </div>
                <?php if (empty($categories)): ?>
                    <p class="text-muted text-center py-3 mb-0">Nenhuma despesa lançada em <?= htmlspecialchars($monthLabel) ?> até agora.</p>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(0,0,0,.05) !important;">
                            <div>
                                <span class="fh-dot"></span>
                                <strong class="small"><?= htmlspecialchars($cat['label']) ?></strong>
                                <span class="text-muted small ms-1"><?= $cat['pct'] ?>% do mês</span>
                            </div>
                            <span class="portal-pill portal-pill-green">Pago</span>
                        </div>
                    <?php endforeach; ?>
                    <div class="fh-progress mt-3">
                        <div class="fh-progress-bar" style="width: 100%;"></div>
                    </div>
                    <div class="text-center text-muted small mt-1">100% das saídas distribuídas — sem R$</div>
                <?php endif; ?>
            </div>
            <div class="modal-footer fh-modal-footer justify-content-center">
                <span class="text-muted small text-center">🙏 Sem R$ na visão pública evita fofoca e mantém o foco no cuidado da casa</span>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

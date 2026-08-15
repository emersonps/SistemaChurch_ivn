<?php include __DIR__ . '/layout/header.php'; ?>

<?php
function portalStudyTypeMeta($type) {
    $map = [
        'Estudo' => ['label' => 'Estudos', 'class' => 'pst-gold', 'icon' => 'fa-book'],
        'Esboço' => ['label' => 'Esboços', 'class' => 'pst-sage', 'icon' => 'fa-file-lines'],
        'EBD'    => ['label' => 'EBD', 'class' => 'pst-coral', 'icon' => 'fa-graduation-cap'],
        'Livro'  => ['label' => 'Livros', 'class' => 'pst-blue', 'icon' => 'fa-book-open'],
    ];
    return $map[$type] ?? $map['Estudo'];
}

function portalFormatFileSize($bytes) {
    if ($bytes === null || $bytes <= 0) return null;
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1024 * 1024) return round($bytes / 1024) . ' KB';
    return round($bytes / (1024 * 1024), 1) . ' MB';
}
?>

<style>
    .pst-title {
        font-weight: 800;
        font-size: 1.7rem;
        color: #1a1a1a;
        letter-spacing: -.01em;
        line-height: 1.15;
    }
    .pst-title-accent { color: var(--portal-primary); }
    .pst-subtitle { font-size: .85rem; max-width: 540px; }

    .pst-tab {
        border: 1px solid rgba(0,0,0,.08);
        background: #fff;
        color: #495057;
        font-weight: 700;
        font-size: .8rem;
        padding: .45rem 1.05rem;
        border-radius: 999px;
        cursor: pointer;
        transition: all .15s ease;
    }
    .pst-tab:hover { background: #f8f9fa; }
    .pst-tab.active { background: #1a1a1a; color: #fff; border-color: #1a1a1a; }

    .pst-search-card { border-radius: 14px; }
    .pst-search-card input:focus { box-shadow: none; }

    .pst-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(17,17,17,.04);
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .pst-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(17,17,17,.08); }

    .pst-thumb {
        position: relative;
        padding: 1.4rem 1.1rem;
        min-height: 170px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        color: #2b2b2b;
        overflow: hidden;
    }
    .pst-thumb-badge {
        position: absolute;
        top: .85rem;
        left: .9rem;
        background: rgba(255,255,255,.6);
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .05em;
        padding: .22rem .6rem;
        border-radius: 999px;
    }
    .pst-thumb-sub {
        position: absolute;
        top: .85rem;
        right: .9rem;
        background: rgba(255,255,255,.6);
        font-size: .6rem;
        font-weight: 800;
        letter-spacing: .04em;
        padding: .22rem .6rem;
        border-radius: 999px;
        max-width: 48%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .pst-thumb-icon { position: absolute; right: 1rem; bottom: 1rem; font-size: 2.4rem; opacity: .18; }
    .pst-thumb-title {
        font-family: Georgia, 'Times New Roman', serif;
        font-weight: 700;
        font-size: 1.25rem;
        line-height: 1.18;
        position: relative;
        z-index: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .pst-gold { background: linear-gradient(135deg, #f7d79b, #f0a85c); }
    .pst-sage { background: linear-gradient(135deg, #c9e4c5, #8fbf8a); }
    .pst-coral { background: linear-gradient(135deg, #fbd0c4, #f0987e); }
    .pst-blue { background: linear-gradient(135deg, #cfe0f7, #93b8e6); }

    .pst-body { padding: 1rem 1.1rem 1.1rem; display: flex; flex-direction: column; flex-grow: 1; }
    .date-pill-portal {
        display: inline-flex;
        align-items: center;
        font-size: .68rem;
        font-weight: 700;
        color: #868e96;
        background: #f4f5f7;
        padding: .2rem .6rem;
        border-radius: 999px;
    }
    .pst-desc { font-size: .8rem; color: #6c757d; margin: .55rem 0; }
    .pst-tag {
        font-size: .68rem;
        font-weight: 700;
        color: var(--portal-primary);
        background: rgba(179,0,0,.07);
        padding: .16rem .55rem;
        border-radius: 999px;
    }
    .pst-open-btn {
        margin-top: auto;
        background: var(--portal-primary);
        color: #fff;
        border: none;
        border-radius: 999px;
        font-weight: 700;
        font-size: .82rem;
        padding: .6rem 1rem;
    }
    .pst-open-btn:hover { background: var(--portal-primary-dark); color: #fff; }
</style>

<div class="mb-3">
    <span class="portal-pill portal-pill-red"><i class="fas fa-circle me-1" style="font-size:.4rem; vertical-align: middle;"></i>Materiais em PDF</span>
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mt-2">
        <div>
            <h1 class="pst-title mb-1">Estudos <span class="pst-title-accent">e Esboços</span></h1>
            <p class="text-muted mb-0 pst-subtitle">Materiais em PDF disponíveis para você. Capas visuais, leitura rápida e acesso imediato.</p>
        </div>
        <span class="portal-pill portal-pill-gray" id="pstCountPill"><?= count($studies) ?> <?= count($studies) === 1 ? 'material' : 'materiais' ?></span>
    </div>
</div>

<?php if (!empty($studies)): ?>
<div class="d-flex flex-wrap gap-2 mb-3" id="pstFilterTabs">
    <button type="button" class="pst-tab active" data-type="all">Todos</button>
    <button type="button" class="pst-tab" data-type="Estudo">Estudos</button>
    <button type="button" class="pst-tab" data-type="Esboço">Esboços</button>
    <button type="button" class="pst-tab" data-type="EBD">EBD</button>
    <button type="button" class="pst-tab" data-type="Livro">Livros</button>
</div>

<div class="portal-card pst-search-card mb-4">
    <div class="p-2 d-flex align-items-center gap-2">
        <i class="fas fa-search text-muted ms-2"></i>
        <input type="search" id="pstSearchInput" class="form-control border-0 shadow-none" placeholder="Buscar por título, tema ou tag...">
    </div>
</div>
<?php endif; ?>

<div class="row g-3" id="pstGrid">
    <?php if (empty($studies)): ?>
        <div class="col-12">
            <div class="portal-card text-center py-5 text-muted">
                <i class="fas fa-book-open fa-2x mb-3 opacity-50"></i>
                <p class="mb-0">Nenhum estudo disponível no momento.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($studies as $s): ?>
            <?php
            $typeMeta = portalStudyTypeMeta($s['material_type'] ?? null);
            $studyFilePath = __DIR__ . '/../../../public/uploads/studies/' . ($s['file_path'] ?? '');
            $fileSize = (!empty($s['file_path']) && is_file($studyFilePath)) ? portalFormatFileSize(filesize($studyFilePath)) : null;
            $description = trim((string)($s['description'] ?? ''));

            $titleWords = preg_split('/\s+/', trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', (string)$s['title'])));
            $tagWords = [];
            foreach ($titleWords as $w) {
                if (mb_strlen($w, 'UTF-8') >= 4 && count($tagWords) < 3) $tagWords[] = $w;
            }

            $searchBlob = mb_strtolower(($s['title'] ?? '') . ' ' . $description . ' ' . ($s['congregation_name'] ?? '') . ' ' . $typeMeta['label'], 'UTF-8');
            ?>
            <div class="col-md-6 col-lg-4 pst-item" data-type="<?= htmlspecialchars($s['material_type'] ?? 'Estudo') ?>" data-search="<?= htmlspecialchars($searchBlob) ?>">
                <div class="pst-card">
                    <div class="pst-thumb <?= $typeMeta['class'] ?>">
                        <span class="pst-thumb-badge"><?= htmlspecialchars(mb_strtoupper($typeMeta['label'], 'UTF-8')) ?></span>
                        <span class="pst-thumb-sub"><?= htmlspecialchars($s['congregation_name'] ?: 'Geral') ?></span>
                        <i class="fas <?= $typeMeta['icon'] ?> pst-thumb-icon"></i>
                        <div class="pst-thumb-title"><?= htmlspecialchars($s['title']) ?></div>
                    </div>
                    <div class="pst-body">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="date-pill-portal"><i class="far fa-calendar-alt me-1"></i><?= date('d/m/Y', strtotime($s['created_at'])) ?></span>
                            <?php if ($fileSize): ?>
                                <span class="date-pill-portal"><i class="far fa-file-pdf me-1"></i><?= htmlspecialchars($fileSize) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($description !== ''): ?>
                            <p class="pst-desc"><?= htmlspecialchars(mb_strimwidth($description, 0, 110, '...', 'UTF-8')) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($tagWords)): ?>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <?php foreach ($tagWords as $tw): ?>
                                    <span class="pst-tag">#<?= htmlspecialchars($tw) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <a href="/portal/studies/view/<?= $s['id'] ?>" target="_blank" class="btn pst-open-btn">
                            <i class="fas fa-file-pdf me-2"></i> Abrir PDF <i class="fas fa-arrow-up-right-from-square ms-2 small"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="portal-card text-center py-5 text-muted d-none mt-3" id="pstNoResults">
    <i class="fas fa-search fa-2x mb-3 opacity-50"></i>
    <p class="mb-0">Nenhum material encontrado para esse filtro/busca.</p>
</div>

<?php if (!empty($studies)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabs = Array.prototype.slice.call(document.querySelectorAll('#pstFilterTabs .pst-tab'));
    var searchInput = document.getElementById('pstSearchInput');
    var items = Array.prototype.slice.call(document.querySelectorAll('.pst-item'));
    var countPill = document.getElementById('pstCountPill');
    var noResults = document.getElementById('pstNoResults');
    var activeType = 'all';

    var DIACRITICS_RE = new RegExp('[\\u0300-\\u036f]', 'g');
    function normalize(str) {
        return String(str || '').toLowerCase().normalize('NFD').replace(DIACRITICS_RE, '');
    }

    function applyFilters() {
        var q = normalize(searchInput.value.trim());
        var visible = 0;
        items.forEach(function (item) {
            var matchesType = activeType === 'all' || item.getAttribute('data-type') === activeType;
            var matchesSearch = q === '' || normalize(item.getAttribute('data-search')).indexOf(q) !== -1;
            var show = matchesType && matchesSearch;
            item.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        countPill.textContent = visible + (visible === 1 ? ' material' : ' materiais');
        noResults.classList.toggle('d-none', visible !== 0);
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            activeType = tab.getAttribute('data-type');
            applyFilters();
        });
    });

    searchInput.addEventListener('input', applyFilters);
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>

<?php
// src/views/admin/studies/_mobile_list.php
// Mobile/tablet (<992px) hero-carousel presentation of the same $studies data already
// loaded by StudyController::index(). Desktop keeps the classic table untouched
// (hidden via d-none d-lg-block there). Reuses studyTypeMeta()/$isAdmin/$userId/
// $canManagePermission already defined earlier in index.php.

function stmCoverUrl($filePath) {
    $baseName = pathinfo((string)$filePath, PATHINFO_FILENAME);
    if ($baseName === '') return null;
    $coverDir = __DIR__ . '/../../../../public/uploads/studies/covers/';
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $candidate = $coverDir . $baseName . '.' . $ext;
        if (is_file($candidate)) return '/uploads/studies/covers/' . $baseName . '.' . $ext;
    }
    return null;
}

$stmTypesPresent = [];
foreach ($studies as $s) {
    $meta = studyTypeMeta($s['material_type'] ?? null);
    $stmTypesPresent[$meta['label']] = $meta['class'];
}

$mobilePageCategory = 'Estudos';
$mobilePageTitle = null;
include __DIR__ . '/../../layout/mobile_page_header.php';
?>
<style>
    .stm-wrap { padding-bottom: 90px; }
    .stm-fab { position: fixed; right: 1rem; bottom: calc(1rem + env(safe-area-inset-bottom)); width: 54px; height: 54px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 6px 16px rgba(37,99,235,.3); z-index: 1030; text-decoration: none; }
    .stm-search { position: relative; margin-bottom: .9rem; }
    .stm-search i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .stm-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #fff; border-radius: 12px; padding: .55rem .8rem .55rem 2.3rem; font-size: .85rem; }
    .stm-search input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }

    .stm-chip-row { display: flex; gap: .5rem; overflow-x: auto; padding-bottom: .2rem; margin-bottom: 1rem; scrollbar-width: none; }
    .stm-chip-row::-webkit-scrollbar { display: none; }
    .stm-chip { flex: 0 0 auto; border: 1px solid rgba(17,24,39,.1); background: #fff; color: #495057; border-radius: 999px; padding: .42rem .95rem; font-size: .8rem; font-weight: 700; white-space: nowrap; }
    .stm-chip.active { background: #16213e; border-color: #16213e; color: #fff; }

    .stm-carousel { display: flex; gap: .7rem; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; scrollbar-width: none; margin: 0 -1rem; padding: 0 1rem .3rem; }
    .stm-carousel::-webkit-scrollbar { display: none; }
    .stm-hero { flex: 0 0 calc(100% - 0px); scroll-snap-align: center; border-radius: 20px; overflow: hidden; position: relative; min-height: 220px; display: flex; flex-direction: column; justify-content: flex-end; padding: 1.1rem; color: #fff; background: linear-gradient(160deg, #1e3a5f 0%, #16213e 60%, #0f172a 100%); background-size: cover; background-position: center; }
    .stm-hero.stm-hidden { display: none; }
    .stm-hero::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,.15) 0%, rgba(15,23,42,.85) 100%); }
    .stm-hero-top { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: flex-start; position: absolute; top: 1rem; left: 1rem; right: 1rem; }
    .stm-hero-type { background: rgba(255,255,255,.15); backdrop-filter: blur(4px); color: #fff; font-size: .68rem; font-weight: 800; padding: .3rem .7rem; border-radius: 999px; display: inline-flex; align-items: center; gap: .35rem; }
    .stm-hero-icon { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.15); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; font-size: .9rem; }
    .stm-hero-body { position: relative; z-index: 1; }
    .stm-hero-title { font-size: 1.05rem; font-weight: 800; line-height: 1.25; margin-bottom: .35rem; }
    .stm-hero-desc { font-size: .78rem; color: rgba(255,255,255,.8); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: .4rem; }
    .stm-hero-tags { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .8rem; }
    .stm-hero-tag { font-size: .66rem; font-weight: 700; padding: .2rem .6rem; border-radius: 999px; background: rgba(255,255,255,.12); }
    .stm-hero-actions { display: flex; gap: .6rem; }
    .stm-hero-btn { flex: 1 1 0; text-align: center; border-radius: 999px; padding: .55rem; font-size: .8rem; font-weight: 700; text-decoration: none; }
    .stm-hero-btn.is-primary { background: #fff; color: #16213e; }
    .stm-hero-btn.is-outline { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.35); color: #fff; }

    .stm-dots { display: flex; justify-content: center; gap: .35rem; margin: .8rem 0 .6rem; }
    .stm-dot { width: 6px; height: 6px; border-radius: 50%; background: rgba(17,24,39,.15); }
    .stm-dot.active { background: #16213e; width: 16px; border-radius: 4px; }


    .stm-swipe { position: relative; overflow: hidden; border-radius: 14px; margin-bottom: .5rem; }
    .stm-swipe.stm-hidden { display: none; }
    .stm-swipe-actions { position: absolute; top: 0; right: 0; bottom: 0; display: flex; align-items: stretch; }
    .stm-swipe-action { width: 56px; border: none; background: #dc3545; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .stm-list-item { position: relative; z-index: 1; display: flex; align-items: center; gap: .65rem; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 14px; padding: .65rem .8rem; text-decoration: none; transition: transform .18s ease; touch-action: pan-y; }
    .stm-list-icon { flex: 0 0 auto; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .95rem; object-fit: cover; }
    .stm-list-id { min-width: 0; flex: 1 1 auto; }
    .stm-list-title { font-weight: 700; font-size: .83rem; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .stm-list-meta { font-size: .7rem; color: #8b93a7; }
    .stm-list-chev { flex: 0 0 auto; color: #ced4da; }

    .type-estudo .stm-list-icon, .stm-list-icon.type-estudo { background: rgba(179,0,0,.1); color: #b30000; }
    .stm-list-icon.type-esboco { background: rgba(255,153,0,.14); color: #b36b00; }
    .stm-list-icon.type-ebd { background: rgba(25,135,84,.12); color: #198754; }
    .stm-list-icon.type-livro { background: rgba(111,66,193,.12); color: #6f42c1; }

    .stm-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }
</style>

<div class="stm-wrap d-lg-none">
    <div class="stm-search">
        <i class="fas fa-search"></i>
        <input type="text" id="stmSearchInput" placeholder="Buscar estudos, esboços...">
    </div>

    <?php if (!empty($stmTypesPresent)): ?>
        <div class="stm-chip-row" id="stmChips">
            <button type="button" class="stm-chip active" data-type="">Todos</button>
            <?php foreach ($stmTypesPresent as $label => $class): ?>
                <button type="button" class="stm-chip" data-type="<?= htmlspecialchars($class) ?>"><?= htmlspecialchars($label) ?></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($studies)): ?>
        <div class="stm-empty">Nenhum estudo cadastrado.</div>
    <?php else: ?>
        <div class="stm-carousel" id="stmCarousel">
            <?php foreach ($studies as $s):
                $typeMeta = studyTypeMeta($s['material_type'] ?? null);
                $coverUrl = stmCoverUrl($s['file_path'] ?? '');
                $desc = trim((string)($s['description'] ?? ''));
                $term = mb_strtolower(($s['title'] ?? '') . ' ' . $desc . ' ' . ($s['congregation_name'] ?? ''), 'UTF-8');
            ?>
                <div class="stm-hero <?= $typeMeta['class'] ?>" id="stmHero<?= $s['id'] ?>" data-type="<?= $typeMeta['class'] ?>" data-term="<?= htmlspecialchars($term) ?>" <?= $coverUrl ? 'style="background-image: linear-gradient(180deg, rgba(15,23,42,.15) 0%, rgba(15,23,42,.85) 100%), url(\'' . htmlspecialchars($coverUrl) . '\');"' : '' ?>>
                    <div class="stm-hero-top">
                        <span class="stm-hero-type"><i class="fas fa-book-open"></i> <?= htmlspecialchars($typeMeta['label']) ?></span>
                        <span class="stm-hero-icon"><i class="fas fa-book"></i></span>
                    </div>
                    <div class="stm-hero-body">
                        <div class="stm-hero-title"><?= htmlspecialchars($s['title']) ?></div>
                        <?php if ($desc !== ''): ?><div class="stm-hero-desc"><?= htmlspecialchars($desc) ?></div><?php endif; ?>
                        <div class="stm-hero-tags">
                            <span class="stm-hero-tag"><?= $s['congregation_name'] ? htmlspecialchars($s['congregation_name']) : 'Geral (Todas)' ?></span>
                            <span class="stm-hero-tag"><?= date('d/m/Y', strtotime($s['created_at'])) ?></span>
                        </div>
                        <div class="stm-hero-actions">
                            <a href="/admin/studies/view/<?= $s['id'] ?>" target="_blank" class="stm-hero-btn is-primary"><i class="fas fa-file-pdf me-1"></i> Ver PDF</a>
                            <?php
                            $isUnowned = !isset($s['created_by']) || $s['created_by'] === null || $s['created_by'] === '';
                            $isOwner = $userId !== null && isset($s['created_by']) && (string)$s['created_by'] === (string)$userId;
                            $canManage = $canManagePermission && ($isAdmin || ($userId !== null && $isUnowned) || $isOwner);
                            if ($canManage): ?>
                                <a href="/admin/studies/edit/<?= $s['id'] ?>" class="stm-hero-btn is-outline"><i class="fas fa-pen me-1"></i> Editar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="stm-dots" id="stmDots">
            <?php foreach ($studies as $i => $s): ?>
                <span class="stm-dot <?= $i === 0 ? 'active' : '' ?>"></span>
            <?php endforeach; ?>
        </div>

        <div id="stmList">
            <?php foreach ($studies as $s):
                $typeMeta = studyTypeMeta($s['material_type'] ?? null);
                $coverUrl = stmCoverUrl($s['file_path'] ?? '');
                $desc = trim((string)($s['description'] ?? ''));
                $term = mb_strtolower(($s['title'] ?? '') . ' ' . $desc . ' ' . ($s['congregation_name'] ?? ''), 'UTF-8');
                $isUnowned = !isset($s['created_by']) || $s['created_by'] === null || $s['created_by'] === '';
                $isOwner = $userId !== null && isset($s['created_by']) && (string)$s['created_by'] === (string)$userId;
                $canManage = $canManagePermission && ($isAdmin || ($userId !== null && $isUnowned) || $isOwner);
            ?>
                <div class="stm-swipe" data-type="<?= $typeMeta['class'] ?>" data-term="<?= htmlspecialchars($term) ?>">
                    <?php if ($canManage): ?>
                        <div class="stm-swipe-actions">
                            <a class="stm-swipe-action btn-delete-study" href="/admin/studies/delete/<?= $s['id'] ?>" data-title="<?= htmlspecialchars($s['title']) ?>" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    <a href="#stmHero<?= $s['id'] ?>" class="stm-list-item stm-jump" data-jump="stmHero<?= $s['id'] ?>">
                        <?php if ($coverUrl): ?>
                            <img src="<?= htmlspecialchars($coverUrl) ?>" alt="" class="stm-list-icon" style="object-fit:cover;">
                        <?php else: ?>
                            <span class="stm-list-icon <?= $typeMeta['class'] ?>"><i class="fas fa-book"></i></span>
                        <?php endif; ?>
                        <div class="stm-list-id">
                            <div class="stm-list-title"><?= htmlspecialchars($s['title']) ?></div>
                            <div class="stm-list-meta"><?= htmlspecialchars($typeMeta['label']) ?> • <?= $s['congregation_name'] ? htmlspecialchars($s['congregation_name']) : 'Geral' ?></div>
                        </div>
                        <i class="fas fa-chevron-right stm-list-chev"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    $mobilePageFooterLabel = 'Estudos';
    include __DIR__ . '/../../layout/mobile_page_footer.php';
    ?>

    <a href="/admin/studies/create" class="stm-fab" aria-label="Novo estudo"><i class="fas fa-plus"></i></a>
</div>

<script>
(function () {
    var wrap = document.querySelector('.stm-wrap');
    if (!wrap) return;

    var carousel = document.getElementById('stmCarousel');
    var dots = Array.prototype.slice.call(document.querySelectorAll('#stmDots .stm-dot'));
    var total = dots.length;

    function visibleCards() {
        return Array.prototype.slice.call(carousel.querySelectorAll('.stm-hero:not(.stm-hidden)'));
    }

    function currentIndex() {
        var cards = visibleCards();
        var center = carousel.scrollLeft + carousel.clientWidth / 2;
        var idx = 0;
        cards.forEach(function (card, i) {
            if (card.offsetLeft <= center) idx = i;
        });
        return idx;
    }

    function updatePager() {
        var idx = currentIndex();
        dots.forEach(function (d, i) { d.classList.toggle('active', i === idx); });
    }

    if (carousel) {
        carousel.addEventListener('scroll', function () {
            window.requestAnimationFrame(updatePager);
        }, { passive: true });
    }

    // Swipe-to-reveal Excluir on the compact list, plus tap-to-jump to the hero card
    document.querySelectorAll('.stm-swipe').forEach(function (swipe) {
        var card = swipe.querySelector('.stm-list-item');
        if (!card) return;
        var actionsWidth = swipe.querySelectorAll('.stm-swipe-action').length * 56;
        var startX = 0, currentX = 0, dragging = false, open = false, moved = false;

        function setOpen(state) {
            open = state;
            card.style.transform = open ? 'translateX(-' + actionsWidth + 'px)' : 'translateX(0)';
        }
        function closeOthers() {
            document.querySelectorAll('.stm-swipe .stm-list-item').forEach(function (c) {
                if (c !== card) c.style.transform = 'translateX(0)';
            });
        }

        if (actionsWidth) {
            card.addEventListener('touchstart', function (e) {
                startX = e.touches[0].clientX;
                dragging = true;
                moved = false;
                card.style.transition = 'none';
                closeOthers();
            }, { passive: true });

            card.addEventListener('touchmove', function (e) {
                if (!dragging) return;
                currentX = e.touches[0].clientX - startX;
                if (Math.abs(currentX) > 8) moved = true;
                var base = open ? -actionsWidth : 0;
                var next = Math.min(0, Math.max(-actionsWidth, base + currentX));
                card.style.transform = 'translateX(' + next + 'px)';
            }, { passive: true });

            card.addEventListener('touchend', function () {
                dragging = false;
                card.style.transition = '';
                var base = open ? -actionsWidth : 0;
                var draggedTo = base + currentX;
                setOpen(draggedTo < -(actionsWidth / 2));
                currentX = 0;
            });
        }

        card.addEventListener('click', function (e) {
            if (moved || open) {
                e.preventDefault();
                if (open && !moved) setOpen(false);
                moved = false;
                return;
            }
            e.preventDefault();
            var target = document.getElementById(card.getAttribute('data-jump'));
            if (target) target.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        });
    });

    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    var searchInput = document.getElementById('stmSearchInput');
    var activeType = '';

    function applyFilters() {
        var term = normalize(searchInput ? searchInput.value.trim() : '');
        document.querySelectorAll('.stm-hero').forEach(function (card) {
            var matchesTerm = term === '' || normalize(card.getAttribute('data-term')).indexOf(term) !== -1;
            var matchesType = activeType === '' || card.getAttribute('data-type') === activeType;
            card.classList.toggle('stm-hidden', !(matchesTerm && matchesType));
        });
        document.querySelectorAll('.stm-swipe').forEach(function (item) {
            var matchesTerm = term === '' || normalize(item.getAttribute('data-term')).indexOf(term) !== -1;
            var matchesType = activeType === '' || item.getAttribute('data-type') === activeType;
            item.classList.toggle('stm-hidden', !(matchesTerm && matchesType));
        });
        total = visibleCards().length;
        var dotsWrap = document.getElementById('stmDots');
        if (dotsWrap) {
            dots.forEach(function (d, i) { d.style.display = i < total ? '' : 'none'; });
        }
        updatePager();
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);

    var chipsRow = document.getElementById('stmChips');
    if (chipsRow) {
        chipsRow.querySelectorAll('.stm-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                chipsRow.querySelectorAll('.stm-chip').forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                activeType = chip.getAttribute('data-type') || '';
                applyFilters();
            });
        });
    }

    updatePager();
})();
</script>

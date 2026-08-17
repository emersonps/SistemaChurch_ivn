<?php
// src/views/admin/congregations/_mobile_list.php
// Mobile/tablet (<992px) card-based presentation of $congregations already loaded
// by CongregationController::index(). Desktop keeps the classic table untouched
// (hidden via d-none d-lg-block there). Mirrors admin/tithes and admin/members.

function cmInitials($name) {
    $parts = preg_split('/\s+/', trim((string)$name));
    $out = '';
    if (!empty($parts[0])) $out .= mb_substr($parts[0], 0, 1);
    if (count($parts) > 1) $out .= mb_substr(end($parts), 0, 1);
    return mb_strtoupper($out !== '' ? $out : '?');
}
?>
<style>
    .cm-wrap { padding-bottom: 90px; }
    .cm-count { font-size: .78rem; color: #8b93a7; font-weight: 600; margin: -.5rem 0 .9rem; }
    .cm-search { position: relative; margin-bottom: 1rem; }
    .cm-search i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .cm-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #fff; border-radius: 12px; padding: .55rem .8rem .55rem 2.3rem; font-size: .85rem; }
    .cm-search input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .cm-swipe { position: relative; overflow: hidden; border-radius: 16px; margin-bottom: .55rem; }
    .cm-swipe.cm-hidden { display: none; }
    .cm-swipe-actions { position: absolute; top: 0; right: 0; bottom: 0; display: flex; align-items: stretch; }
    .cm-action { width: 56px; border: none; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; text-decoration: none; }
    .cm-action-members { background: #0891b2; }
    .cm-action-edit { background: #0d6efd; }
    .cm-action-delete { background: #dc3545; }
    .cm-card { position: relative; z-index: 1; display: flex; align-items: center; gap: .7rem; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .7rem .85rem; transition: transform .18s ease; touch-action: pan-y; }
    .cm-avatar { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 50%; background: rgba(37,99,235,.1); color: #2563eb; font-weight: 800; font-size: .8rem; display: flex; align-items: center; justify-content: center; }
    .cm-id { min-width: 0; flex: 1 1 auto; }
    .cm-name { font-weight: 700; font-size: .87rem; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cm-meta { font-size: .74rem; color: #8b93a7; }
    .cm-hq-pill { flex: 0 0 auto; background: rgba(37,99,235,.1); color: #2563eb; font-size: .64rem; font-weight: 700; padding: .2rem .55rem; border-radius: 999px; }
    .cm-kebab { flex: 0 0 auto; width: 30px; height: 30px; border: none; background: transparent; color: #adb5bd; border-radius: 50%; }
    .cm-loadmore { display: block; width: 100%; background: #fff; border: 1px solid rgba(17,24,39,.1); color: #16213e; font-weight: 700; font-size: .85rem; border-radius: 12px; padding: .65rem; margin-top: .3rem; }
    .cm-done { text-align: center; font-size: .76rem; color: #adb5bd; font-weight: 600; padding: .6rem 0 1rem; }
    .cm-fab { position: fixed; right: 1rem; bottom: calc(1rem + env(safe-area-inset-bottom)); width: 54px; height: 54px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 6px 16px rgba(37,99,235,.3); z-index: 1030; text-decoration: none; }
    .cm-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }
</style>

<div class="cm-wrap d-lg-none">
    <?php
    $mobilePageCategory = 'Secretaria';
    $mobilePageTitle = 'Congregações';
    include __DIR__ . '/../../layout/mobile_page_header.php';
    ?>
    <div class="cm-count"><?= count($congregations) ?> cadastrada<?= count($congregations) === 1 ? '' : 's' ?></div>

    <div class="cm-search">
        <i class="fas fa-search"></i>
        <input type="text" id="cmSearchInput" placeholder="Buscar congregação...">
    </div>

    <?php if (empty($congregations)): ?>
        <div class="cm-empty">Nenhuma congregação encontrada.</div>
    <?php else: ?>
        <div id="cmCardList">
            <?php foreach ($congregations as $idx => $c):
                $type = strtolower((string)($c['type'] ?? ''));
                $isHq = in_array($type, ['headquarters', 'sede', 'matriz', 'principal'], true);
                $term = mb_strtolower($c['name'] . ' ' . ($c['leader_name'] ?? '') . ' ' . ($c['phone'] ?? ''), 'UTF-8');
            ?>
                <div class="cm-swipe <?= $idx >= 10 ? 'cm-hidden' : '' ?>" data-term="<?= htmlspecialchars($term) ?>">
                    <div class="cm-swipe-actions">
                        <a class="cm-action cm-action-members" href="/admin/members?congregation_id=<?= $c['id'] ?>" title="Ver membros">
                            <i class="fas fa-users"></i>
                        </a>
                        <a class="cm-action cm-action-edit" href="/admin/congregations/edit/<?= $c['id'] ?>" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a class="cm-action cm-action-delete btn-delete-congregation" href="/admin/congregations/delete/<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <div class="cm-card">
                        <span class="cm-avatar"><?= htmlspecialchars(cmInitials($c['name'])) ?></span>
                        <div class="cm-id">
                            <div class="cm-name"><?= htmlspecialchars($c['name']) ?></div>
                            <div class="cm-meta"><?= htmlspecialchars($c['leader_name'] ?? 'Sem dirigente') ?><?php if (!empty($c['phone'])): ?> • <?= htmlspecialchars($c['phone']) ?><?php endif; ?></div>
                        </div>
                        <?php if ($isHq): ?><span class="cm-hq-pill">Principal</span><?php endif; ?>
                        <button type="button" class="cm-kebab" aria-label="Ações"><i class="fas fa-ellipsis-vertical"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="cmLoadMore" class="cm-loadmore <?= count($congregations) > 10 ? '' : 'd-none' ?>">Ver mais</button>
        <div id="cmDone" class="cm-done <?= count($congregations) > 10 ? 'd-none' : '' ?>">Você viu tudo • <?= count($congregations) ?> congregações</div>
    <?php endif; ?>

    <?php
    $mobilePageFooterLabel = 'Congregações';
    include __DIR__ . '/../../layout/mobile_page_footer.php';
    ?>

    <a href="/admin/congregations/create" class="cm-fab" aria-label="Nova congregação"><i class="fas fa-plus"></i></a>
</div>

<script>
(function () {
    var wrap = document.querySelector('.cm-wrap');
    if (!wrap) return;

    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    var searchInput = document.getElementById('cmSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var term = normalize(searchInput.value.trim());
            document.querySelectorAll('#cmCardList .cm-swipe').forEach(function (card) {
                var match = term === '' || normalize(card.getAttribute('data-term')).indexOf(term) !== -1;
                card.style.display = match ? '' : 'none';
            });
        });
    }

    // "Ver mais" reveals the next batch of already-rendered cards
    var loadMoreBtn = document.getElementById('cmLoadMore');
    var doneLabel = document.getElementById('cmDone');
    var BATCH = 10;
    function revealMore() {
        var hidden = document.querySelectorAll('#cmCardList .cm-swipe.cm-hidden');
        for (var i = 0; i < BATCH && i < hidden.length; i++) {
            hidden[i].classList.remove('cm-hidden');
        }
        if (hidden.length <= BATCH) {
            if (loadMoreBtn) loadMoreBtn.classList.add('d-none');
            if (doneLabel) doneLabel.classList.remove('d-none');
        }
    }
    if (loadMoreBtn) loadMoreBtn.addEventListener('click', revealMore);

    // Swipe-to-reveal quick actions (iOS Mail style), plus a kebab fallback for non-touch
    document.querySelectorAll('.cm-swipe').forEach(function (swipe) {
        var card = swipe.querySelector('.cm-card');
        var actionsWidth = swipe.querySelectorAll('.cm-action').length * 56;
        var startX = 0, currentX = 0, dragging = false, open = false;

        function setOpen(state) {
            open = state;
            card.style.transform = open ? 'translateX(-' + actionsWidth + 'px)' : 'translateX(0)';
        }
        function closeOthers() {
            document.querySelectorAll('.cm-swipe .cm-card').forEach(function (c) {
                if (c !== card) c.style.transform = 'translateX(0)';
            });
        }

        card.addEventListener('touchstart', function (e) {
            startX = e.touches[0].clientX;
            dragging = true;
            card.style.transition = 'none';
            closeOthers();
        }, { passive: true });

        card.addEventListener('touchmove', function (e) {
            if (!dragging) return;
            currentX = e.touches[0].clientX - startX;
            var base = open ? -actionsWidth : 0;
            var next = Math.min(0, Math.max(-actionsWidth, base + currentX));
            card.style.transform = 'translateX(' + next + 'px)';
        }, { passive: true });

        card.addEventListener('touchend', function () {
            dragging = false;
            card.style.transition = '';
            var base = open ? -actionsWidth : 0;
            var moved = base + currentX;
            setOpen(moved < -(actionsWidth / 2));
            currentX = 0;
        });

        var kebab = swipe.querySelector('.cm-kebab');
        if (kebab) {
            kebab.addEventListener('click', function (e) {
                e.stopPropagation();
                closeOthers();
                setOpen(!open);
            });
        }
    });
})();
</script>

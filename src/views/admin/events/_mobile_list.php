<?php
// src/views/admin/events/_mobile_list.php
// Mobile/tablet (<992px) card-based presentation of $events already loaded by
// EventController::index() and grouped by index.php above ($groupedEvents,
// $categories, $now). Desktop keeps the classic tabs/table untouched (hidden
// via d-none d-lg-block/flex there). Mirrors admin/tithes, members, congregations.
?>
<style>
    .em-wrap { padding-bottom: 90px; }
    .em-count { font-size: .78rem; color: #8b93a7; font-weight: 600; margin: -.5rem 0 .9rem; }
    .em-toolbar { display: flex; gap: .5rem; overflow-x: auto; padding-bottom: .2rem; margin-bottom: 1rem; scrollbar-width: none; }
    .em-toolbar::-webkit-scrollbar { display: none; }
    .em-toolbar .btn { flex: 0 0 auto; white-space: nowrap; }
    .em-searchrow { display: flex; gap: .5rem; margin-bottom: .9rem; }
    .em-search { position: relative; flex: 1 1 auto; min-width: 0; }
    .em-search i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .em-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #fff; border-radius: 12px; padding: .55rem .8rem .55rem 2.3rem; font-size: .85rem; }
    .em-search input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .em-filter-btn { flex: 0 0 auto; border: none; background: #16213e; color: #fff; border-radius: 12px; padding: 0 1rem; font-size: .82rem; font-weight: 700; }
    .em-swipe { position: relative; overflow: hidden; border-radius: 16px; margin-bottom: .55rem; }
    .em-swipe.em-hidden { display: none; }
    .em-swipe-actions { position: absolute; top: 0; right: 0; bottom: 0; display: flex; align-items: stretch; }
    .em-action { width: 56px; border: none; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; text-decoration: none; }
    .em-action-edit { background: #0d6efd; }
    .em-action-toggle-on { background: #f59e0b; }
    .em-action-toggle-off { background: #16a34a; }
    .em-action-delete { background: #dc3545; }
    .em-card { position: relative; z-index: 1; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .8rem .9rem; transition: transform .18s ease; touch-action: pan-y; }
    .em-card-top { display: flex; align-items: flex-start; gap: .6rem; }
    .em-icon { flex: 0 0 auto; width: 38px; height: 38px; border-radius: 11px; background: rgba(37,99,235,.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .em-id { min-width: 0; flex: 1 1 auto; }
    .em-title { font-weight: 700; font-size: .87rem; color: #16213e; }
    .em-meta { font-size: .74rem; color: #8b93a7; margin-top: .1rem; }
    .em-date { font-size: .78rem; font-weight: 700; color: #16213e; }
    .em-kebab { flex: 0 0 auto; width: 30px; height: 30px; border: none; background: transparent; color: #adb5bd; border-radius: 50%; }
    .em-status { display: inline-flex; align-items: center; gap: .35rem; padding: .22rem .6rem; border-radius: 999px; font-size: .64rem; font-weight: 700; margin-top: .5rem; }
    .em-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .em-status.is-active { background: rgba(25,135,84,.12); color: #198754; }
    .em-status.is-inactive { background: rgba(0,0,0,.06); color: #6c757d; }
    .em-loadmore { display: block; width: 100%; background: #fff; border: 1px solid rgba(17,24,39,.1); color: #16213e; font-weight: 700; font-size: .85rem; border-radius: 12px; padding: .65rem; margin-top: .3rem; }
    .em-done { text-align: center; font-size: .76rem; color: #adb5bd; font-weight: 600; padding: .6rem 0 1rem; }
    .em-fab { position: fixed; right: 1rem; bottom: calc(1rem + env(safe-area-inset-bottom)); width: 54px; height: 54px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 6px 16px rgba(37,99,235,.3); z-index: 1030; text-decoration: none; }
    .em-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }
    .em-sheet .offcanvas-header { border-bottom: 1px solid rgba(0,0,0,.06); }
    .em-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 92vh; }
    .em-filter-label { font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; margin-bottom: .5rem; }
    .em-chip-row { display: flex; flex-wrap: wrap; gap: .45rem; }
    .em-chip { border: 1px solid rgba(17,24,39,.1); background: #fff; color: #495057; border-radius: 999px; padding: .4rem .9rem; font-size: .8rem; font-weight: 700; }
    .em-chip.active { background: #16213e; border-color: #16213e; color: #fff; }
</style>

<div class="em-wrap d-lg-none">
    <?php
    $mobilePageCategory = 'Secretaria';
    $mobilePageTitle = 'Eventos';
    include __DIR__ . '/../../layout/mobile_page_header.php';
    ?>
    <div class="em-count"><?= count($events) ?> evento<?= count($events) === 1 ? '' : 's' ?></div>

    <div class="em-toolbar">
        <a href="/admin/attendance" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold px-3">
            <i class="fas fa-list-check me-1"></i> Controle de Presença
        </a>
    </div>

    <div class="em-searchrow">
        <div class="em-search">
            <i class="fas fa-search"></i>
            <input type="text" id="emSearchInput" placeholder="Buscar evento...">
        </div>
        <button type="button" class="em-filter-btn" data-bs-toggle="offcanvas" data-bs-target="#emFilterSheet">
            <i class="fas fa-sliders-h me-1"></i>Filtros
        </button>
    </div>

    <?php if (empty($events)): ?>
        <div class="em-empty">Nenhum evento encontrado.</div>
    <?php else: ?>
        <div id="emCardList">
            <?php
            $emIdx = 0;
            foreach ($categories as $catKey => $catLabel):
                foreach ($groupedEvents[$catKey] as $e):
                    $emIdx++;
                    $dateBadges = eventGetDateBadges($e);
                    $next = eventNextOccurrence($e, $now);
                    if (empty($dateBadges)) {
                        $emDateText = 'Data indefinida';
                    } else {
                        $emDateText = $next ? $next->format('d/m/Y H:i') : ($dateBadges[0]['date'] . ' ' . $dateBadges[0]['time']);
                        if (count($dateBadges) > 1) $emDateText .= ' (+' . (count($dateBadges) - 1) . ')';
                    }
                    $isActive = (($e['status'] ?? 'active') === 'active');
                    $canToggle = (
                        strtolower($e['type'] ?? '') === 'culto'
                        || !empty($e['recurring_days'])
                        || eventHasFutureOccurrence($e, $now)
                    );
                    $term = mb_strtolower($e['title'] . ' ' . ($e['location'] ?? ''), 'UTF-8');
                    ?>
                    <div class="em-swipe <?= $emIdx > 10 ? 'em-hidden' : '' ?>" data-term="<?= htmlspecialchars($term) ?>" data-cat="<?= htmlspecialchars($catKey) ?>">
                        <div class="em-swipe-actions">
                            <a class="em-action em-action-edit" href="/admin/events/edit/<?= $e['id'] ?>" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($canToggle): ?>
                                <a class="em-action <?= $isActive ? 'em-action-toggle-on' : 'em-action-toggle-off' ?>" href="/admin/events/toggle/<?= $e['id'] ?>" title="<?= $isActive ? 'Desativar' : 'Ativar' ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                            <?php endif; ?>
                            <a class="em-action em-action-delete btn-delete-event" href="/admin/events/delete/<?= $e['id'] ?>" data-title="<?= htmlspecialchars($e['title']) ?>" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                        <div class="em-card">
                            <div class="em-card-top">
                                <span class="em-icon"><i class="fas <?= !empty($e['banner_path']) ? 'fa-image' : 'fa-calendar-alt' ?>"></i></span>
                                <div class="em-id">
                                    <div class="em-title"><?= htmlspecialchars($e['title']) ?></div>
                                    <div class="em-meta"><?= htmlspecialchars($catLabel) ?><?php if (!empty($e['location'])): ?> • <?= htmlspecialchars($e['location']) ?><?php endif; ?></div>
                                    <div class="em-date mt-1"><i class="far fa-clock me-1"></i><?= htmlspecialchars($emDateText) ?></div>
                                    <span class="em-status <?= $isActive ? 'is-active' : 'is-inactive' ?>"><?= $isActive ? 'Ativo' : 'Inativo' ?></span>
                                </div>
                                <button type="button" class="em-kebab" aria-label="Ações"><i class="fas fa-ellipsis-vertical"></i></button>
                            </div>
                        </div>
                    </div>
                    <?php
                endforeach;
            endforeach;
            ?>
        </div>
        <button type="button" id="emLoadMore" class="em-loadmore <?= count($events) > 10 ? '' : 'd-none' ?>">Ver mais</button>
        <div id="emDone" class="em-done <?= count($events) > 10 ? 'd-none' : '' ?>">Você viu tudo • <?= count($events) ?> eventos</div>
    <?php endif; ?>

    <?php
    $mobilePageFooterLabel = 'Eventos';
    include __DIR__ . '/../../layout/mobile_page_footer.php';
    ?>

    <a href="/admin/events/create" class="em-fab" aria-label="Novo evento"><i class="fas fa-plus"></i></a>
</div>

<div class="offcanvas offcanvas-bottom em-sheet" tabindex="-1" id="emFilterSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Filtros</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <div class="em-filter-label">Categoria</div>
        <div class="em-chip-row" id="emCatChips">
            <button type="button" class="em-chip active" data-cat="">Todos</button>
            <?php foreach ($categories as $catKey => $catLabel): ?>
                <button type="button" class="em-chip" data-cat="<?= htmlspecialchars($catKey) ?>"><?= htmlspecialchars($catLabel) ?> (<?= count($groupedEvents[$catKey]) ?>)</button>
            <?php endforeach; ?>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="button" id="emClearFilters" class="btn btn-outline-secondary flex-fill rounded-pill">Limpar</button>
            <button type="button" class="btn btn-dark flex-fill rounded-pill" data-bs-dismiss="offcanvas">Aplicar filtros</button>
        </div>
    </div>
</div>

<script>
(function () {
    var wrap = document.querySelector('.em-wrap');
    if (!wrap) return;

    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    var activeCat = '';
    var searchInput = document.getElementById('emSearchInput');
    var catChips = document.getElementById('emCatChips');

    function applyFilters() {
        var term = normalize(searchInput ? searchInput.value.trim() : '');
        var filtering = term !== '' || activeCat !== '';
        if (filtering) {
            document.querySelectorAll('#emCardList .em-swipe.em-hidden').forEach(function (card) {
                card.classList.remove('em-hidden');
            });
        }
        document.querySelectorAll('#emCardList .em-swipe').forEach(function (card) {
            var matchesTerm = term === '' || normalize(card.getAttribute('data-term')).indexOf(term) !== -1;
            var matchesCat = activeCat === '' || card.getAttribute('data-cat') === activeCat;
            card.style.display = (matchesTerm && matchesCat) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);

    if (catChips) {
        catChips.querySelectorAll('.em-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                catChips.querySelectorAll('.em-chip').forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                activeCat = chip.getAttribute('data-cat') || '';
                applyFilters();
            });
        });
    }

    var clearBtn = document.getElementById('emClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            activeCat = '';
            if (catChips) {
                catChips.querySelectorAll('.em-chip').forEach(function (c, i) { c.classList.toggle('active', i === 0); });
            }
            applyFilters();
        });
    }

    // "Ver mais" reveals the next batch of already-rendered cards
    var loadMoreBtn = document.getElementById('emLoadMore');
    var doneLabel = document.getElementById('emDone');
    var BATCH = 10;
    function revealMore() {
        var hidden = document.querySelectorAll('#emCardList .em-swipe.em-hidden');
        for (var i = 0; i < BATCH && i < hidden.length; i++) {
            hidden[i].classList.remove('em-hidden');
        }
        if (hidden.length <= BATCH) {
            if (loadMoreBtn) loadMoreBtn.classList.add('d-none');
            if (doneLabel) doneLabel.classList.remove('d-none');
        }
    }
    if (loadMoreBtn) loadMoreBtn.addEventListener('click', revealMore);

    // Swipe-to-reveal quick actions (iOS Mail style), plus a kebab fallback for non-touch
    document.querySelectorAll('.em-swipe').forEach(function (swipe) {
        var card = swipe.querySelector('.em-card');
        var actionsWidth = swipe.querySelectorAll('.em-action').length * 56;
        var startX = 0, currentX = 0, dragging = false, open = false;

        function setOpen(state) {
            open = state;
            card.style.transform = open ? 'translateX(-' + actionsWidth + 'px)' : 'translateX(0)';
        }
        function closeOthers() {
            document.querySelectorAll('.em-swipe .em-card').forEach(function (c) {
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

        var kebab = swipe.querySelector('.em-kebab');
        if (kebab) {
            kebab.addEventListener('click', function (e) {
                e.stopPropagation();
                closeOthers();
                setOpen(!open);
            });
        }
    });
    // Note: delete confirmation for .btn-delete-event is wired via the page's own
    // $(document).on('click', '.btn-delete-event', ...) delegated handler below,
    // which already covers these dynamically-added swipe buttons — no need to repeat it here.
})();
</script>

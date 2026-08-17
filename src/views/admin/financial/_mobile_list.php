<?php
// src/views/admin/financial/_mobile_list.php
// Mobile/tablet (<992px) card-based presentation of the same $closures/$groupedClosures
// data already loaded by FinancialClosureController::index(). Desktop keeps the
// classic tabs/table untouched (hidden via d-none d-lg-block there).
// Mirrors admin/congregations/_mobile_list.php (closest analog): 2 swipe actions,
// no create page (the FAB opens the existing #newClosureModal instead).
?>
<style>
    .fcm-wrap { padding-bottom: 90px; }
    .fcm-count { font-size: .78rem; color: #8b93a7; font-weight: 600; margin: -.5rem 0 .9rem; }
    .fcm-searchrow { display: flex; gap: .5rem; margin-bottom: .9rem; }
    .fcm-search { position: relative; flex: 1 1 auto; min-width: 0; }
    .fcm-search i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .fcm-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #fff; border-radius: 12px; padding: .55rem .8rem .55rem 2.3rem; font-size: .85rem; }
    .fcm-search input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .fcm-filter-btn { flex: 0 0 auto; border: none; background: #16213e; color: #fff; border-radius: 12px; padding: 0 1rem; font-size: .82rem; font-weight: 700; }
    .fcm-chip-row { display: flex; flex-wrap: wrap; gap: .45rem; }
    .fcm-chip { border: 1px solid rgba(17,24,39,.1); background: #fff; color: #495057; border-radius: 999px; padding: .4rem .9rem; font-size: .8rem; font-weight: 700; }
    .fcm-chip.active { background: #2563eb; border-color: #2563eb; color: #fff; }
    .fcm-swipe { position: relative; overflow: hidden; border-radius: 16px; margin-bottom: .6rem; }
    .fcm-swipe.fcm-hidden { display: none; }
    .fcm-swipe-actions { position: absolute; top: 0; right: 0; bottom: 0; display: flex; align-items: stretch; }
    .fcm-action { width: 56px; border: none; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; text-decoration: none; }
    .fcm-action-view { background: #0891b2; }
    .fcm-action-delete { background: #dc3545; }
    .fcm-card { position: relative; z-index: 1; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .8rem .9rem; transition: transform .18s ease; touch-action: pan-y; }
    .fcm-card-top { display: flex; align-items: center; justify-content: space-between; gap: .6rem; }
    .fcm-period { font-weight: 800; font-size: .92rem; color: #16213e; }
    .fcm-meta { font-size: .72rem; color: #8b93a7; margin-top: .1rem; }
    .fcm-type-pill { flex: 0 0 auto; font-size: .66rem; font-weight: 700; padding: .22rem .6rem; border-radius: 999px; }
    .fcm-type-pill.type-mensal { background: rgba(13,202,240,.14); color: #087990; }
    .fcm-type-pill.type-anual { background: rgba(37,99,235,.1); color: #2563eb; }
    .fcm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; margin-top: .7rem; }
    .fcm-cell { background: #f8f9fb; border-radius: 10px; padding: .5rem .6rem; }
    .fcm-cell-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #8b93a7; }
    .fcm-cell-value { font-size: .82rem; font-weight: 800; }
    .fcm-cell-value.is-up { color: #16a34a; }
    .fcm-cell-value.is-down { color: #dc2626; }
    .fcm-cell.is-final { background: #16213e; }
    .fcm-cell.is-final .fcm-cell-label { color: rgba(255,255,255,.65); }
    .fcm-cell.is-final .fcm-cell-value { color: #fff; }
    .fcm-kebab { flex: 0 0 auto; width: 30px; height: 30px; border: none; background: transparent; color: #adb5bd; border-radius: 50%; }
    .fcm-loadmore { display: block; width: 100%; background: #fff; border: 1px solid rgba(17,24,39,.1); color: #16213e; font-weight: 700; font-size: .85rem; border-radius: 12px; padding: .65rem; margin-top: .3rem; }
    .fcm-done { text-align: center; font-size: .76rem; color: #adb5bd; font-weight: 600; padding: .6rem 0 1rem; }
    .fcm-fab { position: fixed; right: 1rem; bottom: calc(1rem + env(safe-area-inset-bottom)); width: 54px; height: 54px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 16px rgba(37,99,235,.3); z-index: 1030; border: none; }
    .fcm-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }
    .fcm-sheet .offcanvas-header { border-bottom: 1px solid rgba(0,0,0,.06); }
    .fcm-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 92vh; }
    .fcm-filter-label { font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; margin-bottom: .5rem; }
</style>

<div class="fcm-wrap d-lg-none">
    <?php
    $mobilePageCategory = 'Financeiro';
    $mobilePageTitle = 'Fechamentos';
    include __DIR__ . '/../../layout/mobile_page_header.php';
    ?>
    <div class="fcm-count"><?= count($closures) ?> fechamento<?= count($closures) === 1 ? '' : 's' ?></div>

    <div class="fcm-searchrow">
        <div class="fcm-search">
            <i class="fas fa-search"></i>
            <input type="text" id="fcmSearchInput" placeholder="Buscar período...">
        </div>
        <?php if ($hasMultipleCongregations): ?>
            <button type="button" class="fcm-filter-btn" data-bs-toggle="offcanvas" data-bs-target="#fcmFilterSheet">
                <i class="fas fa-sliders-h me-1"></i>Filtros
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($closures)): ?>
        <div class="fcm-empty">Nenhum fechamento financeiro registrado.</div>
    <?php else: ?>
        <div id="fcmCardList">
            <?php foreach ($closures as $idx => $fc):
                $congName = $fc['congregation_name'] ?? 'Sem Congregação';
                $term = mb_strtolower($fc['period'] . ' ' . $fc['type'] . ' ' . $congName, 'UTF-8');
            ?>
                <div class="fcm-swipe <?= $idx >= 10 ? 'fcm-hidden' : '' ?>" data-term="<?= htmlspecialchars($term) ?>" data-cong="<?= htmlspecialchars(mb_strtolower($congName, 'UTF-8')) ?>">
                    <div class="fcm-swipe-actions">
                        <a class="fcm-action fcm-action-view" href="/admin/financial/closures/show/<?= $fc['id'] ?>" title="Ver detalhes">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a class="fcm-action fcm-action-delete btn-delete-closure" href="/admin/financial/closures/delete/<?= $fc['id'] ?>" data-period="<?= htmlspecialchars($fc['period']) ?>" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <div class="fcm-card">
                        <div class="fcm-card-top">
                            <div>
                                <div class="fcm-period"><?= htmlspecialchars($fc['period']) ?></div>
                                <div class="fcm-meta"><?= htmlspecialchars($congName) ?> • <?= date('d/m/Y H:i', strtotime($fc['created_at'])) ?></div>
                            </div>
                            <span class="fcm-type-pill type-<?= $fc['type'] == 'Mensal' ? 'mensal' : 'anual' ?>"><?= htmlspecialchars($fc['type']) ?></span>
                            <button type="button" class="fcm-kebab" aria-label="Ações"><i class="fas fa-ellipsis-vertical"></i></button>
                        </div>
                        <div class="fcm-grid">
                            <div class="fcm-cell">
                                <div class="fcm-cell-label">Entradas</div>
                                <div class="fcm-cell-value is-up">R$ <?= number_format($fc['total_entries'], 2, ',', '.') ?></div>
                            </div>
                            <div class="fcm-cell">
                                <div class="fcm-cell-label">Saídas</div>
                                <div class="fcm-cell-value is-down">R$ <?= number_format($fc['total_expenses'], 2, ',', '.') ?></div>
                            </div>
                            <div class="fcm-cell">
                                <div class="fcm-cell-label">Saldo período</div>
                                <div class="fcm-cell-value <?= $fc['balance'] >= 0 ? 'is-up' : 'is-down' ?>">R$ <?= number_format($fc['balance'], 2, ',', '.') ?></div>
                            </div>
                            <div class="fcm-cell is-final">
                                <div class="fcm-cell-label">Saldo final</div>
                                <div class="fcm-cell-value">R$ <?= number_format($fc['final_balance'], 2, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="fcmLoadMore" class="fcm-loadmore <?= count($closures) > 10 ? '' : 'd-none' ?>">Ver mais</button>
        <div id="fcmDone" class="fcm-done <?= count($closures) > 10 ? 'd-none' : '' ?>">Você viu tudo • <?= count($closures) ?> fechamentos</div>
    <?php endif; ?>

    <?php
    $mobilePageFooterLabel = 'Fechamentos Financeiros';
    include __DIR__ . '/../../layout/mobile_page_footer.php';
    ?>

    <button type="button" class="fcm-fab" data-bs-toggle="modal" data-bs-target="#newClosureModal" aria-label="Novo fechamento"><i class="fas fa-file-invoice-dollar"></i></button>
</div>

<?php if ($hasMultipleCongregations): ?>
<div class="offcanvas offcanvas-bottom fcm-sheet" tabindex="-1" id="fcmFilterSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Filtros</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <div class="fcm-filter-label">Congregação</div>
        <div class="fcm-chip-row" id="fcmCongChips">
            <button type="button" class="fcm-chip active" data-cong="">Todas</button>
            <?php foreach ($groupedClosures as $congregationName => $items): ?>
                <button type="button" class="fcm-chip" data-cong="<?= htmlspecialchars(mb_strtolower($congregationName, 'UTF-8')) ?>"><?= htmlspecialchars($congregationName) ?></button>
            <?php endforeach; ?>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="button" id="fcmClearFilters" class="btn btn-outline-secondary flex-fill rounded-pill">Limpar</button>
            <button type="button" class="btn btn-dark flex-fill rounded-pill" data-bs-dismiss="offcanvas">Aplicar filtros</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    var wrap = document.querySelector('.fcm-wrap');
    if (!wrap) return;

    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    var activeCong = '';
    var searchInput = document.getElementById('fcmSearchInput');
    var congChips = document.getElementById('fcmCongChips');

    function applyFilters() {
        var term = normalize(searchInput ? searchInput.value.trim() : '');
        var filtering = term !== '' || activeCong !== '';
        if (filtering) {
            document.querySelectorAll('#fcmCardList .fcm-swipe.fcm-hidden').forEach(function (card) {
                card.classList.remove('fcm-hidden');
            });
        }
        document.querySelectorAll('#fcmCardList .fcm-swipe').forEach(function (card) {
            var matchesTerm = term === '' || normalize(card.getAttribute('data-term')).indexOf(term) !== -1;
            var matchesCong = activeCong === '' || card.getAttribute('data-cong') === activeCong;
            card.style.display = (matchesTerm && matchesCong) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);

    if (congChips) {
        congChips.querySelectorAll('.fcm-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                congChips.querySelectorAll('.fcm-chip').forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                activeCong = chip.getAttribute('data-cong') || '';
                applyFilters();
            });
        });
    }

    var clearBtn = document.getElementById('fcmClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            activeCong = '';
            if (congChips) {
                congChips.querySelectorAll('.fcm-chip').forEach(function (c, i) { c.classList.toggle('active', i === 0); });
            }
            applyFilters();
        });
    }

    var loadMoreBtn = document.getElementById('fcmLoadMore');
    var doneLabel = document.getElementById('fcmDone');
    var BATCH = 10;
    function revealMore() {
        var hidden = document.querySelectorAll('#fcmCardList .fcm-swipe.fcm-hidden');
        for (var i = 0; i < BATCH && i < hidden.length; i++) {
            hidden[i].classList.remove('fcm-hidden');
        }
        if (hidden.length <= BATCH) {
            if (loadMoreBtn) loadMoreBtn.classList.add('d-none');
            if (doneLabel) doneLabel.classList.remove('d-none');
        }
    }
    if (loadMoreBtn) loadMoreBtn.addEventListener('click', revealMore);

    document.querySelectorAll('.fcm-swipe').forEach(function (swipe) {
        var card = swipe.querySelector('.fcm-card');
        var actionsWidth = swipe.querySelectorAll('.fcm-action').length * 56;
        var startX = 0, currentX = 0, dragging = false, open = false;

        function setOpen(state) {
            open = state;
            card.style.transform = open ? 'translateX(-' + actionsWidth + 'px)' : 'translateX(0)';
        }
        function closeOthers() {
            document.querySelectorAll('.fcm-swipe .fcm-card').forEach(function (c) {
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

        var kebab = swipe.querySelector('.fcm-kebab');
        if (kebab) {
            kebab.addEventListener('click', function (e) {
                e.stopPropagation();
                closeOthers();
                setOpen(!open);
            });
        }
    });
    // Note: delete confirmation for .btn-delete-closure is wired via the page's own
    // document.querySelectorAll('.btn-delete-closure')... handler (runs after this
    // partial's DOM is already present) — no need to repeat it here.
})();
</script>

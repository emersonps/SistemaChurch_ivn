<?php
// src/views/admin/expenses/_mobile_list.php
// Mobile/tablet (<992px) card-based presentation of the same $expenses/$congregations/
// $totalAmount data already loaded by ExpenseController::index(). Desktop keeps the
// classic table/tabs untouched (hidden via d-none d-lg-block there).
// Mirrors admin/tithes/_mobile_list.php (closest analog), minus the Tipo filter and
// receipt modal (neither exists for expenses) — just Editar/Excluir per card.

$exmSparkDays = [];
for ($i = 6; $i >= 0; $i--) {
    $exmSparkDays[date('Y-m-d', strtotime("-$i day"))] = 0.0;
}
foreach ($expenses as $e) {
    $d = substr((string)$e['expense_date'], 0, 10);
    if (isset($exmSparkDays[$d])) $exmSparkDays[$d] += (float)$e['amount'];
}
$exmSparkMax = max($exmSparkDays) ?: 1;
$exmSparkPoints = [];
$exmSparkCount = count($exmSparkDays);
$exmI = 0;
foreach ($exmSparkDays as $v) {
    $x = $exmSparkCount > 1 ? ($exmI / ($exmSparkCount - 1)) * 100 : 0;
    $y = 28 - (($v / $exmSparkMax) * 24);
    $exmSparkPoints[] = round($x, 1) . ',' . round($y, 1);
    $exmI++;
}
$exmSparkPolyline = implode(' ', $exmSparkPoints);

$exmShowCongFilter = empty($_SESSION['user_congregation_id']);
$exmActiveFilters = 0;
if (!empty($_GET['congregation_id'])) $exmActiveFilters++;
if (!empty($_GET['start_date']) || !empty($_GET['end_date'])) $exmActiveFilters++;

function exmInitials($name) {
    $parts = preg_split('/\s+/', trim((string)$name));
    $out = '';
    if (!empty($parts[0])) $out .= mb_substr($parts[0], 0, 1);
    if (count($parts) > 1) $out .= mb_substr(end($parts), 0, 1);
    return mb_strtoupper($out !== '' ? $out : '?');
}
?>
<style>
    .exm-wrap { padding-bottom: 90px; }
    .exm-stats { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: .9rem; }
    .exm-stat { background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .8rem .9rem; overflow: hidden; }
    .exm-stat-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; margin-bottom: .3rem; }
    .exm-stat-value { font-size: 1.05rem; font-weight: 800; color: #16213e; }
    .exm-stat-sub { font-size: .72rem; color: #8b93a7; font-weight: 700; margin-top: .1rem; }
    .exm-spark { width: 100%; height: 26px; margin-top: .35rem; }
    .exm-spark polyline { fill: none; stroke: #dc2626; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .exm-searchrow { display: flex; gap: .5rem; margin-bottom: .7rem; }
    .exm-search { position: relative; flex: 1 1 auto; min-width: 0; }
    .exm-search i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .exm-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #fff; border-radius: 12px; padding: .55rem .8rem .55rem 2.3rem; font-size: .85rem; }
    .exm-search input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .exm-filter-btn { position: relative; flex: 0 0 auto; border: none; background: #16213e; color: #fff; border-radius: 12px; padding: 0 1rem; font-size: .82rem; font-weight: 700; }
    .exm-filter-badge { position: absolute; top: -6px; right: -6px; background: #dc3545; color: #fff; border-radius: 999px; min-width: 18px; height: 18px; font-size: .64rem; display: flex; align-items: center; justify-content: center; padding: 0 3px; }
    .exm-section-label { font-size: .74rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #8b93a7; margin: 0 0 .55rem 2px; }
    .exm-swipe { position: relative; overflow: hidden; border-radius: 16px; margin-bottom: .6rem; }
    .exm-swipe.exm-hidden { display: none; }
    .exm-swipe-actions { position: absolute; top: 0; right: 0; bottom: 0; display: flex; align-items: stretch; }
    .exm-action { width: 56px; border: none; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; text-decoration: none; }
    .exm-action-edit { background: #0d6efd; }
    .exm-action-delete { background: #dc3545; }
    .exm-card { position: relative; z-index: 1; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .8rem .9rem; transition: transform .18s ease; touch-action: pan-y; }
    .exm-card-top { display: flex; align-items: center; gap: .6rem; }
    .exm-avatar { flex: 0 0 auto; width: 38px; height: 38px; border-radius: 50%; background: rgba(220,38,38,.1); color: #dc2626; font-weight: 800; font-size: .78rem; display: flex; align-items: center; justify-content: center; }
    .exm-card-id { min-width: 0; flex: 1 1 auto; }
    .exm-card-name { font-weight: 700; font-size: .87rem; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .exm-card-meta { font-size: .72rem; color: #8b93a7; }
    .exm-kebab { flex: 0 0 auto; width: 30px; height: 30px; border: none; background: transparent; color: #adb5bd; border-radius: 50%; }
    .exm-card-bottom { display: flex; align-items: flex-end; justify-content: space-between; margin-top: .55rem; }
    .exm-card-value-label { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; }
    .exm-card-value { font-size: 1rem; font-weight: 800; color: #dc2626; }
    .exm-cat-pill { background: #eef0f2; color: #495057; font-size: .68rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px; }
    .exm-unaccountable-pill { display: inline-block; margin-top: .3rem; background: #eef0f2; color: #6c757d; font-size: .62rem; font-weight: 700; padding: .18rem .5rem; border-radius: 999px; }
    .exm-loadmore { display: block; width: 100%; background: #fff; border: 1px solid rgba(17,24,39,.1); color: #16213e; font-weight: 700; font-size: .85rem; border-radius: 12px; padding: .65rem; margin-top: .3rem; }
    .exm-done { text-align: center; font-size: .76rem; color: #adb5bd; font-weight: 600; padding: .6rem 0 1rem; }
    .exm-fab { position: fixed; right: 1rem; bottom: calc(1rem + env(safe-area-inset-bottom)); width: 54px; height: 54px; border-radius: 50%; background: #dc2626; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 6px 16px rgba(220,38,38,.32); z-index: 1030; text-decoration: none; }
    .exm-sheet .offcanvas-header { border-bottom: 1px solid rgba(0,0,0,.06); }
    .exm-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 92vh; }
    .exm-filter-group { margin-bottom: 1.1rem; }
    .exm-filter-label { font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; margin-bottom: .5rem; }
    .exm-mini-label { font-size: .7rem; font-weight: 700; color: #8b93a7; }
    .exm-chip-row { display: flex; flex-wrap: wrap; gap: .45rem; }
    .exm-chip { border: 1px solid rgba(17,24,39,.1); background: #fff; color: #495057; border-radius: 999px; padding: .4rem .9rem; font-size: .8rem; font-weight: 700; }
    .exm-chip.active { background: #16213e; border-color: #16213e; color: #fff; }
    .exm-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }
</style>

<div class="exm-wrap d-lg-none">
    <?php
    $mobilePageCategory = 'Financeiro';
    $mobilePageTitle = 'Saídas / Despesas';
    include __DIR__ . '/../../layout/mobile_page_header.php';
    ?>

    <?php if (!empty($expenses)): ?>
        <div class="exm-stats">
            <div class="exm-stat">
                <div class="exm-stat-label"><i class="fas fa-arrow-trend-down me-1"></i>Total exibido</div>
                <div class="exm-stat-value">R$ <?= number_format($totalAmount, 2, ',', '.') ?></div>
                <svg class="exm-spark" viewBox="0 0 100 28" preserveAspectRatio="none"><polyline points="<?= htmlspecialchars($exmSparkPolyline) ?>"></polyline></svg>
            </div>
            <div class="exm-stat">
                <div class="exm-stat-label"><i class="fas fa-receipt me-1"></i>Lançamentos</div>
                <div class="exm-stat-value"><?= count($expenses) ?></div>
                <div class="exm-stat-sub">no período exibido</div>
            </div>
        </div>
    <?php endif; ?>

    <div class="exm-searchrow">
        <div class="exm-search">
            <i class="fas fa-search"></i>
            <input type="text" id="exmSearchInput" placeholder="Buscar despesa...">
        </div>
        <button type="button" class="exm-filter-btn" data-bs-toggle="offcanvas" data-bs-target="#exmFilterSheet">
            <i class="fas fa-sliders-h me-1"></i>Filtros
            <?php if ($exmActiveFilters > 0): ?><span class="exm-filter-badge"><?= $exmActiveFilters ?></span><?php endif; ?>
        </button>
    </div>

    <div class="exm-section-label">Lançamentos (<?= count($expenses) ?>)</div>

    <?php if (empty($expenses)): ?>
        <div class="exm-empty">Nenhuma despesa encontrada.</div>
    <?php else: ?>
        <div id="exmCardList">
            <?php foreach ($expenses as $idx => $e):
                $term = mb_strtolower($e['description'] . ' ' . ($e['category'] ?? '') . ' ' . ($e['congregation_name'] ?? ''), 'UTF-8');
            ?>
                <div class="exm-swipe <?= $idx >= 10 ? 'exm-hidden' : '' ?>" data-term="<?= htmlspecialchars($term) ?>">
                    <div class="exm-swipe-actions">
                        <a class="exm-action exm-action-edit" href="/admin/expenses/edit/<?= $e['id'] ?>" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a class="exm-action exm-action-delete btn-delete-expense" href="/admin/expenses/delete/<?= $e['id'] ?>" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <div class="exm-card">
                        <div class="exm-card-top">
                            <span class="exm-avatar"><?= htmlspecialchars(exmInitials($e['description'])) ?></span>
                            <div class="exm-card-id">
                                <div class="exm-card-name"><?= htmlspecialchars($e['description']) ?></div>
                                <div class="exm-card-meta"><?= htmlspecialchars($e['congregation_name'] ?? 'Sede') ?> • <?= date('d/m', strtotime($e['expense_date'])) ?></div>
                            </div>
                            <button type="button" class="exm-kebab" aria-label="Ações"><i class="fas fa-ellipsis-vertical"></i></button>
                        </div>
                        <div class="exm-card-bottom">
                            <div>
                                <div class="exm-card-value-label">Valor</div>
                                <div class="exm-card-value">- R$ <?= number_format($e['amount'], 2, ',', '.') ?></div>
                                <?php if (isset($e['is_accountable']) && (int)$e['is_accountable'] === 0): ?>
                                    <div><span class="exm-unaccountable-pill">Não contabilizada</span></div>
                                <?php endif; ?>
                            </div>
                            <span class="exm-cat-pill"><?= htmlspecialchars($e['category'] ?: 'Outros') ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="exmLoadMore" class="exm-loadmore <?= count($expenses) > 10 ? '' : 'd-none' ?>">Ver mais</button>
        <div id="exmDone" class="exm-done <?= count($expenses) > 10 ? 'd-none' : '' ?>">Você viu tudo • <?= count($expenses) ?> lançamentos</div>
    <?php endif; ?>

    <?php
    $mobilePageFooterLabel = 'Saídas / Despesas';
    include __DIR__ . '/../../layout/mobile_page_footer.php';
    ?>

    <a href="/admin/expenses/create" class="exm-fab" aria-label="Nova saída"><i class="fas fa-minus"></i></a>
</div>

<div class="offcanvas offcanvas-bottom exm-sheet" tabindex="-1" id="exmFilterSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Filtros</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="/admin/expenses">
            <?php if ($exmShowCongFilter): ?>
                <div class="exm-filter-group">
                    <div class="exm-filter-label">Congregação</div>
                    <div class="exm-chip-row" data-chip-target="exmCongregationId">
                        <button type="button" class="exm-chip <?= empty($_GET['congregation_id']) ? 'active' : '' ?>" data-value="">Todas</button>
                        <?php foreach ($congregations as $c): ?>
                            <button type="button" class="exm-chip <?= (($_GET['congregation_id'] ?? '') == $c['id']) ? 'active' : '' ?>" data-value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="congregation_id" id="exmCongregationId" value="<?= htmlspecialchars($_GET['congregation_id'] ?? '') ?>">
                </div>
            <?php endif; ?>

            <div class="exm-filter-group">
                <div class="exm-filter-label">Data</div>
                <div class="exm-chip-row" id="exmDatePresets">
                    <button type="button" class="exm-chip" data-preset="all">Tudo</button>
                    <button type="button" class="exm-chip" data-preset="today">Hoje</button>
                    <button type="button" class="exm-chip" data-preset="7d">7 dias</button>
                    <button type="button" class="exm-chip" data-preset="month">Mês</button>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <label class="exm-mini-label">Início</label>
                        <input type="date" name="start_date" id="exmStartDate" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="exm-mini-label">Fim</label>
                        <input type="date" name="end_date" id="exmEndDate" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="/admin/expenses" class="btn btn-outline-secondary flex-fill rounded-pill">Limpar</a>
                <button type="submit" class="btn btn-dark flex-fill rounded-pill">Aplicar filtros</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var wrap = document.querySelector('.exm-wrap');
    if (!wrap) return;

    document.querySelectorAll('.exm-chip-row[data-chip-target]').forEach(function (row) {
        var hidden = document.getElementById(row.getAttribute('data-chip-target'));
        row.querySelectorAll('.exm-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                row.querySelectorAll('.exm-chip').forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                if (hidden) hidden.value = chip.getAttribute('data-value') || '';
            });
        });
    });

    var presets = document.getElementById('exmDatePresets');
    var startInput = document.getElementById('exmStartDate');
    var endInput = document.getElementById('exmEndDate');
    function fmt(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    if (presets) {
        presets.querySelectorAll('.exm-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                presets.querySelectorAll('.exm-chip').forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                var today = new Date();
                var preset = chip.getAttribute('data-preset');
                if (preset === 'all') {
                    startInput.value = '';
                    endInput.value = '';
                } else if (preset === 'today') {
                    startInput.value = fmt(today);
                    endInput.value = fmt(today);
                } else if (preset === '7d') {
                    var d7 = new Date(today); d7.setDate(d7.getDate() - 6);
                    startInput.value = fmt(d7);
                    endInput.value = fmt(today);
                } else if (preset === 'month') {
                    startInput.value = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
                    endInput.value = fmt(today);
                }
            });
        });
    }

    var searchInput = document.getElementById('exmSearchInput');
    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var term = normalize(searchInput.value.trim());
            document.querySelectorAll('#exmCardList .exm-swipe').forEach(function (card) {
                var match = term === '' || normalize(card.getAttribute('data-term')).indexOf(term) !== -1;
                card.style.display = match ? '' : 'none';
            });
        });
    }

    var loadMoreBtn = document.getElementById('exmLoadMore');
    var doneLabel = document.getElementById('exmDone');
    var BATCH = 10;
    function revealMore() {
        var hidden = document.querySelectorAll('#exmCardList .exm-swipe.exm-hidden');
        for (var i = 0; i < BATCH && i < hidden.length; i++) {
            hidden[i].classList.remove('exm-hidden');
        }
        if (hidden.length <= BATCH) {
            if (loadMoreBtn) loadMoreBtn.classList.add('d-none');
            if (doneLabel) doneLabel.classList.remove('d-none');
        }
    }
    if (loadMoreBtn) loadMoreBtn.addEventListener('click', revealMore);

    document.querySelectorAll('.exm-swipe').forEach(function (swipe) {
        var card = swipe.querySelector('.exm-card');
        var actionsWidth = swipe.querySelectorAll('.exm-action').length * 56;
        var startX = 0, currentX = 0, dragging = false, open = false;

        function setOpen(state) {
            open = state;
            card.style.transform = open ? 'translateX(-' + actionsWidth + 'px)' : 'translateX(0)';
        }
        function closeOthers() {
            document.querySelectorAll('.exm-swipe .exm-card').forEach(function (c) {
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

        var kebab = swipe.querySelector('.exm-kebab');
        if (kebab) {
            kebab.addEventListener('click', function (e) {
                e.stopPropagation();
                closeOthers();
                setOpen(!open);
            });
        }
    });
    // Note: delete confirmation for .btn-delete-expense is wired via the page's own
    // document.querySelectorAll('.btn-delete-expense')... handler (runs on $(document).ready,
    // after this partial's DOM is already present) — no need to repeat it here.
})();
</script>

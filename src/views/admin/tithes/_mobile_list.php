<?php
// src/views/admin/tithes/_mobile_list.php
// Mobile/tablet (<992px) card-based presentation of the same $tithes/$congregations
// data already loaded by TitheController::index(). Desktop keeps the classic
// table/tabs untouched (hidden here via d-none d-lg-block on the mobile block itself).

$tmTotalSum = 0.0;
$tmTitheSum = 0.0;
$tmSparkDays = [];
for ($i = 6; $i >= 0; $i--) {
    $tmSparkDays[date('Y-m-d', strtotime("-$i day"))] = 0.0;
}
foreach ($tithes as $t) {
    $amount = (float)$t['amount'];
    $tmTotalSum += $amount;
    if (($t['type'] ?? 'Dízimo') === 'Dízimo') $tmTitheSum += $amount;
    $d = substr((string)$t['payment_date'], 0, 10);
    if (isset($tmSparkDays[$d])) $tmSparkDays[$d] += $amount;
}
$tmTithePct = $tmTotalSum > 0 ? round(($tmTitheSum / $tmTotalSum) * 100) : 0;

$tmSparkMax = max($tmSparkDays) ?: 1;
$tmSparkPoints = [];
$tmSparkCount = count($tmSparkDays);
$tmI = 0;
foreach ($tmSparkDays as $v) {
    $x = $tmSparkCount > 1 ? ($tmI / ($tmSparkCount - 1)) * 100 : 0;
    $y = 28 - (($v / $tmSparkMax) * 24);
    $tmSparkPoints[] = round($x, 1) . ',' . round($y, 1);
    $tmI++;
}
$tmSparkPolyline = implode(' ', $tmSparkPoints);

$tmHasMultiCong = count($congregations) > 1;
$tmActiveFilters = 0;
if (!empty($_GET['congregation_id'])) $tmActiveFilters++;
if (!empty($_GET['start_date']) || !empty($_GET['end_date'])) $tmActiveFilters++;
if (!empty($_GET['type'])) $tmActiveFilters++;

function tmInitials($name) {
    $parts = preg_split('/\s+/', trim((string)$name));
    $out = '';
    if (!empty($parts[0])) $out .= mb_substr($parts[0], 0, 1);
    if (count($parts) > 1) $out .= mb_substr(end($parts), 0, 1);
    return mb_strtoupper($out !== '' ? $out : '?');
}
?>
<style>
    .tm-wrap { padding-bottom: 90px; }
    .tm-stats { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: .9rem; }
    .tm-stat { background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .8rem .9rem; overflow: hidden; }
    .tm-stat-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; margin-bottom: .3rem; }
    .tm-stat-value { font-size: 1.05rem; font-weight: 800; color: #16213e; }
    .tm-stat-sub { font-size: .72rem; color: #198754; font-weight: 700; margin-top: .1rem; }
    .tm-spark { width: 100%; height: 26px; margin-top: .35rem; }
    .tm-spark polyline { fill: none; stroke: #0d6efd; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .tm-searchrow { display: flex; gap: .5rem; margin-bottom: .7rem; }
    .tm-search { position: relative; flex: 1 1 auto; min-width: 0; }
    .tm-search i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .tm-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #fff; border-radius: 12px; padding: .55rem .8rem .55rem 2.3rem; font-size: .85rem; }
    .tm-search input:focus { outline: none; border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.12); }
    .tm-filter-btn { position: relative; flex: 0 0 auto; border: none; background: #16213e; color: #fff; border-radius: 12px; padding: 0 1rem; font-size: .82rem; font-weight: 700; }
    .tm-filter-badge { position: absolute; top: -6px; right: -6px; background: #dc3545; color: #fff; border-radius: 999px; min-width: 18px; height: 18px; font-size: .64rem; display: flex; align-items: center; justify-content: center; padding: 0 3px; }
    .tm-section-label { font-size: .74rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #8b93a7; margin: 0 0 .55rem 2px; }
    .tm-swipe { position: relative; overflow: hidden; border-radius: 16px; margin-bottom: .6rem; }
    .tm-swipe.tm-hidden { display: none; }
    .tm-swipe-actions { position: absolute; top: 0; right: 0; bottom: 0; display: flex; align-items: stretch; }
    .tm-action { width: 56px; border: none; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; text-decoration: none; }
    .tm-action-view { background: #0891b2; }
    .tm-action-edit { background: #0d6efd; }
    .tm-action-delete { background: #dc3545; }
    .tm-card { position: relative; z-index: 1; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .8rem .9rem; transition: transform .18s ease; touch-action: pan-y; }
    .tm-card-top { display: flex; align-items: center; gap: .6rem; }
    .tm-avatar { flex: 0 0 auto; width: 38px; height: 38px; border-radius: 50%; background: rgba(13,110,253,.12); color: #0d6efd; font-weight: 800; font-size: .78rem; display: flex; align-items: center; justify-content: center; }
    .tm-card-id { min-width: 0; flex: 1 1 auto; }
    .tm-card-name { font-weight: 700; font-size: .87rem; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tm-card-meta { font-size: .72rem; color: #8b93a7; }
    .tm-kebab { flex: 0 0 auto; width: 30px; height: 30px; border: none; background: transparent; color: #adb5bd; border-radius: 50%; }
    .tm-card-bottom { display: flex; align-items: flex-end; justify-content: space-between; margin-top: .55rem; }
    .tm-card-value-label { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; }
    .tm-card-value { font-size: 1rem; font-weight: 800; color: #198754; }
    .tm-origin-pill { background: #eef0f2; color: #495057; font-size: .68rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px; }
    .tm-loadmore { display: block; width: 100%; background: #fff; border: 1px solid rgba(17,24,39,.1); color: #16213e; font-weight: 700; font-size: .85rem; border-radius: 12px; padding: .65rem; margin-top: .3rem; }
    .tm-done { text-align: center; font-size: .76rem; color: #adb5bd; font-weight: 600; padding: .6rem 0 1rem; }
    .tm-fab { position: fixed; right: 1rem; bottom: calc(1rem + env(safe-area-inset-bottom)); width: 54px; height: 54px; border-radius: 50%; background: #16a34a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 6px 16px rgba(22,163,74,.35); z-index: 1030; text-decoration: none; }
    .tm-sheet .offcanvas-header { border-bottom: 1px solid rgba(0,0,0,.06); }
    .tm-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 92vh; }
    .tm-filter-group { margin-bottom: 1.1rem; }
    .tm-filter-label { font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; margin-bottom: .5rem; }
    .tm-mini-label { font-size: .7rem; font-weight: 700; color: #8b93a7; }
    .tm-chip-row { display: flex; flex-wrap: wrap; gap: .45rem; }
    .tm-chip { border: 1px solid rgba(17,24,39,.1); background: #fff; color: #495057; border-radius: 999px; padding: .4rem .9rem; font-size: .8rem; font-weight: 700; }
    .tm-chip.active { background: #16213e; border-color: #16213e; color: #fff; }
    .tm-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }
</style>

<div class="tm-wrap d-lg-none">
    <?php
    $mobilePageCategory = 'Financeiro';
    $mobilePageTitle = 'Dízimos e Ofertas';
    include __DIR__ . '/../../layout/mobile_page_header.php';
    ?>

    <?php if (!empty($tithes)): ?>
        <div class="tm-stats">
            <div class="tm-stat">
                <div class="tm-stat-label"><i class="fas fa-arrow-trend-up me-1"></i>Total exibido</div>
                <div class="tm-stat-value">R$ <?= number_format($tmTotalSum, 2, ',', '.') ?></div>
                <svg class="tm-spark" viewBox="0 0 100 28" preserveAspectRatio="none"><polyline points="<?= htmlspecialchars($tmSparkPolyline) ?>"></polyline></svg>
            </div>
            <div class="tm-stat">
                <div class="tm-stat-label"><i class="fas fa-hand-holding-usd me-1"></i>Dízimos</div>
                <div class="tm-stat-value">R$ <?= number_format($tmTitheSum, 2, ',', '.') ?></div>
                <div class="tm-stat-sub"><?= (int)$tmTithePct ?>% do total</div>
            </div>
        </div>
    <?php endif; ?>

    <div class="tm-searchrow">
        <div class="tm-search">
            <i class="fas fa-search"></i>
            <input type="text" id="tmSearchInput" placeholder="Buscar membro...">
        </div>
        <button type="button" class="tm-filter-btn" data-bs-toggle="offcanvas" data-bs-target="#tmFilterSheet">
            <i class="fas fa-sliders-h me-1"></i>Filtros
            <?php if ($tmActiveFilters > 0): ?><span class="tm-filter-badge"><?= $tmActiveFilters ?></span><?php endif; ?>
        </button>
    </div>

    <div class="tm-section-label">Lançamentos (<?= count($tithes) ?>)</div>

    <?php if (empty($tithes)): ?>
        <div class="tm-empty">Nenhum lançamento encontrado.</div>
    <?php else: ?>
        <div id="tmCardList">
            <?php foreach ($tithes as $idx => $t):
                if (!empty($t['member_name'])) {
                    $tmName = $t['member_name'];
                } elseif (!empty($t['giver_name'])) {
                    $tmName = $t['giver_name'] . ' (Visitante)';
                } else {
                    $tmName = 'Não identificado';
                }
                $tmTerm = mb_strtolower($tmName . ' ' . ($t['congregation_name'] ?? ''), 'UTF-8');
            ?>
                <div class="tm-swipe <?= $idx >= 10 ? 'tm-hidden' : '' ?>" data-term="<?= htmlspecialchars($tmTerm) ?>">
                    <div class="tm-swipe-actions">
                        <button type="button" class="tm-action tm-action-view" data-bs-toggle="modal" data-bs-target="#receiptModal" data-url="/admin/tithes/receipt/<?= $t['id'] ?>" title="Ver recibo">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a class="tm-action tm-action-edit" href="/admin/tithes/edit/<?= $t['id'] ?>" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a class="tm-action tm-action-delete btn-delete-tithe" href="/admin/tithes/delete/<?= $t['id'] ?>" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <div class="tm-card">
                        <div class="tm-card-top">
                            <span class="tm-avatar"><?= htmlspecialchars(tmInitials($tmName)) ?></span>
                            <div class="tm-card-id">
                                <div class="tm-card-name"><?= htmlspecialchars($tmName) ?></div>
                                <div class="tm-card-meta"><?= htmlspecialchars($t['congregation_name'] ?? 'Sede') ?> • <?= date('d/m', strtotime($t['payment_date'])) ?></div>
                            </div>
                            <button type="button" class="tm-kebab" aria-label="Ações"><i class="fas fa-ellipsis-vertical"></i></button>
                        </div>
                        <div class="tm-card-bottom">
                            <div>
                                <div class="tm-card-value-label">Valor</div>
                                <div class="tm-card-value">R$ <?= number_format($t['amount'], 2, ',', '.') ?></div>
                            </div>
                            <span class="tm-origin-pill"><?= htmlspecialchars(ucfirst($t['payment_method'] ?? '-')) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="tmLoadMore" class="tm-loadmore <?= count($tithes) > 10 ? '' : 'd-none' ?>">Ver mais</button>
        <div id="tmDone" class="tm-done <?= count($tithes) > 10 ? 'd-none' : '' ?>">Você viu tudo • <?= count($tithes) ?> lançamentos</div>
    <?php endif; ?>

    <?php
    $mobilePageFooterLabel = 'Dízimos e Ofertas';
    include __DIR__ . '/../../layout/mobile_page_footer.php';
    ?>

    <a href="/admin/tithes/create" class="tm-fab" aria-label="Novo lançamento"><i class="fas fa-plus"></i></a>
</div>

<div class="offcanvas offcanvas-bottom tm-sheet" tabindex="-1" id="tmFilterSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Filtros</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="/admin/tithes">
            <?php if ($tmHasMultiCong): ?>
                <div class="tm-filter-group">
                    <div class="tm-filter-label">Congregação</div>
                    <div class="tm-chip-row" data-chip-target="tmCongregationId">
                        <button type="button" class="tm-chip <?= empty($_GET['congregation_id']) ? 'active' : '' ?>" data-value="">Todas</button>
                        <?php foreach ($congregations as $c): ?>
                            <button type="button" class="tm-chip <?= (($_GET['congregation_id'] ?? '') == $c['id']) ? 'active' : '' ?>" data-value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="congregation_id" id="tmCongregationId" value="<?= htmlspecialchars($_GET['congregation_id'] ?? '') ?>">
                </div>
            <?php endif; ?>

            <div class="tm-filter-group">
                <div class="tm-filter-label">Data</div>
                <div class="tm-chip-row" id="tmDatePresets">
                    <button type="button" class="tm-chip" data-preset="all">Tudo</button>
                    <button type="button" class="tm-chip" data-preset="today">Hoje</button>
                    <button type="button" class="tm-chip" data-preset="7d">7 dias</button>
                    <button type="button" class="tm-chip" data-preset="month">Mês</button>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <label class="tm-mini-label">Início</label>
                        <input type="date" name="start_date" id="tmStartDate" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="tm-mini-label">Fim</label>
                        <input type="date" name="end_date" id="tmEndDate" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="tm-filter-group">
                <div class="tm-filter-label">Tipo</div>
                <div class="tm-chip-row" data-chip-target="tmType">
                    <button type="button" class="tm-chip <?= empty($_GET['type']) ? 'active' : '' ?>" data-value="">Todos</button>
                    <button type="button" class="tm-chip <?= (($_GET['type'] ?? '') === 'Dízimo') ? 'active' : '' ?>" data-value="Dízimo">Dízimo</button>
                    <button type="button" class="tm-chip <?= (($_GET['type'] ?? '') === 'Oferta') ? 'active' : '' ?>" data-value="Oferta">Oferta</button>
                </div>
                <input type="hidden" name="type" id="tmType" value="<?= htmlspecialchars($_GET['type'] ?? '') ?>">
            </div>

            <input type="hidden" name="member_name" value="<?= htmlspecialchars($_GET['member_name'] ?? '') ?>">

            <div class="d-flex gap-2 mt-3">
                <a href="/admin/tithes" class="btn btn-outline-secondary flex-fill rounded-pill">Limpar</a>
                <button type="submit" class="btn btn-dark flex-fill rounded-pill">Aplicar filtros</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var wrap = document.querySelector('.tm-wrap');
    if (!wrap) return;

    // Chip groups that write into a hidden input
    document.querySelectorAll('.tm-chip-row[data-chip-target]').forEach(function (row) {
        var hidden = document.getElementById(row.getAttribute('data-chip-target'));
        row.querySelectorAll('.tm-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                row.querySelectorAll('.tm-chip').forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                if (hidden) hidden.value = chip.getAttribute('data-value') || '';
            });
        });
    });

    // Date presets
    var presets = document.getElementById('tmDatePresets');
    var startInput = document.getElementById('tmStartDate');
    var endInput = document.getElementById('tmEndDate');
    function fmt(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    if (presets) {
        presets.querySelectorAll('.tm-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                presets.querySelectorAll('.tm-chip').forEach(function (c) { c.classList.remove('active'); });
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

    // Instant client-side search over already-rendered cards
    var searchInput = document.getElementById('tmSearchInput');
    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var term = normalize(searchInput.value.trim());
            document.querySelectorAll('#tmCardList .tm-swipe').forEach(function (card) {
                var match = term === '' || normalize(card.getAttribute('data-term')).indexOf(term) !== -1;
                card.classList.toggle('tm-search-hide', !match);
                card.style.display = match ? '' : 'none';
            });
        });
    }

    // "Ver mais" reveals the next batch of already-rendered cards
    var loadMoreBtn = document.getElementById('tmLoadMore');
    var doneLabel = document.getElementById('tmDone');
    var BATCH = 10;
    function revealMore() {
        var hidden = document.querySelectorAll('#tmCardList .tm-swipe.tm-hidden');
        for (var i = 0; i < BATCH && i < hidden.length; i++) {
            hidden[i].classList.remove('tm-hidden');
        }
        if (hidden.length <= BATCH) {
            if (loadMoreBtn) loadMoreBtn.classList.add('d-none');
            if (doneLabel) doneLabel.classList.remove('d-none');
        }
    }
    if (loadMoreBtn) loadMoreBtn.addEventListener('click', revealMore);

    // Swipe-to-reveal quick actions (iOS Mail style), plus a kebab fallback for non-touch
    document.querySelectorAll('.tm-swipe').forEach(function (swipe) {
        var card = swipe.querySelector('.tm-card');
        var actionsWidth = swipe.querySelectorAll('.tm-action').length * 56;
        var startX = 0, currentX = 0, dragging = false, open = false;

        function setOpen(state) {
            open = state;
            card.style.transform = open ? 'translateX(-' + actionsWidth + 'px)' : 'translateX(0)';
        }
        function closeOthers() {
            document.querySelectorAll('.tm-swipe .tm-card').forEach(function (c) {
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

        var kebab = swipe.querySelector('.tm-kebab');
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

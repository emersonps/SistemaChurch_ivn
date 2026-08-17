<?php
// src/views/admin/ebd/classes/_mobile_list.php
// Mobile/tablet (<992px) card-based presentation of the same $classes data already
// loaded by EbdController::index(). Desktop keeps the classic card grid untouched
// (hidden via d-none d-lg-flex there).

$ebmActiveCount = 0;
$ebmInactiveCount = 0;
foreach ($classes as $c) {
    if (($c['status'] ?? '') === 'active') $ebmActiveCount++; else $ebmInactiveCount++;
}

$mobilePageCategory = 'Ensino';
$mobilePageTitle = null;
include __DIR__ . '/../../../layout/mobile_page_header.php';
?>
<style>
    .ebm-wrap { padding-bottom: 90px; }
    .ebm-toolbar { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .ebm-search-btn { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(17,24,39,.1); background: #fff; color: #16213e; }
    .ebm-search-btn.is-active { background: #16213e; color: #fff; border-color: #16213e; }
    .ebm-toolbar-btn { flex: 1 1 0; display: flex; align-items: center; justify-content: center; gap: .4rem; border-radius: 12px; padding: 0 .6rem; font-size: .78rem; font-weight: 700; text-decoration: none; white-space: nowrap; }
    .ebm-toolbar-btn.is-outline { border: 1px solid rgba(17,24,39,.1); background: #fff; color: #16213e; }
    .ebm-fab { position: fixed; right: 1rem; bottom: calc(1rem + env(safe-area-inset-bottom)); width: 54px; height: 54px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 6px 16px rgba(37,99,235,.3); z-index: 1030; text-decoration: none; }

    .ebm-search { position: relative; margin-bottom: 1rem; }
    .ebm-search i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .ebm-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #fff; border-radius: 12px; padding: .55rem .8rem .55rem 2.3rem; font-size: .85rem; }
    .ebm-search input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }

    .ebm-segmented { display: flex; background: #f1f3f7; border-radius: 999px; padding: .25rem; margin-bottom: 1rem; }
    .ebm-seg-btn { flex: 1 1 0; border: none; background: transparent; color: #6c757d; font-weight: 700; font-size: .78rem; padding: .5rem .3rem; border-radius: 999px; white-space: nowrap; }
    .ebm-seg-btn.active { background: #fff; color: #16213e; box-shadow: 0 2px 6px rgba(17,24,39,.08); }

    .ebm-card { background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .9rem 1rem; margin-bottom: .7rem; }
    .ebm-card.ebm-hidden { display: none; }
    .ebm-card-top { display: flex; align-items: center; gap: .6rem; }
    .ebm-icon { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 12px; background: rgba(37,99,235,.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .ebm-card-id { min-width: 0; flex: 1 1 auto; cursor: pointer; }
    .ebm-card-name { font-weight: 800; font-size: .92rem; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ebm-delete-btn { flex: 0 0 auto; width: 30px; height: 30px; border-radius: 50%; border: none; background: transparent; color: #ced4da; }
    .ebm-chev { flex: 0 0 auto; color: #ced4da; }

    .ebm-tags { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-top: .55rem; }
    .ebm-tag { font-size: .68rem; font-weight: 700; padding: .22rem .6rem; border-radius: 999px; }
    .ebm-tag.is-active { background: rgba(22,163,74,.12); color: #16a34a; }
    .ebm-tag.is-inactive { background: rgba(0,0,0,.06); color: #6c757d; }
    .ebm-tag.is-neutral { background: #eef0f2; color: #6c757d; }
    .ebm-meta-text { font-size: .74rem; color: #8b93a7; }

    .ebm-card-bottom { display: flex; align-items: center; justify-content: space-between; gap: .6rem; margin-top: .8rem; padding-top: .75rem; border-top: 1px dashed rgba(17,24,39,.08); }
    .ebm-students { display: flex; align-items: center; gap: .35rem; font-size: .76rem; font-weight: 700; color: #16213e; }
    .ebm-lessonbtn { flex: 0 0 auto; width: 32px; height: 32px; border-radius: 50%; background: #16a34a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: .82rem; text-decoration: none; }

    .ebm-pagerow { display: flex; align-items: center; justify-content: center; gap: .8rem; margin-top: .8rem; }
    .ebm-pager-arrow { flex: 0 0 auto; width: 34px; height: 34px; border-radius: 50%; border: 1px solid rgba(17,24,39,.1); background: #fff; color: #16213e; }
    .ebm-pager-arrow:disabled { opacity: .35; }
    .ebm-dots { display: flex; gap: .35rem; }
    .ebm-dot { width: 6px; height: 6px; border-radius: 50%; background: rgba(17,24,39,.15); }
    .ebm-dot.active { background: #16213e; width: 16px; border-radius: 4px; }
    .ebm-pager-count { text-align: center; font-size: .7rem; font-weight: 700; color: #8b93a7; margin-top: .5rem; }

    .ebm-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }

    .ebm-cong-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 92vh; }
    .ebm-cong-chip { display: flex; align-items: center; gap: .55rem; width: 100%; text-align: left; border: 1px solid rgba(17,24,39,.08); background: #fff; color: #16213e; font-size: .84rem; font-weight: 600; padding: .65rem .9rem; border-radius: 12px; margin-bottom: .5rem; text-decoration: none; }
    .ebm-cong-chip.active { border-color: #2563eb; background: rgba(37,99,235,.06); color: #2563eb; font-weight: 700; }
    .ebm-cong-chip i:first-child { color: #8b93a7; width: 1.1em; }
    .ebm-cong-chip.active i:first-child { color: #2563eb; }
</style>

<div class="ebm-wrap d-lg-none">
    <div class="ebm-toolbar">
        <button type="button" class="ebm-search-btn" id="ebmSearchToggle" aria-label="Buscar"><i class="fas fa-search"></i></button>
        <?php if (!empty($congregations)): ?>
            <button type="button" class="ebm-search-btn <?= $selected_congregation_id ? 'is-active' : '' ?>" data-bs-toggle="offcanvas" data-bs-target="#ebmCongSheet" aria-label="Filtrar por congregação"><i class="fas fa-church"></i></button>
        <?php endif; ?>
        <a href="/admin/ebd/reports" class="ebm-toolbar-btn is-outline"><i class="fas fa-chart-bar"></i> Relatórios</a>
    </div>

    <div class="ebm-search d-none" id="ebmSearchRow">
        <i class="fas fa-search"></i>
        <input type="text" id="ebmSearchInput" placeholder="Buscar classe, professor...">
    </div>

    <div class="ebm-segmented" id="ebmSegmented">
        <button type="button" class="ebm-seg-btn active" data-status="">Todas</button>
        <button type="button" class="ebm-seg-btn" data-status="active">Ativas (<?= $ebmActiveCount ?>)</button>
        <button type="button" class="ebm-seg-btn" data-status="inactive">Inativas (<?= $ebmInactiveCount ?>)</button>
    </div>

    <?php if (empty($classes)): ?>
        <div class="ebm-empty">Nenhuma classe cadastrada.</div>
    <?php else: ?>
        <div id="ebmCardList">
            <?php foreach ($classes as $idx => $class):
                $isActive = ($class['status'] ?? '') === 'active';
                $teachers = trim((string)($class['teachers_names'] ?? ''));
                $term = mb_strtolower($class['name'] . ' ' . $teachers . ' ' . ($class['congregation_name'] ?? ''), 'UTF-8');
            ?>
                <div class="ebm-card" data-status="<?= $isActive ? 'active' : 'inactive' ?>" data-term="<?= htmlspecialchars($term) ?>" data-page="<?= (int)floor($idx / 3) ?>">
                    <div class="ebm-card-top">
                        <span class="ebm-icon"><i class="fas fa-book-reader"></i></span>
                        <div class="ebm-card-id" onclick="window.location.href='/admin/ebd/classes/show/<?= $class['id'] ?>'">
                            <div class="ebm-card-name"><?= htmlspecialchars($class['name']) ?></div>
                        </div>
                        <button type="button" class="ebm-delete-btn" data-name="<?= htmlspecialchars($class['name']) ?>" data-href="/admin/ebd/classes/delete/<?= $class['id'] ?>" aria-label="Excluir"><i class="fas fa-trash"></i></button>
                        <i class="fas fa-chevron-right ebm-chev"></i>
                    </div>
                    <div class="ebm-tags">
                        <span class="ebm-tag <?= $isActive ? 'is-active' : 'is-inactive' ?>"><?= $isActive ? 'Ativa' : 'Inativa' ?></span>
                        <span class="ebm-meta-text"><i class="fas fa-church me-1"></i><?= htmlspecialchars($class['congregation_name'] ?? 'Todas') ?></span>
                    </div>
                    <div class="ebm-tags">
                        <span class="ebm-tag is-neutral">Faixa <?= $class['min_age'] ?? 0 ?> a <?= $class['max_age'] ?? 99 ?> anos</span>
                        <span class="ebm-meta-text"><?= $teachers !== '' ? 'Prof. ' . htmlspecialchars($teachers) : 'Sem professor' ?></span>
                    </div>
                    <div class="ebm-card-bottom">
                        <span class="ebm-students"><i class="fas fa-user-group"></i> <?= (int)$class['students_count'] ?> alunos</span>
                        <a href="/admin/ebd/lessons/create/<?= $class['id'] ?>" class="ebm-lessonbtn" aria-label="Lançar aula" onclick="event.stopPropagation()"><i class="fas fa-clipboard-check"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="ebm-pagerow">
            <button type="button" class="ebm-pager-arrow" id="ebmPrev" aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>
            <div class="ebm-dots" id="ebmDots"></div>
            <button type="button" class="ebm-pager-arrow" id="ebmNext" aria-label="Próximo"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="ebm-pager-count" id="ebmPagerCount"></div>
    <?php endif; ?>

    <?php
    $mobilePageFooterLabel = 'EBD';
    include __DIR__ . '/../../../layout/mobile_page_footer.php';
    ?>

    <a href="/admin/ebd/classes/create" class="ebm-fab" aria-label="Nova classe"><i class="fas fa-plus"></i></a>
</div>

<?php if (!empty($congregations)): ?>
<div class="offcanvas offcanvas-bottom ebm-cong-sheet" tabindex="-1" id="ebmCongSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Congregação</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <a href="/admin/ebd/classes" class="ebm-cong-chip <?= !$selected_congregation_id ? 'active' : '' ?>">
            <i class="fas fa-globe"></i> Todas as congregações
        </a>
        <?php foreach ($congregations as $cong): ?>
            <a href="/admin/ebd/classes?congregation_id=<?= (int)$cong['id'] ?>" class="ebm-cong-chip <?= (string)$selected_congregation_id === (string)$cong['id'] ? 'active' : '' ?>">
                <i class="fas fa-church"></i> <?= htmlspecialchars($cong['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    var wrap = document.querySelector('.ebm-wrap');
    if (!wrap) return;

    var PAGE_SIZE = 3;
    var activeStatus = '';

    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    function matchingCards() {
        var term = normalize(document.getElementById('ebmSearchInput') ? document.getElementById('ebmSearchInput').value.trim() : '');
        return Array.prototype.filter.call(document.querySelectorAll('#ebmCardList .ebm-card'), function (card) {
            var matchesTerm = term === '' || normalize(card.getAttribute('data-term')).indexOf(term) !== -1;
            var matchesStatus = activeStatus === '' || card.getAttribute('data-status') === activeStatus;
            return matchesTerm && matchesStatus;
        });
    }

    var currentPage = 0;
    var dotsWrap = document.getElementById('ebmDots');
    var countEl = document.getElementById('ebmPagerCount');
    var prevBtn = document.getElementById('ebmPrev');
    var nextBtn = document.getElementById('ebmNext');

    function render() {
        var matches = matchingCards();
        var totalPages = Math.max(1, Math.ceil(matches.length / PAGE_SIZE));
        if (currentPage >= totalPages) currentPage = totalPages - 1;
        if (currentPage < 0) currentPage = 0;

        document.querySelectorAll('#ebmCardList .ebm-card').forEach(function (card) {
            card.classList.add('ebm-hidden');
        });
        matches.forEach(function (card, i) {
            if (Math.floor(i / PAGE_SIZE) === currentPage) card.classList.remove('ebm-hidden');
        });

        if (dotsWrap) {
            dotsWrap.innerHTML = '';
            for (var p = 0; p < totalPages; p++) {
                var dot = document.createElement('span');
                dot.className = 'ebm-dot' + (p === currentPage ? ' active' : '');
                dotsWrap.appendChild(dot);
            }
        }
        if (countEl) {
            countEl.textContent = matches.length
                ? (currentPage + 1) + ' DE ' + totalPages + ' • ' + matches.length + ' NO TOTAL'
                : 'Nenhum resultado';
        }
        if (prevBtn) prevBtn.disabled = currentPage <= 0;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages - 1;
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { currentPage--; render(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { currentPage++; render(); });

    var searchToggle = document.getElementById('ebmSearchToggle');
    var searchRow = document.getElementById('ebmSearchRow');
    var searchInput = document.getElementById('ebmSearchInput');
    if (searchToggle) {
        searchToggle.addEventListener('click', function () {
            var open = searchRow.classList.contains('d-none');
            searchRow.classList.toggle('d-none', !open);
            searchToggle.classList.toggle('is-active', open);
            if (open) searchInput.focus();
        });
    }
    if (searchInput) {
        searchInput.addEventListener('input', function () { currentPage = 0; render(); });
    }

    var segmented = document.getElementById('ebmSegmented');
    if (segmented) {
        segmented.querySelectorAll('.ebm-seg-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                segmented.querySelectorAll('.ebm-seg-btn').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                activeStatus = btn.getAttribute('data-status') || '';
                currentPage = 0;
                render();
            });
        });
    }

    document.querySelectorAll('.ebm-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var href = btn.getAttribute('data-href');
            var name = btn.getAttribute('data-name');
            Swal.fire({
                title: 'Excluir classe?',
                text: 'Tem certeza que deseja excluir "' + name + '"? Se houver alunos ou aulas, a exclusão será bloqueada.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) window.location.replace(href);
            });
        });
    });

    render();
})();
</script>

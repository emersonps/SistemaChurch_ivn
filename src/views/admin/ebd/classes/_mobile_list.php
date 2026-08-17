<?php
// src/views/admin/ebd/classes/_mobile_list.php
// Mobile/tablet (<992px) card-based presentation of the same $classes data already
// loaded by EbdController::index(). Desktop keeps the classic card grid untouched
// (hidden via d-none d-lg-flex there).
// Visual language matches the EBD Mobile design handoff (navy/green/blue palette),
// distinct from the rest of the admin's blue mobile theme — see design_handoff_ebd_mobile.

$ebmActiveCount = 0;
foreach ($classes as $c) {
    if (($c['status'] ?? '') === 'active') $ebmActiveCount++;
}
$ebmTotalCount = count($classes);
?>
<style>
    .ebm-wrap { padding: 20px 18px 90px; }
    .ebm-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .ebm-head-left { display: flex; align-items: center; gap: 10px; }
    .ebm-back { flex: 0 0 auto; width: 34px; height: 34px; border-radius: 50%; background: #fff; border: 1px solid #eef1f5; color: #101828; display: flex; align-items: center; justify-content: center; }
    .ebm-head-title { font-size: 22px; font-weight: 800; color: #101828; }
    .ebm-head-pill { background: #10162b; color: #fff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px; }
    .ebm-reports-btn { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; color: #101828; font-size: 1.05rem; text-decoration: none; }

    .ebm-search { position: relative; margin-bottom: 14px; }
    .ebm-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9aa4b2; font-size: .82rem; }
    .ebm-search input { width: 100%; border: none; background: #eef1f4; border-radius: 14px; padding: 12px 14px 12px 2.4rem; font-size: 14px; color: #101828; }
    .ebm-search input::placeholder { color: #9aa4b2; }
    .ebm-search input:focus { outline: none; box-shadow: 0 0 0 2px rgba(24,165,88,.25); }

    .ebm-filters { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
    .ebm-seg-btn { border: none; font-weight: 700; font-size: 13px; padding: 8px 20px; border-radius: 999px; background: #e7e9ee; color: #5b6472; }
    .ebm-seg-btn.active { background: #10162b; color: #fff; }
    .ebm-cong-btn { flex: 0 0 auto; width: 38px; height: 38px; border-radius: 50%; border: 1px solid #eef1f5; background: #fff; color: #5b6472; display: flex; align-items: center; justify-content: center; margin-left: auto; }
    .ebm-cong-btn.is-active { background: #10162b; border-color: #10162b; color: #fff; }

    .ebm-swipe { position: relative; margin-bottom: 12px; border-radius: 16px; overflow: hidden; }
    .ebm-swipe.ebm-hidden { display: none; }
    .ebm-swipe-actions { position: absolute; top: 0; right: 0; bottom: 0; display: flex; }
    .ebm-swipe-actions .ebm-action { width: 64px; display: flex; align-items: center; justify-content: center; color: #fff; background: #dc3545; text-decoration: none; font-size: 1rem; }
    .ebm-card { position: relative; z-index: 1; background: #fff; border: 1px solid #eef1f5; border-radius: 16px; padding: 14px 16px; touch-action: pan-y; }
    .ebm-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px; }
    .ebm-card-name { font-weight: 800; font-size: 15px; color: #101828; }
    .ebm-status { display: flex; align-items: center; gap: 5px; flex: 0 0 auto; }
    .ebm-status-dot { width: 6px; height: 6px; border-radius: 50%; background: #18a558; }
    .ebm-status.is-inactive .ebm-status-dot { background: #adb5bd; }
    .ebm-status-label { color: #18a558; font-size: 12px; font-weight: 700; }
    .ebm-status.is-inactive .ebm-status-label { color: #8b93a3; }
    .ebm-card-sub { color: #8b93a3; font-size: 12.5px; margin-bottom: 8px; }
    .ebm-card-bottom { display: flex; align-items: center; justify-content: space-between; }
    .ebm-card-meta { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .ebm-card-students { display: flex; align-items: center; gap: 5px; color: #5b6472; font-size: 12.5px; }
    .ebm-card-prof { color: #3b6fef; font-size: 12.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ebm-chev { color: #c2c8d2; font-size: 16px; flex: 0 0 auto; }

    .ebm-fab { position: fixed; right: 22px; bottom: calc(30px + env(safe-area-inset-bottom)); width: 56px; height: 56px; border-radius: 50%; background: #18a558; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.55rem; font-weight: 400; box-shadow: 0 10px 24px rgba(24,165,88,.4); z-index: 1030; text-decoration: none; }

    .ebm-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }

    .ebm-cong-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 85vh; }
    .ebm-cong-chip { display: flex; align-items: center; gap: .55rem; width: 100%; text-align: left; border: 1px solid #eef1f5; background: #fff; color: #101828; font-size: .84rem; font-weight: 600; padding: .65rem .9rem; border-radius: 12px; margin-bottom: .5rem; text-decoration: none; }
    .ebm-cong-chip.active { border-color: #10162b; background: rgba(16,22,43,.05); font-weight: 700; }
    .ebm-cong-chip i:first-child { color: #8b93a3; width: 1.1em; }
    .ebm-cong-chip.active i:first-child { color: #10162b; }
</style>

<div class="ebm-wrap d-lg-none">
    <div class="ebm-head">
        <div class="ebm-head-left">
            <button type="button" id="ebmBackBtn" class="ebm-back" data-fallback="<?= htmlspecialchars($mobileLauncherHref ?? '/admin?launcher=1') ?>" aria-label="Voltar"><i class="fas fa-arrow-left"></i></button>
            <span class="ebm-head-title">EBD</span>
            <span class="ebm-head-pill"><?= $ebmTotalCount ?> turma<?= $ebmTotalCount === 1 ? '' : 's' ?></span>
        </div>
        <a href="/admin/ebd/reports" class="ebm-reports-btn" aria-label="Relatórios"><i class="fas fa-chart-bar"></i></a>
    </div>

    <div class="ebm-search">
        <i class="fas fa-search"></i>
        <input type="text" id="ebmSearchInput" placeholder="Buscar turma...">
    </div>

    <div class="ebm-filters">
        <div class="d-flex gap-2" id="ebmSegmented">
            <button type="button" class="ebm-seg-btn active" data-status="">Todas</button>
            <button type="button" class="ebm-seg-btn" data-status="active">Ativas</button>
        </div>
        <?php if (!empty($congregations)): ?>
            <button type="button" class="ebm-cong-btn <?= $selected_congregation_id ? 'is-active' : '' ?>" data-bs-toggle="offcanvas" data-bs-target="#ebmCongSheet" aria-label="Filtrar por congregação"><i class="fas fa-church"></i></button>
        <?php endif; ?>
    </div>

    <?php if (empty($classes)): ?>
        <div class="ebm-empty">Nenhuma classe cadastrada.</div>
    <?php else: ?>
        <div id="ebmCardList">
            <?php foreach ($classes as $class):
                $isActive = ($class['status'] ?? '') === 'active';
                $teachers = trim((string)($class['teachers_names'] ?? ''));
                $term = mb_strtolower($class['name'] . ' ' . $teachers . ' ' . ($class['congregation_name'] ?? ''), 'UTF-8');
            ?>
                <div class="ebm-swipe" data-status="<?= $isActive ? 'active' : 'inactive' ?>" data-term="<?= htmlspecialchars($term) ?>">
                    <div class="ebm-swipe-actions">
                        <a href="/admin/ebd/classes/delete/<?= $class['id'] ?>" class="ebm-action ebm-delete-btn" data-name="<?= htmlspecialchars($class['name']) ?>" aria-label="Excluir"><i class="fas fa-trash"></i></a>
                    </div>
                    <div class="ebm-card" onclick="window.location.href='/admin/ebd/classes/show/<?= $class['id'] ?>'">
                        <div class="ebm-card-top">
                            <span class="ebm-card-name"><?= htmlspecialchars($class['name']) ?></span>
                            <span class="ebm-status <?= $isActive ? '' : 'is-inactive' ?>">
                                <span class="ebm-status-dot"></span>
                                <span class="ebm-status-label"><?= $isActive ? 'Ativa' : 'Inativa' ?></span>
                            </span>
                        </div>
                        <div class="ebm-card-sub"><?= htmlspecialchars($class['congregation_name'] ?? 'Todas') ?> • <?= $class['min_age'] ?? 0 ?>-<?= $class['max_age'] ?? 99 ?> anos</div>
                        <div class="ebm-card-bottom">
                            <div class="ebm-card-meta">
                                <span class="ebm-card-students"><i class="fas fa-user-group"></i> <?= (int)$class['students_count'] ?> alunos</span>
                                <span class="ebm-card-prof">Prof. <?= $teachers !== '' ? htmlspecialchars($teachers) : 'Nenhum' ?></span>
                            </div>
                            <i class="fas fa-chevron-right ebm-chev"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="ebm-empty d-none" id="ebmNoResults">Nenhuma classe encontrada.</div>
    <?php endif; ?>

    <a href="/admin/ebd/classes/create" class="ebm-fab" aria-label="Nova classe">+</a>
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

    var backBtn = document.getElementById('ebmBackBtn');
    if (backBtn) {
        backBtn.addEventListener('click', function () {
            var cameFromSameSite = document.referrer && document.referrer.indexOf(window.location.origin) === 0;
            if (cameFromSameSite && window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = backBtn.getAttribute('data-fallback');
            }
        });
    }

    var activeStatus = '';

    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    function render() {
        var term = normalize(document.getElementById('ebmSearchInput') ? document.getElementById('ebmSearchInput').value.trim() : '');
        var cards = Array.prototype.slice.call(document.querySelectorAll('#ebmCardList .ebm-swipe'));
        var visibleCount = 0;
        cards.forEach(function (card) {
            var matchesTerm = term === '' || normalize(card.getAttribute('data-term')).indexOf(term) !== -1;
            var matchesStatus = activeStatus === '' || card.getAttribute('data-status') === activeStatus;
            var show = matchesTerm && matchesStatus;
            card.classList.toggle('ebm-hidden', !show);
            if (show) visibleCount++;
        });
        var noResults = document.getElementById('ebmNoResults');
        if (noResults) noResults.classList.toggle('d-none', visibleCount > 0 || cards.length === 0);
    }

    var searchInput = document.getElementById('ebmSearchInput');
    if (searchInput) searchInput.addEventListener('input', render);

    var segmented = document.getElementById('ebmSegmented');
    if (segmented) {
        segmented.querySelectorAll('.ebm-seg-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                segmented.querySelectorAll('.ebm-seg-btn').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                activeStatus = btn.getAttribute('data-status') || '';
                render();
            });
        });
    }

    // Swipe-to-reveal delete action on class cards (same pattern used for students in classes/show.php)
    document.querySelectorAll('.ebm-swipe').forEach(function (swipe) {
        var card = swipe.querySelector('.ebm-card');
        var actionsWidth = swipe.querySelectorAll('.ebm-action').length * 64;
        var startX = 0, currentX = 0, dragging = false, open = false;

        function setOpen(state) {
            open = state;
            card.style.transform = open ? 'translateX(-' + actionsWidth + 'px)' : 'translateX(0)';
        }
        function closeOthers() {
            document.querySelectorAll('.ebm-swipe .ebm-card').forEach(function (c) {
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
    });

    document.querySelectorAll('.ebm-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var href = btn.getAttribute('href');
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
                if (result.isConfirmed) window.location.href = href;
            });
        });
    });
})();
</script>

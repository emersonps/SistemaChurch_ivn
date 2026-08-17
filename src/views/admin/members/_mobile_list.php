<?php
// src/views/admin/members/_mobile_list.php
// Mobile/tablet (<992px) card-based presentation of the same $members/$groupedMembers
// data already loaded+grouped by MemberController::index() + index.php above. Desktop
// keeps the classic table/tabs untouched (hidden via d-none d-lg-block there).
// Mirrors the layout/interactions of admin/tithes/_mobile_list.php: search + Filtros
// sheet (congregation only), swipeable cards with Ver/Editar/Excluir, FAB, "Ver mais".

function mmInitials($name) {
    $parts = preg_split('/\s+/', trim((string)$name));
    $out = '';
    if (!empty($parts[0])) $out .= mb_substr($parts[0], 0, 1);
    if (count($parts) > 1) $out .= mb_substr(end($parts), 0, 1);
    return mb_strtoupper($out !== '' ? $out : '?');
}

$mmCanManage = hasPermission('members.manage');
$mmCanDelete = $mmCanManage && (($_SESSION['user_role'] ?? '') === 'admin');
?>
<style>
    .mm-wrap { padding-bottom: 90px; }
    .mm-count { font-size: .78rem; color: #8b93a7; font-weight: 600; margin: -.5rem 0 .9rem; }
    .mm-toolbar { display: flex; gap: .5rem; overflow-x: auto; padding-bottom: .2rem; margin-bottom: 1rem; scrollbar-width: none; }
    .mm-toolbar::-webkit-scrollbar { display: none; }
    .mm-toolbar .btn { flex: 0 0 auto; white-space: nowrap; }
    .mm-searchrow { display: flex; gap: .5rem; margin-bottom: .9rem; }
    .mm-search { position: relative; flex: 1 1 auto; min-width: 0; }
    .mm-search i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .mm-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #fff; border-radius: 12px; padding: .55rem .8rem .55rem 2.3rem; font-size: .85rem; }
    .mm-search input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .mm-filter-btn { flex: 0 0 auto; border: none; background: #16213e; color: #fff; border-radius: 12px; padding: 0 1rem; font-size: .82rem; font-weight: 700; }
    .mm-chip-row { display: flex; flex-wrap: wrap; gap: .45rem; }
    .mm-chip { border: 1px solid rgba(17,24,39,.1); background: #fff; color: #495057; border-radius: 999px; padding: .4rem .9rem; font-size: .8rem; font-weight: 700; }
    .mm-chip.active { background: #2563eb; border-color: #2563eb; color: #fff; }
    .mm-swipe { position: relative; overflow: hidden; border-radius: 16px; margin-bottom: .55rem; }
    .mm-swipe.mm-hidden { display: none; }
    .mm-swipe-actions { position: absolute; top: 0; right: 0; bottom: 0; display: flex; align-items: stretch; }
    .mm-action { width: 56px; border: none; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; text-decoration: none; }
    .mm-action-view { background: #0891b2; }
    .mm-action-edit { background: #0d6efd; }
    .mm-action-delete { background: #dc3545; }
    .mm-card { position: relative; z-index: 1; display: flex; align-items: center; gap: .7rem; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .7rem .85rem; transition: transform .18s ease; touch-action: pan-y; }
    .mm-avatar { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 50%; object-fit: cover; background: rgba(37,99,235,.1); color: #2563eb; font-weight: 800; font-size: .8rem; display: flex; align-items: center; justify-content: center; }
    .mm-id { min-width: 0; flex: 1 1 auto; }
    .mm-name { font-weight: 700; font-size: .87rem; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mm-meta { font-size: .74rem; color: #8b93a7; }
    .mm-status { flex: 0 0 auto; display: inline-flex; align-items: center; gap: .35rem; padding: .28rem .6rem; border-radius: 999px; font-size: .66rem; font-weight: 700; }
    .mm-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .mm-status.is-active { background: rgba(25,135,84,.12); color: #198754; }
    .mm-status.is-inactive { background: rgba(0,0,0,.06); color: #6c757d; }
    .mm-kebab { flex: 0 0 auto; width: 30px; height: 30px; border: none; background: transparent; color: #adb5bd; border-radius: 50%; }
    .mm-loadmore { display: block; width: 100%; background: #fff; border: 1px solid rgba(17,24,39,.1); color: #16213e; font-weight: 700; font-size: .85rem; border-radius: 12px; padding: .65rem; margin-top: .3rem; }
    .mm-done { text-align: center; font-size: .76rem; color: #adb5bd; font-weight: 600; padding: .6rem 0 1rem; }
    .mm-fab { position: fixed; right: 1rem; bottom: calc(1rem + env(safe-area-inset-bottom)); width: 54px; height: 54px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 6px 16px rgba(37,99,235,.3); z-index: 1030; text-decoration: none; }
    .mm-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }
    .mm-sheet .offcanvas-header { border-bottom: 1px solid rgba(0,0,0,.06); }
    .mm-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 92vh; }
    .mm-filter-label { font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #8b93a7; margin-bottom: .5rem; }
</style>

<div class="mm-wrap d-lg-none">
    <?php
    $mobilePageCategory = 'Secretaria';
    $mobilePageTitle = 'Membros';
    include __DIR__ . '/../../layout/mobile_page_header.php';
    ?>
    <div class="mm-count"><?= count($members) ?> cadastrado<?= count($members) === 1 ? '' : 's' ?></div>

    <div class="mm-toolbar">
        <?php if (hasPermission('members.manage')): ?>
            <a href="/admin/members/import" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
                <i class="fas fa-file-import me-1"></i> Importar Planilha
            </a>
        <?php endif; ?>
        <a href="<?= $waLink ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill fw-semibold px-3">
            <i class="fab fa-whatsapp me-1"></i> Enviar Ficha
        </a>
    </div>

    <div class="mm-searchrow">
        <div class="mm-search">
            <i class="fas fa-search"></i>
            <input type="text" id="mmSearchInput" placeholder="Buscar membro...">
        </div>
        <?php if ($hasMultipleCongregations): ?>
            <button type="button" class="mm-filter-btn" data-bs-toggle="offcanvas" data-bs-target="#mmFilterSheet">
                <i class="fas fa-sliders-h me-1"></i>Filtros
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($members)): ?>
        <div class="mm-empty">Nenhum membro encontrado.</div>
    <?php else: ?>
        <div id="mmCardList">
            <?php foreach ($members as $idx => $member):
                $status = $member['status'] ?? 'active';
                $isActive = ($status === 'active' || strtolower(trim((string)$status)) === 'congregando');
                $congName = $member['congregation_name'] ?? 'Sem Congregação';
                $term = mb_strtolower($member['name'] . ' ' . ($member['role'] ?? '') . ' ' . $congName, 'UTF-8');
            ?>
                <div class="mm-swipe <?= $idx >= 10 ? 'mm-hidden' : '' ?>" data-term="<?= htmlspecialchars($term) ?>" data-cong="<?= htmlspecialchars(mb_strtolower($congName, 'UTF-8')) ?>">
                    <div class="mm-swipe-actions">
                        <a class="mm-action mm-action-view" href="/admin/members/show/<?= $member['id'] ?>" title="Ver ficha">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($mmCanManage): ?>
                            <a class="mm-action mm-action-edit" href="/admin/members/edit/<?= $member['id'] ?>" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($mmCanDelete): ?>
                            <a class="mm-action mm-action-delete btn-delete-member" href="/admin/members/delete/<?= $member['id'] ?>" data-name="<?= htmlspecialchars($member['name']) ?>" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="mm-card">
                        <?php if (!empty($member['photo'])): ?>
                            <img src="/uploads/members/<?= htmlspecialchars($member['photo']) ?>" class="mm-avatar" alt="">
                        <?php else: ?>
                            <span class="mm-avatar"><?= htmlspecialchars(mmInitials($member['name'])) ?></span>
                        <?php endif; ?>
                        <div class="mm-id">
                            <div class="mm-name"><?= htmlspecialchars($member['name']) ?><?php if (!empty($member['is_leader'])): ?> <i class="fas fa-star text-warning" style="font-size:.65rem;" title="Dirigente"></i><?php endif; ?></div>
                            <div class="mm-meta"><?= htmlspecialchars($member['role'] ?? 'Membro') ?> • <?= htmlspecialchars($congName) ?></div>
                        </div>
                        <span class="mm-status <?= $isActive ? 'is-active' : 'is-inactive' ?>"><?= $isActive ? 'Ativo' : 'Inativo' ?></span>
                        <button type="button" class="mm-kebab" aria-label="Ações"><i class="fas fa-ellipsis-vertical"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="mmLoadMore" class="mm-loadmore <?= count($members) > 10 ? '' : 'd-none' ?>">Ver mais</button>
        <div id="mmDone" class="mm-done <?= count($members) > 10 ? 'd-none' : '' ?>">Você viu tudo • <?= count($members) ?> membros</div>
    <?php endif; ?>

    <?php
    $mobilePageFooterLabel = 'Membros';
    include __DIR__ . '/../../layout/mobile_page_footer.php';
    ?>

    <a href="/admin/members/create" class="mm-fab" aria-label="Novo membro"><i class="fas fa-plus"></i></a>
</div>

<?php if ($hasMultipleCongregations): ?>
<div class="offcanvas offcanvas-bottom mm-sheet" tabindex="-1" id="mmFilterSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Filtros</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mm-filter-label">Congregação</div>
        <div class="mm-chip-row" id="mmCongChips">
            <button type="button" class="mm-chip active" data-cong="">Todas</button>
            <?php foreach ($groupedMembers as $congregationName => $congregationMembers): ?>
                <button type="button" class="mm-chip" data-cong="<?= htmlspecialchars(mb_strtolower($congregationName, 'UTF-8')) ?>"><?= htmlspecialchars($congregationName) ?></button>
            <?php endforeach; ?>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="button" id="mmClearFilters" class="btn btn-outline-secondary flex-fill rounded-pill">Limpar</button>
            <button type="button" class="btn btn-dark flex-fill rounded-pill" data-bs-dismiss="offcanvas">Aplicar filtros</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    var wrap = document.querySelector('.mm-wrap');
    if (!wrap) return;

    function normalize(str) {
        return (str || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    var activeCong = '';
    var searchInput = document.getElementById('mmSearchInput');
    var congChips = document.getElementById('mmCongChips');

    function applyFilters() {
        var term = normalize(searchInput ? searchInput.value.trim() : '');
        var filtering = term !== '' || activeCong !== '';
        // Search/congregation filters must reach cards past the "Ver mais" batch too.
        if (filtering) {
            document.querySelectorAll('#mmCardList .mm-swipe.mm-hidden').forEach(function (card) {
                card.classList.remove('mm-hidden');
            });
        }
        document.querySelectorAll('#mmCardList .mm-swipe').forEach(function (card) {
            var matchesTerm = term === '' || normalize(card.getAttribute('data-term')).indexOf(term) !== -1;
            var matchesCong = activeCong === '' || card.getAttribute('data-cong') === activeCong;
            card.style.display = (matchesTerm && matchesCong) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);

    if (congChips) {
        congChips.querySelectorAll('.mm-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                congChips.querySelectorAll('.mm-chip').forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                activeCong = chip.getAttribute('data-cong') || '';
                applyFilters();
            });
        });
    }

    var clearBtn = document.getElementById('mmClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            activeCong = '';
            if (congChips) {
                congChips.querySelectorAll('.mm-chip').forEach(function (c, i) { c.classList.toggle('active', i === 0); });
            }
            applyFilters();
        });
    }

    // "Ver mais" reveals the next batch of already-rendered cards
    var loadMoreBtn = document.getElementById('mmLoadMore');
    var doneLabel = document.getElementById('mmDone');
    var BATCH = 10;
    function revealMore() {
        var hidden = document.querySelectorAll('#mmCardList .mm-swipe.mm-hidden');
        for (var i = 0; i < BATCH && i < hidden.length; i++) {
            hidden[i].classList.remove('mm-hidden');
        }
        if (hidden.length <= BATCH) {
            if (loadMoreBtn) loadMoreBtn.classList.add('d-none');
            if (doneLabel) doneLabel.classList.remove('d-none');
        }
    }
    if (loadMoreBtn) loadMoreBtn.addEventListener('click', revealMore);

    // Swipe-to-reveal quick actions (iOS Mail style), plus a kebab fallback for non-touch
    document.querySelectorAll('.mm-swipe').forEach(function (swipe) {
        var card = swipe.querySelector('.mm-card');
        var actionsWidth = swipe.querySelectorAll('.mm-action').length * 56;
        if (!actionsWidth) return;
        var startX = 0, currentX = 0, dragging = false, open = false;

        function setOpen(state) {
            open = state;
            card.style.transform = open ? 'translateX(-' + actionsWidth + 'px)' : 'translateX(0)';
        }
        function closeOthers() {
            document.querySelectorAll('.mm-swipe .mm-card').forEach(function (c) {
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

        var kebab = swipe.querySelector('.mm-kebab');
        if (kebab) {
            kebab.addEventListener('click', function (e) {
                e.stopPropagation();
                closeOthers();
                setOpen(!open);
            });
        }
    });

    // Delete confirmation (same pattern as admin/members/show.php's .btn-delete-member)
    document.querySelectorAll('.btn-delete-member').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var href = btn.getAttribute('href');
            var name = btn.getAttribute('data-name');
            Swal.fire({
                title: 'Excluir membro?',
                text: 'Tem certeza que deseja excluir "' + name + '"? Esta ação não pode ser desfeita.',
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
})();
</script>

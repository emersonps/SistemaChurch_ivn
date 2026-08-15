    </div><!-- /.portal-content -->

<?php
$systemVersion = function_exists('getSystemSetting') ? (string)getSystemSetting('system_version', '') : '';
$systemVersion = $systemVersion !== '' ? $systemVersion : '1.0.0';

$portalBottomNavActive = function ($paths) use ($portalCurrentUri) {
    foreach ((array)$paths as $p) {
        if (strpos($portalCurrentUri, $p) !== false) return true;
    }
    return false;
};
?>

<div class="d-none d-lg-block">
    <footer class="text-center text-muted small py-3 border-top bg-white mt-4">
        Portal do Membro · Sistema v<?= htmlspecialchars($systemVersion) ?>
    </footer>
</div>

<!-- Bottom App Nav -->
<nav class="portal-bottom-nav">
    <div class="portal-bottom-nav-inner">
        <a href="/portal/dashboard" class="portal-bottom-nav-item <?= ($portalCurrentUri === '/portal/dashboard' || $portalCurrentUri === '/portal' || $portalCurrentUri === '/portal/') ? 'active' : '' ?>">
            <span class="portal-bottom-nav-icon-wrap"><i class="fas fa-house"></i></span>
            <span>Início</span>
        </a>
        <a href="/portal/financial" class="portal-bottom-nav-item <?= $portalBottomNavActive('/portal/financial') ? 'active' : '' ?>">
            <span class="portal-bottom-nav-icon-wrap"><i class="fas fa-hand-holding-dollar"></i></span>
            <span>Dízimos</span>
        </a>
        <a href="/portal/agenda" class="portal-bottom-nav-item <?= $portalBottomNavActive('/portal/agenda') ? 'active' : '' ?>">
            <span class="portal-bottom-nav-icon-wrap"><i class="fas fa-calendar-days"></i></span>
            <span>Agenda</span>
        </a>
        <a href="/portal/card" class="portal-bottom-nav-item <?= $portalBottomNavActive('/portal/card') ? 'active' : '' ?>">
            <span class="portal-bottom-nav-icon-wrap"><i class="fas fa-id-card"></i></span>
            <span>Carteira</span>
        </a>
        <button type="button" class="portal-bottom-nav-item" data-bs-toggle="offcanvas" data-bs-target="#portalMoreSheet">
            <span class="portal-bottom-nav-icon-wrap"><i class="fas fa-ellipsis"></i></span>
            <span>Mais</span>
        </button>
    </div>
</nav>

<!-- "Mais" Offcanvas Sheet -->
<div class="offcanvas offcanvas-bottom" tabindex="-1" id="portalMoreSheet">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold">Mais Opções</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php foreach ($portalNavGroups as $groupName => $items): ?>
            <div class="portal-section-label"><span><?= htmlspecialchars($groupName) ?></span></div>
            <?php foreach ($items as $item): ?>
                <a href="<?= htmlspecialchars($item['href']) ?>" class="portal-sheet-item">
                    <span class="portal-sheet-icon plc-<?= $item['color'] ?>"><i class="fas <?= $item['icon'] ?>"></i></span>
                    <span>
                        <span class="portal-sheet-title d-block"><?= htmlspecialchars($item['label']) ?></span>
                        <span class="portal-sheet-subtitle"><?= htmlspecialchars($item['subtitle']) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <a href="/portal/logout" class="portal-sheet-item mt-2">
            <span class="portal-sheet-icon" style="background: rgba(220,53,69,.12); color: #dc3545;"><i class="fas fa-sign-out-alt"></i></span>
            <span>
                <span class="portal-sheet-title d-block text-danger">Sair</span>
                <span class="portal-sheet-subtitle">Encerrar sessão</span>
            </span>
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('portalSearchInput');
        var searchModal = document.getElementById('portalSearchModal');
        if (!searchInput || !searchModal) return;

        var results = Array.prototype.slice.call(document.querySelectorAll('.portal-search-result'));

        var DIACRITICS_RE = new RegExp('[\\u0300-\\u036f]', 'g');
        function normalize(str) {
            return String(str || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(DIACRITICS_RE, '');
        }

        results.forEach(function (el) {
            el.dataset.termNormalized = normalize(el.getAttribute('data-term'));
        });

        function filterResults() {
            var q = normalize(searchInput.value.trim());
            var firstMatch = null;
            results.forEach(function (el) {
                var match = q === '' || el.dataset.termNormalized.indexOf(q) !== -1;
                el.classList.toggle('d-none', !match);
                if (match && !firstMatch) firstMatch = el;
            });
            searchInput.dataset.firstHref = firstMatch ? firstMatch.getAttribute('href') : '';
        }

        searchInput.addEventListener('input', filterResults);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && searchInput.dataset.firstHref) {
                window.location.href = searchInput.dataset.firstHref;
            }
        });

        searchModal.addEventListener('shown.bs.modal', function () {
            searchInput.value = '';
            filterResults();
            searchInput.focus();
        });
    });
</script>
</body>
</html>

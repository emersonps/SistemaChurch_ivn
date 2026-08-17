<?php
// src/views/layout/mobile_page_header.php
// Shared compact identity header for redesigned mobile (<992px) admin pages,
// replacing the shared red top navbar on the pages that opt in (they set
// $suppressMobileTopbar = true before including layout/header.php).
// Expects $mobilePageCategory (small eyebrow, e.g. "Financeiro") and
// $mobilePageTitle (big bold title, e.g. "Dízimos e Ofertas") to be set by the caller.
// Reuses the same blue palette introduced in layout/mobile_launcher.php.
// Optional: $mobilePageMenuItems — array of ['icon','label','sub'(optional),'href'] — when
// set, swaps the avatar link for a "..." dropdown menu (e.g. export actions). Pages that
// don't set it keep the default avatar-links-to-launcher behavior.

$mphBrand = ($siteProfile['alias'] ?? 'IVN') . ' ADMIN';
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'secretary') {
    $mphBrand = ($siteProfile['alias'] ?? 'IVN') . ' SECRETARIA';
}
$mphFallback = $mobileLauncherHref ?? '/admin?launcher=1';
?>
<style>
    .mph-topbar { display: flex; align-items: center; gap: .7rem; padding: .3rem 0 1.1rem; }
    .mph-back { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #fff; border: 1px solid rgba(17,24,39,.08); color: #16213e; display: flex; align-items: center; justify-content: center; }
    .mph-id { flex: 1 1 auto; min-width: 0; }
    .mph-eyebrow { font-size: .66rem; font-weight: 800; letter-spacing: .06em; color: #2563eb; }
    .mph-category { font-size: .92rem; font-weight: 800; color: #16213e; }
    .mph-avatar { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #16213e; color: #fff; font-weight: 800; font-size: .72rem; display: flex; align-items: center; justify-content: center; text-decoration: none; }
    .mph-title { font-size: 1.4rem; font-weight: 800; color: #16213e; line-height: 1.15; margin-bottom: 1rem; }
    .mph-menu-btn { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #fff; border: 1px solid rgba(17,24,39,.08); color: #16213e; display: flex; align-items: center; justify-content: center; }
    .mph-menu-item { display: flex; flex-direction: column; gap: .1rem; padding: .55rem .3rem; }
    .mph-menu-item-label { font-weight: 700; font-size: .84rem; color: #16213e; }
    .mph-menu-item-sub { font-size: .7rem; color: #8b93a7; }
</style>
<div class="mph-topbar">
    <button type="button" id="mphBackBtn" class="mph-back" data-fallback="<?= htmlspecialchars($mphFallback) ?>" aria-label="Voltar">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="mph-id">
        <div class="mph-eyebrow"><?= htmlspecialchars($mphBrand) ?></div>
        <div class="mph-category"><?= htmlspecialchars($mobilePageCategory ?? '') ?></div>
    </div>
    <?php if (!empty($mobilePageMenuItems)): ?>
        <div class="dropdown">
            <button type="button" class="mph-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mais opções">
                <i class="fas fa-ellipsis"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <?php foreach ($mobilePageMenuItems as $mphItem): ?>
                    <li>
                        <a class="dropdown-item mph-menu-item" href="<?= htmlspecialchars($mphItem['href']) ?>" <?= !empty($mphItem['target']) ? 'target="' . htmlspecialchars($mphItem['target']) . '"' : '' ?>>
                            <span class="mph-menu-item-label"><i class="fas <?= htmlspecialchars($mphItem['icon']) ?> me-2"></i><?= htmlspecialchars($mphItem['label']) ?></span>
                            <?php if (!empty($mphItem['sub'])): ?><span class="mph-menu-item-sub"><?= htmlspecialchars($mphItem['sub']) ?></span><?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <a href="<?= htmlspecialchars($mphFallback) ?>" class="mph-avatar" aria-label="Menu"><?= htmlspecialchars($topbarInitials ?? '?') ?></a>
    <?php endif; ?>
</div>
<?php if (!empty($mobilePageTitle)): ?>
    <div class="mph-title"><?= htmlspecialchars($mobilePageTitle) ?></div>
<?php endif; ?>
<script>
(function () {
    var btn = document.getElementById('mphBackBtn');
    if (!btn || btn.dataset.wired) return;
    btn.dataset.wired = '1';
    btn.addEventListener('click', function () {
        var cameFromSameSite = document.referrer && document.referrer.indexOf(window.location.origin) === 0;
        if (cameFromSameSite && window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = btn.getAttribute('data-fallback');
        }
    });
})();
</script>

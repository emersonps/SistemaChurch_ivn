<?php
// Included at the very top of <body> on public pages. $siteProfile must
// already be set by the including page. Shared across every instance via
// the core mirror — only renders where the instance's own
// "show_example_banner" setting is turned on (managed from Central).
if (!empty($siteProfile['show_example_banner'])):
?>
<div id="exampleContentBanner" style="position:fixed;top:0;left:0;right:0;z-index:99999;background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#1a1200;font-weight:700;text-align:center;padding:.5rem 1rem;font-size:.85rem;box-shadow:0 2px 8px rgba(0,0,0,.15);">
    <i class="fas fa-triangle-exclamation" style="margin-right:.4rem;"></i>
    Site de demonstração — conteúdo de exemplo, não é uma igreja real.
</div>
<script>
    (function () {
        var banner = document.getElementById('exampleContentBanner');
        if (!banner) return;
        var apply = function () {
            var current = parseFloat(getComputedStyle(document.body).paddingTop) || 0;
            document.body.style.paddingTop = (current + banner.offsetHeight) + 'px';
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', apply);
        } else {
            apply();
        }
    })();
</script>
<?php endif; ?>

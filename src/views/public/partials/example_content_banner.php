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
            var height = banner.offsetHeight;

            // Push normal page content down.
            var current = parseFloat(getComputedStyle(document.body).paddingTop) || 0;
            document.body.style.paddingTop = (current + height) + 'px';

            // Fixed/sticky headers (Bootstrap's .fixed-top navbar, or the
            // harpa/prayer .topbar which is position:sticky) are anchored
            // to the viewport at top:0 once pinned — body padding doesn't
            // affect them — so without this they end up hidden behind the
            // banner, immediately (fixed) or as soon as the page scrolls
            // past them (sticky). Push any other top-pinned element down
            // by the banner's height too.
            Array.prototype.forEach.call(document.querySelectorAll('body *'), function (el) {
                if (el === banner || banner.contains(el)) return;
                var style = getComputedStyle(el);
                if ((style.position === 'fixed' || style.position === 'sticky') && (parseFloat(style.top) || 0) === 0) {
                    var elCurrentTop = parseFloat(el.style.top) || 0;
                    el.style.top = (elCurrentTop + height) + 'px';
                }
            });
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', apply);
        } else {
            apply();
        }
    })();
</script>

<?php
// CTA de aquisição — mesmo número configurado como "Telefone da igreja" na
// instância (Central > Configurações Globais), sem número de fallback: sem
// telefone configurado, não tem pra quem mandar a mensagem.
$demoWhatsappPhone = preg_replace('/\D/', '', (string)($siteProfile['phone'] ?? ''));
if ($demoWhatsappPhone !== ''):
    $demoWhatsappMessage = 'Olá! Vi o sistema de demonstração e fiquei interessado(a). '
        . 'Como faço para adquirir o mesmo sistema para minha igreja?';
    $demoWhatsappUrl = 'https://wa.me/55' . $demoWhatsappPhone . '?text=' . rawurlencode($demoWhatsappMessage);
?>
<a href="<?= htmlspecialchars($demoWhatsappUrl) ?>" target="_blank" rel="noopener" id="demoAcquireCta"
   style="position:fixed;right:18px;bottom:18px;z-index:99998;display:inline-flex;align-items:center;gap:.5rem;background:#25D366;color:#fff;font-weight:700;font-size:.88rem;text-decoration:none;padding:.7rem 1.1rem;border-radius:999px;box-shadow:0 4px 14px rgba(0,0,0,.25);">
    <i class="fab fa-whatsapp" style="font-size:1.15rem;"></i>
    <span>Quero adquirir este sistema</span>
</a>
<?php endif; ?>
<?php endif; ?>

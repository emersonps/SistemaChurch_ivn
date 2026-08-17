<?php
// src/views/layout/mobile_page_footer.php
// Shared "app footer" bar for redesigned mobile (<992px) admin pages: brand mark +
// page label + real system version, plus today's date. Pairs with mobile_page_header.php.
// Expects $mobilePageFooterLabel to be set by the caller; falls back to
// $mobilePageTitle / $mobilePageCategory if not set.

$mpfVersion = function_exists('getSystemSetting') ? (string)getSystemSetting('system_version', '') : '';
$mpfVersion = $mpfVersion !== '' ? $mpfVersion : '1.0.0';
$mpfLabel = $mobilePageFooterLabel ?? ($mobilePageTitle ?? ($mobilePageCategory ?? ''));
?>
<style>
    .mpf-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 1.3rem; padding-top: .8rem; border-top: 1px solid rgba(17,24,39,.06); font-size: .68rem; color: #adb5bd; }
    .mpf-footer-brand { display: flex; align-items: center; gap: .4rem; font-weight: 700; color: #6c757d; }
    .mpf-footer-mark { flex: 0 0 auto; width: 18px; height: 18px; border-radius: 50%; background: #16213e; color: #fff; font-size: .58rem; font-weight: 800; display: flex; align-items: center; justify-content: center; }
</style>
<div class="mpf-footer">
    <span class="mpf-footer-brand">
        <span class="mpf-footer-mark"><?= htmlspecialchars(mb_substr($siteProfile['alias'] ?? 'IVN', 0, 1, 'UTF-8')) ?></span>
        <?= htmlspecialchars($mpfLabel) ?><?= $mpfLabel !== '' ? ' • ' : '' ?>v<?= htmlspecialchars($mpfVersion) ?>
    </span>
    <span><?= date('d/m/Y') ?></span>
</div>

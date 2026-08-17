<?php
// Reseta o OPcache do PHP no servidor. Só aceita chamadas vindas do próprio
// servidor (via SSH no pipeline de deploy), nunca da internet.
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteAddr, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('forbidden');
}

if (function_exists('opcache_reset') && opcache_reset()) {
    echo 'opcache cleared';
} else {
    echo 'opcache_reset not available';
}

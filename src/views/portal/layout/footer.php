        </main>
    </div>
</div>

<?php
$systemVersion = function_exists('getSystemSetting') ? (string)getSystemSetting('system_version', '') : '';
$systemVersion = $systemVersion !== '' ? $systemVersion : '1.0.0';
?>
<footer class="text-center text-muted small py-2 border-top bg-white">
    Sistema v<?= htmlspecialchars($systemVersion) ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

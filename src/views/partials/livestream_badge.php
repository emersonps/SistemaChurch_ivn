<style>
    .live-badge {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: .7rem; font-weight: 700; letter-spacing: .03em;
        padding: .3rem .7rem; border-radius: 999px; text-transform: uppercase;
        line-height: 1;
    }
    .live-badge::before {
        content: ''; width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
    }
    .live-badge-countdown {
        background: rgba(0, 0, 0, 0.75); color: #fff;
    }
    .live-badge-countdown::before {
        background: #ffc107;
        animation: liveBadgePulse 1.4s ease-in-out infinite;
    }
    .live-badge-live {
        background: #dc3545; color: #fff;
        animation: liveBadgeBlink 1.2s steps(1) infinite;
    }
    .live-badge-live::before {
        background: #fff;
    }
    @keyframes liveBadgePulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.4); opacity: .5; }
    }
    @keyframes liveBadgeBlink {
        0%, 49% { opacity: 1; }
        50%, 100% { opacity: .55; }
    }
</style>
<script>
(function () {
    // How long after the scheduled start we keep showing "AO VIVO" before
    // treating the stream as over (we have no way to detect the real end
    // without the YouTube Data API) and hiding the badge entirely.
    var LIVE_WINDOW_MS = 4 * 60 * 60 * 1000;

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function formatCountdown(ms) {
        var totalSeconds = Math.max(0, Math.floor(ms / 1000));
        var h = Math.floor(totalSeconds / 3600);
        var m = Math.floor((totalSeconds % 3600) / 60);
        var s = totalSeconds % 60;
        return pad(h) + ':' + pad(m) + ':' + pad(s);
    }

    function updateBadge(el) {
        var scheduledAt = new Date(el.dataset.scheduledAt).getTime();
        if (isNaN(scheduledAt)) {
            el.style.display = 'none';
            return;
        }

        var diff = scheduledAt - Date.now();

        if (diff > 0) {
            el.className = 'live-badge live-badge-countdown';
            el.textContent = 'Ao vivo em ' + formatCountdown(diff);
            el.style.display = '';
        } else if (Math.abs(diff) < LIVE_WINDOW_MS) {
            el.className = 'live-badge live-badge-live';
            el.textContent = 'AO VIVO';
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    function tick() {
        document.querySelectorAll('.live-badge[data-scheduled-at]').forEach(updateBadge);
    }

    document.addEventListener('DOMContentLoaded', function () {
        tick();
        setInterval(tick, 1000);
    });
})();
</script>

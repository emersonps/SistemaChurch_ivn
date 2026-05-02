<?php
$siteProfile = getChurchSiteProfileSettings();
$brand = getChurchBrandingName($siteProfile);
$alias = getChurchBrandingAlias($siteProfile);
$logoUrl = getChurchLogoUrl($siteProfile, true);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = (string)($_SERVER['HTTP_HOST'] ?? '');
$baseUrl = $host !== '' ? ($scheme . '://' . $host) : '';

$harpaDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'harpa_crista';
$hymns = [];
if (is_dir($harpaDir)) {
    $entries = scandir($harpaDir);
    foreach ($entries as $entry) {
        if (!is_string($entry) || $entry === '.' || $entry === '..') {
            continue;
        }

        if (!preg_match('/\.(pptx?)$/i', $entry)) {
            continue;
        }

        if (!preg_match('/^(\d+)\s*-\s*(.*?)\.(pptx?)$/i', $entry, $m)) {
            continue;
        }

        $num = (int)($m[1] ?? 0);
        if ($num <= 0) {
            continue;
        }

        $title = trim((string)($m[2] ?? ''));
        if ($title === '' || $title === '-') {
            $title = 'Hino sem título';
        }

        $hymns[] = [
            'number' => $num,
            'title' => $title,
        ];
    }
}

usort($hymns, function ($a, $b) {
    return ($a['number'] ?? 0) <=> ($b['number'] ?? 0);
});

$defaultNum = (int)($_GET['n'] ?? $_GET['num'] ?? 0);
$hymnsJson = json_encode($hymns, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harpa Cristã - <?= htmlspecialchars($brand) ?></title>
    <link rel="shortcut icon" href="<?= htmlspecialchars($logoUrl) ?>" type="image/png">
    <link rel="icon" href="<?= htmlspecialchars($logoUrl) ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg0:#050610;
            --bg1:#0b1330;
            --gold:#d4af37;
            --hot:#ff2a7a;
            --ink:rgba(255,255,255,.94);
            --muted:rgba(255,255,255,.72);
            --panel:rgba(255,255,255,.07);
            --panel2:rgba(255,255,255,.10);
            --border:rgba(255,255,255,.14);
            --shadow:0 24px 70px rgba(0,0,0,.45);
        }
        body{
            font-family:Poppins,sans-serif;
            color:var(--ink);
            background:
                radial-gradient(circle at 20% 18%, rgba(255,42,122,.24), transparent 36%),
                radial-gradient(circle at 78% 26%, rgba(212,175,55,.24), transparent 40%),
                radial-gradient(circle at 55% 115%, rgba(139,21,56,.26), transparent 44%),
                linear-gradient(135deg, var(--bg0) 0%, var(--bg1) 45%, var(--bg0) 100%);
            min-height:100vh;
        }
        .topbar{
            position:sticky;
            top:0;
            z-index:1000;
            background:rgba(5,6,16,.62);
            backdrop-filter:blur(12px);
            border-bottom:1px solid rgba(255,255,255,.08);
        }
        .topbar-inner{
            min-height:74px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:1rem;
        }
        .brand-pill{
            display:flex;
            align-items:center;
            gap:.8rem;
            padding:.45rem .8rem;
            border-radius:999px;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.10);
            box-shadow:0 18px 40px rgba(0,0,0,.32);
        }
        .brand-pill img{
            width:34px;
            height:34px;
            object-fit:contain;
            border-radius:10px;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.10);
            padding:4px;
        }
        .brand-pill .title{
            font-weight:800;
            line-height:1.1;
            font-size:.95rem;
        }
        .brand-pill .subtitle{
            font-size:.74rem;
            color:var(--muted);
        }
        .btn-cta{
            position:relative;
            overflow:hidden;
            border:0;
            border-radius:999px;
            background:linear-gradient(135deg, rgba(255,42,122,1) 0%, rgba(212,175,55,1) 100%);
            color:#090a15 !important;
            font-weight:900;
            box-shadow:0 14px 32px rgba(0,0,0,.20);
            transition:transform .15s ease, filter .15s ease, box-shadow .15s ease;
        }
        .btn-cta:hover{
            filter:brightness(1.02);
            transform:translateY(-1px);
            box-shadow:0 18px 42px rgba(0,0,0,.26);
        }
        .btn-cta::after{
            content:"";
            position:absolute;
            top:-30%;
            left:-30%;
            width:60%;
            height:160%;
            background:rgba(255,255,255,.35);
            transform:rotate(25deg) translateX(-140%);
            animation:ctaShimmer 3.2s ease-in-out infinite;
            pointer-events:none;
        }
        @keyframes ctaShimmer{
            0%{transform:rotate(25deg) translateX(-140%);opacity:0;}
            12%{opacity:.35;}
            28%{transform:rotate(25deg) translateX(260%);opacity:0;}
            100%{transform:rotate(25deg) translateX(260%);opacity:0;}
        }
        .hero{
            padding:2.6rem 0 1.1rem;
        }
        .hero-shell{
            border-radius:28px;
            padding:2rem;
            background:linear-gradient(135deg, rgba(255,255,255,.10), rgba(255,255,255,.04));
            border:1px solid rgba(255,255,255,.14);
            box-shadow:var(--shadow);
            position:relative;
            overflow:hidden;
        }
        .hero-shell::before{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius:30px;
            background:linear-gradient(120deg, rgba(255,42,122,.40), rgba(212,175,55,.40), rgba(255,255,255,.22));
            filter:blur(18px);
            opacity:.55;
            z-index:-1;
        }
        .hero-kicker{
            display:inline-flex;
            align-items:center;
            gap:.55rem;
            padding:.42rem .8rem;
            border-radius:999px;
            background:rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.14);
            color:rgba(255,255,255,.92);
            font-size:.78rem;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.04em;
            margin-bottom:1rem;
        }
        .hero-title{
            font-weight:900;
            line-height:1.05;
            font-size:clamp(2rem, 4vw, 3.2rem);
            margin:0 0 .65rem 0;
        }
        .hero-copy{
            color:var(--muted);
            font-size:1.02rem;
            max-width:55rem;
            margin:0;
        }
        .grid{
            display:grid;
            grid-template-columns: 420px 1fr;
            gap:1rem;
            padding-bottom:2.2rem;
        }
        @media (max-width: 992px){
            .grid{grid-template-columns:1fr;}
        }
        .panel{
            background:var(--panel);
            border:1px solid var(--border);
            border-radius:24px;
            box-shadow:0 18px 45px rgba(0,0,0,.30);
            backdrop-filter:blur(10px);
            overflow:hidden;
        }
        .panel-head{
            padding:1.1rem 1.2rem .9rem;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.8rem;
            background:linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04));
            border-bottom:1px solid rgba(255,255,255,.10);
        }
        .panel-title{
            font-weight:900;
            margin:0;
            font-size:1.05rem;
        }
        .panel-sub{
            margin:0;
            color:var(--muted);
            font-size:.84rem;
        }
        .panel-body{
            padding:1.1rem 1.2rem 1.2rem;
        }
        .cover{
            border-radius:22px;
            background:
                radial-gradient(circle at 25% 22%, rgba(255,42,122,.30), transparent 44%),
                radial-gradient(circle at 78% 24%, rgba(212,175,55,.26), transparent 46%),
                linear-gradient(135deg, rgba(0,0,0,.35), rgba(0,0,0,.10));
            border:1px solid rgba(255,255,255,.14);
            box-shadow:0 18px 50px rgba(0,0,0,.40);
            position:relative;
            overflow:hidden;
            aspect-ratio: 4 / 5;
            display:flex;
            flex-direction:column;
            justify-content:flex-end;
        }
        .cover::after{
            content:"";
            position:absolute;
            inset:auto -40px -40px auto;
            width:220px;
            height:220px;
            border-radius:50%;
            background:radial-gradient(circle at 30% 30%, rgba(255,255,255,.16), rgba(255,255,255,0) 70%);
        }
        .cover-inner{
            position:relative;
            padding:1.2rem;
        }
        .cover-badge{
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            padding:.35rem .7rem;
            border-radius:999px;
            background:rgba(255,255,255,.10);
            border:1px solid rgba(255,255,255,.16);
            font-weight:800;
            font-size:.78rem;
            margin-bottom:.7rem;
        }
        .cover-title{
            font-weight:900;
            margin:0;
            font-size:1.65rem;
            letter-spacing:.02em;
        }
        .cover-meta{
            margin:.35rem 0 0 0;
            color:rgba(255,255,255,.78);
            font-size:.9rem;
        }
        .cover-logo{
            position:absolute;
            top:1.1rem;
            left:1.1rem;
            width:54px;
            height:54px;
            border-radius:16px;
            background:rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.14);
            padding:7px;
            object-fit:contain;
        }
        .input-wrap{
            display:flex;
            gap:.6rem;
        }
        .input-wrap .form-control{
            border-radius:14px;
            background:rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.14);
            color:var(--ink);
            font-weight:700;
        }
        .input-wrap .form-control:focus{
            background:rgba(255,255,255,.10);
            border-color:rgba(212,175,55,.55);
            box-shadow:0 0 0 .2rem rgba(212,175,55,.12);
            color:var(--ink);
        }
        .list-shell{
            border-radius:18px;
            border:1px solid rgba(255,255,255,.12);
            background:rgba(0,0,0,.18);
            overflow:hidden;
        }
        .list-top{
            padding:.85rem .95rem;
            display:flex;
            gap:.6rem;
            align-items:center;
            border-bottom:1px solid rgba(255,255,255,.10);
            background:rgba(255,255,255,.04);
        }
        .list-top input{
            border-radius:14px;
            background:rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.14);
            color:var(--ink);
            font-weight:600;
        }
        .list-top input:focus{
            background:rgba(255,255,255,.10);
            border-color:rgba(212,175,55,.55);
            box-shadow:0 0 0 .2rem rgba(212,175,55,.12);
            color:var(--ink);
        }
        .hymn-list{
            max-height: 58vh;
            overflow:auto;
        }
        .hymn-item{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.8rem;
            padding:.7rem .95rem;
            cursor:pointer;
            border-bottom:1px solid rgba(255,255,255,.07);
            transition:background .12s ease;
        }
        .hymn-item:hover{
            background:rgba(255,255,255,.05);
        }
        .hymn-item.active{
            background:linear-gradient(135deg, rgba(255,42,122,.18), rgba(212,175,55,.14));
        }
        .hymn-item:last-child{
            border-bottom:0;
        }
        .hymn-left{
            display:flex;
            align-items:center;
            gap:.8rem;
            min-width:0;
        }
        .num-pill{
            min-width:48px;
            text-align:center;
            padding:.3rem .55rem;
            border-radius:999px;
            background:rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.14);
            font-weight:900;
            color:rgba(255,255,255,.92);
        }
        .hymn-title{
            font-weight:800;
            font-size:.95rem;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }
        .hymn-tag{
            font-size:.72rem;
            color:var(--muted);
            white-space:nowrap;
        }
        .stage{
            border-radius:20px;
            border:1px solid rgba(255,255,255,.12);
            background:rgba(0,0,0,.18);
            overflow:hidden;
        }
        .stage-top{
            padding:.85rem .95rem;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.8rem;
            border-bottom:1px solid rgba(255,255,255,.10);
            background:rgba(255,255,255,.04);
            flex-wrap:wrap;
        }
        .stage-title{
            margin:0;
            font-weight:900;
            font-size:1.05rem;
        }
        .stage-sub{
            margin:0;
            color:var(--muted);
            font-size:.84rem;
        }
        .stage-actions{
            display:flex;
            gap:.45rem;
            flex-wrap:wrap;
        }
        .stage-actions .btn{
            border-radius:999px;
            font-weight:800;
            border-color:rgba(255,255,255,.14);
        }
        .stage-body{
            position:relative;
            height:min(70vh, 680px);
            background:
                radial-gradient(circle at 15% 15%, rgba(255,42,122,.14), transparent 36%),
                radial-gradient(circle at 85% 20%, rgba(212,175,55,.12), transparent 42%),
                linear-gradient(180deg, rgba(255,255,255,.03), rgba(0,0,0,.18));
        }
        .stage-body.flip{
            animation:pageFlip .55s ease;
        }
        @keyframes pageFlip{
            0%{transform:perspective(1200px) rotateY(0deg);}
            45%{transform:perspective(1200px) rotateY(-22deg);}
            100%{transform:perspective(1200px) rotateY(0deg);}
        }
        .stage-iframe{
            width:100%;
            height:100%;
            border:0;
            display:block;
        }
        .stage-placeholder{
            position:absolute;
            inset:0;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:1.2rem;
            text-align:center;
        }
        .placeholder-card{
            border-radius:22px;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.12);
            box-shadow:0 18px 50px rgba(0,0,0,.35);
            padding:1.3rem;
            max-width:520px;
        }
        .placeholder-card h3{
            font-weight:900;
            margin:0 0 .55rem 0;
        }
        .placeholder-card p{
            margin:0;
            color:var(--muted);
        }
        .toast-container{
            position:fixed;
            inset:auto 1rem 1rem auto;
            z-index:2000;
        }
        .toast{
            background:rgba(10,12,25,.92);
            border:1px solid rgba(255,255,255,.14);
            color:var(--ink);
            border-radius:16px;
            box-shadow:0 18px 50px rgba(0,0,0,.40);
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <div class="brand-pill">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($alias) ?>">
                <div>
                    <div class="title">Harpa Cristã</div>
                    <div class="subtitle"><?= htmlspecialchars($alias) ?></div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <a href="/" class="btn btn-outline-light btn-sm rounded-pill fw-bold"><i class="fas fa-house me-2"></i>Início</a>
                <a href="/contato" class="btn btn-cta btn-sm"><i class="fas fa-circle-info me-2"></i>Contato</a>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-shell">
                <span class="hero-kicker"><i class="fas fa-music"></i> Repertório • Escolha pelo número</span>
                <h1 class="hero-title">Escolha o hino e “folheie” como um livro</h1>
                <p class="hero-copy">
                    Digite o número do hino ou selecione na lista. A apresentação abre em formato de slides, perfeita para cantar e acompanhar na igreja.
                </p>
                <div class="mt-4 input-wrap">
                    <input type="number" min="1" step="1" class="form-control form-control-lg" id="hymnNumber" placeholder="Digite o número do hino (ex: 23)" inputmode="numeric" value="<?= $defaultNum > 0 ? htmlspecialchars((string)$defaultNum) : '' ?>">
                    <button class="btn btn-cta btn-lg px-4" id="btnOpen"><i class="fas fa-book-open me-2"></i>Abrir</button>
                </div>
            </div>
        </div>
    </section>

    <main>
        <div class="container">
            <div class="grid">
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2 class="panel-title mb-1">Lista de Hinos</h2>
                            <p class="panel-sub"><?= count($hymns) ?> disponíveis</p>
                        </div>
                        <span class="badge rounded-pill text-bg-warning text-dark fw-bold px-3 py-2"><i class="fas fa-harp me-2"></i>Harpa</span>
                    </div>
                    <div class="panel-body">
                        <div class="cover mb-3">
                            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($alias) ?>" class="cover-logo">
                            <div class="cover-inner">
                                <div class="cover-badge"><i class="fas fa-microphone-lines"></i> Estilo cantor cristão</div>
                                <h3 class="cover-title">Harpa Cristã</h3>
                                <p class="cover-meta">Selecione o hino e conduza o louvor com agilidade.</p>
                            </div>
                        </div>

                        <div class="list-shell">
                            <div class="list-top">
                                <i class="fas fa-magnifying-glass text-warning"></i>
                                <input type="text" class="form-control" id="filter" placeholder="Filtrar por número ou título">
                            </div>
                            <div class="hymn-list" id="hymnList"></div>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2 class="panel-title mb-1">Palco</h2>
                            <p class="panel-sub" id="nowPlaying">Abra um hino para começar</p>
                        </div>
                        <div class="stage-actions">
                            <button class="btn btn-outline-light btn-sm" id="btnPrev"><i class="fas fa-chevron-left me-2"></i>Anterior</button>
                            <button class="btn btn-outline-light btn-sm" id="btnNext">Próximo<i class="fas fa-chevron-right ms-2"></i></button>
                            <a class="btn btn-outline-warning btn-sm" id="btnNewTab" target="_blank" rel="noopener noreferrer"><i class="fas fa-up-right-from-square me-2"></i>Abrir</a>
                            <a class="btn btn-outline-success btn-sm" id="btnDownload" target="_blank" rel="noopener noreferrer"><i class="fas fa-download me-2"></i>Baixar</a>
                        </div>
                    </div>
                    <div class="panel-body p-0">
                        <div class="stage">
                            <div class="stage-top">
                                <div>
                                    <div class="stage-title" id="stageTitle">Harpa Cristã</div>
                                    <div class="stage-sub" id="stageSub">Escolha um hino na lista ou digite o número</div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge rounded-pill text-bg-light text-dark fw-bold px-3 py-2" id="badgeNumber" style="display:none;"></span>
                                </div>
                            </div>
                            <div class="stage-body" id="stageBody">
                                <div class="stage-placeholder" id="placeholder">
                                    <div class="placeholder-card">
                                        <h3>Pronto para o louvor</h3>
                                        <p>Abra um hino para visualizar as páginas e conduzir a igreja com clareza.</p>
                                        <div class="mt-3" id="placeholderActions"></div>
                                    </div>
                                </div>
                                <iframe class="stage-iframe" id="viewer" title="Harpa Cristã" allowfullscreen style="display:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <div class="toast-container">
        <div class="toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true" id="toast">
            <div class="d-flex">
                <div class="toast-body" id="toastBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const hymns = <?= $hymnsJson ?: '[]' ?>;
        const baseUrl = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

        const elements = {
            list: document.getElementById('hymnList'),
            filter: document.getElementById('filter'),
            number: document.getElementById('hymnNumber'),
            open: document.getElementById('btnOpen'),
            prev: document.getElementById('btnPrev'),
            next: document.getElementById('btnNext'),
            nowPlaying: document.getElementById('nowPlaying'),
            stageTitle: document.getElementById('stageTitle'),
            stageSub: document.getElementById('stageSub'),
            badgeNumber: document.getElementById('badgeNumber'),
            newTab: document.getElementById('btnNewTab'),
            download: document.getElementById('btnDownload'),
            viewer: document.getElementById('viewer'),
            placeholder: document.getElementById('placeholder'),
            placeholderActions: document.getElementById('placeholderActions'),
            stageBody: document.getElementById('stageBody'),
            toastEl: document.getElementById('toast'),
            toastBody: document.getElementById('toastBody')
        };

        const toast = bootstrap.Toast.getOrCreateInstance(elements.toastEl, { delay: 2400 });

        const state = {
            currentIndex: -1,
            filtered: hymns.slice()
        };

        function normalizeText(value) {
            return (value || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');
        }

        function renderList() {
            const html = state.filtered.map((h, idx) => {
                const active = idx === state.currentIndex ? ' active' : '';
                const title = `${h.number} - ${h.title}`;
                return `
                    <div class="hymn-item${active}" data-index="${idx}" tabindex="0" role="button" aria-label="${title}">
                        <div class="hymn-left">
                            <div class="num-pill">${h.number}</div>
                            <div class="min-w-0">
                                <div class="hymn-title">${h.title}</div>
                                <div class="hymn-tag">Clique para abrir</div>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-warning"></i>
                    </div>
                `;
            }).join('');
            elements.list.innerHTML = html || `<div class="p-3 text-center" style="color: rgba(255,255,255,.72);">Nenhum hino encontrado.</div>`;
        }

        function showToast(message) {
            elements.toastBody.textContent = message;
            toast.show();
        }

        function getAbsoluteHymnUrl(number, download) {
            const path = `/harpa/hino?n=${encodeURIComponent(number)}${download ? '&download=1' : ''}`;
            if (baseUrl) return baseUrl + path;
            return path;
        }

        function getOfficeEmbedUrl(absoluteUrl) {
            return `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absoluteUrl)}`;
        }

        function escapeHtml(value) {
            return (value || '').toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function isOfficeEmbedAvailable() {
            if (!baseUrl) return false;
            try {
                const url = new URL(baseUrl);
                const host = (url.hostname || '').toLowerCase();
                if (!host) return false;
                if (host === 'localhost' || host === '127.0.0.1') return false;
                if (/^127\./.test(host)) return false;
                if (/^10\./.test(host)) return false;
                if (/^192\.168\./.test(host)) return false;
                const m = host.match(/^172\.(\d+)\./);
                if (m) {
                    const second = Number(m[1] || 0);
                    if (second >= 16 && second <= 31) return false;
                }
                return true;
            } catch (e) {
                return false;
            }
        }

        function showLocalPreviewFallback(h, openUrl, downloadUrl) {
            elements.viewer.style.display = 'none';
            elements.viewer.removeAttribute('src');
            elements.placeholder.style.display = 'flex';
            if (elements.placeholderActions) {
                elements.placeholderActions.innerHTML =
                    `<div class="d-grid gap-2">` +
                    `<a class="btn btn-cta btn-lg" target="_blank" rel="noopener noreferrer" href="${escapeHtml(openUrl)}">` +
                    `<i class="fas fa-play me-2"></i>Abrir o hino` +
                    `</a>` +
                    `<a class="btn btn-outline-success btn-lg" target="_blank" rel="noopener noreferrer" href="${escapeHtml(downloadUrl)}">` +
                    `<i class="fas fa-download me-2"></i>Baixar o PowerPoint` +
                    `</a>` +
                    `<div style="color: rgba(255,255,255,.72); font-weight: 600; font-size: .92rem;">` +
                    `Para “folhear” dentro da página, use um domínio público (não localhost).` +
                    `</div>` +
                    `</div>`;
            }
            showToast('Modo local: use Abrir/Baixar para abrir no dispositivo.');
        }

        function setActiveByNumber(number) {
            const idx = state.filtered.findIndex(h => Number(h.number) === Number(number));
            if (idx === -1) return false;
            openByIndex(idx);
            return true;
        }

        function openByIndex(index) {
            if (index < 0 || index >= state.filtered.length) return;
            state.currentIndex = index;
            const h = state.filtered[index];

            const absolutePpt = getAbsoluteHymnUrl(h.number, false);
            const absoluteDownload = getAbsoluteHymnUrl(h.number, true);
            const embed = getOfficeEmbedUrl(absolutePpt);

            elements.nowPlaying.textContent = `Agora: Hino ${h.number}`;
            elements.stageTitle.textContent = `Hino ${h.number}`;
            elements.stageSub.textContent = h.title;
            elements.badgeNumber.style.display = '';
            elements.badgeNumber.textContent = `#${h.number}`;
            elements.newTab.href = absolutePpt;
            elements.download.href = absoluteDownload;

            if (!isOfficeEmbedAvailable()) {
                showLocalPreviewFallback(h, absolutePpt, absoluteDownload);
            } else {
                elements.placeholder.style.display = 'none';
                if (elements.placeholderActions) {
                    elements.placeholderActions.innerHTML = '';
                }
                elements.viewer.style.display = 'block';
                elements.viewer.src = embed;
            }

            elements.stageBody.classList.remove('flip');
            void elements.stageBody.offsetWidth;
            elements.stageBody.classList.add('flip');

            renderList();
            const activeEl = elements.list.querySelector(`.hymn-item[data-index="${index}"]`);
            if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
        }

        function applyFilter() {
            const q = normalizeText(elements.filter.value);
            if (!q) {
                state.filtered = hymns.slice();
            } else {
                state.filtered = hymns.filter(h => {
                    const t = normalizeText(h.title);
                    const n = (h.number || '').toString();
                    return t.includes(q) || n.includes(q);
                });
            }
            state.currentIndex = -1;
            renderList();
        }

        elements.list.addEventListener('click', (e) => {
            const item = e.target.closest('.hymn-item');
            if (!item) return;
            openByIndex(Number(item.dataset.index));
        });

        elements.list.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            const item = e.target.closest('.hymn-item');
            if (!item) return;
            openByIndex(Number(item.dataset.index));
        });

        elements.open.addEventListener('click', () => {
            const n = Number(elements.number.value || 0);
            if (!n) return showToast('Digite um número válido.');
            if (!setActiveByNumber(n)) return showToast('Hino não encontrado na lista.');
        });

        elements.number.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            elements.open.click();
        });

        elements.filter.addEventListener('input', () => {
            applyFilter();
        });

        elements.prev.addEventListener('click', () => {
            if (state.filtered.length === 0) return;
            if (state.currentIndex <= 0) return showToast('Você já está no primeiro hino da lista atual.');
            openByIndex(state.currentIndex - 1);
        });

        elements.next.addEventListener('click', () => {
            if (state.filtered.length === 0) return;
            if (state.currentIndex === -1) return showToast('Abra um hino para continuar.');
            if (state.currentIndex >= state.filtered.length - 1) return showToast('Você já está no último hino da lista atual.');
            openByIndex(state.currentIndex + 1);
        });

        renderList();

        const initial = <?= json_encode($defaultNum, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        if (initial && Number.isFinite(Number(initial))) {
            const ok = setActiveByNumber(initial);
            if (!ok && initial > 0) showToast('Hino inicial não encontrado na lista.');
        }
    </script>
</body>
</html>

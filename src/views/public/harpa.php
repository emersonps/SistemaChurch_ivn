<?php
$siteProfile = getChurchSiteProfileSettings();
$brand = getChurchBrandingName($siteProfile);
$alias = getChurchBrandingAlias($siteProfile);
$logoUrl = getChurchLogoUrl($siteProfile, true);
$hymns = [];
try {
    $db = (new Database())->connect();
    $rows = $db->query("SELECT hymn_number as number, title FROM harpa_hymns ORDER BY hymn_number ASC")->fetchAll(PDO::FETCH_ASSOC);
    if (is_array($rows) && count($rows) > 0) {
        foreach ($rows as $r) {
            $num = (int)($r['number'] ?? 0);
            if ($num <= 0) {
                continue;
            }
            $hymns[] = [
                'number' => $num,
                'title' => (string)($r['title'] ?? ''),
            ];
        }
    }
} catch (Throwable $e) {
}

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
        main{
            padding-top: 1.2rem;
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
        .panel-list{order:1;}
        .panel-reader{order:2;}
        @media (max-width: 992px){
            .grid{grid-template-columns:1fr;}
            .panel-reader{order:1;}
            .panel-list{order:2;}
            .panel-reader{display:none;}
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
        .reader{
            border-radius:20px;
            border:1px solid rgba(255,255,255,.12);
            background:rgba(0,0,0,.18);
            overflow:hidden;
        }
        .reader-top{
            padding:.85rem .95rem;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.8rem;
            border-bottom:1px solid rgba(255,255,255,.10);
            background:rgba(255,255,255,.04);
            flex-wrap:wrap;
        }
        .reader-title{
            margin:0;
            font-weight:900;
            font-size:1.05rem;
        }
        .reader-sub{
            margin:0;
            color:var(--muted);
            font-size:.84rem;
        }
        .reader-actions{
            display:flex;
            gap:.45rem;
            flex-wrap:wrap;
        }
        .reader-actions .btn{
            border-radius:999px;
            font-weight:800;
            border-color:rgba(255,255,255,.14);
        }
        .reader-body{
            position:relative;
            background:
                radial-gradient(circle at 15% 15%, rgba(255,42,122,.14), transparent 36%),
                radial-gradient(circle at 85% 20%, rgba(212,175,55,.12), transparent 42%),
                linear-gradient(180deg, rgba(255,255,255,.03), rgba(0,0,0,.18));
        }
        .reader-body.flip{
            animation:pageFlip .55s ease;
        }
        @keyframes pageFlip{
            0%{transform:perspective(1200px) rotateY(0deg);}
            45%{transform:perspective(1200px) rotateY(-22deg);}
            100%{transform:perspective(1200px) rotateY(0deg);}
        }
        .reader-content{
            padding: 1.05rem 1rem 1.2rem;
        }
        .reader-card{
            border-radius:22px;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.12);
            box-shadow:0 18px 50px rgba(0,0,0,.35);
            padding:1.15rem 1.1rem;
        }
        .reader-meta{
            color: var(--muted);
            font-weight: 750;
            letter-spacing: .01em;
        }
        .lyrics{
            --fontSize: 1.1rem;
            --lineHeight: 1.75;
            margin: .85rem auto 0;
            max-width: 50rem;
            padding: 1.05rem 1.05rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: rgba(0,0,0,.16);
            color: rgba(255,255,255,.94);
            font-weight: 650;
            font-size: var(--fontSize);
            line-height: var(--lineHeight);
            white-space: pre-wrap;
            word-break: break-word;
        }
        .lyrics.compact{--lineHeight:1.55;}
        .lyrics.large{--fontSize:1.28rem;}
        .lyrics.xlarge{--fontSize:1.45rem;}
        @media (max-width: 576px){
            .lyrics{padding:.95rem .95rem;}
        }
        .hymn-modal .modal-content{
            background:rgba(6,8,18,.96);
            border:1px solid rgba(255,255,255,.12);
            color:var(--ink);
            border-radius:20px;
            overflow:hidden;
        }
        .hymn-modal .modal-header,
        .hymn-modal .modal-footer{
            border-color:rgba(255,255,255,.10);
            background:rgba(255,255,255,.04);
        }
        .hymn-modal .btn-close{
            filter: invert(1);
            opacity:.75;
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

    <main>
        <div class="container">
            <div class="grid">
                <section class="panel panel-list">
                    <div class="panel-head">
                        <div>
                            <h2 class="panel-title mb-1">Lista de Hinos</h2>
                            <p class="panel-sub"><?= count($hymns) ?> disponíveis</p>
                        </div>
                        <span class="badge rounded-pill text-bg-warning text-dark fw-bold px-3 py-2"><i class="fas fa-harp me-2"></i>Harpa</span>
                    </div>
                    <div class="panel-body">
                        <div class="list-shell">
                            <div class="list-top">
                                <i class="fas fa-magnifying-glass text-warning"></i>
                                <input type="text" class="form-control" id="filter" placeholder="Filtrar por número ou título">
                            </div>
                            <div class="hymn-list" id="hymnList"></div>
                        </div>
                    </div>
                </section>

                <section class="panel panel-reader">
                    <div class="panel-head">
                        <div>
                            <h2 class="panel-title mb-1">Leitura</h2>
                            <p class="panel-sub" id="nowPlaying">Abra um hino para começar</p>
                        </div>
                        <div class="reader-actions">
                            <button class="btn btn-outline-light btn-sm" id="btnPrev"><i class="fas fa-chevron-left me-2"></i>Anterior</button>
                            <button class="btn btn-outline-light btn-sm" id="btnNext">Próximo<i class="fas fa-chevron-right ms-2"></i></button>
                            <button class="btn btn-outline-warning btn-sm" id="btnFont"><i class="fas fa-font me-2"></i>Tamanho</button>
                            <button class="btn btn-outline-success btn-sm" id="btnLine"><i class="fas fa-align-left me-2"></i>Linhas</button>
                        </div>
                    </div>
                    <div class="panel-body p-0">
                        <div class="reader">
                            <div class="reader-top">
                                <div>
                                    <div class="reader-title" id="stageTitle">Harpa Cristã</div>
                                    <div class="reader-sub" id="stageSub">Escolha um hino na lista ou digite o número</div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge rounded-pill text-bg-light text-dark fw-bold px-3 py-2" id="badgeNumber" style="display:none;"></span>
                                </div>
                            </div>
                            <div class="reader-body" id="stageBody">
                                <div class="reader-content">
                                    <div class="reader-card" id="placeholder">
                                        <h3 style="font-weight:900;" class="mb-2">Pronto para o louvor</h3>
                                        <div class="reader-meta">Abra um hino para ler a letra com conforto no celular.</div>
                                    </div>
                                    <div class="lyrics" id="lyricsText" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <div class="modal fade hymn-modal" id="hymnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="reader-title" id="modalTitle">Harpa Cristã</div>
                            <div class="reader-sub" id="modalSub">Selecione um hino</div>
                        </div>
                        <span class="badge rounded-pill text-bg-light text-dark fw-bold px-3 py-2" id="modalBadge" style="display:none;"></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="reader-body" id="modalReaderBody">
                        <div class="reader-content">
                            <div class="reader-card" id="modalPlaceholder">
                                <h3 style="font-weight:900;" class="mb-2">Pronto para o louvor</h3>
                                <div class="reader-meta">Abrindo a letra...</div>
                            </div>
                            <div class="lyrics" id="modalLyrics" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex align-items-center justify-content-between w-100 gap-2 flex-wrap">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-light btn-sm" id="modalPrev"><i class="fas fa-chevron-left me-2"></i>Anterior</button>
                            <button class="btn btn-outline-light btn-sm" id="modalNext">Próximo<i class="fas fa-chevron-right ms-2"></i></button>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-warning btn-sm" id="modalFont"><i class="fas fa-font me-2"></i>Tamanho</button>
                            <button class="btn btn-outline-success btn-sm" id="modalLine"><i class="fas fa-align-left me-2"></i>Linhas</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        const elements = {
            list: document.getElementById('hymnList'),
            filter: document.getElementById('filter'),
            prev: document.getElementById('btnPrev'),
            next: document.getElementById('btnNext'),
            font: document.getElementById('btnFont'),
            line: document.getElementById('btnLine'),
            nowPlaying: document.getElementById('nowPlaying'),
            stageTitle: document.getElementById('stageTitle'),
            stageSub: document.getElementById('stageSub'),
            badgeNumber: document.getElementById('badgeNumber'),
            placeholder: document.getElementById('placeholder'),
            stageBody: document.getElementById('stageBody'),
            lyricsText: document.getElementById('lyricsText'),
            modalEl: document.getElementById('hymnModal'),
            modalTitle: document.getElementById('modalTitle'),
            modalSub: document.getElementById('modalSub'),
            modalBadge: document.getElementById('modalBadge'),
            modalReaderBody: document.getElementById('modalReaderBody'),
            modalPlaceholder: document.getElementById('modalPlaceholder'),
            modalLyrics: document.getElementById('modalLyrics'),
            modalPrev: document.getElementById('modalPrev'),
            modalNext: document.getElementById('modalNext'),
            modalFont: document.getElementById('modalFont'),
            modalLine: document.getElementById('modalLine'),
            toastEl: document.getElementById('toast'),
            toastBody: document.getElementById('toastBody')
        };

        const toast = bootstrap.Toast.getOrCreateInstance(elements.toastEl, { delay: 2400 });
        const hymnModal = elements.modalEl ? bootstrap.Modal.getOrCreateInstance(elements.modalEl) : null;
        const mediaMobile = window.matchMedia('(max-width: 992px)');

        const state = {
            currentIndex: -1,
            filtered: hymns.slice(),
            fontSizeMode: localStorage.getItem('harpa_font') || 'normal',
            lineMode: localStorage.getItem('harpa_line') || 'normal'
        };

        function applyReaderPrefs(targetLyrics) {
            const lyricsEl = targetLyrics || elements.lyricsText;
            lyricsEl.classList.remove('large', 'xlarge', 'compact');
            if (state.fontSizeMode === 'large') lyricsEl.classList.add('large');
            if (state.fontSizeMode === 'xlarge') lyricsEl.classList.add('xlarge');
            if (state.lineMode === 'compact') lyricsEl.classList.add('compact');
        }

        function isMobile() {
            return !!hymnModal && mediaMobile.matches;
        }

        function getReaderTarget() {
            if (isMobile()) {
                return {
                    title: elements.modalTitle,
                    sub: elements.modalSub,
                    badge: elements.modalBadge,
                    placeholder: elements.modalPlaceholder,
                    lyrics: elements.modalLyrics,
                    body: elements.modalReaderBody
                };
            }
            return {
                title: elements.stageTitle,
                sub: elements.stageSub,
                badge: elements.badgeNumber,
                placeholder: elements.placeholder,
                lyrics: elements.lyricsText,
                body: elements.stageBody
            };
        }

        function animateFlip(targetBody) {
            if (!targetBody) return;
            targetBody.classList.remove('flip');
            void targetBody.offsetWidth;
            targetBody.classList.add('flip');
        }

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

        async function loadLyrics(number) {
            const target = getReaderTarget();
            try {
                const url = `/harpa/letra?n=${encodeURIComponent(number)}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) {
                    target.lyrics.style.display = 'none';
                    target.placeholder.style.display = 'block';
                    return;
                }
                const data = await res.json();
                const lyrics = (data && typeof data.lyrics === 'string') ? data.lyrics.trim() : '';

                if (!lyrics) {
                    target.lyrics.style.display = 'none';
                    target.placeholder.style.display = 'block';
                    return;
                }

                target.placeholder.style.display = 'none';
                target.lyrics.style.display = 'block';
                target.lyrics.textContent = lyrics;
                applyReaderPrefs(target.lyrics);
            } catch (e) {
                const target = getReaderTarget();
                target.lyrics.style.display = 'none';
                target.placeholder.style.display = 'block';
            }
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

            elements.nowPlaying.textContent = `Agora: Hino ${h.number}`;
            elements.stageTitle.textContent = `Hino ${h.number}`;
            elements.stageSub.textContent = h.title;
            elements.badgeNumber.style.display = '';
            elements.badgeNumber.textContent = `#${h.number}`;

            if (isMobile()) {
                elements.modalTitle.textContent = `Hino ${h.number}`;
                elements.modalSub.textContent = h.title;
                elements.modalBadge.style.display = '';
                elements.modalBadge.textContent = `#${h.number}`;
                elements.modalPlaceholder.style.display = 'block';
                elements.modalLyrics.style.display = 'none';
                hymnModal.show();
            }

            loadLyrics(h.number);

            animateFlip(getReaderTarget().body);

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

        if (elements.modalPrev) {
            elements.modalPrev.addEventListener('click', () => elements.prev.click());
        }
        if (elements.modalNext) {
            elements.modalNext.addEventListener('click', () => elements.next.click());
        }

        elements.font.addEventListener('click', () => {
            const order = ['normal', 'large', 'xlarge'];
            const idx = order.indexOf(state.fontSizeMode);
            state.fontSizeMode = order[(idx + 1) % order.length];
            localStorage.setItem('harpa_font', state.fontSizeMode);
            applyReaderPrefs(getReaderTarget().lyrics);
        });

        elements.line.addEventListener('click', () => {
            state.lineMode = state.lineMode === 'compact' ? 'normal' : 'compact';
            localStorage.setItem('harpa_line', state.lineMode);
            applyReaderPrefs(getReaderTarget().lyrics);
        });

        if (elements.modalFont) {
            elements.modalFont.addEventListener('click', () => elements.font.click());
        }
        if (elements.modalLine) {
            elements.modalLine.addEventListener('click', () => elements.line.click());
        }

        renderList();

        const initial = <?= json_encode($defaultNum, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        if (initial && Number.isFinite(Number(initial))) {
            const ok = setActiveByNumber(initial);
            if (!ok && initial > 0) showToast('Hino inicial não encontrado na lista.');
        }
    </script>
</body>
</html>

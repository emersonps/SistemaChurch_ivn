<?php
$siteProfile = getChurchSiteProfileSettings();
$albumCount = $albumCount ?? 0;
$yearRange = $yearRange ?? '';
$categories = $categories ?? [];
$categoryCounts = $categoryCounts ?? [];
$photosByYear = $photosByYear ?? [];
$totalPhotoCount = $totalPhotoCount ?? 0;
$galleryInitialLimit = 12;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria de Fotos - <?= htmlspecialchars(getChurchBrandingName($siteProfile)) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Lightbox CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gallery-gold: #d4af37;
            --gallery-wine: #8b1538;
            --gallery-wine-dark: #5a1026;
            --gallery-ink: rgba(15,18,28,0.92);
            --gallery-muted: rgba(15,18,28,0.62);
        }
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--gallery-ink);
            background:
                radial-gradient(circle at 14% 18%, rgba(255, 42, 122, 0.10), transparent 38%),
                radial-gradient(circle at 84% 22%, rgba(212, 175, 55, 0.14), transparent 40%),
                linear-gradient(180deg, #fffdfd 0%, #fff7f9 100%);
            padding-top: 86px;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.92) !important;
            box-shadow: 0 2px 14px rgba(0,0,0,0.08);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            backdrop-filter: blur(12px);
        }

        .navbar-brand {
            font-weight: 900;
            color: rgba(15,18,28,0.92) !important;
            display: inline-flex;
            align-items: center;
            gap: .65rem;
        }

        .navbar-brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .nav-link {
            color: rgba(15,18,28,0.70) !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            transition: color 0.2s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--gallery-gold) !important;
        }

        .nav-link.active {
            font-weight: 800;
        }

        .nav-link.active::after {
            content: "";
            position: absolute;
            left: 14%;
            right: 14%;
            bottom: -10px;
            height: 2px;
            border-radius: 999px;
            background: var(--gallery-gold);
        }

        .btn-cta {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(255,42,122,1) 0%, rgba(212,175,55,1) 100%);
            color: #090a15 !important;
            font-weight: 800;
            box-shadow: 0 14px 32px rgba(0,0,0,0.16);
            transition: transform .15s ease, filter .15s ease, box-shadow .15s ease;
        }

        .btn-cta:hover {
            filter: brightness(1.02);
            transform: translateY(-1px);
            box-shadow: 0 18px 42px rgba(0,0,0,0.20);
        }

        .btn-cta::after {
            content: "";
            position: absolute;
            top: -30%;
            left: -30%;
            width: 60%;
            height: 160%;
            background: rgba(255,255,255,0.35);
            transform: rotate(25deg) translateX(-140%);
            animation: ctaShimmer 3.2s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes ctaShimmer {
            0% { transform: rotate(25deg) translateX(-140%); opacity: 0; }
            12% { opacity: 0.35; }
            28% { transform: rotate(25deg) translateX(260%); opacity: 0; }
            100% { transform: rotate(25deg) translateX(260%); opacity: 0; }
        }

        .empty-state {
            border-radius: 24px;
            padding: 2.4rem 1.4rem;
            text-align: center;
            background: rgba(255,255,255,0.84);
            border: 1px dashed rgba(139,21,56,0.18);
            color: rgba(15,18,28,0.62);
        }

        @media (max-width: 575.98px) {
            body { padding-top: 78px; }
        }

        /* Gallery/Video Wall tab switcher */
        .gallery-tabs-wrap { display: flex; justify-content: center; padding-top: 1.6rem; }
        .gallery-tabs {
            display: inline-flex;
            gap: .3rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(15,18,28,0.08);
            border-radius: 999px;
            padding: .3rem;
            box-shadow: 0 10px 24px rgba(0,0,0,0.06);
        }
        .gallery-tab {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem 1.1rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: .86rem;
            color: var(--gallery-ink);
            text-decoration: none;
            border: none;
            background: transparent;
        }
        .gallery-tab.is-active { background: #212529; color: #fff; }
        .gallery-tab-panel.d-none { display: none; }

        .vw-filters { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 2rem; }
        .vw-filter-pill {
            padding: .45rem 1.1rem; border-radius: 999px; border: 1px solid #dee2e6;
            background: #fff; color: #343a40; font-weight: 600; font-size: .85rem; text-decoration: none;
        }
        .vw-filter-pill.is-active { background: #212529; color: #fff; border-color: #212529; }
        .vw-filter-divider { width: 1px; align-self: stretch; background: #dee2e6; margin: 0 .25rem; }
        .vw-filter-pill.vw-filter-live { display: inline-flex; align-items: center; gap: .4rem; }
        .vw-filter-pill.vw-filter-live::before {
            content: ''; width: 7px; height: 7px; border-radius: 50%; background: #dc3545;
        }
        .vw-filter-pill.vw-filter-ended::before {
            content: ''; width: 7px; height: 7px; border-radius: 50%; background: #adb5bd; display: inline-block; margin-right: .4rem;
        }
        .vw-card {
            background: #fff; border-radius: 16px; overflow: hidden; height: 100%;
            box-shadow: 0 .5rem 1.5rem rgba(0,0,0,0.06); display: flex; flex-direction: column;
        }
        .vw-card-thumb { position: relative; padding-top: 56.25%; background: #000; }
        .vw-card-thumb img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .vw-card-play {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 2.5rem; background: rgba(0,0,0,0.15); text-decoration: none;
        }
        .vw-card-category {
            position: absolute; top: .6rem; left: .6rem; background: rgba(0,0,0,0.7); color: #fff;
            font-size: .68rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px;
        }
        .vw-card-body { padding: 1rem 1.1rem; flex: 1; display: flex; flex-direction: column; }
        .vw-card-title { font-weight: 700; margin-bottom: .3rem; }
        .vw-card-meta { font-size: .8rem; color: #868e96; margin-bottom: .6rem; }
        .vw-card-desc { font-size: .85rem; color: #495057; margin-bottom: 1rem; flex: 1; }

        /* Header card */
        .gallery-page-wrap { padding: 1.6rem 0 4rem; }
        .gallery-header-card {
            background: #fff;
            border: 1px solid rgba(15,18,28,0.07);
            border-radius: 22px;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 18px 40px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .gallery-breadcrumb { font-size: .8rem; color: rgba(15,18,28,0.4); margin-bottom: .8rem; }
        .gallery-breadcrumb a { color: rgba(15,18,28,0.4); text-decoration: none; }
        .gallery-breadcrumb strong { color: var(--gallery-ink); font-weight: 700; }
        .gallery-header-row { display: flex; justify-content: space-between; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap; }
        .gallery-header-row h1 { font-weight: 900; font-size: 2rem; margin-bottom: .5rem; }
        .gallery-header-row p { color: var(--gallery-muted); max-width: 540px; margin-bottom: 0; }
        .gallery-header-meta { display: flex; align-items: center; gap: .85rem; flex-wrap: wrap; }
        .gallery-meta-text { color: var(--gallery-muted); font-size: .86rem; font-weight: 600; white-space: nowrap; }
        .gallery-meta-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #212529;
            color: #fff;
            font-size: .78rem;
            font-weight: 700;
            padding: .45rem .8rem;
            border-radius: 999px;
            white-space: nowrap;
        }

        /* Filters */
        .gallery-filters { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 2rem; }
        .gallery-filter-pill {
            border: 1px solid rgba(15,18,28,0.1);
            background: #fff;
            border-radius: 999px;
            padding: .5rem 1rem;
            font-size: .84rem;
            font-weight: 700;
            color: var(--gallery-ink);
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }
        .gallery-filter-pill .count { opacity: .6; }
        .gallery-filter-pill.is-active { background: #212529; border-color: #212529; color: #fff; }

        /* Year sections */
        .gallery-year-section { margin-bottom: 2.5rem; }
        .gallery-year-head { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .gallery-year-title { font-weight: 900; font-size: 1.15rem; white-space: nowrap; }
        .gallery-year-line { flex: 1; height: 1px; background: rgba(15,18,28,0.1); }
        .gallery-year-count { font-size: .74rem; font-weight: 700; letter-spacing: .04em; color: var(--gallery-muted); text-transform: uppercase; white-space: nowrap; }

        .gallery-photo-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-auto-rows: 190px;
            grid-auto-flow: dense;
            gap: 1rem;
        }
        .gallery-photo-item {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            background: rgba(15,18,28,0.06);
            box-shadow: 0 12px 26px rgba(0,0,0,0.08);
        }
        .gallery-photo-item:nth-child(3n) {
            grid-row: span 2;
        }
        .gallery-photo-item.is-hidden { display: none; }
        .gallery-photo-item a { display: block; width: 100%; height: 100%; text-decoration: none; }
        .gallery-photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .3s ease;
        }
        .gallery-photo-item:hover img { transform: scale(1.04); }
        .gallery-photo-caption {
            position: absolute;
            left: .6rem;
            bottom: .6rem;
            right: .6rem;
            background: rgba(255,255,255,0.94);
            color: var(--gallery-ink);
            font-size: .78rem;
            font-weight: 700;
            padding: .4rem .7rem;
            border-radius: 10px;
        }

        @media (max-width: 767.98px) {
            .gallery-photo-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-auto-rows: 160px; }
            .gallery-header-row { align-items: flex-start; }
        }
        @media (max-width: 480px) {
            .gallery-photo-grid { grid-template-columns: 1fr; }
            .gallery-photo-item:nth-child(3n) { grid-row: span 1; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/partials/example_content_banner.php'; ?>
    <nav class="navbar navbar-expand-lg fixed-top navbar-light">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?>" onerror="this.style.display='none'">
                <span><?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?></span>
            </a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGallery" aria-controls="navbarGallery" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarGallery">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="/">Início</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/galeria" aria-current="page">Galeria</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="gallery-tabs-wrap">
        <div class="gallery-tabs">
            <button type="button" class="gallery-tab is-active" data-gallery-tab="photos"><i class="fas fa-images"></i> Galeria de Fotos</button>
            <button type="button" class="gallery-tab" data-gallery-tab="videos"><i class="fas fa-clapperboard"></i> Mural de Vídeos</button>
        </div>
    </div>

    <div class="container gallery-page-wrap">
        <div id="galleryPanelPhotos" class="gallery-tab-panel">
            <div class="gallery-header-card">
                <nav class="gallery-breadcrumb"><a href="/">Início</a> › <strong>Galeria</strong></nav>
                <div class="gallery-header-row">
                    <div>
                        <h1>Galeria de Fotos</h1>
                        <p>Reviva os melhores momentos da nossa igreja. Cada imagem conta uma história de fé, comunhão e celebração.</p>
                    </div>
                    <div class="gallery-header-meta">
                        <span class="gallery-meta-text"><i class="far fa-calendar me-1"></i> <?= (int)$albumCount ?> <?= $albumCount === 1 ? 'álbum' : 'álbuns' ?><?= $yearRange !== '' ? ' • ' . htmlspecialchars($yearRange) : '' ?></span>
                        <span class="gallery-meta-badge"><i class="fas fa-arrows-rotate"></i> Atualizado semanalmente</span>
                    </div>
                </div>
            </div>

            <?php if (empty($photosByYear)): ?>
                <div class="empty-state">
                    <i class="fas fa-images fa-3x mb-3" style="color: rgba(15,18,28,0.26);"></i>
                    <h3 class="h4 mb-2">Nenhum álbum publicado ainda.</h3>
                    <p class="mb-0">Assim que os álbuns forem publicados, as fotos vão aparecer aqui.</p>
                </div>
            <?php else: ?>
                <div class="gallery-filters">
                    <button type="button" class="gallery-filter-pill is-active" data-gallery-filter="all">Todos <span class="count"><?= (int)$albumCount ?></span></button>
                    <?php foreach ($categories as $cat): ?>
                        <?php if (($categoryCounts[$cat] ?? 0) === 0) continue; ?>
                        <button type="button" class="gallery-filter-pill" data-gallery-filter="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?> <span class="count"><?= (int)$categoryCounts[$cat] ?></span></button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($photosByYear as $year => $yearPhotos): ?>
                    <div class="gallery-year-section" data-year-section>
                        <div class="gallery-year-head">
                            <span class="gallery-year-title"><?= (int)$year ?></span>
                            <span class="gallery-year-line"></span>
                            <span class="gallery-year-count" data-year-count><?= count($yearPhotos) ?> foto<?= count($yearPhotos) === 1 ? '' : 's' ?></span>
                        </div>
                        <div class="gallery-photo-grid">
                            <?php foreach ($yearPhotos as $photo): ?>
                                <div class="gallery-photo-item" data-gallery-photo data-category="<?= htmlspecialchars($photo['category']) ?>">
                                    <a href="<?= htmlspecialchars($photo['url']) ?>" data-lightbox="gallery-<?= (int)$year ?>" data-title="<?= htmlspecialchars($photo['album_title']) ?>">
                                        <img src="<?= htmlspecialchars($photo['url']) ?>" alt="<?= htmlspecialchars($photo['album_title'] ?: 'Foto da galeria') ?>" loading="lazy">
                                        <?php if ($photo['is_first_of_album'] && $photo['album_title'] !== ''): ?>
                                            <span class="gallery-photo-caption"><?= htmlspecialchars($photo['album_title']) ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($totalPhotoCount > $galleryInitialLimit): ?>
                    <div class="text-center mt-2 mb-4">
                        <button type="button" class="btn btn-outline-dark rounded-pill px-4" id="galleryLoadMoreBtn">Ver mais fotos</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div id="galleryPanelVideos" class="gallery-tab-panel d-none">
            <div class="gallery-header-card">
                <nav class="gallery-breadcrumb"><a href="/">Início</a> › <strong>Mural de Vídeos</strong></nav>
                <div class="gallery-header-row">
                    <div>
                        <h1>Mural de Vídeos</h1>
                        <p>Cultos, mensagens e momentos especiais da nossa igreja.</p>
                    </div>
                    <div class="gallery-header-meta">
                        <span class="gallery-meta-text"><i class="fas fa-video me-1"></i> <?= count($videos ?? []) ?> vídeo<?= count($videos ?? []) === 1 ? '' : 's' ?></span>
                    </div>
                </div>
            </div>

            <?php if (!empty($videos)): ?>
                <div class="vw-filters">
                    <button type="button" class="vw-filter-pill is-active" data-video-filter="all">Todos</button>
                    <?php foreach (getVideoWallCategories() as $vcat): ?>
                        <button type="button" class="vw-filter-pill" data-video-filter="<?= htmlspecialchars($vcat) ?>"><?= htmlspecialchars($vcat) ?></button>
                    <?php endforeach; ?>
                    <span class="vw-filter-divider"></span>
                    <button type="button" class="vw-filter-pill vw-filter-live" data-video-status-filter="ao_vivo">Ao Vivo</button>
                    <button type="button" class="vw-filter-pill vw-filter-ended" data-video-status-filter="encerrado">Encerrados</button>
                </div>
            <?php endif; ?>

            <?php include __DIR__ . '/partials/video_wall_cards.php'; ?>
        </div>
    </div>

    <?php include __DIR__ . '/partials/floating_faith_widget.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
        (function () {
            var INITIAL_LIMIT = <?= (int)$galleryInitialLimit ?>;
            var state = { category: 'all', revealCount: INITIAL_LIMIT };

            var pills = document.querySelectorAll('[data-gallery-filter]');
            var photos = document.querySelectorAll('[data-gallery-photo]');
            var yearSections = document.querySelectorAll('[data-year-section]');
            var loadMoreBtn = document.getElementById('galleryLoadMoreBtn');

            function applyFilters() {
                var visibleIndex = 0;
                photos.forEach(function (photo) {
                    var matchesCategory = state.category === 'all' || photo.getAttribute('data-category') === state.category;
                    var show = false;
                    if (matchesCategory) {
                        show = visibleIndex < state.revealCount;
                        visibleIndex++;
                    }
                    photo.classList.toggle('is-hidden', !show);
                });

                yearSections.forEach(function (section) {
                    var visiblePhotos = section.querySelectorAll('[data-gallery-photo]:not(.is-hidden)');
                    section.style.display = visiblePhotos.length > 0 ? '' : 'none';
                    var countEl = section.querySelector('[data-year-count]');
                    if (countEl) {
                        countEl.textContent = visiblePhotos.length + (visiblePhotos.length === 1 ? ' foto' : ' fotos');
                    }
                });

                if (loadMoreBtn) {
                    loadMoreBtn.style.display = visibleIndex > state.revealCount ? '' : 'none';
                }
            }

            pills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    pills.forEach(function (p) { p.classList.remove('is-active'); });
                    pill.classList.add('is-active');
                    state.category = pill.getAttribute('data-gallery-filter');
                    state.revealCount = INITIAL_LIMIT;
                    applyFilters();
                });
            });

            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function () {
                    state.revealCount = Infinity;
                    applyFilters();
                });
            }

            applyFilters();
        })();

        (function () {
            var tabs = document.querySelectorAll('[data-gallery-tab]');
            var panels = {
                photos: document.getElementById('galleryPanelPhotos'),
                videos: document.getElementById('galleryPanelVideos')
            };

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var target = tab.getAttribute('data-gallery-tab');
                    tabs.forEach(function (t) { t.classList.remove('is-active'); });
                    tab.classList.add('is-active');
                    Object.keys(panels).forEach(function (key) {
                        if (panels[key]) {
                            panels[key].classList.toggle('d-none', key !== target);
                        }
                    });
                });
            });
        })();

        (function () {
            var videoState = { category: 'all', status: '' };
            var categoryPills = document.querySelectorAll('[data-video-filter]');
            var statusPills = document.querySelectorAll('[data-video-status-filter]');
            var videoCards = document.querySelectorAll('[data-video-card]');

            function applyVideoFilters() {
                videoCards.forEach(function (card) {
                    var show;
                    if (videoState.status !== '') {
                        show = card.getAttribute('data-live-status') === videoState.status;
                    } else if (videoState.category !== 'all') {
                        show = card.getAttribute('data-category') === videoState.category;
                    } else {
                        show = true;
                    }
                    card.classList.toggle('d-none', !show);
                });
            }

            categoryPills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    videoState.category = pill.getAttribute('data-video-filter');
                    videoState.status = '';
                    categoryPills.forEach(function (p) { p.classList.remove('is-active'); });
                    statusPills.forEach(function (p) { p.classList.remove('is-active'); });
                    pill.classList.add('is-active');
                    applyVideoFilters();
                });
            });

            statusPills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    videoState.status = pill.getAttribute('data-video-status-filter');
                    videoState.category = 'all';
                    categoryPills.forEach(function (p) { p.classList.remove('is-active'); });
                    statusPills.forEach(function (p) { p.classList.remove('is-active'); });
                    pill.classList.add('is-active');
                    applyVideoFilters();
                });
            });
        })();
    </script>
    <?php include __DIR__ . '/../partials/livestream_badge.php'; ?>
</body>
</html>

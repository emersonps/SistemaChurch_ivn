<?php
$siteProfile = getChurchSiteProfileSettings();
$albumCount = isset($albums) && is_array($albums) ? count($albums) : 0;
$highlightPhotosCount = 0;
if (!empty($albums) && is_array($albums)) {
    foreach ($albums as $album) {
        $highlightPhotosCount += isset($album['photos']) && is_array($album['photos']) ? count($album['photos']) : 0;
    }
}
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

        .gallery-hero {
            padding: 2.6rem 0 1.6rem;
        }

        .hero-shell {
            border-radius: 28px;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(139,21,56,0.96), rgba(90,16,38,0.94));
            color: #fff;
            box-shadow: 0 28px 60px rgba(90,16,38,0.18);
            position: relative;
            overflow: hidden;
        }

        .hero-shell::after {
            content: "";
            position: absolute;
            inset: auto -48px -48px auto;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }

        .hero-shell::before {
            content: "";
            position: absolute;
            inset: -2px;
            border-radius: 30px;
            background: linear-gradient(120deg, rgba(255,42,122,0.36), rgba(212,175,55,0.34), rgba(255,255,255,0.16));
            filter: blur(18px);
            opacity: 0.55;
            z-index: -1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .42rem .8rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.14);
            color: #fff;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 1rem;
        }

        .hero-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 900;
            line-height: 1.08;
            margin: 0 0 .75rem;
        }

        .hero-copy {
            margin: 0;
            max-width: 46rem;
            color: rgba(255,255,255,0.86);
            font-size: 1rem;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .9rem;
        }

        .hero-stat {
            border-radius: 22px;
            padding: 1rem 1.05rem;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .hero-stat strong {
            display: block;
            font-size: 1.7rem;
            line-height: 1;
            margin-bottom: .35rem;
        }

        .hero-stat span {
            color: rgba(255,255,255,0.82);
            font-size: .92rem;
        }

        .albums-wrap {
            padding: 0 0 4.2rem;
        }

        .album-card {
            border-radius: 22px;
            overflow: hidden;
            background: rgba(255,255,255,0.94);
            border: 1px solid rgba(139,21,56,0.08);
            box-shadow: 0 18px 45px rgba(49,24,31,0.08);
            margin-bottom: 1.2rem;
        }

        .album-hero {
            position: relative;
            min-height: 160px;
            padding: 1.35rem 1.35rem 1.25rem;
            background:
                radial-gradient(circle at 12% 18%, rgba(212,175,55,0.22), transparent 40%),
                linear-gradient(135deg, rgba(15,18,28,0.86), rgba(90,16,38,0.86));
            color: #fff;
            overflow: hidden;
        }

        .album-hero.has-cover {
            background-size: cover;
            background-position: center;
        }

        .album-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.20), rgba(0,0,0,0.62));
        }

        .album-hero > * {
            position: relative;
            z-index: 1;
        }

        .album-title {
            font-weight: 900;
            margin: 0;
            font-size: 1.3rem;
        }

        .album-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
            margin-top: .65rem;
            color: rgba(255,255,255,0.86);
            font-weight: 700;
            font-size: .9rem;
        }

        .album-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .38rem .7rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.14);
        }

        .album-desc {
            padding: 1rem 1.35rem 0;
            color: var(--gallery-muted);
            font-size: .95rem;
        }

        .album-desc strong {
            color: rgba(15,18,28,0.78);
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
            padding: 1.15rem 1.35rem 1.35rem;
        }

        @media (min-width: 576px) {
            .photo-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (min-width: 992px) {
            .photo-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .photo-item {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            background: rgba(15,18,28,0.06);
            border: 1px solid rgba(15,18,28,0.08);
            box-shadow: 0 14px 28px rgba(0,0,0,0.10);
        }

        .photo-item a {
            display: block;
            text-decoration: none;
            color: inherit;
        }

        .photo-item img {
            width: 100%;
            height: auto;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            transition: transform .35s ease, filter .35s ease;
            display: block;
        }

        .photo-item:hover img {
            transform: scale(1.04);
            filter: saturate(1.04);
        }

        .photo-overlay {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 30%, rgba(255,255,255,0.06), rgba(0,0,0,0.58));
            opacity: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity .2s ease;
        }

        .photo-item:hover .photo-overlay {
            opacity: 1;
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
            .hero-shell { padding: 1.5rem; border-radius: 24px; }
            .hero-stats { gap: .65rem; }
            .hero-stat { border-radius: 18px; padding: .85rem .9rem; }
            .hero-stat strong { font-size: 1.42rem; }
            .hero-stat span { font-size: .82rem; }
            .album-desc { padding-left: 1.1rem; padding-right: 1.1rem; }
            .photo-grid { padding-left: 1.1rem; padding-right: 1.1rem; gap: .65rem; }
        }

        .harpa-fab {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 1200;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .72rem 1rem;
            border-radius: 999px;
            text-decoration: none;
            background: linear-gradient(135deg, rgba(255,42,122,1) 0%, rgba(212,175,55,1) 100%);
            color: #090a15;
            font-weight: 900;
            box-shadow: 0 14px 32px rgba(0,0,0,0.22);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
        }
        .harpa-fab:hover {
            color: #090a15;
            filter: brightness(1.02);
            transform: translateY(-2px);
            box-shadow: 0 18px 40px rgba(0,0,0,0.28);
        }
        .harpa-fab .label {
            display: none;
        }
        @media (min-width: 768px) {
            .harpa-fab .label {
                display: inline;
            }
        }
    </style>
</head>
<body>
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
                    <li class="nav-item"><a class="nav-link" href="/oracao">Oração</a></li>
                    <li class="nav-item"><a class="nav-link" href="/devocional">Devocional</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/galeria" aria-current="page">Galeria</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contato">Contato</a></li>
                    <li class="nav-item ms-lg-2">
                        <a href="#albuns" class="btn btn-cta btn-sm px-3 text-nowrap"><i class="fas fa-images me-2"></i>Ver álbuns</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="gallery-hero" id="inicio">
        <div class="container">
            <div class="hero-shell">
                <div class="row g-4 align-items-end">
                    <div class="col-lg-8">
                        <span class="hero-badge"><i class="fas fa-camera-retro"></i> Mural de Fotos</span>
                        <h1 class="hero-title">Momentos marcantes da comunidade</h1>
                        <p class="hero-copy">Álbuns organizados por evento. Toque em uma foto para abrir e deslize para ver as próximas.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stats">
                            <div class="hero-stat">
                                <strong><?= (int)$albumCount ?></strong>
                                <span>Álbuns publicados</span>
                            </div>
                            <div class="hero-stat">
                                <strong><?= (int)$highlightPhotosCount ?></strong>
                                <span>Fotos em destaque</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="albums-wrap" id="albuns">
        <div class="container">
        <?php if (empty($albums)): ?>
            <div class="empty-state">
                <i class="fas fa-images fa-3x mb-3" style="color: rgba(15,18,28,0.26);"></i>
                <h3 class="h4 mb-2">Nenhum álbum publicado ainda.</h3>
                <p class="mb-0">Assim que os álbuns forem publicados, as fotos vão aparecer aqui.</p>
            </div>
        <?php else: ?>
            <?php foreach ($albums as $album): ?>
                <?php
                    $albumTitle = (string)($album['title'] ?? '');
                    $albumLocation = (string)($album['location'] ?? '');
                    $albumDescription = (string)($album['description'] ?? '');
                    $albumDateRaw = (string)($album['event_date'] ?? '');
                    $albumDate = $albumDateRaw !== '' ? date('d/m/Y', strtotime($albumDateRaw)) : '';
                    $cover = '';
                    if (!empty($album['photos']) && is_array($album['photos'])) {
                        $first = $album['photos'][0] ?? null;
                        if (is_array($first) && !empty($first['filename'])) {
                            $cover = '/uploads/gallery/' . ltrim((string)$first['filename'], '/');
                        }
                    }
                    $heroStyle = $cover !== '' ? ' style="background-image:url(\'' . htmlspecialchars($cover) . '\')"' : '';
                    $heroClass = $cover !== '' ? 'album-hero has-cover' : 'album-hero';
                ?>
                <div class="album-card">
                    <div class="<?= $heroClass ?>"<?= $heroStyle ?>>
                        <h2 class="album-title"><?= htmlspecialchars($albumTitle) ?></h2>
                        <div class="album-meta" aria-label="Informações do álbum">
                            <?php if ($albumDate !== ''): ?>
                                <span class="album-chip"><i class="far fa-calendar"></i><?= htmlspecialchars($albumDate) ?></span>
                            <?php endif; ?>
                            <?php if ($albumLocation !== ''): ?>
                                <span class="album-chip"><i class="fas fa-location-dot"></i><?= htmlspecialchars($albumLocation) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($albumDescription !== ''): ?>
                        <div class="album-desc">
                            <strong>Descrição:</strong> <?= htmlspecialchars($albumDescription) ?>
                        </div>
                    <?php endif; ?>
                    <div class="photo-grid" aria-label="Fotos do álbum">
                        <?php foreach (($album['photos'] ?? []) as $photo): ?>
                            <?php $file = (string)($photo['filename'] ?? ''); ?>
                            <?php if ($file === '') continue; ?>
                            <?php $url = '/uploads/gallery/' . ltrim($file, '/'); ?>
                            <div class="photo-item">
                                <a href="<?= htmlspecialchars($url) ?>" data-lightbox="album-<?= (int)($album['id'] ?? 0) ?>" data-title="<?= htmlspecialchars($albumTitle) ?>">
                                    <img src="<?= htmlspecialchars($url) ?>" alt="Foto do álbum">
                                    <div class="photo-overlay" aria-hidden="true">
                                        <i class="fas fa-magnifying-glass-plus fa-2x text-white"></i>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($album['photos'])): ?>
                            <div class="empty-state" style="padding: 1.4rem 1.2rem; grid-column: 1 / -1;">
                                <i class="fas fa-image fa-2x mb-2" style="color: rgba(15,18,28,0.26);"></i>
                                <div class="fw-bold">Álbum sem fotos</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>

    <a href="/harpa" class="harpa-fab" aria-label="Abrir Harpa Cristã" title="Harpa Cristã">
        <i class="fas fa-music"></i>
        <span class="label">Harpa Cristã</span>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
</body>
</html>

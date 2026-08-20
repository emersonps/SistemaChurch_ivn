<?php
$siteProfile = getChurchSiteProfileSettings();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mural de Vídeos - <?= htmlspecialchars(getChurchBrandingName($siteProfile)) ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-top: 76px; background: #f8f9fa; }
        .navbar { background-color: rgba(255,255,255,0.96) !important; box-shadow: 0 2px 14px rgba(0,0,0,0.08); }
        .vw-hero { padding: 3rem 0 2rem; text-align: center; }
        .vw-filters { display: flex; flex-wrap: wrap; justify-content: center; gap: .5rem; margin-bottom: 2.5rem; }
        .vw-filter-pill {
            padding: .45rem 1.1rem; border-radius: 999px; border: 1px solid #dee2e6;
            background: #fff; color: #343a40; font-weight: 600; font-size: .85rem; text-decoration: none;
        }
        .vw-filter-pill.is-active { background: #212529; color: #fff; border-color: #212529; }
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
    </style>
</head>
<body>
<?php include __DIR__ . '/partials/example_content_banner.php'; ?>
    <nav class="navbar navbar-expand-lg fixed-top navbar-light">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>" alt="" style="height: 32px; width: auto; object-fit: contain;" onerror="this.style.display='none'">
                <?= htmlspecialchars(getChurchBrandingAlias($siteProfile)) ?>
            </a>
            <a href="/" class="btn btn-outline-dark btn-sm rounded-pill px-3"><i class="fas fa-house me-1"></i> Início</a>
        </div>
    </nav>

    <div class="vw-hero">
        <div class="container">
            <h1 class="fw-bold">Mural de Vídeos</h1>
            <p class="text-muted">Cultos, mensagens e momentos especiais da nossa igreja</p>
        </div>
    </div>

    <div class="container">
        <div class="vw-filters">
            <a href="/mural-de-videos" class="vw-filter-pill <?= $selectedCategory === '' ? 'is-active' : '' ?>">Todos</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/mural-de-videos?category=<?= urlencode($cat) ?>" class="vw-filter-pill <?= $selectedCategory === $cat ? 'is-active' : '' ?>"><?= htmlspecialchars($cat) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($videos)): ?>
            <div class="text-center text-muted py-5">Nenhum vídeo disponível ainda.</div>
        <?php else: ?>
            <div class="row g-4 pb-5">
                <?php foreach ($videos as $video): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="vw-card">
                            <div class="vw-card-thumb">
                                <img src="https://img.youtube.com/vi/<?= htmlspecialchars($video['youtube_video_id']) ?>/hqdefault.jpg" alt="">
                                <span class="vw-card-category"><?= htmlspecialchars($video['category']) ?></span>
                                <a href="/mural-de-videos/assistir/<?= (int)$video['id'] ?>" class="vw-card-play" target="_blank" rel="noopener">
                                    <i class="fas fa-circle-play"></i>
                                </a>
                            </div>
                            <div class="vw-card-body">
                                <div class="vw-card-title"><?= htmlspecialchars($video['title']) ?></div>
                                <div class="vw-card-meta">
                                    <?= !empty($video['video_date']) ? date('d/m/Y', strtotime($video['video_date'])) : '' ?>
                                    <?php if (!empty($video['speaker'])): ?> · <?= htmlspecialchars($video['speaker']) ?><?php endif; ?>
                                </div>
                                <?php if (!empty($video['description'])): ?>
                                    <div class="vw-card-desc"><?= htmlspecialchars($video['description']) ?></div>
                                <?php endif; ?>
                                <a href="/mural-de-videos/assistir/<?= (int)$video['id'] ?>" class="btn btn-dark btn-sm mt-auto" target="_blank" rel="noopener">
                                    <i class="fas fa-play me-1"></i> Assistir Agora
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

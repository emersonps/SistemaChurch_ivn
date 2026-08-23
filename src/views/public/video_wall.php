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
        <?php $selectedStatus = $selectedStatus ?? ''; ?>
        <div class="vw-filters">
            <a href="/mural-de-videos" class="vw-filter-pill <?= ($selectedCategory === '' && $selectedStatus === '') ? 'is-active' : '' ?>">Todos</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/mural-de-videos?category=<?= urlencode($cat) ?>" class="vw-filter-pill <?= $selectedCategory === $cat ? 'is-active' : '' ?>"><?= htmlspecialchars($cat) ?></a>
            <?php endforeach; ?>
            <span class="vw-filter-divider"></span>
            <a href="/mural-de-videos?status=ao_vivo" class="vw-filter-pill vw-filter-live <?= $selectedStatus === 'ao_vivo' ? 'is-active' : '' ?>">Ao Vivo</a>
            <a href="/mural-de-videos?status=encerrado" class="vw-filter-pill vw-filter-ended <?= $selectedStatus === 'encerrado' ? 'is-active' : '' ?>">Encerrados</a>
        </div>

        <?php include __DIR__ . '/partials/video_wall_cards.php'; ?>
    </div>

    <?php include __DIR__ . '/partials/floating_faith_widget.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include __DIR__ . '/../partials/livestream_badge.php'; ?>
</body>
</html>

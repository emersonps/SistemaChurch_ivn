<?php $siteProfile = getChurchSiteProfileSettings(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria de Fotos - <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>?v=1">
    
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
            --primary-gold: #d4af37;
            --primary-red: #b30000;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }

        /* Simple Header Style */
        .simple-header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand-logo {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand-logo img {
            height: 40px;
            width: auto;
        }
        .nav-link-home {
            color: #333;
            font-weight: 600;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s;
        }
        .nav-link-home:hover {
            background-color: var(--primary-red);
            color: white;
        }
        
        .gallery-header {
            background-color: #1a1a1a;
            color: white;
            padding: 40px 0;
            text-align: center;
            border-bottom: 5px solid var(--primary-gold);
        }

        /* Gallery Styles Restored */
        .album-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .album-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .album-header {
            background-color: var(--primary-red);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            padding: 20px;
        }
        
        .photo-item {
            position: relative;
            height: 150px;
            overflow: hidden;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .photo-item:hover img {
            transform: scale(1.1);
        }
        
        .overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .photo-item:hover .overlay {
            opacity: 1;
        }
    </style>
</head>
<body>

    <!-- Simple Public Header -->
    <header class="simple-header">
        <div class="container header-container">
            <a href="/" class="brand-logo">
                <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?> Logo">
                <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>
            </a>
            <a href="/" class="nav-link-home">
                <i class="fas fa-home me-1"></i> Início
            </a>
        </div>
    </header>

    <div class="gallery-header">
        <div class="container">
            <h1 class="display-5 fw-bold">Mural de Fotos</h1>
            <p class="lead mb-0">Momentos marcantes da nossa comunidade</p>
        </div>
    </div>

    <div class="container py-5">
        <?php if (empty($albums)): ?>
            <div class="text-center py-5">
                <i class="fas fa-images fa-4x text-muted mb-3"></i>
                <h3 class="text-muted">Nenhum álbum publicado ainda.</h3>
            </div>
        <?php else: ?>
            <?php foreach ($albums as $album): ?>
                <div class="album-card">
                    <div class="album-header">
                        <h4 class="mb-0 fw-bold"><?= htmlspecialchars($album['title']) ?></h4>
                        <small><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($album['event_date'])) ?></small>
                    </div>
                    <div class="p-3 bg-light border-bottom">
                        <p class="mb-0 text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= htmlspecialchars($album['location']) ?> - <?= htmlspecialchars($album['description']) ?></p>
                    </div>
                    <div class="photo-grid">
                        <?php foreach ($album['photos'] as $photo): ?>
                            <div class="photo-item">
                                <a href="/uploads/gallery/<?= $photo['filename'] ?>" data-lightbox="album-<?= $album['id'] ?>" data-title="<?= htmlspecialchars($album['title']) ?>">
                                    <img src="/uploads/gallery/<?= $photo['filename'] ?>" alt="Foto">
                                    <div class="overlay">
                                        <i class="fas fa-search-plus fa-2x text-white"></i>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($album['photos'])): ?>
                            <p class="text-muted col-12 text-center py-3">Álbum sem fotos.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
</body>
</html>

<?php $siteProfile = getChurchSiteProfileSettings(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Dynamic SEO Tags -->
    <title><?= $seo_title ?? (($siteProfile['alias'] ?? 'Igreja') . ' - ' . ($siteProfile['name'] ?? 'Nossa Igreja')) ?></title>
    <meta name="description" content="<?= $seo_description ?? ('A ' . ($siteProfile['name'] ?? $siteProfile['alias'] ?? 'igreja') . ' é uma comunidade cristã comprometida com a proclamação do Evangelho, edificação da família e adoração a Deus.') ?>">
    <meta name="keywords" content="<?= htmlspecialchars(implode(', ', array_unique(array_filter(['igreja', 'assembleia de deus', $siteProfile['alias'] ?? $siteProfile['name'] ?? 'igreja', 'culto', 'evangelho', 'jesus', 'família', 'adoração'])))) ?>">
    <meta name="author" content="<?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="<?= $seo_title ?? (($siteProfile['alias'] ?? 'Igreja') . ' - ' . ($siteProfile['name'] ?? 'Nossa Igreja')) ?>">
    <meta property="og:description" content="<?= $seo_description ?? 'Venha adorar a Deus conosco!' ?>">
    <meta property="og:image" content="<?= $seo_image ?? ($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>">
    <meta property="og:url" content="<?= $_SERVER['REQUEST_URI'] ?? '' ?>">
    <meta property="og:type" content="website">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>" type="image/png">
    <link rel="icon" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    
    <!-- PWA / Web App Manifest -->
    <link rel="manifest" href="<?= htmlspecialchars(getChurchManifestUrl($siteProfile)) ?>">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars(getChurchBrandingAlias($siteProfile)) ?>">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gold: #d4af37; /* Dourado */
            --primary-gold-dark: #aa8c2c;
            --primary-red: #b30000; /* Vermelho */
            --primary-red-dark: #800000;
            --text-dark: #333;
        }
        
        body { font-family: 'Poppins', sans-serif; color: var(--text-dark); }
        
        /* Navbar Dark Mode */
        .navbar {
            background-color: #000 !important;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(255,255,255,0.1);
        }

        /* Utility Classes */
        .text-gold { color: var(--primary-gold) !important; }
        .text-red { color: var(--primary-red) !important; }
        .bg-gold { background-color: var(--primary-gold) !important; }
        .bg-red { background-color: var(--primary-red) !important; }

        .navbar-brand {
            font-weight: 700;
            color: #fff !important;
            font-size: 1.5rem;
            letter-spacing: 1px;
        }
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 400;
            margin: 0 10px;
            font-size: 1rem;
            transition: color 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--primary-gold) !important;
        }

        /* Buttons */
        .btn-outline-gold {
            color: var(--primary-gold);
            border: 1px solid var(--primary-gold);
            padding: 5px 15px;
            border-radius: 5px;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-outline-gold:hover {
            background-color: var(--primary-gold);
            color: #000;
        }

        /* Hero Buttons Customization */
        .hero-section .btn {
            padding: 12px 35px;
            font-weight: 600;
            border-radius: 50px; /* Ensure pill shape */
            transition: all 0.3s ease;
        }
        
        .hero-section .btn-primary {
            background-color: #b30000 !important; /* Red */
            border-color: #b30000 !important;
            color: white !important;
        }
        
        .hero-section .btn-primary:hover {
            background-color: #990000 !important;
            border-color: #990000 !important;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .hero-section .btn-outline-light {
            border: 2px solid rgba(255,255,255,0.8) !important;
            color: white !important;
            background: transparent !important;
        }
        
        .hero-section .btn-outline-light:hover {
            background-color: white !important;
            color: #b30000 !important;
            border-color: white !important;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1438232992991-995b7058bbb3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            height: 90vh;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-bottom: 5px solid var(--primary-gold);
        }
        .hero-title {
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .hero-highlight {
            color: var(--primary-gold);
        }

        /* Section Headers */
        .section-title {
            color: var(--primary-red);
            font-weight: 700;
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: var(--primary-gold);
            margin: 10px auto 0;
        }

        /* Cards */
        .service-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-top: 4px solid var(--primary-gold);
        }
        .service-card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .service-card i {
            color: var(--primary-gold) !important;
        }
        
        .event-card {
            border: none;
            border-left: 5px solid var(--primary-red);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .event-card .badge {
            background-color: var(--primary-gold) !important;
            color: #000;
        }

        /* Footer */
        .footer { 
            background: #1a1a1a; 
            color: white; 
            padding: 60px 0 30px; 
            border-top: 5px solid var(--primary-gold);
        }
        .footer h5 {
            color: var(--primary-gold);
            font-weight: 600;
            margin-bottom: 20px;
        }
        .social-icon {
            color: white;
            transition: color 0.3s;
        }
        .social-icon:hover {
            color: var(--primary-gold);
        }

        /* Custom Pills for Tabs (Cultos, etc) - Highly Specific */
        #pills-tab .nav-link, #pills-tab-eventos .nav-link {
            color: #b30000 !important; /* Red text for inactive */
            background-color: #fff !important; /* White background for inactive */
            border: 1px solid #ddd !important;
            font-weight: 600 !important;
            margin: 0 5px;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            border-radius: 50px !important; /* Ensure rounded */
            white-space: nowrap !important; /* Prevent text wrapping */
        }
        
        #pills-tab .nav-link:hover, #pills-tab-eventos .nav-link:hover {
            color: #fff !important; /* White text on hover */
            background-color: #b30000 !important; /* Red background on hover */
            border-color: #b30000 !important;
            transform: translateY(-1px);
        }
        
        #pills-tab .nav-link.active, #pills-tab-eventos .nav-link.active {
            background-color: #b30000 !important; /* Red Solid for active */
            color: #fff !important; /* White text for active */
            border-color: #b30000 !important;
            box-shadow: 0 4px 10px rgba(179, 0, 0, 0.3) !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?>" height="50" class="me-2">
                <span class="d-none d-md-block fw-bold"><?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="/">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#sobre">Sobre</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#cultos">Cultos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#eventos">Eventos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#convites">Convites</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#congregacoes">Congregações</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#contato">Contato</a></li>
                    <li class="nav-item"><a class="nav-link" href="/portal/login"><i class="fas fa-user me-1"></i> Área do Membro</a></li>
                </ul>
            </div>
        </div>
    </nav>

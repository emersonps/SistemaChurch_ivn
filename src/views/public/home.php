<?php
$siteProfile = getChurchSiteProfileSettings();
$firstAndLastName = function ($name) {
    $name = trim((string)$name);
    if ($name === '') {
        return '';
    }

    $parts = preg_split('/\s+/', $name);
    if (!$parts || count($parts) === 1) {
        return $name;
    }

    return ($parts[0] ?? '') . ' ' . ($parts[count($parts) - 1] ?? '');
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($siteProfile['alias'] ?? 'Igreja') . ' - ' . ($siteProfile['name'] ?? 'Nossa Igreja')) ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>" type="image/png">
    <link rel="icon" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    <!-- PWA / Web App Manifest -->
    <link rel="manifest" href="<?= htmlspecialchars(getChurchManifestUrl($siteProfile)) ?>">
    <meta name="theme-color" content="#b30000">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars(getChurchBrandingAlias($siteProfile)) ?>">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Inter:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Roboto:wght@300;400;500;700&family=Montserrat:wght@300;400;600;700&family=Cinzel:wght@400;600;700&family=Nunito:wght@300;400;600;700&family=Lato:wght@300;400;700&family=Lora:ital,wght@0,400;0,600;1,400&family=Raleway:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Theme Variables Injected from Database */
            <?php if (isset($site_settings['theme_id']) && $site_settings['theme_id'] === 'theme-0'): ?>
            --primary-gold: #d4af37;
            --primary-gold-dark: #aa8c2c;
            --primary-red: #b30000;
            --primary-red-dark: #800000;
            <?php else: ?>
            --primary-gold: <?= $site_settings['primary_color'] ?? '#d4af37' ?>; 
            --primary-gold-dark: <?= $site_settings['secondary_color'] ?? '#aa8c2c' ?>;
            --primary-red: <?= $site_settings['primary_color'] ?? '#b30000' ?>; 
            --primary-red-dark: <?= $site_settings['secondary_color'] ?? '#800000' ?>;
            <?php endif; ?>
            --text-dark: #333;
        }
        
        body { font-family: '<?= explode(',', $site_settings['font_family'] ?? 'Poppins, sans-serif')[0] ?>', sans-serif; color: var(--text-dark); padding-bottom: 116px; }
        
        /* Custom Colors */
        .text-gold { color: var(--primary-gold) !important; }
        .text-red { color: var(--primary-red) !important; }
        .bg-gold { background-color: var(--primary-gold) !important; }
        .bg-red { background-color: var(--primary-red) !important; }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
            color: white;
            font-weight: 600;
            padding: 10px 30px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: var(--primary-red-dark);
            border-color: var(--primary-red-dark);
            transform: translateY(-2px);
        }
        
        .btn-outline-gold {
            color: var(--primary-gold);
            border-color: var(--primary-gold);
            font-weight: 600;
        }
        .btn-outline-gold:hover {
            background-color: var(--primary-gold);
            color: white;
        }

        .hero-section .btn-outline-light {
            border: 2px solid rgba(255,255,255,0.8) !important;
            color: white !important;
            background: transparent !important;
            transition: all 0.3s;
        }
        
        .hero-section .btn-outline-light:hover {
            background-color: white !important;
            color: var(--primary-red) !important;
            border-color: white !important;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        /* Navbar */
        .navbar {
            background-color: rgba(0, 0, 0, 0.95) !important; /* Dark background to make gold pop */
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand img {
            height: 50px; /* Ajuste conforme a logo */
            width: auto;
        }
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            transition: color 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--primary-gold) !important;
        }
        
        /* Hero Section */
        <?php
            // Lógica para definir a imagem de fundo corretamente
            $bgUrl = "https://images.unsplash.com/photo-1438232992991-995b7058bbb3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"; // Fallback geral
            
            if (isset($site_settings['hero_bg_image'])) {
                if (strpos($site_settings['hero_bg_image'], 'custom_hero_') === 0) {
                    // É uma imagem que o usuário fez upload
                    $bgUrl = "/assets/uploads/themes/" . $site_settings['hero_bg_image'];
                } else {
                    // É uma imagem padrão de um tema (que não existe fisicamente, pegamos a URL do Unsplash)
                    $unsplash_fallbacks = [
                        'theme-0' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-1' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-2' => 'https://images.unsplash.com/photo-1504052434569-70ad5836ab65?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-3' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-4' => 'https://images.unsplash.com/photo-1502759683299-cdcd6974244f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-5' => 'https://images.unsplash.com/photo-1550684848-fac1c5b4e853?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-6' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-7' => 'https://images.unsplash.com/photo-1498623116890-37e912163d5d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-8' => 'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-9' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80',
                        'theme-10'=> 'https://images.unsplash.com/photo-1518621736915-f3b1c41bfd00?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80'
                    ];
                    $bgUrl = $unsplash_fallbacks[$site_settings['theme_id']] ?? $bgUrl;
                }
            }
        ?>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.68), rgba(0,0,0,0.68)), url('<?= $bgUrl ?>');
            background-size: cover;
            background-position: center;
            min-height: 90vh;
            color: #fff;
            display: flex;
            align-items: center;
            text-align: left;
            border-bottom: 5px solid var(--primary-gold);
            position: relative;
            padding: 130px 0 90px;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 18% 22%, rgba(255,42,122,0.20), transparent 55%),
                radial-gradient(circle at 78% 28%, rgba(212,175,55,0.18), transparent 60%),
                linear-gradient(rgba(0,0,0,0.74), rgba(0,0,0,0.74));
            pointer-events: none;
        }
        .hero-section > .container { position: relative; z-index: 2; }
        .hero-title {
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.05;
            text-shadow: 0 18px 50px rgba(0,0,0,0.45);
        }
        .hero-highlight { color: #ff2a7a; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: .75rem; }
        .hero-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-hero-primary {
            background: linear-gradient(135deg, #ff2a7a 0%, var(--primary-red) 100%);
            border: none;
            color: #fff;
            font-weight: 800;
            padding: 12px 26px;
            border-radius: 999px;
            box-shadow: 0 16px 34px rgba(179,0,0,0.30);
            transition: transform .15s ease, filter .15s ease, box-shadow .15s ease;
        }
        .btn-hero-primary:hover { filter: brightness(1.03); transform: translateY(-1px); box-shadow: 0 20px 44px rgba(179,0,0,0.34); }
        .btn-hero-secondary {
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.35);
            color: #fff;
            font-weight: 800;
            padding: 12px 22px;
            border-radius: 999px;
            backdrop-filter: blur(8px);
            transition: background .15s ease, transform .15s ease;
        }
        .btn-hero-secondary:hover { background: rgba(255,255,255,0.16); color: #fff; transform: translateY(-1px); }

        .hero-countdown-panel {
            background: rgba(255,255,255,0.96);
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(0,0,0,0.28);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(10px);
        }
        .hero-countdown-header { position: relative; border-bottom: 1px solid rgba(0,0,0,0.06); }
        .hero-countdown-media {
            height: 170px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .hero-countdown-media::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.24), rgba(0,0,0,0.40));
        }
        .hero-countdown-label {
            position: absolute;
            left: 16px;
            top: 14px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .7rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.88);
            color: #5a1634;
            font-weight: 800;
        }
        .hero-countdown-head {
            padding: 1rem 1.05rem .95rem;
            display: grid;
            gap: .45rem;
            padding-right: 6.75rem;
        }
        .hero-countdown-head-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .9rem;
        }
        .hero-countdown-header h3 { margin: 0; font-size: 1.25rem; color: #111; }
        .hero-countdown-subtitle { color: rgba(0,0,0,0.62); font-size: .9rem; }
        .hero-countdown-status {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .7rem;
            border-radius: 999px;
            background: rgba(255,42,122,0.10);
            border: 1px solid rgba(179,0,0,0.12);
            color: #5a1634;
            font-weight: 800;
            white-space: nowrap;
        }

        .countdown-carousel { position: relative; padding: .85rem 3.25rem 1.05rem; overflow: hidden; }
        .countdown-carousel-track {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            padding-bottom: .35rem;
            scrollbar-width: none;
        }
        .countdown-carousel-track::-webkit-scrollbar { display: none; }
        .countdown-card { min-width: 100%; max-width: 100%; flex: 0 0 100%; scroll-snap-align: center; padding: 0; }
        .countdown-slide {
            border-radius: 16px;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 12px 28px rgba(0,0,0,0.08);
            padding: 1rem 1.05rem;
        }
        .countdown-congregation-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .35rem .65rem;
            border-radius: 999px;
            background: rgba(179,0,0,0.08);
            color: var(--primary-red);
            font-weight: 800;
            text-transform: uppercase;
            font-size: .72rem;
        }
        .countdown-card-title { font-weight: 800; color: #111; margin: .55rem 0 .1rem; font-size: 1.2rem; }
        .countdown-card-meta { color: rgba(0,0,0,0.62); font-size: .92rem; margin-bottom: .6rem; }
        .countdown-card-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .65rem; }
        .countdown-box {
            text-align: center;
            padding: .8rem .4rem;
            border-radius: 14px;
            background: #f8f9fa;
            border: 1px solid rgba(0,0,0,0.08);
        }
        .countdown-box strong { display: block; font-size: 1.2rem; color: var(--primary-red); font-variant-numeric: tabular-nums; }
        .countdown-box span { display: block; font-size: .72rem; color: #777; text-transform: uppercase; letter-spacing: .06em; }

        .countdown-carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: #fff;
            color: var(--primary-red);
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
            z-index: 2;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }
        .countdown-carousel-btn:hover { transform: translateY(-50%) scale(1.04); box-shadow: 0 16px 30px rgba(0,0,0,0.18); }
        .countdown-carousel-btn:disabled { opacity: .45; box-shadow: none; }
        .countdown-carousel-btn.prev { left: .9rem; }
        .countdown-carousel-btn.next { right: .9rem; }

        .hero-countdown-cta { padding: 0 1.05rem 1.05rem; }
        .hero-countdown-cta .btn { width: 100%; border-radius: 999px; font-weight: 800; padding: 12px 18px; }
        .countdown-empty-note { padding: 1rem 1.5rem 1.5rem; color: #666; }
        .scrollable-panel {
            position: relative;
        }
        .scrollable-panel::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #ff2a7a 0%, var(--primary-red) 52%, var(--primary-gold) 100%);
            z-index: 3;
        }
        .scrollable-panel::after {
            content: 'DESLIZE';
            position: absolute;
            top: 14px;
            right: 16px;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .34rem .62rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(179,0,0,0.12);
            color: #7b1f44;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .08em;
            box-shadow: 0 8px 18px rgba(0,0,0,0.08);
            pointer-events: none;
        }
        .scrollable-panel.scrollable-panel-dark::after {
            background: rgba(255,255,255,0.88);
        }

        .home-live-section {
            background: #f6f3f5;
        }
        .live-board {
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 18px 40px rgba(0,0,0,0.08);
            padding: 0;
        }
        .panel-soft-head {
            padding: 1.15rem 1.35rem 1rem;
            background: linear-gradient(135deg, rgba(255,42,122,0.08), rgba(179,0,0,0.06));
            border-bottom: 1px solid rgba(0,0,0,0.06);
            position: relative;
        }
        .panel-soft-head.panel-scrollable {
            padding-right: 6.75rem;
        }
        .panel-soft-body {
            padding: 1.35rem;
        }
        .live-board-title {
            display: flex;
            align-items: center;
            gap: .65rem;
            font-weight: 800;
            color: #3c1d25;
            margin-bottom: 0;
        }
        .live-board-title i {
            color: #8b1538;
        }
        .live-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .live-card {
            border-radius: 16px;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.06);
            padding: 1rem;
        }
        .live-card-head {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-bottom: .5rem;
        }
        .live-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255,42,122,0.12), rgba(179,0,0,0.10));
            color: #d11f68;
            font-size: 1.1rem;
        }
        .live-card-title {
            font-weight: 800;
            color: #3c1d25;
            margin: 0;
        }
        .live-card p {
            margin: 0;
            color: #6b7280;
            line-height: 1.45;
        }
        .mini-avatars {
            display: flex;
            align-items: flex-start;
            gap: .9rem;
            margin-top: .75rem;
            flex-wrap: wrap;
        }
        .mini-avatar-item {
            min-width: 74px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .5rem;
            text-align: center;
        }
        .mini-avatar-name {
            display: block;
            color: #6b7280;
            font-size: .8rem;
            font-weight: 700;
            line-height: 1.1;
            width: 100%;
            white-space: nowrap;
        }
        .mini-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffd1e3, #f2b3cd);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .76rem;
            font-weight: 800;
            color: #8b1538;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.10);
        }
        .mini-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .live-card-link {
            margin-top: .95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            border: none;
            border-radius: 999px;
            padding: .72rem 1rem;
            background: rgba(179,0,0,0.08);
            color: var(--primary-red);
            font-weight: 800;
            transition: background .16s ease, transform .16s ease;
        }
        .live-card-link:hover {
            background: rgba(179,0,0,0.12);
            color: var(--primary-red);
            transform: translateY(-1px);
        }
        .birthdays-modal .modal-content {
            border: none;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(0,0,0,0.22);
        }
        .birthdays-modal-header {
            padding: 1.25rem 1.35rem 1rem;
            background: linear-gradient(135deg, rgba(255,42,122,0.12), rgba(212,175,55,0.14));
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .birthdays-modal-title {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
            color: #3c1d25;
        }
        .birthdays-modal-subtitle {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .94rem;
        }
        .birthdays-modal-body {
            padding: 1.35rem 1.35rem calc(1.35rem + 1.1rem);
            background: #fffdfd;
        }
        .birthdays-modal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .9rem;
        }
        .birthday-person-card {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .9rem 1rem;
            border-radius: 18px;
            background: linear-gradient(180deg, #fff, #fff7fa);
            border: 1px solid rgba(139,21,56,0.08);
        }
        .birthday-person-avatar {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            flex: 0 0 52px;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255,42,122,0.14), rgba(212,175,55,0.2));
            color: #8b1538;
            font-weight: 800;
            box-shadow: 0 10px 22px rgba(60,29,37,0.08);
        }
        .birthday-person-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .birthday-person-name {
            display: block;
            color: #3c1d25;
            font-weight: 800;
            line-height: 1.2;
        }
        .photo-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .55rem;
            margin-top: .85rem;
        }
        .photo-strip img {
            width: 100%;
            height: 78px;
            object-fit: cover;
            border-radius: 12px;
        }
        .upcoming-panel {
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 18px 40px rgba(0,0,0,0.08);
            padding: 0;
            height: 100%;
        }
        .cultos-card-head,
        .section-panel-head {
            padding-right: 6.75rem;
        }
        .upcoming-list {
            display: grid;
            gap: .9rem;
        }
        .upcoming-item {
            display: grid;
            grid-template-columns: 54px 1fr;
            gap: .8rem;
            align-items: start;
            padding-bottom: .9rem;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .upcoming-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .upcoming-thumb {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(255,42,122,0.12), rgba(179,0,0,0.10));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #d11f68;
        }
        .upcoming-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .upcoming-item-title {
            font-weight: 800;
            color: #2d1a21;
            margin-bottom: .18rem;
        }
        .upcoming-item-subtitle {
            color: #6b7280;
            font-size: .92rem;
            line-height: 1.4;
        }
        .upcoming-panel .btn {
            border-radius: 999px;
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
        .cultos-carousel-shell {
            position: relative;
            max-width: 1080px;
            margin: 0 auto;
        }
        .cultos-carousel {
            position: relative;
            padding: .5rem 4.5rem;
        }
        .cultos-carousel-track {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
        }
        .cultos-carousel-track::-webkit-scrollbar {
            display: none;
        }
        .cultos-slide {
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            padding: .35rem;
        }
        .cultos-card {
            background: #fff;
            border-radius: 22px;
            border: 1px solid rgba(0,0,0,0.07);
            box-shadow: 0 16px 42px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .cultos-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem 1.35rem;
            background: linear-gradient(135deg, rgba(255,42,122,0.08), rgba(179,0,0,0.06));
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .cultos-card-kicker {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .42rem .72rem;
            border-radius: 999px;
            background: rgba(179,0,0,0.09);
            color: var(--primary-red);
            font-size: .78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .cultos-card-head h3 {
            margin: .7rem 0 .2rem;
            font-size: 1.55rem;
            font-weight: 800;
            color: #2d1a21;
        }
        .cultos-card-head p {
            margin: 0;
            color: #6b7280;
        }
        .cultos-card-body {
            padding: 1.25rem 1.35rem 1.35rem;
        }
        .cultos-card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .culto-item {
            height: 100%;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 18px;
            padding: 1rem;
            background: #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        }
        .culto-item-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255,42,122,0.14), rgba(179,0,0,0.10));
            color: var(--primary-red);
            margin-bottom: .9rem;
        }
        .culto-item h4 {
            font-size: 1.12rem;
            font-weight: 800;
            color: #2d1a21;
            margin-bottom: .7rem;
        }
        .culto-item-schedule {
            display: grid;
            gap: .45rem;
            color: #6b7280;
            margin-bottom: .8rem;
        }
        .culto-item-schedule span,
        .culto-item-location {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
        }
        .culto-item-schedule i,
        .culto-item-location i {
            color: var(--primary-red);
            margin-top: .12rem;
        }
        .culto-item-days {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-bottom: .8rem;
        }
        .culto-item-days .badge {
            background: rgba(212,175,55,0.18) !important;
            color: #5b4300;
            font-weight: 700;
            border-radius: 999px;
            padding: .42rem .7rem;
        }
        .culto-item-description {
            color: #6b7280;
            margin: 0;
            line-height: 1.55;
        }
        .cultos-carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: #fff;
            color: var(--primary-red);
            box-shadow: 0 14px 26px rgba(0,0,0,0.14);
            z-index: 2;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }
        .cultos-carousel-btn:hover {
            transform: translateY(-50%) scale(1.04);
            box-shadow: 0 18px 30px rgba(0,0,0,0.18);
        }
        .cultos-carousel-btn:disabled {
            opacity: .4;
            box-shadow: none;
        }
        .cultos-carousel-btn.prev {
            left: .2rem;
        }
        .cultos-carousel-btn.next {
            right: .2rem;
        }
        .section-carousel-shell {
            position: relative;
            max-width: 1080px;
            margin: 0 auto;
        }
        .section-carousel {
            position: relative;
            padding: .5rem 4.5rem;
        }
        .section-carousel-track {
            display: flex;
            gap: 0;
            align-items: flex-start;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
        }
        .section-carousel-track::-webkit-scrollbar {
            display: none;
        }
        .section-carousel-slide {
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            padding: .35rem;
        }
        .section-carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: #fff;
            color: var(--primary-red);
            box-shadow: 0 14px 26px rgba(0,0,0,0.14);
            z-index: 2;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }
        .section-carousel-btn:hover {
            transform: translateY(-50%) scale(1.04);
            box-shadow: 0 18px 30px rgba(0,0,0,0.18);
        }
        .section-carousel-btn:disabled {
            opacity: .4;
            box-shadow: none;
        }
        .section-carousel-btn.prev {
            left: .2rem;
        }
        .section-carousel-btn.next {
            right: .2rem;
        }
        .section-panel-card {
            background: #fff;
            border-radius: 22px;
            border: 1px solid rgba(0,0,0,0.07);
            box-shadow: 0 16px 42px rgba(0,0,0,0.08);
            overflow: hidden;
            height: 100%;
        }
        .section-panel-head {
            padding: 1.15rem 1.35rem;
            background: linear-gradient(135deg, rgba(255,42,122,0.08), rgba(179,0,0,0.06));
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .section-panel-kicker {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .42rem .72rem;
            border-radius: 999px;
            background: rgba(179,0,0,0.09);
            color: var(--primary-red);
            font-size: .78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .section-panel-head h3 {
            margin: .7rem 0 .2rem;
            font-size: 1.45rem;
            font-weight: 800;
            color: #2d1a21;
        }
        .section-panel-head p {
            margin: 0;
            color: #6b7280;
        }
        .section-panel-body {
            padding: 1.25rem 1.35rem 1.35rem;
        }
        .section-panel-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .section-panel-grid.single-column {
            grid-template-columns: 1fr;
        }
        .section-panel-card .event-card,
        .section-panel-card .card {
            height: 100%;
        }
        .section-panel-card .btn {
            border-radius: 999px;
        }
        .section-panel-card .photo-strip {
            margin-bottom: 0;
        }
        .section-carousel-inline {
            padding: .25rem 3.5rem .35rem;
        }
        .section-carousel-inline .section-carousel-slide {
            padding: 0;
        }
        .section-carousel-inline .live-card {
            height: auto;
        }
        .live-card-photos {
            margin-top: 1rem;
        }
        .congregacao-card {
            background: #fff;
            border-radius: 22px;
            border: 1px solid rgba(0,0,0,0.07);
            box-shadow: 0 16px 42px rgba(0,0,0,0.08);
            overflow: hidden;
            height: 100%;
        }
        .congregacao-card-media {
            height: 220px;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .congregacao-card-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .congregacao-card-body {
            padding: 1.25rem 1.35rem 1.35rem;
        }
        .congregacao-card-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: #2d1a21;
            margin-bottom: .45rem;
        }
        .congregacao-card-meta {
            color: #6b7280;
            margin-bottom: 1rem;
        }
        .congregacao-card-info {
            display: grid;
            gap: .75rem;
        }
        .congregacao-card-info p,
        .congregacao-card-info li {
            margin: 0;
            color: #4b5563;
        }
        .congregacao-card-info i {
            color: var(--primary-red);
        }
        .congregacao-schedules {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0,0,0,0.08);
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
        .floating-faith-actions {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1085;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .6rem;
            padding: .6rem .85rem calc(.6rem + env(safe-area-inset-bottom, 0px));
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(18px);
            box-shadow: 0 -16px 32px rgba(0,0,0,0.12);
            border-top: 1px solid rgba(15,18,28,0.08);
            transition: transform .22s ease, opacity .18s ease, visibility .18s ease;
        }
        .floating-faith-actions.is-suppressed {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(110%);
        }
        .floating-faith-hover-zone {
            display: none;
        }
        .floating-faith-item {
            display: flex;
            min-width: 0;
        }
        .floating-faith-card {
            width: 100%;
            min-width: 0;
            border: none;
            border-radius: 20px;
            color: #fff;
            box-shadow: 0 12px 24px rgba(0,0,0,0.14);
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: box-shadow .18s ease, transform .18s ease;
        }
        .floating-faith-card.is-expanded {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(0,0,0,0.22);
        }
        .floating-faith-card.is-collapsed {
            width: 100%;
        }
        .floating-faith-card.is-expanded {
            width: 100%;
        }
        .floating-faith-toggle {
            width: 100%;
            border: none;
            background: transparent;
            min-height: 52px;
            padding: .6rem .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .65rem;
            color: inherit;
            text-align: center;
            cursor: pointer;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        .floating-faith-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(0,0,0,0.2);
        }
        .floating-faith-toggle:focus-visible,
        .floating-faith-action:focus-visible {
            outline: 2px solid rgba(255,255,255,0.72);
            outline-offset: 2px;
        }
        .floating-faith-icon {
            width: 40px;
            height: 40px;
            border-radius: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 40px;
            background: rgba(255,255,255,0.18);
            font-size: 1rem;
        }
        .floating-faith-content {
            min-width: 0;
            flex: 0 1 auto;
            opacity: 1;
            transition: opacity .16s ease;
            text-align: center;
        }
        .floating-faith-card.is-collapsed .floating-faith-toggle {
            justify-content: center;
        }
        .floating-faith-card.is-collapsed .floating-faith-icon {
            margin: 0;
        }
        .floating-faith-card.is-collapsed .floating-faith-content span {
            display: none;
        }
        .floating-faith-card.is-expanded .floating-faith-toggle {
            padding-bottom: .55rem;
        }
        .floating-faith-content strong {
            display: block;
            font-size: .9rem;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .floating-faith-content span {
            display: block;
            font-size: .8rem;
            opacity: .9;
            line-height: 1.25;
        }
        .floating-faith-action-wrap {
            padding: 0 .9rem .9rem;
            max-height: 88px;
            opacity: 1;
            overflow: hidden;
            transition: max-height .18s ease, opacity .18s ease, padding .18s ease;
        }
        .floating-faith-card.is-collapsed .floating-faith-action-wrap {
            max-height: 0;
            opacity: 0;
            padding-bottom: 0;
            pointer-events: none;
        }
        .floating-faith-action {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: .72rem .9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            text-decoration: none;
            color: #fff;
            background: rgba(255,255,255,0.16);
            font-weight: 700;
            transition: background .16s ease;
        }
        .floating-faith-action:hover {
            color: #fff;
            background: rgba(255,255,255,0.24);
        }
        .floating-faith-card-prayer {
            background: linear-gradient(135deg, #8b1538 0%, #c62662 100%);
        }
        .floating-faith-card-devotional {
            background: linear-gradient(135deg, #3c1d25 0%, #6f3a54 100%);
        }
        .devotional-modal .modal-content {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 28px 60px rgba(0,0,0,0.2);
        }
        .devotional-modal-header {
            padding: 1.2rem 1.35rem 1rem;
            background: linear-gradient(135deg, rgba(255,42,122,0.1), rgba(179,0,0,0.08));
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .devotional-modal-title {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
            color: #3c1d25;
        }
        .devotional-modal-subtitle {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .93rem;
        }
        .devotional-modal-body {
            padding: 1.35rem;
            background: #fffdfd;
        }
        .devotional-verse-card {
            position: relative;
            border-radius: 20px;
            background: linear-gradient(180deg, #fff, #fff5f8);
            border: 1px solid rgba(179,0,0,0.08);
            padding: 1.2rem;
        }
        .devotional-verse-share {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #8b1538;
            background: rgba(255,255,255,0.88);
            box-shadow: 0 10px 20px rgba(60,29,37,0.08);
            transition: background .16s ease, transform .16s ease, box-shadow .16s ease;
        }
        .devotional-verse-share:hover {
            color: #8b1538;
            background: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(60,29,37,0.12);
        }
        .devotional-verse-share:focus-visible {
            outline: 2px solid rgba(139,21,56,0.32);
            outline-offset: 2px;
        }
        .devotional-verse-label {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .38rem .72rem;
            border-radius: 999px;
            background: rgba(255,42,122,0.1);
            color: #8b1538;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .devotional-verse-meta {
            margin-top: .8rem;
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
        }
        .devotional-verse-chip {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            padding: .34rem .65rem;
            border-radius: 999px;
            background: rgba(60,29,37,0.06);
            color: #5b3341;
            font-size: .76rem;
            font-weight: 700;
        }
        .devotional-verse-text {
            margin: 1rem 0 .8rem;
            font-size: 1.22rem;
            line-height: 1.75;
            color: #2d1a21;
            font-weight: 600;
        }
        .devotional-verse-reference {
            color: #8b1538;
            font-weight: 800;
            margin-bottom: 0;
        }
        .devotional-modal-actions {
            margin-top: 1rem;
            display: grid;
            gap: .95rem;
        }
        .devotional-modal-note {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .85rem .95rem;
            border-radius: 16px;
            background: linear-gradient(180deg, #fffefe, #fff8fa);
            border: 1px solid rgba(139,21,56,0.06);
        }
        .devotional-modal-note-icon {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 30px;
            color: #8b1538;
            background: rgba(255,42,122,0.08);
            border: 1px solid rgba(139,21,56,0.08);
            font-size: .82rem;
        }
        .devotional-modal-note strong {
            display: block;
            color: #3c1d25;
            font-size: .9rem;
            line-height: 1.2;
        }
        .devotional-modal-note span {
            display: block;
            margin-top: .18rem;
            color: #6b7280;
            font-size: .84rem;
            line-height: 1.45;
        }
        .devotional-modal-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: .75rem;
        }
        .devotional-modal-button {
            min-height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            padding: .8rem 1rem;
        }
        .devotional-share-feedback {
            min-height: 1.2rem;
            margin: -.15rem .2rem 0;
            color: #8b1538;
            font-size: .82rem;
            font-weight: 600;
            text-align: center;
        }
        .devotional-modal .btn {
            border-radius: 999px;
            font-weight: 700;
        }
        @media (hover: hover) and (pointer: fine) and (min-width: 992px) {
            .floating-faith-hover-zone {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                height: 22px;
                display: block;
                z-index: 1084;
            }
            .floating-faith-actions {
                transform: translateY(calc(100% + 8px));
                opacity: 0;
                pointer-events: none;
                transition: transform .22s ease, opacity .18s ease, box-shadow .18s ease;
            }
            .floating-faith-hover-zone:hover + .floating-faith-actions,
            .floating-faith-actions:hover,
            .floating-faith-actions:focus-within,
            .floating-faith-actions.is-visible {
                transform: translateY(0);
                opacity: 1;
                pointer-events: auto;
            }
        }
        @media (max-width: 991.98px) {
            .hero-section {
                height: auto;
                min-height: auto;
                padding: 112px 0 64px;
            }
            .hero-title {
                font-size: clamp(2.4rem, 6vw, 3.5rem);
            }
            .hero-section .lead {
                font-size: 1.08rem !important;
                max-width: 36rem;
            }
            .hero-actions {
                gap: .65rem;
            }
            .hero-actions .btn {
                padding: 11px 20px;
            }
            .hero-countdown-panel {
                margin: 0 auto;
                max-width: 560px;
            }
            .countdown-carousel {
                padding: 1rem 3rem 1.15rem;
            }
            .countdown-slide {
                padding: .95rem;
            }
            .live-grid {
                grid-template-columns: 1fr;
            }
            .live-board,
            .upcoming-panel {
                padding: 1.2rem;
            }
        }
        @media (max-width: 767.98px) {
            .hero-section {
                padding: 98px 0 42px;
                text-align: center;
                min-height: auto;
            }
            .hero-section .row {
                gap: 1.5rem;
            }
            .hero-title {
                font-size: clamp(2.15rem, 9vw, 3rem);
                margin-bottom: .9rem !important;
            }
            .hero-section .lead {
                font-size: 1rem !important;
                margin: 0 auto 1.35rem !important;
                max-width: 30rem;
            }
            .hero-actions {
                justify-content: center;
            }
            .hero-actions .btn {
                width: 100%;
                justify-content: center;
            }
            .hero-section .col-lg-7,
            .hero-section .col-lg-5 {
                width: 100%;
            }
            .hero-countdown-panel {
                max-width: 100%;
                border-radius: 16px;
            }
            .scrollable-panel::after {
                top: 12px;
                right: 12px;
            }
            .hero-countdown-media {
                height: 150px;
            }
            .hero-countdown-head {
                padding: .9rem .95rem .85rem;
                padding-right: 6rem;
            }
            .hero-countdown-header h3 {
                font-size: 1.08rem;
            }
            .hero-countdown-subtitle {
                font-size: .84rem;
            }
            .countdown-carousel {
                padding: .9rem .9rem 1rem;
            }
            .countdown-carousel-btn {
                display: none;
            }
            .countdown-slide {
                padding: .9rem;
                border-radius: 14px;
            }
            .countdown-card-title {
                font-size: 1.05rem;
                line-height: 1.2;
            }
            .countdown-card-meta {
                font-size: .86rem;
                line-height: 1.45;
            }
            .countdown-card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .55rem;
            }
            .countdown-box {
                padding: .72rem .35rem;
            }
            .countdown-box strong {
                font-size: 1.08rem;
            }
            .hero-countdown-cta {
                padding: 0 .9rem .95rem;
            }
            .home-live-section .row {
                gap: 1rem;
            }
            .home-live-section {
                padding-top: 2rem !important;
                padding-bottom: 2rem !important;
            }
            .live-board,
            .upcoming-panel {
                border-radius: 16px;
            }
            .panel-soft-head {
                padding: 1rem 1rem .9rem;
            }
            .panel-soft-head.panel-scrollable {
                padding-right: 6rem;
            }
            .panel-soft-body {
                padding: 1rem;
            }
            .live-grid {
                gap: .8rem;
            }
            .live-board-title {
                margin-bottom: 1rem;
                font-size: 1.2rem;
            }
            .live-card {
                padding: .95rem;
                border-radius: 14px;
            }
            .mini-avatars {
                gap: .75rem;
                justify-content: center;
            }
            .mini-avatar-item {
                min-width: 68px;
            }
            .upcoming-item {
                grid-template-columns: 48px 1fr;
                gap: .7rem;
                padding-bottom: .8rem;
            }
            .upcoming-thumb {
                width: 48px;
                height: 48px;
                border-radius: 10px;
            }
            .upcoming-item-title {
                font-size: .97rem;
            }
            .upcoming-item-subtitle {
                font-size: .87rem;
            }
            .upcoming-panel .btn {
                width: 100%;
            }
            .cultos-carousel {
                padding: .35rem 3.5rem;
            }
            .cultos-card-head {
                padding: 1rem 1.1rem;
                padding-right: 6rem;
            }
            .cultos-card-head h3 {
                font-size: 1.28rem;
            }
            .cultos-card-body {
                padding: 1rem 1.1rem 1.1rem;
            }
            .cultos-card-grid {
                grid-template-columns: 1fr;
                gap: .85rem;
            }
            .section-carousel {
                padding: .35rem 3.5rem;
            }
            .section-panel-head {
                padding: 1rem 1.1rem;
                padding-right: 6rem;
            }
            .section-panel-head h3 {
                font-size: 1.22rem;
            }
            .section-panel-body {
                padding: 1rem 1.1rem 1.1rem;
            }
            .section-panel-grid {
                grid-template-columns: 1fr;
                gap: .85rem;
            }
            .congregacao-card-body {
                padding: 1rem 1.1rem 1.1rem;
            }
            .section-carousel-inline {
                padding: .25rem 3rem .35rem;
            }
        }
        @media (max-width: 575.98px) {
            .hero-section {
                padding: 92px 0 34px;
            }
            .hero-title {
                font-size: clamp(1.95rem, 10vw, 2.45rem);
                line-height: 1.08;
            }
            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .hero-actions .btn {
                width: 100%;
                padding: 12px 16px;
                font-size: .95rem;
            }
            .hero-countdown-panel {
                border-radius: 14px;
                box-shadow: 0 16px 34px rgba(0,0,0,0.22);
            }
            .scrollable-panel::after {
                font-size: .62rem;
                padding: .3rem .55rem;
                top: 10px;
                right: 10px;
            }
            .hero-countdown-media {
                height: 128px;
            }
            .hero-countdown-label {
                left: 12px;
                top: 12px;
                font-size: .78rem;
                padding: .38rem .62rem;
            }
            .hero-countdown-head {
                gap: .7rem;
            }
            .hero-countdown-head-top {
                width: 100%;
            }
            .hero-countdown-status {
                padding: .38rem .58rem;
                font-size: .84rem;
            }
            .countdown-carousel {
                padding: .85rem .75rem .95rem;
            }
            .countdown-slide {
                padding: .85rem;
                box-shadow: none;
            }
            .countdown-congregation-pill {
                font-size: .68rem;
            }
            .countdown-card-title {
                font-size: 1rem;
            }
            .countdown-card-meta {
                font-size: .82rem;
            }
            .countdown-card-grid {
                gap: .5rem;
            }
            .countdown-box span {
                font-size: .66rem;
            }
            .photo-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .photo-strip img {
                height: 84px;
            }
            .live-board,
            .upcoming-panel {
            }
            .panel-soft-head {
                padding: .95rem .95rem .85rem;
            }
            .panel-soft-head.panel-scrollable {
                padding-right: 5.6rem;
            }
            .panel-soft-body {
                padding: .95rem;
            }
            .live-card-head {
                gap: .6rem;
            }
            .live-card-icon {
                width: 38px;
                height: 38px;
                border-radius: 10px;
            }
            .live-card-title {
                font-size: 1rem;
            }
            .mini-avatars {
                justify-content: flex-start;
                gap: .7rem;
            }
            .mini-avatar-item {
                min-width: 58px;
                flex: 0 0 calc(33.333% - .5rem);
            }
            .mini-avatar-name {
                font-size: .75rem;
                white-space: normal;
                line-height: 1.15;
            }
            .upcoming-item {
                grid-template-columns: 42px 1fr;
                gap: .65rem;
            }
            .upcoming-thumb {
                width: 42px;
                height: 42px;
            }
            .cultos-carousel {
                padding: .25rem .25rem .1rem;
            }
            .cultos-carousel-btn {
                display: none;
            }
            .cultos-slide {
                padding: 0;
            }
            .cultos-card {
                border-radius: 16px;
            }
            .cultos-card-head {
                padding: .95rem;
                flex-direction: column;
                align-items: flex-start;
                padding-right: 5.6rem;
            }
            .cultos-card-head h3 {
                font-size: 1.15rem;
                margin-top: .55rem;
            }
            .cultos-card-body {
                padding: .95rem;
            }
            .culto-item {
                border-radius: 14px;
                padding: .9rem;
            }
            .culto-item h4 {
                font-size: 1rem;
            }
            .culto-item-description {
                font-size: .92rem;
            }
            .section-carousel {
                padding: .25rem .25rem .1rem;
            }
            .section-carousel-btn {
                display: none;
            }
            .section-carousel-slide {
                padding: 0;
            }
            .section-panel-card,
            .congregacao-card {
                border-radius: 16px;
            }
            .section-panel-head {
                padding: .95rem;
                padding-right: 5.6rem;
            }
            .section-panel-head h3 {
                font-size: 1.12rem;
            }
            .section-panel-body,
            .congregacao-card-body {
                padding: .95rem;
            }
            .congregacao-card-media {
                height: 190px;
            }
            .congregacao-card-title {
                font-size: 1.15rem;
            }
            .section-carousel-inline {
                padding: .15rem .15rem 0;
            }
            .floating-faith-actions {
                gap: .55rem;
                padding: .55rem .65rem calc(.55rem + env(safe-area-inset-bottom, 0px));
            }
            .floating-faith-card {
                border-radius: 16px;
            }
            .floating-faith-toggle {
                min-height: 50px;
                padding: .58rem .68rem;
                gap: .7rem;
            }
            .floating-faith-card.is-collapsed .floating-faith-toggle {
                padding: .58rem .68rem;
                gap: .6rem;
            }
            .floating-faith-icon {
                width: 38px;
                height: 38px;
                border-radius: 12px;
                flex-basis: 38px;
            }
            .floating-faith-content strong {
                font-size: .84rem;
            }
            .floating-faith-content span {
                font-size: .72rem;
            }
            .floating-faith-action-wrap {
                padding: 0 .8rem .8rem;
            }
            .floating-faith-action {
                padding: .68rem .7rem;
                font-size: .8rem;
            }
            .birthdays-modal-grid {
                grid-template-columns: 1fr;
            }
            .birthday-person-card {
                padding: .85rem .9rem;
            }
            .devotional-modal-header,
            .devotional-modal-body {
                padding: 1rem;
            }
            .devotional-verse-text {
                font-size: 1.02rem;
                line-height: 1.65;
            }
            .devotional-verse-share {
                top: .9rem;
                right: .9rem;
                width: 38px;
                height: 38px;
            }
            .devotional-modal-note {
                padding: .85rem .9rem;
            }
            .devotional-modal-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <!-- Placeholder para a logo -->
                <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?>" onerror="this.style.display='none'">
                <span class="d-none d-md-inline"><?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?></span>
            </a>
            <button class="navbar-toggler bg-gold" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="#">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="#sobre">Sobre</a></li>
                    <li class="nav-item"><a class="nav-link" href="#cultos">Cultos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#eventos">Eventos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#convites">Convites</a></li>
                    <li class="nav-item"><a class="nav-link" href="#congregacoes">Congregações</a></li>
                    <li class="nav-item"><a class="nav-link" href="/galeria">Galeria</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contato">Contato</a></li>
                    <li class="nav-item">
                        <a class="nav-link text-nowrap" href="/portal/login"><i class="fas fa-user-circle me-1"></i> Área do Membro</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-gold ms-lg-3 px-4 rounded-pill text-nowrap" href="/admin/login">Área Administrativa</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section" id="inicio">
        <?php $heroCardImg = !empty($banners[0]['image_path']) ? '/' . ltrim((string)$banners[0]['image_path'], '/') : $bgUrl; ?>
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <h1 class="display-3 hero-title mb-3">Bem-vindo à<br><span class="hero-highlight"><?= htmlspecialchars($siteProfile['name'] ?? 'Nossa Igreja') ?></span></h1>
                    <p class="lead mb-4 fs-4">Uma igreja comprometida com a Palavra de Deus e o amor ao próximo.</p>
                    <div class="hero-actions">
                        <a href="/portal/login" class="btn btn-hero-secondary"><i class="fas fa-user me-2"></i> Área do Membro</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <?php if (!empty($countdownCultos)): ?>
                        <div class="hero-countdown-panel scrollable-panel scrollable-panel-dark">
                            <div class="hero-countdown-header">
                                <div class="hero-countdown-media" style="background-image:url('<?= htmlspecialchars($heroCardImg) ?>');">
                                    <div class="hero-countdown-label"><i class="fas fa-hourglass-half"></i> Próximo culto</div>
                                </div>
                                <div class="hero-countdown-head">
                                    <div class="hero-countdown-head-top">
                                        <h3>Contagem regressiva</h3>
                                        <div class="hero-countdown-status">
                                            <span id="heroCountdownIndex">1</span>
                                            <span>/</span>
                                            <span id="heroCountdownTotal"><?= (int)count($countdownCultos) ?></span>
                                        </div>
                                    </div>
                                    <div class="hero-countdown-subtitle">Role para o lado para ver mais informações.</div>
                                </div>
                            </div>
                            <div class="countdown-carousel">
                                <button type="button" class="countdown-carousel-btn prev" data-countdown-scroll="prev" aria-label="Anterior">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="countdown-carousel-track" id="heroCountdownTrack">
                                    <?php foreach ($countdownCultos as $countdownCulto): ?>
                                        <div class="countdown-card" data-countdown="<?= htmlspecialchars($countdownCulto['starts_at']) ?>">
                                            <div class="countdown-slide">
                                                <span class="countdown-congregation-pill"><i class="fas fa-church"></i><?= htmlspecialchars($countdownCulto['congregation_name']) ?></span>
                                                <div class="countdown-card-title"><?= htmlspecialchars($countdownCulto['title']) ?></div>
                                                <div class="countdown-card-meta">
                                                    <i class="fas fa-calendar-day me-1 text-danger"></i><?= htmlspecialchars($countdownCulto['weekday_label']) ?>
                                                    <span class="mx-1">•</span>
                                                    <?= htmlspecialchars($countdownCulto['date_label']) ?>
                                                    <span class="mx-1">•</span>
                                                    <i class="far fa-clock me-1 text-danger"></i><?= htmlspecialchars($countdownCulto['start_label']) ?>
                                                </div>
                                                <?php if (!empty($countdownCulto['location'])): ?>
                                                    <div class="small text-muted mb-3"><i class="fas fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($countdownCulto['location']) ?></div>
                                                <?php endif; ?>
                                                <div class="countdown-card-grid">
                                                    <div class="countdown-box">
                                                        <strong data-unit="days">0</strong>
                                                        <span>Dias</span>
                                                    </div>
                                                    <div class="countdown-box">
                                                        <strong data-unit="hours">00</strong>
                                                        <span>Horas</span>
                                                    </div>
                                                    <div class="countdown-box">
                                                        <strong data-unit="minutes">00</strong>
                                                        <span>Min</span>
                                                    </div>
                                                    <div class="countdown-box">
                                                        <strong data-unit="seconds">00</strong>
                                                        <span>Seg</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="countdown-carousel-btn next" data-countdown-scroll="next" aria-label="Próximo">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            <?php /* CTA oculto temporariamente
                            <div class="hero-countdown-cta">
                                <a href="/portal/login" class="btn btn-primary rounded-pill">Ver escalas da semana</a>
                            </div>
                            */ ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Banner Modal -->
    <?php if (!empty($banners)): ?>
    <div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="bannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: transparent; border: none;">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2 z-3" data-bs-dismiss="modal" aria-label="Close" style="background-color: rgba(0,0,0,0.5); border-radius: 50%; padding: 0.8rem;"></button>
                    
                    <?php if (count($banners) === 1): ?>
                        <?php $banner = $banners[0]; ?>
                        <?php if (!empty($banner['link'])): ?>
                            <a href="<?= htmlspecialchars($banner['link']) ?>" target="_blank">
                        <?php endif; ?>
                            <img src="/<?= $banner['image_path'] ?>" class="img-fluid rounded shadow-lg w-100" alt="<?= htmlspecialchars($banner['title']) ?>">
                        <?php if (!empty($banner['link'])): ?>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div id="bannerCarouselModal" class="carousel slide rounded shadow-lg overflow-hidden" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <?php foreach ($banners as $key => $banner): ?>
                                    <button type="button" data-bs-target="#bannerCarouselModal" data-bs-slide-to="<?= $key ?>" class="<?= $key === 0 ? 'active' : '' ?>" aria-current="<?= $key === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $key + 1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="carousel-inner">
                                <?php foreach ($banners as $key => $banner): ?>
                                    <div class="carousel-item <?= $key === 0 ? 'active' : '' ?>">
                                        <?php if (!empty($banner['link'])): ?>
                                            <a href="<?= htmlspecialchars($banner['link']) ?>" target="_blank">
                                        <?php endif; ?>
                                            <img src="/<?= $banner['image_path'] ?>" class="d-block w-100" alt="<?= htmlspecialchars($banner['title']) ?>">
                                        <?php if (!empty($banner['link'])): ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarouselModal" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarouselModal" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Próximo</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var bannerModal = new bootstrap.Modal(document.getElementById('bannerModal'));
            bannerModal.show();
        });
    </script>
    <?php endif; ?>

    <?php $siteProfile = getChurchSiteProfileSettings(); ?>
    <!-- About Section -->
    <section id="sobre" class="py-5 home-live-section">
        <div class="container py-5">
            <?php
                $highlights = $homeHighlights ?? [];
                $birthdays = $highlights['birthdays'] ?? [];
                $newMembers = $highlights['new_members'] ?? [];
                $baptisms = $highlights['baptisms'] ?? [];
                $latestAlbum = $highlights['latest_album'] ?? null;
                $latestAlbumPhotos = $latestAlbum['photos'] ?? [];
                $upcomingItems = $highlights['upcoming_items'] ?? [];
            ?>
            <div class="row g-4 align-items-start">
                <div class="col-lg-7">
                    <div class="live-board scrollable-panel">
                        <div class="panel-soft-head panel-scrollable">
                            <h2 class="live-board-title"><i class="fas fa-newspaper"></i> Atualizações Recentes</h2>
                            <p class="text-muted small mb-0 mt-2">Role para o lado para ver mais informações.</p>
                        </div>
                        <div class="panel-soft-body">
                        <div class="section-carousel js-section-carousel section-carousel-inline">
                            <button type="button" class="section-carousel-btn prev" aria-label="Quadro anterior das atualizações">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="section-carousel-track">
                                <div class="section-carousel-slide">
                                    <div class="live-card">
                                        <div class="live-card-head">
                                            <div class="live-card-icon"><i class="fas fa-cake-candles"></i></div>
                                            <h3 class="live-card-title h5">Aniversariantes</h3>
                                        </div>
                                        <p><?= !empty($birthdays) ? 'Mostrando os primeiros aniversariantes do mês.' : 'Nenhum aniversariante cadastrado para este mês.' ?></p>
                                        <?php if (!empty($birthdays)): ?>
                                            <div class="mini-avatars">
                                                <?php foreach (array_slice($birthdays, 0, 3) as $birthday): ?>
                                                    <div class="mini-avatar-item">
                                                        <div class="mini-avatar" title="<?= htmlspecialchars($birthday['name']) ?>">
                                                            <?php if (!empty($birthday['photo'])): ?>
                                                                <img src="/uploads/members/<?= htmlspecialchars($birthday['photo']) ?>" alt="<?= htmlspecialchars($birthday['name']) ?>">
                                                            <?php else: ?>
                                                                <?= htmlspecialchars(substr((string)$birthday['name'], 0, 1)) ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="mini-avatar-name"><?= htmlspecialchars(trim((string)($birthday['first_name'] ?? $birthday['name'] ?? ''))) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if (count($birthdays) > 3): ?>
                                                <button type="button" class="live-card-link" data-bs-toggle="modal" data-bs-target="#birthdaysModal">
                                                    <i class="fas fa-cake-candles"></i> Ver mais
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="section-carousel-slide">
                                    <div class="live-card">
                                        <div class="live-card-head">
                                            <div class="live-card-icon"><i class="fas fa-user-plus"></i></div>
                                            <h3 class="live-card-title h5">Novos Membros</h3>
                                        </div>
                                        <?php if (!empty($newMembers)): ?>
                                            <p>Todos os novos membros registrados no mês corrente.</p>
                                            <div class="mini-avatars">
                                                <?php foreach ($newMembers as $member): ?>
                                                    <div class="mini-avatar-item">
                                                        <div class="mini-avatar" title="<?= htmlspecialchars($member['name']) ?>">
                                                            <?php if (!empty($member['photo'])): ?>
                                                                <img src="/uploads/members/<?= htmlspecialchars($member['photo']) ?>" alt="<?= htmlspecialchars($member['name']) ?>">
                                                            <?php else: ?>
                                                                <?= htmlspecialchars(substr((string)$member['name'], 0, 1)) ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="mini-avatar-name"><?= htmlspecialchars(trim((string)($member['first_name'] ?? $member['name'] ?? ''))) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p>Nenhum novo membro registrado neste mês.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="section-carousel-slide">
                                    <div class="live-card">
                                        <div class="live-card-head">
                                            <div class="live-card-icon"><i class="fas fa-droplet"></i></div>
                                            <h3 class="live-card-title h5">Batismos</h3>
                                        </div>
                                        <?php if (!empty($baptisms)): ?>
                                            <p>Todos os batismos registrados no mês corrente.</p>
                                            <div class="mini-avatars">
                                                <?php foreach ($baptisms as $baptism): ?>
                                                    <div class="mini-avatar-item">
                                                        <div class="mini-avatar" title="<?= htmlspecialchars($baptism['name']) ?>">
                                                            <?php if (!empty($baptism['photo'])): ?>
                                                                <img src="/uploads/members/<?= htmlspecialchars($baptism['photo']) ?>" alt="<?= htmlspecialchars($baptism['name']) ?>">
                                                            <?php else: ?>
                                                                <?= htmlspecialchars(substr((string)$baptism['name'], 0, 1)) ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="mini-avatar-name"><?= htmlspecialchars(trim((string)($baptism['first_name'] ?? $baptism['name'] ?? ''))) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p>Sem batismos registrados neste mês.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="section-carousel-btn next" aria-label="Próximo quadro das atualizações">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        </div>
                    </div>
                    <div class="live-board mt-4">
                        <div class="panel-soft-head">
                            <h2 class="live-board-title"><i class="fas fa-images"></i> Últimas Fotos</h2>
                        </div>
                        <div class="panel-soft-body">
                        <div class="live-card live-card-photos mt-0">
                            <div class="live-card-head">
                                <div class="live-card-icon"><i class="fas fa-camera-retro"></i></div>
                                <h3 class="live-card-title h5">Mural de Fotos</h3>
                            </div>
                            <p><?= !empty($latestAlbum['title']) ? htmlspecialchars($latestAlbum['title']) : 'Publique fotos na galeria para destacar aqui.' ?></p>
                            <?php if (!empty($latestAlbumPhotos)): ?>
                                <div class="photo-strip">
                                    <?php foreach (array_slice($latestAlbumPhotos, 0, 3) as $photo): ?>
                                        <img src="/uploads/gallery/<?= htmlspecialchars($photo['filename']) ?>" alt="Foto da igreja">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="/galeria" class="btn btn-outline-secondary btn-sm rounded-pill mt-3 px-3">Ir para o mural de fotos</a>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="upcoming-panel h-100">
                        <div class="panel-soft-head">
                            <h2 class="live-board-title"><i class="fas fa-calendar-days"></i> Próximo Evento</h2>
                        </div>
                        <div class="panel-soft-body">
                        <div class="upcoming-list">
                            <?php if (!empty($upcomingItems)): ?>
                                <?php foreach ($upcomingItems as $item): ?>
                                    <div class="upcoming-item">
                                        <div class="upcoming-thumb">
                                            <?php if (!empty($item['icon'])): ?>
                                                <img src="<?= htmlspecialchars($item['icon']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                            <?php else: ?>
                                                <i class="fas fa-calendar-heart"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="upcoming-item-title"><?= htmlspecialchars($item['title']) ?></div>
                                            <div class="upcoming-item-subtitle"><?= htmlspecialchars($item['subtitle']) ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted">Nenhum evento em destaque no momento.</div>
                            <?php endif; ?>
                        </div>
                        <a href="#eventos" class="btn btn-outline-secondary mt-4 px-4">Ver tudo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services (Cultos) Section -->
    <section id="cultos" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title">Nossos Cultos</h2>
                <p class="text-muted">Participe das nossas celebrações semanais</p>
                <p class="text-muted small mb-0">Role para o lado para ver mais informações.</p>
            </div>

            <?php
            // Agrupar cultos por congregação
            $cultosPorCongregacao = [];
            foreach ($cultos as $c) {
                $loc = !empty($c['location']) ? $c['location'] : 'Geral';
                if (!isset($cultosPorCongregacao[$loc])) {
                    $cultosPorCongregacao[$loc] = [];
                }
                $cultosPorCongregacao[$loc][] = $c;
            }
            ksort($cultosPorCongregacao); // Ordenar por nome da congregação
            
            // Garantir que Sede apareça primeiro se existir
            if (isset($cultosPorCongregacao['Sede'])) {
                $sede = $cultosPorCongregacao['Sede'];
                unset($cultosPorCongregacao['Sede']);
                $cultosPorCongregacao = array_merge(['Sede' => $sede], $cultosPorCongregacao);
            }
            ?>

            <?php if (empty($cultosPorCongregacao)): ?>
                <div class="text-center">
                    <p class="text-muted">Nenhum culto cadastrado no momento.</p>
                </div>
            <?php else: ?>
                <div class="cultos-carousel-shell">
                    <div class="cultos-carousel">
                        <button type="button" class="cultos-carousel-btn prev" data-cultos-scroll="prev" aria-label="Congregação anterior">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="cultos-carousel-track" id="cultosCarouselTrack">
                            <?php foreach ($cultosPorCongregacao as $congregacao => $items): ?>
                                <div class="cultos-slide">
                                    <div class="cultos-card scrollable-panel">
                                        <div class="cultos-card-head">
                                            <div>
                                                <span class="cultos-card-kicker"><i class="fas fa-church"></i> Congregação</span>
                                                <h3><?= htmlspecialchars($congregacao) ?></h3>
                                                <p>Role para ver cultos de outras congregações.</p>
                                            </div>
                                        </div>
                                        <div class="cultos-card-body">
                                            <div class="cultos-card-grid">
                                                <?php foreach ($items as $culto): ?>
                                                    <?php
                                                        $is_valid_date = !empty($culto['event_date']) && strpos($culto['event_date'], '1970-01-01') === false;
                                                        $time_only = !empty($culto['event_date']) ? date('H:i', strtotime($culto['event_date'])) : '';
                                                        $end_time = !empty($culto['end_time']) ? $culto['end_time'] : '';
                                                        $days = !empty($culto['recurring_days']) ? json_decode($culto['recurring_days'], true) : [];
                                                        if (!is_array($days)) $days = [];
                                                    ?>
                                                    <article class="culto-item">
                                                        <div class="culto-item-icon">
                                                            <i class="fas fa-book-bible fa-lg"></i>
                                                        </div>
                                                        <h4><?= htmlspecialchars($culto['title']) ?></h4>
                                                        <?php if (!empty($days)): ?>
                                                            <div class="culto-item-days">
                                                                <?php foreach ($days as $day): ?>
                                                                    <span class="badge"><?= htmlspecialchars($day) ?></span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <div class="culto-item-schedule">
                                                                <span><i class="far fa-clock"></i> <?= $time_only ? $time_only : 'Horário a confirmar' ?><?= $end_time ? ' às ' . htmlspecialchars($end_time) : '' ?></span>
                                                            </div>
                                                        <?php elseif ($is_valid_date): ?>
                                                            <div class="culto-item-schedule">
                                                                <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($culto['event_date'])) ?></span>
                                                                <span><i class="far fa-clock"></i> <?= $time_only ?: 'Horário a confirmar' ?></span>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="culto-item-schedule">
                                                                <span><i class="far fa-clock"></i> Horário a confirmar</span>
                                                            </div>
                                                        <?php endif; ?>
                                                        <p class="culto-item-description"><?= htmlspecialchars($culto['description'] ?: 'Participe conosco e acompanhe a programação desta congregação.') ?></p>
                                                        <?php if (!empty($culto['location']) && $culto['location'] !== $congregacao): ?>
                                                            <div class="culto-item-location mt-3">
                                                                <i class="fas fa-location-dot"></i>
                                                                <span><?= htmlspecialchars($culto['location']) ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </article>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="cultos-carousel-btn next" data-cultos-scroll="next" aria-label="Próxima congregação">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Events Section -->
    <section id="eventos" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title">Próximos Eventos</h2>
                <p class="text-muted">Fique por dentro da nossa agenda</p>
                <p class="text-muted small mb-0">Role para o lado para ver mais informações.</p>
            </div>
            
            <?php
            // Agrupar eventos por congregação
            $eventosPorCongregacao = [];
            foreach ($eventos as $e) {
                $loc = !empty($e['location']) ? $e['location'] : 'Geral';
                if (!isset($eventosPorCongregacao[$loc])) {
                    $eventosPorCongregacao[$loc] = [];
                }
                $eventosPorCongregacao[$loc][] = $e;
            }
            ksort($eventosPorCongregacao); // Ordenar alfabeticamente
            
            // Mover Sede para o início
            if (isset($eventosPorCongregacao['Sede'])) {
                $sede = $eventosPorCongregacao['Sede'];
                unset($eventosPorCongregacao['Sede']);
                $eventosPorCongregacao = array_merge(['Sede' => $sede], $eventosPorCongregacao);
            }
            ?>

            <?php if (empty($eventosPorCongregacao)): ?>
                <div class="text-center">
                    <p class="text-muted">Nenhum evento próximo agendado.</p>
                </div>
            <?php else: ?>
                <div class="section-carousel-shell">
                    <div class="section-carousel js-section-carousel">
                        <button type="button" class="section-carousel-btn prev" aria-label="Congregação anterior">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="section-carousel-track">
                            <?php foreach ($eventosPorCongregacao as $congregacao => $items): ?>
                                <div class="section-carousel-slide">
                                    <div class="section-panel-card scrollable-panel">
                                        <div class="section-panel-head">
                                            <span class="section-panel-kicker"><i class="fas fa-calendar-days"></i> Eventos</span>
                                            <h3><?= htmlspecialchars($congregacao) ?></h3>
                                            <p>Role para ver eventos de outras congregações.</p>
                                        </div>
                                        <div class="section-panel-body">
                                            <div class="section-panel-grid">
                                                <?php foreach ($items as $evento): ?>
                                                    <div class="card event-card shadow-sm h-100 border-0 overflow-hidden">
                                                        <?php if (!empty($evento['banner_path'])): ?>
                                                            <div class="position-relative">
                                                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted overflow-hidden" style="height: 180px;">
                                                                    <img src="<?= $evento['banner_path'] ?>" class="w-100 h-100" style="object-fit: cover; filter: blur(2px); opacity: 0.8;" alt="Thumbnail">
                                                                    <div class="position-absolute top-50 start-50 translate-middle">
                                                                        <button type="button" class="btn btn-primary rounded-pill shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#bannerModal<?= $evento['id'] ?>">
                                                                            <i class="fas fa-image me-2"></i> Ver Banner
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="card-body p-4">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($evento['title']) ?></h5>
                                                                    <h6 class="card-subtitle mb-2 text-muted mt-2">
                                                                        <?php
                                                                            $is_valid_date = !empty($evento['event_date']) && strpos($evento['event_date'], '1970-01-01') === false;
                                                                            $recurring = !empty($evento['recurring_days']) ? json_decode($evento['recurring_days'], true) : [];
                                                                            $end_time = !empty($evento['end_time']) ? $evento['end_time'] : '';
                                                                            $event_time = !empty($evento['event_date']) ? date('H:i', strtotime($evento['event_date'])) : '';
                                                                            $time_display = $event_time . ($end_time ? ' às ' . $end_time : '');
                                                                        ?>
                                                                        <?php if ($is_valid_date): ?>
                                                                            <i class="far fa-calendar-alt me-1 text-gold"></i> <?= date('d/m/Y', strtotime($evento['event_date'])) ?>
                                                                            <i class="far fa-clock ms-2 me-1 text-gold"></i> <?= $time_display ?>
                                                                        <?php elseif (!empty($recurring)): ?>
                                                                            <i class="fas fa-redo me-1 text-gold"></i> <?= implode(', ', $recurring) ?>
                                                                            <?php if (!empty($evento['event_date'])): ?>
                                                                                <i class="far fa-clock ms-2 me-1 text-gold"></i> <?= $time_display ?>
                                                                            <?php endif; ?>
                                                                        <?php else: ?>
                                                                            <i class="far fa-calendar-alt me-1 text-gold"></i> Data a confirmar
                                                                        <?php endif; ?>
                                                                    </h6>
                                                                </div>
                                                                <span class="badge rounded-pill bg-primary text-white"><?= ucfirst($evento['type']) ?></span>
                                                            </div>
                                                            <p class="card-text mt-3 text-secondary"><?= htmlspecialchars($evento['description']) ?></p>
                                                            <?php if (!empty($evento['location']) && $evento['location'] !== $congregacao): ?>
                                                                <p class="card-text mt-3 pt-3 border-top"><small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= htmlspecialchars($evento['location']) ?></small></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <?php if (!empty($evento['banner_path'])): ?>
                                                    <div class="modal fade" id="bannerModal<?= $evento['id'] ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content bg-transparent border-0">
                                                                <div class="modal-body p-0 position-relative text-center">
                                                                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    <img src="<?= $evento['banner_path'] ?>" class="img-fluid rounded shadow-lg" alt="<?= htmlspecialchars($evento['title']) ?>">
                                                                    <div class="mt-2">
                                                                        <a href="<?= $evento['banner_path'] ?>" download class="btn btn-light btn-sm"><i class="fas fa-download me-2"></i> Baixar Banner</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="section-carousel-btn next" aria-label="Próxima congregação">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Convites Section -->
    <section id="convites" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title">Convites</h2>
                <p class="text-muted">Você é nosso convidado de honra</p>
                <p class="text-muted small mb-0">Role para o lado para ver mais informações.</p>
            </div>

            <?php
            $convitesPorCongregacao = [];
            foreach ($convites as $conviteItem) {
                $loc = !empty($conviteItem['location']) ? $conviteItem['location'] : 'Geral';
                if (!isset($convitesPorCongregacao[$loc])) {
                    $convitesPorCongregacao[$loc] = [];
                }
                $convitesPorCongregacao[$loc][] = $conviteItem;
            }
            ksort($convitesPorCongregacao);
            if (isset($convitesPorCongregacao['Sede'])) {
                $sede = $convitesPorCongregacao['Sede'];
                unset($convitesPorCongregacao['Sede']);
                $convitesPorCongregacao = array_merge(['Sede' => $sede], $convitesPorCongregacao);
            }
            ?>

            <?php if (empty($convitesPorCongregacao)): ?>
                <div class="text-center">
                    <p class="text-muted">Nenhum convite especial no momento.</p>
                </div>
            <?php else: ?>
                <div class="section-carousel-shell">
                    <div class="section-carousel js-section-carousel">
                        <button type="button" class="section-carousel-btn prev" aria-label="Convite anterior">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="section-carousel-track">
                            <?php foreach ($convitesPorCongregacao as $congregacao => $items): ?>
                                <div class="section-carousel-slide">
                                    <div class="section-panel-card scrollable-panel">
                                        <div class="section-panel-head">
                                            <span class="section-panel-kicker"><i class="fas fa-envelope-open-text"></i> Convites</span>
                                            <h3><?= htmlspecialchars($congregacao) ?></h3>
                                            <p>Role para ver convites de outras congregações.</p>
                                        </div>
                                        <div class="section-panel-body">
                                            <div class="section-panel-grid">
                                                <?php foreach ($items as $convite): ?>
                                                    <div class="card h-100 border-0 shadow-sm" style="background: #fff; border-top: 4px solid var(--primary-gold) !important;">
                                                        <?php if (!empty($convite['banner_path'])): ?>
                                                            <div class="position-relative">
                                                                <img src="<?= $convite['banner_path'] ?>" class="card-img-top" alt="<?= htmlspecialchars($convite['title']) ?>" style="height: 250px; object-fit: cover;">
                                                                <div class="position-absolute top-0 end-0 m-2">
                                                                    <span class="badge bg-gold text-dark shadow-sm">Especial</span>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-gold" style="height: 250px;">
                                                                <i class="fas fa-envelope-open-text fa-4x"></i>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="card-body text-center p-4">
                                                            <h5 class="card-title fw-bold text-dark mb-3"><?= htmlspecialchars($convite['title']) ?></h5>

                                                            <div class="mb-3">
                                                                <?php
                                                                    $is_valid_date = !empty($convite['event_date']) && strpos($convite['event_date'], '1970-01-01') === false;
                                                                    $event_time = !empty($convite['event_date']) ? date('H:i', strtotime($convite['event_date'])) : '';
                                                                ?>
                                                                <?php if ($is_valid_date): ?>
                                                                    <p class="mb-1 text-gold fw-bold fs-5">
                                                                        <i class="far fa-calendar-alt me-2"></i> <?= date('d/m/Y', strtotime($convite['event_date'])) ?>
                                                                    </p>
                                                                    <p class="mb-0 text-muted">
                                                                        <i class="far fa-clock me-2"></i> <?= $event_time ?>
                                                                    </p>
                                                                <?php else: ?>
                                                                    <p class="mb-0 text-gold fw-bold">Data a confirmar</p>
                                                                <?php endif; ?>
                                                            </div>

                                                            <p class="card-text text-muted small mb-4"><?= htmlspecialchars($convite['description']) ?></p>

                                                            <?php if (!empty($convite['location'])): ?>
                                                                <div class="d-inline-block border rounded-pill px-3 py-1 bg-light text-muted small">
                                                                    <i class="fas fa-map-marker-alt text-danger me-1"></i> <?= htmlspecialchars($convite['location']) ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <?php if (!empty($convite['banner_path'])): ?>
                                                                <div class="mt-4">
                                                                    <button type="button" class="btn btn-outline-gold btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#conviteModal<?= $convite['id'] ?>">
                                                                        <i class="fas fa-expand me-1"></i> Ver Detalhes
                                                                    </button>
                                                                </div>

                                                                <div class="modal fade" id="conviteModal<?= $convite['id'] ?>" tabindex="-1" aria-hidden="true">
                                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                                        <div class="modal-content bg-transparent border-0">
                                                                            <div class="modal-body p-0 position-relative text-center">
                                                                                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                <img src="<?= $convite['banner_path'] ?>" class="img-fluid rounded shadow-lg" alt="<?= htmlspecialchars($convite['title']) ?>">
                                                                                <div class="mt-2">
                                                                                    <a href="<?= $convite['banner_path'] ?>" download class="btn btn-light btn-sm"><i class="fas fa-download me-2"></i> Baixar Convite</a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                        </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="section-carousel-btn next" aria-label="Próximo convite">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Congregations Section -->
    <section id="congregacoes" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title">Nossas Congregações</h2>
                <p class="text-muted">Encontre uma de nossas igrejas</p>
                <p class="text-muted small mb-0">Role para o lado para ver mais informações.</p>
            </div>
            <?php if (empty($congregacoes)): ?>
                <div class="text-center">
                    <p class="text-muted">Nenhuma congregação cadastrada.</p>
                </div>
            <?php else: ?>
                <div class="section-carousel-shell">
                    <div class="section-carousel js-section-carousel">
                        <button type="button" class="section-carousel-btn prev" aria-label="Congregação anterior">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="section-carousel-track">
                            <?php foreach ($congregacoes as $congregacao): ?>
                                <div class="section-carousel-slide">
                                    <div class="congregacao-card scrollable-panel">
                                        <div class="congregacao-card-media">
                                            <?php if (!empty($congregacao['photo'])): ?>
                                                <img src="/uploads/congregations/<?= htmlspecialchars($congregacao['photo']) ?>" alt="<?= htmlspecialchars($congregacao['name']) ?>">
                                            <?php else: ?>
                                                <i class="fas fa-church fa-4x text-white opacity-50"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="congregacao-card-body">
                                            <h3 class="congregacao-card-title"><?= htmlspecialchars($congregacao['name']) ?></h3>
                                            <p class="congregacao-card-meta">Dirigente: <strong><?= htmlspecialchars($congregacao['leader_name'] ?? 'Não informado') ?></strong></p>
                                            <div class="congregacao-card-info">
                                                <?php if (!empty($congregacao['address'])): ?>
                                                    <p><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($congregacao['address']) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($congregacao['city'])): ?>
                                                    <p><i class="fas fa-city me-2"></i><?= htmlspecialchars($congregacao['city']) ?><?= !empty($congregacao['state']) ? ' - ' . htmlspecialchars($congregacao['state']) : '' ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($congregacao['phone'])): ?>
                                                    <p><i class="fas fa-phone me-2"></i><?= htmlspecialchars($congregacao['phone']) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($congregacao['email'])): ?>
                                                    <p><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($congregacao['email']) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($congregacao['opening_date'])): ?>
                                                    <p><i class="fas fa-birthday-cake me-2"></i>Desde <?= date('d/m/Y', strtotime($congregacao['opening_date'])) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <?php
                                                $schedules = !empty($congregacao['service_schedule']) ? json_decode($congregacao['service_schedule'], true) : [];
                                                if (!empty($schedules)):
                                            ?>
                                                <div class="congregacao-schedules">
                                                    <h6 class="small fw-bold mb-3"><i class="far fa-clock text-gold me-1"></i> Horários de Culto</h6>
                                                    <ul class="list-unstyled small mb-0 congregacao-card-info">
                                                        <?php foreach ($schedules as $schedule): ?>
                                                            <li>
                                                                <strong><?= htmlspecialchars($schedule['day'] ?? '') ?></strong>
                                                                <span class="ms-2"><?= htmlspecialchars($schedule['start_time'] ?? '') ?><?= !empty($schedule['end_time']) ? ' às ' . htmlspecialchars($schedule['end_time']) : '' ?></span>
                                                                <?php if (!empty($schedule['name'])): ?>
                                                                    <div class="text-muted fst-italic mt-1">- <?= htmlspecialchars($schedule['name']) ?></div>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="section-carousel-btn next" aria-label="Próxima congregação">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php
        $prayerLink = '/oracao';
        $prayerTarget = '_self';
        $devotionalVersesByTheme = [
            'Ansiedade' => [
                ['text' => 'Lançando sobre ele toda a vossa ansiedade, porque ele tem cuidado de vós.', 'reference' => '1 Pedro 5:7', 'testament' => 'Novo Testamento'],
                ['text' => 'Não andeis ansiosos por coisa alguma; em tudo, porém, sejam conhecidas diante de Deus as vossas petições.', 'reference' => 'Filipenses 4:6', 'testament' => 'Novo Testamento'],
                ['text' => 'No dia em que eu temer, hei de confiar em ti.', 'reference' => 'Salmo 56:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Deixo-vos a paz, a minha paz vos dou; não se turbe o vosso coração, nem se atemorize.', 'reference' => 'João 14:27', 'testament' => 'Novo Testamento'],
                ['text' => 'Entregue o seu caminho ao Senhor; confie nele, e ele agirá.', 'reference' => 'Salmo 37:5', 'testament' => 'Velho Testamento'],
                ['text' => 'Quando a ansiedade já me dominava no íntimo, o teu consolo trouxe alívio à minha alma.', 'reference' => 'Salmo 94:19', 'testament' => 'Velho Testamento'],
                ['text' => 'O Senhor é bom, um refúgio em tempos de angústia. Ele protege os que nele confiam.', 'reference' => 'Naum 1:7', 'testament' => 'Velho Testamento'],
                ['text' => 'Venham a mim todos os que estão cansados e sobrecarregados, e eu lhes darei descanso.', 'reference' => 'Mateus 11:28', 'testament' => 'Novo Testamento'],
                ['text' => 'Tu conservarás em perfeita paz aquele cujo propósito é firme, porque em ti confia.', 'reference' => 'Isaías 26:3', 'testament' => 'Velho Testamento']
            ],
            'Fé' => [
                ['text' => 'Ora, a fé é a certeza daquilo que esperamos e a prova das coisas que não vemos.', 'reference' => 'Hebreus 11:1', 'testament' => 'Novo Testamento'],
                ['text' => 'Sem fé é impossível agradar a Deus.', 'reference' => 'Hebreus 11:6', 'testament' => 'Novo Testamento'],
                ['text' => 'Porque vivemos por fé, e não pelo que vemos.', 'reference' => '2 Coríntios 5:7', 'testament' => 'Novo Testamento'],
                ['text' => 'Se tiverdes fé do tamanho de um grão de mostarda, nada vos será impossível.', 'reference' => 'Mateus 17:20', 'testament' => 'Novo Testamento'],
                ['text' => 'O justo viverá pela sua fé.', 'reference' => 'Habacuque 2:4', 'testament' => 'Velho Testamento'],
                ['text' => 'Tudo é possível ao que crê.', 'reference' => 'Marcos 9:23', 'testament' => 'Novo Testamento'],
                ['text' => 'Confia no Senhor de todo o teu coração e não te estribes no teu próprio entendimento.', 'reference' => 'Provérbios 3:5', 'testament' => 'Velho Testamento'],
                ['text' => 'Peça-a, porém, com fé, em nada duvidando.', 'reference' => 'Tiago 1:6', 'testament' => 'Novo Testamento'],
                ['text' => 'Esperei confiantemente pelo Senhor, e ele se inclinou para mim e me ouviu.', 'reference' => 'Salmo 40:1', 'testament' => 'Velho Testamento']
            ],
            'Promessas' => [
                ['text' => 'Porque eu bem sei os planos que tenho para vós, diz o Senhor, planos de paz e não de mal.', 'reference' => 'Jeremias 29:11', 'testament' => 'Velho Testamento'],
                ['text' => 'Das promessas do Senhor nenhuma falhou; todas se cumpriram.', 'reference' => 'Josué 21:45', 'testament' => 'Velho Testamento'],
                ['text' => 'Fiel é o que vos chama, o qual também o fará.', 'reference' => '1 Tessalonicenses 5:24', 'testament' => 'Novo Testamento'],
                ['text' => 'Todas as promessas de Deus encontram nele o sim.', 'reference' => '2 Coríntios 1:20', 'testament' => 'Novo Testamento'],
                ['text' => 'Aquele que prometeu é fiel.', 'reference' => 'Hebreus 10:23', 'testament' => 'Novo Testamento'],
                ['text' => 'O céu e a terra passarão, mas as minhas palavras jamais passarão.', 'reference' => 'Mateus 24:35', 'testament' => 'Novo Testamento'],
                ['text' => 'A palavra do nosso Deus permanece eternamente.', 'reference' => 'Isaías 40:8', 'testament' => 'Velho Testamento'],
                ['text' => 'Bendito seja o Senhor, que deu repouso ao seu povo, segundo tudo o que prometera.', 'reference' => '1 Reis 8:56', 'testament' => 'Velho Testamento'],
                ['text' => 'Guardemos firme a confissão da esperança, sem vacilar.', 'reference' => 'Hebreus 10:23', 'testament' => 'Novo Testamento']
            ],
            'Força' => [
                ['text' => 'Posso todas as coisas naquele que me fortalece.', 'reference' => 'Filipenses 4:13', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor é a minha força e o meu cântico.', 'reference' => 'Salmo 118:14', 'testament' => 'Velho Testamento'],
                ['text' => 'Os que esperam no Senhor renovarão as suas forças.', 'reference' => 'Isaías 40:31', 'testament' => 'Velho Testamento'],
                ['text' => 'Diga o fraco: Eu sou forte.', 'reference' => 'Joel 3:10', 'testament' => 'Velho Testamento'],
                ['text' => 'A minha graça te basta, porque o poder se aperfeiçoa na fraqueza.', 'reference' => '2 Coríntios 12:9', 'testament' => 'Novo Testamento'],
                ['text' => 'Sede fortes e revigore-se o vosso coração, vós todos que esperais no Senhor.', 'reference' => 'Salmo 31:24', 'testament' => 'Velho Testamento'],
                ['text' => 'Fortalecei-vos no Senhor e na força do seu poder.', 'reference' => 'Efésios 6:10', 'testament' => 'Novo Testamento'],
                ['text' => 'Deus é o que me cinge de força e aperfeiçoa o meu caminho.', 'reference' => 'Salmo 18:32', 'testament' => 'Velho Testamento'],
                ['text' => 'Não temas, porque eu sou contigo; eu te fortaleço e te ajudo.', 'reference' => 'Isaías 41:10', 'testament' => 'Velho Testamento']
            ],
            'Esperança' => [
                ['text' => 'Porque eu sei em quem tenho crido.', 'reference' => '2 Timóteo 1:12', 'testament' => 'Novo Testamento'],
                ['text' => 'Bendito o homem que confia no Senhor e cuja esperança é o Senhor.', 'reference' => 'Jeremias 17:7', 'testament' => 'Velho Testamento'],
                ['text' => 'Quero trazer à memória o que me pode dar esperança.', 'reference' => 'Lamentações 3:21', 'testament' => 'Velho Testamento'],
                ['text' => 'Alegrai-vos na esperança, sede pacientes na tribulação, perseverai na oração.', 'reference' => 'Romanos 12:12', 'testament' => 'Novo Testamento'],
                ['text' => 'Ora, o Deus de esperança vos encha de todo o gozo e paz no vosso crer.', 'reference' => 'Romanos 15:13', 'testament' => 'Novo Testamento'],
                ['text' => 'A esperança que se retarda deixa o coração doente, mas o desejo cumprido é árvore de vida.', 'reference' => 'Provérbios 13:12', 'testament' => 'Velho Testamento'],
                ['text' => 'Bom é aguardar a salvação do Senhor, e isso, em silêncio.', 'reference' => 'Lamentações 3:26', 'testament' => 'Velho Testamento'],
                ['text' => 'Temos esta esperança como âncora da alma, firme e segura.', 'reference' => 'Hebreus 6:19', 'testament' => 'Novo Testamento'],
                ['text' => 'Mas eu esperarei continuamente e te louvarei cada vez mais.', 'reference' => 'Salmo 71:14', 'testament' => 'Velho Testamento']
            ],
            'Paz' => [
                ['text' => 'Tu conservarás em perfeita paz aquele cujo propósito é firme, porque em ti confia.', 'reference' => 'Isaías 26:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Deixo-vos a paz, a minha paz vos dou.', 'reference' => 'João 14:27', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor dará força ao seu povo; o Senhor abençoará o seu povo com paz.', 'reference' => 'Salmo 29:11', 'testament' => 'Velho Testamento'],
                ['text' => 'Que a paz de Cristo seja o árbitro em vosso coração.', 'reference' => 'Colossenses 3:15', 'testament' => 'Novo Testamento'],
                ['text' => 'Grande paz têm os que amam a tua lei.', 'reference' => 'Salmo 119:165', 'testament' => 'Velho Testamento'],
                ['text' => 'Em paz me deito e logo pego no sono, porque, Senhor, só tu me fazes repousar seguro.', 'reference' => 'Salmo 4:8', 'testament' => 'Velho Testamento'],
                ['text' => 'A paz de Deus, que excede todo entendimento, guardará o vosso coração.', 'reference' => 'Filipenses 4:7', 'testament' => 'Novo Testamento'],
                ['text' => 'Bem-aventurados os pacificadores, porque serão chamados filhos de Deus.', 'reference' => 'Mateus 5:9', 'testament' => 'Novo Testamento'],
                ['text' => 'O fruto da justiça semeia-se em paz.', 'reference' => 'Tiago 3:18', 'testament' => 'Novo Testamento']
            ],
            'Salvação' => [
                ['text' => 'Porque Deus amou o mundo de tal maneira que deu o seu Filho unigênito.', 'reference' => 'João 3:16', 'testament' => 'Novo Testamento'],
                ['text' => 'Crê no Senhor Jesus e serás salvo, tu e tua casa.', 'reference' => 'Atos 16:31', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor é a minha luz e a minha salvação; de quem terei medo?', 'reference' => 'Salmo 27:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Todo aquele que invocar o nome do Senhor será salvo.', 'reference' => 'Romanos 10:13', 'testament' => 'Novo Testamento'],
                ['text' => 'Em nenhum outro há salvação.', 'reference' => 'Atos 4:12', 'testament' => 'Novo Testamento'],
                ['text' => 'Com alegria tirareis água das fontes da salvação.', 'reference' => 'Isaías 12:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Pela graça sois salvos, mediante a fé.', 'reference' => 'Efésios 2:8', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor desnudou o seu santo braço perante todas as nações; e todos verão a salvação do nosso Deus.', 'reference' => 'Isaías 52:10', 'testament' => 'Velho Testamento'],
                ['text' => 'Eu sou o caminho, e a verdade, e a vida; ninguém vem ao Pai senão por mim.', 'reference' => 'João 14:6', 'testament' => 'Novo Testamento']
            ],
            'Oração' => [
                ['text' => 'Orai sem cessar.', 'reference' => '1 Tessalonicenses 5:17', 'testament' => 'Novo Testamento'],
                ['text' => 'A oração de um justo é poderosa e eficaz.', 'reference' => 'Tiago 5:16', 'testament' => 'Novo Testamento'],
                ['text' => 'Buscai o Senhor enquanto se pode achar; invocai-o enquanto está perto.', 'reference' => 'Isaías 55:6', 'testament' => 'Velho Testamento'],
                ['text' => 'Clama a mim, e responder-te-ei.', 'reference' => 'Jeremias 33:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Se permanecerdes em mim, e as minhas palavras permanecerem em vós, pedireis o que quiserdes.', 'reference' => 'João 15:7', 'testament' => 'Novo Testamento'],
                ['text' => 'Invoca-me no dia da angústia; eu te livrarei, e tu me glorificarás.', 'reference' => 'Salmo 50:15', 'testament' => 'Velho Testamento'],
                ['text' => 'Tudo quanto pedirdes em oração, crendo, recebereis.', 'reference' => 'Mateus 21:22', 'testament' => 'Novo Testamento'],
                ['text' => 'Eu amo o Senhor, porque ele ouve a minha voz e as minhas súplicas.', 'reference' => 'Salmo 116:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Antes de clamarem, eu responderei.', 'reference' => 'Isaías 65:24', 'testament' => 'Velho Testamento']
            ],
            'Sabedoria' => [
                ['text' => 'Se, porém, algum de vós necessita de sabedoria, peça-a a Deus.', 'reference' => 'Tiago 1:5', 'testament' => 'Novo Testamento'],
                ['text' => 'O temor do Senhor é o princípio da sabedoria.', 'reference' => 'Provérbios 9:10', 'testament' => 'Velho Testamento'],
                ['text' => 'Entrega o teu caminho ao Senhor; confia nele, e o mais ele fará.', 'reference' => 'Salmo 37:5', 'testament' => 'Velho Testamento'],
                ['text' => 'A sabedoria do alto é primeiramente pura, depois pacífica, indulgente.', 'reference' => 'Tiago 3:17', 'testament' => 'Novo Testamento'],
                ['text' => 'Ensina-nos a contar os nossos dias, para que alcancemos coração sábio.', 'reference' => 'Salmo 90:12', 'testament' => 'Velho Testamento'],
                ['text' => 'Reconhece-o em todos os teus caminhos, e ele endireitará as tuas veredas.', 'reference' => 'Provérbios 3:6', 'testament' => 'Velho Testamento'],
                ['text' => 'A tua palavra é lâmpada para os meus pés e luz para o meu caminho.', 'reference' => 'Salmo 119:105', 'testament' => 'Velho Testamento'],
                ['text' => 'Quem ouve estas minhas palavras e as pratica será comparado a um homem prudente.', 'reference' => 'Mateus 7:24', 'testament' => 'Novo Testamento'],
                ['text' => 'Cristo Jesus se nos tornou da parte de Deus sabedoria.', 'reference' => '1 Coríntios 1:30', 'testament' => 'Novo Testamento']
            ],
            'Gratidão' => [
                ['text' => 'Em tudo dai graças, porque esta é a vontade de Deus.', 'reference' => '1 Tessalonicenses 5:18', 'testament' => 'Novo Testamento'],
                ['text' => 'Rendei graças ao Senhor, porque ele é bom; porque a sua misericórdia dura para sempre.', 'reference' => 'Salmo 136:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Bendize, ó minha alma, ao Senhor, e não te esqueças de nenhum de seus benefícios.', 'reference' => 'Salmo 103:2', 'testament' => 'Velho Testamento'],
                ['text' => 'Graças a Deus por seu dom indescritível.', 'reference' => '2 Coríntios 9:15', 'testament' => 'Novo Testamento'],
                ['text' => 'Celebrai com júbilo ao Senhor, todos os moradores da terra.', 'reference' => 'Salmo 100:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Cantarei ao Senhor por tudo o que me tem feito.', 'reference' => 'Salmo 13:6', 'testament' => 'Velho Testamento'],
                ['text' => 'Louvando a Deus e contando com a simpatia de todo o povo.', 'reference' => 'Atos 2:47', 'testament' => 'Novo Testamento'],
                ['text' => 'Ofereçamos sempre, por meio de Jesus, a Deus sacrifício de louvor.', 'reference' => 'Hebreus 13:15', 'testament' => 'Novo Testamento'],
                ['text' => 'Entrai por suas portas com ações de graças.', 'reference' => 'Salmo 100:4', 'testament' => 'Velho Testamento']
            ],
            'Amor' => [
                ['text' => 'Acima de tudo, porém, revistam-se do amor, que é o elo perfeito.', 'reference' => 'Colossenses 3:14', 'testament' => 'Novo Testamento'],
                ['text' => 'Nós amamos porque ele nos amou primeiro.', 'reference' => '1 João 4:19', 'testament' => 'Novo Testamento'],
                ['text' => 'O amor é paciente, o amor é bondoso.', 'reference' => '1 Coríntios 13:4', 'testament' => 'Novo Testamento'],
                ['text' => 'Muitas águas não poderiam apagar o amor.', 'reference' => 'Cânticos 8:7', 'testament' => 'Velho Testamento'],
                ['text' => 'Amarás o teu próximo como a ti mesmo.', 'reference' => 'Mateus 22:39', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor teu Deus está no meio de ti, poderoso para salvar; ele se deleitará em ti com alegria.', 'reference' => 'Sofonias 3:17', 'testament' => 'Velho Testamento'],
                ['text' => 'Nisto conhecerão todos que sois meus discípulos: se tiverdes amor uns aos outros.', 'reference' => 'João 13:35', 'testament' => 'Novo Testamento'],
                ['text' => 'O ódio excita contendas, mas o amor cobre todas as transgressões.', 'reference' => 'Provérbios 10:12', 'testament' => 'Velho Testamento'],
                ['text' => 'Acima de tudo, tende amor intenso uns para com os outros.', 'reference' => '1 Pedro 4:8', 'testament' => 'Novo Testamento']
            ],
            'Direção' => [
                ['text' => 'Eu o instruirei e o ensinarei no caminho que você deve seguir.', 'reference' => 'Salmo 32:8', 'testament' => 'Velho Testamento'],
                ['text' => 'O coração do homem pode fazer planos, mas a resposta certa vem do Senhor.', 'reference' => 'Provérbios 16:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Mostra-me, Senhor, os teus caminhos, ensina-me as tuas veredas.', 'reference' => 'Salmo 25:4', 'testament' => 'Velho Testamento'],
                ['text' => 'Se alguém quer fazer a vontade dele, conhecerá a respeito da doutrina.', 'reference' => 'João 7:17', 'testament' => 'Novo Testamento'],
                ['text' => 'O homem faz planos, mas o Senhor dirige os seus passos.', 'reference' => 'Provérbios 16:9', 'testament' => 'Velho Testamento'],
                ['text' => 'As tuas ovelhas ouvem a minha voz; eu as conheço, e elas me seguem.', 'reference' => 'João 10:27', 'testament' => 'Novo Testamento'],
                ['text' => 'Guia-me pela vereda da justiça por amor do seu nome.', 'reference' => 'Salmo 23:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Quando vier, porém, o Espírito da verdade, ele vos guiará a toda a verdade.', 'reference' => 'João 16:13', 'testament' => 'Novo Testamento'],
                ['text' => 'Confia no Senhor de todo o teu coração... e ele endireitará as tuas veredas.', 'reference' => 'Provérbios 3:5-6', 'testament' => 'Velho Testamento']
            ]
        ];
        $devotionalVerses = [];
        foreach ($devotionalVersesByTheme as $theme => $themeVerses) {
            foreach ($themeVerses as $verse) {
                $verse['theme'] = $theme;
                $devotionalVerses[] = $verse;
            }
        }
    ?>
    <div class="floating-faith-hover-zone" aria-hidden="true"></div>
    <div class="floating-faith-actions" aria-label="Ações rápidas de fé">
        <div class="floating-faith-item">
            <div class="floating-faith-card floating-faith-card-prayer is-collapsed" data-faith-card>
                <button type="button" class="floating-faith-toggle" data-faith-toggle aria-expanded="false" aria-label="Abrir oração">
                    <span class="floating-faith-icon"><i class="fas fa-hands-praying"></i></span>
                    <span class="floating-faith-content">
                        <strong>Oração</strong>
                        <span>Fale com a igreja e compartilhe seu pedido.</span>
                    </span>
                </button>
                <div class="floating-faith-action-wrap">
                    <a href="<?= htmlspecialchars($prayerLink) ?>" class="floating-faith-action" target="<?= htmlspecialchars($prayerTarget) ?>"<?= $prayerTarget === '_blank' ? ' rel="noopener noreferrer"' : '' ?>>
                        <i class="fas fa-paper-plane"></i> Fazer pedido
                    </a>
                </div>
            </div>
        </div>
        <div class="floating-faith-item">
            <div class="floating-faith-card floating-faith-card-devotional is-collapsed" data-faith-card>
                <button type="button" class="floating-faith-toggle" data-faith-toggle aria-expanded="false" aria-label="Abrir devocional">
                    <span class="floating-faith-icon"><i class="fas fa-book-bible"></i></span>
                    <span class="floating-faith-content">
                        <strong>Devocional</strong>
                        <span>Abra um devocional e receba uma meditação.</span>
                    </span>
                </button>
                <div class="floating-faith-action-wrap">
                    <button type="button" class="floating-faith-action" data-bs-toggle="modal" data-bs-target="#devotionalModal">
                        <i class="fas fa-sparkles"></i> Abrir palavra
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade birthdays-modal" id="birthdaysModal" tabindex="-1" aria-labelledby="birthdaysModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="birthdays-modal-header">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="birthdays-modal-title" id="birthdaysModalLabel">Aniversariantes do Mês</h2>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                </div>
                <div class="birthdays-modal-body">
                    <div class="birthdays-modal-grid">
                        <?php foreach ($birthdays as $birthday): ?>
                            <?php
                                $birthdayName = trim((string)($birthday['name'] ?? ''));
                                $birthdayLabel = $firstAndLastName($birthdayName);
                            ?>
                            <div class="birthday-person-card">
                                <div class="birthday-person-avatar" title="<?= htmlspecialchars($birthdayName) ?>">
                                    <?php if (!empty($birthday['photo'])): ?>
                                        <img src="/uploads/members/<?= htmlspecialchars($birthday['photo']) ?>" alt="<?= htmlspecialchars($birthdayName) ?>">
                                    <?php else: ?>
                                        <?= htmlspecialchars(substr($birthdayName, 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="birthday-person-name"><?= htmlspecialchars($birthdayLabel !== '' ? $birthdayLabel : $birthdayName) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade devotional-modal" id="devotionalModal" tabindex="-1" aria-labelledby="devotionalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="devotional-modal-header">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="devotional-modal-title" id="devotionalModalLabel">Devocional do Dia</h2>
                            <p class="devotional-modal-subtitle">Abra quando quiser uma palavra de encorajamento, meditação e fé.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                </div>
                <div class="devotional-modal-body">
                    <div class="devotional-verse-card">
                        <button type="button" class="devotional-verse-share" id="devotionalShareButton" aria-label="Compartilhar mensagem">
                            <i class="fas fa-share-nodes"></i>
                        </button>
                        <span class="devotional-verse-label"><i class="fas fa-sparkles"></i> Palavra para o seu coração</span>
                        <div class="devotional-verse-meta">
                            <span class="devotional-verse-chip" id="devotionalVerseTheme"><i class="fas fa-bookmark"></i> Tema</span>
                            <span class="devotional-verse-chip" id="devotionalVerseTestament"><i class="fas fa-scroll"></i> Testamento</span>
                        </div>
                        <blockquote class="devotional-verse-text mb-0" id="devotionalVerseText"></blockquote>
                        <p class="devotional-verse-reference" id="devotionalVerseReference"></p>
                    </div>
                    <div class="devotional-modal-actions">
                        <div class="devotional-modal-note">
                            <span class="devotional-modal-note-icon"><i class="fas fa-heart"></i></span>
                            <div>
                                <strong>Momento de meditar</strong>
                                <span>Leia com calma, guarde a palavra no coracao e compartilhe essa mensagem se ela falou com voce.</span>
                            </div>
                        </div>
                        <div class="devotional-modal-buttons">
                            <button type="button" class="btn btn-primary devotional-modal-button" id="devotionalShuffleButton"><i class="fas fa-shuffle"></i> Nova palavra</button>
                        </div>
                        <div class="devotional-share-feedback" id="devotionalShareFeedback" aria-live="polite"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/layout/footer.php'; ?>

    <script>
        // Fechar menu mobile automaticamente ao clicar em um link (Específico para Home que não usa o footer padrão de admin)
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.navbar-collapse .nav-link');
            const bsCollapse = document.querySelector('.navbar-collapse');
            const countdownBlocks = document.querySelectorAll('[data-countdown]');
            const countdownTrack = document.getElementById('heroCountdownTrack');
            const scrollButtons = document.querySelectorAll('[data-countdown-scroll]');
            const countdownIndexEl = document.getElementById('heroCountdownIndex');
            const countdownTotalEl = document.getElementById('heroCountdownTotal');
            const prevBtn = document.querySelector('.countdown-carousel-btn.prev');
            const nextBtn = document.querySelector('.countdown-carousel-btn.next');
            const cultosTrack = document.getElementById('cultosCarouselTrack');
            const cultosButtons = document.querySelectorAll('[data-cultos-scroll]');
            const cultosPrevBtn = document.querySelector('.cultos-carousel-btn.prev');
            const cultosNextBtn = document.querySelector('.cultos-carousel-btn.next');
            const sectionCarousels = document.querySelectorAll('.js-section-carousel');
            const faithCards = document.querySelectorAll('[data-faith-card]');
            const faithToggles = document.querySelectorAll('[data-faith-toggle]');
            const faithDock = document.querySelector('.floating-faith-actions');
            const faithHoverZone = document.querySelector('.floating-faith-hover-zone');
            const desktopFaithHoverMedia = window.matchMedia('(hover: hover) and (pointer: fine) and (min-width: 992px)');
            const homeModals = document.querySelectorAll('.modal');
            const devotionalPool = <?= json_encode($devotionalVerses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const devotionalModal = document.getElementById('devotionalModal');
            const devotionalVerseText = document.getElementById('devotionalVerseText');
            const devotionalVerseReference = document.getElementById('devotionalVerseReference');
            const devotionalVerseTheme = document.getElementById('devotionalVerseTheme');
            const devotionalVerseTestament = document.getElementById('devotionalVerseTestament');
            const devotionalShuffleButton = document.getElementById('devotionalShuffleButton');
            const devotionalShareButton = document.getElementById('devotionalShareButton');
            const devotionalShareFeedback = document.getElementById('devotionalShareFeedback');
            let devotionalShareFeedbackTimer = 0;
            let isFaithDockVisible = false;

            function setDevotionalShareFeedback(message) {
                if (!devotionalShareFeedback) return;
                devotionalShareFeedback.textContent = message || '';
                if (devotionalShareFeedbackTimer) {
                    window.clearTimeout(devotionalShareFeedbackTimer);
                }
                if (message) {
                    devotionalShareFeedbackTimer = window.setTimeout(function() {
                        devotionalShareFeedback.textContent = '';
                    }, 2800);
                }
            }

            function getCurrentDevotionalShareText() {
                const verseText = devotionalVerseText ? devotionalVerseText.textContent.trim() : '';
                const verseReference = devotionalVerseReference ? devotionalVerseReference.textContent.trim() : '';
                const verseTheme = devotionalVerseTheme ? devotionalVerseTheme.textContent.replace('Tema:', '').trim() : '';
                const churchLabel = <?= json_encode($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                if (!verseText && !verseReference) return 'Palavra para o seu coração';
                return verseText + (verseReference ? ' - ' + verseReference : '') + (verseTheme ? ' | Tema: ' + verseTheme : '') + (churchLabel ? ' | ' + churchLabel : '');
            }

            function updateFaithDockInset() {
                if (!faithDock || !document.body) return;
                const hasExpandedCard = Array.from(faithCards).some(function(card) {
                    return card.classList.contains('is-expanded');
                });
                const isSuppressed = faithDock.classList.contains('is-suppressed');
                const useCompactInset = isSuppressed || (desktopFaithHoverMedia.matches && !isFaithDockVisible && !hasExpandedCard);
                const baseInset = useCompactInset ? 14 : 116;
                const measured = useCompactInset ? 14 : (faithDock.offsetHeight || 0) + 16;
                document.body.style.paddingBottom = String(Math.max(baseInset, measured)) + 'px';
            }

            function setFaithDockVisibility(visible) {
                if (!faithDock || !desktopFaithHoverMedia.matches) return;
                isFaithDockVisible = visible;
                faithDock.classList.toggle('is-visible', visible);
                updateFaithDockInset();
            }

            function setFaithCardState(card, expanded) {
                if (!card) return;
                card.classList.toggle('is-expanded', expanded);
                card.classList.toggle('is-collapsed', !expanded);
                const toggle = card.querySelector('[data-faith-toggle]');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                }
            }

            faithToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(event) {
                    event.preventDefault();
                    const card = toggle.closest('[data-faith-card]');
                    const shouldExpand = card ? !card.classList.contains('is-expanded') : false;

                    faithCards.forEach(function(item) {
                        setFaithCardState(item, item === card ? shouldExpand : false);
                    });

                    updateFaithDockInset();
                });
            });

            document.addEventListener('click', function(event) {
                if (event.target.closest('.floating-faith-actions')) return;
                faithCards.forEach(function(card) {
                    setFaithCardState(card, false);
                });
                if (desktopFaithHoverMedia.matches) {
                    setFaithDockVisibility(false);
                    return;
                }
                updateFaithDockInset();
            });

            if (faithHoverZone && faithDock) {
                faithHoverZone.addEventListener('mouseenter', function() {
                    setFaithDockVisibility(true);
                });
                faithDock.addEventListener('mouseenter', function() {
                    setFaithDockVisibility(true);
                });
                faithDock.addEventListener('mouseleave', function() {
                    const hasExpandedCard = Array.from(faithCards).some(function(card) {
                        return card.classList.contains('is-expanded');
                    });
                    if (!hasExpandedCard) {
                        setFaithDockVisibility(false);
                    }
                });
            }

            homeModals.forEach(function(modalEl) {
                modalEl.addEventListener('show.bs.modal', function() {
                    faithCards.forEach(function(card) {
                        setFaithCardState(card, false);
                    });
                    if (faithDock) {
                        faithDock.classList.add('is-suppressed');
                    }
                    updateFaithDockInset();
                });

                modalEl.addEventListener('hidden.bs.modal', function() {
                    if (faithDock) {
                        faithDock.classList.remove('is-suppressed');
                    }
                    if (desktopFaithHoverMedia.matches) {
                        setFaithDockVisibility(false);
                        return;
                    }
                    updateFaithDockInset();
                });
            });

            window.addEventListener('resize', updateFaithDockInset);
            updateFaithDockInset();

            function renderDevotional(index) {
                if (!Array.isArray(devotionalPool) || !devotionalPool.length || !devotionalVerseText || !devotionalVerseReference) return;
                const safeIndex = Math.max(0, index % devotionalPool.length);
                const verse = devotionalPool[safeIndex];
                devotionalVerseText.textContent = '"' + (verse.text || '') + '"';
                devotionalVerseReference.textContent = verse.reference || '';
                if (devotionalVerseTheme) {
                    devotionalVerseTheme.innerHTML = '<i class="fas fa-bookmark"></i> Tema: ' + (verse.theme || 'Geral');
                }
                if (devotionalVerseTestament) {
                    devotionalVerseTestament.innerHTML = '<i class="fas fa-scroll"></i> ' + (verse.testament || 'Testamento');
                }
                setDevotionalShareFeedback('');
            }

            if (Array.isArray(devotionalPool) && devotionalPool.length) {
                const today = new Date();
                const seed = (today.getDate() + today.getMonth() + today.getFullYear()) % devotionalPool.length;
                renderDevotional(seed);

                if (devotionalShuffleButton) {
                    devotionalShuffleButton.addEventListener('click', function() {
                        const randomIndex = Math.floor(Math.random() * devotionalPool.length);
                        renderDevotional(randomIndex);
                    });
                }

                if (devotionalShareButton) {
                    devotionalShareButton.addEventListener('click', async function() {
                        const shareText = getCurrentDevotionalShareText();
                        const sharePayload = {
                            title: 'Devocional do Dia - ' + <?= json_encode($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                            text: shareText
                        };

                        try {
                            if (navigator.share) {
                                await navigator.share(sharePayload);
                                setDevotionalShareFeedback('Mensagem compartilhada com sucesso.');
                                return;
                            }

                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                await navigator.clipboard.writeText(shareText);
                                setDevotionalShareFeedback('Mensagem copiada para compartilhar.');
                                return;
                            }

                            const tempField = document.createElement('textarea');
                            tempField.value = shareText;
                            tempField.setAttribute('readonly', 'readonly');
                            tempField.style.position = 'absolute';
                            tempField.style.left = '-9999px';
                            document.body.appendChild(tempField);
                            tempField.select();
                            document.execCommand('copy');
                            document.body.removeChild(tempField);
                            setDevotionalShareFeedback('Mensagem copiada para compartilhar.');
                        } catch (error) {
                            setDevotionalShareFeedback('Nao foi possivel compartilhar agora.');
                        }
                    });
                }
            }

            countdownBlocks.forEach(function(block) {
                const targetValue = block.getAttribute('data-countdown');
                if (!targetValue) return;

                const parsedDate = new Date(targetValue.replace(' ', 'T'));
                const target = parsedDate.getTime();
                if (Number.isNaN(target)) return;

                const daysEl = block.querySelector('[data-unit="days"]');
                const hoursEl = block.querySelector('[data-unit="hours"]');
                const minutesEl = block.querySelector('[data-unit="minutes"]');
                const secondsEl = block.querySelector('[data-unit="seconds"]');

                function updateCountdown() {
                    const diff = Math.max(0, target - Date.now());
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                    const minutes = Math.floor((diff / (1000 * 60)) % 60);
                    const seconds = Math.floor((diff / 1000) % 60);

                    if (daysEl) daysEl.textContent = String(days);
                    if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
                    if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
                    if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
                }

                updateCountdown();
                window.setInterval(updateCountdown, 1000);
            });

            function getCarouselIndex() {
                if (!countdownTrack) return 0;
                const w = countdownTrack.clientWidth || 1;
                return Math.max(0, Math.min((countdownTrack.children.length || 1) - 1, Math.round(countdownTrack.scrollLeft / w)));
            }

            function updateCarouselUI() {
                if (!countdownTrack) return;
                const total = countdownTrack.children.length || 0;
                const idx = getCarouselIndex();
                if (countdownTotalEl && total) countdownTotalEl.textContent = String(total);
                if (countdownIndexEl && total) countdownIndexEl.textContent = String(idx + 1);
                if (prevBtn) prevBtn.disabled = idx <= 0;
                if (nextBtn) nextBtn.disabled = total ? idx >= total - 1 : true;
            }

            if (countdownTrack) {
                let rafId = 0;
                countdownTrack.addEventListener('scroll', function() {
                    if (rafId) return;
                    rafId = window.requestAnimationFrame(function() {
                        rafId = 0;
                        updateCarouselUI();
                    });
                }, { passive: true });
                updateCarouselUI();
            }

            if (countdownTrack && scrollButtons.length) {
                scrollButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        const direction = button.getAttribute('data-countdown-scroll');
                        const amount = countdownTrack.clientWidth;
                        countdownTrack.scrollBy({
                            left: direction === 'next' ? amount : -amount,
                            behavior: 'smooth'
                        });
                        window.setTimeout(updateCarouselUI, 250);
                    });
                });
            }

            function getCultosIndex() {
                if (!cultosTrack) return 0;
                const w = cultosTrack.clientWidth || 1;
                return Math.max(0, Math.min((cultosTrack.children.length || 1) - 1, Math.round(cultosTrack.scrollLeft / w)));
            }

            function updateCultosUI() {
                if (!cultosTrack) return;
                const total = cultosTrack.children.length || 0;
                const idx = getCultosIndex();
                if (cultosPrevBtn) cultosPrevBtn.disabled = idx <= 0;
                if (cultosNextBtn) cultosNextBtn.disabled = total ? idx >= total - 1 : true;
            }

            if (cultosTrack) {
                let cultosRafId = 0;
                cultosTrack.addEventListener('scroll', function() {
                    if (cultosRafId) return;
                    cultosRafId = window.requestAnimationFrame(function() {
                        cultosRafId = 0;
                        updateCultosUI();
                    });
                }, { passive: true });
                updateCultosUI();
            }

            if (cultosTrack && cultosButtons.length) {
                cultosButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        const direction = button.getAttribute('data-cultos-scroll');
                        const amount = cultosTrack.clientWidth;
                        cultosTrack.scrollBy({
                            left: direction === 'next' ? amount : -amount,
                            behavior: 'smooth'
                        });
                        window.setTimeout(updateCultosUI, 250);
                    });
                });
            }

            sectionCarousels.forEach(function(carousel) {
                const track = carousel.querySelector('.section-carousel-track');
                const prev = carousel.querySelector('.section-carousel-btn.prev');
                const next = carousel.querySelector('.section-carousel-btn.next');
                if (!track) return;

                function getIndex() {
                    const w = track.clientWidth || 1;
                    return Math.max(0, Math.min((track.children.length || 1) - 1, Math.round(track.scrollLeft / w)));
                }

                function updateUI() {
                    const total = track.children.length || 0;
                    const idx = getIndex();
                    if (prev) prev.disabled = idx <= 0;
                    if (next) next.disabled = total ? idx >= total - 1 : true;
                }

                let rafId = 0;
                track.addEventListener('scroll', function() {
                    if (rafId) return;
                    rafId = window.requestAnimationFrame(function() {
                        rafId = 0;
                        updateUI();
                    });
                }, { passive: true });

                [prev, next].forEach(function(button) {
                    if (!button) return;
                    button.addEventListener('click', function() {
                        const amount = track.clientWidth;
                        track.scrollBy({
                            left: button.classList.contains('next') ? amount : -amount,
                            behavior: 'smooth'
                        });
                        window.setTimeout(updateUI, 250);
                    });
                });

                updateUI();
            });

            if (bsCollapse) {
                navLinks.forEach((l) => {
                    l.addEventListener('click', () => {
                        // Verifica se está visível (mobile) e fecha
                        if (window.getComputedStyle(bsCollapse).display !== 'none' && bsCollapse.classList.contains('show')) {
                            // Usar a API do Bootstrap para fechar
                            var collapseInstance = bootstrap.Collapse.getInstance(bsCollapse);
                            if (collapseInstance) {
                                collapseInstance.hide();
                            } else {
                                // Fallback se a instância não existir
                                new bootstrap.Collapse(bsCollapse).hide();
                            }
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>

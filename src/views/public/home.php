<?php $siteProfile = getChurchSiteProfileSettings(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($siteProfile['alias'] ?? 'IVN') . ' - ' . ($siteProfile['name'] ?? 'Igreja Vida Nova')) ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" type="image/png">
    <link rel="icon" href="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" type="image/png">
    <!-- PWA / Web App Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#b30000">

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
        
        body { font-family: '<?= explode(',', $site_settings['font_family'] ?? 'Poppins, sans-serif')[0] ?>', sans-serif; color: var(--text-dark); }
        
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
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?= $bgUrl ?>');
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
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <!-- Placeholder para a logo -->
                <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>" onerror="this.style.display='none'">
                <span class="d-none d-md-inline"><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></span>
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
        <div class="container">
            <h1 class="display-3 hero-title mb-4">Bem-vindo à <span class="hero-highlight"><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></span></h1>
            <p class="lead mb-5 fs-4">Uma igreja comprometida com a Palavra de Deus e o amor ao próximo.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#cultos" class="btn btn-primary btn-lg rounded-pill shadow-lg">Nossos Cultos</a>
                <a href="/portal/login" class="btn btn-outline-light btn-lg rounded-pill shadow-lg"><i class="fas fa-user me-2"></i> Área do Membro</a>
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

    <!-- About Section -->
    <section id="sobre" class="py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-md-6 text-center">
                    <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" class="img-fluid" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?> Logo" style="max-height: 400px;">
                </div>
                <div class="col-md-6 mt-4 mt-md-0 ps-md-5">
                    <h2 class="section-title">Quem Somos</h2>
                    <p class="lead text-muted"><?= htmlspecialchars(($siteProfile['alias'] ?? 'IVN') . ' – ' . ($siteProfile['name'] ?? 'Igreja Vida Nova')) ?></p>
                    <p class="text-secondary"><?= nl2br(htmlspecialchars($siteProfile['about_text'])) ?></p>
                    <a href="#" class="btn btn-outline-danger mt-3 rounded-pill px-4">Saiba Mais</a>
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
                <!-- Abas de Congregação -->
                <style>
                    /* Forçar override local para garantir as cores corretas */
                    .nav-pills .nav-link {
                        color: #b30000 !important; /* Vermelho */
                        background-color: #fff !important; /* Fundo Branco */
                        border: 1px solid #b30000 !important; /* Borda Vermelha */
                        margin: 0 5px;
                        font-weight: 600;
                    }
                    .nav-pills .nav-link:hover {
                        background-color: #800000 !important; /* Vermelho Escuro Hover */
                        color: #fff !important;
                        border-color: #800000 !important;
                    }
                    .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
                        background-color: #b30000 !important; /* Vermelho Ativo */
                        color: #fff !important; /* Texto Branco */
                        border-color: #b30000 !important;
                    }
                </style>
                <ul class="nav nav-pills justify-content-center mb-5" id="pills-tab" role="tablist">
                    <?php $first = true; foreach ($cultosPorCongregacao as $congregacao => $items): 
                        $slug = md5($congregacao);
                    ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $first ? 'active' : '' ?> rounded-pill px-4 mx-1" id="pills-<?= $slug ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-<?= $slug ?>" type="button" role="tab" aria-controls="pills-<?= $slug ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                                <?= htmlspecialchars($congregacao) ?>
                            </button>
                        </li>
                    <?php $first = false; endforeach; ?>
                </ul>

                <!-- Conteúdo das Abas -->
                <div class="tab-content" id="pills-tabContent">
                    <?php $first = true; foreach ($cultosPorCongregacao as $congregacao => $items): 
                        $slug = md5($congregacao);
                    ?>
                        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="pills-<?= $slug ?>" role="tabpanel" aria-labelledby="pills-<?= $slug ?>-tab">
                            <div class="row justify-content-center">
                                <?php foreach ($items as $culto): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card service-card h-100 text-center border-0 shadow-sm">
                                            <div class="card-body p-4">
                                                <div class="mb-3 text-gold">
                                                    <i class="fas fa-bible fa-3x"></i>
                                                </div>
                                                <h4 class="fw-bold mb-3"><?= htmlspecialchars($culto['title']) ?></h4>
                                                
                                                <?php 
                                                    // Lógica de exibição de data/hora
                                                    $is_valid_date = !empty($culto['event_date']) && strpos($culto['event_date'], '1970-01-01') === false;
                                                    $time_only = !empty($culto['event_date']) ? date('H:i', strtotime($culto['event_date'])) : '';
                                                    $end_time = !empty($culto['end_time']) ? $culto['end_time'] : '';
                                                    
                                                    $days = !empty($culto['recurring_days']) ? json_decode($culto['recurring_days'], true) : [];
                                                    if (!is_array($days)) $days = []; // Fallback
                                                ?>

                                                <div class="mb-3">
                                                    <?php if (!empty($days)): ?>
                                                        <span class="badge bg-gold text-dark mb-2 fs-6"><?= implode(', ', $days) ?></span>
                                                        <div class="text-muted fs-5">
                                                            <i class="far fa-clock me-1"></i> <?= $time_only ? $time_only : '' ?> <?= $end_time ? ' às ' . $end_time : '' ?>
                                                        </div>
                                                    <?php elseif ($is_valid_date): ?>
                                                        <div class="text-muted fs-5">
                                                            <i class="far fa-calendar-alt me-1"></i> <?= date('d/m/Y', strtotime($culto['event_date'])) ?>
                                                            <br>
                                                            <i class="far fa-clock me-1"></i> <?= $time_only ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-muted">Horário a confirmar</div>
                                                    <?php endif; ?>
                                                </div>

                                                <p class="card-text text-muted"><?= htmlspecialchars($culto['description']) ?></p>
                                                
                                                <?php if (!empty($culto['location']) && $culto['location'] !== $congregacao): ?>
                                                    <small class="text-muted d-block mt-3 border-top pt-2">
                                                        <i class="fas fa-map-marker-alt text-danger me-1"></i> <?= htmlspecialchars($culto['location']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php $first = false; endforeach; ?>
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
                <!-- Abas de Congregação (Eventos) -->
                <!-- Reutilizando o estilo #pills-tab definido anteriormente -->
                <ul class="nav nav-pills justify-content-center mb-5" id="pills-tab-eventos" role="tablist">
                    <?php $first = true; foreach ($eventosPorCongregacao as $congregacao => $items): 
                        $slug = 'evt-' . md5($congregacao);
                    ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $first ? 'active' : '' ?> rounded-pill px-4 mx-1" id="pills-<?= $slug ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-<?= $slug ?>" type="button" role="tab" aria-controls="pills-<?= $slug ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                                <?= htmlspecialchars($congregacao) ?>
                            </button>
                        </li>
                    <?php $first = false; endforeach; ?>
                </ul>
                
                <!-- Estilo Específico para Abas de Eventos (Cópia do Cultos para garantir) -->
                <style>
                    #pills-tab-eventos .nav-link {
                        color: #b30000 !important;
                        background-color: #fff !important;
                        border: 1px solid #b30000 !important;
                        font-weight: 600 !important;
                        margin: 0 5px;
                        text-transform: uppercase;
                        font-size: 0.9rem;
                        letter-spacing: 0.5px;
                        border-radius: 50px !important;
                    }
                    #pills-tab-eventos .nav-link:hover {
                        color: #fff !important;
                        background-color: #b30000 !important;
                        border-color: #b30000 !important;
                        transform: translateY(-1px);
                    }
                    #pills-tab-eventos .nav-link.active {
                        background-color: #b30000 !important;
                        color: #fff !important;
                        border-color: #b30000 !important;
                        box-shadow: 0 4px 10px rgba(179, 0, 0, 0.3) !important;
                    }
                </style>

                <!-- Conteúdo das Abas -->
                <div class="tab-content" id="pills-tabContent-eventos">
                    <?php $first = true; foreach ($eventosPorCongregacao as $congregacao => $items): 
                        $slug = 'evt-' . md5($congregacao);
                    ?>
                        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="pills-<?= $slug ?>" role="tabpanel" aria-labelledby="pills-<?= $slug ?>-tab">
                            <div class="row">
                                <?php foreach ($items as $evento): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card event-card shadow-sm h-100 border-0 overflow-hidden">
                                            <!-- Image Container (Only if Banner Exists) -->
                                            <?php if (!empty($evento['banner_path'])): ?>
                                                <div class="position-relative">
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted overflow-hidden" style="height: 180px;">
                                                        <!-- Blurred Background Effect for Thumbnail feel -->
                                                        <img src="<?= $evento['banner_path'] ?>" class="w-100 h-100" style="object-fit: cover; filter: blur(2px); opacity: 0.8;" alt="Thumbnail">
                                                        
                                                        <!-- Action Button Overlay -->
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
                                    </div>

                                    <!-- Modal for Banner -->
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
                    <?php $first = false; endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

        </div>
    </section>

    <!-- Convites Section -->
    <section id="convites" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title">Convites</h2>
                <p class="text-muted">Você é nosso convidado de honra</p>
            </div>
            
            <?php if (empty($convites)): ?>
                <div class="text-center">
                    <p class="text-muted">Nenhum convite especial no momento.</p>
                </div>
            <?php else: ?>
                <div class="row justify-content-center">
                    <?php foreach ($convites as $convite): ?>
                        <div class="col-md-4 mb-4">
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

                                        <!-- Modal for Convite Banner -->
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
                        </div>
                    <?php endforeach; ?>
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
            </div>
            <div class="row">
                <?php if (empty($congregacoes)): ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Nenhuma congregação cadastrada.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($congregacoes as $congregacao): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm hover-card">
                            <?php if (!empty($congregacao['photo'])): ?>
                                <img src="/uploads/congregations/<?= $congregacao['photo'] ?>" class="card-img-top" alt="<?= htmlspecialchars($congregacao['name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-church fa-4x text-white opacity-50"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body text-center p-4">
                                <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($congregacao['name']) ?></h5>
                                <p class="text-muted small mb-2">Dirigente: <span class="fw-bold"><?= htmlspecialchars($congregacao['leader_name'] ?? 'Não informado') ?></span></p>
                                
                                <hr class="my-3">
                                
                                <div class="text-start">
                                    <?php if (!empty($congregacao['address'])): ?>
                                        <p class="mb-2 small">
                                            <i class="fas fa-map-marker-alt text-danger me-2"></i> 
                                            <?= htmlspecialchars($congregacao['address']) ?>
                                            <?= !empty($congregacao['city']) ? '<br><span class="ms-4 text-muted">' . htmlspecialchars($congregacao['city']) . '-' . htmlspecialchars($congregacao['state']) . '</span>' : '' ?>
                                            <?= !empty($congregacao['zip_code']) ? '<br><span class="ms-4 text-muted">CEP: ' . htmlspecialchars($congregacao['zip_code']) . '</span>' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($congregacao['phone'])): ?>
                                        <p class="mb-2 small"><i class="fas fa-phone text-success me-2"></i> <?= htmlspecialchars($congregacao['phone']) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($congregacao['email'])): ?>
                                        <p class="mb-2 small"><i class="fas fa-envelope text-primary me-2"></i> <?= htmlspecialchars($congregacao['email']) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($congregacao['opening_date'])): ?>
                                        <p class="mb-2 small"><i class="fas fa-birthday-cake text-warning me-2"></i> Desde <?= date('d/m/Y', strtotime($congregacao['opening_date'])) ?></p>
                                    <?php endif; ?>

                                    <?php 
                                        $schedules = !empty($congregacao['service_schedule']) ? json_decode($congregacao['service_schedule'], true) : [];
                                        if (!empty($schedules)): 
                                    ?>
                                        <div class="mt-3 pt-3 border-top">
                                            <h6 class="small fw-bold mb-2"><i class="far fa-clock text-gold me-1"></i> Horários de Culto:</h6>
                                            <ul class="list-unstyled small mb-0">
                                                <?php foreach ($schedules as $schedule): ?>
                                                    <li class="mb-1">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="fw-bold"><?= $schedule['day'] ?></span>
                                                            <span>
                                                                <?= $schedule['start_time'] ?> 
                                                                <?= !empty($schedule['end_time']) ? ' às ' . $schedule['end_time'] : '' ?>
                                                            </span>
                                                        </div>
                                                        <?php if (!empty($schedule['name'])): ?>
                                                            <div class="text-muted fst-italic ms-2" style="font-size: 0.9em;">- <?= htmlspecialchars($schedule['name']) ?></div>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include __DIR__ . '/layout/footer.php'; ?>

    <script>
        // Fechar menu mobile automaticamente ao clicar em um link (Específico para Home que não usa o footer padrão de admin)
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.navbar-collapse .nav-link');
            const bsCollapse = document.querySelector('.navbar-collapse');

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

<?php
$siteProfile = $siteProfile ?? getChurchSiteProfileSettings();
$totalCultos = count($cultos);
$weekdaysPt = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
$todayWeekday = $weekdaysPt[(int)date('w')];
$whatsappDigits = preg_replace('/\D/', '', (string)($siteProfile['phone'] ?? ''));
if ($whatsappDigits !== '' && strlen($whatsappDigits) <= 11) {
    $whatsappDigits = '55' . $whatsappDigits;
}
$congregationAddressByName = [];
foreach ($congregacoes as $c) {
    if (!empty($c['name']) && !empty($c['address'])) {
        $congregationAddressByName[$c['name']] = $c['address'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cultos - <?= htmlspecialchars(getChurchBrandingName($siteProfile)) ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #b30000;
            --primary-gold: #d4af37;
        }
        body { padding-top: 76px; background: #fdfaf7; color: #2d1a21; }
        .navbar { background-color: rgba(255,255,255,0.96) !important; box-shadow: 0 2px 14px rgba(0,0,0,0.08); }
        .navbar-brand {
            font-weight: 900;
            color: #2d1a21 !important;
            display: inline-flex;
            align-items: center;
            gap: .65rem;
        }
        .navbar-brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }
        .nav-link { color: rgba(45,26,33,0.72) !important; font-weight: 600; }
        .nav-link.active, .nav-link:hover { color: var(--primary-red) !important; }

        .cultos-page-wrap { padding-top: 2.5rem; padding-bottom: 4rem; }
        .cultos-page-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.75rem;
        }
        .cultos-page-head h1 {
            font-size: 2.4rem;
            font-weight: 800;
            margin-bottom: .5rem;
        }
        .cultos-page-head p { color: #6b7280; max-width: 560px; margin-bottom: 0; }
        .cultos-count-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .84rem;
            font-weight: 700;
            color: #198754;
            white-space: nowrap;
        }
        .cultos-count-pill .dot {
            width: 8px; height: 8px; border-radius: 50%; background: #198754; display: inline-block;
        }

        .cultos-filters {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .cultos-day-pills { display: flex; flex-wrap: wrap; gap: .5rem; }
        .cultos-day-pill {
            border: 1px solid rgba(0,0,0,0.1);
            background: #fff;
            border-radius: 999px;
            padding: .5rem 1rem;
            font-size: .84rem;
            font-weight: 700;
            color: #2d1a21;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            transition: background .15s ease, color .15s ease, border-color .15s ease;
        }
        .cultos-day-pill .count { color: inherit; opacity: .6; font-weight: 700; }
        .cultos-day-pill.is-active {
            background: #212529;
            border-color: #212529;
            color: #fff;
        }
        .cultos-filters-right { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .cultos-filters-right .form-select {
            border-radius: 999px;
            border-color: rgba(0,0,0,0.1);
            font-size: .84rem;
            font-weight: 700;
            color: #2d1a21;
            padding: .5rem 2.1rem .5rem 1rem;
            height: auto;
        }
        .cultos-toggle-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border: 1px solid rgba(0,0,0,0.1);
            background: #fff;
            border-radius: 999px;
            padding: .5rem 1rem;
            font-size: .84rem;
            font-weight: 700;
            color: #2d1a21;
            transition: background .15s ease, color .15s ease, border-color .15s ease;
        }
        .cultos-toggle-pill .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: rgba(0,0,0,0.25);
            flex-shrink: 0;
            transition: background .15s ease;
        }
        .cultos-toggle-pill.is-active {
            background: #212529;
            border-color: #212529;
            color: #fff;
        }
        .cultos-toggle-pill.is-active .dot { background: #2ecc71; }

        .cultos-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: start;
        }
        .cultos-timeline {
            position: sticky;
            top: 96px;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 16px;
            padding: 1.1rem;
        }
        .cultos-timeline-title {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9a8f92;
            margin-bottom: .9rem;
        }
        .cultos-timeline-item {
            display: flex;
            align-items: flex-start;
            gap: .7rem;
            width: 100%;
            border: none;
            background: none;
            text-align: left;
            padding: .55rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .cultos-timeline-item:last-child { border-bottom: none; }
        .cultos-timeline-dot {
            width: 10px; height: 10px; border-radius: 50%;
            border: 2px solid #212529;
            margin-top: .3rem;
            flex-shrink: 0;
        }
        .cultos-timeline-item.is-active .cultos-timeline-dot { background: var(--primary-red); border-color: var(--primary-red); }
        .cultos-timeline-item strong { display: block; font-size: .84rem; letter-spacing: .03em; }
        .cultos-timeline-count { display: block; font-size: .74rem; color: #9a8f92; margin-bottom: .3rem; }
        .cultos-timeline-sample { list-style: none; padding: 0; margin: 0; font-size: .74rem; color: #6b7280; }
        .cultos-timeline-sample li { margin-bottom: .1rem; }

        .cultos-day-section-title {
            display: inline-block;
            background: rgba(179,0,0,0.06);
            color: var(--primary-red);
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .06em;
            padding: .35rem .9rem;
            border-radius: 999px;
            margin-bottom: 1rem;
        }
        .cultos-day-section { margin-bottom: 2rem; }
        .cultos-day-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .cultos-full-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 16px;
            padding: 1.1rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        }
        .cultos-full-card .flat-strip-day-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .4rem .7rem;
            border-radius: 12px;
            background: var(--primary-red);
            color: #fff;
            font-weight: 800;
            font-size: .76rem;
            margin-bottom: .8rem;
            white-space: nowrap;
        }
        .cultos-full-card h4 { font-size: 1.02rem; font-weight: 800; margin-bottom: .35rem; }
        .cultos-full-card .flat-strip-card-location { display: flex; align-items: flex-start; gap: .4rem; color: #6b7280; font-size: .84rem; margin-bottom: .4rem; }
        .cultos-full-card .flat-strip-card-location i { color: var(--primary-red); margin-top: .15rem; }
        .cultos-full-card-address { display: flex; align-items: flex-start; gap: .4rem; color: #9a8f92; font-size: .78rem; margin-bottom: .4rem; }
        .cultos-full-card-address i { color: #9a8f92; margin-top: .15rem; }
        .cultos-full-card-time { display: flex; align-items: center; gap: .4rem; color: #6b7280; font-size: .84rem; margin-bottom: .6rem; }
        .cultos-full-card-time i { color: var(--primary-red); }
        .cultos-full-card-desc { color: #6b7280; font-size: .86rem; margin-bottom: .8rem; }
        .cultos-full-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            padding-top: .7rem;
            border-top: 1px solid rgba(0,0,0,0.06);
            color: #b9adb0;
            font-size: .82rem;
        }
        .cultos-full-card-footer .icon-link-btn {
            width: 30px; height: 30px; border-radius: 50%; border: none;
            background: rgba(179,0,0,0.08); color: var(--primary-red);
            display: inline-flex; align-items: center; justify-content: center;
        }

        .cultos-map-section {
            margin-top: 3rem;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 20px;
            padding: 1.75rem;
        }
        .cultos-map-section h3 { font-weight: 800; margin-bottom: .3rem; }
        .cultos-congregation-pills { display: flex; flex-wrap: wrap; gap: .5rem; margin: 1.2rem 0 1.5rem; }
        .cultos-congregation-pill {
            border: none;
            background: #212529;
            color: #fff;
            border-radius: 999px;
            padding: .5rem 1.1rem;
            font-size: .8rem;
            font-weight: 700;
            transition: opacity .15s ease;
        }
        .cultos-congregation-pill.is-off { background: #e9ecef; color: #adb5bd; }
        .cultos-map-layout {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        .cultos-illustrative-map {
            position: relative;
            height: 320px;
            background: linear-gradient(135deg, #f4ede4, #fbf7f1);
            border-radius: 16px;
            overflow: hidden;
        }
        .cultos-illustrative-map svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        .cultos-map-pin {
            position: absolute;
            transform: translate(-50%, -100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .3rem;
            transition: opacity .15s ease;
        }
        .cultos-map-pin.is-off { opacity: .25; }
        .cultos-map-pin .pin-icon {
            width: 34px; height: 34px; border-radius: 50%;
            background: #6c757d; color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .82rem;
        }
        .cultos-map-pin .pin-label {
            background: #212529; color: #fff; font-size: .72rem; font-weight: 700;
            padding: .25rem .6rem; border-radius: 999px; white-space: nowrap;
        }
        .map-illustrative-note {
            position: absolute; left: 1rem; bottom: 1rem;
            font-size: .72rem; color: #9a8f92;
            display: inline-flex; align-items: center; gap: .4rem;
        }
        .map-illustrative-note .dot { width: 6px; height: 6px; border-radius: 50%; background: #198754; display: inline-block; }
        .cultos-congregation-list { display: grid; gap: .9rem; max-height: 320px; overflow-y: auto; padding-right: .4rem; }
        .cultos-congregation-list-item {
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 12px;
            padding: .85rem 1rem;
            transition: opacity .15s ease;
        }
        .cultos-congregation-list-item.is-off { opacity: .35; }

        .cultos-whatsapp-cta {
            margin-top: 2rem;
            background: #1a1a1a;
            color: #fff;
            border-radius: 20px;
            padding: 1.75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .cultos-whatsapp-cta h4 { font-weight: 800; margin-bottom: .4rem; }
        .cultos-whatsapp-cta p { color: rgba(255,255,255,0.68); margin-bottom: 0; max-width: 480px; font-size: .88rem; }

        @media (max-width: 991.98px) {
            .cultos-layout { grid-template-columns: 1fr; }
            .cultos-timeline { position: static; }
            .cultos-map-layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 575.98px) {
            .cultos-day-grid { grid-template-columns: 1fr; }
            .cultos-page-head h1 { font-size: 1.9rem; }
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
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCultos" aria-controls="navbarCultos" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCultos">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="/">Início</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/cultos" aria-current="page">Cultos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/galeria">Galeria</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-dark px-4 rounded-pill text-nowrap" href="/portal/login"><i class="fas fa-right-to-bracket me-1"></i> Entrar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container cultos-page-wrap">
        <div class="cultos-page-head">
            <div>
                <h1>Programação Semanal</h1>
                <p>Encontre um culto perto de você. Cada congregação tem horários próprios — veja tudo em um só lugar.</p>
            </div>
            <div class="cultos-count-pill"><span class="dot"></span> <span id="cultosVisibleCount"><?= $totalCultos ?></span> programaç<?= $totalCultos === 1 ? 'ão encontrada' : 'ões encontradas' ?></div>
        </div>

        <?php if ($totalCultos === 0): ?>
            <div class="text-center text-muted py-5">Nenhum culto cadastrado no momento.</div>
        <?php else: ?>
            <div class="cultos-filters">
                <div class="cultos-day-pills">
                    <button type="button" class="cultos-day-pill is-active" data-filter-day="all">Todos <span class="count"><?= $totalCultos ?></span></button>
                    <?php foreach ($weekOrder as $day): ?>
                        <?php $count = count($cultosByWeekday[$day]); if ($count === 0) continue; ?>
                        <button type="button" class="cultos-day-pill" data-filter-day="<?= htmlspecialchars($day) ?>"><?= htmlspecialchars(mb_strtoupper(mb_substr($day, 0, 3))) ?> <span class="count"><?= $count ?></span></button>
                    <?php endforeach; ?>
                </div>
                <div class="cultos-filters-right">
                    <select class="form-select form-select-sm" id="filterCongregation" style="width:auto;">
                        <option value="all">Todas</option>
                        <?php foreach ($congregacoes as $c): ?>
                            <option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="cultos-toggle-pill" id="filterToday" aria-pressed="false">
                        <span class="dot"></span> Apenas hoje
                    </button>
                </div>
            </div>

            <div class="cultos-layout">
                <aside class="cultos-timeline">
                    <div class="cultos-timeline-title">Timeline da Semana</div>
                    <?php foreach ($weekOrder as $day): ?>
                        <?php $items = $cultosByWeekday[$day]; if (empty($items)) continue; ?>
                        <button type="button" class="cultos-timeline-item" data-filter-day="<?= htmlspecialchars($day) ?>">
                            <span class="cultos-timeline-dot"></span>
                            <span>
                                <strong><?= htmlspecialchars(mb_strtoupper($day)) ?></strong>
                                <span class="cultos-timeline-count"><?= count($items) ?> programaç<?= count($items) === 1 ? 'ão' : 'ões' ?></span>
                                <ul class="cultos-timeline-sample">
                                    <?php foreach (array_slice($items, 0, 2) as $it): ?>
                                        <li><?= htmlspecialchars($it['title']) ?><?= !empty($it['location']) ? ' • ' . htmlspecialchars($it['location']) : '' ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </aside>

                <div class="cultos-main">
                    <?php foreach ($weekOrder as $day): ?>
                        <?php $items = $cultosByWeekday[$day]; if (empty($items)) continue; ?>
                        <div class="cultos-day-section" data-day-section="<?= htmlspecialchars($day) ?>">
                            <span class="cultos-day-section-title"><?= htmlspecialchars(mb_strtoupper($day)) ?></span>
                            <div class="cultos-day-grid">
                                <?php foreach ($items as $culto): ?>
                                    <div class="cultos-full-card" data-day="<?= htmlspecialchars($day) ?>" data-congregation="<?= htmlspecialchars($culto['location'] ?? '') ?>">
                                        <span class="flat-strip-day-badge"><?= htmlspecialchars($culto['weekday_abbrev']) ?><?= $culto['time_start'] !== '' ? ' ' . htmlspecialchars($culto['time_start']) : '' ?></span>
                                        <h4><?= htmlspecialchars($culto['title']) ?></h4>
                                        <?php if (!empty($culto['location'])): ?>
                                            <div class="flat-strip-card-location">
                                                <i class="fas fa-location-dot"></i>
                                                <span><?= htmlspecialchars($culto['location']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php $cultoAddress = $culto['address'] ?? ($congregationAddressByName[$culto['location']] ?? ''); ?>
                                        <?php if (!empty($cultoAddress)): ?>
                                            <div class="cultos-full-card-address">
                                                <i class="fas fa-signs-post"></i>
                                                <span><?= htmlspecialchars($cultoAddress) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($culto['time_range'] !== ''): ?>
                                            <div class="cultos-full-card-time"><i class="far fa-clock"></i> <?= htmlspecialchars($culto['time_range']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($culto['description'])): ?>
                                            <p class="cultos-full-card-desc"><?= htmlspecialchars($culto['description']) ?></p>
                                        <?php endif; ?>
                                        <div class="cultos-full-card-footer">
                                            <span>Esperamos você</span>
                                            <?php if (!empty($culto['banner_path'])): ?>
                                                <button type="button" class="icon-link-btn" data-bs-toggle="modal" data-bs-target="#bannerModalCulto<?= (int)$culto['id'] ?>"><i class="fas fa-chevron-right"></i></button>
                                                <div class="modal fade" id="bannerModalCulto<?= (int)$culto['id'] ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content bg-transparent border-0">
                                                            <div class="modal-body p-0 position-relative text-center">
                                                                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                <img src="<?= htmlspecialchars($culto['banner_path']) ?>" class="img-fluid rounded shadow-lg" alt="<?= htmlspecialchars($culto['title']) ?>">
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
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($congregacoes)): ?>
                <div class="cultos-map-section">
                    <h3>Onde estamos</h3>
                    <p class="text-muted">Veja as congregações com programação ativa nos filtros atuais.</p>
                    <div class="cultos-congregation-pills">
                        <?php foreach ($congregacoes as $c): ?>
                            <button type="button" class="cultos-congregation-pill is-active" data-congregation-toggle="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="cultos-map-layout">
                        <div class="cultos-illustrative-map">
                            <?php
                                $pinCount = count($congregacoes);
                                $pinPositions = [];
                                foreach ($congregacoes as $i => $c) {
                                    $x = $pinCount > 1 ? 12 + ($i * (76 / max(1, $pinCount - 1))) : 46;
                                    $y = 32 + (($i % 2 === 0) ? 0 : 34) + (($i % 3) * 6);
                                    $pinPositions[] = ['x' => $x, 'y' => min(78, $y)];
                                }
                            ?>
                            <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                                <?php for ($i = 0; $i < count($pinPositions) - 1; $i++): ?>
                                    <path d="M <?= $pinPositions[$i]['x'] ?> <?= $pinPositions[$i]['y'] ?> Q <?= ($pinPositions[$i]['x'] + $pinPositions[$i + 1]['x']) / 2 ?> <?= max(10, min($pinPositions[$i]['y'], $pinPositions[$i + 1]['y']) - 12) ?>, <?= $pinPositions[$i + 1]['x'] ?> <?= $pinPositions[$i + 1]['y'] ?>" fill="none" stroke="rgba(0,0,0,0.12)" stroke-width=".5" />
                                <?php endfor; ?>
                            </svg>
                            <?php foreach ($congregacoes as $i => $c): ?>
                                <div class="cultos-map-pin" data-map-pin="<?= htmlspecialchars($c['name']) ?>" style="left: <?= $pinPositions[$i]['x'] ?>%; top: <?= $pinPositions[$i]['y'] ?>%;">
                                    <span class="pin-icon"><i class="fas fa-location-dot"></i></span>
                                    <span class="pin-label"><?= htmlspecialchars($c['name']) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <span class="map-illustrative-note"><span class="dot"></span> Mapa ilustrativo</span>
                        </div>
                        <div class="cultos-congregation-list">
                            <?php foreach ($congregacoes as $c): ?>
                                <?php $items = array_values(array_filter($cultos, function ($x) use ($c) { return ($x['location'] ?? '') === $c['name']; })); ?>
                                <?php if (empty($items)) continue; ?>
                                <div class="cultos-congregation-list-item" data-congregation-block="<?= htmlspecialchars($c['name']) ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong><?= htmlspecialchars($c['name']) ?></strong>
                                        <span class="text-muted small"><?= count($items) ?> horário<?= count($items) === 1 ? '' : 's' ?></span>
                                    </div>
                                    <?php foreach ($items as $it): ?>
                                        <div class="d-flex justify-content-between small text-muted">
                                            <span><?= htmlspecialchars($it['weekday_abbrev']) ?> • <?= htmlspecialchars($it['title']) ?></span>
                                            <span><?= htmlspecialchars($it['time_start']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($whatsappDigits !== ''): ?>
                <div class="cultos-whatsapp-cta">
                    <div>
                        <h4>Não encontrou horário?</h4>
                        <p>Fale com nossa equipe. Te ajudamos a encontrar a congregação mais próxima e o melhor horário para você e sua família.</p>
                    </div>
                    <a href="https://wa.me/<?= htmlspecialchars($whatsappDigits) ?>?text=<?= rawurlencode('Olá! Gostaria de ajuda para encontrar um horário de culto.') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success rounded-pill px-4"><i class="fab fa-whatsapp me-2"></i> Fale no WhatsApp</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/partials/floating_faith_widget.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var todayWeekday = <?= json_encode($todayWeekday, JSON_UNESCAPED_UNICODE) ?>;
            var state = { day: 'all', congregation: 'all', today: false };

            var dayPills = document.querySelectorAll('[data-filter-day]');
            var congregationSelect = document.getElementById('filterCongregation');
            var todayToggle = document.getElementById('filterToday');
            var cards = document.querySelectorAll('.cultos-full-card');
            var daySections = document.querySelectorAll('[data-day-section]');
            var visibleCountEl = document.getElementById('cultosVisibleCount');
            var congregationPills = document.querySelectorAll('[data-congregation-toggle]');
            var mapPins = document.querySelectorAll('[data-map-pin]');
            var congregationBlocks = document.querySelectorAll('[data-congregation-block]');

            function setActiveDayUI(day) {
                dayPills.forEach(function (el) {
                    el.classList.toggle('is-active', el.getAttribute('data-filter-day') === day);
                });
            }

            function applyFilters() {
                var visible = 0;
                cards.forEach(function (card) {
                    var matchesDay = state.day === 'all' || card.getAttribute('data-day') === state.day;
                    var matchesCongregation = state.congregation === 'all' || card.getAttribute('data-congregation') === state.congregation;
                    var matchesToday = !state.today || card.getAttribute('data-day') === todayWeekday;
                    var show = matchesDay && matchesCongregation && matchesToday;
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                daySections.forEach(function (section) {
                    var hasVisible = Array.prototype.some.call(section.querySelectorAll('.cultos-full-card'), function (c) {
                        return c.style.display !== 'none';
                    });
                    section.style.display = hasVisible ? '' : 'none';
                });

                if (visibleCountEl) {
                    visibleCountEl.textContent = visible;
                }
            }

            dayPills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    state.day = pill.getAttribute('data-filter-day');
                    setActiveDayUI(state.day);
                    applyFilters();
                });
            });

            if (congregationSelect) {
                congregationSelect.addEventListener('change', function () {
                    state.congregation = congregationSelect.value;
                    applyFilters();
                });
            }

            if (todayToggle) {
                todayToggle.addEventListener('click', function () {
                    state.today = todayToggle.classList.toggle('is-active');
                    todayToggle.setAttribute('aria-pressed', state.today ? 'true' : 'false');
                    applyFilters();
                });
            }

            congregationPills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    var name = pill.getAttribute('data-congregation-toggle');
                    var isOff = pill.classList.toggle('is-off');
                    pill.classList.toggle('is-active', !isOff);
                    mapPins.forEach(function (pin) {
                        if (pin.getAttribute('data-map-pin') === name) {
                            pin.classList.toggle('is-off', isOff);
                        }
                    });
                    congregationBlocks.forEach(function (block) {
                        if (block.getAttribute('data-congregation-block') === name) {
                            block.classList.toggle('is-off', isOff);
                        }
                    });
                });
            });

            applyFilters();
        })();
    </script>
</body>
</html>

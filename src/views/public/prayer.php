<?php
$siteProfile = getChurchSiteProfileSettings();
$amenedLookup = array_fill_keys(array_map('intval', $amenedIds ?? []), true);
$totalRequests = (int)($stats['total_requests'] ?? 0);
$totalAmens = (int)($stats['total_amens'] ?? 0);
$currentPage = max(1, (int)($currentPage ?? 1));
$totalPages = max(1, (int)($totalPages ?? 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mural de Oração - <?= htmlspecialchars(getChurchBrandingName($siteProfile)) ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --prayer-wine: #8b1538;
            --prayer-wine-dark: #5a1026;
            --prayer-gold: #d4af37;
            --prayer-ink: #2d1a21;
            --prayer-muted: #6b7280;
            --prayer-soft: #fff6f8;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--prayer-ink);
            background:
                radial-gradient(circle at top left, rgba(212, 175, 55, 0.14), transparent 28%),
                linear-gradient(180deg, #fffdfd 0%, #fff7f9 100%);
        }

        .simple-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }

        .header-container {
            min-height: 74px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            color: var(--prayer-wine);
            text-decoration: none;
            font-weight: 800;
            font-size: 1.2rem;
        }

        .brand-logo img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .hero-prayer {
            padding: 4.5rem 0 2.2rem;
        }

        .hero-shell {
            border-radius: 28px;
            padding: 2.4rem;
            background: linear-gradient(135deg, rgba(139,21,56,0.96), rgba(90,16,38,0.94));
            color: #fff;
            box-shadow: 0 28px 60px rgba(90,16,38,0.22);
            overflow: hidden;
            position: relative;
        }

        .hero-shell::after {
            content: "";
            position: absolute;
            inset: auto -40px -40px auto;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }

        .hero-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            line-height: 1.08;
            margin-bottom: .9rem;
        }

        .hero-copy {
            max-width: 40rem;
            color: rgba(255,255,255,0.88);
            font-size: 1.02rem;
            margin-bottom: 0;
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

        .prayer-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .9rem;
        }

        .prayer-stat-card {
            border-radius: 22px;
            padding: 1rem 1.05rem;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .prayer-stat-card strong {
            display: block;
            font-size: 1.7rem;
            line-height: 1;
            margin-bottom: .38rem;
        }

        .prayer-stat-card span {
            color: rgba(255,255,255,0.82);
            font-size: .92rem;
        }

        .content-section {
            padding: 0 0 4.5rem;
        }

        .prayer-panel,
        .request-card {
            border-radius: 24px;
            background: rgba(255,255,255,0.94);
            border: 1px solid rgba(139,21,56,0.08);
            box-shadow: 0 18px 45px rgba(49,24,31,0.08);
        }

        .prayer-panel {
            padding: 1.4rem;
            position: sticky;
            top: 96px;
        }

        .panel-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--prayer-wine-dark);
            margin-bottom: .4rem;
        }

        .panel-subtitle {
            color: var(--prayer-muted);
            font-size: .95rem;
            margin-bottom: 1.2rem;
        }

        .field-label {
            font-size: .86rem;
            font-weight: 700;
            color: var(--prayer-wine-dark);
            margin-bottom: .45rem;
        }

        .form-control,
        .form-check-input {
            border-radius: 14px;
        }

        .form-control {
            border-color: rgba(139,21,56,0.14);
            padding: .82rem .95rem;
        }

        textarea.form-control {
            min-height: 165px;
            resize: vertical;
        }

        .helper-card {
            margin-top: 1rem;
            padding: .95rem 1rem;
            border-radius: 18px;
            background: var(--prayer-soft);
            color: var(--prayer-muted);
            font-size: .88rem;
            border: 1px solid rgba(139,21,56,0.06);
        }

        .request-card {
            padding: 1.25rem;
        }

        .request-card + .request-card {
            margin-top: 1rem;
        }

        .request-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: .95rem;
        }

        .request-author {
            display: flex;
            align-items: center;
            gap: .8rem;
        }

        .request-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(139,21,56,0.14), rgba(212,175,55,0.24));
            color: var(--prayer-wine);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 50px;
            font-size: 1.1rem;
            font-weight: 800;
            border: 1px solid rgba(139,21,56,0.08);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
        }

        .request-avatar i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
            font-size: 1.35rem;
        }

        .request-avatar.is-anonymous {
            background: linear-gradient(135deg, rgba(45,26,33,0.08), rgba(139,21,56,0.12));
            color: #6a4250;
            font-size: 1rem;
        }

        .request-author strong {
            display: block;
            font-size: 1rem;
            color: var(--prayer-ink);
        }

        .request-author span {
            display: block;
            color: var(--prayer-muted);
            font-size: .84rem;
        }

        .request-message {
            font-size: 1rem;
            line-height: 1.8;
            color: #39242c;
            margin-bottom: 1rem;
        }

        .request-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .75rem;
        }

        .amen-form {
            margin: 0;
        }

        .amen-button {
            border: none;
            border-radius: 999px;
            padding: .72rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            font-weight: 700;
            color: var(--prayer-wine);
            background: rgba(139,21,56,0.08);
            transition: transform .16s ease, background .16s ease, color .16s ease;
        }

        .amen-button:hover:not(:disabled) {
            transform: translateY(-1px);
            background: rgba(139,21,56,0.12);
        }

        .amen-button.is-checked,
        .amen-button:disabled {
            color: #fff;
            background: linear-gradient(135deg, var(--prayer-wine), #bd2e60);
            opacity: 1;
        }

        .amen-count {
            padding: .2rem .48rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.16);
            font-size: .78rem;
        }

        .moderation-tools {
            margin-left: auto;
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
        }

        .prayer-pagination {
            margin-top: 1.8rem;
        }

        .prayer-pagination .page-link {
            color: var(--prayer-wine);
            border-color: rgba(139,21,56,0.16);
            padding: .65rem .95rem;
            border-radius: 999px;
        }

        .prayer-pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--prayer-wine), var(--prayer-wine-dark));
            border-color: transparent;
            color: #fff;
            box-shadow: 0 12px 24px rgba(90,16,38,0.16);
        }

        .prayer-pagination .page-item.disabled .page-link {
            color: #9ca3af;
        }

        .moderation-tools details {
            border-radius: 16px;
            background: #fff8fa;
            border: 1px solid rgba(139,21,56,0.08);
            padding: .7rem .85rem;
            width: 100%;
        }

        .moderation-tools summary {
            cursor: pointer;
            list-style: none;
            font-weight: 700;
            color: var(--prayer-wine-dark);
        }

        .moderation-tools summary::-webkit-details-marker {
            display: none;
        }

        .moderation-inline-form {
            margin-top: .85rem;
            display: grid;
            gap: .8rem;
        }

        .moderation-inline-form textarea {
            min-height: 120px;
        }

        .flash-wrap {
            margin-bottom: 1rem;
        }

        .empty-state {
            border-radius: 24px;
            padding: 2.2rem 1.4rem;
            text-align: center;
            background: rgba(255,255,255,0.84);
            border: 1px dashed rgba(139,21,56,0.16);
            color: var(--prayer-muted);
        }

        @media (max-width: 991.98px) {
            .hero-prayer {
                padding-top: 3.2rem;
            }

            .hero-shell {
                padding: 1.8rem;
            }

            .prayer-panel {
                position: static;
                margin-bottom: 1.2rem;
            }
        }

        @media (max-width: 575.98px) {
            .header-container {
                min-height: 68px;
            }

            .hero-shell {
                border-radius: 24px;
                padding: 1.4rem;
            }

            .prayer-stat-grid {
                grid-template-columns: 1fr;
            }

            .request-head {
                flex-direction: column;
            }

            .request-actions {
                align-items: stretch;
            }

            .moderation-tools {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="simple-header">
        <div class="container header-container">
            <a href="/" class="brand-logo">
                <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?>">
                <?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?>
            </a>
            <a href="/" class="btn btn-outline-dark rounded-pill px-3">
                <i class="fas fa-arrow-left me-2"></i>Voltar ao Início
            </a>
        </div>
    </header>

    <section class="hero-prayer">
        <div class="container">
            <div class="hero-shell">
                <div class="row g-4 align-items-end">
                    <div class="col-lg-8">
                        <span class="hero-badge"><i class="fas fa-hands-praying"></i> Mural de Oração</span>
                        <h1 class="hero-title">Compartilhe seu pedido e permita que a igreja ore com você.</h1>
                        <p class="hero-copy">Compartilhe seu pedido de oração e permita que a igreja caminhe em fé com você. Se preferir, você também pode enviar de forma anônima e receber o carinho da comunidade em oração.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="prayer-stat-grid">
                            <div class="prayer-stat-card">
                                <strong><?= $totalRequests ?></strong>
                                <span>Pedidos publicados</span>
                            </div>
                            <div class="prayer-stat-card">
                                <strong><?= $totalAmens ?></strong>
                                <span>Améns registrados</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-4">
                    <div class="prayer-panel" id="pedido-form">
                        <h2 class="panel-title">Enviar Pedido</h2>
                        <p class="panel-subtitle">Escreva com calma. Seu pedido pode aparecer com seu nome ou de forma anônima.</p>

                        <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
                            <div class="flash-wrap">
                                <?php if (isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    <?php unset($_SESSION['success']); ?>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-circle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    <?php unset($_SESSION['error']); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form action="/oracao" method="POST" class="row g-3">
                            <?= csrf_field() ?>
                            <div class="col-12">
                                <label for="prayerName" class="field-label">Seu nome</label>
                                <input type="text" class="form-control" id="prayerName" name="name" maxlength="120" placeholder="Ex.: Maria Souza">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="prayerAnonymous" name="is_anonymous">
                                    <label class="form-check-label" for="prayerAnonymous">
                                        Publicar como anônimo
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="prayerMessage" class="field-label">Pedido de oração</label>
                                <textarea class="form-control" id="prayerMessage" name="message" maxlength="3000" placeholder="Escreva aqui o seu pedido..."></textarea>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-danger btn-lg rounded-pill">
                                    <i class="fas fa-paper-plane me-2"></i>Enviar pedido
                                </button>
                            </div>
                        </form>

                        <div class="helper-card">
                            <strong class="d-block mb-1">Como funciona?</strong>
                            Os pedidos entram no mural e a comunidade pode marcar <strong>Amém</strong> como sinal de fé, apoio e intercessão.
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3" id="mural">
                        <div>
                            <h2 class="panel-title mb-1">Pedidos de Oração</h2>
                            <p class="panel-subtitle mb-0">Ore, acompanhe e responda com uma mãozinha de Amém.</p>
                        </div>
                    </div>

                    <?php if (empty($requests)): ?>
                        <div class="empty-state">
                            <i class="fas fa-dove fa-3x mb-3 text-secondary"></i>
                            <h3 class="h4 text-dark">Nenhum pedido publicado ainda.</h3>
                            <p class="mb-0">Seja a primeira pessoa a compartilhar um pedido de oração neste mural.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <?php
                                $requestId = (int)($request['id'] ?? 0);
                                $isAnonymous = !empty($request['is_anonymous']);
                                $displayName = $isAnonymous ? 'Anônimo' : trim((string)($request['name'] ?? ''));
                                if ($displayName === '') {
                                    $displayName = 'Anônimo';
                                }
                                $avatarClass = $isAnonymous ? 'request-avatar is-anonymous' : 'request-avatar';
                                $hasAmened = isset($amenedLookup[$requestId]);
                                $createdAt = !empty($request['created_at']) ? strtotime($request['created_at']) : false;
                            ?>
                            <article class="request-card">
                                <div class="request-head">
                                    <div class="request-author">
                                        <span class="<?= $avatarClass ?>">
                                            <?php if ($isAnonymous): ?>
                                                <i class="fas fa-user-secret"></i>
                                            <?php else: ?>
                                                <i class="fas fa-hands-praying"></i>
                                            <?php endif; ?>
                                        </span>
                                        <div>
                                            <strong><?= htmlspecialchars($displayName) ?></strong>
                                            <span>
                                                <i class="far fa-clock me-1"></i>
                                                <?= $createdAt ? date('d/m/Y', $createdAt) . ' às ' . date('H:i', $createdAt) : 'Agora há pouco' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="request-message"><?= nl2br(htmlspecialchars((string)($request['message'] ?? ''))) ?></div>

                                <div class="request-actions">
                                    <form action="/oracao/amem/<?= $requestId ?>" method="POST" class="amen-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="page" value="<?= $currentPage ?>">
                                        <button type="submit" class="amen-button<?= $hasAmened ? ' is-checked' : '' ?>"<?= $hasAmened ? ' disabled' : '' ?>>
                                            <i class="fas fa-hands-praying"></i>
                                            <span><?= $hasAmened ? 'Você disse Amém' : 'Dizer Amém' ?></span>
                                            <span class="amen-count"><?= (int)($request['amen_count'] ?? 0) ?></span>
                                        </button>
                                    </form>

                                    <?php if ($canModerate): ?>
                                        <div class="moderation-tools">
                                            <details>
                                                <summary><i class="fas fa-pen-to-square me-2"></i>Editar pedido</summary>
                                                <form action="/oracao/editar/<?= $requestId ?>" method="POST" class="moderation-inline-form">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="page" value="<?= $currentPage ?>">
                                                    <div>
                                                        <label class="field-label">Nome</label>
                                                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars((string)($request['name'] ?? '')) ?>" maxlength="120">
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="1" id="anonymous-<?= $requestId ?>" name="is_anonymous"<?= $isAnonymous ? ' checked' : '' ?>>
                                                        <label class="form-check-label" for="anonymous-<?= $requestId ?>">Publicar como anônimo</label>
                                                    </div>
                                                    <div>
                                                        <label class="field-label">Mensagem</label>
                                                        <textarea class="form-control" name="message" maxlength="3000"><?= htmlspecialchars((string)($request['message'] ?? '')) ?></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-dark rounded-pill">Salvar alterações</button>
                                                </form>
                                            </details>

                                            <form action="/oracao/excluir/<?= $requestId ?>" method="POST" onsubmit="return confirm('Deseja realmente excluir este pedido?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="page" value="<?= $currentPage ?>">
                                                <button type="submit" class="btn btn-outline-danger rounded-pill">
                                                    <i class="fas fa-trash-can me-2"></i>Excluir
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                        <?php if ($totalPages > 1): ?>
                            <nav class="prayer-pagination" aria-label="Paginação do mural de oração">
                                <ul class="pagination justify-content-center flex-wrap gap-2 mb-0">
                                    <li class="page-item<?= $currentPage <= 1 ? ' disabled' : '' ?>">
                                        <a class="page-link" href="/oracao?page=<?= max(1, $currentPage - 1) ?>#mural" aria-label="Página anterior">Anterior</a>
                                    </li>
                                    <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                                        <li class="page-item<?= $page === $currentPage ? ' active' : '' ?>">
                                            <a class="page-link" href="/oracao?page=<?= $page ?>#mural"><?= $page ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item<?= $currentPage >= $totalPages ? ' disabled' : '' ?>">
                                        <a class="page-link" href="/oracao?page=<?= min($totalPages, $currentPage + 1) ?>#mural" aria-label="Próxima página">Próxima</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

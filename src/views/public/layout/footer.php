    <?php $siteProfile = getChurchSiteProfileSettings(); ?>
    <!-- Footer -->
    <footer id="contato" class="footer text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?></h5>
                    <p>Levando a palavra de Deus e transformando vidas através do amor de Cristo.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contato</h5>
                    <p><i class="fas fa-phone me-2 text-gold"></i> <?= htmlspecialchars($siteProfile['phone']) ?><br>
                    <i class="fas fa-envelope me-2 text-gold"></i> <?= htmlspecialchars($siteProfile['email']) ?></p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Redes Sociais</h5>
                    <div class="d-flex justify-content-center gap-3">
                        <?php foreach ($siteProfile['social_links'] as $social): ?>
                            <a href="<?= htmlspecialchars($social['url']) ?>" class="social-icon" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($social['label']) ?>">
                                <i class="<?= htmlspecialchars($social['icon']) ?> fa-fw" style="font-size: 2rem;"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <p class="mb-0 small text-gold">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?>. Todos os direitos reservados.</p>
        </div>
    </footer>
    <a href="/harpa" class="harpa-fab" aria-label="Abrir Harpa Cristã" title="Harpa Cristã">
        <i class="fas fa-music"></i>
        <span class="label">Harpa Cristã</span>
    </a>
    <style>
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

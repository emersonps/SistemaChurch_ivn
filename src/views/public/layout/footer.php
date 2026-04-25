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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JSON-LD Structured Data -->
    <?php if (isset($jsonld)): ?>
    <script type="application/ld+json">
    <?= json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
    <?php endif; ?>
</body>
</html>

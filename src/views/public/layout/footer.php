<?php
$siteProfile = getChurchSiteProfileSettings();
$footerNavItems = [
    ['label' => 'Início', 'url' => '/'],
    ['label' => 'Cultos', 'url' => '/cultos'],
    ['label' => 'Convites', 'url' => '/#convites'],
    ['label' => 'Congregações', 'url' => '/#congregacoes'],
    ['label' => 'Galeria', 'url' => '/galeria'],
];
?>
<style>
    .site-footer {
        background: #15151a;
        color: rgba(255,255,255,0.82);
        position: relative;
        padding: 3rem 0 1.5rem;
    }
    .site-footer::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-gold), var(--primary-red));
    }
    .footer-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr 1fr 1.3fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 991.98px) {
        .footer-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 575.98px) {
        .footer-grid { grid-template-columns: 1fr; }
    }
    .footer-brand {
        display: flex;
        align-items: center;
        gap: .45rem;
        font-size: 1.4rem;
        font-weight: 900;
        color: #fff;
        margin-bottom: .9rem;
    }
    .footer-brand .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary-gold);
    }
    .footer-tagline {
        font-size: .88rem;
        line-height: 1.55;
        margin-bottom: 1.1rem;
        color: rgba(255,255,255,0.6);
        max-width: 26rem;
    }
    .footer-since-badge {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 999px;
        padding: .4rem .9rem;
        font-size: .8rem;
    }
    .footer-since-badge .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
    }
    .footer-col-title {
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.45);
        margin-bottom: 1.1rem;
    }
    .footer-nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: .7rem;
    }
    .footer-nav-list a {
        color: rgba(255,255,255,0.85);
        text-decoration: none;
        font-weight: 600;
        font-size: .92rem;
    }
    .footer-nav-list a:hover {
        color: var(--primary-gold);
    }
    .footer-contact-item {
        display: flex;
        align-items: center;
        gap: .7rem;
        margin-bottom: 1rem;
        font-size: .88rem;
        color: rgba(255,255,255,0.85);
    }
    .footer-contact-item:last-child {
        margin-bottom: 0;
    }
    .footer-contact-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.7);
        flex-shrink: 0;
    }
    .footer-newsletter-form {
        display: flex;
        gap: .5rem;
        margin-bottom: 1.5rem;
    }
    .footer-newsletter-input {
        flex: 1;
        min-width: 0;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        padding: .6rem .9rem;
        color: #fff;
        font-size: .86rem;
    }
    .footer-newsletter-input::placeholder {
        color: rgba(255,255,255,0.4);
    }
    .footer-newsletter-input:focus {
        outline: none;
        border-color: var(--primary-gold);
    }
    .footer-newsletter-btn {
        background: linear-gradient(135deg, var(--primary-gold), var(--primary-red));
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: .6rem 1.2rem;
        font-weight: 700;
        font-size: .86rem;
        white-space: nowrap;
    }
    .footer-social-row {
        display: flex;
        gap: .6rem;
    }
    .footer-social-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.85);
        transition: background .15s ease, color .15s ease;
    }
    .footer-social-icon:hover {
        background: rgba(255,255,255,0.14);
        color: var(--primary-gold);
    }
    .footer-divider {
        border: none;
        border-top: 1px solid rgba(255,255,255,0.08);
        margin: 0 0 1.25rem;
    }
    .footer-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        font-size: .82rem;
        color: rgba(255,255,255,0.5);
    }
    .footer-bottom-links {
        display: flex;
        gap: 1.2rem;
    }
    .footer-bottom-links a {
        color: rgba(255,255,255,0.5);
        text-decoration: none;
    }
    .footer-bottom-links a:hover {
        color: rgba(255,255,255,0.8);
    }
    .footer-bottom-made {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }
    .footer-bottom-made i {
        color: var(--primary-red);
    }
</style>

<footer id="contato" class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">
                    <?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?>
                    <span class="dot"></span>
                </div>
                <p class="footer-tagline">Levando a palavra de Deus e transformando vidas através do amor de Cristo.</p>
                <?php if (!empty($siteProfile['founded_year'])): ?>
                    <span class="footer-since-badge"><span class="dot"></span> Desde <?= htmlspecialchars($siteProfile['founded_year']) ?></span>
                <?php endif; ?>
            </div>
            <div>
                <div class="footer-col-title">Navegação</div>
                <ul class="footer-nav-list">
                    <?php foreach ($footerNavItems as $item): ?>
                        <li><a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <div class="footer-col-title">Contato</div>
                <?php if (!empty($siteProfile['phone'])): ?>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon"><i class="fas fa-phone"></i></span>
                        <?= htmlspecialchars($siteProfile['phone']) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($siteProfile['email'])): ?>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon"><i class="fas fa-envelope"></i></span>
                        <?= htmlspecialchars($siteProfile['email']) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <div class="footer-col-title">Newsletter</div>
                <form class="footer-newsletter-form" onsubmit="return false;">
                    <input type="email" class="footer-newsletter-input" placeholder="Receba novidades">
                    <button type="submit" class="footer-newsletter-btn">Enviar</button>
                </form>
                <?php if (!empty($siteProfile['social_links'])): ?>
                    <div class="footer-col-title">Redes Sociais</div>
                    <div class="footer-social-row">
                        <?php foreach ($siteProfile['social_links'] as $social): ?>
                            <a href="<?= htmlspecialchars($social['url']) ?>" class="footer-social-icon" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($social['label']) ?>">
                                <i class="<?= htmlspecialchars($social['icon']) ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja') ?> Todos os direitos reservados.</span>
            <div class="footer-bottom-links">
                <a href="#">Política de Privacidade</a>
                <a href="#">Termos</a>
            </div>
            <span class="footer-bottom-made">Feito com <i class="fas fa-heart"></i> para o Reino</span>
        </div>
    </div>
</footer>
<?php if (empty($skipFloatingFaithWidget)) { include __DIR__ . '/../partials/floating_faith_widget.php'; } ?>

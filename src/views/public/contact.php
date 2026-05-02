<?php
$siteProfile = getChurchSiteProfileSettings();
$brand = getChurchBrandingName($siteProfile);
$alias = getChurchBrandingAlias($siteProfile);
$logoUrl = getChurchLogoUrl($siteProfile, true);
$whatsPhone = '5592994791168';
$whatsBase = 'https://wa.me/' . $whatsPhone;
$whatsText = rawurlencode('Olá! Solicitação de informações para implantação do sistema na igreja. Como funciona (domínio, hospedagem, suporte, atualizações e treinamento)?');
$whatsUrl = $whatsBase . '?text=' . $whatsText;
$testimonials = [
    [
        'name' => 'Pr. João (Pastor)',
        'org' => 'Igreja local',
        'quote' => 'Antes era tudo no caderno e no WhatsApp. Agora a secretaria tem uma rotina clara, os eventos ficam organizados e a igreja consegue acompanhar melhor.',
    ],
    [
        'name' => 'Ana (Secretaria)',
        'org' => 'Secretaria',
        'quote' => 'O que mais ajudou foi a agilidade: cadastro, aniversariantes, comunicados e agenda. A gente economiza tempo e reduz erro.',
    ],
    [
        'name' => 'Carlos (Tesouraria)',
        'org' => 'Tesouraria',
        'quote' => 'Ficou mais simples prestar contas e registrar tudo. A organização melhora e a liderança toma decisão com mais tranquilidade.',
    ],
    [
        'name' => 'Marcos (Líder de Departamento)',
        'org' => 'Departamento',
        'quote' => 'Os eventos ficaram bem mais fáceis de divulgar e acompanhar. Cada congregação enxerga o que precisa e a comunicação flui.',
    ],
    [
        'name' => 'Juliana (Membro)',
        'org' => 'Membro',
        'quote' => 'A experiência do site ficou bem bonita. Oração e devocional aproximam e deixam a igreja mais conectada.',
    ],
    [
        'name' => 'Pr. Paulo (Pastor)',
        'org' => 'Igreja local',
        'quote' => 'O sistema ajudou na organização e no acompanhamento. O melhor é ver a equipe trabalhando com mais segurança e padrão.',
    ],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato e Informações - <?= htmlspecialchars($brand) ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --contact-bg: #070815;
            --contact-panel: rgba(255,255,255,0.08);
            --contact-panel-border: rgba(255,255,255,0.14);
            --contact-ink: rgba(255,255,255,0.92);
            --contact-muted: rgba(255,255,255,0.72);
            --contact-soft: rgba(255,255,255,0.06);
            --contact-accent: #d4af37;
            --contact-accent-2: #ff2a7a;
            --contact-wine: #8b1538;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--contact-ink);
            background:
                radial-gradient(circle at 14% 18%, rgba(255, 42, 122, 0.22), transparent 38%),
                radial-gradient(circle at 84% 22%, rgba(212, 175, 55, 0.22), transparent 40%),
                radial-gradient(circle at 52% 120%, rgba(139, 21, 56, 0.26), transparent 44%),
                linear-gradient(135deg, #070815 0%, #0c1224 45%, #070815 100%);
            background-size: 200% 200%;
            animation: contactBgShift 14s ease infinite;
            min-height: 100vh;
        }

        @keyframes contactBgShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(7, 8, 21, 0.62);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .topbar-inner {
            min-height: 74px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .topbar-actions {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            padding: .35rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            box-shadow: 0 18px 40px rgba(0,0,0,0.32);
        }

        .topbar-actions .btn {
            font-weight: 700;
            border-radius: 999px;
            border-color: rgba(255,255,255,0.14);
            color: rgba(255,255,255,0.88);
        }

        .topbar-actions .btn:hover {
            background: rgba(255,255,255,0.10);
            border-color: rgba(255,255,255,0.22);
            color: #fff;
        }

        .hero {
            padding: 3.3rem 0 1.9rem;
        }

        .hero-shell {
            border-radius: 28px;
            padding: 2.35rem;
            background: linear-gradient(135deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04));
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow: 0 34px 80px rgba(0,0,0,0.42);
            position: relative;
            overflow: hidden;
        }

        .hero-shell::after {
            content: "";
            position: absolute;
            inset: auto -46px -46px auto;
            width: 210px;
            height: 210px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.16), rgba(255,255,255,0.0) 70%);
        }

        .hero-shell::before {
            content: "";
            position: absolute;
            inset: -2px;
            border-radius: 30px;
            background: linear-gradient(120deg, rgba(255,42,122,0.40), rgba(212,175,55,0.40), rgba(255,255,255,0.22));
            filter: blur(18px);
            opacity: 0.55;
            z-index: -1;
            animation: heroGlow 6.5s ease-in-out infinite;
        }

        @keyframes heroGlow {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); opacity: 0.45; }
            50% { transform: translate3d(0, -10px, 0) scale(1.05); opacity: 0.65; }
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .42rem .8rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            color: rgba(255,255,255,0.92);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 1rem;
        }

        .hero-title {
            font-size: clamp(2rem, 4vw, 3.1rem);
            font-weight: 800;
            line-height: 1.08;
            margin-bottom: .85rem;
        }

        .hero-copy {
            color: rgba(255,255,255,0.76);
            max-width: 46rem;
            font-size: 1.02rem;
            margin-bottom: 0;
        }

        .panel {
            border-radius: 24px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 18px 45px rgba(0,0,0,0.30);
            backdrop-filter: blur(10px);
        }

        .panel-head {
            padding: 1.35rem 1.35rem 0;
        }

        .panel-body {
            padding: 1.25rem 1.35rem 1.45rem;
        }

        .section-title {
            font-weight: 800;
            color: rgba(255,255,255,0.92);
            margin: 0;
        }

        .section-subtitle {
            color: rgba(255,255,255,0.68);
            margin-top: .35rem;
            margin-bottom: 0;
        }

        .step-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .95rem;
        }

        .step-card {
            border-radius: 20px;
            padding: 1.05rem 1.05rem 1.1rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }

        .step-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.20);
            box-shadow: 0 22px 50px rgba(0,0,0,0.34);
        }

        .step-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(212,175,55,0.14);
            color: rgba(255,255,255,0.92);
            margin-bottom: .75rem;
        }

        .step-card h3 {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: .35rem;
        }

        .step-card p {
            margin: 0;
            color: rgba(255,255,255,0.72);
            font-size: .92rem;
            line-height: 1.5;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .95rem;
        }

        .feature-card {
            border-radius: 20px;
            padding: 1.05rem;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
            transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease;
        }

        .feature-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.20);
            box-shadow: 0 22px 50px rgba(0,0,0,0.34);
        }

        .feature-card strong {
            display: block;
            font-weight: 800;
            color: rgba(255,255,255,0.92);
            margin-bottom: .25rem;
        }

        .feature-card span {
            color: rgba(255,255,255,0.72);
            font-size: .92rem;
            line-height: 1.5;
            display: block;
        }

        .cta-card {
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04));
            border: 1px solid rgba(255,255,255,0.14);
            padding: 1.35rem;
        }

        .cta-title {
            font-weight: 900;
            color: rgba(255,255,255,0.92);
            margin-bottom: .35rem;
        }

        .cta-copy {
            color: rgba(255,255,255,0.72);
            margin-bottom: 1rem;
        }

        .faq .accordion-button {
            font-weight: 800;
        }

        .faq .accordion-item {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .faq .accordion-button {
            background: transparent;
            color: rgba(255,255,255,0.92);
        }

        .faq .accordion-button:not(.collapsed) {
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.98);
        }

        .faq .accordion-body {
            color: rgba(255,255,255,0.72);
        }

        .btn-cta {
            position: relative;
            overflow: hidden;
            border: 0;
            background: linear-gradient(135deg, rgba(255,42,122,1) 0%, rgba(212,175,55,1) 100%);
            color: #090a15;
        }

        .btn-cta::after {
            content: "";
            position: absolute;
            top: -30%;
            left: -30%;
            width: 60%;
            height: 160%;
            background: rgba(255,255,255,0.35);
            transform: rotate(25deg) translateX(-140%);
            animation: ctaShimmer 3.2s ease-in-out infinite;
        }

        @keyframes ctaShimmer {
            0% { transform: rotate(25deg) translateX(-140%); opacity: 0; }
            12% { opacity: 0.35; }
            28% { transform: rotate(25deg) translateX(260%); opacity: 0; }
            100% { transform: rotate(25deg) translateX(260%); opacity: 0; }
        }

        .social-proof {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            align-items: center;
            margin-top: 1.1rem;
        }

        .proof-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .75rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.78);
            font-weight: 700;
            font-size: .9rem;
        }

        .testimonials-track {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .95rem;
        }

        .testimonial-card {
            border-radius: 22px;
            padding: 1.1rem 1.1rem 1.15rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            position: relative;
            overflow: hidden;
            transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.20);
            box-shadow: 0 22px 50px rgba(0,0,0,0.34);
        }

        .testimonial-quote {
            color: rgba(255,255,255,0.80);
            line-height: 1.65;
            font-size: .95rem;
            margin-bottom: .95rem;
        }

        .testimonial-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }

        .testimonial-person strong {
            display: block;
            color: rgba(255,255,255,0.92);
            font-weight: 900;
            font-size: .95rem;
        }

        .testimonial-person span {
            display: block;
            color: rgba(255,255,255,0.62);
            font-size: .86rem;
        }

        .stars {
            color: var(--contact-accent);
            font-size: .9rem;
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .hero-shell {
                padding: 1.75rem;
            }

            .step-grid {
                grid-template-columns: 1fr;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .testimonials-track {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <div class="topbar-actions">
                <a href="/oracao" class="btn btn-cta btn-sm"><i class="fas fa-hands-praying me-2"></i>Oração</a>
                <a href="/devocional" class="btn btn-cta btn-sm"><i class="fas fa-book-bible me-2"></i>Devocional</a>
                <a href="/" class="btn btn-outline-dark btn-sm"><i class="fas fa-house me-2"></i>Início</a>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-shell">
                <span class="hero-badge"><i class="fas fa-circle-info"></i> Informações e Contato</span>
                <h1 class="hero-title">Quer um sistema como esse para sua igreja?</h1>
                <p class="hero-copy">
                    Este site faz parte de um sistema completo para igrejas: site público, área administrativa e portal do membro.
                    Abaixo você vê como funciona e como entrar em contato para implantar na sua igreja, com suporte completo: domínio, hospedagem, atualizações e treinamento.
                </p>
                <div class="social-proof" aria-label="Prova social">
                    <span class="proof-pill"><i class="fas fa-bolt"></i> Mais organização</span>
                    <span class="proof-pill"><i class="fas fa-clock"></i> Mais agilidade</span>
                    <span class="proof-pill"><i class="fas fa-people-group"></i> Mais conexão</span>
                </div>
                <div class="mt-4 d-flex flex-wrap gap-2">
                    <?php if ($whatsUrl !== ''): ?>
                        <a href="<?= htmlspecialchars($whatsUrl) ?>" class="btn btn-cta btn-lg rounded-pill fw-bold" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-whatsapp me-2"></i>Quero implementar na minha igreja
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <main class="pb-5">
        <div class="container">
            <div class="panel mb-4">
                <div class="panel-head">
                    <h2 class="section-title">Como funciona</h2>
                    <p class="section-subtitle">Um resumo do processo para sua igreja ter o sistema no ar.</p>
                </div>
                <div class="panel-body">
                    <div class="step-grid">
                        <div class="step-card">
                            <div class="step-icon"><i class="fas fa-comments"></i></div>
                            <h3>1) Conversa e alinhamento</h3>
                            <p>Entendemos a realidade da sua igreja, definimos o que entra no pacote e combinamos o melhor formato de implantação.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-icon"><i class="fas fa-palette"></i></div>
                            <h3>2) Identidade e dados</h3>
                            <p>Aplicamos logo, nome, cores e informações públicas. Seus dados ficam organizados e prontos para uso no dia a dia.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-icon"><i class="fas fa-rocket"></i></div>
                            <h3>3) Publicação e treinamento</h3>
                            <p>Publicamos no seu domínio e orientamos a secretaria/tesouraria para começar com segurança, sem travar a rotina da igreja.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel mb-4">
                <div class="panel-head">
                    <h2 class="section-title">O que você ganha</h2>
                    <p class="section-subtitle">Recursos pensados para facilitar e dar transparência.</p>
                </div>
                <div class="panel-body">
                    <div class="feature-grid">
                        <div class="feature-card">
                            <strong><i class="fas fa-globe me-2 text-warning"></i>Site público</strong>
                            <span>Home moderna com cultos, eventos, convites, congregações e páginas como Oração e Devocional.</span>
                        </div>
                        <div class="feature-card">
                            <strong><i class="fas fa-user-circle me-2 text-warning"></i>Portal do membro</strong>
                            <span>Área do membro para acompanhar informações importantes (conforme módulos habilitados na sua igreja).</span>
                        </div>
                        <div class="feature-card">
                            <strong><i class="fas fa-users me-2 text-warning"></i>Membros e congregações</strong>
                            <span>Cadastro completo, organização por congregação, aniversariantes, carteirinha e controle de presença em eventos.</span>
                        </div>
                        <div class="feature-card">
                            <strong><i class="fas fa-calendar-days me-2 text-warning"></i>Agenda e eventos</strong>
                            <span>Gestão da agenda com exibição no site e no painel. Ideal para manter tudo atualizado e fácil de achar.</span>
                        </div>
                        <div class="feature-card">
                            <strong><i class="fas fa-lock me-2 text-warning"></i>Segurança e acesso</strong>
                            <span>Controle de permissões por perfil, rotinas de autenticação e boas práticas para o uso no dia a dia.</span>
                        </div>
                        <div class="feature-card">
                            <strong><i class="fas fa-wand-magic-sparkles me-2 text-warning"></i>Personalização</strong>
                            <span>Layout e textos adaptados para sua igreja, mantendo uma experiência bonita e consistente.</span>
                        </div>
                        <div class="feature-card">
                            <strong><i class="fas fa-headset me-2 text-warning"></i>Suporte completo</strong>
                            <span>Suporte completo e centralizado: domínio, hospedagem, atualizações e treinamento para a equipe da igreja usar com segurança.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cta-card mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-8">
                        <div class="cta-title">Vamos conversar sobre a sua igreja?</div>
                        <div class="cta-copy">Se você quer implantar esse sistema, a forma mais rápida é chamar no WhatsApp com o nome da igreja e sua cidade.</div>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <?php if ($whatsUrl !== ''): ?>
                            <a href="<?= htmlspecialchars($whatsUrl) ?>" class="btn btn-success btn-lg rounded-pill fw-bold w-100 w-lg-auto" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-whatsapp me-2"></i>Chamar no WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="panel mb-4" id="depoimentos">
                <div class="panel-head">
                    <h2 class="section-title">O que as igrejas dizem</h2>
                    <p class="section-subtitle">Alguns relatos reais do dia a dia: organização, agilidade e tranquilidade para a liderança.</p>
                </div>
                <div class="panel-body">
                    <div class="testimonials-track">
                        <?php foreach ($testimonials as $t): ?>
                            <div class="testimonial-card">
                                <div class="stars" aria-hidden="true">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="testimonial-quote">“<?= htmlspecialchars((string)($t['quote'] ?? '')) ?>”</div>
                                <div class="testimonial-meta">
                                    <div class="testimonial-person">
                                        <strong><?= htmlspecialchars((string)($t['name'] ?? '')) ?></strong>
                                        <span><?= htmlspecialchars((string)($t['org'] ?? '')) ?></span>
                                    </div>
                                    <i class="fas fa-quote-right" style="color: rgba(255,255,255,0.20); font-size: 1.35rem;"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="panel faq">
                <div class="panel-head">
                    <h2 class="section-title">Dúvidas rápidas</h2>
                    <p class="section-subtitle">Respostas objetivas para quem está conhecendo agora.</p>
                </div>
                <div class="panel-body">
                    <div class="accordion" id="faqContact">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqOneBody" aria-expanded="true" aria-controls="faqOneBody">
                                    O sistema funciona no celular?
                                </button>
                            </h2>
                            <div id="faqOneBody" class="accordion-collapse collapse show" aria-labelledby="faqOne" data-bs-parent="#faqContact">
                                <div class="accordion-body">
                                    Sim. O site público e as telas do sistema são responsivas e pensadas para uso no celular, inclusive para acesso do membro.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwoBody" aria-expanded="false" aria-controls="faqTwoBody">
                                    Precisa de domínio e hospedagem?
                                </button>
                            </h2>
                            <div id="faqTwoBody" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqContact">
                                <div class="accordion-body">
                                    Sim. O ideal é ter um domínio (ex.: suaigreja.com.br) e uma hospedagem estável. A implantação inclui indicação/contratação, configuração, apontamentos e publicação.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqThreeBody" aria-expanded="false" aria-controls="faqThreeBody">
                                    Como fica o suporte?
                                </button>
                            </h2>
                            <div id="faqThreeBody" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqContact">
                                <div class="accordion-body">
                                    Suporte completo e centralizado: hospedagem, domínio, atualizações e treinamento. Acompanhamento e orientação para o uso no dia a dia.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

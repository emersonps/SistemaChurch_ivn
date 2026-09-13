<?php
$dlBrand = getChurchBrandingName($siteProfile);
$dlLogoUrl = getChurchLogoUrl($siteProfile);
$dlPlanPrices = isset($planPrices) && is_array($planPrices) ? $planPrices : [];
$dlPlans = [
    'mensal' => ['label' => 'Mensal', 'price' => (float)($dlPlanPrices['mensal'] ?? 59.99), 'note' => 'Comece agora, sem compromisso de longo prazo.'],
    'trimestral' => ['label' => 'Trimestral', 'price' => (float)($dlPlanPrices['trimestral'] ?? 53.99), 'note' => 'Equilíbrio perfeito entre economia e flexibilidade.', 'highlight' => true],
    'anual' => ['label' => 'Anual', 'price' => (float)($dlPlanPrices['anual'] ?? 47.99), 'note' => 'Para igrejas que querem economia máxima.'],
];

// Etiqueta de desconto: no Mensal mostra o período promocional ativo (se
// houver); no Trimestral/Anual mostra quanto eles já economizam em relação
// ao preço do Mensal — mesmo sem trial, esses planos nascem mais baratos.
$dlPromo = isset($promo) && is_array($promo) ? $promo : [];
$dlTrial = isset($dlPromo['trial']) && is_array($dlPromo['trial']) ? $dlPromo['trial'] : [];
if (!empty($dlTrial['enabled']) && (float)($dlTrial['percent'] ?? 0) > 0) {
    $dlPlans['mensal']['trial_percent'] = (float)$dlTrial['percent'];
    $dlPlans['mensal']['trial_months'] = max(1, (int)($dlTrial['months'] ?? 1));
}
$dlMensalPrice = $dlPlans['mensal']['price'];
foreach ($dlPlans as $dlPlanKey => &$dlPlanRef) {
    if ($dlPlanKey === 'mensal' || $dlMensalPrice <= 0 || $dlPlanRef['price'] >= $dlMensalPrice) {
        continue;
    }
    $dlPlanRef['savings_percent'] = (int)round((1 - $dlPlanRef['price'] / $dlMensalPrice) * 100);
}
unset($dlPlanRef);

$dlAdesaoDiscount = isset($dlPromo['adesao_discount']) && is_array($dlPromo['adesao_discount']) ? $dlPromo['adesao_discount'] : [];
$dlAdesaoDiscountActive = !empty($dlAdesaoDiscount['enabled']) && (float)($dlAdesaoDiscount['amount'] ?? 0) > 0;

// Mapa do Brasil: extrai a UF de cada cliente (real ou fictício) que já
// tem localização, pra colorir no cartograma quais estados já têm alguma
// igreja usando o sistema. Layout em grade (não é um mapa geográfico real,
// é um cartograma simplificado) — cada UF tem uma posição fixa aproximada
// à sua posição real no mapa.
$dlStatesWithClients = [];
foreach ($clients as $dlClient) {
    if (empty($dlClient['location'])) {
        continue;
    }
    $dlLocParts = explode('/', $dlClient['location']);
    $dlState = strtoupper(trim(end($dlLocParts)));
    if (strlen($dlState) !== 2) {
        continue;
    }
    if (!isset($dlStatesWithClients[$dlState])) {
        $dlStatesWithClients[$dlState] = [];
    }
    $dlStatesWithClients[$dlState][] = $dlClient['sigla'];
}

$dlBrazilStates = [
    'RR' => ['Roraima', 0, 2], 'AP' => ['Amapá', 0, 4],
    'AM' => ['Amazonas', 1, 1], 'PA' => ['Pará', 1, 3], 'MA' => ['Maranhão', 1, 5], 'CE' => ['Ceará', 1, 6], 'RN' => ['Rio Grande do Norte', 1, 7],
    'AC' => ['Acre', 2, 0], 'RO' => ['Rondônia', 2, 1], 'TO' => ['Tocantins', 2, 4], 'PI' => ['Piauí', 2, 5], 'PE' => ['Pernambuco', 2, 6], 'PB' => ['Paraíba', 2, 7],
    'MT' => ['Mato Grosso', 3, 2], 'DF' => ['Distrito Federal', 3, 3], 'GO' => ['Goiás', 3, 4], 'BA' => ['Bahia', 3, 5], 'SE' => ['Sergipe', 3, 6], 'AL' => ['Alagoas', 3, 7],
    'MS' => ['Mato Grosso do Sul', 4, 2], 'MG' => ['Minas Gerais', 4, 4], 'ES' => ['Espírito Santo', 4, 5],
    'SP' => ['São Paulo', 5, 3], 'RJ' => ['Rio de Janeiro', 5, 4],
    'PR' => ['Paraná', 6, 3],
    'SC' => ['Santa Catarina', 7, 3],
    'RS' => ['Rio Grande do Sul', 8, 3],
];

// Mural de depoimentos — pastores fictícios das próprias igrejas fictícias
// da vitrine (mesma sigla, pra puxar o logo certo automaticamente).
$dlTestimonials = [
    ['name' => 'Pr. Marcos Andrade', 'church' => 'Igreja Batista Nova Vida', 'sigla' => 'IBNV', 'quote' => 'Depois que começamos a usar o sistema, a gestão financeira da igreja ficou muito mais transparente. Os dízimos e ofertas são registrados na hora, e o financeiro fecha o mês em minutos, não mais em dias.'],
    ['name' => 'Pra. Sandra Lima', 'church' => 'Comunidade Cristã Águas Vivas', 'sigla' => 'CCAV', 'quote' => 'O que mais gosto é a carteirinha digital dos membros e o controle de frequência. Facilitou muito o acompanhamento pastoral da nossa congregação.'],
    ['name' => 'Pr. Eliseu Fontes', 'church' => 'Igreja Presbiteriana Monte Sinai', 'sigla' => 'IPMS', 'quote' => 'Migramos de planilhas soltas pra um sistema completo em poucos dias. O suporte nos ajudou em cada etapa, e hoje não vivemos mais sem ele.'],
    ['name' => 'Pr. Ronaldo Vieira', 'church' => 'Igreja Metodista Renascer', 'sigla' => 'IMR', 'quote' => 'A área do tesoureiro mudou a forma como prestamos contas pra igreja. Tudo fica registrado, com relatório pronto pra apresentar em qualquer reunião.'],
    ['name' => 'Pra. Débora Nascimento', 'church' => 'Assembleia de Deus Shalom', 'sigla' => 'ADS', 'quote' => 'Os membros adoraram o portal deles — conseguem ver a agenda de cultos, os estudos e até a própria carteirinha pelo celular.'],
    ['name' => 'Pr. Anderson Melo', 'church' => 'Igreja Batista Getsêmani', 'sigla' => 'BGET', 'quote' => 'Antes gastávamos horas organizando informações dos membros. Hoje é tudo automático, e sobra mais tempo pra cuidar das pessoas.'],
    ['name' => 'Pr. Ivan Castro', 'church' => 'Comunidade Evangélica Vida Plena', 'sigla' => 'CEVP', 'quote' => 'Recomendo pra qualquer igreja que ainda usa papel e caneta. O sistema organiza tudo: membros, finanças e comunicação, num só lugar.'],
    ['name' => 'Secretária Juliana Rocha', 'church' => 'Igreja Metodista Renascer', 'sigla' => 'IMR', 'quote' => 'O que mais nos conquistou foi o suporte: rápido, direto ao ponto e sempre muito educado. O Emerson resolve qualquer dúvida na hora, com uma atenção que a gente raramente encontra em outros sistemas.'],
];

// Bot de dúvidas por palavras-chave — sem IA de verdade, só casamento de
// termos (sem custo por mensagem, sem chave de API). Preços/plano vêm dos
// mesmos $dlPlans já calculados acima, então a resposta nunca fica
// desatualizada em relação ao que a própria página mostra.
$dlAiPlanSummary = 'Mensal R$ ' . number_format($dlPlans['mensal']['price'], 2, ',', '.') . '/mês, Trimestral R$ '
    . number_format($dlPlans['trimestral']['price'], 2, ',', '.') . '/mês e Anual R$ '
    . number_format($dlPlans['anual']['price'], 2, ',', '.') . '/mês.';

$dlAiFaqs = [
    ['keywords' => ['oi', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'eae', 'e ai'], 'answer' => 'Olá! 👋 Posso te ajudar com informações sobre preços, planos, suporte ou como testar o sistema. O que você quer saber?'],
    ['keywords' => ['preco', 'valor', 'quanto custa', 'mensalidade', 'quanto e', 'quanto sai'], 'answer' => 'Nossos planos são: ' . $dlAiPlanSummary . ' Dá uma olhada na seção "Escolha como quer começar" logo abaixo pra ver todos os detalhes.'],
    ['keywords' => ['plano', 'planos', 'diferenca', 'qual escolher'], 'answer' => 'Temos 3 planos: Mensal, Trimestral e Anual — quanto maior o período, menor o valor mensal. Compare direto na seção de planos aqui na página.'],
    ['keywords' => ['funciona', 'recurso', 'funcionalidade', 'o que e', 'o que faz', 'pra que serve'], 'answer' => 'O sistema cuida de membros, finanças, cultos, grupos e muito mais — tudo num só lugar. A melhor forma de ver é explorando a demonstração: escolha um perfil (Administrador, Secretaria, Tesoureiro ou Membro) e entre direto, sem senha.'],
    ['keywords' => ['suporte', 'ajuda', 'atendimento'], 'answer' => 'Nosso suporte é rápido e feito por gente de verdade — vários clientes comentam sobre isso no mural de depoimentos aqui na página. Qualquer dúvida, é só chamar no WhatsApp.'],
    ['keywords' => ['contratar', 'adquirir', 'comprar', 'assinar', 'como faco', 'quero o sistema'], 'answer' => 'É simples: escolha um plano na seção "Escolha como quer começar" e preencha o formulário "Solicitar meu sistema". Você recebe contato em até 2h úteis pra colocar tudo no ar.'],
    ['keywords' => ['whatsapp', 'contato', 'falar com alguem', 'humano', 'pessoa', 'atendente'], 'answer' => 'Claro! Você pode falar direto com nossa equipe clicando no botão verde "Falar no WhatsApp" aqui na página.'],
    ['keywords' => ['teste', 'gratis', 'demonstracao', 'experimentar', 'trial'], 'answer' => 'Você já está na demonstração! Role até "Escolha um perfil e entre direto no sistema" e clique em qualquer card — sem senha, é instantâneo.'],
    ['keywords' => ['cancelar', 'fidelidade', 'contrato', 'multa'], 'answer' => 'Sem fidelidade nenhuma — você pode cancelar quando quiser, sem multa.'],
    ['keywords' => ['seguranca', 'senha', 'dados', 'privacidade'], 'answer' => 'No seu sistema real, cada perfil acessa só com usuário e senha pessoal, com dados protegidos. Aqui na demonstração liberamos o acesso direto só pra você explorar, com dados fictícios.'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dlBrand) ?> — Demonstração ao vivo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --dl-purple: #6d28d9; --dl-purple-light: #7c3aed; --dl-ink: #0f172a; }
        body { font-family: 'Inter', sans-serif; color: var(--dl-ink); background: #f8f9fc; }
        .dl-nav { background: #fff; border-bottom: 1px solid rgba(15,23,42,.06); }
        .dl-brand-icon { width: 2.2rem; height: 2.2rem; border-radius: .6rem; background: linear-gradient(135deg, var(--dl-purple), var(--dl-purple-light)); display: inline-flex; align-items: center; justify-content: center; color: #fff; }
        .dl-tag { font-size: .65rem; font-weight: 800; letter-spacing: .04em; background: rgba(124,58,237,.1); color: var(--dl-purple); padding: .25rem .55rem; border-radius: 999px; }
        .dl-hero { background: linear-gradient(180deg, #f3eeff 0%, #f8f9fc 55%); padding: 3.5rem 0 3rem; text-align: center; }
        .dl-live-pill { display: inline-flex; align-items: center; gap: .5rem; background: #fff; border: 1px solid rgba(15,23,42,.08); border-radius: 999px; padding: .5rem 1.1rem; font-size: .82rem; font-weight: 600; box-shadow: 0 .5rem 1.5rem rgba(15,23,42,.06); }
        .dl-live-dot { width: .5rem; height: .5rem; border-radius: 50%; background: #16a34a; animation: dlPulse 1.4s ease-in-out infinite; }
        @keyframes dlPulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }
        .dl-hero h1 { font-weight: 800; font-size: 2.6rem; }
        .dl-hero h1 .dl-accent { color: var(--dl-purple); }
        .dl-cta-btn { background: var(--dl-ink); color: #fff; border-radius: .8rem; padding: .9rem 1.6rem; font-weight: 700; border: 0; }
        .dl-cta-btn:hover { background: #1e293b; color: #fff; }
        .dl-section { padding: 3rem 0; }
        .dl-section-title { font-weight: 800; font-size: 1.6rem; text-align: center; margin-bottom: .5rem; }
        .dl-section-sub { text-align: center; color: #64748b; margin-bottom: 2rem; }
        .dl-role-card { background: #fff; border: 1px solid rgba(15,23,42,.07); border-radius: 1.1rem; padding: 1.5rem; height: 100%; box-shadow: 0 .5rem 1.5rem rgba(15,23,42,.04); transition: transform .15s ease, box-shadow .15s ease; position: relative; }
        .dl-role-card:hover { transform: translateY(-4px); box-shadow: 0 1rem 2rem rgba(15,23,42,.08); }
        .dl-role-icon { width: 3rem; height: 3rem; border-radius: .8rem; background: rgba(124,58,237,.1); color: var(--dl-purple); display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 1rem; }
        .dl-role-highlight-badge { position: absolute; top: 1.2rem; right: 1.2rem; background: var(--dl-ink); color: #fff; font-size: .65rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px; }
        .dl-role-btn { background: var(--dl-ink); color: #fff; border-radius: .7rem; padding: .65rem 1rem; font-weight: 700; width: 100%; border: 0; text-align: center; text-decoration: none; display: inline-block; }
        .dl-role-btn:hover { background: var(--dl-purple); color: #fff; }
        .dl-role-btn.dl-role-btn-outline { background: transparent; color: var(--dl-ink); border: 1px solid rgba(15,23,42,.15); }
        .dl-role-btn.dl-role-btn-outline:hover { background: #f8f9fc; }
        .dl-safety-note { background: #fffbeb; border: 1px solid rgba(217,119,6,.25); border-radius: 1rem; padding: 1rem 1.3rem; font-size: .88rem; color: #92400e; display: flex; gap: .7rem; align-items: flex-start; }
        .dl-client-strip { background: #fff; overflow: hidden; }
        .dl-client-track-wrap { overflow: hidden; -webkit-mask-image: linear-gradient(90deg, transparent, #000 5%, #000 95%, transparent); mask-image: linear-gradient(90deg, transparent, #000 5%, #000 95%, transparent); }
        .dl-client-track { display: flex; gap: 1rem; width: max-content; animation: dlClientScroll 35s linear infinite; }
        .dl-client-track:hover { animation-play-state: paused; }

        .dl-brazil-map { display: grid; grid-template-columns: repeat(8, 1fr); gap: .4rem; max-width: 420px; margin: 0 auto; }
        .dl-state-cell { aspect-ratio: 1; border-radius: .45rem; background: rgba(15,23,42,.06); color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: .62rem; font-weight: 800; cursor: default; transition: transform .15s ease; }
        .dl-state-cell.on-active { background: var(--dl-purple); color: #fff; box-shadow: 0 .3rem .8rem rgba(109,40,217,.3); }
        .dl-state-cell.on-active:hover { transform: scale(1.12); }
        .dl-map-legend { display: flex; align-items: center; justify-content: center; gap: 1.5rem; margin-top: 1.5rem; font-size: .8rem; color: #64748b; flex-wrap: wrap; }
        .dl-map-legend-swatch { display: inline-block; width: .8rem; height: .8rem; border-radius: .25rem; margin-right: .4rem; vertical-align: -1px; }

        .dl-testimonial-card { background: #fff; border: 1px solid rgba(15,23,42,.07); border-radius: 1.1rem; padding: 1.6rem; height: 100%; box-shadow: 0 .5rem 1.5rem rgba(15,23,42,.04); }
        .dl-testimonial-quote-icon { color: rgba(124,58,237,.25); font-size: 1.4rem; }
        .dl-testimonial-text { font-size: .9rem; color: #334155; margin: .75rem 0 0; line-height: 1.55; }
        .dl-testimonial-avatar { width: 2.4rem; height: 2.4rem; border-radius: 50%; object-fit: cover; flex: 0 0 auto; }
        .dl-testimonial-avatar-fallback { background: var(--dl-purple); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .75rem; }
        @keyframes dlClientScroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        .dl-client-badge { display: flex; align-items: center; gap: .6rem; background: #f8f9fc; border: 1px solid rgba(15,23,42,.06); border-radius: 999px; padding: .5rem 1.1rem .5rem .5rem; white-space: nowrap; flex: 0 0 auto; }
        .dl-client-avatar { width: 2.1rem; height: 2.1rem; border-radius: 50%; object-fit: cover; background: var(--dl-purple); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .75rem; flex: 0 0 auto; }
        .dl-plan-card { background: #fff; border: 1px solid rgba(15,23,42,.08); border-radius: 1.1rem; padding: 1.8rem; height: 100%; }
        .dl-plan-card.dl-plan-highlight { background: var(--dl-ink); color: #fff; transform: scale(1.03); }
        .dl-plan-card.dl-plan-highlight .text-muted { color: rgba(255,255,255,.6) !important; }
        .dl-plan-badge { background: #fff; color: var(--dl-ink); font-size: .68rem; font-weight: 800; padding: .25rem .6rem; border-radius: 999px; }
        .dl-savings-badge { background: #16a34a; color: #fff; font-size: .68rem; font-weight: 800; padding: .25rem .6rem; border-radius: 999px; }
        .dl-adesao-badge { display: inline-block; background: #fef3c7; color: #92400e; font-size: .85rem; font-weight: 700; padding: .5rem 1.1rem; border-radius: 999px; }
        .dl-price-struck { text-decoration: line-through; font-size: 1rem; }
        .dl-final-cta { background: linear-gradient(135deg, var(--dl-ink), #1e1b4b); color: #fff; border-radius: 1.5rem; padding: 3rem 2.5rem; }
        .dl-whatsapp-btn { background: #16a34a; color: #fff; border-radius: .7rem; padding: .8rem 1.3rem; font-weight: 700; border: 0; text-decoration: none; display: inline-flex; align-items: center; gap: .5rem; }
        .dl-whatsapp-btn:hover { background: #15803d; color: #fff; }
        .dl-ai-fab { position: fixed; left: 1.5rem; bottom: 1.5rem; z-index: 1050; background: var(--dl-purple); color: #fff; border-radius: 999px; padding: .8rem 1.3rem; font-weight: 700; border: 0; box-shadow: 0 .8rem 2rem rgba(109,40,217,.35); display: inline-flex; align-items: center; gap: .5rem; }
        .dl-ai-panel { position: fixed; left: 1.5rem; bottom: 5.2rem; z-index: 1050; width: 320px; max-width: 90vw; background: #fff; border-radius: 1rem; box-shadow: 0 1.5rem 3rem rgba(15,23,42,.2); overflow: hidden; }
        .dl-ai-panel-header { background: var(--dl-purple); color: #fff; padding: .9rem 1.1rem; }
        .dl-ai-messages { max-height: 320px; overflow-y: auto; padding: .9rem; display: flex; flex-direction: column; gap: .6rem; background: #f8f9fc; }
        .dl-ai-msg { max-width: 85%; padding: .55rem .8rem; border-radius: .9rem; font-size: .82rem; line-height: 1.4; white-space: pre-wrap; }
        .dl-ai-msg.on-bot { background: #fff; border: 1px solid rgba(15,23,42,.08); align-self: flex-start; border-bottom-left-radius: .25rem; }
        .dl-ai-msg.on-user { background: var(--dl-purple); color: #fff; align-self: flex-end; border-bottom-right-radius: .25rem; }
        .dl-ai-input-row { display: flex; gap: .5rem; padding: .7rem; border-top: 1px solid rgba(15,23,42,.08); background: #fff; }
        .dl-ai-send-btn { background: var(--dl-purple); color: #fff; border-radius: .6rem; padding: 0 .9rem; border: 0; }
        .dl-ai-send-btn:hover { background: var(--dl-purple-light); color: #fff; }
        .dl-error-toast { position: fixed; top: 1rem; left: 50%; transform: translateX(-50%); z-index: 1060; }
    </style>
</head>
<body>

<?php if (!empty($leadError)): ?>
    <div class="dl-error-toast alert alert-danger alert-dismissible fade show shadow" role="alert">
        <?= htmlspecialchars($leadError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<nav class="dl-nav py-3">
    <div class="container d-flex justify-content-between align-items-center flex-wrap flex-xl-nowrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="dl-brand-icon overflow-hidden"><?php if (!empty($dlLogoUrl) && strpos($dlLogoUrl, '/assets/img/logo.png') === false): ?><img src="<?= htmlspecialchars($dlLogoUrl) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:.6rem;"><?php else: ?><i class="fa-solid fa-church"></i><?php endif; ?></span>
            <span class="fw-bold fs-5"><?= htmlspecialchars($dlBrand) ?></span>
            <span class="dl-tag">AMOSTRA</span>
        </div>
        <div class="d-none d-xl-flex align-items-center gap-3 flex-shrink-0">
            <a href="#acessos" class="text-decoration-none text-dark small fw-semibold text-nowrap">Demonstração</a>
            <a href="#clientes" class="text-decoration-none text-dark small fw-semibold text-nowrap">Igrejas Clientes</a>
            <a href="#mapa" class="text-decoration-none text-dark small fw-semibold text-nowrap">Igrejas no Brasil</a>
            <a href="#depoimentos" class="text-decoration-none text-dark small fw-semibold text-nowrap">Depoimentos</a>
            <a href="#planos" class="text-decoration-none text-dark small fw-semibold text-nowrap">Planos</a>
            <a href="#solicitar" class="text-decoration-none text-dark small fw-semibold text-nowrap">Contato</a>
        </div>
        <a href="#solicitar" class="btn btn-dark rounded-pill px-3 fw-semibold">Quero meu Sistema</a>
    </div>
</nav>

<section class="dl-hero">
    <div class="container">
        <div class="dl-live-pill mb-4" id="dlLivePill">
            <span class="dl-live-dot"></span>
            <span>AMOSTRA AO VIVO &middot; Dados fictícios &middot; Atualiza a cada <?= (int)$rotationDays ?> dias</span>
        </div>
        <h1 class="mb-3">O sistema que já faz parte de <span class="dl-accent">igrejas de verdade</span><br>e está mudando a forma como elas se gerenciam</h1>
        <p class="text-muted mx-auto mb-4" style="max-width:640px;">
            Explore em 1 clique como se fosse Administrador, Secretaria, Tesoureiro ou Membro. No seu servidor real, tudo protegido por <strong>login e senha</strong>.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="#acessos" class="dl-cta-btn">Explorar demonstração agora <i class="fa-solid fa-arrow-right ms-1"></i></a>
            <?php if (!empty($salesWhatsapp)): ?>
                <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D/', '', $salesWhatsapp)) ?>?text=<?= rawurlencode('Olá! Vi a demonstração do ' . $dlBrand . ' e quero saber mais.') ?>" target="_blank" rel="noopener" class="dl-whatsapp-btn">
                    <i class="fa-brands fa-whatsapp"></i> Falar no WhatsApp
                </a>
            <?php endif; ?>
        </div>
        <div class="mt-3 small text-muted" id="dlPresenceLabel" style="display:none;">
            <i class="fa-solid fa-circle-user me-1"></i><span id="dlPresenceCount">0</span> pessoa(s) explorando agora
        </div>
    </div>
</section>

<section class="dl-section" id="acessos">
    <div class="container">
        <h2 class="dl-section-title">Escolha um perfil e entre direto no sistema</h2>
        <p class="dl-section-sub">Clique em qualquer card e entre instantaneamente. Sem login, sem senha, só na demonstração.</p>

        <?php if (empty($accessCards)): ?>
            <div class="text-center text-muted py-4">Nenhuma conta de demonstração configurada ainda.</div>
        <?php else: ?>
            <div class="row g-4 mb-4">
                <?php foreach ($accessCards as $card): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="dl-role-card">
                            <?php if (!empty($card['highlight'])): ?><span class="dl-role-highlight-badge"><i class="fa-solid fa-star me-1"></i>Mais popular</span><?php endif; ?>
                            <span class="dl-role-icon"><i class="fa-solid <?= htmlspecialchars($card['icon']) ?>"></i></span>
                            <h3 class="h6 fw-bold mb-1"><?= htmlspecialchars($card['label']) ?></h3>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($card['desc']) ?></p>
                            <a href="<?= htmlspecialchars($card['magic_url']) ?>" class="dl-role-btn">Acessar como <?= htmlspecialchars($card['label']) ?> <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="col-md-6 col-lg-3">
                    <div class="dl-role-card">
                        <span class="dl-role-icon"><i class="fa-solid fa-globe"></i></span>
                        <h3 class="h6 fw-bold mb-1">Site Público da Igreja</h3>
                        <p class="text-muted small mb-3">A página inicial que seus membros e visitantes veem. Totalmente personalizável.</p>
                        <a href="/site" class="dl-role-btn dl-role-btn-outline">Ver Site da Igreja <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="dl-safety-note">
            <i class="fa-solid fa-lock mt-1"></i>
            <div><strong>No seu sistema real, tudo com segurança máxima.</strong> Cada perfil acessa apenas com usuário e senha pessoal, criptografada e com permissões definidas por você. Aqui liberamos o acesso direto <strong>apenas para demonstração</strong> — com dados fictícios que são resetados a cada <?= (int)$rotationDays ?> dias.</div>
        </div>
    </div>
</section>

<?php if (!empty($clients)): ?>
<section class="dl-section dl-client-strip" id="clientes">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="dl-section-title mb-0 text-start"><i class="fa-regular fa-building me-2"></i>Igrejas que já utilizam o nosso sistema</h2>
            <span class="dl-tag">+<?= count($clients) ?> igrejas ativas</span>
        </div>
    </div>
    <?php
    // Duplica a lista pra animação de scroll infinito nunca mostrar um
    // "buraco" no fim — translateX(-50%) encosta exatamente na 2ª cópia.
    $dlClientLoop = array_merge($clients, $clients);
    ?>
    <div class="dl-client-track-wrap">
        <div class="dl-client-track">
            <?php foreach ($dlClientLoop as $client): ?>
                <div class="dl-client-badge">
                    <?php if (!empty($client['logo_url'])): ?>
                        <img src="<?= htmlspecialchars($client['logo_url']) ?>" alt="" class="dl-client-avatar">
                    <?php else: ?>
                        <span class="dl-client-avatar"><?= htmlspecialchars(mb_substr($client['sigla'], 0, 2, 'UTF-8')) ?></span>
                    <?php endif; ?>
                    <div>
                        <div class="fw-bold small"><?= htmlspecialchars($client['sigla']) ?></div>
                        <div class="text-muted" style="font-size:.68rem;"><?= !empty($client['location']) ? htmlspecialchars(str_replace('/', ' · ', $client['location'])) : 'Cliente ativo' ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($dlStatesWithClients)): ?>
<section class="dl-section" id="mapa" style="background:#fff;">
    <div class="container text-center">
        <h2 class="dl-section-title"><i class="fa-solid fa-map-location-dot me-2"></i>Igrejas em todo o Brasil</h2>
        <p class="dl-section-sub">
            <?= count($dlStatesWithClients) ?> <?= count($dlStatesWithClients) === 1 ? 'estado já tem' : 'estados já têm' ?> igrejas usando o sistema — e esse número só cresce.
        </p>
        <div class="dl-brazil-map">
            <?php foreach ($dlBrazilStates as $dlUf => $dlStateInfo): ?>
                <?php
                $dlHasClient = isset($dlStatesWithClients[$dlUf]);
                $dlTooltip = $dlStateInfo[0] . ($dlHasClient ? ': ' . implode(', ', $dlStatesWithClients[$dlUf]) : '');
                ?>
                <div class="dl-state-cell<?= $dlHasClient ? ' on-active' : '' ?>" style="grid-column:<?= $dlStateInfo[2] + 1 ?>;grid-row:<?= $dlStateInfo[1] + 1 ?>;" title="<?= htmlspecialchars($dlTooltip) ?>">
                    <?= htmlspecialchars($dlUf) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="dl-map-legend">
            <span><span class="dl-map-legend-swatch" style="background:var(--dl-purple);"></span>Com igrejas usando o sistema</span>
            <span><span class="dl-map-legend-swatch" style="background:rgba(15,23,42,.06);"></span>Em breve</span>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($dlTestimonials)): ?>
<section class="dl-section" id="depoimentos" style="background:#f8f9fc;">
    <div class="container">
        <h2 class="dl-section-title text-center"><i class="fa-solid fa-quote-left me-2"></i>O que os pastores estão dizendo</h2>
        <p class="dl-section-sub text-center mb-4">Depoimentos de quem já usa o sistema no dia a dia da igreja.</p>
        <div class="row g-4">
            <?php foreach ($dlTestimonials as $dlT): ?>
                <?php
                $dlTLogo = null;
                foreach ($clients as $dlC) {
                    if ($dlC['sigla'] === $dlT['sigla']) {
                        $dlTLogo = $dlC['logo_url'] ?? null;
                        break;
                    }
                }
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="dl-testimonial-card">
                        <i class="fa-solid fa-quote-left dl-testimonial-quote-icon"></i>
                        <p class="dl-testimonial-text">"<?= htmlspecialchars($dlT['quote']) ?>"</p>
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <?php if (!empty($dlTLogo)): ?>
                                <img src="<?= htmlspecialchars($dlTLogo) ?>" class="dl-testimonial-avatar" alt="">
                            <?php else: ?>
                                <span class="dl-testimonial-avatar dl-testimonial-avatar-fallback"><?= htmlspecialchars(mb_substr($dlT['sigla'], 0, 2, 'UTF-8')) ?></span>
                            <?php endif; ?>
                            <div>
                                <div class="fw-bold small"><?= htmlspecialchars($dlT['name']) ?></div>
                                <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($dlT['church']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="dl-section" id="planos" style="background:#fff;">
    <div class="container">
        <h2 class="dl-section-title">Escolha como quer começar</h2>
        <p class="dl-section-sub">Sem fidelidade. Cancele quando quiser. Migração gratuita e suporte humano.</p>
        <?php if ($dlAdesaoDiscountActive): ?>
            <div class="text-center mb-4">
                <span class="dl-adesao-badge">🎁 Adesão com R$ <?= number_format((float)$dlAdesaoDiscount['amount'], 2, ',', '.') ?> de desconto por tempo limitado</span>
            </div>
        <?php endif; ?>
        <div class="row g-4 justify-content-center">
            <?php foreach ($dlPlans as $planKey => $plan): ?>
                <div class="col-md-4">
                    <div class="dl-plan-card <?= !empty($plan['highlight']) ? 'dl-plan-highlight' : '' ?>">
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <?php if (!empty($plan['highlight'])): ?><span class="dl-plan-badge d-inline-block">MAIS ESCOLHIDO</span><?php endif; ?>
                            <?php if (!empty($plan['trial_percent'])): ?>
                                <span class="dl-savings-badge d-inline-block">-<?= (int)round($plan['trial_percent']) ?>% por <?= $plan['trial_months'] ?> <?= $plan['trial_months'] == 1 ? 'mês' : 'meses' ?></span>
                            <?php elseif (!empty($plan['savings_percent'])): ?>
                                <span class="dl-savings-badge d-inline-block">Economize <?= $plan['savings_percent'] ?>%</span>
                            <?php endif; ?>
                        </div>
                        <?php
                        // Preço riscado: no Mensal com trial ativo, risca o preço
                        // cheio e mostra o valor promocional; no Trimestral/Anual,
                        // risca o preço do Mensal pra evidenciar quanto já
                        // economizam nesses planos.
                        $dlStruckPrice = null;
                        $dlDisplayPrice = $plan['price'];
                        if (!empty($plan['trial_percent'])) {
                            $dlStruckPrice = $plan['price'];
                            $dlDisplayPrice = round($plan['price'] * (1 - $plan['trial_percent'] / 100), 2);
                        } elseif (!empty($plan['savings_percent'])) {
                            $dlStruckPrice = $dlMensalPrice;
                        }
                        ?>
                        <div class="fw-bold mb-1"><?= htmlspecialchars($plan['label']) ?></div>
                        <div class="mb-2">
                            <?php if ($dlStruckPrice !== null): ?>
                                <span class="dl-price-struck text-muted d-block">de R$ <?= number_format($dlStruckPrice, 2, ',', '.') ?></span>
                            <?php endif; ?>
                            <span class="fw-bold" style="font-size:2rem;">R$ <?= number_format($dlDisplayPrice, 2, ',', '.') ?></span><span class="text-muted">/mês</span>
                        </div>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($plan['note']) ?></p>
                        <button type="button" class="btn <?= !empty($plan['highlight']) ? 'btn-light' : 'btn-outline-dark' ?> w-100 fw-semibold" onclick="dlSelectPlan('<?= $planKey ?>')">Escolher <?= htmlspecialchars($plan['label']) ?></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="dl-section" id="solicitar">
    <div class="container">
        <div class="dl-final-cta">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-2">Pronto para organizar sua igreja?</h2>
                    <p class="mb-4" style="color:rgba(255,255,255,.75);">Preencha seus dados, escolha o plano e receba o link de pagamento na hora. Nossa equipe entra em contato pra colocar seu sistema no ar.</p>
                    <?php if (!empty($salesWhatsapp)): ?>
                        <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D/', '', $salesWhatsapp)) ?>?text=<?= rawurlencode('Olá! Vi a demonstração do ' . $dlBrand . ' e quero saber mais.') ?>" target="_blank" rel="noopener" class="dl-whatsapp-btn">
                            <i class="fa-brands fa-whatsapp"></i> Conversar no WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <form action="/demo/solicitar-sistema" method="POST" class="bg-white text-dark rounded-4 p-4">
                        <h3 class="h5 fw-bold mb-3">Solicitar meu sistema</h3>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Seu nome</label>
                            <input type="text" name="client_name" class="form-control" placeholder="Ex: Pr. João Silva" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Nome da Igreja</label>
                            <input type="text" name="church_name" class="form-control" placeholder="Ex: Igreja Batista Central">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">WhatsApp</label>
                                <input type="text" name="client_phone" class="form-control" placeholder="(00) 00000-0000" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" name="client_email" class="form-control" placeholder="seu@email.com">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Plano</label>
                            <select name="plan" id="dlPlanSelect" class="form-select">
                                <?php foreach ($dlPlans as $planKey => $plan): ?>
                                    <option value="<?= $planKey ?>" <?= !empty($plan['highlight']) ? 'selected' : '' ?>><?= htmlspecialchars($plan['label']) ?> — R$ <?= number_format($plan['price'], 2, ',', '.') ?>/mês</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 fw-semibold py-2">Solicitar meu sistema agora <i class="fa-solid fa-arrow-right ms-1"></i></button>
                        <p class="text-muted small mt-2 mb-0 text-center">Você receberá contato em até 2h úteis para colocar seu sistema no ar. Sem compromisso.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="py-4 text-center text-muted small border-top bg-white">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($dlBrand) ?> &middot; Sistema de Gestão para Igrejas
</footer>

<button type="button" class="dl-ai-fab" id="dlAiFab"><i class="fa-solid fa-robot"></i> Tire dúvidas com IA</button>
<div class="dl-ai-panel d-none" id="dlAiPanel">
    <div class="dl-ai-panel-header d-flex justify-content-between align-items-center">
        <span class="fw-bold small"><i class="fa-solid fa-robot me-1"></i> Assistente <?= htmlspecialchars($dlBrand) ?></span>
        <button type="button" class="btn-close btn-close-white btn-sm" id="dlAiClose"></button>
    </div>
    <div class="dl-ai-messages" id="dlAiMessages"></div>
    <form id="dlAiForm" class="dl-ai-input-row">
        <input type="text" id="dlAiInput" class="form-control form-control-sm" placeholder="Digite sua pergunta..." autocomplete="off">
        <button type="submit" class="dl-ai-send-btn"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // Contador "N pessoas explorando agora" — heartbeat real via
    // DemoPresenceController (mesma chave de sessão só nesta aba).
    var key = sessionStorage.getItem('dlPresenceKey');
    if (!key) {
        key = 'dl' + Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
        sessionStorage.setItem('dlPresenceKey', key);
    }

    function ping() {
        var body = new URLSearchParams({ key: key });
        fetch('/demo-presence/ping', { method: 'POST', body: body })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.status === 'ok' && typeof data.count === 'number') {
                    document.getElementById('dlPresenceCount').textContent = data.count;
                    document.getElementById('dlPresenceLabel').style.display = '';
                }
            })
            .catch(function () {});
    }
    ping();
    var pingInterval = setInterval(ping, 8000);

    window.addEventListener('beforeunload', function () {
        clearInterval(pingInterval);
        var body = new URLSearchParams({ key: key });
        navigator.sendBeacon && navigator.sendBeacon('/demo-presence/leave', body);
    });

    // Botão "Falar com IA" — bot por palavras-chave, sem custo por
    // mensagem (nenhuma chamada de API real, só casamento de termos contra
    // as perguntas mais comuns).
    var fab = document.getElementById('dlAiFab');
    var panel = document.getElementById('dlAiPanel');
    var close = document.getElementById('dlAiClose');
    fab.addEventListener('click', function () { panel.classList.toggle('d-none'); });
    close.addEventListener('click', function () { panel.classList.add('d-none'); });

    var dlAiFaqs = <?= json_encode($dlAiFaqs, JSON_UNESCAPED_UNICODE) ?>;
    var dlAiFallback = 'Não tenho certeza sobre isso, mas nossa equipe pode te ajudar direto — clica no botão verde "Falar no WhatsApp" aqui na página, ou preenche o formulário "Solicitar meu sistema" que a gente entra em contato.';

    function dlAiNormalize(str) {
        return str.toLowerCase()
            .replace(/[áàãâ]/g, 'a').replace(/[éê]/g, 'e').replace(/[íî]/g, 'i')
            .replace(/[óõô]/g, 'o').replace(/[úü]/g, 'u').replace(/ç/g, 'c');
    }

    function dlAiFindAnswer(message) {
        var normalized = dlAiNormalize(message);
        var best = null;
        var bestScore = 0;
        dlAiFaqs.forEach(function (faq) {
            var score = 0;
            faq.keywords.forEach(function (kw) {
                if (normalized.indexOf(kw) !== -1) score++;
            });
            if (score > bestScore) {
                bestScore = score;
                best = faq;
            }
        });
        return best ? best.answer : dlAiFallback;
    }

    var dlAiMessages = document.getElementById('dlAiMessages');
    var dlAiForm = document.getElementById('dlAiForm');
    var dlAiInput = document.getElementById('dlAiInput');

    function dlAiAddMessage(text, sender) {
        var div = document.createElement('div');
        div.className = 'dl-ai-msg on-' + sender;
        div.textContent = text;
        dlAiMessages.appendChild(div);
        dlAiMessages.scrollTop = dlAiMessages.scrollHeight;
    }

    dlAiAddMessage('Olá! 👋 Sou o assistente virtual da <?= addslashes(htmlspecialchars($dlBrand)) ?>. Pergunte sobre preços, planos, suporte ou como testar o sistema.', 'bot');

    dlAiForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = dlAiInput.value.trim();
        if (!text) return;
        dlAiAddMessage(text, 'user');
        dlAiInput.value = '';
        setTimeout(function () {
            dlAiAddMessage(dlAiFindAnswer(text), 'bot');
        }, 400);
    });
})();

function dlSelectPlan(planKey) {
    document.getElementById('dlPlanSelect').value = planKey;
    document.getElementById('solicitar').scrollIntoView({ behavior: 'smooth' });
}
</script>
</body>
</html>

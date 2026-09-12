<?php
// Botão flutuante "voltar pra demonstração", incluído nos layouts do
// admin e do portal do membro — só aparece enquanto a Página de
// Demonstração estiver ativa nesta instância, pra quem está explorando
// como visitante não ficar preso dentro do painel sem saber como voltar
// pra landing de vendas.
if ((new DemoLandingService())->getConfig()['enabled']):
?>
<a href="/" class="demo-back-fab" title="Voltar para a página inicial da demonstração">
    <i class="fa-solid fa-arrow-left"></i> Voltar para a Demonstração
</a>
<style>
    .demo-back-fab {
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 1080;
        background: #6d28d9;
        color: #fff;
        border-radius: 999px;
        padding: .7rem 1.2rem;
        font-weight: 700;
        font-size: .85rem;
        text-decoration: none;
        box-shadow: 0 .6rem 1.5rem rgba(109, 40, 217, .35);
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }
    .demo-back-fab:hover { background: #5b21b6; color: #fff; }
    @media (max-width: 576px) {
        .demo-back-fab { bottom: 4.75rem; padding: .6rem .95rem; font-size: .78rem; }
    }
</style>
<?php endif; ?>

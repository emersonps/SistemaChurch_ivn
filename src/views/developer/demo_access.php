<?php include __DIR__ . '/layout_developer.php'; ?>

<h1 class="h2 mb-1">Acesso Demonstrativo</h1>
<p class="text-muted mb-4">Envie o portal e as credenciais de teste diretamente para um cliente interessado, pelo WhatsApp.</p>

<?php if (empty($demoConfig['enabled'])): ?>
    <div class="alert alert-warning">
        <i class="fas fa-triangle-exclamation me-2"></i>
        O ambiente demonstrativo está desativado para esta instância. Ative-o na tela "Página de Demonstração" da Central para liberar as credenciais aqui.
    </div>
<?php elseif (empty($demo['credentials'])): ?>
    <div class="alert alert-warning">
        <i class="fas fa-triangle-exclamation me-2"></i>
        Nenhum usuário demonstrativo configurado ainda. Cadastre os usuários (Administrador/Secretaria/Membro) na tela "Página de Demonstração" da Central.
    </div>
<?php else: ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-globe me-1"></i> Portal Demonstrativo
        </div>
        <div class="card-body">
            <?php if (!empty($demoConfig['public_url'])): ?>
                <a href="<?= htmlspecialchars($demoConfig['public_url']) ?>" target="_blank" rel="noopener noreferrer" class="fw-bold fs-5 text-decoration-none">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> <?= htmlspecialchars(preg_replace('#^https?://#', '', $demoConfig['public_url'])) ?>
                </a>
            <?php else: ?>
                <span class="text-muted">Nenhuma URL pública configurada.</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-key me-1"></i> Credenciais de Teste</span>
            <span class="badge bg-light text-dark border">
                Renovam a cada <?= (int)$demo['rotation_days'] ?> dias
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php
                $icons = ['Administrador' => 'fa-user-shield', 'Secretaria' => 'fa-clipboard', 'Membro' => 'fa-user'];
                ?>
                <?php foreach ($demo['credentials'] as $cred): ?>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase fw-bold mb-2">
                                <i class="fas <?= $icons[$cred['label']] ?? 'fa-user' ?> me-1"></i> <?= htmlspecialchars($cred['label']) ?>
                            </div>
                            <div class="small text-muted">Usuário</div>
                            <div class="fw-bold mb-2"><?= htmlspecialchars($cred['username']) ?></div>
                            <div class="small text-muted">Senha</div>
                            <div class="fw-bold"><?= htmlspecialchars($cred['password']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-muted small mt-3">
                <i class="fas fa-rotate me-1"></i> Última renovação: <?= !empty($demo['rotated_at']) ? date('d/m/Y H:i', strtotime($demo['rotated_at'])) : '—' ?>
                · Próxima: <?= !empty($demo['next_rotation_at']) ? date('d/m/Y H:i', strtotime($demo['next_rotation_at'])) : '—' ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-paper-plane me-1"></i> Enviar para o cliente
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Escolha o contato ou grupo no WhatsApp e envie o portal com as credenciais prontas.</p>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-success rounded-pill fw-semibold px-4" onclick="sendDemoWhatsapp()">
                    <i class="fab fa-whatsapp me-2"></i> Enviar pelo WhatsApp
                </button>
                <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-4" id="btnCopyDemoText">
                    <i class="fas fa-copy me-2"></i> Copiar texto
                </button>
            </div>
        </div>
    </div>

    <script>
        const demoMessage = <?= json_encode(
            "Conheça o " . ($siteProfile['name'] ?? 'nosso sistema') . " na prática! 🚀\n\n" .
            "Acesse o portal demonstrativo e explore os recursos:\n" . ($demoConfig['public_url'] ?? '') . "\n\n" .
            implode("\n\n", array_map(function ($c) {
                return "*" . $c['label'] . "*\nUsuário: " . $c['username'] . "\nSenha: " . $c['password'];
            }, $demo['credentials'])) .
            "\n\nPor segurança, essas senhas são renovadas periodicamente — se pararem de funcionar, me chame que eu envio novas.",
            JSON_UNESCAPED_UNICODE
        ) ?>;

        function sendDemoWhatsapp() {
            window.open('https://wa.me/?text=' + encodeURIComponent(demoMessage), '_blank');
        }

        document.getElementById('btnCopyDemoText').addEventListener('click', function () {
            navigator.clipboard.writeText(demoMessage).then(() => {
                const btn = document.getElementById('btnCopyDemoText');
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-2"></i> Copiado!';
                setTimeout(() => { btn.innerHTML = original; }, 1500);
            });
        });
    </script>

<?php endif; ?>

<?php include __DIR__ . '/layout_footer.php'; ?>

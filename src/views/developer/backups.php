<?php include __DIR__ . '/layout_developer.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Backups do Banco</h1>
        <p class="text-muted mb-0">Acesso exclusivo do desenvolvedor. O sistema mantém backups automáticos semanais e permite gerar backups manuais sob demanda.</p>
    </div>
    <form method="POST" action="/developer/backups/generate">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-download me-1"></i> Gerar Backup Agora
        </button>
    </form>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white">Modelo Recomendado</div>
            <div class="card-body">
                <div class="mb-2"><strong>Periodicidade:</strong> semanal</div>
                <div class="mb-2"><strong>Armazenamento:</strong> fora da pasta pública</div>
                <div class="mb-2"><strong>Acesso:</strong> somente desenvolvedor</div>
                <div class="mb-2"><strong>Retenção:</strong> até 12 backups automáticos</div>
                <div><strong>Formato:</strong> dump SQL completo</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white">Regras de Segurança</div>
            <div class="card-body">
                <div class="mb-2">Downloads passam pelo painel do dev, sem exposição pública de pasta.</div>
                <div class="mb-2">O sistema valida o nome do arquivo antes de liberar o download.</div>
                <div class="mb-2">Backups automáticos são gerados somente quando necessário.</div>
                <div>Para automação total, agende o script <strong>scripts/generate_developer_backup.php</strong> no servidor.</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white">Observações</div>
            <div class="card-body">
                <div class="mb-2">Backups manuais não são apagados automaticamente.</div>
                <div class="mb-2">Backups automáticos são renovados no máximo uma vez por semana.</div>
                <div>Use esta tela para baixar o arquivo e armazená-lo também fora do servidor.</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span>Arquivos Disponíveis</span>
        <span class="badge bg-secondary"><?= count($backups ?? []) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Arquivo</th>
                    <th>Tipo</th>
                    <th>Banco</th>
                    <th>Tamanho</th>
                    <th>Gerado em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($backups)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">Nenhum backup disponível.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><?= htmlspecialchars($backup['filename']) ?></td>
                            <td>
                                <span class="badge bg-<?= $backup['mode'] === 'automatico' ? 'info' : 'primary' ?>">
                                    <?= ucfirst($backup['mode']) ?>
                                </span>
                            </td>
                            <td><?= strtoupper(htmlspecialchars($backup['driver'])) ?></td>
                            <td><?= $backup['size_label'] ?></td>
                            <td><?= $backup['created_at_label'] ?></td>
                            <td class="text-end">
                                <a href="/developer/backups/download?file=<?= urlencode($backup['filename']) ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-download me-1"></i> Baixar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/layout_footer.php'; ?>

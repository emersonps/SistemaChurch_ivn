<?php include __DIR__ . '/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Meus Documentos</h1>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($docs)): ?>
            <p class="text-muted">Você ainda não possui documentos anexados.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Data</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($docs as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['title']) ?></td>
                                <td>
                                    <?php 
                                        $type = strtolower($d['type'] ?? '');
                                        $label = $type;
                                        if ($type === 'transfer_letter') $label = 'Carta de Transferência';
                                        elseif ($type === 'rg') $label = 'RG';
                                        elseif ($type === 'cpf') $label = 'CPF';
                                        elseif ($type === 'other') $label = 'Outro';
                                    ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($label) ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" target="_blank" href="/portal/documents/open/<?= $d['id'] ?>">
                                        <i class="fas fa-download me-1"></i> Abrir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
 </div>

<?php include __DIR__ . '/layout/footer.php'; ?>

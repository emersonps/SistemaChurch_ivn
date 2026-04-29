<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Estudo / Esboço</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/studies" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php 
        if ($_GET['error'] == 'invalid_type') echo "Apenas arquivos PDF são permitidos.";
        elseif ($_GET['error'] == 'invalid_cover') echo "A capa deve ser uma imagem (JPG, PNG ou WEBP).";
        elseif ($_GET['error'] == 'upload_failed') echo "Falha ao enviar o arquivo.";
        else echo "Ocorreu um erro.";
        ?>
    </div>
<?php endif; ?>

<?php
$baseName = pathinfo((string)($study['file_path'] ?? ''), PATHINFO_FILENAME);
$coverUrl = null;
if ($baseName !== '') {
    $coverDir = __DIR__ . '/../../../../public/uploads/studies/covers/';
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $candidate = $coverDir . $baseName . '.' . $ext;
        if (is_file($candidate)) {
            $coverUrl = '/uploads/studies/covers/' . $baseName . '.' . $ext;
            break;
        }
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="/admin/studies/edit/<?= (int)$study['id'] ?>" method="POST" enctype="multipart/form-data" class="app-form-with-bottom-actions">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($study['title'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição (Opcional)</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($study['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Arquivo atual</label>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a href="/uploads/studies/<?= htmlspecialchars($study['file_path'] ?? '') ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-file-pdf me-1"></i> Abrir PDF
                            </a>
                            <span class="text-muted small"><?= htmlspecialchars($study['file_path'] ?? '') ?></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Substituir arquivo (PDF)</label>
                        <input type="file" name="file" class="form-control" accept="application/pdf">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Capa</label>
                        <div class="d-flex gap-3 align-items-start">
                            <?php if ($coverUrl): ?>
                                <img src="<?= htmlspecialchars($coverUrl) ?>" alt="Capa" style="width: 72px; height: 96px; object-fit: contain; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: #fff;">
                            <?php else: ?>
                                <div style="width: 72px; height: 96px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: #f8f9fa; color: #6c757d;">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <input type="file" name="cover" class="form-control" accept="image/png,image/jpeg,image/webp">
                                <div class="form-text">Se enviar, a capa substituirá a atual.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Visibilidade</label>
                        <select name="congregation_id" class="form-select">
                            <option value="" <?= empty($study['congregation_id']) ? 'selected' : '' ?>>Geral (Visível para todos os membros)</option>
                            <?php foreach ($congregations as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ((string)($study['congregation_id'] ?? '') === (string)$c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecione uma congregação para restringir o acesso ou deixe "Geral" para todos.</div>
                    </div>

                    <div class="mt-3 text-end d-none d-lg-block">
                        <button type="submit" class="btn btn-primary px-4">Salvar</button>
                        <a href="/admin/studies" class="btn btn-outline-secondary px-4">Cancelar</a>
                    </div>

                    <div class="app-form-bottom-actions d-lg-none">
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary w-100">Salvar</button>
                            </div>
                            <div class="col-6">
                                <a href="/admin/studies" class="btn btn-outline-secondary w-100">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

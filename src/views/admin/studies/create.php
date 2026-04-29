<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Estudo / Esboço</h1>
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
        elseif ($_GET['error'] == 'no_file') echo "Nenhum arquivo selecionado.";
        else echo "Ocorreu um erro.";
        ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="/admin/studies/create" method="POST" enctype="multipart/form-data" class="app-form-with-bottom-actions">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control" required placeholder="Ex: Estudo sobre Oração">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição (Opcional)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Arquivo (PDF)</label>
                        <input type="file" name="file" class="form-control" accept="application/pdf" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Capa (Opcional)</label>
                        <input type="file" name="cover" class="form-control" accept="image/png,image/jpeg,image/webp">
                        <div class="form-text">Se enviar, a capa será usada como miniatura do estudo.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Visibilidade</label>
                        <select name="congregation_id" class="form-select">
                            <option value="">Geral (Visível para todos os membros)</option>
                            <?php foreach ($congregations as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
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

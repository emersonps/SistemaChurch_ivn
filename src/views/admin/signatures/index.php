<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Assinaturas Digitais</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSignatureModal">
        <i class="fas fa-plus"></i> Nova Assinatura
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Assinatura salva com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <?php foreach ($signatures as $sig): ?>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <h5 class="card-title text-primary"><?= htmlspecialchars($sig['role_label']) ?></h5>
                <p class="card-text text-muted mb-3"><?= htmlspecialchars($sig['name']) ?></p>
                
                <div class="border rounded p-2 mb-3 d-flex align-items-center justify-content-center bg-light" style="height: 120px;">
                    <?php if (!empty($sig['image_path'])): ?>
                        <img src="/uploads/signatures/<?= $sig['image_path'] ?>" style="max-height: 100px; max-width: 100%;">
                    <?php else: ?>
                        <span class="text-muted small">Sem imagem</span>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editSignatureModal<?= $sig['id'] ?>">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-outline-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteSignatureModal<?= $sig['id'] ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <!-- Edit Modal -->
                <div class="modal fade text-start" id="editSignatureModal<?= $sig['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="/admin/signatures/store" method="POST" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="modal-header">
                                    <h5 class="modal-title">Editar Assinatura</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $sig['id'] ?>">
                                    <input type="hidden" name="slug" value="<?= $sig['slug'] ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Cargo / Função</label>
                                        <input type="text" class="form-control" name="role_label" value="<?= htmlspecialchars($sig['role_label']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nome do Responsável</label>
                                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($sig['name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Imagem da Assinatura (PNG transparente)</label>
                                        <input type="file" class="form-control" name="signature_image" accept="image/png, image/jpeg">
                                        <div class="form-text">Recomendado: Imagem PNG com fundo transparente.</div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade text-start" id="deleteSignatureModal<?= $sig['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Excluir Assinatura</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Tem certeza que deseja excluir a assinatura de <strong><?= htmlspecialchars($sig['role_label']) ?></strong>?</p>
                                <p class="text-danger small">Esta ação não pode ser desfeita.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <a href="/admin/signatures/delete/<?= $sig['id'] ?>" class="btn btn-danger">Excluir</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addSignatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/signatures/store" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Nova Assinatura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cargo / Função</label>
                        <input type="text" class="form-control" name="role_label" placeholder="Ex: Dirigente de Congregação" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome do Responsável</label>
                        <input type="text" class="form-control" name="name" placeholder="Ex: Pb. João da Silva" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagem da Assinatura</label>
                        <input type="file" class="form-control" name="signature_image" accept="image/png, image/jpeg">
                    </div>
                    <input type="hidden" name="slug" value=""> <!-- Será gerado automaticamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

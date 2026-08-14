<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Assinaturas Digitais</h1>
    <button type="button" class="btn btn-primary rounded-pill fw-semibold px-3" onclick="openAddModal()">
        <i class="fas fa-plus me-1"></i> Nova Assinatura
    </button>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .member-form-card-body { padding: 1.25rem; }
    .signature-preview {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        background: #fafafa;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    .modal-content { border-radius: 16px; border: none; }
    .modal-header { border-bottom: 1px solid rgba(0,0,0,0.07); }
    .modal-footer { border-top: 1px solid rgba(0,0,0,0.07); }
    .modal-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .modal-body .form-control {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .modal-body .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Assinatura salva com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (empty($signatures)): ?>
    <div class="member-form-card">
        <div class="member-form-card-body text-center py-5">
            <i class="fas fa-signature fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Nenhuma assinatura cadastrada.</h5>
            <p class="text-muted mb-0">Clique em "Nova Assinatura" para começar.</p>
        </div>
    </div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($signatures as $sig): ?>
    <div class="col-md-4">
        <div class="member-form-card h-100">
            <div class="member-form-card-body text-center">
                <h5 class="mb-1"><?= htmlspecialchars($sig['role_label']) ?></h5>
                <p class="text-muted mb-3"><?= htmlspecialchars($sig['name']) ?></p>

                <div class="signature-preview mb-3">
                    <?php if (!empty($sig['image_path'])): ?>
                        <img src="/uploads/signatures/<?= $sig['image_path'] ?>" style="max-height: 100px; max-width: 100%;">
                    <?php else: ?>
                        <span class="text-muted small">Sem imagem</span>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3"
                            onclick="editSignature(<?= $sig['id'] ?>, '<?= htmlspecialchars($sig['slug']) ?>', '<?= htmlspecialchars($sig['role_label']) ?>', '<?= htmlspecialchars($sig['name']) ?>', '<?= htmlspecialchars($sig['document_types'] ?? '') ?>')">
                        <i class="fas fa-edit me-1"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-outline-danger icon-btn"
                            onclick="deleteSignature(<?= $sig['id'] ?>, '<?= htmlspecialchars($sig['role_label']) ?>')" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Add Modal -->
<div class="modal fade" id="addSignatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/signatures/store" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Nova Assinatura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cargo / Função</label>
                        <input type="text" class="form-control" name="role_label" id="add_role_label" placeholder="Ex: Dirigente de Congregação" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome do Responsável</label>
                        <input type="text" class="form-control" name="name" id="add_name" placeholder="Ex: Pb. João da Silva" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Documentos que usam esta assinatura</label>
                        <div id="add_document_types">
                            <?php
                                $docTypes = getSignatureDocumentTypes();
                            ?>
                            <?php foreach ($docTypes as $type => $label): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="document_types[]" value="<?= $type ?>" id="doc_new_<?= $type ?>">
                                    <label class="form-check-label" for="doc_new_<?= $type ?>">
                                        <?= htmlspecialchars($label) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagem da Assinatura</label>
                        <input type="file" class="form-control" name="signature_image" accept="image/png, image/jpeg">
                    </div>
                    <input type="hidden" name="slug" id="add_slug" value=""> <!-- Será gerado automaticamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold px-3">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editSignatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/signatures/store" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Editar Assinatura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="slug" id="edit_slug">

                    <div class="mb-3">
                        <label class="form-label">Cargo / Função</label>
                        <input type="text" class="form-control" name="role_label" id="edit_role_label" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome do Responsável</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Documentos que usam esta assinatura</label>
                        <div id="edit_document_types">
                            <?php foreach ($docTypes as $type => $label): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="document_types[]" value="<?= $type ?>" id="doc_edit_<?= $type ?>">
                                    <label class="form-check-label" for="doc_edit_<?= $type ?>">
                                        <?= htmlspecialchars($label) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagem da Assinatura (PNG transparente)</label>
                        <input type="file" class="form-control" name="signature_image" accept="image/png, image/jpeg">
                        <div class="form-text">Recomendado: Imagem PNG com fundo transparente.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold px-3">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteSignatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Excluir Assinatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a assinatura de <strong id="delete_signature_name"></strong>?</p>
                <p class="text-danger small mb-0">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="delete_signature_link" class="btn btn-danger rounded-pill fw-semibold px-3">Excluir</a>
            </div>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('add_role_label').value = '';
    document.getElementById('add_name').value = '';
    document.getElementById('add_slug').value = '';
    document.querySelectorAll('#add_document_types input[type="checkbox"]').forEach(cb => cb.checked = false);
    new bootstrap.Modal(document.getElementById('addSignatureModal')).show();
}

function editSignature(id, slug, roleLabel, name, documentTypes) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_slug').value = slug;
    document.getElementById('edit_role_label').value = roleLabel;
    document.getElementById('edit_name').value = name;

    const docTypes = documentTypes ? JSON.parse(documentTypes) : [];
    document.querySelectorAll('#edit_document_types input[type="checkbox"]').forEach(cb => {
        cb.checked = docTypes.includes(cb.value);
    });

    new bootstrap.Modal(document.getElementById('editSignatureModal')).show();
}

function deleteSignature(id, name) {
    document.getElementById('delete_signature_name').textContent = name;
    document.getElementById('delete_signature_link').href = '/admin/signatures/delete/' + id;
    new bootstrap.Modal(document.getElementById('deleteSignatureModal')).show();
}
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

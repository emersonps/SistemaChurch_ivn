<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Histórico de <?= htmlspecialchars($member['name']) ?></h1>
    <div>
        <a href="/admin/members/show/<?= $member['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
 </div>

<div class="card mb-4">
    <div class="card-body">
        <form action="/admin/members/history/<?= $member['id'] ?>" method="POST" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Categoria</label>
                <select class="form-select" name="category">
                    <option value="Observação">Observação</option>
                    <option value="Atendimento Pastoral">Atendimento Pastoral</option>
                    <option value="Participação">Participação</option>
                    <option value="Disciplina">Disciplina</option>
                    <option value="Financeiro">Financeiro</option>
                    <option value="Saúde">Saúde</option>
                    <option value="Família">Família</option>
                    <option value="Ministério">Ministério</option>
                </select>
            </div>
            <div class="col-md-9">
                <label class="form-label">Observação</label>
                <textarea class="form-control" name="note" rows="3" placeholder="Descreva o histórico ou observação relevante"></textarea>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Registrar</button>
            </div>
        </form>
    </div>
 </div>

<div class="card">
    <div class="card-body">
        <?php if (empty($items)): ?>
            <p class="text-muted">Nenhum histórico registrado para este membro.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Registrado por</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $h): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($h['category']) ?></span></td>
                                <td><?= nl2br(htmlspecialchars($h['note'])) ?></td>
                                <td><?= htmlspecialchars($h['username'] ?? 'Usuário') ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-hist"
                                            data-id="<?= $h['id'] ?>"
                                            data-category="<?= htmlspecialchars($h['category']) ?>"
                                            data-note="<?= htmlspecialchars($h['note']) ?>">
                                        <i class="fas fa-edit me-1"></i> Editar
                                    </button>
                                    <a href="/admin/members/history/delete/<?= $h['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete-hist">
                                        <i class="fas fa-trash me-1"></i> Excluir
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

<script>
document.querySelectorAll('.btn-delete-hist').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.getAttribute('href');
        Swal.fire({
            title: 'Excluir registro?',
            text: 'Esta ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});

document.querySelectorAll('.btn-edit-hist').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const currentCategory = this.getAttribute('data-category');
        const currentNote = this.getAttribute('data-note');
        const tokenInput = document.querySelector('input[name="csrf_token"]');
        const csrfToken = tokenInput ? tokenInput.value : '';
        const categories = ['Observação','Atendimento Pastoral','Participação','Disciplina','Financeiro','Saúde','Família','Ministério'];
        const selectHtml = `<select id="histEditCategory" class="form-select mb-2">` +
            categories.map(c => `<option value="${c}" ${c===currentCategory?'selected':''}>${c}</option>`).join('') +
            `</select>`;
        const textareaHtml = `<textarea id="histEditNote" class="form-control" rows="4" placeholder="Observação">${currentNote}</textarea>`;
        Swal.fire({
            title: 'Editar histórico',
            html: selectHtml + textareaHtml,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Salvar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const cat = document.getElementById('histEditCategory').value;
                const note = document.getElementById('histEditNote').value.trim();
                if (!note) {
                    Swal.showValidationMessage('Digite a observação');
                    return false;
                }
                return {cat, note};
            }
        }).then((res) => {
            if (res.isConfirmed) {
                const payload = new FormData();
                payload.append('csrf_token', csrfToken);
                payload.append('category', res.value.cat);
                payload.append('note', res.value.note);
                fetch(`/admin/members/history/update/${id}`, {
                    method: 'POST',
                    body: payload
                }).then(() => {
                    window.location.reload();
                }).catch(() => {
                    Swal.fire('Erro', 'Falha ao salvar. Tente novamente.', 'error');
                });
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

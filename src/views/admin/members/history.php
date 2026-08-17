<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/members" class="text-decoration-none">Membros</a></li>
                <li class="breadcrumb-item"><a href="/admin/members/show/<?= $member['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($member['name']) ?></a></li>
                <li class="breadcrumb-item active">Histórico</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Histórico de <?= htmlspecialchars($member['name']) ?></h1>
    </div>
    <div>
        <a href="/admin/members/show/<?= $member['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .member-form-card-header {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-badge {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #eef0f2;
        color: #212529;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .95rem;
    }
    .member-form-card-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
        line-height: 1.2;
    }
    .member-form-card-subtitle {
        font-size: .82rem;
        color: #868e96;
        margin-top: .1rem;
    }
    .member-form-card-body { padding: 1.25rem; }
    .member-form-card-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .member-form-card-body .form-control,
    .member-form-card-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus,
    .member-form-card-body .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }

    .history-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .history-table td {
        vertical-align: top;
        padding-top: .7rem;
        padding-bottom: .7rem;
    }
    .category-pill {
        display: inline-block;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
        white-space: nowrap;
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
</style>

<!-- Novo Registro -->
<div class="member-form-card mb-4">
    <div class="member-form-card-header">
        <div class="member-form-badge"><i class="fas fa-plus"></i></div>
        <div>
            <div class="member-form-card-title">Novo Registro</div>
            <div class="member-form-card-subtitle">Adicione uma observação ao histórico deste membro.</div>
        </div>
    </div>
    <div class="member-form-card-body">
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
                <button type="submit" class="btn btn-primary rounded-pill fw-semibold px-4">Registrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Registros -->
<div class="member-form-card">
    <div class="member-form-card-header">
        <div class="member-form-badge"><i class="fas fa-history"></i></div>
        <div>
            <div class="member-form-card-title">Registros Anteriores</div>
            <div class="member-form-card-subtitle"><?= count($items) ?> registro(s).</div>
        </div>
    </div>
    <div class="p-2">
        <?php if (empty($items)): ?>
            <p class="text-muted text-center py-4 mb-0">Nenhum histórico registrado para este membro.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover history-table mb-0">
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
                                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
                                <td><span class="category-pill"><?= htmlspecialchars($h['category']) ?></span></td>
                                <td><?= nl2br(htmlspecialchars($h['note'])) ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($h['username'] ?? 'Usuário') ?></td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-btn btn-edit-hist"
                                            data-id="<?= $h['id'] ?>"
                                            data-category="<?= htmlspecialchars($h['category']) ?>"
                                            data-note="<?= htmlspecialchars($h['note']) ?>"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="/admin/members/history/delete/<?= $h['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-hist" title="Excluir">
                                        <i class="fas fa-trash"></i>
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
                window.location.replace(href);
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

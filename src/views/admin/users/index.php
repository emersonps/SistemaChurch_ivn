<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gerenciar Usuários</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/users/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Novo Usuário
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
    .users-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .users-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
    }
    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #eef0f2;
        color: #495057;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .9rem;
        flex: 0 0 auto;
    }
    .role-pill {
        display: inline-block;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .role-pill.role-admin { background: rgba(179,0,0,0.10); color: #b30000; }
    .role-pill.role-developer { background: #212529; color: #fff; }
    .role-pill.role-secretary { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .role-pill.role-accountant { background: rgba(25,135,84,0.10); color: #198754; }
    .role-pill.role-treasurer { background: rgba(255,153,0,0.12); color: #b36b00; }
    .role-pill.role-default { background: #eef0f2; color: #495057; }
    .linked-members-text {
        font-size: .8rem;
        color: #6c757d;
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
    .filter-card .form-control {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .filter-card .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<?php
function userRolePillClass($role) {
    $known = ['admin', 'developer', 'secretary', 'accountant', 'treasurer'];
    return in_array($role, $known, true) ? 'role-' . $role : 'role-default';
}
$rbac = require __DIR__ . '/../../../../config/rbac.php';
?>

<div class="member-form-card filter-card mb-3">
    <div class="p-3">
        <input type="search" class="form-control" id="usersSearch" placeholder="Pesquisar por usuário, função, membro vinculado..." autocomplete="off">
    </div>
</div>

<div class="d-lg-none">
    <?php if (empty($users)): ?>
        <div class="member-form-card">
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Nenhum usuário cadastrado.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="d-grid gap-2">
            <?php foreach ($users as $u): ?>
                <?php
                $roleLabel = $rbac['roles'][$u['role']]['label'] ?? $u['role'];
                $canDelete = $u['id'] != $_SESSION['user_id'] && $u['role'] !== 'developer';
                ?>
                <div class="member-form-card user-item" data-search="<?= htmlspecialchars(mb_strtolower(($u['username'] ?? '') . ' ' . $roleLabel . ' ' . ($u['linked_members'] ?? ''), 'UTF-8')) ?>">
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="user-avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($u['username'], 0, 1))) ?></div>
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?= htmlspecialchars($u['username']) ?></div>
                                <div class="mt-1"><span class="role-pill <?= userRolePillClass($u['role']) ?>"><?= htmlspecialchars($roleLabel) ?></span></div>
                                <?php if (!empty($u['linked_members'])): ?>
                                    <div class="linked-members-text mt-1"><i class="fas fa-user me-1"></i> <?= htmlspecialchars($u['linked_members']) ?></div>
                                <?php endif; ?>
                                <div class="small text-muted mt-1"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="/admin/users/edit/<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </a>
                                <?php if ($canDelete): ?>
                                    <a href="/admin/users/delete/<?= $u['id'] ?>" class="btn btn-sm btn-danger rounded-pill fw-semibold px-3 btn-delete-user" data-username="<?= htmlspecialchars($u['username']) ?>">
                                        <i class="fas fa-trash me-1"></i> Excluir
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="member-form-card d-none d-lg-block">
    <div class="table-responsive p-2">
        <table class="table table-hover users-table" style="width:100%">
            <thead>
                <tr>
                    <th style="width: 60px;"></th>
                    <th>Usuário</th>
                    <th>Função</th>
                    <th>Membros Vinculados</th>
                    <th>Criado em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Nenhum usuário cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php
                        $roleLabel = $rbac['roles'][$u['role']]['label'] ?? $u['role'];
                        $canDelete = $u['id'] != $_SESSION['user_id'] && $u['role'] !== 'developer';
                        ?>
                        <tr class="user-item" data-search="<?= htmlspecialchars(mb_strtolower(($u['username'] ?? '') . ' ' . $roleLabel . ' ' . ($u['linked_members'] ?? ''), 'UTF-8')) ?>">
                            <td><div class="user-avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($u['username'], 0, 1))) ?></div></td>
                            <td class="fw-bold"><?= htmlspecialchars($u['username']) ?></td>
                            <td><span class="role-pill <?= userRolePillClass($u['role']) ?>"><?= htmlspecialchars($roleLabel) ?></span></td>
                            <td class="linked-members-text"><?= htmlspecialchars($u['linked_members'] ?: '-') ?></td>
                            <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="/admin/users/edit/<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($canDelete): ?>
                                    <a href="/admin/users/delete/<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-user" data-username="<?= htmlspecialchars($u['username']) ?>" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('usersSearch');
    if (!input) return;

    function normalize(v) {
        return String(v || '').toLowerCase().trim();
    }

    function filter() {
        var q = normalize(input.value);

        document.querySelectorAll('.user-item').forEach(function (item) {
            var hay = item.getAttribute('data-search') || '';
            item.style.display = q === '' || hay.indexOf(q) !== -1 ? '' : 'none';
        });
    }

    input.addEventListener('input', filter);
    filter();
});

document.querySelectorAll('.btn-delete-user').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const username = btn.getAttribute('data-username');
        Swal.fire({
            title: 'Excluir usuário?',
            text: `Tem certeza que deseja excluir o usuário "${username}"? Esta ação não pode ser desfeita.`,
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
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

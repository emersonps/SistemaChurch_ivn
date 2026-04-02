<?php include __DIR__ . '/layout_developer.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Permissões do Papel: <?= htmlspecialchars($roleData['label']) ?> (<?= $roleKey ?>)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/developer/users" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <form action="/developer/roles/edit/<?= $roleKey ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-4">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Você está editando as permissões base para o papel <strong><?= htmlspecialchars($roleData['label']) ?></strong>.
                    Qualquer alteração feita aqui afetará <strong>todos</strong> os usuários que possuem este papel.
                </div>
            </div>

            <?php
            $permissionSections = [];
            foreach ($permissionGroups as $group) {
                $permissionSections[$group['section']][] = $group;
            }
            $rolePermissionMap = array_fill_keys($rolePermissions, true);
            ?>
            <div class="mb-3">
                <label class="form-label fw-bold">Permissões do Papel</label>
                <?php foreach ($permissionSections as $sectionTitle => $sectionGroups): ?>
                    <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                        <div class="fw-bold text-uppercase text-muted small mb-3"><?= htmlspecialchars($sectionTitle) ?></div>
                        <?php foreach ($sectionGroups as $group): ?>
                            <div class="card mb-3 permission-group" data-parent-slug="<?= htmlspecialchars($group['parent_slug'] ?? '') ?>">
                                <div class="card-header bg-white">
                                    <strong><?= htmlspecialchars($group['title']) ?></strong>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($group['items'] as $perm): ?>
                                        <div class="form-check mb-2 permission-item">
                                            <input
                                                class="form-check-input permission-checkbox"
                                                type="checkbox"
                                                name="permissions[]"
                                                value="<?= htmlspecialchars($perm['slug']) ?>"
                                                id="perm_<?= $perm['id'] ?>"
                                                data-slug="<?= htmlspecialchars($perm['slug']) ?>"
                                                data-is-parent="<?= !empty($perm['is_parent']) ? '1' : '0' ?>"
                                                data-parent-slug="<?= !empty($perm['is_parent']) ? '' : htmlspecialchars($group['parent_slug'] ?? '') ?>"
                                                <?= isset($rolePermissionMap[$perm['slug']]) ? 'checked' : '' ?>
                                            >
                                            <label class="form-check-label" for="perm_<?= $perm['id'] ?>" title="<?= htmlspecialchars($perm['description'] ?? '') ?>">
                                                <?= htmlspecialchars($perm['label'] ?: getPermissionLabelFallback($perm['slug'])) ?>
                                                <span class="badge rounded-pill text-bg-light ms-1"><?= !empty($perm['is_parent']) ? 'Menu Pai' : 'Menu Filho' ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Salvar Permissões do Papel
            </button>
        </form>
    </div>
</div>

<script>
    const rolePermissionCheckboxes = document.querySelectorAll('.permission-checkbox');

    function syncRolePermissionParents() {
        document.querySelectorAll('.permission-group').forEach(group => {
            const parentSlug = group.getAttribute('data-parent-slug');
            if (!parentSlug) {
                return;
            }

            const parentCheckbox = group.querySelector('.permission-checkbox[data-slug="' + parentSlug + '"]');
            if (!parentCheckbox) {
                return;
            }

            const children = group.querySelectorAll('.permission-checkbox[data-parent-slug="' + parentSlug + '"]');
            let hasCheckedChild = false;
            children.forEach(child => {
                if (child.checked) {
                    hasCheckedChild = true;
                }
            });

            if (hasCheckedChild) {
                parentCheckbox.checked = true;
            }

            children.forEach(child => {
                if (!parentCheckbox.checked) {
                    child.checked = false;
                    child.disabled = true;
                } else {
                    child.disabled = false;
                }
            });
        });
    }

    rolePermissionCheckboxes.forEach(input => {
        input.addEventListener('change', function() {
            if (this.checked && this.dataset.parentSlug) {
                const parent = document.querySelector('.permission-checkbox[data-slug="' + this.dataset.parentSlug + '"]');
                if (parent) {
                    parent.checked = true;
                }
            }

            syncRolePermissionParents();
        });
    });

    syncRolePermissionParents();
</script>

<?php include __DIR__ . '/layout_footer.php'; ?>

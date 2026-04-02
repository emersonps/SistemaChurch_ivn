<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Usuário</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/users" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <form action="/admin/users/edit/<?= $user['id'] ?>" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="username" class="form-label">Nome de Usuário</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Senha (Deixe em branco para manter a atual)</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Função (Perfil)</label>
                <?php 
                $isDev = ($_SESSION['user_role'] ?? '') === 'developer';
                $isAdminTarget = $user['role'] === 'admin';
                $canEditRole = !($isAdminTarget && !$isDev);
                ?>
                <select class="form-select" id="role" name="role" <?= $canEditRole ? 'required' : 'disabled' ?>>
                    <?php foreach ($roles as $key => $role): ?>
                        <option value="<?= $key ?>" <?= $user['role'] == $key ? 'selected' : '' ?>><?= htmlspecialchars($role['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$canEditRole): ?>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
                    <div class="form-text text-danger">Apenas o desenvolvedor pode alterar a função de um Administrador.</div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="congregation_id" class="form-label">Congregação Vinculada (Opcional)</label>
                <select class="form-select" id="congregation_id" name="congregation_id">
                    <option value="">Todas (Geral)</option>
                    <?php foreach ($congregations as $congregation): ?>
                        <option value="<?= $congregation['id'] ?>" <?= $user['congregation_id'] == $congregation['id'] ? 'selected' : '' ?>><?= htmlspecialchars($congregation['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Selecione uma congregação para restringir o acesso deste usuário. Deixe em branco para acesso geral.</div>
            </div>

            <div class="mb-3">
                <label for="member_search" class="form-label">Vincular a Membros (Opcional)</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="member_search" list="members_list" placeholder="Digite para buscar e adicionar um membro..." autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="add_member_btn">Adicionar</button>
                </div>
                
                <?php 
                // Prepare initial JSON for existing linked members
                $initialMembers = [];
                if (!empty($linkedMembers)) {
                    foreach ($linkedMembers as $lm) {
                        $initialMembers[] = ['id' => $lm['id'], 'name' => $lm['name']];
                    }
                }
                ?>
                <input type="hidden" id="member_ids" name="member_ids" value='<?= json_encode(array_column($initialMembers, 'id')) ?>'>
                
                <div id="selected_members_container" class="d-flex flex-wrap gap-2 mt-2">
                    <!-- Selected members badges will appear here -->
                </div>
                
                <datalist id="members_list">
                    <?php foreach ($members as $m): ?>
                        <option data-id="<?= $m['id'] ?>" value="<?= htmlspecialchars($m['name']) ?>">
                    <?php endforeach; ?>
                </datalist>
                <div class="form-text">Você pode vincular este usuário a múltiplos membros (ex: casal pastoral).</div>
            </div>

            <?php
            $permissionSections = [];
            foreach ($permissionGroups as $group) {
                $permissionSections[$group['section']][] = $group;
            }
            $rolePermissionsMap = [];
            foreach ($roles as $roleKey => $roleData) {
                $rolePermissionsMap[$roleKey] = array_values(array_unique($roleData['permissions'] ?? []));
            }
            $explicitPermissionMap = array_fill_keys($userPermissions, true);
            $currentRolePermissions = $roles[$user['role']]['permissions'] ?? [];
            $adminEditablePermissionsMap = array_fill_keys($adminEditablePermissions, true);
            ?>
            <div class="mb-3">
                <label class="form-label fw-bold">Controle de Menus e Permissões</label>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="permission_mode" id="permission_mode_additive" value="additive" <?= empty($isOverride) ? 'checked' : '' ?> <?= ($canEditRole && ($isDeveloperEditor || !$permissionsLockedByOverride)) ? '' : 'disabled' ?>>
                                <label class="form-check-label" for="permission_mode_additive">
                                    Herdar permissões do perfil e adicionar extras
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="permission_mode" id="permission_mode_override" value="override" <?= !empty($isOverride) ? 'checked' : '' ?> <?= ($canEditRole && $isDeveloperEditor) ? '' : 'disabled' ?>>
                                <label class="form-check-label" for="permission_mode_override">
                                    Personalizar menus deste usuário
                                </label>
                            </div>
                            <?php if (!$canEditRole || !$isDeveloperEditor): ?>
                                <input type="hidden" name="permission_mode" value="<?= !empty($isOverride) ? 'override' : 'additive' ?>">
                            <?php endif; ?>
                            <div class="form-text mt-2">
                                <?php if (!$canEditRole): ?>
                                    <span class="text-danger">Apenas o desenvolvedor pode alterar as permissões de um Administrador.</span>
                                <?php elseif ($permissionsLockedByOverride): ?>
                                    <span class="text-danger">Este usuário está com permissões personalizadas pelo desenvolvedor. O admin não pode sobrescrevê-las.</span>
                                <?php elseif (!$isDeveloperEditor): ?>
                                    Como admin, você pode ajustar apenas as permissões de Configurações. Os demais recursos ficam sob definição do desenvolvedor.
                                <?php else: ?>
                                    No modo personalizado, você define exatamente quais menus pais e filhos este usuário verá.
                                <?php endif; ?>
                            </div>
                        </div>

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
                                                <?php
                                                $isChecked = !empty($isOverride)
                                                    ? isset($explicitPermissionMap[$perm['slug']])
                                                    : in_array($perm['slug'], $currentRolePermissions) || isset($explicitPermissionMap[$perm['slug']]);
                                                ?>
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
                                                        data-explicit="<?= isset($explicitPermissionMap[$perm['slug']]) ? '1' : '0' ?>"
                                                        data-admin-editable="<?= isset($adminEditablePermissionsMap[$perm['slug']]) ? '1' : '0' ?>"
                                                        <?= $isChecked ? 'checked' : '' ?>
                                                        <?= (!$canEditRole || $permissionsLockedByOverride) ? 'disabled' : '' ?>
                                                    >
                                                    <label class="form-check-label" for="perm_<?= $perm['id'] ?>" title="<?= htmlspecialchars($perm['description'] ?? '') ?>">
                                                        <?= htmlspecialchars($perm['label'] ?: getPermissionLabelFallback($perm['slug'])) ?>
                                                        <span class="badge rounded-pill text-bg-light ms-1"><?= !empty($perm['is_parent']) ? 'Menu Pai' : 'Menu Filho' ?></span>
                                                        <span class="badge rounded-pill text-bg-secondary ms-1 permission-origin d-none"></span>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </form>
    </div>
</div>

<script>
    // Logic to capture the ID from the datalist
    const memberInput = document.getElementById('member_search');
    const memberIdsInput = document.getElementById('member_ids');
    const memberList = document.getElementById('members_list');
    const addBtn = document.getElementById('add_member_btn');
    const container = document.getElementById('selected_members_container');
    
    // Initialize with PHP data
    let selectedMembers = <?= json_encode($initialMembers) ?>;
    const roleSelect = document.getElementById('role');
    const permissionModeInputs = document.querySelectorAll('input[name="permission_mode"]');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const rolePermissionsMap = <?= json_encode($rolePermissionsMap) ?>;
    const canEditPermissions = <?= $canEditRole ? 'true' : 'false' ?>;
    const isDeveloperEditor = <?= $isDeveloperEditor ? 'true' : 'false' ?>;
    const permissionsLockedByOverride = <?= $permissionsLockedByOverride ? 'true' : 'false' ?>;
    const manualPermissionState = {};

    function updateHiddenInput() {
        memberIdsInput.value = JSON.stringify(selectedMembers.map(m => m.id));
    }

    // Dynamic Member Filtering based on Congregation
    const congregationSelect = document.getElementById('congregation_id');
    
    congregationSelect.addEventListener('change', function() {
        const congId = this.value;
        const datalist = document.getElementById('members_list');
        
        // Clear current list
        datalist.innerHTML = '<option value="Carregando...">';
        memberInput.value = '';
        
        // Determine fetch URL
        let url = '/admin/users/members-by-congregation/' + (congId ? congId : 'all');
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                datalist.innerHTML = '';
                if (data.length === 0) {
                    const option = document.createElement('option');
                    option.value = "Nenhum membro encontrado nesta congregação";
                    datalist.appendChild(option);
                } else {
                    data.forEach(member => {
                        const option = document.createElement('option');
                        option.setAttribute('data-id', member.id);
                        option.value = member.name;
                        datalist.appendChild(option);
                    });
                }
            })
            .catch(err => {
                console.error('Erro ao buscar membros:', err);
                datalist.innerHTML = '<option value="Erro ao carregar membros">';
            });
    });

    // Trigger change on load if value is selected, but don't clear selected members
    // Only filter the dropdown for new additions
    if (congregationSelect.value) {
        congregationSelect.dispatchEvent(new Event('change'));
    }

    function renderBadges() {
        container.innerHTML = '';
        selectedMembers.forEach(member => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary d-flex align-items-center p-2';
            badge.innerHTML = `
                <i class="fas fa-user me-2"></i> ${member.name}
                <i class="fas fa-times ms-2 cursor-pointer" onclick="removeMember('${member.id}')" style="cursor: pointer;"></i>
            `;
            container.appendChild(badge);
        });
    }

    window.removeMember = function(id) {
        selectedMembers = selectedMembers.filter(m => m.id != id); // loose comparison for string/int IDs
        renderBadges();
        updateHiddenInput();
    };

    function addMember() {
        const val = memberInput.value;
        if (!val) return;

        const options = memberList.childNodes;
        let foundId = null;
        let foundName = null;

        for (let i = 0; i < options.length; i++) {
            if (options[i].value === val) {
                foundId = options[i].getAttribute('data-id');
                foundName = val;
                break;
            }
        }

        if (foundId) {
            // Check duplicates
            if (!selectedMembers.some(m => m.id == foundId)) {
                selectedMembers.push({ id: foundId, name: foundName });
                renderBadges();
                updateHiddenInput();
            }
            memberInput.value = '';
        }
    }

    addBtn.addEventListener('click', addMember);
    
    memberInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addMember();
        }
    });

    // Initial render
    renderBadges();

    function getPermissionMode() {
        const selected = document.querySelector('input[name="permission_mode"]:checked');
        return selected ? selected.value : 'additive';
    }

    function syncPermissionParentState() {
        const mode = getPermissionMode();
        const rolePerms = rolePermissionsMap[roleSelect.value] || [];

        document.querySelectorAll('.permission-group').forEach(group => {
            const parentSlug = group.getAttribute('data-parent-slug');
            if (!parentSlug) {
                return;
            }

            const parentCheckbox = group.querySelector('.permission-checkbox[data-slug="' + parentSlug + '"]');
            if (!parentCheckbox) {
                return;
            }

            const childCheckboxes = group.querySelectorAll('.permission-checkbox[data-parent-slug="' + parentSlug + '"]');
            let hasCheckedChild = false;
            childCheckboxes.forEach(child => {
                if (child.checked) {
                    hasCheckedChild = true;
                }
            });

            if (hasCheckedChild && !parentCheckbox.disabled) {
                parentCheckbox.checked = true;
                manualPermissionState[parentSlug] = true;
            }

            childCheckboxes.forEach(child => {
                const isRolePerm = rolePerms.includes(child.value);
                if (mode === 'additive' && isRolePerm && canEditPermissions) {
                    child.checked = true;
                    child.disabled = true;
                    return;
                }

                if (!parentCheckbox.checked && canEditPermissions) {
                    child.checked = false;
                    child.disabled = true;
                } else if (canEditPermissions) {
                    child.disabled = false;
                }
            });
        });
    }

    function syncPermissionUi() {
        const mode = getPermissionMode();
        const rolePerms = rolePermissionsMap[roleSelect.value] || [];

        permissionCheckboxes.forEach(input => {
            const slug = input.value;
            const isRolePerm = rolePerms.includes(slug);
            const badge = input.closest('.permission-item').querySelector('.permission-origin');
            const isAdminEditable = input.dataset.adminEditable === '1';

            if (!canEditPermissions) {
                badge.classList.add('d-none');
                return;
            }

            if (permissionsLockedByOverride) {
                input.disabled = true;
                badge.textContent = 'Definido pelo Desenvolvedor';
                badge.classList.remove('d-none');
                return;
            }

            if (!isDeveloperEditor && !isAdminEditable) {
                input.checked = isRolePerm || input.dataset.explicit === '1';
                input.disabled = true;
                badge.textContent = 'Gerenciado pelo Desenvolvedor';
                badge.classList.remove('d-none');
                return;
            }

            if (mode === 'additive' && isRolePerm) {
                input.checked = true;
                input.disabled = true;
                badge.textContent = 'Padrão do Perfil';
                badge.classList.remove('d-none');
            } else {
                if (Object.prototype.hasOwnProperty.call(manualPermissionState, slug)) {
                    input.checked = manualPermissionState[slug];
                } else {
                    input.checked = input.dataset.explicit === '1';
                }
                input.disabled = false;
                badge.classList.add('d-none');
                badge.textContent = '';
            }
        });

        syncPermissionParentState();
    }

    permissionCheckboxes.forEach(input => {
        input.addEventListener('change', function() {
            manualPermissionState[this.value] = this.checked;

            if (this.dataset.isParent === '1') {
                syncPermissionParentState();
                return;
            }

            if (this.checked && this.dataset.parentSlug) {
                const parent = document.querySelector('.permission-checkbox[data-slug="' + this.dataset.parentSlug + '"]');
                if (parent && !parent.disabled) {
                    parent.checked = true;
                    manualPermissionState[parent.value] = true;
                }
            }

            syncPermissionParentState();
        });
    });

    permissionModeInputs.forEach(input => input.addEventListener('change', syncPermissionUi));
    if (roleSelect) {
        roleSelect.addEventListener('change', syncPermissionUi);
    }
    syncPermissionUi();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

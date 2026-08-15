<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/users" class="text-decoration-none">Usuários</a></li>
                <li class="breadcrumb-item active">Novo</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Novo Usuário</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/users" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="userCreateForm" class="btn btn-dark rounded-pill fw-semibold px-3">Criar Usuário</button>
    </div>
</div>

<style>
    .member-form-topbar {
        position: sticky;
        top: 0;
        z-index: 1030;
        background: #f8f9fa;
        padding-bottom: .85rem;
    }
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
    .required-mark { color: #dc3545; }

    .member-summary-box .summary-label {
        font-size: .76rem;
        color: #868e96;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .member-summary-box .summary-value {
        font-weight: 700;
        color: #212529;
        margin-bottom: .9rem;
    }
    .member-summary-box .summary-value.text-muted-value { color: #adb5bd; font-weight: 500; }
    .member-summary-note {
        font-size: .8rem;
        color: #868e96;
    }
    .permission-section-title {
        font-weight: 700;
        text-transform: uppercase;
        font-size: .76rem;
        letter-spacing: .03em;
        color: #868e96;
        margin-bottom: .85rem;
    }
    .permission-section {
        border: 1px solid rgba(0,0,0,0.07);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #fafafa;
    }
    .permission-group {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: .85rem;
        background: #fff;
    }
    .permission-group-header {
        padding: .6rem .9rem;
        background: #fff;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        font-weight: 700;
        font-size: .9rem;
    }
    .permission-group-body { padding: .9rem; }
    .permission-mode-card {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #fafafa;
    }
</style>

<div class="row">
<div class="col-lg-8">
<form action="/admin/users/create" method="POST" class="app-form-with-bottom-actions" id="userCreateForm">
    <?= csrf_field() ?>

    <div class="member-form-card mb-3">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-user"></i></div>
            <div>
                <div class="member-form-card-title">Dados da Conta</div>
                <div class="member-form-card-subtitle">Usuário, senha e função de acesso.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="username" class="form-label">Nome de Usuário <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Senha <span class="required-mark">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="col-12">
                    <label for="role" class="form-label">Função (Perfil) <span class="required-mark">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <?php foreach ($roles as $key => $role): ?>
                            <option value="<?= $key ?>"><?= htmlspecialchars($role['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="member-form-card mb-3">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-link"></i></div>
            <div>
                <div class="member-form-card-title">Vínculo e Acesso</div>
                <div class="member-form-card-subtitle">Congregação e membros vinculados a esta conta.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label for="congregation_id" class="form-label">Congregação Vinculada (Opcional)</label>
                    <select class="form-select" id="congregation_id" name="congregation_id">
                        <option value="">Todas (Geral)</option>
                        <?php foreach ($congregations as $congregation): ?>
                            <option value="<?= $congregation['id'] ?>"><?= htmlspecialchars($congregation['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Selecione uma congregação para restringir o acesso deste usuário. Deixe em branco para acesso geral.</div>
                </div>

                <div class="col-12">
                    <label for="member_search" class="form-label">Vincular a Membros (Opcional)</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="member_search" list="members_list" placeholder="Digite para buscar e adicionar um membro..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="add_member_btn">Adicionar</button>
                    </div>

                    <input type="hidden" id="member_ids" name="member_ids" value="[]">

                    <div id="selected_members_container" class="d-flex flex-wrap gap-2 mt-2"></div>

                    <datalist id="members_list">
                        <?php foreach ($members as $m): ?>
                            <option data-id="<?= $m['id'] ?>" value="<?= htmlspecialchars($m['name']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <div class="form-text">Você pode vincular este usuário a múltiplos membros (ex: casal pastoral).</div>
                </div>
            </div>
        </div>
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
    $adminEditablePermissionsMap = array_fill_keys($adminEditablePermissions, true);
    ?>
    <div class="member-form-card mb-3">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-shield-halved"></i></div>
            <div>
                <div class="member-form-card-title">Controle de Menus e Permissões</div>
                <div class="member-form-card-subtitle">Defina o que este usuário pode ver e acessar.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="permission-mode-card">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="permission_mode" id="permission_mode_additive" value="additive" checked>
                    <label class="form-check-label" for="permission_mode_additive">
                        Herdar permissões do perfil e adicionar extras
                    </label>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="permission_mode" id="permission_mode_override" value="override" <?= $isDeveloperEditor ? '' : 'disabled' ?>>
                    <label class="form-check-label" for="permission_mode_override">
                        Personalizar menus deste usuário
                    </label>
                </div>
                <?php if (!$isDeveloperEditor): ?>
                    <input type="hidden" name="permission_mode" value="additive">
                <?php endif; ?>
                <div class="form-text mt-2">
                    <?php if ($isDeveloperEditor): ?>
                        No modo personalizado, você define exatamente quais menus pais e filhos este usuário verá.
                    <?php else: ?>
                        Como admin, você pode ajustar apenas as permissões de Configurações. Os demais recursos ficam sob definição do desenvolvedor.
                    <?php endif; ?>
                </div>
            </div>

            <?php foreach ($permissionSections as $sectionTitle => $sectionGroups): ?>
                <div class="permission-section">
                    <div class="permission-section-title"><?= htmlspecialchars($sectionTitle) ?></div>
                    <?php foreach ($sectionGroups as $group): ?>
                        <div class="permission-group" data-parent-slug="<?= htmlspecialchars($group['parent_slug'] ?? '') ?>">
                            <div class="permission-group-header"><?= htmlspecialchars($group['title']) ?></div>
                            <div class="permission-group-body">
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
                                            data-admin-editable="<?= isset($adminEditablePermissionsMap[$perm['slug']]) ? '1' : '0' ?>"
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

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/users" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Criar Usuário</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Usuário</div>
                <div class="summary-value text-muted-value" id="summaryUsername">—</div>

                <div class="summary-label">Função</div>
                <div class="summary-value" id="summaryRole"></div>

                <div class="summary-label">Congregação</div>
                <div class="summary-value mb-1" id="summaryCongregation">Geral (Todas)</div>

                <div class="summary-label">Membros Vinculados</div>
                <div class="summary-value mb-2" id="summaryMembers">Nenhum</div>

                <hr>
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Preenchimento</span>
                    <span id="summaryProgressPct">0%</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-dark" id="summaryProgressBar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <div class="member-form-card">
            <div class="member-form-card-body member-summary-note">
                Campos marcados com <span class="required-mark">*</span> são obrigatórios.
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Logic to capture the ID from the datalist
    const memberInput = document.getElementById('member_search');
    const memberIdsInput = document.getElementById('member_ids');
    const memberList = document.getElementById('members_list');
    const addBtn = document.getElementById('add_member_btn');
    const container = document.getElementById('selected_members_container');

    let selectedMembers = [];
    const roleSelect = document.getElementById('role');
    const permissionModeInputs = document.querySelectorAll('input[name="permission_mode"]');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const rolePermissionsMap = <?= json_encode($rolePermissionsMap) ?>;
    const isDeveloperEditor = <?= $isDeveloperEditor ? 'true' : 'false' ?>;
    const manualPermissionState = {};

    function updateHiddenInput() {
        memberIdsInput.value = JSON.stringify(selectedMembers.map(m => m.id));
        updateSummary();
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

    // Trigger change on load if value is selected (e.g. edit mode or pre-selected)
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
        selectedMembers = selectedMembers.filter(m => m.id !== id);
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
            if (!selectedMembers.some(m => m.id === foundId)) {
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
                if (mode === 'additive' && isRolePerm) {
                    child.checked = true;
                    child.disabled = true;
                    return;
                }

                if (!parentCheckbox.checked) {
                    child.checked = false;
                    child.disabled = true;
                } else {
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

            if (!isDeveloperEditor && !isAdminEditable) {
                input.checked = isRolePerm;
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
    roleSelect.addEventListener('change', function () {
        syncPermissionUi();
        updateSummary();
    });

    syncPermissionUi();

    // Summary sidebar
    const userForm = document.getElementById('userCreateForm');
    const summaryUsername = document.getElementById('summaryUsername');
    const summaryRole = document.getElementById('summaryRole');
    const summaryCongregation = document.getElementById('summaryCongregation');
    const summaryMembers = document.getElementById('summaryMembers');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');

    function updateSummary() {
        const usernameVal = userForm.querySelector('[name="username"]').value.trim();
        summaryUsername.textContent = usernameVal || '—';
        summaryUsername.classList.toggle('text-muted-value', !usernameVal);

        const roleOption = roleSelect.options[roleSelect.selectedIndex];
        summaryRole.textContent = roleOption ? roleOption.text : '—';

        const congOption = congregationSelect.options[congregationSelect.selectedIndex];
        summaryCongregation.textContent = (congOption && congOption.value) ? congOption.text : 'Geral (Todas)';

        summaryMembers.textContent = selectedMembers.length ? selectedMembers.map(m => m.name).join(', ') : 'Nenhum';

        const requiredFields = [userForm.querySelector('[name="username"]'), userForm.querySelector('[name="password"]'), roleSelect];
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = Math.round((filled / requiredFields.length) * 100);
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    userForm.addEventListener('input', updateSummary);
    userForm.addEventListener('change', updateSummary);
    updateSummary();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

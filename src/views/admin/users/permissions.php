<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Permissões do Sistema (RBAC)</h1>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .filter-card .form-control {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .filter-card .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
    .role-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        padding: 1.1rem 1.25rem;
        color: #fff;
        font-weight: 800;
        font-size: 1.02rem;
    }
    .role-card-header.role-admin { background: #b30000; }
    .role-card-header.role-developer { background: #212529; }
    .role-card-header.role-secretary { background: #0d6efd; }
    .role-card-header.role-accountant { background: #198754; }
    .role-card-header.role-treasurer { background: #b36b00; }
    .role-card-header.role-default { background: #6c757d; }
    .role-count-badge {
        background: rgba(255,255,255,0.18);
        color: #fff;
        font-size: .74rem;
        font-weight: 700;
        padding: .25rem .65rem;
        border-radius: 999px;
        white-space: nowrap;
    }
    .role-card-body {
        max-height: 440px;
        overflow-y: auto;
        padding: 1rem;
    }
    .role-card-body::-webkit-scrollbar { width: 8px; }
    .role-card-body::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 4px; }
    .permission-section-title {
        font-weight: 700;
        text-transform: uppercase;
        font-size: .72rem;
        letter-spacing: .03em;
        color: #868e96;
        margin-bottom: .6rem;
        margin-top: .9rem;
    }
    .permission-section-title:first-child { margin-top: 0; }
    .permission-group {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 10px;
        padding: .75rem .85rem;
        margin-bottom: .6rem;
        background: #fafafa;
    }
    .permission-group-title {
        font-weight: 700;
        font-size: .84rem;
        color: #343a40;
        margin-bottom: .4rem;
    }
    .permission-item-line {
        font-size: .82rem;
        color: #495057;
        padding: .15rem 0;
    }
    .permission-item-line i { color: #198754; }
    .no-results-note {
        font-size: .85rem;
        color: #adb5bd;
        text-align: center;
        padding: 1.5rem 0;
    }
</style>

<div class="alert alert-info d-flex align-items-start gap-2">
    <i class="fas fa-info-circle mt-1"></i>
    <div>Esta tela é apenas para visualização. As permissões padrão de cada perfil são definidas na arquitetura do sistema para garantir a segurança da plataforma.</div>
</div>

<div class="member-form-card filter-card mb-3">
    <div class="p-3">
        <input type="search" class="form-control" id="permissionsSearch" placeholder="Pesquisar por permissão, menu ou seção..." autocomplete="off">
    </div>
</div>

<?php
function permissionRoleHeaderClass($roleKey) {
    $known = ['admin', 'developer', 'secretary', 'accountant', 'treasurer'];
    return in_array($roleKey, $known, true) ? 'role-' . $roleKey : 'role-default';
}
?>

<div class="row">
    <?php foreach ($roles as $roleKey => $roleData): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="member-form-card h-100">
            <div class="role-card-header <?= permissionRoleHeaderClass($roleKey) ?>">
                <span><?= htmlspecialchars($roleData['label']) ?></span>
                <span class="role-count-badge"><?= count($roleData['permissions']) ?> Permissões</span>
            </div>
            <div class="role-card-body role-permission-card" data-role-name="<?= htmlspecialchars(mb_strtolower($roleData['label'], 'UTF-8')) ?>">
                <?php
                $rolePermsMap = array_fill_keys($roleData['permissions'], true);
                $groupedSections = [];
                foreach ($permissionGroups as $group) {
                    $filteredItems = array_values(array_filter($group['items'], function($item) use ($rolePermsMap) {
                        return isset($rolePermsMap[$item['slug']]);
                    }));
                    if (!empty($filteredItems)) {
                        $groupCopy = $group;
                        $groupCopy['items'] = $filteredItems;
                        $groupedSections[$group['section']][] = $groupCopy;
                    }
                }
                ?>
                <?php if (empty($groupedSections)): ?>
                    <div class="no-results-note">Nenhuma permissão atribuída a este perfil.</div>
                <?php else: ?>
                    <?php foreach ($groupedSections as $sectionTitle => $sectionGroups): ?>
                        <div class="permission-section-title"><?= htmlspecialchars($sectionTitle) ?></div>
                        <?php foreach ($sectionGroups as $group): ?>
                            <div class="permission-group">
                                <div class="permission-group-title"><?= htmlspecialchars($group['title']) ?></div>
                                <?php foreach ($group['items'] as $item): ?>
                                    <div class="permission-item-line" data-search="<?= htmlspecialchars(mb_strtolower($item['label'] . ' ' . $group['title'] . ' ' . $sectionTitle, 'UTF-8')) ?>">
                                        <i class="fas fa-check me-2"></i><?= htmlspecialchars($item['label']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="no-results-note d-none search-empty-note">Nenhuma permissão encontrada para esta pesquisa.</div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('permissionsSearch');
    if (!input) return;

    function normalize(v) {
        return String(v || '').toLowerCase().trim();
    }

    function filter() {
        var q = normalize(input.value);

        document.querySelectorAll('.role-permission-card').forEach(function (card) {
            var anyVisible = false;

            card.querySelectorAll('.permission-group').forEach(function (group) {
                var groupHasMatch = false;

                group.querySelectorAll('.permission-item-line').forEach(function (line) {
                    var hay = line.getAttribute('data-search') || '';
                    var match = q === '' || hay.indexOf(q) !== -1;
                    line.style.display = match ? '' : 'none';
                    if (match) groupHasMatch = true;
                });

                group.style.display = groupHasMatch ? '' : 'none';
                if (groupHasMatch) anyVisible = true;
            });

            card.querySelectorAll('.permission-section-title').forEach(function (title) {
                var section = title.nextElementSibling;
                var sectionHasVisible = false;
                while (section && section.classList && section.classList.contains('permission-group')) {
                    if (section.style.display !== 'none') sectionHasVisible = true;
                    section = section.nextElementSibling;
                }
                title.style.display = sectionHasVisible ? '' : 'none';
            });

            var emptyNote = card.querySelector('.search-empty-note');
            if (emptyNote) {
                emptyNote.classList.toggle('d-none', q === '' || anyVisible);
            }
        });
    }

    input.addEventListener('input', filter);
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

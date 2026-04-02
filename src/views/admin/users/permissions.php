<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Permissões do Sistema (RBAC)</h1>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i> Esta tela é apenas para visualização. As permissões padrão de cada perfil são definidas na arquitetura do sistema para garantir a segurança da plataforma.
</div>

<div class="row">
    <?php foreach ($roles as $roleKey => $roleData): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($roleData['label']) ?>
                <span class="badge bg-light text-dark"><?= count($roleData['permissions']) ?> Permissões</span>
            </div>
            <div class="card-body p-0">
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
                <div class="p-3" style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($groupedSections as $sectionTitle => $sectionGroups): ?>
                        <div class="small text-uppercase text-muted fw-bold mb-2"><?= htmlspecialchars($sectionTitle) ?></div>
                        <?php foreach ($sectionGroups as $group): ?>
                            <div class="border rounded p-2 mb-2">
                                <div class="fw-semibold small mb-2"><?= htmlspecialchars($group['title']) ?></div>
                                <?php foreach ($group['items'] as $item): ?>
                                    <div class="small mb-1">
                                        <i class="fas fa-check text-success me-2"></i><?= htmlspecialchars($item['label']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

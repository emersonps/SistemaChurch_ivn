<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Grupos e Células</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if (hasPermission('groups.manage')): ?>
        <a href="/admin/groups/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Novo Grupo
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4 bg-light">
    <div class="card-body py-2">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-md-4">
                <select name="congregation_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todas as Congregações</option>
                    <?php foreach ($congregations as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (isset($_GET['congregation_id']) && $_GET['congregation_id'] == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Buscar grupo ou líder..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="col-md-2">
                <a href="/admin/groups" class="btn btn-sm btn-outline-secondary w-100" title="Limpar Filtros">
                    <i class="fas fa-eraser"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <?php if (empty($groups)): ?>
        <div class="col-12 text-center text-muted py-5">
            <i class="fas fa-users fa-3x mb-3 text-secondary"></i>
            <p>Nenhum grupo encontrado.</p>
        </div>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title text-primary mb-0"><?= htmlspecialchars((string)$group['name']) ?></h5>
                        <span class="badge bg-light text-dark border"><?= $group['total_members'] ?> membros</span>
                    </div>
                    
                    <p class="card-text small text-muted mb-2">
                        <i class="fas fa-church me-1"></i> <?= htmlspecialchars((string)($group['congregation_name'] ?? 'Sem Congregação')) ?>
                    </p>
                    
                    <?php if ($group['leader_name']): ?>
                    <p class="card-text small mb-1">
                        <strong>Líder:</strong> <?= htmlspecialchars($group['leader_name']) ?>
                    </p>
                    <?php endif; ?>
                    
                    <p class="card-text small mb-3">
                        <i class="far fa-clock me-1"></i> 
                        <?= htmlspecialchars((string)$group['meeting_day']) ?>
                        <?php if (!empty($group['meeting_time'])): ?>
                             às <?= substr($group['meeting_time'], 0, 5) ?>
                        <?php endif; ?>
                    </p>
                    
                    <div class="d-grid">
                        <a href="/admin/groups/show/<?= $group['id'] ?>" class="btn btn-sm btn-outline-primary">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

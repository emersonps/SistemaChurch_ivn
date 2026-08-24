<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Grupos e Células</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if (hasPermission('groups.manage')): ?>
        <a href="/admin/groups/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Novo Grupo
        </a>
        <?php endif; ?>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .member-form-card-body { padding: 1.25rem; }
    .group-card-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
    }
    .count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .7rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
        white-space: nowrap;
    }
    .group-meta {
        font-size: .85rem;
        color: #6c757d;
    }
    .group-meta i { color: #b30000; width: 16px; }
    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<!-- Filtros -->
<div class="member-form-card filter-card mb-4">
    <div class="member-form-card-body py-3">
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
                <a href="/admin/groups" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold w-100" title="Limpar Filtros">
                    <i class="fas fa-eraser me-1"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php if (empty($groups)): ?>
        <div class="col-12">
            <div class="member-form-card">
                <div class="member-form-card-body text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum grupo encontrado.</h5>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
        <div class="col-md-4">
            <div class="member-form-card h-100">
                <div class="member-form-card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                        <div class="group-card-title"><?= htmlspecialchars((string)$group['name']) ?></div>
                        <span class="count-pill"><?= $group['total_members'] ?> membros</span>
                    </div>

                    <div class="group-meta mb-1">
                        <i class="fas fa-church me-1"></i> <?= htmlspecialchars((string)($group['congregation_name'] ?? 'Sem Congregação')) ?>
                    </div>

                    <?php if ($group['leader_name']): ?>
                    <div class="group-meta mb-1">
                        <i class="fas fa-user-tie me-1"></i> <strong class="text-dark"><?= htmlspecialchars($group['leader_name']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <div class="group-meta mb-1">
                        <i class="far fa-clock me-1"></i>
                        <?= htmlspecialchars((string)$group['meeting_day']) ?>
                        <?php if (!empty($group['meeting_time'])): ?>
                             às <?= substr($group['meeting_time'], 0, 5) ?>
                        <?php endif; ?>
                    </div>

                    <div class="group-meta mb-3">
                        <i class="fas fa-calendar-plus me-1"></i>
                        Criado em <?= !empty($group['created_at']) ? date('d/m/Y H:i', strtotime($group['created_at'])) : '—' ?>
                    </div>

                    <a href="/admin/groups/show/<?= $group['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold mt-auto">
                        Ver Detalhes
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

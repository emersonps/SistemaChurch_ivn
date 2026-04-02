<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Escola Bíblica Dominical (EBD)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/ebd/reports" class="btn btn-sm btn-outline-info me-2">
            <i class="fas fa-chart-bar me-1"></i> Relatórios
        </a>
        <a href="/admin/ebd/classes/create" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus"></i> Nova Classe
        </a>
    </div>
</div>

<div class="row">
    <?php foreach ($classes as $class): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="card-title text-primary mb-0"><?= htmlspecialchars($class['name']) ?></h5>
                    <span class="badge bg-success"><?= $class['status'] == 'active' ? 'Ativa' : 'Inativa' ?></span>
                </div>
                
                <h6 class="card-subtitle mb-2 text-muted small">
                    <i class="fas fa-church me-1"></i> <?= htmlspecialchars($class['congregation_name'] ?? 'Todas') ?>
                </h6>
                
                <p class="card-text text-muted small">
                    <?= htmlspecialchars((string)$class['description']) ?>
                    <br>
                    <strong>Faixa Etária:</strong> <?= $class['min_age'] ?? 0 ?> a <?= $class['max_age'] ?? 99 ?> anos
                    <br>
                    <strong>Professor(es):</strong> <?= htmlspecialchars($class['teachers_names'] ?? 'Nenhum') ?>
                </p>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted small"><i class="fas fa-users me-1"></i> <?= $class['students_count'] ?> alunos</span>
                    <div>
                        <a href="/admin/ebd/classes/delete/<?= $class['id'] ?>" class="btn btn-sm btn-outline-danger me-1" onclick="return confirm('Tem certeza que deseja excluir esta classe? Se houver alunos ou aulas, a exclusão será bloqueada.')" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </a>
                        <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-sm btn-outline-secondary">Gerenciar</a>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <div class="d-grid">
                    <a href="/admin/ebd/lessons/create/<?= $class['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-clipboard-check me-1"></i> Lançar Aula/Chamada
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($classes)): ?>
    <div class="col-12 text-center py-5">
        <p class="text-muted">Nenhuma classe cadastrada.</p>
        <a href="/admin/ebd/classes/create" class="btn btn-primary">Cadastrar Primeira Classe</a>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

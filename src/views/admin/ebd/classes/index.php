<?php $suppressMobileTopbar = true; include __DIR__ . '/../../../layout/header.php'; ?>

<?php include __DIR__ . '/_mobile_list.php'; ?>

<div class="d-none d-lg-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Escola Bíblica Dominical (EBD)</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/admin/ebd/reports" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-chart-bar me-1"></i> Relatórios
        </a>
        <a href="/admin/ebd/classes/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Nova Classe
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
    .class-card-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .3rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }
    .status-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        flex: 0 0 auto;
    }
    .status-pill.is-active { background: rgba(25,135,84,0.12); color: #198754; }
    .status-pill.is-inactive { background: rgba(0,0,0,0.06); color: #6c757d; }
    .class-meta {
        font-size: .85rem;
        color: #6c757d;
    }
    .class-meta i { color: #b30000; width: 16px; }
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

<div class="row g-3 d-none d-lg-flex">
    <?php foreach ($classes as $class): ?>
    <div class="col-md-4">
        <div class="member-form-card h-100 d-flex flex-column">
            <div class="p-3 flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                    <div class="class-card-title"><?= htmlspecialchars($class['name']) ?></div>
                    <span class="status-pill <?= $class['status'] == 'active' ? 'is-active' : 'is-inactive' ?>">
                        <?= $class['status'] == 'active' ? 'Ativa' : 'Inativa' ?>
                    </span>
                </div>

                <div class="class-meta mb-2">
                    <i class="fas fa-church me-1"></i> <?= htmlspecialchars($class['congregation_name'] ?? 'Todas') ?>
                </div>

                <p class="text-muted small mb-2"><?= htmlspecialchars((string)$class['description']) ?></p>

                <div class="class-meta mb-1"><strong class="text-dark">Faixa Etária:</strong> <?= $class['min_age'] ?? 0 ?> a <?= $class['max_age'] ?? 99 ?> anos</div>
                <div class="class-meta mb-1"><strong class="text-dark">Professor(es):</strong> <?= htmlspecialchars($class['teachers_names'] ?? 'Nenhum') ?></div>
                <div class="class-meta mb-3"><i class="far fa-clock me-1"></i> Criado em <?= !empty($class['created_at']) ? date('d/m/Y H:i', strtotime($class['created_at'])) : '—' ?></div>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="class-meta"><i class="fas fa-users me-1"></i> <?= $class['students_count'] ?> alunos</span>
                    <div>
                        <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">Gerenciar</a>
                        <a href="/admin/ebd/classes/delete/<?= $class['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-class" data-name="<?= htmlspecialchars($class['name']) ?>" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="p-3 pt-0">
                <a href="/admin/ebd/lessons/create/<?= $class['id'] ?>" class="btn btn-sm btn-primary rounded-pill fw-semibold w-100">
                    <i class="fas fa-clipboard-check me-1"></i> Lançar Aula/Chamada
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($classes)): ?>
    <div class="col-12">
        <div class="member-form-card">
            <div class="p-5 text-center">
                <i class="fas fa-book-reader fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhuma classe cadastrada.</h5>
                <a href="/admin/ebd/classes/create" class="btn btn-primary rounded-pill fw-semibold px-4 mt-2">Cadastrar Primeira Classe</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.btn-delete-class').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const name = btn.getAttribute('data-name');
        Swal.fire({
            title: 'Excluir classe?',
            text: `Tem certeza que deseja excluir "${name}"? Se houver alunos ou aulas, a exclusão será bloqueada.`,
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

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

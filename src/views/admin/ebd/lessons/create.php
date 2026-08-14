<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/ebd/classes" class="text-decoration-none">EBD</a></li>
                <li class="breadcrumb-item"><a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($class['name']) ?></a></li>
                <li class="breadcrumb-item active">Nova Aula</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Lançar Aula — <?= htmlspecialchars($class['name']) ?></h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="ebdLessonForm" class="btn btn-success rounded-pill fw-semibold px-3">
            <i class="fas fa-check-circle me-1"></i> Finalizar Aula
        </button>
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
    .member-form-card-header-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
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
    .member-form-card-body .form-control {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }

    .count-pill {
        display: inline-flex;
        align-items: center;
        padding: .3rem .7rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        background: rgba(179,0,0,0.10);
        color: #b30000;
    }
    .attendance-table thead th {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .attendance-table td {
        vertical-align: middle;
        padding-top: .6rem;
        padding-bottom: .6rem;
    }
    .attendance-table tr { cursor: pointer; }
    .present-badge {
        display: inline-block;
        padding: .25rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }
    .present-badge.bg-success { background: rgba(25,135,84,0.12) !important; color: #198754; }
    .present-badge.bg-secondary { background: rgba(0,0,0,0.06) !important; color: #6c757d; }
    .role-pill {
        display: inline-block;
        padding: .2rem .55rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        background: rgba(13,110,253,0.10);
        color: #0d6efd;
    }
</style>

<form action="/admin/ebd/lessons/create/<?= $class['id'] ?>" method="POST" class="app-form-with-bottom-actions" id="ebdLessonForm">
    <?= csrf_field() ?>
    <div class="row g-3">
        <!-- Detalhes da Aula -->
        <div class="col-md-4">
            <div class="member-form-card">
                <div class="member-form-card-header">
                    <div class="member-form-badge"><i class="fas fa-clipboard-list"></i></div>
                    <div>
                        <div class="member-form-card-header-title">Dados da Aula</div>
                    </div>
                </div>
                <div class="member-form-card-body">
                    <div class="mb-3">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tema da Lição</label>
                        <input type="text" class="form-control" name="topic" placeholder="Ex: Lição 5 - A Fé de Abraão">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">Oferta da Classe (R$)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" class="form-control" name="offerings" placeholder="0.00">
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">Visitantes</label>
                            <input type="number" class="form-control form-control-sm" name="visitors" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Bíblias</label>
                            <input type="number" class="form-control form-control-sm" name="bibles" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Revistas</label>
                            <input type="number" class="form-control form-control-sm" name="magazines" value="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chamada -->
        <div class="col-md-8">
            <div class="member-form-card">
                <div class="member-form-card-header">
                    <div class="member-form-badge"><i class="fas fa-clipboard-check"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="member-form-card-header-title">Lista de Presença</div>
                            <span class="count-pill"><?= count($students) ?> Alunos</span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover attendance-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll" checked>
                                    </div>
                                </th>
                                <th>Aluno</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr onclick="document.getElementById('check_<?= $student['student_record_id'] ?>').click()">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input attendance-check" type="checkbox"
                                               name="attendance[<?= $student['student_record_id'] ?>]"
                                               id="check_<?= $student['student_record_id'] ?>"
                                               value="1" checked>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">
                                        <?= htmlspecialchars($student['name']) ?>
                                        <?php if (!empty($student['is_teacher'])): ?>
                                            <span class="role-pill ms-2">Professor</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="present-badge bg-success" id="badge_<?= $student['student_record_id'] ?>">Presente</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                                    <p class="mb-2">Nenhum aluno matriculado nesta classe.</p>
                                    <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">Matricular Alunos</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-success px-4"><i class="fas fa-check-circle me-1"></i> Finalizar</button>
    </div>
</form>

<script>
    // Script para selecionar todos
    document.getElementById('checkAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.attendance-check');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            updateBadge(cb);
        });
    });

    // Script para atualizar badge visualmente
    document.querySelectorAll('.attendance-check').forEach(cb => {
        cb.addEventListener('change', function() {
            updateBadge(this);
        });
        // Click na linha (tr) já dispara o click no checkbox via HTML, mas precisamos parar propagação se clicar direto no checkbox
        cb.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    function updateBadge(checkbox) {
        const id = checkbox.id.replace('check_', '');
        const badge = document.getElementById('badge_' + id);
        if (checkbox.checked) {
            badge.className = 'present-badge bg-success';
            badge.innerText = 'Presente';
        } else {
            badge.className = 'present-badge bg-secondary';
            badge.innerText = 'Ausente';
        }
    }
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

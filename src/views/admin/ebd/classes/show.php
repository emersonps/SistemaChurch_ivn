<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/ebd/classes" class="text-decoration-none">EBD</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($class['name']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><?= htmlspecialchars($class['name']) ?></h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/admin/ebd/lessons/create/<?= $class['id'] ?>" class="btn btn-sm btn-success rounded-pill fw-semibold px-3">
            <i class="fas fa-plus-circle me-1"></i> Nova Aula/Chamada
        </a>
        <a href="/admin/ebd/classes/edit/<?= $class['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-edit me-1"></i> Editar Classe
        </a>
        <a href="/admin/ebd/classes" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
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
    .member-form-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-card-header-title {
        font-weight: 800;
        font-size: .98rem;
        color: #1a1a1a;
    }
    .member-form-card-body { padding: 1.25rem; }

    .info-field-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .55rem 0;
        border-top: 1px solid rgba(0,0,0,0.05);
        font-size: .88rem;
    }
    .info-field-row:first-child { border-top: none; padding-top: 0; }
    .info-field-row .label { color: #868e96; }
    .info-field-row .value { font-weight: 700; color: #212529; }

    .status-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
    }
    .status-pill.is-active { background: rgba(25,135,84,0.12); color: #198754; }
    .status-pill.is-inactive { background: rgba(0,0,0,0.06); color: #6c757d; }
    .count-pill {
        display: inline-flex;
        align-items: center;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }

    .teacher-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .6rem 1.25rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        font-size: .9rem;
    }
    .teacher-list-item:first-child { border-top: none; }

    #ebdTabs.nav-tabs {
        border-bottom: none;
        gap: .4rem;
    }
    #ebdTabs.nav-tabs .nav-link {
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 999px;
        padding: .45rem 1rem;
        font-weight: 700;
        font-size: .85rem;
        color: #495057;
        background: #fff;
    }
    #ebdTabs.nav-tabs .nav-link.active {
        background: #b30000;
        border-color: #b30000;
        color: #fff;
    }

    .ebd-table thead th {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .ebd-table td {
        vertical-align: middle;
        padding-top: .6rem;
        padding-bottom: .6rem;
    }
    .role-pill {
        display: inline-block;
        padding: .2rem .55rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        background: rgba(13,110,253,0.10);
        color: #0d6efd;
    }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    .modal-content { border-radius: 16px; border: none; }
    .modal-header { border-bottom: 1px solid rgba(0,0,0,0.07); }
    .modal-footer { border-top: 1px solid rgba(0,0,0,0.07); }
    .modal-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .modal-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .modal-body .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<div class="row g-3">
    <!-- Informações -->
    <div class="col-md-4">
        <div class="member-form-card mb-3">
            <div class="member-form-card-header">
                <div class="member-form-card-header-title">Detalhes</div>
            </div>
            <div class="member-form-card-body">
                <p class="text-muted small"><?= htmlspecialchars((string)$class['description']) ?></p>
                <div class="info-field-row">
                    <span class="label">Congregação</span>
                    <span class="value"><?= htmlspecialchars($class['congregation_name'] ?? 'Todas') ?></span>
                </div>
                <div class="info-field-row">
                    <span class="label">Faixa Etária</span>
                    <span class="value"><?= $class['min_age'] ?> - <?= $class['max_age'] ?> anos</span>
                </div>
                <div class="info-field-row">
                    <span class="label">Alunos Matriculados</span>
                    <span class="count-pill"><?= count($students) ?></span>
                </div>
                <div class="info-field-row">
                    <span class="label">Status</span>
                    <span class="status-pill <?= $class['status'] == 'active' ? 'is-active' : 'is-inactive' ?>"><?= ucfirst($class['status']) ?></span>
                </div>
            </div>
        </div>

        <!-- Professores -->
        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-card-header-title">Professores</div>
                <button class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                    <i class="fas fa-plus me-1"></i> Adicionar
                </button>
            </div>
            <div>
                <?php foreach ($teachers as $teacher): ?>
                <div class="teacher-list-item">
                    <div>
                        <i class="fas fa-chalkboard-teacher text-muted me-2"></i>
                        <?= htmlspecialchars($teacher['member_name']) ?>
                    </div>
                    <a href="/admin/ebd/teachers/remove/<?= $teacher['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-remove-teacher" data-name="<?= htmlspecialchars($teacher['member_name']) ?>" title="Remover">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php if (empty($teachers)): ?>
                <div class="text-muted small text-center py-3">Nenhum professor atribuído.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alunos / Aulas -->
    <div class="col-md-8">
        <ul class="nav nav-tabs mb-3" id="ebdTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">Alunos (<?= count($students) ?>)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="lessons-tab" data-bs-toggle="tab" data-bs-target="#lessons" type="button" role="tab">Histórico de Aulas</button>
            </li>
        </ul>

        <div class="tab-content" id="ebdTabsContent">
            <!-- Aba Alunos -->
            <div class="tab-pane fade show active" id="students" role="tabpanel">
                <div class="member-form-card">
                    <div class="member-form-card-header">
                        <div class="member-form-card-header-title">Lista de Alunos</div>
                        <button class="btn btn-sm btn-primary rounded-pill fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="fas fa-user-plus me-1"></i> Matricular Aluno
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover ebd-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Idade</th>
                                    <th>Data Matrícula</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light me-2 d-flex align-items-center justify-content-center text-secondary" style="width: 32px; height: 32px;">
                                                <i class="fas fa-user small"></i>
                                            </div>
                                            <span class="fw-bold"><?= htmlspecialchars($student['member_name']) ?></span>
                                            <?php if (!empty($student['is_teacher'])): ?>
                                                <span class="role-pill ms-2">Professor</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                            if (!empty($student['birth_date'])) {
                                                $dob = new DateTime($student['birth_date']);
                                                $now = new DateTime();
                                                echo $now->diff($dob)->y . ' anos';
                                            } else {
                                                echo '-';
                                            }
                                        ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($student['enrolled_at'])) ?></td>
                                    <td class="text-end">
                                        <a href="/admin/ebd/students/remove/<?= $student['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-remove-student" data-name="<?= htmlspecialchars($student['member_name']) ?>" title="Remover">
                                            <i class="fas fa-user-minus"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Nenhum aluno matriculado nesta classe.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Aba Aulas -->
            <div class="tab-pane fade" id="lessons" role="tabpanel">
                <div class="member-form-card">
                    <div class="table-responsive">
                        <table class="table table-hover ebd-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tema</th>
                                    <th>Presentes</th>
                                    <th>Oferta</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lessons as $lesson): ?>
                                <tr>
                                    <td class="fw-bold"><?= date('d/m/Y', strtotime($lesson['lesson_date'])) ?></td>
                                    <td><?= htmlspecialchars($lesson['topic']) ?></td>
                                    <td>
                                        <span class="count-pill">
                                            <?=
                                                (new Database())->connect()->query("SELECT COUNT(*) FROM ebd_attendance WHERE lesson_id = {$lesson['id']} AND present = 1")->fetchColumn()
                                            ?>
                                        </span>
                                    </td>
                                    <td>R$ <?= number_format($lesson['offerings'], 2, ',', '.') ?></td>
                                    <td class="text-end">
                                        <a href="/admin/ebd/lessons/show/<?= $lesson['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                                        <a href="/admin/ebd/lessons/edit/<?= $lesson['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn" title="Editar"><i class="fas fa-edit"></i></a>
                                        <a href="/admin/ebd/lessons/delete/<?= $lesson['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-lesson" data-topic="<?= htmlspecialchars($lesson['topic']) ?>" title="Excluir"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($lessons)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Nenhuma aula registrada.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Professor -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/admin/ebd/classes/assign-teacher/<?= $class['id'] ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Adicionar Professor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Selecione o Membro</label>
                        <select class="form-select" name="member_id" required>
                            <option value="">Buscar...</option>
                            <?php foreach ($ebd_teachers_list as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Apenas membros marcados como "Professor de EBD" aparecem aqui.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold px-3">Adicionar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Adicionar Aluno -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/admin/ebd/classes/enroll/<?= $class['id'] ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Matricular Aluno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Selecione o Membro</label>
                        <select class="form-select" name="member_id" required>
                            <option value="">Buscar...</option>
                            <?php foreach ($all_members as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold px-3">Matricular</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.btn-remove-teacher').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const name = btn.getAttribute('data-name');
        Swal.fire({
            title: 'Remover professor?',
            text: `Remover "${name}" desta classe?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = href;
        });
    });
});

document.querySelectorAll('.btn-remove-student').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const name = btn.getAttribute('data-name');
        Swal.fire({
            title: 'Remover aluno?',
            text: `Remover "${name}" desta classe?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = href;
        });
    });
});

document.querySelectorAll('.btn-delete-lesson').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const topic = btn.getAttribute('data-topic');
        Swal.fire({
            title: 'Excluir aula?',
            text: `Tem certeza que deseja excluir a aula "${topic}"? O registro financeiro (se houver) não será excluído automaticamente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = href;
        });
    });
});
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($class['name']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/ebd/lessons/create/<?= $class['id'] ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-plus-circle me-1"></i> Nova Aula/Chamada
            </a>
            <a href="/admin/ebd/classes/edit/<?= $class['id'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-edit me-1"></i> Editar Classe
            </a>
        </div>
        <a href="/admin/ebd/classes" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- Informações -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="card-title">Detalhes</h5>
                <p class="card-text text-muted"><?= htmlspecialchars((string)$class['description']) ?></p>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        Congregação
                        <span><?= htmlspecialchars($class['congregation_name'] ?? 'Todas') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        Faixa Etária
                        <span><?= $class['min_age'] ?> - <?= $class['max_age'] ?> anos</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        Alunos Matriculados
                        <span class="badge bg-primary rounded-pill"><?= count($students) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        Status
                        <span class="badge bg-<?= $class['status'] == 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($class['status']) ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Professores -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Professores</h6>
                <button class="btn btn-sm btn-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($teachers as $teacher): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-chalkboard-teacher text-muted me-2"></i>
                        <?= htmlspecialchars($teacher['member_name']) ?>
                    </div>
                    <a href="/admin/ebd/teachers/remove/<?= $teacher['id'] ?>" class="text-danger small" onclick="return confirm('Remover professor?')">
                        <i class="fas fa-times"></i>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php if (empty($teachers)): ?>
                <li class="list-group-item text-muted small text-center">Nenhum professor atribuído.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Alunos -->
    <div class="col-md-8">
        <!-- Abas -->
        <?php $tabTotal = 2; ?>
        <style>
            @media (max-width: 991.98px) {
                .ebd-class-tabs-carousel {
                    position: relative;
                }
                .ebd-class-tabs-carousel.multi::before {
                    content: '';
                    position: absolute;
                    inset: 0 0 auto 0;
                    height: 4px;
                    background: linear-gradient(90deg, #0d6efd 0%, #6610f2 55%, #d4af37 100%);
                    z-index: 2;
                }
                .ebd-class-tabs-carousel.multi #myTabContent {
                    display: flex;
                    gap: 0;
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    scroll-behavior: smooth;
                    scrollbar-width: none;
                    padding: .25rem .25rem .35rem;
                }
                .ebd-class-tabs-carousel.multi #myTabContent::-webkit-scrollbar { display: none; }
                .ebd-class-tabs-carousel.multi #myTabContent > .tab-pane {
                    display: block !important;
                    flex: 0 0 100%;
                    min-width: 100%;
                    scroll-snap-align: center;
                    opacity: 1 !important;
                    padding: .35rem;
                }
                .ebd-class-tabs-carousel.multi #myTabContent > .tab-pane.fade { transition: none; }
                .ebd-class-pane-head {
                    border-radius: 16px 16px 0 0;
                    border: 1px solid rgba(0,0,0,0.08);
                    border-bottom: 0;
                    background: linear-gradient(135deg, rgba(13,110,253,0.10), rgba(102,16,242,0.12));
                    padding: .9rem 1rem;
                }
                .ebd-class-pane-title {
                    font-weight: 900;
                    font-size: 1.05rem;
                    letter-spacing: .01em;
                    color: #1b1b2a;
                }
                .ebd-class-pane-hint {
                    font-size: .72rem;
                    letter-spacing: .08em;
                    font-weight: 800;
                    color: rgba(0,0,0,0.52);
                    text-transform: uppercase;
                }
                .ebd-class-pane-hint i {
                    color: #6610f2;
                }
                .ebd-class-pane-body {
                    border-radius: 0 0 16px 16px;
                    border: 1px solid rgba(0,0,0,0.08);
                    overflow: hidden;
                    background: #fff;
                }
            }
        </style>

        <ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">Alunos (<?= count($students) ?>)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="lessons-tab" data-bs-toggle="tab" data-bs-target="#lessons" type="button" role="tab">Histórico de Aulas</button>
            </li>
        </ul>
        
        <div class="ebd-class-tabs-carousel multi">
        <div class="tab-content" id="myTabContent">
            <!-- Aba Alunos -->
            <div class="tab-pane fade show active" id="students" role="tabpanel">
                <div class="d-lg-none ebd-class-pane-head">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <div class="ebd-class-pane-title"><i class="fas fa-user-graduate me-2"></i>Alunos</div>
                            <div class="ebd-class-pane-hint mt-1"><i class="fas fa-arrows-left-right me-2"></i>Deslize para mudar (1/<?= $tabTotal ?>)</div>
                        </div>
                        <span class="badge bg-dark">1/<?= $tabTotal ?></span>
                    </div>
                </div>
                <div class="ebd-class-pane-body">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Lista de Alunos</h6>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="fas fa-user-plus me-1"></i> Matricular Aluno
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
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
                                            <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center text-secondary" style="width: 32px; height: 32px;">
                                                <i class="fas fa-user small"></i>
                                            </div>
                                            <?= htmlspecialchars($student['member_name']) ?>
                                            <?php if (!empty($student['is_teacher'])): ?>
                                                <span class="badge bg-info text-dark ms-2" style="font-size: 0.75em;">Professor</span>
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
                                        <a href="/admin/ebd/students/remove/<?= $student['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover aluno desta classe?')">
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
            </div>

            <!-- Aba Aulas -->
            <div class="tab-pane fade" id="lessons" role="tabpanel">
                <div class="d-lg-none ebd-class-pane-head">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <div class="ebd-class-pane-title"><i class="fas fa-calendar-check me-2"></i>Histórico de Aulas</div>
                            <div class="ebd-class-pane-hint mt-1"><i class="fas fa-arrows-left-right me-2"></i>Deslize para mudar (2/<?= $tabTotal ?>)</div>
                        </div>
                        <span class="badge bg-dark">2/<?= $tabTotal ?></span>
                    </div>
                </div>
                <div class="ebd-class-pane-body">
                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
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
                                    <td><?= date('d/m/Y', strtotime($lesson['lesson_date'])) ?></td>
                                    <td><?= htmlspecialchars($lesson['topic']) ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= 
                                                // Quick count query could be optimized
                                                (new Database())->connect()->query("SELECT COUNT(*) FROM ebd_attendance WHERE lesson_id = {$lesson['id']} AND present = 1")->fetchColumn() 
                                            ?>
                                        </span>
                                    </td>
                                    <td>R$ <?= number_format($lesson['offerings'], 2, ',', '.') ?></td>
                                    <td class="text-end">
                                        <a href="/admin/ebd/lessons/show/<?= $lesson['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                                        <a href="/admin/ebd/lessons/edit/<?= $lesson['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                                        <a href="/admin/ebd/lessons/delete/<?= $lesson['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir esta aula? O registro financeiro (se houver) NÃO será excluído automaticamente.')" title="Excluir"><i class="fas fa-trash"></i></a>
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
    </div>
</div>

<!-- Modal Adicionar Professor -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/admin/ebd/classes/assign-teacher/<?= $class['id'] ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Professor</h5>
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
                    <button type="submit" class="btn btn-primary">Adicionar</button>
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
                    <h5 class="modal-title">Matricular Aluno</h5>
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
                    <button type="submit" class="btn btn-primary">Matricular</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

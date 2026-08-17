<?php $suppressMobileTopbar = true; include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-none d-lg-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
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

<div class="d-lg-none mb-2">
    <?php
    $mobilePageCategory = 'Ensino';
    $mobilePageTitle = $class['name'];
    $mobilePageMenuItems = [
        ['icon' => 'fa-edit', 'label' => 'Editar Classe', 'href' => '/admin/ebd/classes/edit/' . $class['id']],
    ];
    include __DIR__ . '/../../../layout/mobile_page_header.php';
    ?>
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

    /* ---------- Mobile (Detalhes da Classe) — EBD Mobile design tokens ---------- */
    .ecs-wrap { padding-bottom: 110px; }
    .ecs-desc { font-size: .84rem; color: #8b93a3; margin-bottom: 1rem; }

    .ecs-info-card { background: #fff; border: 1px solid #eef1f5; border-radius: 16px; padding: 14px 16px; margin-bottom: 1rem; }
    .ecs-info-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; gap: .5rem; }
    .ecs-info-sub { color: #9aa4b2; font-size: 12.5px; }
    .ecs-info-status { background: rgba(24,165,88,.12); color: #18a558; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px; flex: 0 0 auto; }
    .ecs-info-status.is-inactive { background: rgba(0,0,0,.06); color: #6c757d; }
    .ecs-info-line { font-size: 14px; }
    .ecs-info-line .num { color: #3b6fef; font-weight: 800; }
    .ecs-info-line .sep { color: #c2c8d2; }
    .ecs-info-line .name { color: #3b6fef; font-weight: 600; }

    .ecs-section-header { display: flex; align-items: center; justify-content: space-between; margin: 1.2rem 0 .7rem; }
    .ecs-section-title { font-weight: 800; font-size: .9rem; color: #101828; }
    .ecs-add-btn { border: 1px solid #eef1f5; background: #fff; color: #101828; font-size: .74rem; font-weight: 700; padding: .35rem .75rem; border-radius: 999px; }

    .ecs-teacher-row { display: flex; align-items: center; gap: .7rem; background: #fff; border: 1px solid #eef1f5; border-radius: 14px; padding: .55rem .7rem; margin-bottom: .5rem; min-height: 48px; }
    .ecs-avatar { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 50%; background: #e7ebf5; color: #5b6472; font-weight: 800; font-size: .78rem; display: flex; align-items: center; justify-content: center; }
    .ecs-teacher-name { flex: 1 1 auto; min-width: 0; font-weight: 700; font-size: .86rem; color: #101828; }
    .ecs-x-btn { flex: 0 0 auto; width: 26px; height: 26px; border-radius: 50%; border: none; background: transparent; color: #ced4da; display: flex; align-items: center; justify-content: center; font-size: .8rem; }

    .ecs-segmented { display: flex; background: #e7e9ee; border-radius: 14px; padding: 4px; margin-bottom: 1rem; }
    .ecs-seg-btn { flex: 1 1 0; border: none; background: transparent; color: #8b93a3; font-weight: 700; font-size: .82rem; padding: 9px 0; border-radius: 11px; }
    .ecs-seg-btn.active { background: #fff; color: #101828; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    .ecs-panel.d-none { display: none !important; }

    .ecs-enroll-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: .7rem; gap: .5rem; }
    .ecs-enroll-btn { display: flex; align-items: center; gap: .35rem; border: 1.5px solid #18a558; color: #18a558; background: #fff; font-weight: 700; font-size: .8rem; padding: .4rem .8rem; border-radius: 999px; }
    .ecs-swipe-hint { color: #b7bec8; font-size: .68rem; white-space: nowrap; }

    .ecs-swipe { position: relative; margin-bottom: .5rem; border-radius: 14px; overflow: hidden; }
    .ecs-swipe-actions { position: absolute; top: 0; right: 0; bottom: 0; display: flex; }
    .ecs-swipe-actions .ecs-action { width: 64px; display: flex; align-items: center; justify-content: center; color: #fff; background: #dc3545; text-decoration: none; font-size: 1rem; }
    .ecs-student-card { position: relative; z-index: 1; display: flex; align-items: center; gap: .7rem; background: #fff; padding: .55rem .2rem; border-bottom: 1px solid #eef1f5; touch-action: pan-y; }
    .ecs-student-id { flex: 1 1 auto; min-width: 0; }
    .ecs-student-name { font-weight: 800; font-size: .86rem; color: #101828; }
    .ecs-student-meta { font-size: .74rem; color: #8b93a3; margin-top: .1rem; }
    .ecs-student-meta .role-pill { font-size: .62rem; padding: .12rem .45rem; background: rgba(59,111,239,.12); color: #3b6fef; }
    .ecs-chev { flex: 0 0 auto; color: #c2c8d2; }

    .ecs-hist-card { background: #fff; border: 1px solid #eef1f5; border-radius: 14px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; margin-bottom: .6rem; text-decoration: none; }
    .ecs-hist-date { font-weight: 800; font-size: .86rem; color: #101828; }
    .ecs-hist-sub { color: #9aa4b2; font-size: .74rem; margin-top: .1rem; }
    .ecs-hist-right { text-align: right; }
    .ecs-hist-pres { color: #3b6fef; font-size: .78rem; font-weight: 700; }
    .ecs-hist-valor { color: #101828; font-size: .84rem; font-weight: 800; margin-top: .1rem; }

    .ecs-empty { text-align: center; color: #adb5bd; font-size: .84rem; padding: 2rem 0; }

    .ecs-bottom-cta { position: fixed; left: 0; right: 0; bottom: 0; padding: 14px 18px calc(18px + env(safe-area-inset-bottom)); background: #f6f7f9; z-index: 1025; }
    .ecs-bottom-cta a { display: block; background: #18a558; color: #fff; text-align: center; font-weight: 700; font-size: .95rem; padding: 15px 0; border-radius: 999px; box-shadow: 0 10px 20px rgba(24,165,88,.3); text-decoration: none; }

    .ecs-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 85vh; }
    .ecs-sheet-search { position: relative; margin-bottom: .8rem; }
    .ecs-sheet-search i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    .ecs-sheet-search input { width: 100%; border: 1px solid rgba(17,24,39,.08); background: #f8f9fb; border-radius: 12px; padding: .6rem .8rem .6rem 2.3rem; font-size: .85rem; }
    .ecs-pick-row { display: flex; align-items: center; gap: .65rem; width: 100%; text-align: left; border: none; background: #fff; padding: .6rem .3rem; border-radius: 10px; border-bottom: 1px solid rgba(17,24,39,.05); }
    .ecs-pick-row:hover { background: #f8f9fb; }
    .ecs-pick-name { font-weight: 600; font-size: .86rem; color: #101828; }

    @media (max-width: 991.98px) {
        .ebd-lessons-table thead { display: none; }
        .ebd-lessons-table, .ebd-lessons-table tbody, .ebd-lessons-table tr, .ebd-lessons-table td {
            display: block;
            width: 100%;
        }
        .ebd-lessons-table tr {
            background: #fff;
            border: 1px solid rgba(17,24,39,.06);
            border-radius: 14px;
            padding: .8rem .9rem;
            margin-bottom: .55rem;
        }
        .ebd-lessons-table td {
            padding: .15rem 0;
            border: none;
            text-align: left !important;
        }
        .ebd-lessons-table td::before {
            content: attr(data-label);
            display: inline-block;
            min-width: 90px;
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #adb5bd;
        }
        .ebd-lessons-table td.text-end { text-align: left !important; }
    }
</style>

<div class="ecs-wrap d-lg-none">
    <?php if (!empty($class['description'])): ?><p class="ecs-desc"><?= htmlspecialchars($class['description']) ?></p><?php endif; ?>

    <?php $ecsIsActive = $class['status'] == 'active'; ?>
    <div class="ecs-info-card">
        <div class="ecs-info-top">
            <span class="ecs-info-sub"><?= htmlspecialchars($class['congregation_name'] ?? 'Todas') ?> • <?= $class['min_age'] ?>-<?= $class['max_age'] ?> anos</span>
            <span class="ecs-info-status <?= $ecsIsActive ? '' : 'is-inactive' ?>"><?= $ecsIsActive ? 'Active' : 'Inativa' ?></span>
        </div>
        <div class="ecs-info-line"><span class="num"><?= count($students) ?> matriculados</span><span class="sep"> • </span><span class="name"><?= htmlspecialchars($class['name']) ?></span></div>
    </div>

    <div class="ecs-section-header">
        <div class="ecs-section-title">Professores</div>
        <button type="button" class="ecs-add-btn" data-bs-toggle="offcanvas" data-bs-target="#ecsTeacherSheet"><i class="fas fa-plus me-1"></i>Adicionar</button>
    </div>
    <?php foreach ($teachers as $teacher):
        $ecsTParts = preg_split('/\s+/', trim((string)$teacher['member_name']));
        $ecsTInitials = mb_strtoupper(mb_substr($ecsTParts[0], 0, 1) . (count($ecsTParts) > 1 ? mb_substr(end($ecsTParts), 0, 1) : ''));
    ?>
        <div class="ecs-teacher-row">
            <span class="ecs-avatar"><?= htmlspecialchars($ecsTInitials) ?></span>
            <span class="ecs-teacher-name"><?= htmlspecialchars($teacher['member_name']) ?></span>
            <a href="/admin/ebd/teachers/remove/<?= $teacher['id'] ?>" class="ecs-x-btn btn-remove-teacher" data-name="<?= htmlspecialchars($teacher['member_name']) ?>" aria-label="Remover"><i class="fas fa-xmark"></i></a>
        </div>
    <?php endforeach; ?>
    <?php if (empty($teachers)): ?>
        <div class="ecs-empty">Nenhum professor atribuído.</div>
    <?php endif; ?>

    <div class="ecs-section-header">
        <div class="ecs-section-title">Alunos e Aulas</div>
    </div>
    <div class="ecs-segmented" id="ecsSegmented">
        <button type="button" class="ecs-seg-btn active" data-panel="ecsStudents">Alunos (<?= count($students) ?>)</button>
        <button type="button" class="ecs-seg-btn" data-panel="ecsLessons">Histórico</button>
    </div>

    <div class="ecs-panel" id="ecsStudents">
        <div class="ecs-enroll-row">
            <button type="button" class="ecs-enroll-btn" data-bs-toggle="offcanvas" data-bs-target="#ecsStudentSheet"><span>+</span> Matricular Aluno</button>
            <span class="ecs-swipe-hint">deslize para excluir</span>
        </div>
        <?php foreach ($students as $student):
            $ecsSParts = preg_split('/\s+/', trim((string)$student['member_name']));
            $ecsSInitials = mb_strtoupper(mb_substr($ecsSParts[0], 0, 1) . (count($ecsSParts) > 1 ? mb_substr(end($ecsSParts), 0, 1) : ''));
            $ecsAge = null;
            if (!empty($student['birth_date'])) {
                $ecsDob = new DateTime($student['birth_date']);
                $ecsNow = new DateTime();
                $ecsAge = $ecsNow->diff($ecsDob)->y . ' anos';
            }
        ?>
            <div class="ecs-swipe">
                <div class="ecs-swipe-actions">
                    <a href="/admin/ebd/students/remove/<?= $student['id'] ?>" class="ecs-action btn-remove-student" data-name="<?= htmlspecialchars($student['member_name']) ?>"><i class="fas fa-user-minus"></i></a>
                </div>
                <div class="ecs-student-card">
                    <span class="ecs-avatar"><?= htmlspecialchars($ecsSInitials) ?></span>
                    <div class="ecs-student-id">
                        <div class="ecs-student-name"><?= htmlspecialchars($student['member_name']) ?></div>
                        <div class="ecs-student-meta">
                            <?php if (!empty($student['is_teacher'])): ?><span class="role-pill">Professor</span> • <?php endif; ?>
                            <?= $ecsAge ? htmlspecialchars($ecsAge) . ' • ' : '' ?><?= date('d/m/Y', strtotime($student['enrolled_at'])) ?>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right ecs-chev"></i>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($students)): ?>
            <div class="ecs-empty">Nenhum aluno matriculado nesta classe.</div>
        <?php endif; ?>
    </div>

    <div class="ecs-panel d-none" id="ecsLessons">
        <?php foreach ($lessons as $lesson):
            $ecsPresCount = (new Database())->connect()->query("SELECT COUNT(*) FROM ebd_attendance WHERE lesson_id = {$lesson['id']} AND present = 1")->fetchColumn();
        ?>
            <a href="/admin/ebd/lessons/show/<?= $lesson['id'] ?>" class="ecs-hist-card">
                <div>
                    <div class="ecs-hist-date"><?= date('d/m/Y', strtotime($lesson['lesson_date'])) ?></div>
                    <div class="ecs-hist-sub"><?= !empty($lesson['topic']) ? htmlspecialchars($lesson['topic']) : $ecsPresCount . ' presentes' ?></div>
                </div>
                <div class="ecs-hist-right">
                    <div class="ecs-hist-pres"><?= $ecsPresCount ?> pres.</div>
                    <div class="ecs-hist-valor">R$ <?= number_format($lesson['offerings'], 2, ',', '.') ?></div>
                </div>
            </a>
        <?php endforeach; ?>
        <?php if (empty($lessons)): ?>
            <div class="ecs-empty">Nenhuma aula registrada.</div>
        <?php endif; ?>
    </div>
</div>

<div class="ecs-bottom-cta d-lg-none">
    <a href="/admin/ebd/lessons/create/<?= $class['id'] ?>">Nova Aula/Chamada</a>
</div>

<div class="row g-3 d-none d-lg-flex">
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

<!-- Mobile: Adicionar Professor (bottom sheet, sem fundo escuro) -->
<div class="offcanvas offcanvas-bottom ecs-sheet" tabindex="-1" id="ecsTeacherSheet" data-bs-backdrop="false">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Adicionar Professor</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <form id="ecsTeacherForm" action="/admin/ebd/classes/assign-teacher/<?= $class['id'] ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="member_id" id="ecsTeacherMemberId">
        </form>
        <div class="ecs-sheet-search">
            <i class="fas fa-search"></i>
            <input type="text" id="ecsTeacherSearch" placeholder="Buscar...">
        </div>
        <div class="small text-muted mb-2">Apenas membros marcados como "Professor de EBD" aparecem aqui.</div>
        <div id="ecsTeacherList">
            <?php foreach ($ebd_teachers_list as $m): ?>
                <button type="button" class="ecs-pick-row" data-value="<?= $m['id'] ?>" data-term="<?= mb_strtolower(htmlspecialchars($m['name']), 'UTF-8') ?>">
                    <span class="ecs-pick-name"><?= htmlspecialchars($m['name']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Mobile: Matricular Aluno (bottom sheet, sem fundo escuro) -->
<div class="offcanvas offcanvas-bottom ecs-sheet" tabindex="-1" id="ecsStudentSheet" data-bs-backdrop="false">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Matricular Aluno</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <form id="ecsStudentForm" action="/admin/ebd/classes/enroll/<?= $class['id'] ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="member_id" id="ecsStudentMemberId">
        </form>
        <div class="ecs-sheet-search">
            <i class="fas fa-search"></i>
            <input type="text" id="ecsStudentSearch" placeholder="Buscar...">
        </div>
        <div id="ecsStudentList">
            <?php foreach ($all_members as $m): ?>
                <button type="button" class="ecs-pick-row" data-value="<?= $m['id'] ?>" data-term="<?= mb_strtolower(htmlspecialchars($m['name']), 'UTF-8') ?>">
                    <span class="ecs-pick-name"><?= htmlspecialchars($m['name']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
(function () {
    var segmented = document.getElementById('ecsSegmented');
    if (segmented) {
        segmented.querySelectorAll('.ecs-seg-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                segmented.querySelectorAll('.ecs-seg-btn').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                document.querySelectorAll('.ecs-panel').forEach(function (p) { p.classList.add('d-none'); });
                document.getElementById(btn.getAttribute('data-panel')).classList.remove('d-none');
            });
        });
    }

    // Swipe-to-reveal remove action on student rows (iOS Mail style)
    document.querySelectorAll('.ecs-swipe').forEach(function (swipe) {
        var card = swipe.querySelector('.ecs-student-card');
        var actionsWidth = swipe.querySelectorAll('.ecs-action').length * 64;
        var startX = 0, currentX = 0, dragging = false, open = false;

        function setOpen(state) {
            open = state;
            card.style.transform = open ? 'translateX(-' + actionsWidth + 'px)' : 'translateX(0)';
        }
        function closeOthers() {
            document.querySelectorAll('.ecs-swipe .ecs-student-card').forEach(function (c) {
                if (c !== card) c.style.transform = 'translateX(0)';
            });
        }

        card.addEventListener('touchstart', function (e) {
            startX = e.touches[0].clientX;
            dragging = true;
            card.style.transition = 'none';
            closeOthers();
        }, { passive: true });

        card.addEventListener('touchmove', function (e) {
            if (!dragging) return;
            currentX = e.touches[0].clientX - startX;
            var base = open ? -actionsWidth : 0;
            var next = Math.min(0, Math.max(-actionsWidth, base + currentX));
            card.style.transform = 'translateX(' + next + 'px)';
        }, { passive: true });

        card.addEventListener('touchend', function () {
            dragging = false;
            card.style.transition = '';
            var base = open ? -actionsWidth : 0;
            var moved = base + currentX;
            setOpen(moved < -(actionsWidth / 2));
            currentX = 0;
        });
    });

    function wirePickSheet(listId, searchId, hiddenInputId, formId) {
        var list = document.getElementById(listId);
        var search = document.getElementById(searchId);
        var hidden = document.getElementById(hiddenInputId);
        var form = document.getElementById(formId);
        if (!list) return;
        list.querySelectorAll('.ecs-pick-row').forEach(function (row) {
            row.addEventListener('click', function () {
                hidden.value = row.getAttribute('data-value');
                form.submit();
            });
        });
        if (search) {
            search.addEventListener('input', function () {
                var term = search.value.trim().toLowerCase();
                list.querySelectorAll('.ecs-pick-row').forEach(function (row) {
                    row.style.display = row.getAttribute('data-term').indexOf(term) !== -1 ? '' : 'none';
                });
            });
        }
    }
    wirePickSheet('ecsTeacherList', 'ecsTeacherSearch', 'ecsTeacherMemberId', 'ecsTeacherForm');
    wirePickSheet('ecsStudentList', 'ecsStudentSearch', 'ecsStudentMemberId', 'ecsStudentForm');
})();

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
            if (result.isConfirmed) window.location.replace(href);
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
            if (result.isConfirmed) window.location.replace(href);
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
            if (result.isConfirmed) window.location.replace(href);
        });
    });
});
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

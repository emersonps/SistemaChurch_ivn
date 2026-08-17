<?php $suppressMobileTopbar = true; include __DIR__ . '/../../../layout/header.php'; ?>

<div class="member-form-topbar d-none d-lg-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
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
    <div class="d-none d-lg-flex gap-2">
        <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="ebdLessonForm" class="btn btn-success rounded-pill fw-semibold px-3">
            <i class="fas fa-check-circle me-1"></i> Finalizar Aula
        </button>
    </div>
</div>

<div class="d-lg-none llm-topbar">
    <button type="button" id="llmBackBtn" class="llm-back" data-fallback="/admin/ebd/classes/show/<?= $class['id'] ?>" aria-label="Voltar"><i class="fas fa-arrow-left"></i></button>
    <div class="llm-topbar-title">Lançar Aula — <?= htmlspecialchars($class['name']) ?></div>
    <button type="submit" form="ebdLessonForm" class="llm-finalize-link">Finalizar</button>
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
    .elm-classline {
        font-size: .82rem;
        font-weight: 700;
        color: #8b93a7;
        margin-top: -.4rem;
    }

    .llm-avatar { display: none; }

    .llm-topbar { display: flex; align-items: center; gap: .7rem; padding: .3rem 0 1rem; }
    .llm-back { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #fff; border: 1px solid #eef1f5; color: #101828; display: flex; align-items: center; justify-content: center; }
    .llm-topbar-title { flex: 1 1 auto; min-width: 0; font-weight: 800; font-size: 1rem; color: #101828; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .llm-finalize-link { flex: 0 0 auto; border: none; background: transparent; color: #18a558; font-weight: 700; font-size: .88rem; padding: 0; }

    .llm-stepper-v2 { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.2rem; }
    .llm-step-circle { flex: 0 0 auto; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .74rem; font-weight: 700; background: #e7e9ee; border: none; color: #8b93a3; }
    .llm-step-circle.active { background: #18a558; color: #fff; }
    .llm-step-line { flex: 0 0 28px; height: 2px; background: #e7e9ee; border-radius: 1px; transition: background .2s; }
    .llm-step-line.active { background: #18a558; }
    .llm-step-label { font-size: .74rem; color: #9aa4b2; }

    .llm-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: .8rem; }
    .llm-section-title { font-weight: 800; font-size: 1rem; color: #101828; }
    .llm-count-pill-dark { background: #10162b; color: #fff; font-size: .72rem; font-weight: 700; padding: .25rem .7rem; border-radius: 999px; }

    @media (max-width: 991.98px) {
        .member-form-card-body { padding: 1rem; }

        .llm-step-hidden { display: none !important; }
        .llm-hide-header-mobile { display: none !important; }

        .llm-fields-v2 .form-label { color: #3b6fef; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .llm-fields-v2 .form-control { border-color: #e3e7ee; border-radius: 12px; }
        .llm-fields-v2 textarea.form-control::placeholder,
        .llm-fields-v2 input.form-control::placeholder { color: #c2c8d2; }

        .llm-date-wrap { position: relative; }
        .llm-date-wrap .llm-date-icon { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: #101828; pointer-events: none; }
        .llm-date-wrap input[name="date"] { padding-left: 2.3rem; }

        .llm-offer-group .input-group-text { background: #fff; color: #18a558; font-weight: 700; border: 1px solid #e3e7ee; border-right: none; }
        .llm-offer-group input { border: 1px solid #e3e7ee; border-left: none; font-weight: 700; border-radius: 0 12px 12px 0 !important; }

        .llm-counter-list { border: 1px solid #eef1f5; border-radius: 14px; overflow: hidden; }
        .llm-counter-item { display: flex; align-items: center; justify-content: space-between; padding: .75rem .9rem; background: #fff; border-bottom: 1px solid #f1f2f5; }
        .llm-counter-item:last-child { border-bottom: none; }
        .llm-counter-item-label { font-size: .86rem; font-weight: 600; color: #101828; }
        .llm-counter-controls { display: flex; align-items: center; gap: .7rem; }
        .llm-counter-btn { width: 26px; height: 26px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; padding: 0; line-height: 1; font-size: .85rem; font-weight: 700; }
        .llm-counter-btn.is-minus { background: #e7e9ee; color: #5b6472; }
        .llm-counter-btn.is-plus { background: #10162b; color: #fff; }
        .llm-counter-controls input { width: 26px; text-align: center; border: none !important; background: transparent; font-weight: 700; font-size: .92rem; padding: 0 !important; -moz-appearance: textfield; }
        .llm-counter-controls input::-webkit-outer-spin-button,
        .llm-counter-controls input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        .attendance-table thead { display: none; }
        .attendance-table, .attendance-table tbody, .attendance-table tr, .attendance-table td {
            display: block;
            width: 100%;
        }
        .attendance-table tr {
            display: flex;
            align-items: center;
            gap: .65rem;
            background: #fff;
            border: none;
            border-bottom: 1px solid rgba(17,24,39,.06);
            padding: .8rem 1rem;
        }
        .attendance-table tbody tr:last-child { border-bottom: none; }
        .attendance-table tr:hover { background: #fff; }
        .attendance-table td {
            padding: 0;
            border: none;
        }
        .attendance-table td:first-child { flex: 0 0 auto; width: auto; order: 3; }
        .attendance-table td:first-child .form-check { padding-left: 0; margin: 0; min-height: 0; }
        .attendance-table td:nth-child(2) { flex: 1 1 auto; width: auto; min-width: 0; order: 1; }
        .attendance-table td:nth-child(3) { display: none; }
        .attendance-table .fw-bold { font-size: .86rem; }

        .llm-avatar { display: flex; flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #e7ebf5; color: #5b6472; font-weight: 700; font-size: .74rem; align-items: center; justify-content: center; margin-right: .6rem; }
        .attendance-table td:nth-child(2) { display: flex; align-items: center; }

        .attendance-table .attendance-check {
            appearance: none;
            -webkit-appearance: none;
            width: 44px;
            height: 26px;
            border-radius: 999px;
            background: #dcdfe4;
            position: relative;
            outline: none;
            border: none;
            cursor: pointer;
            flex: 0 0 auto;
            margin: 0;
            transition: background .2s;
        }
        .attendance-table .attendance-check::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,.25);
            transition: transform .2s;
        }
        .attendance-table .attendance-check:checked { background: #18a558; }
        .attendance-table .attendance-check:checked::before { transform: translateX(18px); }

        [data-step-nav] .btn-success,
        [data-step-nav] .btn-outline-secondary {
            box-shadow: none;
        }
        [data-step-nav] .btn-success { background-color: #18a558; border-color: #18a558; box-shadow: 0 10px 20px rgba(24,165,88,.3); }
        [data-step-nav] .btn-outline-secondary { background-color: #e7e9ee; border-color: #e7e9ee; color: #5b6472; }
    }
</style>


<div class="d-lg-none llm-stepper-v2">
    <span class="llm-step-circle active" data-circle="1">1</span>
    <span class="llm-step-line"></span>
    <span class="llm-step-circle" data-circle="2">2</span>
    <span class="llm-step-label" id="llmStepLabel">1/2 · Detalhes</span>
</div>

<form action="/admin/ebd/lessons/create/<?= $class['id'] ?>" method="POST" class="app-form-with-bottom-actions" id="ebdLessonForm">
    <?= csrf_field() ?>
    <div class="row g-3">
        <!-- Detalhes da Aula -->
        <div class="col-12 col-md-4 llm-step" data-step="1">
            <div class="member-form-card">
                <div class="member-form-card-header llm-hide-header-mobile">
                    <div class="member-form-badge"><i class="fas fa-clipboard-list"></i></div>
                    <div>
                        <div class="member-form-card-header-title">Dados da Aula</div>
                    </div>
                </div>
                <div class="member-form-card-body llm-fields-v2">
                    <div class="mb-3">
                        <label class="form-label">Data</label>
                        <div class="llm-date-wrap">
                            <i class="fas fa-calendar-alt llm-date-icon d-lg-none"></i>
                            <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tema da Lição</label>
                        <input type="text" class="form-control" name="topic" placeholder="Ex: Lição 5 - A Fé de Abraão">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Opcional..."></textarea>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">Oferta da Classe (R$)</label>
                        <div class="input-group llm-offer-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" class="form-control" name="offerings" placeholder="0.00">
                        </div>
                    </div>

                    <div class="row g-2 d-none d-lg-flex">
                        <div class="col-6">
                            <label class="form-label small">Visitantes</label>
                            <input type="number" class="form-control form-control-sm" id="visitors_d" name="visitors" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Bíblias</label>
                            <input type="number" class="form-control form-control-sm" id="bibles_d" name="bibles" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Revistas</label>
                            <input type="number" class="form-control form-control-sm" id="magazines_d" name="magazines" value="0">
                        </div>
                    </div>

                    <div class="llm-counter-list d-lg-none">
                        <div class="llm-counter-item">
                            <span class="llm-counter-item-label">Visitantes</span>
                            <div class="llm-counter-controls">
                                <button type="button" class="llm-counter-btn is-minus" data-target="visitors_m" data-delta="-1">−</button>
                                <input type="number" id="visitors_m" value="0" readonly>
                                <button type="button" class="llm-counter-btn is-plus" data-target="visitors_m" data-delta="1">+</button>
                            </div>
                        </div>
                        <div class="llm-counter-item">
                            <span class="llm-counter-item-label">Bíblias</span>
                            <div class="llm-counter-controls">
                                <button type="button" class="llm-counter-btn is-minus" data-target="bibles_m" data-delta="-1">−</button>
                                <input type="number" id="bibles_m" value="0" readonly>
                                <button type="button" class="llm-counter-btn is-plus" data-target="bibles_m" data-delta="1">+</button>
                            </div>
                        </div>
                        <div class="llm-counter-item">
                            <span class="llm-counter-item-label">Revistas</span>
                            <div class="llm-counter-controls">
                                <button type="button" class="llm-counter-btn is-minus" data-target="magazines_m" data-delta="-1">−</button>
                                <input type="number" id="magazines_m" value="0" readonly>
                                <button type="button" class="llm-counter-btn is-plus" data-target="magazines_m" data-delta="1">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chamada -->
        <div class="col-12 col-md-8 llm-step llm-step-hidden" data-step="2">
            <div class="llm-section-header d-lg-none">
                <span class="llm-section-title">Presença</span>
                <span class="llm-count-pill-dark"><?= count($students) ?> Alunos</span>
            </div>
            <div class="member-form-card">
                <div class="member-form-card-header llm-hide-header-mobile">
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
                                    <?php
                                        $llmParts = preg_split('/\s+/', trim((string)$student['name']));
                                        $llmInitials = mb_strtoupper(mb_substr($llmParts[0], 0, 1) . (count($llmParts) > 1 ? mb_substr(end($llmParts), 0, 1) : ''));
                                    ?>
                                    <span class="llm-avatar"><?= htmlspecialchars($llmInitials) ?></span>
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

    <div class="d-lg-none mb-5" data-step-nav="1">
        <button type="button" id="llmNext" class="btn btn-success w-100 rounded-pill py-2 fw-semibold">Próximo — Presença <i class="fas fa-arrow-right ms-1"></i></button>
    </div>
    <div class="d-flex gap-2 mb-5 d-lg-none llm-step-hidden" data-step-nav="2">
        <button type="button" id="llmBack" class="btn btn-outline-secondary rounded-pill px-4">Voltar</button>
        <button type="submit" class="btn btn-success flex-grow-1 rounded-pill fw-semibold"><i class="fas fa-check-circle me-1"></i> Finalizar Chamada</button>
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

    // Compact back button (same history.back()+fallback pattern used elsewhere in the app)
    (function () {
        var btn = document.getElementById('llmBackBtn');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var cameFromSameSite = document.referrer && document.referrer.indexOf(window.location.origin) === 0;
            if (cameFromSameSite && window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = btn.getAttribute('data-fallback');
            }
        });
    })();

    // Stepper (mobile only — desktop always shows both cards via CSS override)
    (function () {
        var nextBtn = document.getElementById('llmNext');
        var backBtn = document.getElementById('llmBack');
        var stepLabel = document.getElementById('llmStepLabel');
        var stepLabels = { 1: '1/2 · Detalhes', 2: '2/2 · Presença' };
        if (!nextBtn) return;

        function showStep(step) {
            document.querySelectorAll('.llm-step').forEach(function (el) {
                el.classList.toggle('llm-step-hidden', el.getAttribute('data-step') != step);
            });
            document.querySelectorAll('[data-step-nav]').forEach(function (el) {
                el.classList.toggle('llm-step-hidden', el.getAttribute('data-step-nav') != step);
            });
            document.querySelectorAll('.llm-step-circle').forEach(function (el) {
                el.classList.toggle('active', el.getAttribute('data-circle') <= step);
            });
            document.querySelectorAll('.llm-step-line').forEach(function (el) {
                el.classList.toggle('active', step >= 2);
            });
            if (stepLabel) stepLabel.textContent = stepLabels[step];
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        nextBtn.addEventListener('click', function () {
            var dateInput = document.querySelector('[name="date"]');
            if (!dateInput.value) {
                dateInput.reportValidity();
                return;
            }
            showStep(2);
        });
        if (backBtn) backBtn.addEventListener('click', function () { showStep(1); });
    })();

    // Visitantes/Bíblias/Revistas mini counters (mobile) — mirror value into the
    // real, named desktop input so only one value is ever submitted.
    document.querySelectorAll('.llm-counter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var mInput = document.getElementById(btn.getAttribute('data-target'));
            var delta = parseInt(btn.getAttribute('data-delta'), 10);
            var newVal = Math.max(0, (parseInt(mInput.value, 10) || 0) + delta);
            mInput.value = newVal;
            var realInput = document.getElementById(btn.getAttribute('data-target').replace('_m', '_d'));
            if (realInput) realInput.value = newVal;
        });
    });
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

<?php $suppressMobileTopbar = true; include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-none d-lg-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Editar Aula</h1>
        <h5 class="text-muted"><?= htmlspecialchars($class['name']) ?></h5>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/ebd/lessons/show/<?= $lesson['id'] ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Cancelar
        </a>
    </div>
</div>

<div class="d-lg-none eem-topbar">
    <button type="button" id="eemBackBtn" class="eem-back" data-fallback="/admin/ebd/lessons/show/<?= $lesson['id'] ?>" aria-label="Voltar"><i class="fas fa-arrow-left"></i></button>
    <div class="eem-id">
        <span class="eem-title">Editar Aula</span>
        <span class="eem-sep">•</span>
        <span class="eem-class"><?= htmlspecialchars($class['name']) ?></span>
    </div>
    <button type="submit" form="ebdLessonEditForm" class="eem-save-link">Salvar</button>
</div>

<style>
    .eem-topbar { display: flex; align-items: center; gap: .6rem; padding: .3rem 0 1.1rem; }
    .eem-back { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #fff; border: 1px solid #eef1f5; color: #101828; display: flex; align-items: center; justify-content: center; }
    .eem-id { flex: 1 1 auto; min-width: 0; display: flex; align-items: baseline; gap: .4rem; overflow: hidden; }
    .eem-title { font-weight: 800; font-size: .96rem; color: #101828; white-space: nowrap; }
    .eem-sep { color: #c2c8d2; font-size: .8rem; }
    .eem-class { font-size: .8rem; color: #8b93a3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .eem-save-link { flex: 0 0 auto; border: none; background: transparent; color: #18a558; font-weight: 700; font-size: .88rem; padding: 0; }

    .eem-card { background: #fff; border: 1px solid #eef1f5; border-radius: 16px; padding: 1rem 1.1rem; margin-bottom: 1rem; }
    .eem-card-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #3b6fef; }
    .eem-card-label .fa-circle-info { font-size: .8em; margin-left: .2rem; color: #adb5bd; }
    .eem-card-head-row { display: flex; align-items: center; justify-content: space-between; gap: .6rem; margin-bottom: .8rem; }

    .eem-date-pill { display: flex; align-items: center; gap: .4rem; background: #eef1f4; border: none; border-radius: 999px; padding: .4rem .85rem; font-size: .8rem; font-weight: 700; color: #101828; }
    .eem-date-pill i { color: #3b6fef; font-size: .8rem; }
    .eem-date-pill input[type="date"] { border: none; background: transparent; padding: 0; font-weight: 700; color: #101828; font-size: .8rem; -webkit-appearance: none; }

    .eem-sublabel { font-size: .78rem; color: #8b93a3; margin-bottom: .4rem; }
    .eem-input, .eem-textarea { width: 100%; border: 1px solid #e3e7ee; background: #f8f9fb; border-radius: 12px; padding: .7rem .85rem; font-size: .9rem; color: #101828; font-weight: 600; }
    .eem-textarea { font-weight: 400; resize: vertical; }
    .eem-input:focus, .eem-textarea:focus { outline: none; border-color: #18a558; box-shadow: 0 0 0 .2rem rgba(24,165,88,.12); background: #fff; }

    .eem-counter-list { display: flex; flex-direction: column; gap: .6rem; margin-top: .3rem; }
    .eem-counter-item { display: flex; align-items: center; gap: .7rem; }
    .eem-counter-icon { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #eef1f5; color: #5b6472; display: flex; align-items: center; justify-content: center; font-size: .82rem; }
    .eem-counter-label { flex: 1 1 auto; font-size: .88rem; font-weight: 600; color: #101828; }
    .eem-counter-controls { flex: 0 0 auto; display: flex; align-items: center; gap: .7rem; }
    .eem-counter-btn { width: 27px; height: 27px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; padding: 0; font-size: .82rem; font-weight: 700; }
    .eem-counter-btn.is-minus { background: #fff; border: 1px solid #e3e7ee; color: #8b93a3; }
    .eem-counter-btn.is-plus { background: #10162b; color: #fff; }
    .eem-counter-value { width: 18px; text-align: center; font-weight: 700; font-size: .9rem; color: #101828; }

    .eem-offer-wrap { position: relative; }
    .eem-offer-wrap .eem-offer-prefix { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #18a558; font-weight: 700; font-size: .9rem; }
    .eem-offer-wrap input { padding-left: 2.3rem; font-weight: 700; }
    .eem-offer-hint { font-size: .72rem; color: #9aa4b2; margin-top: .4rem; }

    .eem-att-toggle { flex: 0 0 auto; display: flex; align-items: center; gap: .35rem; border-radius: 999px; padding: .35rem .8rem; font-size: .78rem; font-weight: 700; border: 1px solid #e3e7ee; background: #fff; color: #8b93a3; }
    .eem-att-toggle.is-present { background: #10162b; border-color: #10162b; color: #fff; }
    .eem-att-toggle .eem-att-toggle-dot { width: 14px; height: 14px; border-radius: 50%; border: 1.5px solid #c2c8d2; flex: 0 0 auto; }
    .eem-att-toggle.is-present .eem-att-toggle-dot { border: none; background: #fff; color: #18a558; display: flex; align-items: center; justify-content: center; }
    .eem-att-toggle.is-present .eem-att-toggle-dot::before { content: '\f00c'; font-family: 'Font Awesome 5 Free'; font-weight: 900; font-size: .5rem; }

    .eem-marcar-todos { display: flex; align-items: center; gap: .5rem; font-size: .76rem; font-weight: 700; color: #3b6fef; text-transform: uppercase; letter-spacing: .03em; }
    .eem-switch { position: relative; width: 40px; height: 23px; border-radius: 999px; background: #e2e5ea; border: none; padding: 0; flex: 0 0 auto; }
    .eem-switch::before { content: ''; position: absolute; top: 2px; left: 2px; width: 19px; height: 19px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.2); transition: transform .15s; }
    .eem-switch.is-on { background: #18a558; }
    .eem-switch.is-on::before { transform: translateX(17px); }

    .eem-att-row { display: flex; align-items: center; gap: .65rem; padding: .55rem 0; border-bottom: 1px solid #f1f2f5; }
    .eem-att-row:last-child { border-bottom: none; }
    .eem-att-avatar { flex: 0 0 auto; width: 34px; height: 34px; border-radius: 50%; background: #e7ebf5; color: #5b6472; font-weight: 700; font-size: .68rem; display: flex; align-items: center; justify-content: center; }
    .eem-att-name-wrap { flex: 1 1 auto; min-width: 0; display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; }
    .eem-att-name { font-weight: 700; font-size: .84rem; color: #101828; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
    .eem-att-teacher { font-size: .64rem; font-weight: 700; color: #3b6fef; text-transform: uppercase; }

    .eem-att-footer { display: flex; align-items: center; justify-content: space-between; margin-top: .8rem; padding-top: .7rem; border-top: 1px dashed #eef1f5; font-size: .72rem; color: #9aa4b2; }

    .eem-bottom-bar { display: flex; align-items: center; justify-content: flex-end; gap: 1.2rem; padding: 1rem 0 2rem; }
    .eem-cancel-link { color: #5b6472; font-weight: 600; font-size: .88rem; text-decoration: none; }
    .eem-save-btn { flex: 1 1 auto; max-width: 220px; background: #18a558; color: #fff; text-align: center; font-weight: 700; font-size: .9rem; padding: .8rem 0; border-radius: 999px; border: none; box-shadow: 0 8px 18px rgba(24,165,88,.3); }
</style>

<div class="row">
    <div class="col-md-10 offset-md-1">
        <form action="/admin/ebd/lessons/edit/<?= $lesson['id'] ?>" method="POST" id="ebdLessonEditForm">
            <?= csrf_field() ?>

            <!-- ===== Desktop ===== -->
            <div class="card shadow-sm border-0 mb-4 d-none d-lg-block">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i> Dados da Aula</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label">Data da Aula</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?= $lesson['lesson_date'] ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label for="topic" class="form-label">Tema da Lição</label>
                            <input type="text" class="form-control" id="topic" name="topic" value="<?= htmlspecialchars($lesson['topic']) ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label for="visitors" class="form-label">Visitantes</label>
                            <input type="number" class="form-control" id="visitors" name="visitors" value="<?= $lesson['visitors_count'] ?>" min="0">
                        </div>
                        <div class="col-md-3">
                            <label for="bibles" class="form-label">Bíblias</label>
                            <input type="number" class="form-control" id="bibles" name="bibles" value="<?= $lesson['bibles_count'] ?>" min="0">
                        </div>
                        <div class="col-md-3">
                            <label for="magazines" class="form-label">Revistas</label>
                            <input type="number" class="form-control" id="magazines" name="magazines" value="<?= $lesson['magazines_count'] ?>" min="0">
                        </div>
                        <div class="col-md-3">
                            <label for="offerings" class="form-label text-success fw-bold">Oferta (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control border-success" id="offerings" name="offerings" value="<?= number_format($lesson['offerings'], 2, ',', '.') ?>" placeholder="0,00">
                            </div>
                            <div class="form-text text-warning small">
                                <i class="fas fa-exclamation-triangle"></i> Alterar este valor <strong>NÃO</strong> atualiza o lançamento no Financeiro automaticamente. Corrija lá também se necessário.
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label">Observações</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?= htmlspecialchars($lesson['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4 d-none d-lg-block">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2 text-primary"></i> Chamada / Presença</h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="checkAllDesktop">
                        <label class="form-check-label" for="checkAllDesktop">Marcar Todos</label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">Presença</th>
                                    <th>Aluno</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr class="<?= $student['present'] ? 'table-success-soft' : '' ?>">
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input attendance-check-desktop" type="checkbox"
                                                   data-sync="att_<?= $student['student_record_id'] ?>"
                                                   value="1"
                                                   <?= $student['present'] ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($student['name']) ?>
                                        <?php if (!empty($student['is_teacher'])): ?>
                                            <span class="badge bg-info text-dark ms-2" style="font-size: 0.7em;">Professor</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted">Nenhum aluno matriculado nesta classe.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-none d-lg-flex gap-2 justify-content-end mb-5">
                <a href="/admin/ebd/lessons/show/<?= $lesson['id'] ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-save me-2"></i> Salvar Alterações
                </button>
            </div>

            <!-- ===== Mobile ===== -->
            <div class="d-lg-none">
                <div class="eem-card">
                    <div class="eem-card-head-row">
                        <span class="eem-card-label">Data e Tema</span>
                        <label class="eem-date-pill">
                            <i class="far fa-calendar"></i>
                            <input type="date" value="<?= $lesson['lesson_date'] ?>" data-sync="date">
                        </label>
                    </div>
                    <div class="eem-sublabel">Tema da Lição</div>
                    <input type="text" class="eem-input" data-sync="topic" value="<?= htmlspecialchars($lesson['topic']) ?>" placeholder="Ex: Lição 5 - A Fé de Abraão">
                </div>

                <div class="eem-card">
                    <span class="eem-card-label">Métricas</span>
                    <div class="eem-counter-list">
                        <div class="eem-counter-item">
                            <span class="eem-counter-icon"><i class="fas fa-user-group"></i></span>
                            <span class="eem-counter-label">Visitantes</span>
                            <div class="eem-counter-controls">
                                <button type="button" class="eem-counter-btn is-minus" data-target="visitors_m" data-delta="-1">−</button>
                                <span class="eem-counter-value" id="visitors_m"><?= (int)$lesson['visitors_count'] ?></span>
                                <button type="button" class="eem-counter-btn is-plus" data-target="visitors_m" data-delta="1">+</button>
                            </div>
                        </div>
                        <div class="eem-counter-item">
                            <span class="eem-counter-icon"><i class="fas fa-bible"></i></span>
                            <span class="eem-counter-label">Bíblias</span>
                            <div class="eem-counter-controls">
                                <button type="button" class="eem-counter-btn is-minus" data-target="bibles_m" data-delta="-1">−</button>
                                <span class="eem-counter-value" id="bibles_m"><?= (int)$lesson['bibles_count'] ?></span>
                                <button type="button" class="eem-counter-btn is-plus" data-target="bibles_m" data-delta="1">+</button>
                            </div>
                        </div>
                        <div class="eem-counter-item">
                            <span class="eem-counter-icon"><i class="fas fa-book"></i></span>
                            <span class="eem-counter-label">Revistas</span>
                            <div class="eem-counter-controls">
                                <button type="button" class="eem-counter-btn is-minus" data-target="magazines_m" data-delta="-1">−</button>
                                <span class="eem-counter-value" id="magazines_m"><?= (int)$lesson['magazines_count'] ?></span>
                                <button type="button" class="eem-counter-btn is-plus" data-target="magazines_m" data-delta="1">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="eem-card">
                    <span class="eem-card-label" title="Alterar este valor não atualiza o lançamento no Financeiro automaticamente."><i class="fas fa-circle-info"></i> Oferta</span>
                    <div class="eem-offer-wrap mt-2">
                        <span class="eem-offer-prefix">R$</span>
                        <input type="text" class="eem-input" data-sync="offerings" value="<?= number_format($lesson['offerings'], 2, ',', '.') ?>" placeholder="0,00">
                    </div>
                    <div class="eem-offer-hint">* Alterar aqui não atualiza o Financeiro automaticamente.</div>
                </div>

                <div class="eem-card">
                    <span class="eem-card-label">Observações</span>
                    <textarea class="eem-textarea mt-2" rows="3" data-sync="notes" placeholder="Opcional..."><?= htmlspecialchars($lesson['notes'] ?? '') ?></textarea>
                </div>

                <div class="eem-card">
                    <div class="eem-card-head-row">
                        <span class="eem-card-label">Chamada / Presença</span>
                        <div class="eem-marcar-todos">
                            Marcar Todos
                            <button type="button" class="eem-switch" id="eemMarkAll" aria-label="Marcar todos"></button>
                        </div>
                    </div>
                    <?php foreach ($students as $student):
                        $eemParts = preg_split('/\s+/', trim((string)$student['name']));
                        $eemInitials = mb_strtoupper(mb_substr($eemParts[0], 0, 1) . (count($eemParts) > 1 ? mb_substr(end($eemParts), 0, 1) : ''), 'UTF-8');
                    ?>
                        <div class="eem-att-row">
                            <span class="eem-att-avatar"><?= htmlspecialchars($eemInitials) ?></span>
                            <div class="eem-att-name-wrap">
                                <span class="eem-att-name"><?= htmlspecialchars($student['name']) ?></span>
                                <?php if (!empty($student['is_teacher'])): ?><span class="eem-att-teacher">Professor</span><?php endif; ?>
                            </div>
                            <button type="button" class="eem-att-toggle attendance-check <?= $student['present'] ? 'is-present' : '' ?>" id="att_<?= $student['student_record_id'] ?>" data-present="<?= $student['present'] ? '1' : '0' ?>">
                                <span class="eem-att-toggle-dot"></span>
                                <span class="eem-att-toggle-label"><?= $student['present'] ? 'Presente' : 'Ausente' ?></span>
                            </button>
                            <input type="hidden" name="attendance[<?= $student['student_record_id'] ?>]" id="hidden_att_<?= $student['student_record_id'] ?>" value="<?= $student['present'] ? '1' : '' ?>">
                            <input type="hidden" name="student_ids[]" value="<?= $student['student_record_id'] ?>">
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($students)): ?>
                        <div class="text-center text-muted small py-3">Nenhum aluno matriculado nesta classe.</div>
                    <?php else: ?>
                        <div class="eem-att-footer">
                            <span id="eemPresentCount"><?= count(array_filter($students, fn($s) => $s['present'])) ?> de <?= count($students) ?> presentes</span>
                            <span><i class="fas fa-circle-info me-1"></i>toque para alternar</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="eem-bottom-bar">
                    <a href="/admin/ebd/lessons/show/<?= $lesson['id'] ?>" class="eem-cancel-link">Cancelar</a>
                    <button type="submit" class="eem-save-btn">Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Desktop table checkboxes mirror the mobile toggle buttons (single source of truth: hidden inputs)
    document.querySelectorAll('.attendance-check-desktop').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var toggle = document.getElementById(this.getAttribute('data-sync'));
            if (toggle) toggle.click();
        });
    });
    var checkAllDesktop = document.getElementById('checkAllDesktop');
    if (checkAllDesktop) {
        checkAllDesktop.addEventListener('change', function () {
            var checked = this.checked;
            document.querySelectorAll('.attendance-check').forEach(function (btn) {
                var isPresent = btn.getAttribute('data-present') === '1';
                if (isPresent !== checked) btn.click();
            });
        });
    }

    // Mobile: sync plain fields (date/topic/offerings/notes) into the desktop-named
    // hidden inputs the controller expects, since only one set of fields is submitted.
    (function () {
        var fieldMap = { date: 'date', topic: 'topic', offerings: 'offerings', notes: 'notes' };
        Object.keys(fieldMap).forEach(function (key) {
            var mobileEl = document.querySelector('[data-sync="' + key + '"]');
            var desktopEl = document.getElementById(fieldMap[key]);
            if (!mobileEl) return;
            if (!desktopEl) {
                mobileEl.setAttribute('name', key);
                return;
            }
            mobileEl.addEventListener('input', function () { desktopEl.value = mobileEl.value; });
        });
    })();

    // Métricas counters (mobile) mirror into the real, named desktop inputs
    document.querySelectorAll('.eem-counter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.getAttribute('data-target'));
            var delta = parseInt(btn.getAttribute('data-delta'), 10);
            var newVal = Math.max(0, (parseInt(target.textContent, 10) || 0) + delta);
            target.textContent = newVal;
            var realId = btn.getAttribute('data-target').replace('_m', '');
            var realInput = document.getElementById(realId);
            if (realInput) realInput.value = newVal;
        });
    });

    // Attendance toggle buttons
    var presentCountEl = document.getElementById('eemPresentCount');
    var totalStudents = document.querySelectorAll('.attendance-check').length;
    function updatePresentCount() {
        if (!presentCountEl) return;
        var n = document.querySelectorAll('.attendance-check.is-present').length;
        presentCountEl.textContent = n + ' de ' + totalStudents + ' presentes';
    }
    document.querySelectorAll('.attendance-check').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var isPresent = btn.getAttribute('data-present') === '1';
            var next = !isPresent;
            btn.setAttribute('data-present', next ? '1' : '0');
            btn.classList.toggle('is-present', next);
            btn.querySelector('.eem-att-toggle-label').textContent = next ? 'Presente' : 'Ausente';
            var hidden = document.getElementById('hidden_' + btn.id);
            if (hidden) hidden.value = next ? '1' : '';
            updatePresentCount();
        });
    });

    var markAllBtn = document.getElementById('eemMarkAll');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
            var turningOn = !markAllBtn.classList.contains('is-on');
            markAllBtn.classList.toggle('is-on', turningOn);
            document.querySelectorAll('.attendance-check').forEach(function (btn) {
                var isPresent = btn.getAttribute('data-present') === '1';
                if (isPresent !== turningOn) btn.click();
            });
        });
    }

    // Compact back button (same history.back()+fallback pattern used elsewhere in the app)
    (function () {
        var btn = document.getElementById('eemBackBtn');
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
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

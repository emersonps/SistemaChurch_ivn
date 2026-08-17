<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/service_reports" class="text-decoration-none">Relatórios de Culto</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Editar Relatório de Culto</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/service_reports" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="reportForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
        margin-bottom: 1.25rem;
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
    .member-form-card-body .form-control,
    .member-form-card-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus,
    .member-form-card-body .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
    .required-mark { color: #dc3545; }

    .member-summary-box .summary-label {
        font-size: .76rem;
        color: #868e96;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .member-summary-box .summary-value {
        font-weight: 700;
        color: #212529;
        margin-bottom: .9rem;
    }
    .member-summary-box .summary-value.text-muted-value { color: #adb5bd; font-weight: 500; }
    .member-summary-note {
        font-size: .8rem;
        color: #868e96;
    }

    .attendance-count-box {
        background: #fafafa;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
    }
    .attendance-total-box {
        background: rgba(179,0,0,0.06);
        border: 1px solid rgba(179,0,0,0.15);
        border-radius: 12px;
    }
    .attendance-total-box h3 { color: #b30000; }
</style>

<div class="row">
<div class="col-lg-8">
<form action="/admin/service_reports/update/<?= $report['id'] ?>" method="POST" id="reportForm" class="app-form-with-bottom-actions">
    <?= csrf_field() ?>

    <!-- 1. Informações do Culto -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">1</div>
            <div>
                <div class="member-form-card-title">Informações do Culto</div>
                <div class="member-form-card-subtitle">Quando e quem conduziu o culto.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="congregation_id" class="form-label">Congregação <span class="required-mark">*</span></label>
                    <select class="form-select" id="congregation_id" name="congregation_id" required>
                        <?php foreach ($congregations as $cong): ?>
                            <option value="<?= $cong['id'] ?>" <?= $cong['id'] == $report['congregation_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cong['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Data <span class="required-mark">*</span></label>
                    <input type="date" class="form-control" id="date" name="date" value="<?= !empty($report['date']) ? date('Y-m-d', strtotime($report['date'])) : '' ?>" required>
                </div>
                <div class="col-md-2">
                    <label for="day_of_week" class="form-label">Dia da Semana</label>
                    <input type="text" class="form-control" id="day_of_week" readonly>
                </div>
                <div class="col-md-3">
                    <label for="time" class="form-label">Horário <span class="required-mark">*</span></label>
                    <input type="time" class="form-control" id="time" name="time" value="<?= $report['time'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="leader_name" class="form-label">Dirigente <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" id="leader_name" name="leader_name" value="<?= htmlspecialchars($report['leader_name']) ?>" list="members_list" required autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label for="preacher_name" class="form-label">Pregador <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" id="preacher_name" name="preacher_name" value="<?= htmlspecialchars($report['preacher_name']) ?>" list="members_list" required autocomplete="off">
                </div>
            </div>
        </div>
    </div>

    <datalist id="members_list">
        <?php foreach ($members as $member): ?>
            <option value="<?= htmlspecialchars($member['name']) ?>">
        <?php endforeach; ?>
    </datalist>

    <!-- 2. Contagem de Presença -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">2</div>
            <div>
                <div class="member-form-card-title">Contagem de Presença</div>
                <div class="member-form-card-subtitle">Quantas pessoas participaram do culto.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3 text-center">
                <div class="col attendance-count-box py-2">
                    <label class="form-label fw-bold">Homens</label>
                    <input type="number" class="form-control text-center" name="attendance_men" value="<?= $report['attendance_men'] ?>" min="0" onchange="updateTotal()">
                </div>
                <div class="col attendance-count-box py-2">
                    <label class="form-label fw-bold">Mulheres</label>
                    <input type="number" class="form-control text-center" name="attendance_women" value="<?= $report['attendance_women'] ?>" min="0" onchange="updateTotal()">
                </div>
                <div class="col attendance-count-box py-2">
                    <label class="form-label fw-bold">Jovens</label>
                    <input type="number" class="form-control text-center" name="attendance_youth" value="<?= $report['attendance_youth'] ?>" min="0" onchange="updateTotal()">
                </div>
                <div class="col attendance-count-box py-2">
                    <label class="form-label fw-bold">Crianças</label>
                    <input type="number" class="form-control text-center" name="attendance_children" value="<?= $report['attendance_children'] ?>" min="0" onchange="updateTotal()">
                </div>
                <div class="col attendance-count-box py-2">
                    <label class="form-label fw-bold">Visitantes</label>
                    <input type="number" class="form-control text-center" name="attendance_visitors" value="<?= $report['attendance_visitors'] ?>" min="0" onchange="updateTotal()">
                </div>
                <div class="col attendance-total-box py-2">
                    <label class="form-label fw-bold">TOTAL</label>
                    <h3 id="totalAttendance" class="mb-0"><?= $report['total_attendance'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Financeiro (Removido a pedido - Deve ser lançado no Módulo Financeiro) -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i> As ofertas e dízimos devem ser lançados diretamente no menu <strong>Financeiro > Entradas</strong>.
    </div>

    <!-- 3. Registro de Pessoas -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">3</div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="member-form-card-title">Registro de Pessoas</div>
                        <div class="member-form-card-subtitle">Visitantes, decisões e outras ações do culto.</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3" onclick="addPeopleRow()">
                        <i class="fas fa-plus me-1"></i> Adicionar
                    </button>
                </div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="peopleTable">
                    <thead>
                        <tr>
                            <th style="width: 40%">Nome Completo</th>
                            <th style="width: 25%">Situação/Ação</th>
                            <th style="width: 25%">Observação</th>
                            <th style="width: 10%">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $peopleRowCount = 0;
                        foreach ($people as $p):
                        ?>
                        <tr>
                            <td>
                                <input type="text" class="form-control" name="people[<?= $peopleRowCount ?>][name]" value="<?= htmlspecialchars($p['name']) ?>" list="members_list" required autocomplete="off">
                            </td>
                            <td>
                                <select class="form-select" name="people[<?= $peopleRowCount ?>][action_type]" required>
                                    <option value="Visitante" <?= $p['action_type'] == 'Visitante' ? 'selected' : '' ?>>Visitante</option>
                                    <option value="Aceitou Jesus" <?= $p['action_type'] == 'Aceitou Jesus' ? 'selected' : '' ?>>Aceitou Jesus</option>
                                    <option value="Reconciliado" <?= $p['action_type'] == 'Reconciliado' ? 'selected' : '' ?>>Reconciliado</option>
                                    <option value="Disciplinado" <?= $p['action_type'] == 'Disciplinado' ? 'selected' : '' ?>>Disciplinado</option>
                                    <option value="Desligamento" <?= $p['action_type'] == 'Desligamento' ? 'selected' : '' ?>>Desligamento</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="people[<?= $peopleRowCount ?>][observation]" value="<?= htmlspecialchars($p['observation'] ?? '') ?>">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php $peopleRowCount++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. Observações -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">4</div>
            <div>
                <div class="member-form-card-title">Observações Finais</div>
                <div class="member-form-card-subtitle">Notas gerais sobre o culto.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($report['notes'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/service_reports" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Congregação</div>
                <div class="summary-value" id="summaryCongregation">—</div>

                <div class="summary-label">Data / Horário</div>
                <div class="summary-value" id="summaryDate">—</div>

                <div class="summary-label">Dirigente</div>
                <div class="summary-value" id="summaryLeader">—</div>

                <div class="summary-label">Total de Presença</div>
                <div class="summary-value mb-2" id="summaryTotal">0</div>

                <hr>
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Preenchimento</span>
                    <span id="summaryProgressPct">0%</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-dark" id="summaryProgressBar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <div class="member-form-card">
            <div class="member-form-card-body member-summary-note">
                Campos marcados com <span class="required-mark">*</span> são obrigatórios.
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // --- Member Data for JS ---
    const membersData = <?php echo json_encode($members); ?>;

    // --- Date/Day Logic ---
    const dateInput = document.getElementById('date');
    const dayInput = document.getElementById('day_of_week');

    function updateDayOfWeek() {
        const days = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
        const date = new Date(dateInput.value);
        if (!isNaN(date.getTime())) {
            const dateObj = new Date(dateInput.value + 'T00:00:00');
            dayInput.value = days[dateObj.getDay()];
        }
    }
    dateInput.addEventListener('change', updateDayOfWeek);
    updateDayOfWeek(); // Init

    // --- Attendance Logic ---
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('input[name^="attendance_"]').forEach(input => {
            total += parseInt(input.value) || 0;
        });
        document.getElementById('totalAttendance').innerText = total;
        updateReportSummary();
    }

    // --- People Logic ---
    let peopleRowCount = <?= $peopleRowCount ?>;
    function addPeopleRow() {
        const table = document.getElementById('peopleTable').getElementsByTagName('tbody')[0];
        const row = table.insertRow();
        const rowId = peopleRowCount;

        row.innerHTML = `
            <td>
                <input type="text" class="form-control" name="people[${rowId}][name]" list="members_list" required autocomplete="off">
            </td>
            <td>
                <select class="form-select" name="people[${rowId}][action_type]" required>
                    <option value="Visitante">Visitante</option>
                    <option value="Aceitou Jesus">Aceitou Jesus</option>
                    <option value="Reconciliado">Reconciliado</option>
                    <option value="Disciplinado">Disciplinado</option>
                    <option value="Desligamento">Desligamento</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control" name="people[${rowId}][observation]">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash"></i></button>
            </td>
        `;
        peopleRowCount++;
    }

    function removeRow(btn) {
        const row = btn.closest('tr');
        row.remove();
    }

    // Painel de resumo lateral (congregação, data, dirigente, total, % preenchido)
    const reportForm = document.getElementById('reportForm');
    const summaryCongregation = document.getElementById('summaryCongregation');
    const summaryDate = document.getElementById('summaryDate');
    const summaryLeader = document.getElementById('summaryLeader');
    const summaryTotal = document.getElementById('summaryTotal');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');
    const congregationSelect = document.getElementById('congregation_id');
    const leaderInput = document.getElementById('leader_name');
    const timeInput = document.getElementById('time');

    function updateReportSummary() {
        const congOption = congregationSelect.options[congregationSelect.selectedIndex];
        summaryCongregation.textContent = congOption ? congOption.text : '—';

        const dateVal = dateInput.value;
        const timeVal = timeInput.value;
        summaryDate.textContent = (dateVal || timeVal) ? `${dateVal ? dateVal.split('-').reverse().join('/') : '—'} ${timeVal || ''}`.trim() : '—';

        const leaderVal = leaderInput.value.trim();
        summaryLeader.textContent = leaderVal || '—';

        summaryTotal.textContent = document.getElementById('totalAttendance').innerText;

        const requiredFields = Array.from(reportForm.querySelectorAll('[required]')).filter(f => !f.name.startsWith('people['));
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    reportForm.addEventListener('input', updateReportSummary);
    reportForm.addEventListener('change', updateReportSummary);
    updateReportSummary();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

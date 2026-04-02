<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Relatório de Culto</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/service_reports" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<form action="/admin/service_reports/create" method="POST" id="reportForm">
    <?= csrf_field() ?>
    
    <!-- Dados Gerais -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações do Culto</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="congregation_id" class="form-label">Congregação</label>
                    <select class="form-select" id="congregation_id" name="congregation_id" required>
                        <?php foreach ($congregations as $cong): ?>
                            <option value="<?= $cong['id'] ?>"><?= htmlspecialchars($cong['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Data</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-2">
                    <label for="day_of_week" class="form-label">Dia da Semana</label>
                    <input type="text" class="form-control" id="day_of_week" readonly>
                </div>
                <div class="col-md-3">
                    <label for="time" class="form-label">Horário</label>
                    <input type="time" class="form-control" id="time" name="time" value="19:00" required>
                </div>
                <div class="col-md-6">
                    <label for="leader_name" class="form-label">Dirigente</label>
                    <input type="text" class="form-control" id="leader_name" name="leader_name" list="members_list" required autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label for="preacher_name" class="form-label">Pregador</label>
                    <input type="text" class="form-control" id="preacher_name" name="preacher_name" list="members_list" required autocomplete="off">
                </div>
            </div>
        </div>
    </div>

    <datalist id="members_list">
        <?php foreach ($members as $member): ?>
            <option value="<?= htmlspecialchars($member['name']) ?>">
        <?php endforeach; ?>
    </datalist>

    <!-- Presença -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Contagem de Presença</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col">
                    <label class="form-label fw-bold">Homens</label>
                    <input type="number" class="form-control text-center" name="attendance_men" value="0" min="0" onchange="updateTotal()">
                </div>
                <div class="col">
                    <label class="form-label fw-bold">Mulheres</label>
                    <input type="number" class="form-control text-center" name="attendance_women" value="0" min="0" onchange="updateTotal()">
                </div>
                <div class="col">
                    <label class="form-label fw-bold">Jovens</label>
                    <input type="number" class="form-control text-center" name="attendance_youth" value="0" min="0" onchange="updateTotal()">
                </div>
                <div class="col">
                    <label class="form-label fw-bold">Crianças</label>
                    <input type="number" class="form-control text-center" name="attendance_children" value="0" min="0" onchange="updateTotal()">
                </div>
                <div class="col">
                    <label class="form-label fw-bold">Visitantes</label>
                    <input type="number" class="form-control text-center" name="attendance_visitors" value="0" min="0" onchange="updateTotal()">
                </div>
                <div class="col bg-light border rounded ms-2">
                    <label class="form-label fw-bold mt-2">TOTAL</label>
                    <h3 id="totalAttendance" class="text-primary">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Financeiro (Removido a pedido - Deve ser lançado no Módulo Financeiro) -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i> As ofertas e dízimos devem ser lançados diretamente no menu <strong>Financeiro > Entradas</strong>.
    </div>

    <!-- Pessoas / Ações -->
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registro de Pessoas (Visitantes, Decisões, etc.)</h5>
            <button type="button" class="btn btn-sm btn-success" onclick="addPeopleRow()">
                <i class="fas fa-plus"></i> Adicionar
            </button>
        </div>
        <div class="card-body">
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
                        <!-- Rows will be added here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Observações Finais -->
    <div class="mb-4">
        <label for="notes" class="form-label">Observações Gerais do Culto</label>
        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Salvar Relatório</button>
    </div>

</form>

<!-- Template for Member Options -->
<datalist id="memberList">
    <?php foreach ($members as $m): ?>
        <option value="<?= htmlspecialchars($m['name']) ?>"></option>
    <?php endforeach; ?>
</datalist>

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
            // Fix timezone issue by adding T00:00
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
    }

    // --- People Logic ---
    let peopleRowCount = 0;
    function addPeopleRow() {
        const table = document.getElementById('peopleTable').getElementsByTagName('tbody')[0];
        const row = table.insertRow();
        
        row.innerHTML = `
            <td>
                <input type="text" class="form-control" name="people[${peopleRowCount}][name]" list="members_list" required autocomplete="off">
            </td>
            <td>
                <select class="form-select" name="people[${peopleRowCount}][action_type]" required>
                    <option value="Visitante">Visitante</option>
                    <option value="Aceitou Jesus">Aceitou Jesus</option>
                    <option value="Reconciliado">Reconciliado</option>
                    <option value="Disciplinado">Disciplinado</option>
                    <option value="Desligamento">Desligamento</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control" name="people[${peopleRowCount}][observation]">
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

    // Initialize with one row each
    addPeopleRow();

</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

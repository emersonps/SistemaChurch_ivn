<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Lançar Aula - <?= htmlspecialchars($class['name']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<form action="/admin/ebd/lessons/create/<?= $class['id'] ?>" method="POST">
    <?= csrf_field() ?>
    <div class="row">
        <!-- Detalhes da Aula -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Dados da Aula</h6>
                </div>
                <div class="card-body">
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
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-white">Lista de Presença</h6>
                    <span class="badge bg-white text-primary"><?= count($students) ?> Alunos</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
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
                                    <span class="fw-medium">
                                        <?= htmlspecialchars($student['name']) ?>
                                        <?php if (!empty($student['is_teacher'])): ?>
                                            <span class="badge bg-info text-dark ms-2" style="font-size: 0.7em;">Professor</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success present-badge" id="badge_<?= $student['student_record_id'] ?>">Presente</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-2"></i>
                                    <p>Nenhum aluno matriculado nesta classe.</p>
                                    <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-sm btn-outline-primary">Matricular Alunos</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white py-3 text-end">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-check-circle me-2"></i> Finalizar Aula
                    </button>
                </div>
            </div>
        </div>
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
            badge.className = 'badge bg-success present-badge';
            badge.innerText = 'Presente';
        } else {
            badge.className = 'badge bg-secondary present-badge';
            badge.innerText = 'Ausente';
        }
    }
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

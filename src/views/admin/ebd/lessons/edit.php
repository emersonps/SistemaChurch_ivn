<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
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

<div class="row">
    <div class="col-md-10 offset-md-1">
        <form action="/admin/ebd/lessons/edit/<?= $lesson['id'] ?>" method="POST">
            <?= csrf_field() ?>
            <div class="card shadow-sm border-0 mb-4">
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
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?= htmlspecialchars($lesson['notes']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2 text-primary"></i> Chamada / Presença</h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="checkAll">
                        <label class="form-check-label" for="checkAll">Marcar Todos</label>
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
                                            <input class="form-check-input attendance-check" type="checkbox" 
                                                   name="attendance[<?= $student['student_record_id'] ?>]" 
                                                   value="1" 
                                                   <?= $student['present'] ? 'checked' : '' ?>>
                                            <!-- Hidden input to ensure student ID is sent even if unchecked -->
                                            <input type="hidden" name="student_ids[]" value="<?= $student['student_record_id'] ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <label class="form-check-label w-100 cursor-pointer" for="att_<?= $student['student_record_id'] ?>">
                                            <?= htmlspecialchars($student['name']) ?>
                                            <?php if (!empty($student['is_teacher'])): ?>
                                                <span class="badge bg-info text-dark ms-2" style="font-size: 0.7em;">Professor</span>
                                            <?php endif; ?>
                                        </label>
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

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
                <a href="/admin/ebd/lessons/show/<?= $lesson['id'] ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-save me-2"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('checkAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.attendance-check');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

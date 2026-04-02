<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Detalhes da Aula</h1>
        <h5 class="text-muted"><?= htmlspecialchars($lesson['class_name']) ?></h5>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/ebd/lessons/edit/<?= $lesson['id'] ?>" class="btn btn-sm btn-outline-primary me-2">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="/admin/ebd/classes/show/<?= $lesson['class_id'] ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar para Classe
        </a>
    </div>
</div>

<div class="row">
    <!-- Card Principal -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i> 
                        <?= date('d/m/Y', strtotime($lesson['lesson_date'])) ?>
                    </h5>
                    <span class="badge bg-secondary"><?= htmlspecialchars($lesson['topic']) ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block text-uppercase">Visitantes</small>
                            <h3 class="mb-0 text-primary"><?= $lesson['visitors_count'] ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block text-uppercase">Bíblias</small>
                            <h3 class="mb-0 text-info"><?= $lesson['bibles_count'] ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block text-uppercase">Revistas</small>
                            <h3 class="mb-0 text-warning"><?= $lesson['magazines_count'] ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block text-uppercase">Oferta</small>
                            <h3 class="mb-0 text-success">R$ <?= number_format($lesson['offerings'], 2, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>

                <?php if ($lesson['notes']): ?>
                <div class="alert alert-secondary">
                    <strong><i class="fas fa-sticky-note me-1"></i> Observações:</strong><br>
                    <?= nl2br(htmlspecialchars($lesson['notes'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lista de Presença -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h6 class="mb-0">Chamada / Presença</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance as $att): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($att['student_name']) ?>
                                <?php if (!empty($att['is_teacher'])): ?>
                                    <span class="badge bg-info text-dark ms-2" style="font-size: 0.75em;">Professor</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($att['present']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> Presente</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i> Ausente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($attendance)): ?>
                        <tr>
                            <td colspan="2" class="text-center py-3 text-muted">Nenhum registro de presença encontrado.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card Lateral Financeiro -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-success text-white">
            <div class="card-body">
                <h5 class="card-title border-bottom border-white pb-2 mb-3">Resumo Financeiro</h5>
                <p class="mb-1">Valor Arrecadado:</p>
                <h2 class="mb-3">R$ <?= number_format($lesson['offerings'], 2, ',', '.') ?></h2>
                <p class="small opacity-75 mb-0">
                    <i class="fas fa-check-circle me-1"></i> 
                    Integrado ao Caixa Geral como "Oferta EBD"
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

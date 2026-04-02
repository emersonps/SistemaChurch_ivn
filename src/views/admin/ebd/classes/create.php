<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Nova Classe EBD</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/ebd/classes" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <form action="/admin/ebd/classes/create" method="POST" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome da Classe</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Ex: Jovens, Adultos, Crianças" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="min_age" class="form-label">Idade Mínima</label>
                            <input type="number" class="form-control" id="min_age" name="min_age" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="max_age" class="form-label">Idade Máxima</label>
                            <input type="number" class="form-control" id="max_age" name="max_age" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="congregation_id" class="form-label">Congregação</label>
                        <select class="form-select" id="congregation_id" name="congregation_id">
                            <?php if (empty($_SESSION['user_congregation_id'])): ?>
                                <option value="">Global (Todas)</option>
                            <?php endif; ?>
                            <?php foreach ($congregations as $cong): ?>
                                <option value="<?= $cong['id'] ?>"><?= htmlspecialchars($cong['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecione se a classe for específica de uma congregação.</div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Salvar Classe
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

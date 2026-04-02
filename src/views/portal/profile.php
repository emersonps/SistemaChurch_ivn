<?php include __DIR__ . '/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Meus Dados</h1>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Dados atualizados com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4 text-center">
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (!empty($member['photo'])): ?>
                    <img src="/uploads/members/<?= $member['photo'] ?>" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white mx-auto mb-3" style="width: 150px; height: 150px; font-size: 3rem;">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <h5 class="card-title"><?= htmlspecialchars($member['name']) ?></h5>
                <p class="text-muted mb-1"><?= htmlspecialchars($member['role'] ?? 'Membro') ?></p>
                <span class="badge bg-<?= $member['status'] == 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($member['status']) ?></span>
            </div>
        </div>

        <!-- Minhas Atividades -->
        <div class="card shadow-sm mt-4 text-start">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-layer-group me-2 text-primary"></i> Minhas Atividades</h6>
            </div>
            <ul class="list-group list-group-flush">
                <!-- Grupos -->
                <li class="list-group-item bg-light fw-bold small text-uppercase text-muted"><i class="fas fa-users me-1"></i> Grupos / Células</li>
                <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $g): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center ps-4">
                            <span><?= htmlspecialchars($g['name']) ?></span>
                            <span class="badge bg-secondary rounded-pill" style="font-size: 0.7em;"><?= ucfirst($g['role']) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted small fst-italic ps-4">Não participa de nenhum grupo.</li>
                <?php endif; ?>

                <!-- EBD -->
                <li class="list-group-item bg-light fw-bold small text-uppercase text-muted"><i class="fas fa-book-reader me-1"></i> Escola Bíblica</li>
                <?php if (!empty($ebdStudentClasses)): ?>
                    <?php foreach ($ebdStudentClasses as $c): ?>
                        <li class="list-group-item ps-4">
                            <div class="fw-medium"><?= htmlspecialchars($c['name']) ?></div>
                            <small class="text-muted"><i class="fas fa-user-graduate me-1"></i> Aluno desde <?= date('d/m/Y', strtotime($c['enrolled_at'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!empty($ebdTeacherClasses)): ?>
                    <?php foreach ($ebdTeacherClasses as $c): ?>
                        <li class="list-group-item ps-4">
                            <div class="fw-medium"><?= htmlspecialchars($c['name']) ?></div>
                            <span class="badge bg-primary mt-1"><i class="fas fa-chalkboard-teacher me-1"></i> Professor</span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (empty($ebdStudentClasses) && empty($ebdTeacherClasses)): ?>
                    <li class="list-group-item text-muted small fst-italic ps-4">Nenhuma classe vinculada.</li>
                <?php endif; ?>

                <!-- Usuário do Sistema -->
                <?php if (!empty($systemUsers)): ?>
                    <li class="list-group-item bg-light fw-bold small text-uppercase text-muted"><i class="fas fa-user-shield me-1"></i> Acesso ao Sistema</li>
                    <?php foreach ($systemUsers as $u): ?>
                        <li class="list-group-item ps-4">
                            <div class="fw-medium"><?= htmlspecialchars($u['username']) ?></div>
                            <span class="badge bg-info text-dark mt-1"><?= ucfirst($u['role']) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Editar Informações</h5>
            </div>
            <div class="card-body">
                <form action="/portal/profile" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Foto de Perfil</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone (WhatsApp)</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
                        </div>
                        
                        <h6 class="mt-4 mb-2 text-muted border-bottom pb-2">Endereço</h6>
                        
                        <div class="col-md-9">
                            <label class="form-label">Rua</label>
                            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($member['address'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Número</label>
                            <input type="text" class="form-control" name="address_number" value="<?= htmlspecialchars($member['address_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Bairro</label>
                            <input type="text" class="form-control" name="neighborhood" value="<?= htmlspecialchars($member['neighborhood'] ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Cidade</label>
                            <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($member['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <input type="text" class="form-control" name="state" value="<?= htmlspecialchars($member['state'] ?? '') ?>" maxlength="2">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CEP</label>
                            <input type="text" class="form-control" name="zip_code" value="<?= htmlspecialchars($member['zip_code'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2 text-secondary"></i> Meus Documentos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($memberDocuments)): ?>
                    <p class="text-muted">Nenhum documento anexado.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($memberDocuments as $d): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($d['title']) ?></strong>
                                    <small class="text-muted ms-2"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></small>
                                </div>
                                <a href="/portal/documents/open/<?= $d['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> Abrir
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

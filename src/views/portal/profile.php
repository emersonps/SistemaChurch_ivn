<?php include __DIR__ . '/layout/header.php'; ?>

<style>
    .portal-profile-photo {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 10px rgba(17,17,17,0.1);
    }
    .portal-profile-photo-fallback {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: rgba(179,0,0,0.08);
        color: var(--portal-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto;
    }
    .portal-activity-group-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 800;
        color: #adb5bd;
        padding: .6rem 1.25rem .3rem;
    }
    .portal-activity-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding: .55rem 1.25rem;
        border-top: 1px solid rgba(0,0,0,0.05);
    }
</style>

<div class="portal-page-title">Meus Dados</div>
<p class="text-muted mb-3">Mantenha seu cadastro atualizado.</p>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
        <i class="fas fa-check-circle me-2"></i> Dados atualizados com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="portal-card text-center p-4">
            <?php if (!empty($member['photo'])): ?>
                <img src="/uploads/members/<?= $member['photo'] ?>" class="portal-profile-photo mb-3">
            <?php else: ?>
                <div class="portal-profile-photo-fallback mb-3"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($member['name']) ?></h5>
            <p class="text-muted mb-2"><?= htmlspecialchars($member['role'] ?? 'Membro') ?></p>
            <span class="portal-pill <?= $member['status'] == 'active' ? 'portal-pill-green' : 'portal-pill-gray' ?>"><?= ucfirst($member['status']) ?></span>
        </div>

        <div class="portal-card mt-3">
            <div class="portal-card-header">
                <div class="portal-card-title"><i class="fas fa-layer-group text-primary me-2"></i> Minhas Atividades</div>
            </div>
            <div class="pb-2">
                <div class="portal-activity-group-label"><i class="fas fa-users me-1"></i> Grupos / Células</div>
                <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $g): ?>
                        <div class="portal-activity-item">
                            <span class="small"><?= htmlspecialchars($g['name']) ?></span>
                            <span class="portal-pill portal-pill-gray"><?= ucfirst($g['role']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="portal-activity-item"><span class="text-muted small fst-italic">Não participa de nenhum grupo.</span></div>
                <?php endif; ?>

                <div class="portal-activity-group-label"><i class="fas fa-book-reader me-1"></i> Escola Bíblica</div>
                <?php if (!empty($ebdStudentClasses)): ?>
                    <?php foreach ($ebdStudentClasses as $c): ?>
                        <div class="portal-activity-item">
                            <div>
                                <div class="small fw-medium"><?= htmlspecialchars($c['name']) ?></div>
                                <small class="text-muted"><i class="fas fa-user-graduate me-1"></i> Aluno desde <?= date('d/m/Y', strtotime($c['enrolled_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!empty($ebdTeacherClasses)): ?>
                    <?php foreach ($ebdTeacherClasses as $c): ?>
                        <div class="portal-activity-item">
                            <span class="small fw-medium"><?= htmlspecialchars($c['name']) ?></span>
                            <span class="portal-pill portal-pill-red"><i class="fas fa-chalkboard-teacher me-1"></i> Professor</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (empty($ebdStudentClasses) && empty($ebdTeacherClasses)): ?>
                    <div class="portal-activity-item"><span class="text-muted small fst-italic">Nenhuma classe vinculada.</span></div>
                <?php endif; ?>

                <?php if (!empty($systemUsers)): ?>
                    <div class="portal-activity-group-label"><i class="fas fa-user-shield me-1"></i> Acesso ao Sistema</div>
                    <?php foreach ($systemUsers as $u): ?>
                        <div class="portal-activity-item">
                            <span class="small fw-medium"><?= htmlspecialchars($u['username']) ?></span>
                            <span class="portal-pill" style="background: rgba(13,202,240,.14); color:#087990;"><?= ucfirst($u['role']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="portal-card">
            <div class="portal-card-header">
                <div class="portal-card-title"><i class="fas fa-user-pen text-primary me-2"></i> Editar Informações</div>
            </div>
            <div class="p-4">
                <form action="/portal/profile" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Foto de Perfil</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefone (WhatsApp)</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <div class="portal-section-label mb-1 mt-1"><span>Endereço</span></div>
                        </div>

                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Rua</label>
                            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($member['address'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Número</label>
                            <input type="text" class="form-control" name="address_number" value="<?= htmlspecialchars($member['address_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Bairro</label>
                            <input type="text" class="form-control" name="neighborhood" value="<?= htmlspecialchars($member['neighborhood'] ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Cidade</label>
                            <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($member['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">UF</label>
                            <input type="text" class="form-control" name="state" value="<?= htmlspecialchars($member['state'] ?? '') ?>" maxlength="2">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">CEP</label>
                            <input type="text" class="form-control" name="zip_code" value="<?= htmlspecialchars($member['zip_code'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-dark rounded-pill fw-semibold px-4">
                            <i class="fas fa-save me-2"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="portal-card mt-3">
            <div class="portal-card-header">
                <div class="portal-card-title"><i class="fas fa-file-alt text-secondary me-2"></i> Meus Documentos</div>
            </div>
            <div class="p-3">
                <?php if (empty($memberDocuments)): ?>
                    <p class="text-muted text-center mb-0 py-3">Nenhum documento anexado.</p>
                <?php else: ?>
                    <?php foreach ($memberDocuments as $d): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(0,0,0,0.05) !important;">
                            <div>
                                <strong class="small"><?= htmlspecialchars($d['title']) ?></strong>
                                <div class="text-muted" style="font-size:.75rem;"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></div>
                            </div>
                            <a href="/portal/documents/open/<?= $d['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="fas fa-download me-1"></i> Abrir
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

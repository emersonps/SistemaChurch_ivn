<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Ficha do Membro</h1>
    <div class="btn-group">
        <a href="/admin/members" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
        <a href="/admin/members/history/<?= $member['id'] ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-history me-1"></i> Histórico
        </a>
        <a href="/admin/members/card/<?= $member['id'] ?>" class="btn btn-outline-info btn-sm">
            <i class="fas fa-id-card me-1"></i> Carteirinha
        </a>
        <?php if (hasPermission('members.manage')): ?>
        <a href="/admin/members/edit/<?= $member['id'] ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-edit me-1"></i> Editar
        </a>
        <?php endif; ?>
        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
        <a href="/admin/members/delete/<?= $member['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este membro?');">
            <i class="fas fa-trash me-1"></i> Excluir
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_GET['warning'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= htmlspecialchars($_GET['warning']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <?php if (!empty($member['photo'])): ?>
                    <img src="/uploads/members/<?= htmlspecialchars($member['photo']) ?>" class="rounded-circle shadow-sm mb-3" style="width: 140px; height: 140px; object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white mb-3" style="width: 140px; height: 140px; font-size: 3rem;">
                        <?= strtoupper(substr($member['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <h4 class="mb-1"><?= htmlspecialchars($member['name']) ?></h4>
                <div class="text-muted"><?= htmlspecialchars($member['role'] ?? 'Membro') ?></div>
                <div class="mt-2">
                    <?php 
                        $status = $member['status'] ?? 'active';
                        $isActive = ($status === 'active' || strtolower(trim($status)) === 'congregando');
                        $label = $isActive ? 'Ativo (Congregando)' : 'Inativo';
                    ?>
                    <span class="badge bg-<?= $isActive ? 'success' : 'secondary' ?>"><?= $label ?></span>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">Congregação</div>
            <div class="card-body">
                <div><?= htmlspecialchars($member['congregation_name'] ?? 'Sem Congregação') ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="row g-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Dados Pessoais</div>
                    <div class="card-body row g-3">
                        <div class="col-md-4"><strong>Gênero:</strong> <?= htmlspecialchars($member['gender'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Data de Nascimento:</strong> <?= !empty($member['birth_date']) ? date('d/m/Y', strtotime($member['birth_date'])) : '-' ?></div>
                        <div class="col-md-4"><strong>Nacionalidade:</strong> <?= htmlspecialchars($member['nationality'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Naturalidade:</strong> <?= htmlspecialchars($member['birthplace'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Estado Civil:</strong> <?= htmlspecialchars($member['marital_status'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Profissão:</strong> <?= htmlspecialchars($member['profession'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Contato</div>
                    <div class="card-body row g-3">
                        <div class="col-md-4"><strong>Telefone:</strong> <?= htmlspecialchars($member['phone'] ?? '-') ?></div>
                        <div class="col-md-8"><strong>Email:</strong> <?= htmlspecialchars($member['email'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Documentos</div>
                    <div class="card-body row g-3">
                        <div class="col-md-4"><strong>CPF:</strong> <?= htmlspecialchars($member['cpf'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>RG:</strong> <?= htmlspecialchars($member['rg'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Endereço</div>
                    <div class="card-body row g-3">
                        <div class="col-md-6"><strong>Endereço:</strong> <?= htmlspecialchars(trim(($member['address'] ?? '') . ' ' . ($member['address_number'] ?? ''))) ?></div>
                        <div class="col-md-6"><strong>Bairro:</strong> <?= htmlspecialchars($member['neighborhood'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Cidade:</strong> <?= htmlspecialchars($member['city'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Estado:</strong> <?= htmlspecialchars($member['state'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>CEP:</strong> <?= htmlspecialchars($member['zip_code'] ?? '-') ?></div>
                        <div class="col-md-6"><strong>Complemento:</strong> <?= htmlspecialchars($member['complement'] ?? '-') ?></div>
                        <div class="col-md-6"><strong>Ponto de Referência:</strong> <?= htmlspecialchars($member['reference_point'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Família</div>
                    <div class="card-body row g-3">
                        <div class="col-md-6"><strong>Pai:</strong> <?= htmlspecialchars($member['father_name'] ?? '-') ?></div>
                        <div class="col-md-6"><strong>Mãe:</strong> <?= htmlspecialchars($member['mother_name'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Filhos:</strong> <?= htmlspecialchars($member['children_count'] ?? '0') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Igreja / Ingresso</div>
                    <div class="card-body row g-3">
                        <div class="col-md-6"><strong>Forma de Ingresso:</strong> <?= htmlspecialchars($member['admission_method'] ?? '-') ?></div>
                        <div class="col-md-3"><strong>Data de Ingresso:</strong> <?= !empty($member['admission_date']) ? date('d/m/Y', strtotime($member['admission_date'])) : '-' ?></div>
                        <div class="col-md-3"><strong>Data de Saída:</strong> <?= !empty($member['exit_date']) ? date('d/m/Y', strtotime($member['exit_date'])) : '-' ?></div>
                        <div class="col-md-6"><strong>Igreja de Origem:</strong> <?= htmlspecialchars($member['church_origin'] ?? '-') ?></div>
                        <div class="col-md-3"><strong>Dizimista:</strong> <span class="badge bg-<?= ($member['is_tither'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($member['is_tither'] ?? 0) ? 'Sim' : 'Não' ?></span></div>
                        <div class="col-md-3"><strong>Professor EBD:</strong> <span class="badge bg-<?= ($member['is_ebd_teacher'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($member['is_ebd_teacher'] ?? 0) ? 'Sim' : 'Não' ?></span></div>
                        <div class="col-12 mt-2">
                            <strong>Carta de Transferência:</strong>
                            <?php if (!empty($transferLetter) && !empty($transferLetter['file_path'])): ?>
                                <?php $ext = strtolower(pathinfo($transferLetter['file_path'], PATHINFO_EXTENSION)); ?>
                                <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                    <div class="d-flex align-items-center gap-3 mt-2">
                                        <img src="/uploads/members_docs/<?= htmlspecialchars($transferLetter['file_path']) ?>" alt="Carta" class="img-thumbnail" style="width: 160px; height: auto;">
                                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="/uploads/members_docs/<?= htmlspecialchars($transferLetter['file_path']) ?>">
                                            <i class="fas fa-external-link-alt me-1"></i> Abrir
                                        </a>
                                    </div>
                                <?php elseif ($ext === 'pdf'): ?>
                                    <div class="mt-2">
                                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="/uploads/members_docs/<?= htmlspecialchars($transferLetter['file_path']) ?>">
                                            <i class="fas fa-file-pdf me-1"></i> Abrir PDF
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-2">
                                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="/uploads/members_docs/<?= htmlspecialchars($transferLetter['file_path']) ?>">
                                            <i class="fas fa-file me-1"></i> Abrir Documento
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Nenhuma carta anexada</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Situação Espiritual</div>
                    <div class="card-body row g-3">
                        <div class="col-md-3"><strong>Batizado:</strong> <span class="badge bg-<?= ($member['is_baptized'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($member['is_baptized'] ?? 0) ? 'Sim' : 'Não' ?></span></div>
                        <div class="col-md-3"><strong>Data Batismo:</strong> <?= !empty($member['baptism_date']) ? date('d/m/Y', strtotime($member['baptism_date'])) : '-' ?></div>
                        <div class="col-md-3"><strong>Novo Convertido:</strong> <span class="badge bg-<?= ($member['is_new_convert'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($member['is_new_convert'] ?? 0) ? 'Sim' : 'Não' ?></span></div>
                        <div class="col-md-3"><strong>Data Aceitação:</strong> <?= !empty($member['accepted_jesus_at']) ? date('d/m/Y', strtotime($member['accepted_jesus_at'])) : '-' ?></div>
                        <div class="col-md-3"><strong>Data Reconciliação:</strong> <?= !empty($member['reconciled_at']) ? date('d/m/Y', strtotime($member['reconciled_at'])) : '-' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

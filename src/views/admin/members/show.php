<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/members" class="text-decoration-none">Membros</a></li>
                <li class="breadcrumb-item active">Ficha</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <a href="/admin/members" class="text-muted text-decoration-none" title="Voltar para a lista">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="h3 mb-0">Ficha do Membro</h1>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group">
            <a href="/admin/members/history/<?= $member['id'] ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-history me-1"></i> Histórico
            </a>
            <a href="/admin/members/card/<?= $member['id'] ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-id-card me-1"></i> Carteirinha
            </a>
        </div>
        <?php if (hasPermission('members.manage')): ?>
        <a href="/admin/members/edit/<?= $member['id'] ?>" class="btn btn-dark btn-sm">
            <i class="fas fa-edit me-1"></i> Editar
        </a>
        <?php endif; ?>
        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
        <a href="/admin/members/delete/<?= $member['id'] ?>" class="btn btn-link btn-sm text-danger text-decoration-none ms-1" onclick="return confirm('Tem certeza que deseja excluir este membro?');" title="Excluir membro">
            <i class="fas fa-trash"></i>
        </a>
        <?php endif; ?>
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

    .info-field .info-label {
        font-size: .76rem;
        color: #868e96;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .info-field .info-value {
        font-weight: 600;
        color: #212529;
    }
    .info-field .info-value.text-muted-value { color: #adb5bd; font-weight: 500; }

    .member-summary-box .summary-photo {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid rgba(0,0,0,0.08);
    }
    .member-summary-box .summary-photo-placeholder {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: #eef0f2;
        color: #495057;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        font-weight: 700;
    }
</style>

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

<?php
    $status = $member['status'] ?? 'active';
    $isActive = ($status === 'active' || strtolower(trim($status)) === 'congregando');
?>

<div class="row">
<div class="col-lg-8">

    <!-- 1. Dados pessoais -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">1</div>
            <div>
                <div class="member-form-card-title">Dados pessoais</div>
                <div class="member-form-card-subtitle">Identificação básica do membro.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-4 info-field">
                    <div class="info-label">Sexo</div>
                    <div class="info-value <?= empty($member['gender']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['gender'] ?? '—') ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Data de nascimento</div>
                    <div class="info-value <?= empty($member['birth_date']) ? 'text-muted-value' : '' ?>"><?= !empty($member['birth_date']) ? date('d/m/Y', strtotime($member['birth_date'])) : '—' ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Nacionalidade</div>
                    <div class="info-value <?= empty($member['nationality']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['nationality'] ?? '—') ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Natural de</div>
                    <div class="info-value <?= empty($member['birthplace']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['birthplace'] ?? '—') ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Estado civil</div>
                    <div class="info-value <?= empty($member['marital_status']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['marital_status'] ?? '—') ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Profissão</div>
                    <div class="info-value <?= empty($member['profession']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['profession'] ?? '—') ?></div>
                </div>

                <div class="col-md-4 info-field">
                    <div class="info-label">CPF</div>
                    <div class="info-value <?= empty($member['cpf']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['cpf'] ?? '—') ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">RG</div>
                    <div class="info-value <?= empty($member['rg']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['rg'] ?? '—') ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Quantidade de filhos</div>
                    <div class="info-value"><?= htmlspecialchars($member['children_count'] ?? '0') ?></div>
                </div>

                <div class="col-md-6 info-field">
                    <div class="info-label">Nome do pai</div>
                    <div class="info-value <?= empty($member['father_name']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['father_name'] ?? '—') ?></div>
                </div>
                <div class="col-md-6 info-field">
                    <div class="info-label">Nome da mãe</div>
                    <div class="info-value <?= empty($member['mother_name']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['mother_name'] ?? '—') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Contato -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">2</div>
            <div>
                <div class="member-form-card-title">Contato</div>
                <div class="member-form-card-subtitle">Formas de falar com o membro.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6 info-field">
                    <div class="info-label">E-mail</div>
                    <div class="info-value <?= empty($member['email']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['email'] ?? '—') ?></div>
                </div>
                <div class="col-md-6 info-field">
                    <div class="info-label">WhatsApp / Celular</div>
                    <div class="info-value <?= empty($member['phone']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['phone'] ?? '—') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Endereço -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">3</div>
            <div>
                <div class="member-form-card-title">Endereço</div>
                <div class="member-form-card-subtitle">Endereço residencial do membro.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-3 info-field">
                    <div class="info-label">CEP</div>
                    <div class="info-value <?= empty($member['zip_code']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['zip_code'] ?? '—') ?></div>
                </div>
                <div class="col-md-6 info-field">
                    <div class="info-label">Rua / Logradouro</div>
                    <div class="info-value <?= empty($member['address']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars(trim(($member['address'] ?? '') ?: '—')) ?></div>
                </div>
                <div class="col-md-3 info-field">
                    <div class="info-label">Número</div>
                    <div class="info-value <?= empty($member['address_number']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['address_number'] ?? '—') ?></div>
                </div>

                <div class="col-md-4 info-field">
                    <div class="info-label">Bairro</div>
                    <div class="info-value <?= empty($member['neighborhood']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['neighborhood'] ?? '—') ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Complemento</div>
                    <div class="info-value <?= empty($member['complement']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['complement'] ?? '—') ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Ponto de referência</div>
                    <div class="info-value <?= empty($member['reference_point']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['reference_point'] ?? '—') ?></div>
                </div>

                <div class="col-md-8 info-field">
                    <div class="info-label">Cidade</div>
                    <div class="info-value <?= empty($member['city']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['city'] ?? '—') ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">UF</div>
                    <div class="info-value <?= empty($member['state']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['state'] ?? '—') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Vida eclesiástica -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">4</div>
            <div>
                <div class="member-form-card-title">Vida eclesiástica</div>
                <div class="member-form-card-subtitle">Vínculo do membro com a igreja.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6 info-field">
                    <div class="info-label">Congregação</div>
                    <div class="info-value <?= empty($member['congregation_name']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['congregation_name'] ?? 'Sem Congregação') ?></div>
                </div>
                <div class="col-md-6 info-field">
                    <div class="info-label">Cargo / Função</div>
                    <div class="info-value"><?= htmlspecialchars($member['role'] ?? 'Membro') ?></div>
                </div>

                <div class="col-md-4 info-field">
                    <div class="info-label">Data de batismo</div>
                    <div class="info-value <?= empty($member['baptism_date']) ? 'text-muted-value' : '' ?>"><?= !empty($member['baptism_date']) ? date('d/m/Y', strtotime($member['baptism_date'])) : '—' ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Membro desde</div>
                    <div class="info-value <?= empty($member['admission_date']) ? 'text-muted-value' : '' ?>"><?= !empty($member['admission_date']) ? date('d/m/Y', strtotime($member['admission_date'])) : '—' ?></div>
                </div>
                <div class="col-md-4 info-field">
                    <div class="info-label">Origem (igreja anterior)</div>
                    <div class="info-value <?= empty($member['church_origin']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['church_origin'] ?? '—') ?></div>
                </div>

                <div class="col-md-6 info-field">
                    <div class="info-label">Forma de ingresso</div>
                    <div class="info-value <?= empty($member['admission_method']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($member['admission_method'] ?? '—') ?></div>
                </div>
                <div class="col-md-6 info-field">
                    <div class="info-label">Data de saída</div>
                    <div class="info-value <?= empty($member['exit_date']) ? 'text-muted-value' : '' ?>"><?= !empty($member['exit_date']) ? date('d/m/Y', strtotime($member['exit_date'])) : '—' ?></div>
                </div>

                <div class="col-12">
                    <div class="info-label mb-2">Carta de transferência</div>
                    <?php if (!empty($transferLetter) && !empty($transferLetter['file_path'])): ?>
                        <?php $ext = strtolower(pathinfo($transferLetter['file_path'], PATHINFO_EXTENSION)); ?>
                        <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                            <div class="d-flex align-items-center gap-3">
                                <img src="/uploads/members_docs/<?= htmlspecialchars($transferLetter['file_path']) ?>" alt="Carta" class="img-thumbnail" style="width: 140px; height: auto;">
                                <a class="btn btn-sm btn-outline-primary" target="_blank" href="/uploads/members_docs/<?= htmlspecialchars($transferLetter['file_path']) ?>">
                                    <i class="fas fa-external-link-alt me-1"></i> Abrir
                                </a>
                            </div>
                        <?php elseif ($ext === 'pdf'): ?>
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="/uploads/members_docs/<?= htmlspecialchars($transferLetter['file_path']) ?>">
                                <i class="fas fa-file-pdf me-1"></i> Abrir PDF
                            </a>
                        <?php else: ?>
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="/uploads/members_docs/<?= htmlspecialchars($transferLetter['file_path']) ?>">
                                <i class="fas fa-file me-1"></i> Abrir Documento
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted-value">Nenhuma carta anexada</span>
                    <?php endif; ?>
                </div>

                <div class="col-12"><hr class="my-1"></div>

                <div class="col-md-3 info-field">
                    <div class="info-label">Dizimista</div>
                    <span class="badge bg-<?= ($member['is_tither'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($member['is_tither'] ?? 0) ? 'Sim' : 'Não' ?></span>
                </div>
                <div class="col-md-3 info-field">
                    <div class="info-label">Professor EBD</div>
                    <span class="badge bg-<?= ($member['is_ebd_teacher'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($member['is_ebd_teacher'] ?? 0) ? 'Sim' : 'Não' ?></span>
                </div>
                <div class="col-md-3 info-field">
                    <div class="info-label">Batizado</div>
                    <span class="badge bg-<?= ($member['is_baptized'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($member['is_baptized'] ?? 0) ? 'Sim' : 'Não' ?></span>
                </div>
                <div class="col-md-3 info-field">
                    <div class="info-label">Novo convertido</div>
                    <span class="badge bg-<?= ($member['is_new_convert'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($member['is_new_convert'] ?? 0) ? 'Sim' : 'Não' ?></span>
                </div>

                <div class="col-md-6 info-field mt-3">
                    <div class="info-label">Data de aceitação (Jesus)</div>
                    <div class="info-value <?= empty($member['accepted_jesus_at']) ? 'text-muted-value' : '' ?>"><?= !empty($member['accepted_jesus_at']) ? date('d/m/Y', strtotime($member['accepted_jesus_at'])) : '—' ?></div>
                </div>
                <div class="col-md-6 info-field mt-3">
                    <div class="info-label">Data de reconciliação</div>
                    <div class="info-value <?= empty($member['reconciled_at']) ? 'text-muted-value' : '' ?>"><?= !empty($member['reconciled_at']) ? date('d/m/Y', strtotime($member['reconciled_at'])) : '—' ?></div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body text-center">
                <?php if (!empty($member['photo'])): ?>
                    <img src="/uploads/members/<?= htmlspecialchars($member['photo']) ?>" class="summary-photo mb-3">
                <?php else: ?>
                    <div class="summary-photo-placeholder mx-auto mb-3"><?= strtoupper(substr($member['name'], 0, 1)) ?></div>
                <?php endif; ?>
                <h5 class="mb-1"><?= htmlspecialchars($member['name']) ?></h5>
                <div class="text-muted small mb-2"><?= htmlspecialchars($member['role'] ?? 'Membro') ?></div>
                <span class="badge bg-<?= $isActive ? 'success' : 'secondary' ?>"><?= $isActive ? 'Ativo (Congregando)' : 'Inativo' ?></span>
            </div>
        </div>
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="info-label mb-1">Congregação</div>
                <div class="info-value"><?= htmlspecialchars($member['congregation_name'] ?? 'Sem Congregação') ?></div>
            </div>
        </div>
    </div>
</div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

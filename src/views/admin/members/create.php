<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/members" class="text-decoration-none">Membros</a></li>
                <li class="breadcrumb-item active">Novo</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Cadastro de Membros</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" id="btnClearForm">Limpar</button>
        <button type="submit" form="memberCreateForm" class="btn btn-dark">Salvar membro</button>
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
    .member-form-card-body {
        padding: 1.25rem;
    }
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

    .status-options { display: flex; gap: .75rem; flex-wrap: wrap; }
    .status-option {
        flex: 1 1 220px;
        border: 1.5px solid rgba(0,0,0,0.12);
        border-radius: 12px;
        padding: .85rem 1rem;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        transition: border-color .15s ease, background-color .15s ease;
    }
    .status-option input { margin-top: .25rem; }
    .status-option .status-title { font-weight: 700; font-size: .92rem; color: #212529; }
    .status-option .status-desc { font-size: .78rem; color: #868e96; }
    .status-option.active {
        border-color: #b30000;
        background: rgba(179,0,0,0.045);
    }

    .photo-uploader { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .photo-uploader-preview {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        object-fit: cover;
        background: #f1f3f5;
        border: 1px solid rgba(0,0,0,0.08);
    }
    .photo-uploader-placeholder {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: #f1f3f5;
        border: 1px solid rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
        font-size: 1.7rem;
    }

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
</style>

<div class="row">
<div class="col-lg-8">
<form action="/admin/members/create" method="POST" enctype="multipart/form-data" class="app-form-with-bottom-actions" id="memberCreateForm">
    <?= csrf_field() ?>

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
                <div class="col-12">
                    <div class="photo-uploader">
                        <img id="photoPreview" class="photo-uploader-preview" style="display:none;">
                        <div id="photoPlaceholder" class="photo-uploader-placeholder"><i class="fas fa-user"></i></div>
                        <div>
                            <label class="btn btn-outline-secondary btn-sm mb-0" for="photoInput">Escolher foto</label>
                            <input type="file" class="d-none" name="photo" id="photoInput" accept="image/*">
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-1" id="btnWebcam" title="Tirar foto com webcam"><i class="fas fa-camera"></i></button>
                            <div class="form-text mb-0">JPG ou PNG quadrado, até 2 MB.</div>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <div id="webcamContainer" style="display:none;" class="border p-2 rounded">
                            <video id="webcamVideo" autoplay style="max-width: 100%; height: auto;"></video>
                            <canvas id="webcamCanvas" width="320" height="240" style="display:none;"></canvas>
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm flex-fill" id="btnCapture">Capturar</button>
                                <button type="button" class="btn btn-secondary btn-sm flex-fill" id="btnCancelWebcam">Cancelar</button>
                            </div>
                        </div>
                        <input type="hidden" name="webcam_photo" id="webcamPhotoData">
                    </div>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Nome completo <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="Ex.: Maria da Silva Souza" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nacionalidade</label>
                    <input type="text" class="form-control" name="nationality" value="Brasileira">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Data de nascimento <span class="required-mark">*</span></label>
                    <input type="date" class="form-control" name="birth_date" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sexo</label>
                    <select class="form-select" name="gender">
                        <option value="">Selecione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Feminino</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Estado civil</label>
                    <select class="form-select" name="marital_status">
                        <option value="">Selecione...</option>
                        <option value="Solteiro(a)">Solteiro(a)</option>
                        <option value="Casado(a)">Casado(a)</option>
                        <option value="Divorciado(a)">Divorciado(a)</option>
                        <option value="Viúvo(a)">Viúvo(a)</option>
                        <option value="Separado(a)">Separado(a)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">CPF</label>
                    <input type="text" class="form-control" name="cpf" placeholder="000.000.000-00">
                </div>

                <div class="col-md-6">
                    <label class="form-label">RG</label>
                    <input type="text" class="form-control" name="rg" placeholder="Documento de identidade">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Natural de (Cidade/Estado) <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="birthplace" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Profissão</label>
                    <input type="text" class="form-control" name="profession">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantidade de filhos</label>
                    <input type="number" class="form-control" name="children_count" min="0" value="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nome do pai</label>
                    <input type="text" class="form-control" name="father_name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nome da mãe <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="mother_name" required>
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
                <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control" name="email" placeholder="ex.: maria@email.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label">WhatsApp / Celular <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="phone" placeholder="(00) 00000-0000" required>
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
                <div class="col-md-3">
                    <label class="form-label">CEP</label>
                    <input type="text" class="form-control" name="zip_code" id="zip_code" placeholder="00000-000">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rua / Logradouro <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="address" placeholder="Av. Principal" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Número <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="address_number" placeholder="123" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Bairro <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="neighborhood" placeholder="Centro" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Complemento</label>
                    <input type="text" class="form-control" name="complement">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ponto de referência</label>
                    <input type="text" class="form-control" name="reference_point">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Cidade <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="city" id="city" placeholder="Manaus" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">UF <span class="required-mark">*</span></label>
                    <select class="form-select" name="state" id="state" required>
                        <option value="">--</option>
                        <option value="AC">AC</option><option value="AL">AL</option><option value="AP">AP</option>
                        <option value="AM">AM</option><option value="BA">BA</option><option value="CE">CE</option>
                        <option value="DF">DF</option><option value="ES">ES</option><option value="GO">GO</option>
                        <option value="MA">MA</option><option value="MT">MT</option><option value="MS">MS</option>
                        <option value="MG">MG</option><option value="PA">PA</option><option value="PB">PB</option>
                        <option value="PR">PR</option><option value="PE">PE</option><option value="PI">PI</option>
                        <option value="RJ">RJ</option><option value="RN">RN</option><option value="RS">RS</option>
                        <option value="RO">RO</option><option value="RR">RR</option>
                        <option value="SP">SP</option><option value="SE">SE</option><option value="TO">TO</option>
                    </select>
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
                <div class="col-md-6">
                    <label class="form-label">Congregação <span class="required-mark">*</span></label>
                    <select class="form-select" name="congregation_id" required>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Selecione a congregação de vínculo do membro.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cargo / Função <span class="required-mark">*</span></label>
                    <div class="input-group">
                        <select class="form-select" name="role" id="roleSelect" required>
                            <option value="">Carregando...</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" id="btnAddRole" title="Adicionar novo cargo">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnEditRole" title="Editar cargo selecionado">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" id="btnDeleteRole" title="Excluir cargo selecionado">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Data de batismo</label>
                    <input type="date" class="form-control" name="baptism_date" id="baptismDateInput" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Membro desde</label>
                    <input type="date" class="form-control" name="admission_date" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Origem (igreja anterior)</label>
                    <input type="text" class="form-control" name="church_origin">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Forma de ingresso <span class="required-mark">*</span></label>
                    <select class="form-select" name="admission_method" id="admissionMethod" required>
                        <option value="">Selecione...</option>
                        <option value="Aclamação">Aclamação — recebido pela igreja sem carta de transferência</option>
                        <option value="Transferido">Transferido — recebido com carta de transferência de outra igreja</option>
                        <option value="Batismo">Batismo — novo convertido recebido após batismo</option>
                        <option value="Congregado">Congregado — passou a congregar sem transferência formal</option>
                    </select>
                    <div id="admissionHelp" class="form-text">Selecione a forma de ingresso do membro.</div>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check me-4">
                        <input class="form-check-input" type="checkbox" id="isBaptized" name="is_baptized" value="1">
                        <label class="form-check-label fw-semibold" for="isBaptized">Batizado nas águas?</label>
                    </div>
                </div>

                <div class="col-12" id="transferLetterBox" style="display:none">
                    <div class="border rounded-3 p-3">
                        <h6 class="mb-2">Carta de Transferência</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Importar arquivo</label>
                                <input type="file" class="form-control" name="transfer_letter" accept="image/*,application/pdf">
                                <small class="text-muted">Aceita imagem ou PDF.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Capturar com webcam</label>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnTransferWebcam"><i class="fas fa-camera"></i> Capturar Imagem</button>
                                </div>
                                <div id="transferWebcamContainer" style="display:none;" class="border p-2 rounded mt-2">
                                    <div id="transferVideoWrap" style="position:relative; display:inline-block; overflow:hidden;">
                                        <video id="transferWebcamVideo" width="640" height="480" autoplay style="position:absolute; left:0; top:0;"></video>
                                        <div id="transferGuide" style="position:absolute; border:2px dashed #ffc107;"></div>
                                    </div>
                                    <canvas id="transferWebcamCanvas" width="600" height="848" style="display:none;"></canvas>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-success btn-sm" id="btnTransferCapture">Capturar</button>
                                        <button type="button" class="btn btn-secondary btn-sm" id="btnTransferCancel">Cancelar</button>
                                    </div>
                                </div>
                                <input type="hidden" name="transfer_letter_webcam" id="transferLetterWebcamData">
                                <img id="transferPreviewImage" class="img-thumbnail mt-2" style="width: 150px; height: 212px; object-fit: cover; display:none;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label d-block">Situação <span class="required-mark">*</span></label>
                    <div class="status-options">
                        <label class="status-option active" data-status-option>
                            <input type="radio" name="status" value="active" checked>
                            <div>
                                <div class="status-title">Ativo</div>
                                <div class="status-desc">Participa das atividades regularmente.</div>
                            </div>
                        </label>
                        <label class="status-option" data-status-option>
                            <input type="radio" name="status" value="inactive">
                            <div>
                                <div class="status-title">Inativo</div>
                                <div class="status-desc">Afastado ou transferido.</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="col-md-4" id="exitDateDiv" style="display:none;">
                    <label class="form-label">Data de saída (opcional)</label>
                    <input type="date" class="form-control" name="exit_date">
                </div>

                <div class="col-12 mt-2">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_tither" value="1" id="isTither">
                                <label class="form-check-label fw-semibold" for="isTither">Dizimista?</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_ebd_teacher" value="1" id="isEbdTeacher">
                                <label class="form-check-label fw-semibold text-primary" for="isEbdTeacher">Professor de EBD?</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_new_convert" value="1" id="isNewConvert">
                                <label class="form-check-label fw-semibold" for="isNewConvert">Novo convertido?</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Data de aceitação (Jesus)</label>
                    <input type="date" class="form-control" name="accepted_jesus_at">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Data de reconciliação</label>
                    <input type="date" class="form-control" name="reconciled_at">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/members" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar Membro</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Nome</div>
                <div class="summary-value text-muted-value" id="summaryName">—</div>

                <div class="summary-label">Congregação</div>
                <div class="summary-value text-muted-value" id="summaryCongregation">—</div>

                <div class="summary-label">Cargo</div>
                <div class="summary-value" id="summaryRole">Membro</div>

                <div class="summary-label">Situação</div>
                <div class="summary-value mb-2" id="summaryStatus">Ativo</div>

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
                Campos marcados com <span class="required-mark">*</span> são obrigatórios. Os dados pessoais são de uso interno da secretaria.
            </div>
        </div>
    </div>
</div>
</div>

<script>
document.getElementById('memberCreateForm').addEventListener('submit', function(e) {
    let isValid = true;
    const requiredFields = this.querySelectorAll('[required]');
    requiredFields.forEach(field => field.classList.remove('is-invalid'));

    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            if (isValid) {
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                field.focus();
                isValid = false;
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Obrigatórios',
                    text: 'Por favor, preencha todos os campos marcados com *.',
                    confirmButtonColor: '#3085d6'
                });
            }
        }
    }

    if (!isValid) {
        e.preventDefault();
    }
});

// Função para carregar cargos
function loadRoles(selectedRole = null) {
    const select = document.getElementById('roleSelect');

    fetch('/api/roles/list')
        .then(response => response.json())
        .then(roles => {
            select.innerHTML = '<option value="">Selecione...</option>';
            roles.forEach(role => {
                const option = new Option(role.name, role.name);
                if (selectedRole && role.name === selectedRole) {
                    option.selected = true;
                }
                select.add(option);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar cargos:', error);
            select.innerHTML = '<option value="">Erro ao carregar</option>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    loadRoles();
});

// Adicionar Novo Cargo
document.getElementById('btnAddRole').addEventListener('click', function() {
    Swal.fire({
        title: 'Adicionar Novo Cargo',
        input: 'text',
        inputLabel: 'Nome do Cargo',
        inputPlaceholder: 'Ex: Músico, Porteiro...',
        showCancelButton: true,
        confirmButtonText: 'Salvar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Você precisa escrever algo!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const newRole = result.value;
            fetch('/api/roles/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: newRole })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Sucesso', data.message, 'success');
                    loadRoles(newRole);
                } else {
                    Swal.fire('Erro', data.error || 'Erro desconhecido', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Erro', 'Erro de conexão com o servidor', 'error');
            });
        }
    });
});

// Editar Cargo Selecionado
document.getElementById('btnEditRole').addEventListener('click', function() {
    const select = document.getElementById('roleSelect');
    const selectedOption = select.options[select.selectedIndex];

    if (!selectedOption.value) {
        Swal.fire('Atenção', 'Selecione um cargo para editar.', 'warning');
        return;
    }

    const oldName = selectedOption.value;

    Swal.fire({
        title: 'Editar Cargo',
        input: 'text',
        inputLabel: 'Nome do Cargo',
        inputValue: oldName,
        showCancelButton: true,
        confirmButtonText: 'Salvar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'O nome do cargo não pode ficar vazio!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const newName = result.value;
            fetch('/api/roles/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ old_name: oldName, new_name: newName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Sucesso', data.message, 'success');
                    loadRoles(newName);
                } else {
                    Swal.fire('Erro', data.error || 'Erro desconhecido', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Erro', 'Erro de conexão com o servidor', 'error');
            });
        }
    });
});

// Excluir Cargo Selecionado
document.getElementById('btnDeleteRole').addEventListener('click', function() {
    const select = document.getElementById('roleSelect');
    const selectedOption = select.options[select.selectedIndex];

    if (!selectedOption.value) {
        Swal.fire('Atenção', 'Selecione um cargo para excluir.', 'warning');
        return;
    }

    const roleName = selectedOption.value;

    Swal.fire({
        title: 'Excluir Cargo?',
        text: `Deseja remover "${roleName}" da lista? Isso afetará apenas a lista de seleção.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/api/roles/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: roleName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Removido!', data.message, 'success');
                    loadRoles();
                } else {
                    Swal.fire('Erro', data.error || 'Erro desconhecido', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Erro', 'Erro de conexão com o servidor', 'error');
            });
        }
    });
});
</script>

<script>
    // Controle de exibição da data de batismo
    document.getElementById('isBaptized').addEventListener('change', function() {
        document.getElementById('baptismDateInput').disabled = !this.checked;
        if (!this.checked) document.getElementById('baptismDateInput').value = '';
    });

    // Seleção visual do status (Ativo/Inativo) + campo de data de saída
    const statusOptions = document.querySelectorAll('[data-status-option]');
    const exitDateDiv = document.getElementById('exitDateDiv');
    function refreshStatusUI() {
        statusOptions.forEach(opt => {
            const input = opt.querySelector('input[type="radio"]');
            opt.classList.toggle('active', input.checked);
        });
        const checked = document.querySelector('input[name="status"]:checked');
        exitDateDiv.style.display = (checked && checked.value === 'inactive') ? '' : 'none';
    }
    statusOptions.forEach(opt => {
        opt.addEventListener('click', function () {
            const input = this.querySelector('input[type="radio"]');
            input.checked = true;
            refreshStatusUI();
            updateSummary();
        });
    });
    refreshStatusUI();

    // Painel de resumo lateral (nome, congregação, cargo, situação, % preenchido)
    const form = document.getElementById('memberCreateForm');
    const nameInput = document.querySelector('[name="name"]');
    const congregationSelect = document.querySelector('[name="congregation_id"]');
    const roleSelect = document.getElementById('roleSelect');

    const summaryName = document.getElementById('summaryName');
    const summaryCongregation = document.getElementById('summaryCongregation');
    const summaryRole = document.getElementById('summaryRole');
    const summaryStatus = document.getElementById('summaryStatus');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');

    function updateSummary() {
        summaryName.textContent = nameInput.value.trim() || '—';
        summaryName.classList.toggle('text-muted-value', !nameInput.value.trim());

        const congOption = congregationSelect.options[congregationSelect.selectedIndex];
        summaryCongregation.textContent = (congOption && congOption.value) ? congOption.text : '—';
        summaryCongregation.classList.toggle('text-muted-value', !(congOption && congOption.value));

        summaryRole.textContent = roleSelect.value || 'Membro';

        const checkedStatus = document.querySelector('input[name="status"]:checked');
        summaryStatus.textContent = (checkedStatus && checkedStatus.value === 'inactive') ? 'Inativo' : 'Ativo';

        const requiredFields = Array.from(form.querySelectorAll('[required]'));
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    form.addEventListener('input', updateSummary);
    form.addEventListener('change', updateSummary);
    document.addEventListener('DOMContentLoaded', updateSummary);
    updateSummary();

    // Botão "Limpar"
    const btnClearForm = document.getElementById('btnClearForm');
    if (btnClearForm) {
        btnClearForm.addEventListener('click', function () {
            Swal.fire({
                title: 'Limpar formulário?',
                text: 'Todos os campos preenchidos serão apagados.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, limpar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.reset();
                    photoPreview.style.display = 'none';
                    photoPlaceholder.style.display = '';
                    webcamPhotoData.value = '';
                    refreshStatusUI();
                    updateAdmission();
                    document.getElementById('baptismDateInput').disabled = true;
                    updateSummary();
                }
            });
        });
    }

    // Busca de CEP automática (ViaCEP)
    document.getElementById('zip_code').addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.querySelector('[name="address"]').value = data.logradouro;
                        document.querySelector('[name="neighborhood"]').value = data.bairro;
                        document.querySelector('[name="city"]').value = data.localidade;
                        document.querySelector('[name="state"]').value = data.uf;
                        document.querySelector('[name="address_number"]').focus();
                    }
                })
                .catch(error => console.error('Erro ao buscar CEP:', error));
        }
    });

    // Webcam functionality
    const btnWebcam = document.getElementById('btnWebcam');
    const webcamContainer = document.getElementById('webcamContainer');
    const video = document.getElementById('webcamVideo');
    const canvas = document.getElementById('webcamCanvas');
    const btnCapture = document.getElementById('btnCapture');
    const btnCancelWebcam = document.getElementById('btnCancelWebcam');
    const photoPreview = document.getElementById('photoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const webcamPhotoData = document.getElementById('webcamPhotoData');
    const photoInput = document.getElementById('photoInput');
    let stream = null;

    function showPhotoPreview(src) {
        photoPreview.src = src;
        photoPreview.style.display = '';
        photoPlaceholder.style.display = 'none';
    }

    btnWebcam.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            webcamContainer.style.display = 'block';
        } catch (err) {
            console.error("Error accessing webcam: ", err);
            Swal.fire({
                icon: 'error',
                title: 'Erro na Webcam',
                text: 'Não foi possível acessar a webcam. Verifique as permissões.',
                confirmButtonColor: '#d33'
            });
        }
    });

    btnCapture.addEventListener('click', () => {
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, 320, 240);
        const dataURL = canvas.toDataURL('image/jpeg');
        showPhotoPreview(dataURL);
        webcamPhotoData.value = dataURL;
        photoInput.value = '';

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        webcamContainer.style.display = 'none';
    });

    btnCancelWebcam.addEventListener('click', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        webcamContainer.style.display = 'none';
    });

    photoInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                showPhotoPreview(e.target.result);
            }
            reader.readAsDataURL(e.target.files[0]);
            webcamPhotoData.value = '';
        }
    });

    const methodSelect = document.getElementById('admissionMethod');
    const helpBox = document.getElementById('admissionHelp');
    const transferBox = document.getElementById('transferLetterBox');
    function updateAdmission() {
        const map = {
            'Aclamação': 'Recebido pela igreja local, sem carta de transferência.',
            'Transferido': 'Recebido com carta de transferência de outra igreja.',
            'Batismo': 'Novo convertido, recebido após o batismo nas águas.',
            'Congregado': 'Passou a congregar, sem transferência formal.'
        };
        if (methodSelect.value) {
            helpBox.textContent = map[methodSelect.value] || 'Selecione a forma de ingresso do membro.';
        } else {
            helpBox.textContent = 'Selecione a forma de ingresso do membro.';
        }
        transferBox.style.display = (methodSelect.value === 'Transferido') ? '' : 'none';
    }
    methodSelect.addEventListener('change', updateAdmission);
    updateAdmission();

    let transferStream = null;
    const btnTransferWebcam = document.getElementById('btnTransferWebcam');
    const transferWebcamContainer = document.getElementById('transferWebcamContainer');
    const transferVideo = document.getElementById('transferWebcamVideo');
    const transferCanvas = document.getElementById('transferWebcamCanvas');
    const btnTransferCapture = document.getElementById('btnTransferCapture');
    const btnTransferCancel = document.getElementById('btnTransferCancel');
    const transferData = document.getElementById('transferLetterWebcamData');
    const transferPreview = document.getElementById('transferPreviewImage');
    const transferFile = document.querySelector('input[name="transfer_letter"]');
    if (transferFile) {
        transferFile.addEventListener('change', function () {
            if (transferFile.files && transferFile.files.length > 0) {
                transferData.value = '';
                transferPreview.src = '';
                transferPreview.style.display = 'none';
            }
        });
    }
    function updateA4Guide() {
        const displayW = transferVideo.clientWidth;
        const displayH = transferVideo.clientHeight;
        const targetRatio = transferCanvas.width / transferCanvas.height;
        let guideH = Math.floor(displayH * 0.9);
        let guideW = Math.floor(guideH * targetRatio);
        if (guideW > displayW * 0.95) {
            guideW = Math.floor(displayW * 0.95);
            guideH = Math.floor(guideW / targetRatio);
        }
        const left = Math.floor((displayW - guideW) / 2);
        const top = Math.floor((displayH - guideH) / 2);
        transferGuide.style.left = '0px';
        transferGuide.style.top = '0px';
        transferGuide.style.width = guideW + 'px';
        transferGuide.style.height = guideH + 'px';
        transferVideoWrap.style.width = guideW + 'px';
        transferVideoWrap.style.height = guideH + 'px';
        transferVideo.style.left = -left + 'px';
        transferVideo.style.top = -top + 'px';
    }
    btnTransferWebcam.addEventListener('click', async () => {
        try {
            transferStream = await navigator.mediaDevices.getUserMedia({ video: true });
            transferVideo.srcObject = transferStream;
            transferWebcamContainer.style.display = 'block';
            btnTransferWebcam.style.display = 'none';
            updateA4Guide();
            window.addEventListener('resize', updateA4Guide);
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Erro na Webcam', text: 'Não foi possível acessar a webcam.' });
        }
    });
    btnTransferCapture.addEventListener('click', () => {
        const ctx = transferCanvas.getContext('2d');
        const targetW = transferCanvas.width;
        const targetH = transferCanvas.height;
        const vw = transferVideo.videoWidth || 640;
        const vh = transferVideo.videoHeight || 480;
        const displayW = transferVideo.clientWidth;
        const displayH = transferVideo.clientHeight;
        const scaleX = vw / displayW;
        const scaleY = vh / displayH;
        const guideRect = transferGuide.getBoundingClientRect();
        const videoRect = transferVideo.getBoundingClientRect();
        const gx = guideRect.left - videoRect.left;
        const gy = guideRect.top - videoRect.top;
        const gw = guideRect.width;
        const gh = guideRect.height;
        const sx = Math.floor(gx * scaleX);
        const sy = Math.floor(gy * scaleY);
        const sw = Math.floor(gw * scaleX);
        const sh = Math.floor(gh * scaleY);
        ctx.drawImage(transferVideo, sx, sy, sw, sh, 0, 0, targetW, targetH);
        const dataURL = transferCanvas.toDataURL('image/jpeg');
        transferData.value = dataURL;
        transferPreview.src = dataURL;
        transferPreview.style.display = '';
        if (transferFile) transferFile.value = '';
        if (transferStream) transferStream.getTracks().forEach(t => t.stop());
        transferWebcamContainer.style.display = 'none';
        btnTransferWebcam.style.display = 'block';
    });
    btnTransferCancel.addEventListener('click', () => {
        if (transferStream) transferStream.getTracks().forEach(t => t.stop());
        transferWebcamContainer.style.display = 'none';
        btnTransferWebcam.style.display = 'block';
    });
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

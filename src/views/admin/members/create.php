<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Membro</h1>
</div>

<style>
    @media (max-width: 767.98px) {
        .member-form-sticky-actions {
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .member-form-step-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .25rem .6rem;
            border-radius: 999px;
            font-weight: 900;
            letter-spacing: .08em;
            font-size: .72rem;
            background: rgba(179,0,0,0.10);
            color: #b30000;
            border: 1px solid rgba(179,0,0,0.18);
            white-space: nowrap;
        }
        .member-form-hint {
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .02em;
            color: rgba(0,0,0,0.55);
            white-space: nowrap;
        }
        .member-form-hint i { color: #b30000; }
        .member-form-progress {
            height: 6px;
            background: rgba(0,0,0,0.08);
            border-radius: 999px;
            overflow: hidden;
        }
        .member-form-progress > div {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #ff2a7a 0%, #b30000 52%, #d4af37 100%);
        }
        .member-form-carousel-shell {
            position: relative;
        }
        .member-form-carousel-shell::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #ff2a7a 0%, #b30000 52%, #d4af37 100%);
            z-index: 2;
        }
        .member-form-carousel {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
        }
        .member-form-carousel::-webkit-scrollbar { display: none; }
        .member-form-slide {
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            padding: .35rem;
        }
        .member-form-slide .row {
            margin-left: 0;
            margin-right: 0;
        }
        .member-form-slide .row > [class*="col-"] {
            padding-left: calc(var(--bs-gutter-x) * .5);
            padding-right: calc(var(--bs-gutter-x) * .5);
        }
        .member-form-slide-title {
            font-weight: 900;
            font-size: 1.1rem;
            letter-spacing: .01em;
            color: #2d1a21;
        }
        .member-form-slide-head {
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.08);
            background: linear-gradient(135deg, rgba(179,0,0,0.10), rgba(212,175,55,0.16));
            padding: .75rem .85rem;
        }
        .member-form-slide-step {
            font-weight: 900;
            letter-spacing: .08em;
            font-size: .72rem;
            color: rgba(0,0,0,0.58);
            white-space: nowrap;
        }
    }
</style>

<form action="/admin/members/create" method="POST" enctype="multipart/form-data" class="member-create-form app-form-with-bottom-actions" id="memberCreateForm">
    <?= csrf_field() ?>
    <div class="member-form-sticky-actions d-md-none bg-white border rounded-3 shadow-sm mb-2 px-2 py-2">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="member-form-step-chip">ETAPA <span id="memberFormStepIndicator">1/3</span></span>
                <span class="member-form-hint"><i class="fas fa-arrows-left-right me-1"></i>Deslize</span>
            </div>
        </div>
        <div class="member-form-progress mt-2">
            <div id="memberFormProgressBar"></div>
        </div>
    </div>

    <div class="member-form-carousel-shell">
    <div class="member-form-carousel" id="memberFormCarousel">
        <div class="member-form-slide">
            <div class="row g-3">
                <div class="col-12">
                    <div class="member-form-slide-head mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="member-form-slide-title text-primary" data-step-title>Dados Pessoais</div>
                            <div class="member-form-slide-step">1/3</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Foto do Membro</label>
                    <div class="row g-2">
                        <div class="col-12 text-center">
                            <img id="photoPreview" src="https://via.placeholder.com/150" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <div class="col-12">
                            <input type="file" class="form-control form-control-sm" name="photo" id="photoInput" accept="image/*">
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnWebcam">
                                <i class="fas fa-camera"></i> Tirar Foto com Webcam
                            </button>
                        </div>
                        <div class="col-12">
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
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data de Nascimento *</label>
                    <input type="date" class="form-control" name="birth_date" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sexo</label>
                    <select class="form-select" name="gender">
                        <option value="">Selecione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Feminino</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">CPF *</label>
                    <input type="text" class="form-control" name="cpf" placeholder="000.000.000-00" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Identidade (RG)</label>
                    <input type="text" class="form-control" name="rg">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado Civil</label>
                    <select class="form-select" name="marital_status">
                        <option value="">Selecione...</option>
                        <option value="Solteiro(a)">Solteiro(a)</option>
                        <option value="Casado(a)">Casado(a)</option>
                        <option value="Divorciado(a)">Divorciado(a)</option>
                        <option value="Viúvo(a)">Viúvo(a)</option>
                        <option value="Separado(a)">Separado(a)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nacionalidade</label>
                    <input type="text" class="form-control" name="nationality" value="Brasileira">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Natural de (Cidade/Estado) *</label>
                    <input type="text" class="form-control" name="birthplace" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Profissão</label>
                    <input type="text" class="form-control" name="profession">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantidade de Filhos</label>
                    <input type="number" class="form-control" name="children_count" min="0" value="0">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nome do Pai</label>
                    <input type="text" class="form-control" name="father_name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nome da Mãe *</label>
                    <input type="text" class="form-control" name="mother_name" required>
                </div>
            </div>
        </div>

        <div class="member-form-slide">
            <div class="row g-3">
                <div class="col-12">
                    <div class="member-form-slide-head mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="member-form-slide-title text-primary" data-step-title>Contato e Endereço</div>
                            <div class="member-form-slide-step">2/3</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Telefone (WhatsApp) *</label>
                    <input type="text" class="form-control" name="phone" placeholder="(00) 00000-0000" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email">
                </div>
                <div class="col-md-4">
                    <label class="form-label">CEP</label>
                    <input type="text" class="form-control" name="zip_code" id="zip_code" placeholder="00000-000">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Endereço (Rua) *</label>
                    <input type="text" class="form-control" name="address" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Número *</label>
                    <input type="text" class="form-control" name="address_number" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bairro *</label>
                    <input type="text" class="form-control" name="neighborhood" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Complemento</label>
                    <input type="text" class="form-control" name="complement">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ponto de Referência</label>
                    <input type="text" class="form-control" name="reference_point">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado *</label>
                    <select class="form-select" name="state" id="state" required>
                        <option value="">UF</option>
                        <option value="AC">AC</option><option value="AL">AL</option><option value="AP">AP</option>
                        <option value="AM">AM</option><option value="BA">BA</option><option value="CE">CE</option>
                        <option value="DF">DF</option><option value="ES">ES</option><option value="GO">GO</option>
                        <option value="MA">MA</option><option value="MT">MT</option><option value="MS">MS</option>
                        <option value="MG">MG</option><option value="PA">PA</option><option value="PB">PB</option>
                        <option value="PR">PR</option><option value="PE">PE</option><option value="PI">PI</option>
                        <option value="RJ">RJ</option><option value="RN">RN</option><option value="RS">RS</option>
                        <option value="RO">RO</option><option value="RR">RR</option><option value="IVN">IVN</option>
                        <option value="SP">SP</option><option value="SE">SE</option><option value="TO">TO</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cidade *</label>
                    <input type="text" class="form-control" name="city" id="city" required>
                </div>
            </div>
        </div>

        <div class="member-form-slide">
            <div class="row g-3">
                <div class="col-12">
                    <div class="member-form-slide-head mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="member-form-slide-title text-primary" data-step-title>Dados Eclesiásticos</div>
                            <div class="member-form-slide-step">3/3</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Congregação *</label>
                    <select class="form-select" name="congregation_id" required>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cargo *</label>
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
                    <label class="form-label">Situação do Membro *</label>
                    <select class="form-select" name="status" id="memberStatus" required>
                        <option value="active">Ativo (Congregando)</option>
                        <option value="inactive">Inativo (Desligado/Saiu)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Forma de Ingresso *</label>
                    <select class="form-select" name="admission_method" id="admissionMethod" required>
                        <option value="">Selecione...</option>
                        <option value="Aclamação">Aclamação — recebido pela igreja sem carta de transferência</option>
                        <option value="Transferido">Transferido — recebido com carta de transferência de outra igreja</option>
                        <option value="Batismo">Batismo — novo convertido recebido após batismo</option>
                        <option value="Congregado">Congregado — passou a congregar sem transferência formal</option>
                    </select>
                    <div id="admissionHelp" class="form-text">Selecione a forma de ingresso do membro.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data de Aceite/Entrada</label>
                    <input type="date" class="form-control" name="admission_date" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Origem (Igreja Anterior)</label>
                    <input type="text" class="form-control" name="church_origin">
                </div>
                
                <div class="col-12" id="transferLetterBox" style="display:none">
                    <div class="card mt-2">
                        <div class="card-body">
                            <h6 class="mb-2">Carta de Transferência</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Importar Arquivo</label>
                                    <input type="file" class="form-control" name="transfer_letter" accept="image/*,application/pdf">
                                    <small class="text-muted">Aceita imagem ou PDF.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Capturar com Webcam</label>
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
                </div>

                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="isBaptized" name="is_baptized" value="1">
                        <label class="form-check-label fw-bold" for="isBaptized">Batizado nas Águas?</label>
                    </div>
                </div>
                <div class="col-md-3" id="baptismDateDiv" style="display:none;">
                    <label class="form-label">Data de Batismo</label>
                    <input type="date" class="form-control" name="baptism_date">
                </div>
                
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_tither" value="1">
                        <label class="form-check-label fw-bold">Dizimista?</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_ebd_teacher" value="1">
                        <label class="form-check-label text-primary fw-bold">Professor de EBD?</label>
                    </div>
                </div>
                
                <div class="col-md-12 mt-3">
                    <h5 class="text-secondary border-bottom pb-2">Status Espiritual</h5>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_new_convert" value="1" id="isNewConvert">
                        <label class="form-check-label fw-bold" for="isNewConvert">Novo Convertido?</label>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Data de Aceitação (Jesus)</label>
                    <input type="date" class="form-control" name="accepted_jesus_at">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Data de Reconciliação</label>
                    <input type="date" class="form-control" name="reconciled_at">
                </div>

                <div class="col-md-3" id="exitDateDiv">
                    <label class="form-label">Data de Saída (Opcional)</label>
                    <input type="date" class="form-control" name="exit_date">
                </div>

                <div class="col-12 text-end d-none d-md-block">
                    <button type="submit" class="btn btn-primary px-4">Salvar Membro</button>
                    <a href="/admin/members" class="btn btn-outline-secondary px-4">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="member-form-bottom-actions app-form-bottom-actions d-lg-none">
        <div class="row g-2">
            <div class="col-6">
                <button type="submit" class="btn btn-primary w-100">Salvar</button>
            </div>
            <div class="col-6">
                <a href="/admin/members" class="btn btn-outline-secondary w-100">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('memberCreateForm').addEventListener('submit', function(e) {
    let isValid = true;
    const requiredFields = this.querySelectorAll('[required]');
    
    // Remove estilos anteriores
    requiredFields.forEach(field => field.classList.remove('is-invalid'));
    
    // Valida campos
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            if (isValid) {
                // Foca apenas no primeiro campo inválido
                const carousel = document.getElementById('memberFormCarousel');
                const slide = field.closest('.member-form-slide');
                if (carousel && slide) {
                    const slides = Array.from(carousel.querySelectorAll('.member-form-slide'));
                    const idx = slides.indexOf(slide);
                    if (idx >= 0) {
                        carousel.scrollTo({ left: carousel.clientWidth * idx, behavior: 'smooth' });
                    }
                }
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

// Carregar cargos ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    loadRoles();

    const carousel = document.getElementById('memberFormCarousel');
    const stepEl = document.getElementById('memberFormStepIndicator');
    const progressEl = document.getElementById('memberFormProgressBar');
    const slides = carousel ? Array.from(carousel.querySelectorAll('.member-form-slide')) : [];

    function setStep(activeIndex) {
        const total = slides.length || 1;
        const index = Math.min(Math.max(activeIndex, 0), total - 1);
        if (stepEl) stepEl.textContent = (index + 1) + '/' + total;
        if (progressEl) progressEl.style.width = (((index + 1) / total) * 100) + '%';
    }

    function onScroll() {
        if (!carousel) return;
        const w = carousel.clientWidth || 1;
        const idx = Math.round(carousel.scrollLeft / w);
        setStep(idx);
    }

    if (carousel && slides.length > 0) {
        setStep(0);
        carousel.addEventListener('scroll', onScroll, { passive: true });
    }
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
            
            // Enviar para API
            fetch('/api/roles/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: newRole })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Sucesso', data.message, 'success');
                    loadRoles(newRole); // Recarrega e seleciona o novo
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
            
            // Enviar para API
            fetch('/api/roles/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ old_name: oldName, new_name: newName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Sucesso', data.message, 'success');
                    loadRoles(newName); // Recarrega e seleciona o editado
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
            // Enviar para API
            fetch('/api/roles/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: roleName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Removido!', data.message, 'success');
                    loadRoles(); // Recarrega a lista
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
        document.getElementById('baptismDateDiv').style.display = this.checked ? 'block' : 'none';
    });

    // Controle de exibição da data de saída
    const memberStatus = document.getElementById('memberStatus');
    const exitDateDiv = document.getElementById('exitDateDiv');

    function toggleExitDate() {
        if (memberStatus.value === 'active' || memberStatus.value === '') {
            exitDateDiv.style.display = 'none';
        } else {
            exitDateDiv.style.display = 'block';
        }
    }

    memberStatus.addEventListener('change', toggleExitDate);
    // Executa ao carregar para garantir estado inicial correto
    toggleExitDate();

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
    const webcamPhotoData = document.getElementById('webcamPhotoData');
    const photoInput = document.getElementById('photoInput');
    let stream = null;

    btnWebcam.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            webcamContainer.style.display = 'block';
            btnWebcam.style.display = 'none';
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
        // Set preview image src to dataURL
        const dataURL = canvas.toDataURL('image/jpeg');
        photoPreview.src = dataURL;
        webcamPhotoData.value = dataURL; // Store base64 data in hidden input
        
        // Clear file input if webcam is used
        photoInput.value = '';
        
        // Stop webcam stream
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        webcamContainer.style.display = 'none';
        btnWebcam.style.display = 'block';
    });

    btnCancelWebcam.addEventListener('click', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        webcamContainer.style.display = 'none';
        btnWebcam.style.display = 'block';
    });

    // Preview uploaded file
    photoInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
            
            // Clear webcam data if file is selected
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

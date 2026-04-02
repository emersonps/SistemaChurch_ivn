<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar</h1>
</div>

<form action="/admin/members/edit/<?= $member['id'] ?>" method="POST" enctype="multipart/form-data" class="row g-3">
    <?= csrf_field() ?>
    <!-- Dados Pessoais -->
    <h4 class="mb-3 text-primary border-bottom pb-2">Dados Pessoais</h4>

    <div class="col-md-12 mb-3">
        <label class="form-label">Foto do Membro</label>
        <div class="d-flex gap-3 align-items-start">
            <div class="text-center">
                <?php $photoPath = !empty($member['photo']) ? '/uploads/members/' . $member['photo'] : 'https://via.placeholder.com/150'; ?>
                <img id="photoPreview" src="<?= $photoPath ?>" class="img-thumbnail mb-2" style="width: 150px; height: 150px; object-fit: cover;">
                <input type="file" class="form-control form-control-sm" name="photo" id="photoInput" accept="image/*">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_photo" id="removePhoto">
                    <label class="form-check-label" for="removePhoto">Remover foto</label>
                </div>
            </div>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnWebcam">
                    <i class="fas fa-camera"></i> Alterar Foto com Webcam
                </button>
                <div id="webcamContainer" style="display:none;" class="border p-2 rounded">
                    <video id="webcamVideo" width="320" height="240" autoplay></video>
                    <canvas id="webcamCanvas" width="320" height="240" style="display:none;"></canvas>
                    <div class="mt-2">
                        <button type="button" class="btn btn-success btn-sm" id="btnCapture">Capturar</button>
                        <button type="button" class="btn btn-secondary btn-sm" id="btnCancelWebcam">Cancelar</button>
                    </div>
                </div>
                <input type="hidden" name="webcam_photo" id="webcamPhotoData">
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Nome Completo *</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($member['name']) ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Data de Nascimento *</label>
        <input type="date" class="form-control" name="birth_date" value="<?= !empty($member['birth_date']) ? date('Y-m-d', strtotime($member['birth_date'])) : '' ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Sexo</label>
        <select class="form-select" name="gender">
            <option value="">Selecione...</option>
            <option value="M" <?= ($member['gender'] ?? '') == 'M' ? 'selected' : '' ?>>Masculino</option>
            <option value="F" <?= ($member['gender'] ?? '') == 'F' ? 'selected' : '' ?>>Feminino</option>
        </select>
    </div>
    
    <div class="col-md-3">
        <label class="form-label">CPF *</label>
        <input type="text" class="form-control" name="cpf" value="<?= htmlspecialchars($member['cpf'] ?? '') ?>" placeholder="000.000.000-00" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Identidade (RG)</label>
        <input type="text" class="form-control" name="rg" value="<?= htmlspecialchars($member['rg'] ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Estado Civil</label>
        <select class="form-select" name="marital_status">
            <option value="">Selecione...</option>
            <?php 
                $status_civil = ['Solteiro(a)', 'Casado(a)', 'Divorciado(a)', 'Viúvo(a)', 'Separado(a)'];
                foreach ($status_civil as $IVN) {
                    $selected = ($member['marital_status'] ?? '') == $IVN ? 'selected' : '';
                    echo "<option value='$IVN' $selected>$IVN</option>";
                }
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Nacionalidade</label>
        <input type="text" class="form-control" name="nationality" value="<?= htmlspecialchars($member['nationality'] ?? 'Brasileira') ?>">
    </div>
    
    <div class="col-md-4">
        <label class="form-label">Natural de (Cidade/Estado) *</label>
        <input type="text" class="form-control" name="birthplace" value="<?= htmlspecialchars($member['birthplace'] ?? '') ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Profissão</label>
        <input type="text" class="form-control" name="profession" value="<?= htmlspecialchars($member['profession'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Quantidade de Filhos</label>
        <input type="number" class="form-control" name="children_count" min="0" value="<?= $member['children_count'] ?? 0 ?>">
    </div>

    <div class="col-md-6">
        <label class="form-label">Nome do Pai</label>
        <input type="text" class="form-control" name="father_name" value="<?= htmlspecialchars($member['father_name'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Nome da Mãe *</label>
        <input type="text" class="form-control" name="mother_name" value="<?= htmlspecialchars($member['mother_name'] ?? '') ?>" required>
    </div>

    <!-- Contato e Endereço -->
    <h4 class="mb-3 mt-4 text-primary border-bottom pb-2">Contato e Endereço</h4>

    <div class="col-md-4">
        <label class="form-label">Telefone (WhatsApp) *</label>
        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" placeholder="(00) 00000-0000" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">CEP</label>
        <input type="text" class="form-control" name="zip_code" id="zip_code" value="<?= htmlspecialchars($member['zip_code'] ?? '') ?>" placeholder="00000-000">
    </div>

    <div class="col-md-6">
        <label class="form-label">Endereço (Rua) *</label>
        <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($member['address'] ?? '') ?>" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Número *</label>
        <input type="text" class="form-control" name="address_number" value="<?= htmlspecialchars($member['address_number'] ?? '') ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Bairro *</label>
        <input type="text" class="form-control" name="neighborhood" value="<?= htmlspecialchars($member['neighborhood'] ?? '') ?>" required>
    </div>
    
    <div class="col-md-4">
        <label class="form-label">Complemento</label>
        <input type="text" class="form-control" name="complement" value="<?= htmlspecialchars($member['complement'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Ponto de Referência</label>
        <input type="text" class="form-control" name="reference_point" value="<?= htmlspecialchars($member['reference_point'] ?? '') ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Estado *</label>
        <select class="form-select" name="state" id="state" required>
            <option value="">UF</option>
            <?php 
                $states = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','IVN','SP','SE','TO'];
                foreach ($states as $st) {
                    $selected = ($member['state'] ?? '') == $st ? 'selected' : '';
                    echo "<option value='$st' $selected>$st</option>";
                }
            ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Cidade *</label>
        <input type="text" class="form-control" name="city" id="city" value="<?= htmlspecialchars($member['city'] ?? '') ?>" required>
    </div>

    <!-- Dados Eclesiásticos -->
    <h4 class="mb-3 mt-4 text-primary border-bottom pb-2">Dados Eclesiásticos</h4>

    <div class="col-md-4">
        <label class="form-label">Congregação *</label>
        <select class="form-select" name="congregation_id" required>
            <?php foreach ($congregations as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $member['congregation_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Cargo *</label>
        <div class="input-group">
            <select class="form-select" name="role" id="roleSelect" data-selected="<?= htmlspecialchars($member['role'] ?? '') ?>" required>
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
            <?php 
                // Convert old statuses to active/inactive internally or just keep the visual labels
                $currentStatus = $member['status'] ?? 'active';
                $isActive = ($currentStatus === 'active' || strtolower(trim($currentStatus)) === 'congregando');
            ?>
            <option value="active" <?= $isActive ? 'selected' : '' ?>>Ativo (Congregando)</option>
            <option value="inactive" <?= !$isActive ? 'selected' : '' ?>>Inativo (Desligado/Saiu)</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Forma de Ingresso *</label>
        <select class="form-select" name="admission_method" id="admissionMethod" required>
            <?php 
                $options = [
                    'Aclamação' => 'Aclamação — recebido pela igreja sem carta de transferência',
                    'Transferido' => 'Transferido — recebido com carta de transferência de outra igreja',
                    'Batismo' => 'Batismo — novo convertido recebido após batismo',
                    'Congregado' => 'Congregado — passou a congregar sem transferência formal'
                ];
                foreach ($options as $val => $label) {
                    $selected = ($member['admission_method'] ?? '') == $val ? 'selected' : '';
                    echo "<option value='$val' $selected>$label</option>";
                }
            ?>
        </select>
        <div id="admissionHelp" class="form-text"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Data de Aceite/Entrada</label>
        <input type="date" class="form-control" name="admission_date" value="<?= !empty($member['admission_date']) ? date('Y-m-d', strtotime($member['admission_date'])) : '' ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Origem (Igreja Anterior)</label>
        <input type="text" class="form-control" name="church_origin" value="<?= htmlspecialchars($member['church_origin'] ?? '') ?>">
    </div>
    
    <div class="col-12" id="transferLetterBox" style="display:none">
        <div class="card mt-2">
            <div class="card-body">
                <h6 class="mb-2">Carta de Transferência</h6>
                <?php 
                    $hasTransferLetter = false; 
                    if (!empty($documents)) { 
                        foreach ($documents as $doc) { 
                            if (($doc['type'] ?? '') === 'transfer_letter') { $hasTransferLetter = true; break; } 
                        } 
                    }
                    if ($hasTransferLetter): ?>
                    <div class="alert alert-warning py-2">
                        Já existe uma carta anexada. Ao salvar, a nova substituirá a atual.
                    </div>
                <?php endif; ?>
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
            <input class="form-check-input" type="checkbox" id="isBaptized" name="is_baptized" value="1" <?= $member['is_baptized'] ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold" for="isBaptized">Batizado nas Águas?</label>
        </div>
    </div>
    <div class="col-md-3" id="baptismDateDiv" style="display:<?= $member['is_baptized'] ? 'block' : 'none' ?>;">
        <label class="form-label">Data de Batismo</label>
        <input type="date" class="form-control" name="baptism_date" value="<?= !empty($member['baptism_date']) ? date('Y-m-d', strtotime($member['baptism_date'])) : '' ?>">
    </div>
    
    <div class="col-md-3">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_tither" value="1" <?= ($member['is_tither'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold">Dizimista?</label>
        </div>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="is_ebd_teacher" value="1" <?= ($member['is_ebd_teacher'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label text-primary fw-bold">Professor de EBD?</label>
        </div>
    </div>
    
    <div class="col-md-12 mt-3">
        <h5 class="text-secondary border-bottom pb-2">Status Espiritual</h5>
    </div>
    
    <div class="col-md-3">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_new_convert" value="1" id="isNewConvert" <?= ($member['is_new_convert'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold" for="isNewConvert">Novo Convertido?</label>
        </div>
    </div>
    
    <div class="col-md-3">
        <label class="form-label">Data de Aceitação (Jesus)</label>
        <input type="date" class="form-control" name="accepted_jesus_at" value="<?= !empty($member['accepted_jesus_at']) ? date('Y-m-d', strtotime($member['accepted_jesus_at'])) : '' ?>">
    </div>
    
    <div class="col-md-3">
        <label class="form-label">Data de Reconciliação</label>
        <input type="date" class="form-control" name="reconciled_at" value="<?= !empty($member['reconciled_at']) ? date('Y-m-d', strtotime($member['reconciled_at'])) : '' ?>">
    </div>
    
    <div class="col-md-3" id="exitDateDiv">
        <label class="form-label">Data de Saída (Opcional)</label>
        <input type="date" class="form-control" name="exit_date" value="<?= !empty($member['exit_date']) ? date('Y-m-d', strtotime($member['exit_date'])) : '' ?>">
    </div>

    <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary px-4">Salvar Alterações</button>
        <a href="/admin/members" class="btn btn-outline-secondary px-4">Cancelar</a>
    </div>
</form>

<div class="mt-4">
    <h4 class="mb-3 text-primary border-bottom pb-2">Documentos do Membro</h4>
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($documents)): ?>
                <p class="text-muted">Nenhum documento anexado para este membro.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['title']) ?></td>
                                    <td>
                                        <?php 
                                            $type = strtolower($d['type'] ?? '');
                                            $label = $type;
                                            if ($type === 'transfer_letter') $label = 'Carta de Transferência';
                                            elseif ($type === 'rg') $label = 'RG';
                                            elseif ($type === 'cpf') $label = 'CPF';
                                            elseif ($type === 'other') $label = 'Outro';
                                        ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($label) ?></span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="/uploads/members_docs/<?= htmlspecialchars($d['file_path']) ?>">
                                            <i class="fas fa-download me-1"></i> Abrir
                                        </a>
                                        <a class="btn btn-sm btn-outline-danger btn-delete-doc" data-href="/admin/members/documents/delete/<?= $d['id'] ?>">
                                            <i class="fas fa-trash me-1"></i> Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
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
    const initialSelected = select.getAttribute('data-selected');
    
    // Se não foi passado um cargo específico para selecionar, usa o do atributo data-selected (cargo atual do membro)
    if (!selectedRole && initialSelected) {
        selectedRole = initialSelected;
    }
    
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
            
            // Se o cargo atual não estiver na lista (foi removido ou é legado), adiciona ele
            if (selectedRole && !Array.from(select.options).some(opt => opt.value === selectedRole)) {
                const option = new Option(selectedRole, selectedRole);
                option.selected = true;
                select.add(option);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar cargos:', error);
            select.innerHTML = '<option value="">Erro ao carregar</option>';
        });
}

// Carregar cargos ao iniciar
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
        const val = methodSelect.value.split(' — ')[0];
        helpBox.textContent = map[val] || '';
        transferBox.style.display = (val === 'Transferido') ? '' : 'none';
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
    const transferFile = document.querySelector('input[name=\"transfer_letter\"]');
    const hasExistingTransfer = <?= isset($hasTransferLetter) && $hasTransferLetter ? 'true' : 'false' ?>;
    if (transferFile) {
        transferFile.addEventListener('change', async function () {
            if (transferFile.files && transferFile.files.length > 0) {
                if (hasExistingTransfer) {
                    if (typeof Swal !== 'undefined') {
                        const res = await Swal.fire({
                            icon: 'warning',
                            title: 'Substituir carta?',
                            text: 'Já existe uma carta anexada. A nova irá substituir a atual.',
                            showCancelButton: true,
                            confirmButtonText: 'Substituir',
                            cancelButtonText: 'Cancelar'
                        });
                        if (!res.isConfirmed) {
                            transferFile.value = '';
                            return;
                        }
                    } else {
                        if (!confirm('Já existe uma carta anexada. A nova irá substituir a atual. Deseja continuar?')) {
                            transferFile.value = '';
                            return;
                        }
                    }
                }
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
    btnTransferCapture.addEventListener('click', async () => {
        if (hasExistingTransfer) {
            if (typeof Swal !== 'undefined') {
                const res = await Swal.fire({
                    icon: 'warning',
                    title: 'Substituir carta?',
                    text: 'Já existe uma carta anexada. A nova irá substituir a atual.',
                    showCancelButton: true,
                    confirmButtonText: 'Substituir',
                    cancelButtonText: 'Cancelar'
                });
                if (!res.isConfirmed) return;
            } else {
                if (!confirm('Já existe uma carta anexada. A nova irá substituir a atual. Deseja continuar?')) {
                    return;
                }
            }
        }
        const ctx = transferCanvas.getContext('2d');
        const targetW = transferCanvas.width;
        const targetH = transferCanvas.height;
        const targetRatio = targetW / targetH; // ~0.707 (A4 portrait)
        const vw = transferVideo.videoWidth || 640;
        const vh = transferVideo.videoHeight || 480;
        const videoRatio = vw / vh;
        let sx, sy, sw, sh;
        if (videoRatio > targetRatio) {
            sh = vh;
            sw = Math.floor(vh * targetRatio);
            sx = Math.floor((vw - sw) / 2);
            sy = 0;
        } else {
            sw = vw;
            sh = Math.floor(vw / targetRatio);
            sx = 0;
            sy = Math.floor((vh - sh) / 2);
        }
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

<script>
document.querySelectorAll('.btn-delete-doc').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.getAttribute('data-href');
        Swal.fire({
            title: 'Excluir documento?',
            text: 'Esta ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});
</script>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
<?php include __DIR__ . '/../../layout/footer.php'; ?>

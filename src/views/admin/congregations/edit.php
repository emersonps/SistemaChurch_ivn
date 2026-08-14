<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/congregations" class="text-decoration-none">Congregações</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Editar Congregação</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/congregations" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="congregationEditForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar alterações</button>
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
<form action="/admin/congregations/edit/<?= $congregation['id'] ?>" method="POST" enctype="multipart/form-data" class="app-form-with-bottom-actions" id="congregationEditForm" autocomplete="off">
    <?= csrf_field() ?>

    <!-- 1. Dados da Congregação -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">1</div>
            <div>
                <div class="member-form-card-title">Dados da Congregação</div>
                <div class="member-form-card-subtitle">Identificação e contato.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome da Congregação <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($congregation['name']) ?>" required>
                    <?php $type = strtolower((string)($congregation['type'] ?? '')); $isHq = in_array($type, ['headquarters', 'sede', 'matriz', 'principal'], true); ?>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_headquarters" value="1" id="isHeadquartersEdit" <?= $isHq ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isHeadquartersEdit">Igreja principal (Sede/Matriz)</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Dirigente <span class="required-mark">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="leader_name" id="leaderName" list="membersList" value="<?= htmlspecialchars($congregation['leader_name'] ?? '') ?>" autocomplete="off" placeholder="Digite o nome ou selecione..." required>
                        <datalist id="membersList">
                            <!-- Será preenchido via JS -->
                        </datalist>
                    </div>
                    <input type="hidden" name="leader_member_id" id="leaderMemberId">
                    <input type="hidden" name="transfer_leader" id="transferLeader" value="0">
                    <div class="form-text">Selecione um membro da lista ou digite um novo nome. Se selecionar um membro de outra congregação, ele poderá ser transferido para esta.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">CNPJ (Opcional)</label>
                    <input type="text" class="form-control" name="cnpj" id="cnpj" value="<?= htmlspecialchars($congregation['cnpj'] ?? '') ?>" placeholder="00.000.000/0000-00">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data de Abertura (Aniversário)</label>
                    <input type="date" class="form-control" name="opening_date" value="<?= !empty($congregation['opening_date']) ? date('Y-m-d', strtotime($congregation['opening_date'])) : '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telefone <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($congregation['phone'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($congregation['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto de Destaque</label>
                    <?php if (!empty($congregation['photo'])): ?>
                        <div class="mb-2">
                            <img src="/uploads/congregations/<?= $congregation['photo'] ?>" alt="Foto Atual" style="height: 50px;" class="rounded">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="photo" accept="image/*">
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Endereço -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">2</div>
            <div>
                <div class="member-form-card-title">Endereço</div>
                <div class="member-form-card-subtitle">Localização da congregação.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">CEP</label>
                    <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?= htmlspecialchars($congregation['zip_code'] ?? '') ?>">
                </div>
                <div class="col-md-9">
                    <label class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($congregation['address'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cidade <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($congregation['city'] ?? '') ?>" list="citiesList" required>
                    <datalist id="citiesList"></datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" id="state" name="state" value="<?= htmlspecialchars($congregation['state'] ?? '') ?>" list="statesList" required>
                    <datalist id="statesList"></datalist>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Horários de Culto -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">3</div>
            <div>
                <div class="member-form-card-title">Horários de Culto</div>
                <div class="member-form-card-subtitle">Agenda semanal de cultos e reuniões.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div id="schedule-container">
                <?php
                $schedules = !empty($congregation['service_schedule']) ? json_decode($congregation['service_schedule'], true) : [];
                if (empty($schedules)) {
                    $schedules = [['day' => 'Domingo', 'start_time' => '', 'end_time' => '']];
                }
                foreach ($schedules as $index => $schedule):
                ?>
                <div class="row g-3 mb-2 schedule-row">
                    <div class="col-md-3">
                        <select class="form-select" name="schedule[<?= $index ?>][day]">
                            <?php foreach (['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'] as $day): ?>
                                <option value="<?= $day ?>" <?= ($schedule['day'] ?? '') == $day ? 'selected' : '' ?>><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="schedule[<?= $index ?>][name]" value="<?= htmlspecialchars($schedule['name'] ?? '') ?>" placeholder="Nome (Ex: Escola Bíblica)">
                    </div>
                    <div class="col-md-2">
                        <input type="time" class="form-control" name="schedule[<?= $index ?>][start_time]" value="<?= $schedule['start_time'] ?? '' ?>" placeholder="Início">
                    </div>
                    <div class="col-md-2">
                        <input type="time" class="form-control" name="schedule[<?= $index ?>][end_time]" value="<?= $schedule['end_time'] ?? '' ?>" placeholder="Término">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-schedule" <?= count($schedules) == 1 ? 'disabled' : '' ?>><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill mt-2" id="add-schedule"><i class="fas fa-plus me-1"></i> Adicionar Horário</button>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/congregations" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Nome</div>
                <div class="summary-value" id="summaryName"><?= htmlspecialchars($congregation['name'] ?: '—') ?></div>

                <div class="summary-label">Dirigente</div>
                <div class="summary-value" id="summaryLeader"><?= htmlspecialchars($congregation['leader_name'] ?: '—') ?></div>

                <div class="summary-label">Localização</div>
                <div class="summary-value" id="summaryLocation"><?= htmlspecialchars(trim(($congregation['city'] ?? '') . ' / ' . ($congregation['state'] ?? ''), ' /') ?: '—') ?></div>

                <div class="summary-label">Tipo</div>
                <div class="summary-value mb-2" id="summaryType"><?= $isHq ? 'Sede/Matriz' : 'Regular' ?></div>

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
                Campos marcados com <span class="required-mark">*</span> são obrigatórios.
            </div>
        </div>
    </div>
</div>
</div>

<script>
// Cache global para membros
var membersCache = [];
var currentCongregationId = <?= (int)($congregation['id'] ?? 0) ?>;
document.getElementById('add-schedule').addEventListener('click', function() {
    const container = document.getElementById('schedule-container');
    const index = container.children.length;
    const template = `
        <div class="row g-3 mb-2 schedule-row">
            <div class="col-md-3">
                <select class="form-select" name="schedule[${index}][day]">
                    <option value="Domingo">Domingo</option>
                    <option value="Segunda">Segunda</option>
                    <option value="Terça">Terça</option>
                    <option value="Quarta">Quarta</option>
                    <option value="Quinta">Quinta</option>
                    <option value="Sexta">Sexta</option>
                    <option value="Sábado">Sábado</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="schedule[${index}][name]" placeholder="Nome (Ex: Escola Bíblica)">
            </div>
            <div class="col-md-2">
                <input type="time" class="form-control" name="schedule[${index}][start_time]" placeholder="Início">
            </div>
            <div class="col-md-2">
                <input type="time" class="form-control" name="schedule[${index}][end_time]" placeholder="Término">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-schedule"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', template);
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-schedule')) {
        const row = e.target.closest('.schedule-row');
        if (document.querySelectorAll('.schedule-row').length > 1) {
            row.remove();
        }
    }
});

// Carregar lista de membros para o datalist de Dirigente
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/members/list')
        .then(response => {
            if (!response.ok) throw new Error('Erro ao carregar membros');
            return response.json();
        })
        .then(members => {
            const datalist = document.getElementById('membersList');
            membersCache = members || [];
            members.forEach(member => {
                const option = document.createElement('option');
                option.value = member.name;
                datalist.appendChild(option);
            });
        })
        .catch(error => console.error('Erro:', error));

    // Máscara de CNPJ
    const cnpjInput = document.getElementById('cnpj');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '');
            if (v.length > 14) v = v.slice(0, 14);
            let out = '';
            if (v.length > 12) {
                out = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*$/, '$1.$2.$3/$4-$5');
            } else if (v.length > 8) {
                out = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4}).*$/, '$1.$2.$3/$4');
            } else if (v.length > 5) {
                out = v.replace(/^(\d{2})(\d{3})(\d{0,3}).*$/, '$1.$2.$3');
            } else if (v.length > 2) {
                out = v.replace(/^(\d{2})(\d{0,3}).*$/, '$1.$2');
            } else {
                out = v;
            }
            this.value = out;
        });
    }
});

// Confirmação de transferência de dirigente
const leaderInput = document.getElementById('leaderName');
const leaderMemberId = document.getElementById('leaderMemberId');
const transferLeader = document.getElementById('transferLeader');
leaderInput.addEventListener('change', () => {
    const name = String(leaderInput.value || '').trim().toLowerCase();
    leaderMemberId.value = '';
    transferLeader.value = '0';
    if (!name) return;
    const found = membersCache.find(m => String(m.name).toLowerCase() === name)
                || membersCache.find(m => String(m.name).toLowerCase().startsWith(name));
    if (found) {
        leaderMemberId.value = found.id;
        fetch(`/api/members/info/${found.id}`)
            .then(r => r.json())
            .then(info => {
                if (info && info.congregation_name && String(info.congregation_id || '') !== String(currentCongregationId)) {
                    Swal.fire({
                        title: 'Transferir dirigente?',
                        text: `O membro selecionado pertence à congregação "${info.congregation_name}". Ao confirmar, ele será transferido para esta congregação.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Confirmar transferência',
                        cancelButtonText: 'Cancelar'
                    }).then(res => {
                        if (res.isConfirmed) {
                            transferLeader.value = '1';
                        } else {
                            transferLeader.value = '0';
                            leaderMemberId.value = '';
                        }
                    });
                }
            })
            .catch(() => {});
    }
});
// Também escutar evento input para seleção via datalist
leaderInput.addEventListener('input', () => {
    const evt = new Event('change');
    leaderInput.dispatchEvent(evt);
});

const zipInput = document.getElementById('zip_code');
const addressInput = document.getElementById('address');
const cityInput = document.getElementById('city');
const stateInput = document.getElementById('state');
const statesList = document.getElementById('statesList');
const citiesList = document.getElementById('citiesList');
let states = [];

function loadStates() {
    fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados?order=nome')
        .then(r => r.json())
        .then(data => {
            states = data.map(s => ({ id: s.id, sigla: s.sigla, nome: s.nome }));
            statesList.innerHTML = '';
            states.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.sigla;
                statesList.appendChild(opt);
            });
        });
}

function loadCitiesByStateSigla(sigla) {
    const st = states.find(s => s.sigla.toLowerCase() === String(sigla || '').toLowerCase() || s.nome.toLowerCase() === String(sigla || '').toLowerCase());
    if (!st) return;
    fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${st.id}/municipios?order=nome`)
        .then(r => r.json())
        .then(data => {
            citiesList.innerHTML = '';
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.nome;
                citiesList.appendChild(opt);
            });
        });
}

stateInput.addEventListener('input', () => {
    loadCitiesByStateSigla(stateInput.value);
});

zipInput.addEventListener('blur', () => {
    const cep = (zipInput.value || '').replace(/\D/g, '');
    if (cep.length !== 8) return;
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(r => r.json())
        .then(data => {
            if (data.erro) return;
            addressInput.value = [data.logradouro, data.bairro].filter(Boolean).join(' - ');
            stateInput.value = data.uf || '';
            cityInput.value = data.localidade || '';
            loadCitiesByStateSigla(stateInput.value);
        })
        .catch(() => {});
});

loadStates();

// Painel de resumo lateral (nome, dirigente, localização, tipo, % preenchido)
const congForm = document.getElementById('congregationEditForm');
const summaryName = document.getElementById('summaryName');
const summaryLeader = document.getElementById('summaryLeader');
const summaryLocation = document.getElementById('summaryLocation');
const summaryType = document.getElementById('summaryType');
const summaryProgressPct = document.getElementById('summaryProgressPct');
const summaryProgressBar = document.getElementById('summaryProgressBar');
const isHeadquartersInput = document.getElementById('isHeadquartersEdit');

function updateCongSummary() {
    const nameVal = congForm.querySelector('[name="name"]').value.trim();
    summaryName.textContent = nameVal || '—';

    const leaderVal = leaderInput.value.trim();
    summaryLeader.textContent = leaderVal || '—';

    const cityVal = cityInput.value.trim();
    const stateVal = stateInput.value.trim();
    const location = [cityVal, stateVal].filter(Boolean).join(' / ');
    summaryLocation.textContent = location || '—';

    summaryType.textContent = isHeadquartersInput.checked ? 'Sede/Matriz' : 'Regular';

    const requiredFields = Array.from(congForm.querySelectorAll('[required]'));
    const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
    const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
    summaryProgressPct.textContent = pct + '%';
    summaryProgressBar.style.width = pct + '%';
}

congForm.addEventListener('input', updateCongSummary);
congForm.addEventListener('change', updateCongSummary);
updateCongSummary();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

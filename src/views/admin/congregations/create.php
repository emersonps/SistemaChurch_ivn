<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Nova Congregação</h1>
</div>

<form action="/admin/congregations/create" method="POST" enctype="multipart/form-data" class="row g-3" autocomplete="off">
    <?= csrf_field() ?>
    <div class="col-md-6">
        <label class="form-label">Nome da Congregação</label>
        <input type="text" class="form-control" name="name" required placeholder="Ex: Congregação Vale da Bênção" autocomplete="off">
    </div>
    <div class="col-md-6">
        <label class="form-label">Dirigente</label>
        <div class="input-group">
            <input type="text" class="form-control" name="leader_name" id="leaderNameCreate" list="membersListCreate" placeholder="Digite o nome ou selecione..." autocomplete="off">
            <datalist id="membersListCreate"></datalist>
        </div>
        <input type="hidden" name="leader_member_id" id="leaderMemberId">
        <input type="hidden" name="transfer_leader" id="transferLeader" value="0">
        <small class="text-muted">Selecione um membro da lista ou digite um novo nome. Se selecionar um membro de outra congregação, ele será transferido para esta nova congregação ao salvar.</small>
    </div>
    <div class="col-md-4">
        <label class="form-label">CNPJ (Opcional)</label>
        <input type="text" class="form-control" name="cnpj" id="cnpj" placeholder="00.000.000/0000-00" autocomplete="off">
    </div>
    <div class="col-md-4">
        <label class="form-label">Data de Abertura (Aniversário)</label>
        <input type="date" class="form-control" name="opening_date" autocomplete="off">
    </div>
    <div class="col-md-4">
        <label class="form-label">Telefone</label>
        <input type="text" class="form-control" name="phone" placeholder="(00) 00000-0000" autocomplete="off">
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" autocomplete="off">
    </div>
    <div class="col-md-3">
        <label class="form-label">CEP</label>
        <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="00000-000" autocomplete="off">
    </div>
    <div class="col-md-6">
        <label class="form-label">Endereço</label>
        <input type="text" class="form-control" id="address" name="address" placeholder="Rua, Número, Bairro" autocomplete="off">
    </div>
    <div class="col-md-3">
        <label class="form-label">Cidade</label>
        <input type="text" class="form-control" id="city" name="city" list="citiesList" autocomplete="off">
        <datalist id="citiesList"></datalist>
    </div>
    <div class="col-md-3">
        <label class="form-label">Estado</label>
        <input type="text" class="form-control" id="state" name="state" placeholder="UF" list="statesList" autocomplete="off">
        <datalist id="statesList"></datalist>
    </div>
    <div class="col-md-9">
        <label class="form-label">Foto de Destaque</label>
        <input type="file" class="form-control" name="photo" accept="image/*" autocomplete="off">
    </div>

    <div class="col-md-12 mt-4">
        <h4 class="mb-3">Horários de Culto</h4>
        <div id="schedule-container">
            <div class="row g-3 mb-2 schedule-row">
                <div class="col-md-3">
                    <select class="form-select" name="schedule[0][day]">
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
                    <input type="text" class="form-control" name="schedule[0][name]" placeholder="Nome (Ex: Escola Bíblica)">
                </div>
                <div class="col-md-2">
                    <input type="time" class="form-control" name="schedule[0][start_time]" placeholder="Início">
                </div>
                <div class="col-md-2">
                    <input type="time" class="form-control" name="schedule[0][end_time]" placeholder="Término">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-schedule" disabled><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-success btn-sm mt-2" id="add-schedule"><i class="fas fa-plus"></i> Adicionar Horário</button>
    </div>

    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/admin/congregations" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<script>
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
// Carregar lista de membros para o datalist de Dirigente (Create)
document.addEventListener('DOMContentLoaded', function() {
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

    let membersCache = [];
    fetch('/api/members/list')
        .then(response => {
            if (!response.ok) throw new Error('Erro ao carregar membros');
            return response.json();
        })
        .then(members => {
            const datalist = document.getElementById('membersListCreate');
            datalist.innerHTML = '';
            membersCache = members || [];
            members.forEach(member => {
                const option = document.createElement('option');
                option.value = member.name;
                datalist.appendChild(option);
            });
        })
        .catch(error => console.error('Erro:', error));
    
    const leaderInput = document.getElementById('leaderNameCreate');
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
                    if (info && info.congregation_name) {
                        Swal.fire({
                            title: 'Transferir dirigente?',
                            text: `O membro selecionado pertence à congregação "${info.congregation_name}". Ao confirmar, ele será transferido para a nova congregação.`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Confirmar transferência',
                            cancelButtonText: 'Cancelar'
                        }).then(res => {
                            if (res.isConfirmed) {
                                transferLeader.value = '1';
                                // mantém seleção e nome
                            } else {
                                transferLeader.value = '0';
                                leaderMemberId.value = '';
                                // mantém nome digitado caso queira apenas texto
                            }
                        });
                    }
                })
                .catch(() => {});
        }
    });
});
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
                <button type="button" class="btn btn-danger btn-sm remove-schedule"><i class="fas fa-trash"></i></button>
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
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/events" class="text-decoration-none">Eventos</a></li>
                <li class="breadcrumb-item active">Novo</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Novo Evento</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/events" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="eventCreateForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
    .event-date-row .weekday-label {
        min-height: 1.2em;
        white-space: nowrap;
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
<form action="/admin/events/create" method="POST" class="app-form-with-bottom-actions" id="eventCreateForm" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- 1. Informações Básicas -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">1</div>
            <div>
                <div class="member-form-card-title">Informações Básicas</div>
                <div class="member-form-card-subtitle">Título, tipo e banner do evento.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="col-md-6" id="bannerFieldBox">
                    <label class="form-label">Banner (Imagem)</label>
                    <input type="file" class="form-control" name="banner" accept="image/*">
                    <div class="form-text">Recomendado: Formato JPG/PNG</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" name="type">
                        <option value="culto">Culto — recorrente (cultos semanais, diários etc.)</option>
                        <option value="evento">Evento — pontual (aniversários, congressos, datas marcadas)</option>
                        <option value="convite">Convite — fora da igreja (culto no lar, rua, convite de outras igrejas)</option>
                        <option value="interno">Interno — reuniões e encontros para grupos fechados</option>
                    </select>
                    <div id="typeHelp" class="form-text">Culto: para eventos recorrentes, diários, como cultos semanais.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Data e Recorrência -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">2</div>
            <div>
                <div class="member-form-card-title">Data e Recorrência</div>
                <div class="member-form-card-subtitle">Quando o evento acontece.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-12" id="eventDatesBox">
                    <label class="form-label">Datas</label>
                    <div id="eventDatesContainer" class="d-grid gap-2"></div>
                    <div class="form-text">Adicione uma ou mais datas para o mesmo evento.</div>
                </div>
                <div class="col-12" id="recurringDaysBox" style="display:none">
                    <label class="form-label">Dias da Semana (Recorrente)</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="recurring_days[]" value="Domingo" id="dom">
                            <label class="form-check-label" for="dom">Domingo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="recurring_days[]" value="Segunda" id="seg">
                            <label class="form-check-label" for="seg">Segunda</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="recurring_days[]" value="Terça" id="ter">
                            <label class="form-check-label" for="ter">Terça</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="recurring_days[]" value="Quarta" id="qua">
                            <label class="form-check-label" for="qua">Quarta</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="recurring_days[]" value="Quinta" id="qui">
                            <label class="form-check-label" for="qui">Quinta</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="recurring_days[]" value="Sexta" id="sex">
                            <label class="form-check-label" for="sex">Sexta</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="recurring_days[]" value="Sábado" id="sab">
                            <label class="form-check-label" for="sab">Sábado</label>
                        </div>
                    </div>
                    <div class="form-text mb-2">Selecione para cultos semanais fixos.</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Horário do Culto</label>
                            <input type="time" class="form-control" name="event_time_only">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Horário Término</label>
                            <input type="time" class="form-control" name="end_time">
                            <div class="form-text">Opcional</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Local e Contato -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">3</div>
            <div>
                <div class="member-form-card-title">Local e Contato</div>
                <div class="member-form-card-subtitle">Onde o evento acontece e como falar com os responsáveis.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-9">
                    <label class="form-label">Local (Congregação ou Outro) <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="location" id="locationInput" list="congregationList" placeholder="Selecione ou digite um local..." required>
                    <datalist id="congregationList">
                        <?php foreach ($congregations as $cong): ?>
                            <option value="<?= htmlspecialchars($cong['name']) ?>" data-address="<?= htmlspecialchars($cong['address'] ?? '') ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <div class="form-text">Escolha uma congregação ou digite um local livre. Se digitar livre, todos verão o evento.</div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Endereço do Evento</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="address" id="addressInput" placeholder="Digite o endereço ou selecione a congregação">
                        <button class="btn btn-outline-secondary" type="button" id="useCongregationAddress">
                            Usar da Congregação
                        </button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">E-mail de Contato</label>
                    <input type="email" class="form-control" name="contact_email" placeholder="ex: contato@igreja.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label">WhatsApp/Celular</label>
                    <input type="text" class="form-control" name="contact_phone" placeholder="(00) 00000-0000">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Acesso (Interno) -->
    <div class="member-form-card" id="internalOptions" style="display:none">
        <div class="member-form-card-header">
            <div class="member-form-badge">4</div>
            <div>
                <div class="member-form-card-title">Acesso</div>
                <div class="member-form-card-subtitle">Evento interno: selecione quem poderá visualizar na área de membro.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Membros Autorizados</label>
                    <select class="form-select" name="allowed_members[]" multiple size="8">
                        <?php foreach (($members ?? []) as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?><?= !empty($m['congregation_name']) ? ' (' . htmlspecialchars($m['congregation_name']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Selecione um ou mais membros específicos.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Congregações Autorizadas</label>
                    <select class="form-select" name="allowed_congregations[]" multiple size="8">
                        <?php foreach ($congregations as $cong): ?>
                            <option value="<?= $cong['id'] ?>"><?= htmlspecialchars($cong['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Todos os membros das congregações selecionadas poderão ver.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/events" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Título</div>
                <div class="summary-value text-muted-value" id="summaryTitle">—</div>

                <div class="summary-label">Local</div>
                <div class="summary-value text-muted-value" id="summaryLocation">—</div>

                <div class="summary-label">Tipo</div>
                <div class="summary-value" id="summaryType">Culto</div>

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
                Campos marcados com <span class="required-mark">*</span> são obrigatórios.
            </div>
        </div>
    </div>
</div>
</div>

<script>
    document.getElementById('useCongregationAddress').addEventListener('click', function() {
        var inputVal = document.getElementById('locationInput').value;
        var options = document.getElementById('congregationList').options;
        var found = false;

        for (var i = 0; i < options.length; i++) {
            if (options[i].value === inputVal) {
                var address = options[i].getAttribute('data-address');
                if (address) {
                    document.getElementById('addressInput').value = address;
                    found = true;
                }
                break;
            }
        }

        if (!found) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'A congregação selecionada não possui endereço cadastrado ou é um local personalizado.',
                confirmButtonColor: '#3085d6'
            });
        }
    });

    (function () {
        var container = document.getElementById('eventDatesContainer');
        if (!container) return;

        var week = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

        function getWeekdayLabel(dateValue) {
            if (!dateValue) return '';
            var parts = String(dateValue).split('-');
            if (parts.length !== 3) return '';
            var d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            return week[d.getDay()] || '';
        }

        function renumber() {
            var rows = container.querySelectorAll('.event-date-row');
            rows.forEach(function (row, idx) {
                row.dataset.index = String(idx);
                var dateInput = row.querySelector('input[type="date"]');
                var timeInput = row.querySelector('input[type="time"]');
                if (dateInput) dateInput.name = 'event_dates[' + idx + '][date]';
                if (timeInput) timeInput.name = 'event_dates[' + idx + '][time]';
            });
            rows.forEach(function (row) {
                var del = row.querySelector('.btn-remove-date');
                if (del) del.disabled = rows.length <= 1;
            });
        }

        function updateWeekday(row) {
            var dateInput = row.querySelector('input[type="date"]');
            var label = row.querySelector('.weekday-label');
            if (!dateInput || !label) return;
            var text = getWeekdayLabel(dateInput.value);
            label.textContent = text || '';
        }

        function addRow(initial) {
            var idx = container.querySelectorAll('.event-date-row').length;
            var row = document.createElement('div');
            row.className = 'event-date-row row g-2 align-items-start';
            row.dataset.index = String(idx);
            row.innerHTML = `
                <div class="col-6 col-md-3">
                    <label class="form-label mb-1">Data</label>
                    <input type="date" class="form-control" name="event_dates[${idx}][date]">
                    <div class="form-text weekday-label"></div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label mb-1">Horário</label>
                    <input type="time" class="form-control" name="event_dates[${idx}][time]">
                </div>
                <div class="col-12 col-md-2 d-flex gap-2 align-self-end">
                    <button type="button" class="btn btn-outline-primary btn-add-date" title="Adicionar outra data">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-remove-date" title="Remover esta data">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);

            var dateInput = row.querySelector('input[type="date"]');
            var timeInput = row.querySelector('input[type="time"]');
            if (initial && dateInput) dateInput.value = initial.date || '';
            if (initial && timeInput) timeInput.value = initial.time || '';

            row.querySelector('.btn-add-date').addEventListener('click', function () {
                addRow();
                renumber();
            });
            row.querySelector('.btn-remove-date').addEventListener('click', function () {
                row.remove();
                renumber();
            });
            if (dateInput) {
                dateInput.addEventListener('change', function () {
                    updateWeekday(row);
                });
                updateWeekday(row);
            }
            renumber();
        }

        addRow();
    })();


    const typeSelect = document.querySelector('select[name="type"]');
    const internalBox = document.getElementById('internalOptions');
    const recurringDaysBox = document.getElementById('recurringDaysBox');
    const eventDatesBox = document.getElementById('eventDatesBox');
    const bannerFieldBox = document.getElementById('bannerFieldBox');
    function toggleInternal() {
        if (String(typeSelect.value).toLowerCase() === 'interno') {
            internalBox.style.display = '';
        } else {
            internalBox.style.display = 'none';
        }
        const isCulto = String(typeSelect.value).toLowerCase() === 'culto';
        if (recurringDaysBox) {
            recurringDaysBox.style.display = isCulto ? '' : 'none';
        }
        if (eventDatesBox) {
            eventDatesBox.style.display = isCulto ? 'none' : '';
        }
        if (bannerFieldBox) {
            bannerFieldBox.style.display = isCulto ? 'none' : '';
        }
        const map = {
            'culto': 'Culto: para eventos recorrentes, diários, como cultos semanais.',
            'evento': 'Evento: para eventos com datas marcadas, não recorrentes (aniversário, congressos).',
            'convite': 'Convite: para eventos fora da igreja, culto de rua, no lar, convites de outras igrejas.',
            'interno': 'Interno: apenas para reuniões e grupos fechados; visível só para selecionados.'
        };
        document.getElementById('typeHelp').textContent = map[String(typeSelect.value).toLowerCase()] || '';
    }
    typeSelect.addEventListener('change', toggleInternal);
    toggleInternal();

    // Exclusividade: selecionar membros limpa congregações e vice-versa
    (function(){
        const selMembers = document.querySelector('select[name="allowed_members[]"]');
        const selCongs = document.querySelector('select[name="allowed_congregations[]"]');
        function clearIfOtherSelected(changed) {
            if (!selMembers || !selCongs) return;
            const membersSelected = Array.from(selMembers.options).some(o => o.selected);
            const congsSelected = Array.from(selCongs.options).some(o => o.selected);
            if (changed === 'members' && membersSelected) {
                Array.from(selCongs.options).forEach(o => o.selected = false);
            } else if (changed === 'congs' && congsSelected) {
                Array.from(selMembers.options).forEach(o => o.selected = false);
            }
        }
        if (selMembers) selMembers.addEventListener('change', () => clearIfOtherSelected('members'));
        if (selCongs) selCongs.addEventListener('change', () => clearIfOtherSelected('congs'));
    })();

    // Painel de resumo lateral (título, local, tipo, % preenchido)
    const eventForm = document.getElementById('eventCreateForm');
    const summaryTitle = document.getElementById('summaryTitle');
    const summaryLocation = document.getElementById('summaryLocation');
    const summaryType = document.getElementById('summaryType');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');
    const typeLabels = {
        'culto': 'Culto',
        'evento': 'Evento',
        'convite': 'Convite',
        'interno': 'Interno'
    };

    function updateEventSummary() {
        const titleVal = eventForm.querySelector('[name="title"]').value.trim();
        summaryTitle.textContent = titleVal || '—';
        summaryTitle.classList.toggle('text-muted-value', !titleVal);

        const locationVal = document.getElementById('locationInput').value.trim();
        summaryLocation.textContent = locationVal || '—';
        summaryLocation.classList.toggle('text-muted-value', !locationVal);

        summaryType.textContent = typeLabels[String(typeSelect.value).toLowerCase()] || 'Culto';

        const requiredFields = Array.from(eventForm.querySelectorAll('[required]'));
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    eventForm.addEventListener('input', updateEventSummary);
    eventForm.addEventListener('change', updateEventSummary);
    updateEventSummary();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

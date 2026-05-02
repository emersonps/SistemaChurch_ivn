<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Evento</h1>
</div>

<style>
    .event-date-row .weekday-label {
        min-height: 1.2em;
        white-space: nowrap;
    }
</style>

<form action="/admin/events/create" method="POST" class="row g-3 app-form-with-bottom-actions" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="col-md-6">
        <label class="form-label">Título</label>
        <input type="text" class="form-control" name="title" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Banner (Imagem)</label>
        <input type="file" class="form-control" name="banner" accept="image/*">
        <small class="text-muted">Recomendado: Formato JPG/PNG</small>
    </div>
    <div class="col-12">
        <label class="form-label">Datas</label>
        <div id="eventDatesContainer" class="d-grid gap-2"></div>
        <div class="form-text">Adicione uma ou mais datas para o mesmo evento.</div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Tipo</label>
        <select class="form-select" name="type">
            <option value="culto">Culto — recorrente (cultos semanais, diários etc.)</option>
            <option value="evento">Evento — pontual (aniversários, congressos, datas marcadas)</option>
            <option value="convite">Convite — fora da igreja (culto no lar, rua, convite de outras igrejas)</option>
            <option value="interno">Interno — reuniões e encontros para grupos fechados</option>
        </select>
        <div id="typeHelp" class="form-text">Culto: para eventos recorrentes, diários, como cultos semanais.</div>
    </div>
    <div class="col-md-9">
        <label class="form-label">Local (Congregação ou Outro)</label>
        <input type="text" class="form-control" name="location" id="locationInput" list="congregationList" placeholder="Selecione ou digite um local...">
        <datalist id="congregationList">
            <?php foreach ($congregations as $cong): ?>
                <option value="<?= htmlspecialchars($cong['name']) ?>" data-address="<?= htmlspecialchars($cong['address'] ?? '') ?>">
            <?php endforeach; ?>
        </datalist>
        <small class="text-muted">Escolha uma congregação ou digite um local livre. Se digitar livre, todos verão o evento.</small>
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
    
    <div class="col-md-12" id="internalOptions" style="display:none">
        <div class="alert alert-warning mb-2">Evento Interno: selecione quem poderá visualizar na área de membro.</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Membros Autorizados</label>
                <select class="form-select" name="allowed_members[]" multiple size="8">
                    <?php foreach (($members ?? []) as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?><?= !empty($m['congregation_name']) ? ' (' . htmlspecialchars($m['congregation_name']) . ')' : '' ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Selecione um ou mais membros específicos.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Congregações Autorizadas</label>
                <select class="form-select" name="allowed_congregations[]" multiple size="8">
                    <?php foreach ($congregations as $cong): ?>
                        <option value="<?= $cong['id'] ?>"><?= htmlspecialchars($cong['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Todos os membros das congregações selecionadas poderão ver.</small>
            </div>
        </div>
    </div>

    <div class="col-12 mt-4 text-end d-none d-lg-block">
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
        <a href="/admin/events" class="btn btn-outline-secondary px-4">Cancelar</a>
    </div>

    <div class="col-12 app-form-bottom-actions d-lg-none">
        <div class="row g-2">
            <div class="col-6">
                <button type="submit" class="btn btn-primary w-100">Salvar</button>
            </div>
            <div class="col-6">
                <a href="/admin/events" class="btn btn-outline-secondary w-100">Cancelar</a>
            </div>
        </div>
    </div>
</form>

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
    function toggleInternal() {
        if (String(typeSelect.value).toLowerCase() === 'interno') {
            internalBox.style.display = '';
        } else {
            internalBox.style.display = 'none';
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
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

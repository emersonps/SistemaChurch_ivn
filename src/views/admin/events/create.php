<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Evento</h1>
</div>

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
    <div class="col-md-3">
        <label class="form-label">Data (Evento Único)</label>
        <input type="date" class="form-control" name="event_date_only">
        <small class="text-muted">Se preenchido, ignora dias da semana</small>
    </div>
    <div class="col-md-3">
        <label class="form-label">Horário Início</label>
        <input type="time" class="form-control" name="event_time_only">
        <small class="text-muted">Horário do culto/evento</small>
    </div>
    <div class="col-md-3">
        <label class="form-label">Horário Término</label>
        <input type="time" class="form-control" name="end_time">
        <small class="text-muted">Opcional</small>
    </div>
    <div class="col-md-12">
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
        <small class="text-muted">Selecione para cultos semanais fixos</small>
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

    // Marcar o dia da semana automaticamente ao selecionar a data
    document.querySelector('input[name="event_date_only"]').addEventListener('change', function() {
        if (this.value) {
            // Desmarcar todas as checkboxes primeiro
            var checkboxes = document.querySelectorAll('input[name="recurring_days[]"]');
            checkboxes.forEach(function(cb) {
                cb.checked = false;
            });

            // Criar data forçando o fuso horário local para evitar problemas de fuso no JS
            var dateParts = this.value.split('-');
            var date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
            
            var dayOfWeek = date.getDay(); // 0 (Dom) a 6 (Sab)
            
            // Mapeamento dos IDs das checkboxes
            var dayIds = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab'];
            var targetId = dayIds[dayOfWeek];
            
            // Marcar a checkbox correspondente
            var checkbox = document.getElementById(targetId);
            if (checkbox) {
                checkbox.checked = true;
            }
        }
    });


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

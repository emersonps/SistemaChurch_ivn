<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Evento</h1>
</div>

<form action="/admin/events/edit/<?= $event['id'] ?>" method="POST" class="row g-3" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="col-md-6">
        <label class="form-label">Título</label>
        <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Banner (Atualizar)</label>
        <?php if (!empty($event['banner_path'])): ?>
            <div class="mb-2">
                <a href="<?= $event['banner_path'] ?>" target="_blank">
                    <img src="<?= $event['banner_path'] ?>" alt="Banner Atual" style="height: 50px; object-fit: cover; border-radius: 4px;">
                </a>
                <small class="text-success"><i class="fas fa-check"></i> Banner existente</small>
            </div>
        <?php endif; ?>
        <input type="file" class="form-control" name="banner" accept="image/*">
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="remove_banner" id="removeBanner">
            <label class="form-check-label" for="removeBanner">Remover banner</label>
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Data (Evento Único)</label>
        <input type="date" class="form-control" name="event_date_only" value="<?= $event['event_date'] ? date('Y-m-d', strtotime($event['event_date'])) : '' ?>">
        <small class="text-muted">Se preenchido, ignora dias da semana</small>
    </div>
    <div class="col-md-3">
        <label class="form-label">Horário Início</label>
        <input type="time" class="form-control" name="event_time_only" value="<?= $event['event_date'] ? date('H:i', strtotime($event['event_date'])) : '' ?>">
        <small class="text-muted">Horário do culto/evento</small>
    </div>
    <div class="col-md-3">
        <label class="form-label">Horário Término</label>
        <input type="time" class="form-control" name="end_time" value="<?= $event['end_time'] ?? '' ?>">
        <small class="text-muted">Opcional</small>
    </div>
    <div class="col-md-12">
        <label class="form-label">Dias da Semana (Recorrente)</label>
        <div class="d-flex gap-3 flex-wrap">
            <?php 
                $days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                $selected_days = !empty($event['recurring_days']) ? json_decode($event['recurring_days'], true) : [];
                // Fallback para caso não seja JSON válido (ex: string antiga)
                if (!is_array($selected_days)) $selected_days = [];
            ?>
            <?php foreach ($days as $day): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recurring_days[]" value="<?= $day ?>" id="edit_<?= $day ?>" <?= in_array($day, $selected_days) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="edit_<?= $day ?>"><?= $day ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Tipo</label>
        <select class="form-select" name="type">
            <option value="culto" <?= $event['type'] == 'culto' ? 'selected' : '' ?>>Culto — recorrente (cultos semanais, diários etc.)</option>
            <option value="evento" <?= (in_array($event['type'], ['evento', 'congresso', 'aniversario', 'outro'])) ? 'selected' : '' ?>>Evento — pontual (aniversários, congressos, datas marcadas)</option>
            <option value="convite" <?= $event['type'] == 'convite' ? 'selected' : '' ?>>Convite — fora da igreja (culto no lar, rua, convite de outras igrejas)</option>
            <option value="interno" <?= strtolower($event['type']) == 'interno' ? 'selected' : '' ?>>Interno — reuniões e encontros para grupos fechados</option>
        </select>
        <div id="typeHelp" class="form-text"></div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
            <option value="active" <?= ($event['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>Ativo</option>
            <option value="inactive" <?= ($event['status'] ?? 'active') == 'inactive' ? 'selected' : '' ?>>Inativo</option>
        </select>
    </div>
    <div class="col-md-9">
        <label class="form-label">Local (Congregação ou Outro)</label>
        <input type="text" class="form-control" name="location" id="locationInput" list="congregationList" value="<?= htmlspecialchars($event['location'] ?? '') ?>" placeholder="Selecione ou digite um local...">
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
            <input type="text" class="form-control" name="address" id="addressInput" value="<?= htmlspecialchars($event['address'] ?? '') ?>" placeholder="Digite o endereço ou selecione a congregação">
            <button class="btn btn-outline-secondary" type="button" id="useCongregationAddress">
                Usar da Congregação
            </button>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">E-mail de Contato</label>
        <input type="email" class="form-control" name="contact_email" value="<?= htmlspecialchars($event['contact_email'] ?? '') ?>" placeholder="ex: contato@igreja.com">
    </div>
    <div class="col-md-6">
        <label class="form-label">WhatsApp/Celular</label>
        <input type="text" class="form-control" name="contact_phone" value="<?= htmlspecialchars($event['contact_phone'] ?? '') ?>" placeholder="(00) 00000-0000">
    </div>

    <div class="col-md-12">
        <label class="form-label">Descrição</label>
        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
    </div>
    
    <div class="col-md-12" id="internalOptions" style="display:none">
        <div class="alert alert-warning mb-2">Evento Interno: selecione quem poderá visualizar na área de membro.</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Membros Autorizados</label>
                <select class="form-select" name="allowed_members[]" multiple size="8">
                    <?php foreach (($members ?? []) as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= in_array($m['id'], ($allowedMemberIds ?? [])) ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Selecione um ou mais membros específicos.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Congregações Autorizadas</label>
                <select class="form-select" name="allowed_congregations[]" multiple size="8">
                    <?php foreach ($congregations as $cong): ?>
                        <option value="<?= $cong['id'] ?>" <?= in_array($cong['id'], ($allowedCongIds ?? [])) ? 'selected' : '' ?>><?= htmlspecialchars($cong['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Todos os membros das congregações selecionadas poderão ver.</small>
            </div>
        </div>
    </div>

    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="/admin/events" class="btn btn-secondary">Cancelar</a>
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

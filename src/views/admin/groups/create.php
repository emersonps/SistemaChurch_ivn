<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Grupo / Célula</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/groups" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="/admin/groups/create" method="POST" class="app-form-with-bottom-actions">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nome do Grupo *</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Célula Betel">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Congregação *</label>
                            <select name="congregation_id" id="congregationSelect" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($congregations as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Líder *</label>
                            <select name="leader_id" class="form-select member-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($members as $m): ?>
                                    <option value="<?= $m['id'] ?>" data-congregation-id="<?= $m['congregation_id'] ?? '' ?>">
                                        <?= htmlspecialchars($m['name']) ?> 
                                        <?= $m['congregation_name'] ? '(' . htmlspecialchars($m['congregation_name']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Dia da Reunião *</label>
                            <select name="meeting_day" class="form-select" required>
                                <option value="">Selecione...</option>
                                <option value="Segunda-feira">Segunda-feira</option>
                                <option value="Terça-feira">Terça-feira</option>
                                <option value="Quarta-feira">Quarta-feira</option>
                                <option value="Quinta-feira">Quinta-feira</option>
                                <option value="Sexta-feira">Sexta-feira</option>
                                <option value="Sábado">Sábado</option>
                                <option value="Domingo">Domingo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Horário *</label>
                            <input type="time" name="meeting_time" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Endereço *</label>
                        <input type="text" name="address" class="form-control" placeholder="Rua, Número, Bairro" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Anfitrião (Dono da Casa) *</label>
                        <input type="text" class="form-control" name="host_name" id="hostInput" list="hostList" placeholder="Selecione um membro ou digite o nome..." required>
                        <datalist id="hostList">
                            <?php foreach ($members as $m): ?>
                                <option value="<?= htmlspecialchars($m['name']) ?>" data-id="<?= $m['id'] ?>" data-congregation-id="<?= $m['congregation_id'] ?? '' ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <small class="text-muted">Se a pessoa não for membro, basta digitar o nome dela.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição / Observações</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="text-end d-none d-lg-block">
                        <button type="submit" class="btn btn-primary px-4">Salvar</button>
                        <a href="/admin/groups" class="btn btn-outline-secondary px-4">Cancelar</a>
                    </div>

                    <div class="app-form-bottom-actions d-lg-none">
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary w-100">Salvar</button>
                            </div>
                            <div class="col-6">
                                <a href="/admin/groups" class="btn btn-outline-secondary w-100">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const congregationSelect = document.getElementById('congregationSelect');
    const memberSelects = document.querySelectorAll('.member-select');
    const hostList = document.getElementById('hostList');

    function filterMembers() {
        const selectedCongregationId = congregationSelect.value;
        
        // Filtrar selects normais (Líder)
        memberSelects.forEach(select => {
            const options = Array.from(select.options);
            
            options.forEach(option => {
                if (option.value === "") return;

                const memberCongregationId = option.getAttribute('data-congregation-id');
                let shouldShow = false;
                
                if (!selectedCongregationId) {
                    shouldShow = false; // NÃO mostrar ninguém se não tem congregação selecionada
                } else {
                    if (memberCongregationId == selectedCongregationId) {
                        shouldShow = true;
                    }
                }

                if (shouldShow) {
                    option.style.display = '';
                    option.disabled = false;
                    option.hidden = false;
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                    option.hidden = true;
                    
                    if (option.selected) {
                        select.value = "";
                    }
                }
            });
        });

        // Filtrar datalist (Anfitrião)
        if (hostList) {
            const datalistOptions = Array.from(hostList.options);
            datalistOptions.forEach(option => {
                const memberCongregationId = option.getAttribute('data-congregation-id');
                let shouldShow = false;
                
                if (!selectedCongregationId) {
                    shouldShow = false; // Oculta se não tem congregação
                } else {
                    if (memberCongregationId == selectedCongregationId) {
                        shouldShow = true;
                    }
                }

                if (shouldShow) {
                    option.disabled = false;
                } else {
                    option.disabled = true;
                }
            });
        }
    }

    if (congregationSelect) {
        congregationSelect.addEventListener('change', filterMembers);
        filterMembers();
    }
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

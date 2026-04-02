<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Grupo</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/groups/show/<?= $group['id'] ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Cancelar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="/admin/groups/edit/<?= $group['id'] ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nome do Grupo *</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($group['name']) ?>">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Congregação *</label>
                            <select name="congregation_id" id="congregationSelect" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($congregations as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $group['congregation_id'] == $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Líder *</label>
                            <select name="leader_id" class="form-select member-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($members as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $group['leader_id'] == $m['id'] ? 'selected' : '' ?> data-congregation-id="<?= $m['congregation_id'] ?? '' ?>">
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
                                <?php 
                                $days = ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo'];
                                foreach ($days as $day):
                                ?>
                                    <option value="<?= $day ?>" <?= $group['meeting_day'] == $day ? 'selected' : '' ?>><?= $day ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Horário *</label>
                            <input type="time" name="meeting_time" class="form-control" value="<?= $group['meeting_time'] ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Endereço *</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($group['address']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Anfitrião (Dono da Casa) *</label>
                        <?php 
                        $hostNameValue = '';
                        if ($group['host_id']) {
                            foreach ($members as $m) {
                                if ($m['id'] == $group['host_id']) {
                                    $hostNameValue = $m['name'];
                                    break;
                                }
                            }
                        } else {
                            $hostNameValue = $group['host_name'] ?? '';
                        }
                        ?>
                        <input type="text" class="form-control" name="host_name" id="hostInput" list="hostList" value="<?= htmlspecialchars($hostNameValue) ?>" placeholder="Selecione um membro ou digite o nome..." required>
                        <datalist id="hostList">
                            <?php foreach ($members as $m): ?>
                                <option value="<?= htmlspecialchars($m['name']) ?>" data-id="<?= $m['id'] ?>" data-congregation-id="<?= $m['congregation_id'] ?? '' ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <small class="text-muted">Se a pessoa não for membro, basta digitar o nome dela.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição / Observações</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($group['description']) ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
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
    // Membros que JÁ pertencem ao grupo devem aparecer independente da congregação selecionada
    const currentMemberIds = <?= json_encode(array_map('intval', $currentMemberIds ?? [])) ?>;

    function filterMembers() {
        const selectedCongregationId = congregationSelect.value;
        
        memberSelects.forEach(select => {
            const options = Array.from(select.options);
            
            options.forEach(option => {
                if (option.value === "") return; // Pula a opção "Selecione..."

                const memberCongregationId = option.getAttribute('data-congregation-id');
                const memberId = parseInt(option.value);
                
                let shouldShow = false;
                
                // Se não tem congregação selecionada, NÃO mostra ninguém
                if (!selectedCongregationId) {
                    shouldShow = false;
                } else {
                    // Mostra se:
                    // 1. É da mesma congregação
                    // 2. Já está selecionado (é o líder atual)
                    // 3. É membro do grupo
                    if (memberCongregationId == selectedCongregationId || option.selected || currentMemberIds.includes(memberId)) {
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
                    
                    if (option.selected && selectedCongregationId !== "") {
                         select.value = "";
                    }
                }
            });
        });

        // Filtrar datalist (Anfitrião)
        if (hostList) {
            const datalistOptions = Array.from(hostList.options);
            const currentHostValue = document.getElementById('hostInput').value;

            datalistOptions.forEach(option => {
                const memberCongregationId = option.getAttribute('data-congregation-id');
                let shouldShow = false;
                
                if (!selectedCongregationId) {
                    shouldShow = false; // Oculta se não tem congregação
                } else {
                    // Se for da congregação ou for o anfitrião atual (mesmo nome)
                    if (memberCongregationId == selectedCongregationId || option.value === currentHostValue) {
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
        // Roda uma vez no início para aplicar filtro se já vier selecionado (edit)
        filterMembers();
    }
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

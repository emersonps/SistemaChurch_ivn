<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/groups" class="text-decoration-none">Grupos e Células</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Editar Grupo</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/groups/show/<?= $group['id'] ?>" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="groupEditForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
<form action="/admin/groups/edit/<?= $group['id'] ?>" method="POST" class="app-form-with-bottom-actions" id="groupEditForm">
    <?= csrf_field() ?>

    <!-- 1. Dados do Grupo -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">1</div>
            <div>
                <div class="member-form-card-title">Dados do Grupo</div>
                <div class="member-form-card-subtitle">Identificação, congregação e liderança.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nome do Grupo <span class="required-mark">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($group['name']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Congregação <span class="required-mark">*</span></label>
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
                    <label class="form-label">Líder <span class="required-mark">*</span></label>
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
        </div>
    </div>

    <!-- 2. Encontros -->
    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge">2</div>
            <div>
                <div class="member-form-card-title">Encontros</div>
                <div class="member-form-card-subtitle">Dia, horário, local e anfitrião.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Dia da Reunião <span class="required-mark">*</span></label>
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
                    <label class="form-label">Horário <span class="required-mark">*</span></label>
                    <input type="time" name="meeting_time" class="form-control" value="<?= $group['meeting_time'] ?>" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Endereço <span class="required-mark">*</span></label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($group['address']) ?>" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Anfitrião (Dono da Casa) <span class="required-mark">*</span></label>
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
                    <div class="form-text">Se a pessoa não for membro, basta digitar o nome dela.</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Descrição / Observações</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($group['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/groups/show/<?= $group['id'] ?>" class="btn btn-outline-secondary px-4">Cancelar</a>
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
                <div class="summary-value" id="summaryName"><?= htmlspecialchars($group['name'] ?: '—') ?></div>

                <div class="summary-label">Congregação</div>
                <div class="summary-value" id="summaryCongregation">—</div>

                <div class="summary-label">Líder</div>
                <div class="summary-value" id="summaryLeader">—</div>

                <div class="summary-label">Encontro</div>
                <div class="summary-value mb-2" id="summaryMeeting">—</div>

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

    // Painel de resumo lateral
    const groupForm = document.getElementById('groupEditForm');
    const summaryName = document.getElementById('summaryName');
    const summaryCongregation = document.getElementById('summaryCongregation');
    const summaryLeader = document.getElementById('summaryLeader');
    const summaryMeeting = document.getElementById('summaryMeeting');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');
    const leaderSelect = document.querySelector('select[name="leader_id"]');
    const meetingDaySelect = document.querySelector('select[name="meeting_day"]');
    const meetingTimeInput = document.querySelector('input[name="meeting_time"]');

    function updateGroupSummary() {
        const nameVal = groupForm.querySelector('[name="name"]').value.trim();
        summaryName.textContent = nameVal || '—';

        const congOption = congregationSelect.options[congregationSelect.selectedIndex];
        summaryCongregation.textContent = (congOption && congOption.value) ? congOption.text : '—';

        const leaderOption = leaderSelect.options[leaderSelect.selectedIndex];
        summaryLeader.textContent = (leaderOption && leaderOption.value) ? leaderOption.text.trim() : '—';

        const dayVal = meetingDaySelect.value;
        const timeVal = meetingTimeInput.value;
        const meetingText = [dayVal, timeVal].filter(Boolean).join(' às ');
        summaryMeeting.textContent = meetingText || '—';

        const requiredFields = Array.from(groupForm.querySelectorAll('[required]'));
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    groupForm.addEventListener('input', updateGroupSummary);
    groupForm.addEventListener('change', updateGroupSummary);
    updateGroupSummary();
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

<?php include __DIR__ . '/../../layout/header.php'; ?>

<?php
// Monta o texto de compartilhamento a partir do que já está salvo (o mesmo
// conteúdo que a tela de impressão mostra) — editar e clicar em "Salvar"
// atualiza o texto após o recarregamento da página.
$waLines = [];
$waLines[] = '📋 *' . $schedule['title'] . '*';
if (!empty($schedule['congregation_name'])) {
    $waLines[] = $schedule['congregation_name'];
}
$waLines[] = '';
foreach ($entries as $entry) {
    $waLines[] = date('d/m', strtotime($entry['service_date'])) . ' (' . $entry['weekday'] . ')' . (!empty($entry['service_label']) ? ' - ' . $entry['service_label'] : '');
    foreach ($rolesConfig as $role) {
        $val = trim((string)($entry['values'][$role['key']] ?? ''));
        if ($val !== '') {
            $waLines[] = $role['label'] . ': ' . $val;
        }
    }
    $obs = trim((string)($entry['values']['observacoes'] ?? ''));
    if ($obs !== '') {
        $waLines[] = 'Obs: ' . $obs;
    }
    $waLines[] = '';
}
$whatsappMessage = trim(implode("\n", $waLines));
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0"><?= htmlspecialchars($schedule['title']) ?></h1>
        <div class="text-muted small"><?= htmlspecialchars($schedule['template_name']) ?> &middot; <?= htmlspecialchars($schedule['congregation_name'] ?? 'Todas as congregações') ?></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm btn-success rounded-pill px-3" id="btnShareWhatsapp"><i class="fab fa-whatsapp me-1"></i> WhatsApp</button>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnCopyText"><i class="fas fa-copy me-1"></i> Copiar texto</button>
        <a href="/admin/liturgy-schedules/<?= (int)$schedule['id'] ?>/print" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-print me-1"></i> Imprimir</a>
        <a href="/admin/liturgy-schedules" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<style>
    .ls-table input.form-control { font-size: .82rem; padding: .3rem .5rem; }
    .ls-table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap; }
    .ls-weekday { font-size: .72rem; color: #868e96; }
</style>

<form method="POST" action="/admin/liturgy-schedules/update/<?= (int)$schedule['id'] ?>" id="scheduleForm">
    <?= csrf_field() ?>

    <div class="card shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 ls-table" id="rowsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:130px;">Data</th>
                        <th style="width:140px;">Culto</th>
                        <?php foreach ($rolesConfig as $role): ?>
                            <th><?= htmlspecialchars($role['label']) ?></th>
                        <?php endforeach; ?>
                        <th>Observações</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $i => $entry): ?>
                        <tr>
                            <td>
                                <input type="date" name="rows[<?= $i ?>][service_date]" class="form-control form-control-sm ls-date-input" value="<?= htmlspecialchars($entry['service_date']) ?>" required>
                                <div class="ls-weekday"><?= htmlspecialchars($entry['weekday']) ?></div>
                            </td>
                            <td><input type="text" name="rows[<?= $i ?>][service_label]" class="form-control form-control-sm" value="<?= htmlspecialchars($entry['service_label'] ?? '') ?>" placeholder="Ex: Culto de Família"></td>
                            <?php foreach ($rolesConfig as $role): ?>
                                <td><input type="text" name="rows[<?= $i ?>][values][<?= $role['key'] ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($entry['values'][$role['key']] ?? '') ?>"></td>
                            <?php endforeach; ?>
                            <td><input type="text" name="rows[<?= $i ?>][values][observacoes]" class="form-control form-control-sm" value="<?= htmlspecialchars($entry['values']['observacoes'] ?? '') ?>"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Remover linha"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow"><i class="fas fa-plus me-1"></i> Adicionar linha</button>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Observações gerais (aparece no rodapé da impressão)</label>
        <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($schedule['notes'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar Escala</button>
</form>

<template id="rowTemplate">
    <tr>
        <td>
            <input type="date" name="rows[__INDEX__][service_date]" class="form-control form-control-sm ls-date-input" required>
            <div class="ls-weekday"></div>
        </td>
        <td><input type="text" name="rows[__INDEX__][service_label]" class="form-control form-control-sm" placeholder="Ex: Culto de Família"></td>
        <?php foreach ($rolesConfig as $role): ?>
            <td><input type="text" name="rows[__INDEX__][values][<?= $role['key'] ?>]" class="form-control form-control-sm"></td>
        <?php endforeach; ?>
        <td><input type="text" name="rows[__INDEX__][values][observacoes]" class="form-control form-control-sm"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Remover linha"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
</template>

<script>
(function () {
    var weekdaysPt = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    var nextIndex = <?= count($entries) ?>;
    var tbody = document.querySelector('#rowsTable tbody');
    var template = document.getElementById('rowTemplate');

    function weekdayFromDate(value) {
        if (!value) return '';
        var parts = value.split('-').map(Number);
        var d = new Date(parts[0], parts[1] - 1, parts[2]);
        return weekdaysPt[d.getDay()];
    }

    function wireRow(row) {
        var dateInput = row.querySelector('.ls-date-input');
        var weekdayEl = row.querySelector('.ls-weekday');
        dateInput.addEventListener('change', function () {
            weekdayEl.textContent = weekdayFromDate(dateInput.value);
        });
        row.querySelector('.btn-remove-row').addEventListener('click', function () {
            row.remove();
        });
    }

    document.querySelectorAll('#rowsTable tbody tr').forEach(wireRow);

    document.getElementById('btnAddRow').addEventListener('click', function () {
        var html = template.innerHTML.replace(/__INDEX__/g, nextIndex);
        var wrapper = document.createElement('tbody');
        wrapper.innerHTML = html;
        var row = wrapper.firstElementChild;
        tbody.appendChild(row);
        wireRow(row);
        nextIndex++;
    });

    var whatsappMessage = <?= json_encode($whatsappMessage, JSON_UNESCAPED_UNICODE) ?>;

    document.getElementById('btnShareWhatsapp').addEventListener('click', function () {
        if (whatsappMessage.length > 1800) {
            if (!confirm('Essa escala é longa e o WhatsApp pode cortar o texto em alguns aparelhos. Usar "Copiar texto" ou o link de impressão costuma funcionar melhor nesses casos. Deseja continuar mesmo assim?')) {
                return;
            }
        }
        window.open('https://wa.me/?text=' + encodeURIComponent(whatsappMessage), '_blank');
    });

    document.getElementById('btnCopyText').addEventListener('click', function () {
        navigator.clipboard.writeText(whatsappMessage).then(function () {
            var btn = document.getElementById('btnCopyText');
            var original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Copiado!';
            setTimeout(function () { btn.innerHTML = original; }, 1500);
        });
    });
})();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

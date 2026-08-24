<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contas e Caixas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/bank-accounts/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Nova Conta/Caixa
        </a>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .accounts-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .accounts-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
    }
    .account-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        flex: 0 0 auto;
    }
    .type-pill {
        display: inline-block;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .type-pill.type-caixa { background: #eef0f2; color: #495057; }
    .type-pill.type-conta_corrente { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .type-pill.type-poupanca { background: rgba(13,202,240,0.12); color: #087990; }
    .type-pill.type-investimento { background: rgba(111,66,193,0.10); color: #6f42c1; }
    .type-pill.type-centro_custo { background: rgba(253,126,20,0.12); color: #b8590a; }
    .status-pill {
        display: inline-block;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .status-pill.status-active { background: rgba(25,135,84,0.10); color: #198754; }
    .status-pill.status-inactive { background: #eef0f2; color: #6c757d; }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    .filter-card .form-control {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .filter-card .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php
$accountTypeLabels = [
    'caixa' => 'Caixa Físico',
    'conta_corrente' => 'Conta Corrente',
    'poupanca' => 'Poupança',
    'investimento' => 'Investimento',
    'centro_custo' => 'Centro de Custo'
];
$accountTypeIcons = [
    'caixa' => ['icon' => 'fa-cash-register', 'bg' => 'rgba(108,117,125,0.10)', 'color' => '#495057'],
    'conta_corrente' => ['icon' => 'fa-building-columns', 'bg' => 'rgba(13,110,253,0.10)', 'color' => '#0d6efd'],
    'poupanca' => ['icon' => 'fa-piggy-bank', 'bg' => 'rgba(13,202,240,0.12)', 'color' => '#087990'],
    'investimento' => ['icon' => 'fa-chart-line', 'bg' => 'rgba(111,66,193,0.10)', 'color' => '#6f42c1'],
    'centro_custo' => ['icon' => 'fa-sitemap', 'bg' => 'rgba(253,126,20,0.12)', 'color' => '#b8590a'],
];
?>

<div class="member-form-card filter-card mb-3">
    <div class="p-3">
        <input type="search" class="form-control" id="accountsSearch" placeholder="Pesquisar por nome, banco, tipo..." autocomplete="off">
    </div>
</div>

<div class="d-lg-none">
    <?php if (empty($accounts)): ?>
        <div class="member-form-card">
            <div class="text-center py-5">
                <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Nenhuma conta cadastrada.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="d-grid gap-2">
            <?php foreach ($accounts as $acc): ?>
                <?php $iconInfo = $accountTypeIcons[$acc['type']] ?? $accountTypeIcons['caixa']; ?>
                <div class="member-form-card account-item" data-search="<?= htmlspecialchars(mb_strtolower(($acc['name'] ?? '') . ' ' . ($accountTypeLabels[$acc['type']] ?? '') . ' ' . ($acc['bank_name'] ?? ''), 'UTF-8')) ?>">
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="account-icon" style="background: <?= $iconInfo['bg'] ?>; color: <?= $iconInfo['color'] ?>;">
                                <i class="fas <?= $iconInfo['icon'] ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?= htmlspecialchars($acc['name']) ?></div>
                                <div class="mt-1"><span class="type-pill type-<?= $acc['type'] ?>"><?= htmlspecialchars($accountTypeLabels[$acc['type']] ?? $acc['type']) ?></span></div>
                                <?php if (!in_array($acc['type'], ['caixa', 'centro_custo'], true)): ?>
                                    <div class="small text-muted mt-1"><?= htmlspecialchars($acc['bank_name'] ?? '-') ?> · Ag: <?= htmlspecialchars($acc['agency'] ?? '-') ?> · CC: <?= htmlspecialchars($acc['account_number'] ?? '-') ?></div>
                                <?php endif; ?>
                                <div class="fw-bold mt-2 <?= $acc['current_balance'] < 0 ? 'text-danger' : 'text-success' ?>">R$ <?= number_format($acc['current_balance'], 2, ',', '.') ?></div>
                            </div>
                            <div class="d-flex flex-column gap-2 align-items-end">
                                <span class="status-pill status-<?= $acc['status'] ?>"><?= $acc['status'] === 'active' ? 'Ativa' : 'Inativa' ?></span>
                                <span class="small text-muted">Criado em <?= !empty($acc['created_at']) ? date('d/m/Y H:i', strtotime($acc['created_at'])) : '—' ?></span>
                                <a href="/admin/financial/bank-accounts/edit/<?= $acc['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="member-form-card d-none d-lg-block">
    <div class="table-responsive p-2">
        <table class="table table-hover accounts-table" style="width:100%">
            <thead>
                <tr>
                    <th style="width: 60px;"></th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Banco/Agência/Conta</th>
                    <th>Saldo Atual</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Nenhuma conta cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($accounts as $acc): ?>
                        <?php $iconInfo = $accountTypeIcons[$acc['type']] ?? $accountTypeIcons['caixa']; ?>
                        <tr class="account-item" data-search="<?= htmlspecialchars(mb_strtolower(($acc['name'] ?? '') . ' ' . ($accountTypeLabels[$acc['type']] ?? '') . ' ' . ($acc['bank_name'] ?? ''), 'UTF-8')) ?>">
                            <td><div class="account-icon" style="background: <?= $iconInfo['bg'] ?>; color: <?= $iconInfo['color'] ?>;"><i class="fas <?= $iconInfo['icon'] ?>"></i></div></td>
                            <td class="fw-bold"><?= htmlspecialchars($acc['name']) ?></td>
                            <td><span class="type-pill type-<?= $acc['type'] ?>"><?= htmlspecialchars($accountTypeLabels[$acc['type']] ?? $acc['type']) ?></span></td>
                            <td>
                                <?php if (!in_array($acc['type'], ['caixa', 'centro_custo'], true)): ?>
                                    <?= htmlspecialchars($acc['bank_name'] ?? '-') ?><br>
                                    <small class="text-muted">Ag: <?= htmlspecialchars($acc['agency'] ?? '-') ?> | CC: <?= htmlspecialchars($acc['account_number'] ?? '-') ?></small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold <?= $acc['current_balance'] < 0 ? 'text-danger' : 'text-success' ?>">
                                R$ <?= number_format($acc['current_balance'], 2, ',', '.') ?>
                            </td>
                            <td><span class="status-pill status-<?= $acc['status'] ?>"><?= $acc['status'] === 'active' ? 'Ativa' : 'Inativa' ?></span></td>
                            <td class="small text-muted"><?= !empty($acc['created_at']) ? date('d/m/Y H:i', strtotime($acc['created_at'])) : '—' ?></td>
                            <td class="text-end">
                                <a href="/admin/financial/bank-accounts/edit/<?= $acc['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('accountsSearch');
    if (!input) return;

    function normalize(v) {
        return String(v || '').toLowerCase().trim();
    }

    function filter() {
        var q = normalize(input.value);

        document.querySelectorAll('.account-item').forEach(function (item) {
            var hay = item.getAttribute('data-search') || '';
            item.style.display = q === '' || hay.indexOf(q) !== -1 ? '' : 'none';
        });
    }

    input.addEventListener('input', filter);
    filter();
});
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes do Fechamento</h1>
    <div class="btn-toolbar mb-2 mb-md-0 d-print-none gap-2">
        <a href="/admin/financial/closures" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
        <a href="/admin/financial/closures/print/<?= $closure['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-print me-1"></i> Imprimir
        </a>
        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold px-3 btn-delete-closure" data-id="<?= $closure['id'] ?>" data-period="<?= htmlspecialchars($closure['period']) ?>">
            <i class="fas fa-trash me-1"></i> Excluir
        </button>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .member-form-card-header {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .stat-box {
        border-radius: 14px;
        padding: 1.1rem;
        text-align: center;
        border: 1px solid rgba(0,0,0,0.06);
        height: 100%;
    }
    .stat-box.stat-entries { background: rgba(25,135,84,0.06); }
    .stat-box.stat-expenses { background: rgba(220,53,69,0.06); }
    .stat-box.stat-balance { background: rgba(13,110,253,0.06); }
    .stat-box.stat-final { background: rgba(13,110,253,0.12); border-color: rgba(13,110,253,0.2); }
    .stat-box .stat-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: .3rem;
    }
    .stat-box .stat-value {
        font-size: 1.35rem;
        font-weight: 800;
    }
    .stat-box .stat-sub {
        font-size: .78rem;
        color: #6c757d;
    }
    .report-table thead th {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        background: #fafafa;
    }
    @media print {
        @page { size: A4; margin: 10mm; }
        body { background: white; -webkit-print-color-adjust: exact; }
        .member-form-card { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
        .btn, .d-print-none, nav, footer { display: none !important; }
        .table { width: 100% !important; border-collapse: collapse !important; }
        .table td, .table th { border: 1px solid #ddd !important; padding: 4px !important; }
        h4, h5, h6 { color: #000 !important; }
        .text-success { color: #000 !important; font-weight: bold; }
        .text-danger { color: #000 !important; font-weight: bold; }
        .stat-box { background: #fff !important; border: 1px solid #000 !important; }
    }
</style>

<div class="member-form-card mb-3">
    <div class="member-form-card-header">
        <h5 class="mb-0">Resumo: <?= htmlspecialchars($closure['type']) ?> - <?= htmlspecialchars($closure['period']) ?></h5>
        <small class="text-muted"><?= htmlspecialchars($closure['congregation_name']) ?></small>
    </div>
    <div class="p-4">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="stat-box stat-entries">
                    <div class="stat-label">Entradas</div>
                    <div class="stat-value text-success">R$ <?= number_format($closure['total_entries'], 2, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box stat-expenses">
                    <div class="stat-label">Saídas</div>
                    <div class="stat-value text-danger">R$ <?= number_format($closure['total_expenses'], 2, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box stat-balance">
                    <div class="stat-label">Saldo do Período</div>
                    <div class="stat-value <?= $closure['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                        R$ <?= number_format($closure['balance'], 2, ',', '.') ?>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box stat-final">
                    <div class="stat-label">Saldo Final (Acumulado)</div>
                    <div class="stat-value text-primary">R$ <?= number_format($closure['final_balance'], 2, ',', '.') ?></div>
                    <div class="stat-sub">Anterior: R$ <?= number_format($closure['previous_balance'], 2, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <h6 class="mb-0 fw-bold">Detalhamento Entradas</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm report-table mb-0">
                    <tbody>
                        <tr>
                            <td>Dízimos</td>
                            <td class="text-end">R$ <?= number_format($closure['total_tithes'], 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Ofertas</td>
                            <td class="text-end">R$ <?= number_format($closure['total_offerings'], 2, ',', '.') ?></td>
                        </tr>
                        <tr class="table-light fw-bold">
                            <td>Total</td>
                            <td class="text-end">R$ <?= number_format($closure['total_entries'], 2, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <h6 class="mb-0 fw-bold">Detalhamento Saídas</h6>
            </div>
            <div class="p-3">
                <p class="text-center">
                    Total de Saídas: <strong class="text-danger">R$ <?= number_format($closure['total_expenses'], 2, ',', '.') ?></strong>
                </p>
                <div class="alert alert-info mb-0">
                    Para ver os lançamentos individuais, consulte o relatório financeiro do período <?= date('d/m/Y', strtotime($closure['start_date'])) ?> a <?= date('d/m/Y', strtotime($closure['end_date'])) ?>.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 text-muted text-end">
    <small>Gerado por <?= htmlspecialchars($closure['creator_name']) ?> em <?= date('d/m/Y H:i', strtotime($closure['created_at'])) ?></small>
</div>

<script>
document.querySelectorAll('.btn-delete-closure').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const id = btn.getAttribute('data-id');
        const period = btn.getAttribute('data-period');
        Swal.fire({
            title: 'Excluir fechamento?',
            text: `Tem certeza que deseja excluir o fechamento de "${period}"? Isso reabrirá o período.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/admin/financial/closures/delete/${id}`;
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

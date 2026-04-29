<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Saídas / Despesas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/expenses/create" class="btn btn-sm btn-danger">
            <i class="fas fa-minus-circle"></i> Nova Saída
        </a>
    </div>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card mb-3 bg-light">
    <div class="card-body py-2">
        <form class="row g-2 align-items-end" method="GET" action="/admin/expenses">
            <?php if (empty($_SESSION['user_congregation_id'])): ?>
            <div class="col-md-3">
                <label class="form-label small mb-0">Congregação</label>
                <select name="congregation_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($congregations as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (isset($_GET['congregation_id']) && $_GET['congregation_id'] == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-0">Data Início</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $_GET['start_date'] ?? '' ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-0">Data Fim</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $_GET['end_date'] ?? '' ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-sm btn-secondary w-50" title="Filtrar">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="/admin/expenses" class="btn btn-sm btn-outline-secondary w-50" title="Limpar Filtro">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<?php
// Agrupar Despesas por Congregação
$expensesByCongregation = [];

foreach ($expenses as $e) {
    $congName = $e['congregation_name'] ?? 'Sede';
    if (empty($congName)) $congName = 'Sede'; // Fallback
    
    $expensesByCongregation[$congName][] = $e;
}

// Ordenar para garantir que "Sede" seja a primeira (opcional)
if (isset($expensesByCongregation['Sede'])) {
    $sede = $expensesByCongregation['Sede'];
    unset($expensesByCongregation['Sede']);
    $expensesByCongregation = array_merge(['Sede' => $sede], $expensesByCongregation);
}
$tabTotal = count($expensesByCongregation);
$hasMultipleCongregations = $tabTotal > 1;
?>

<style>
    @media (max-width: 991.98px) {
        .expense-tabs-carousel {
            position: relative;
        }
        .expense-tabs-carousel.multi::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #ff2a7a 0%, #b30000 52%, #d4af37 100%);
            z-index: 2;
        }
        .expense-tabs-carousel.multi #expenseTabsContent {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        .expense-tabs-carousel.multi #expenseTabsContent::-webkit-scrollbar { display: none; }
        .expense-tabs-carousel.multi #expenseTabsContent > .tab-pane {
            display: block !important;
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            opacity: 1 !important;
            padding: .35rem;
        }
        .expense-tabs-carousel.multi #expenseTabsContent > .tab-pane.fade { transition: none; }
        .expense-pane-card {
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            background: #fff;
        }
        .expense-pane-head {
            background: linear-gradient(135deg, rgba(179,0,0,0.10), rgba(212,175,55,0.16));
        }
        .expense-pane-title {
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: .01em;
            color: #2d1a21;
        }
        .expense-pane-hint {
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 800;
            color: rgba(0,0,0,0.52);
            text-transform: uppercase;
        }
        .expense-pane-hint i {
            color: #b30000;
        }
    }
</style>

<ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="expenseTabs" role="tablist">
    <?php 
    $active = true;
    if (empty($expensesByCongregation)) {
        // Se não tiver nada, mostra aba vazia
        echo '<li class="nav-item"><button class="nav-link active" type="button">Lista Vazia</button></li>';
    }
    
    foreach ($expensesByCongregation as $congName => $congExpenses): 
        $slug = md5($congName); // ID seguro para a aba
    ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $active ? 'active text-danger' : 'text-dark' ?>" id="tab-<?= $slug ?>" data-bs-toggle="tab" data-bs-target="#content-<?= $slug ?>" type="button" role="tab">
            <i class="fas fa-church me-2"></i> <?= htmlspecialchars($congName) ?>
            <span class="badge bg-danger ms-1"><?= count($congExpenses) ?></span>
        </button>
    </li>
    <?php 
        $active = false;
    endforeach; 
    ?>
</ul>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<div class="expense-tabs-carousel <?= $hasMultipleCongregations ? 'multi' : '' ?>">
<div class="tab-content" id="expenseTabsContent">
    <?php 
    $active = true;
    $tabStep = 1;
    if (empty($expensesByCongregation)) {
        echo '<div class="tab-pane fade show active p-3 text-center text-muted">Nenhum registro encontrado.</div>';
    }

    foreach ($expensesByCongregation as $congName => $congExpenses): 
        $slug = md5($congName);
    ?>
    <div class="tab-pane fade <?= $active ? 'show active' : '' ?>" id="content-<?= $slug ?>" role="tabpanel">
        <div class="expense-pane-card">
            <div class="d-lg-none px-3 py-3 border-bottom expense-pane-head">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                        <div class="expense-pane-title">
                            <i class="fas fa-church me-2"></i><?= htmlspecialchars($congName) ?>
                        </div>
                        <?php if ($hasMultipleCongregations): ?>
                            <div class="expense-pane-hint mt-1">
                                <i class="fas fa-arrows-left-right me-2"></i>Deslize para mudar (<?= $tabStep ?>/<?= $tabTotal ?>)
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($hasMultipleCongregations): ?>
                        <span class="badge bg-dark"><?= $tabStep ?>/<?= $tabTotal ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="table-responsive p-2">
                <table class="table table-striped table-hover table-sm datatable" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Categoria</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($congExpenses as $e): ?>
                        <tr>
                            <td data-sort="<?= $e['expense_date'] ?>"><?= date('d/m/Y', strtotime($e['expense_date'])) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($e['category']) ?></span>
                                <?php if (isset($e['is_accountable']) && (int)$e['is_accountable'] === 0): ?>
                                    <div><span class="badge bg-secondary mt-1">Não contabilizada</span></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($e['description']) ?></td>
                            <td class="text-danger fw-bold" data-sort="<?= $e['amount'] ?>">- R$ <?= number_format($e['amount'], 2, ',', '.') ?></td>
                            <td class="text-end">
                                <a href="/admin/expenses/edit/<?= $e['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/admin/expenses/delete/<?= $e['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <?php 
        $active = false; // Apenas o primeiro é ativo
        $tabStep++;
    endforeach; 
    ?>
</div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            order: [[0, 'desc']], // Ordenar pela data (coluna 0) decrescente
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            responsive: true,
            paging: true,
            lengthChange: true,
            searching: true,
            info: true,
            pagingType: 'full_numbers'
        });
        
        // Ajustar colunas ao mudar de aba (bug comum do DataTables em abas ocultas)
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            
            // Alterar cor da aba ativa para vermelho (saídas)
            $('#expenseTabs .nav-link').removeClass('text-danger text-dark').addClass('text-dark');
            $(e.target).removeClass('text-dark').addClass('text-danger');
        });

        const carousel = document.querySelector('.expense-tabs-carousel.multi #expenseTabsContent');
        if (carousel) {
            let raf = 0;
            const adjust = () => {
                if (!$.fn.dataTable) return;
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            };
            carousel.addEventListener('scroll', function () {
                if (raf) return;
                raf = requestAnimationFrame(function () {
                    raf = 0;
                    adjust();
                });
            }, { passive: true });
            window.addEventListener('resize', adjust);
        }
    });
</script>

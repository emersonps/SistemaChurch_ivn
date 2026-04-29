<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dízimos e Ofertas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/tithes/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Novo Lançamento
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
        <form class="row g-2 align-items-end" method="GET" action="/admin/tithes">
            <!-- Filtros (mantidos) -->
            <div class="col-md-3">
                <label class="form-label small mb-0">Congregação</label>
                <select name="congregation_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($congregations as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= isset($_GET['congregation_id']) && $_GET['congregation_id'] == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">Membro</label>
                <input type="text" name="member_name" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['member_name'] ?? '') ?>" placeholder="Nome...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-0">Data Início</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $_GET['start_date'] ?? '' ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-0">Data Fim</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $_GET['end_date'] ?? '' ?>">
            </div>
             <div class="col-md-1">
                <label class="form-label small mb-0">Tipo</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Dízimo" <?= (isset($_GET['type']) && $_GET['type'] == 'Dízimo') ? 'selected' : '' ?>>Dízimo</option>
                    <option value="Oferta" <?= (isset($_GET['type']) && $_GET['type'] == 'Oferta') ? 'selected' : '' ?>>Oferta</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-sm btn-secondary w-50" title="Filtrar">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="/admin/tithes" class="btn btn-sm btn-outline-secondary w-50" title="Limpar Filtro">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<?php
// Agrupar Dízimos por Congregação
$tithesByCongregation = [];

foreach ($tithes as $t) {
    $congName = $t['congregation_name'] ?? 'Sede';
    if (empty($congName)) $congName = 'Sede'; // Fallback
    
    $tithesByCongregation[$congName][] = $t;
}

// Ordenar para garantir que "Sede" seja a primeira (opcional)
if (isset($tithesByCongregation['Sede'])) {
    $sede = $tithesByCongregation['Sede'];
    unset($tithesByCongregation['Sede']);
    $tithesByCongregation = array_merge(['Sede' => $sede], $tithesByCongregation);
}
$tabTotal = count($tithesByCongregation);
$hasMultipleCongregations = $tabTotal > 1;
?>

<style>
    @media (max-width: 991.98px) {
        .tithe-tabs-carousel {
            position: relative;
        }
        .tithe-tabs-carousel.multi::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #0d6efd 0%, #198754 55%, #d4af37 100%);
            z-index: 2;
        }
        .tithe-tabs-carousel.multi #titheTabsContent {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        .tithe-tabs-carousel.multi #titheTabsContent::-webkit-scrollbar { display: none; }
        .tithe-tabs-carousel.multi #titheTabsContent > .tab-pane {
            display: block !important;
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            opacity: 1 !important;
            padding: .35rem;
        }
        .tithe-tabs-carousel.multi #titheTabsContent > .tab-pane.fade { transition: none; }
        .tithe-pane-card {
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            background: #fff;
        }
        .tithe-pane-head {
            background: linear-gradient(135deg, rgba(13,110,253,0.12), rgba(25,135,84,0.14));
        }
        .tithe-pane-title {
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: .01em;
            color: #0b2a1b;
        }
        .tithe-pane-hint {
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 800;
            color: rgba(0,0,0,0.52);
            text-transform: uppercase;
        }
        .tithe-pane-hint i {
            color: #198754;
        }
    }
</style>

<ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="titheTabs" role="tablist">
    <?php 
    $active = true;
    if (empty($tithesByCongregation)) {
        // Se não tiver nada, mostra aba vazia
        echo '<li class="nav-item"><button class="nav-link active" type="button">Lista Vazia</button></li>';
    }
    
    foreach ($tithesByCongregation as $congName => $congTithes): 
        $slug = md5($congName); // ID seguro para a aba
    ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $active ? 'active' : '' ?>" id="tab-<?= $slug ?>" data-bs-toggle="tab" data-bs-target="#content-<?= $slug ?>" type="button" role="tab">
            <i class="fas fa-church me-2"></i> <?= htmlspecialchars($congName) ?>
            <span class="badge bg-secondary ms-1"><?= count($congTithes) ?></span>
        </button>
    </li>
    <?php 
        $active = false;
    endforeach; 
    ?>
</ul>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<div class="tithe-tabs-carousel <?= $hasMultipleCongregations ? 'multi' : '' ?>">
<div class="tab-content" id="titheTabsContent">
    <?php 
    $active = true;
    $tabStep = 1;
    if (empty($tithesByCongregation)) {
        echo '<div class="tab-pane fade show active p-3 text-center text-muted">Nenhum registro encontrado.</div>';
    }

    foreach ($tithesByCongregation as $congName => $congTithes): 
        $slug = md5($congName);
    ?>
    <div class="tab-pane fade <?= $active ? 'show active' : '' ?>" id="content-<?= $slug ?>" role="tabpanel">
        <div class="tithe-pane-card">
            <div class="d-lg-none px-3 py-3 border-bottom tithe-pane-head">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                        <div class="tithe-pane-title">
                            <i class="fas fa-church me-2"></i><?= htmlspecialchars($congName) ?>
                        </div>
                        <?php if ($hasMultipleCongregations): ?>
                            <div class="tithe-pane-hint mt-1">
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
                        <th>Membro/Doador</th>
                        <th class="d-none d-md-table-cell">Tipo</th>
                        <th>Valor</th>
                        <th class="d-none d-md-table-cell">Método</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($congTithes as $t): ?>
                    <tr>
                        <td data-sort="<?= $t['payment_date'] ?>"><?= date('d/m/Y', strtotime($t['payment_date'])) ?></td>
                        <td>
                            <div class="d-flex flex-column">
                                <?php 
                                    if (!empty($t['member_name'])) {
                                        echo '<span class="fw-bold">' . htmlspecialchars((string)$t['member_name']) . '</span>';
                                    } elseif (!empty($t['giver_name'])) {
                                        echo '<span class="fw-bold">' . htmlspecialchars((string)$t['giver_name']) . '</span> <small class="text-muted">(Visitante)</small>';
                                    } else {
                                        if ($t['payment_method'] === 'Transferência/OFX' && !empty($t['notes'])) {
                                            echo '<span class="text-secondary fw-bold">OFX:</span> <span class="text-muted">' . htmlspecialchars((string)$t['notes']) . '</span>';
                                        } elseif (!empty($t['notes'])) {
                                            echo '<span class="text-secondary fw-bold">Obs:</span> <span class="text-muted">' . htmlspecialchars((string)mb_strimwidth($t['notes'], 0, 30, '...')) . '</span>';
                                        } else {
                                            echo '<span class="text-muted fst-italic">Não identificado</span>';
                                        }
                                    }
                                ?>
                                <small class="d-md-none text-muted">
                                    <?= htmlspecialchars($t['type'] ?? 'Dízimo') ?> - <?= ucfirst($t['payment_method']) ?>
                                </small>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <span class="badge bg-<?= ($t['type'] ?? 'Dízimo') == 'Dízimo' ? 'primary' : 'success' ?>"><?= htmlspecialchars($t['type'] ?? 'Dízimo') ?></span>
                            <?php if (isset($t['is_accountable']) && (int)$t['is_accountable'] === 0): ?>
                                <div><span class="badge bg-secondary mt-1">Não contabilizada</span></div>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold text-success" data-sort="<?= $t['amount'] ?>">R$ <?= number_format($t['amount'], 2, ',', '.') ?></td>
                        <td class="d-none d-md-table-cell"><?= ucfirst($t['payment_method']) ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#receiptModal" data-url="/admin/tithes/receipt/<?= $t['id'] ?>" title="Recibo"><i class="fas fa-print"></i></button>
                            <a href="/admin/tithes/edit/<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="/admin/tithes/delete/<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este lançamento? Esta ação não pode ser desfeita.')"><i class="fas fa-trash"></i></a>
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

<!-- Modal do Recibo -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="receiptModalLabel">Recibo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="receiptIframe" src="" style="width: 100%; height: 500px; border: none;"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('receiptIframe').contentWindow.print();">
            <i class="fas fa-print me-1"></i> Imprimir Recibo
        </button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var receiptModal = document.getElementById('receiptModal');
        if (receiptModal) {
            receiptModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var url = button.getAttribute('data-url');
                var iframe = document.getElementById('receiptIframe');
                iframe.src = url;
            });
            
            // Limpar iframe ao fechar para não ficar carregado
            receiptModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('receiptIframe').src = '';
            });
        }
    });

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
        });

        const carousel = document.querySelector('.tithe-tabs-carousel.multi #titheTabsContent');
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

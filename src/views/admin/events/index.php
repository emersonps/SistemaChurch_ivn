<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Eventos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/attendance" class="btn btn-sm btn-outline-dark me-2">
            <i class="fas fa-list-check me-1"></i> Controle de Presença
        </a>
        <a href="/admin/events/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Novo Evento
        </a>
    </div>
</div>

<?php
// Group events by type
$groupedEvents = [
    'culto' => [],
    'evento' => [],
    'convite' => [],
    'interno' => []
];

foreach ($events as $e) {
    $type = strtolower($e['type'] ?? '');
    
    // Agrupar categorias
    if ($type === 'culto') {
        $groupedEvents['culto'][] = $e;
    } elseif ($type === 'interno') {
        $groupedEvents['interno'][] = $e;
    } elseif (strpos($type, 'convite') !== false) {
        $groupedEvents['convite'][] = $e;
    } else {
        $groupedEvents['evento'][] = $e;
    }
}

$categories = [
    'culto' => 'Cultos',
    'evento' => 'Eventos',
    'convite' => 'Convites Especiais',
    'interno' => 'Internos'
];
$tabTotal = count($categories);
$hasMultipleCategories = $tabTotal > 1;
$now = new DateTimeImmutable('now');
?>

<style>
    @media (max-width: 991.98px) {
        .event-tabs-carousel {
            position: relative;
        }
        .event-tabs-carousel.multi::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #0d6efd 0%, #6f42c1 55%, #d4af37 100%);
            z-index: 2;
        }
        .event-tabs-carousel.multi #eventTabsContent {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        .event-tabs-carousel.multi #eventTabsContent::-webkit-scrollbar { display: none; }
        .event-tabs-carousel.multi #eventTabsContent > .tab-pane {
            display: block !important;
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            opacity: 1 !important;
            padding: .35rem;
        }
        .event-tabs-carousel.multi #eventTabsContent > .tab-pane.fade { transition: none; }
        .event-pane-card {
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            background: #fff;
        }
        .event-pane-head {
            background: linear-gradient(135deg, rgba(13,110,253,0.10), rgba(111,66,193,0.14));
        }
        .event-pane-title {
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: .01em;
            color: #1b1b2a;
        }
        .event-pane-hint {
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 800;
            color: rgba(0,0,0,0.52);
            text-transform: uppercase;
        }
        .event-pane-hint i {
            color: #6f42c1;
        }
    }
</style>

<ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="eventTabs" role="tablist">
    <?php 
    $first = true; 
    foreach ($categories as $key => $label): 
        // Create safe ID for tab
        $tabId = 'tab-' . $key;
    ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $first ? 'active' : '' ?>" id="<?= $tabId ?>-btn" data-bs-toggle="tab" data-bs-target="#<?= $tabId ?>" type="button" role="tab" aria-controls="<?= $tabId ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                <?= $label ?> 
                <span class="badge bg-secondary ms-1"><?= count($groupedEvents[$key]) ?></span>
            </button>
        </li>
    <?php 
        $first = false; 
    endforeach; 
    ?>
</ul>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<div class="event-tabs-carousel <?= $hasMultipleCategories ? 'multi' : '' ?>">
<div class="tab-content" id="eventTabsContent">
    <?php 
    $first = true; 
    $tabStep = 1;
    foreach ($categories as $key => $label): 
        $tabId = 'tab-' . $key;
    ?>
        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= $tabId ?>" role="tabpanel" aria-labelledby="<?= $tabId ?>-btn">
            <div class="event-pane-card">
                <div class="d-lg-none px-3 py-3 border-bottom event-pane-head">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <div class="event-pane-title">
                                <i class="fas fa-calendar-alt me-2"></i><?= htmlspecialchars($label) ?>
                            </div>
                            <?php if ($hasMultipleCategories): ?>
                                <div class="event-pane-hint mt-1">
                                    <i class="fas fa-arrows-left-right me-2"></i>Deslize para mudar (<?= $tabStep ?>/<?= $tabTotal ?>)
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($hasMultipleCategories): ?>
                            <span class="badge bg-dark"><?= $tabStep ?>/<?= $tabTotal ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-responsive p-2">
                    <table class="table table-striped table-hover table-sm datatable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Data/Recorrência</th>
                            <th>Título</th>
                            <th>Local</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groupedEvents[$key] as $e): ?>
                            <tr>
                                <td>
                                    <?php
                                    $dateBadges = eventGetDateBadges($e);
                                    $next = eventNextOccurrence($e, $now);
                                    $sortKey = $next ? $next->format('Y-m-d H:i:s') : (($dateBadges[0]['raw'] ?? '') !== '' ? date('Y-m-d H:i:s', strtotime($dateBadges[0]['raw'])) : '9999-99-99 99:99:99');
                                    echo '<span class="d-none">' . htmlspecialchars($sortKey) . '</span>';

                                    if (empty($dateBadges)) {
                                        echo 'Indefinido';
                                    } else {
                                        $primary = $next ? $next->format('d/m/Y H:i') : ($dateBadges[0]['date'] . ' ' . $dateBadges[0]['time']);
                                        echo '<div class="fw-bold">' . htmlspecialchars($primary) . '</div>';
                                        if (count($dateBadges) > 1) {
                                            echo '<div class="small text-muted">+ ' . (count($dateBadges) - 1) . ' datas</div>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($e['banner_path'])): ?>
                                        <i class="fas fa-image text-primary me-1" title="Possui Banner"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($e['title']) ?>
                                </td>
                                <td><?= htmlspecialchars($e['location']) ?></td>
                                <td>
                                    <span class="badge bg-<?= ($e['status'] ?? 'active') == 'active' ? 'success' : 'secondary' ?>">
                                        <?= (($e['status'] ?? 'active') == 'active') ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="/admin/events/edit/<?= $e['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (
                                        strtolower($e['type'] ?? '') === 'culto' 
                                        || (!empty($e['recurring_days']))
                                        || eventHasFutureOccurrence($e, $now)
                                    ): ?>
                                    <a href="/admin/events/toggle/<?= $e['id'] ?>" class="btn btn-sm btn-outline-<?= ($e['status'] ?? 'active') == 'active' ? 'warning' : 'success' ?>" title="<?= ($e['status'] ?? 'active') == 'active' ? 'Desativar' : 'Ativar' ?>">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary disabled" title="Evento Finalizado" disabled>
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                    <?php endif; ?>
                                    <a href="/admin/events/delete/<?= $e['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir?')" title="Excluir">
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
    <?php $first = false; $tabStep++; endforeach; ?>
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
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json',
                search: '',
                searchPlaceholder: 'Pesquisar...'
            },
            order: [[0, 'asc']], // Ordenar pela data
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            responsive: true,
            paging: true,
            lengthChange: true,
            searching: true,
            info: true,
            pagingType: 'full_numbers',
            columnDefs: [
                { orderable: false, targets: [4] } // Não ordenar ações
            ]
        });
        
        // Ajustar colunas ao mudar de aba e salvar estado
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            
            // Salvar aba ativa no localStorage
            var target = $(e.target).attr("data-bs-target"); // ex: #nav-evento
            localStorage.setItem('activeEventTab', target);
        });
        
        // Restaurar aba ativa ao carregar a página
        var activeTab = localStorage.getItem('activeEventTab');
        if (activeTab) {
            var tabTrigger = document.querySelector('button[data-bs-target="' + activeTab + '"]');
            if (tabTrigger) {
                var tab = new bootstrap.Tab(tabTrigger);
                tab.show();
            }
        }

        const carousel = document.querySelector('.event-tabs-carousel.multi #eventTabsContent');
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

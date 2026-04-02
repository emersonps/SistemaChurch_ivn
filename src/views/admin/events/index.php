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
?>

<ul class="nav nav-tabs mb-3" id="eventTabs" role="tablist">
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

<div class="tab-content" id="eventTabsContent">
    <?php 
    $first = true; 
    foreach ($categories as $key => $label): 
        $tabId = 'tab-' . $key;
    ?>
        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= $tabId ?>" role="tabpanel" aria-labelledby="<?= $tabId ?>-btn">
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
                                        if (!empty($e['event_date']) && strpos($e['event_date'], '1970-01-01') === false) {
                                            // Data normal
                                            echo '<span class="d-none">' . $e['event_date'] . '</span>'; // Para ordenação correta
                                            echo date('d/m/Y', strtotime($e['event_date']));
                                            if (!empty($e['event_date']) && strpos($e['event_date'], ':') !== false) {
                                                echo ' ' . date('H:i', strtotime($e['event_date']));
                                            }
                                        } elseif (!empty($e['recurring_days'])) {
                                            // Recorrente
                                            $days = json_decode($e['recurring_days'], true);
                                            echo '<span class="d-none">9999-99-99</span>'; // Para jogar recorrentes para o fim ou topo
                                            echo '<span class="badge bg-info text-dark">' . implode(', ', $days) . '</span>';
                                            if (!empty($e['event_date']) && strpos($e['event_date'], ':') !== false) {
                                                echo ' ' . date('H:i', strtotime($e['event_date']));
                                            }
                                        } else {
                                            echo '<span class="d-none">9999-99-99</span>Indefinido';
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
                                        || (strtotime($e['event_date']) >= strtotime('today'))
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
    <?php $first = false; endforeach; ?>
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
    });
</script>

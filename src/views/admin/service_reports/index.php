<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatórios de Culto</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/service_reports/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Novo Relatório
        </a>
    </div>
</div>

<?php
// Agrupar relatórios por congregação
$groupedReports = [];
foreach ($reports as $r) {
    $congregationName = $r['congregation_name'] ?? 'Sem Congregação';
    if (!isset($groupedReports[$congregationName])) {
        $groupedReports[$congregationName] = [];
    }
    $groupedReports[$congregationName][] = $r;
}
ksort($groupedReports);
$tabTotal = count($groupedReports);
$hasMultipleCongregations = $tabTotal > 1;
?>

<style>
    @media (max-width: 991.98px) {
        .service-report-tabs-carousel {
            position: relative;
        }
        .service-report-tabs-carousel.multi::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #0dcaf0 0%, #0d6efd 55%, #d4af37 100%);
            z-index: 2;
        }
        .service-report-tabs-carousel.multi #reportTabsContent {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        .service-report-tabs-carousel.multi #reportTabsContent::-webkit-scrollbar { display: none; }
        .service-report-tabs-carousel.multi #reportTabsContent > .tab-pane {
            display: block !important;
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            opacity: 1 !important;
            padding: .35rem;
        }
        .service-report-tabs-carousel.multi #reportTabsContent > .tab-pane.fade { transition: none; }
        .service-report-pane-card {
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            background: #fff;
        }
        .service-report-pane-head {
            background: linear-gradient(135deg, rgba(13,202,240,0.16), rgba(13,110,253,0.10));
        }
        .service-report-pane-title {
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: .01em;
            color: #0d2b3a;
        }
        .service-report-pane-hint {
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 800;
            color: rgba(0,0,0,0.52);
            text-transform: uppercase;
        }
        .service-report-pane-hint i {
            color: #0d6efd;
        }
    }
</style>

<ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="reportTabs" role="tablist">
    <?php $first = true; foreach ($groupedReports as $congregationName => $items): 
        $tabId = 'tab-' . md5($congregationName);
    ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $first ? 'active' : '' ?>" id="<?= $tabId ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= $tabId ?>" type="button" role="tab" aria-controls="<?= $tabId ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                <?= htmlspecialchars($congregationName) ?> 
                <span class="badge bg-secondary ms-1"><?= count($items) ?></span>
            </button>
        </li>
    <?php $first = false; endforeach; ?>
</ul>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<div class="service-report-tabs-carousel <?= $hasMultipleCongregations ? 'multi' : '' ?>">
<div class="tab-content" id="reportTabsContent">
    <?php $first = true; $tabStep = 1; foreach ($groupedReports as $congregationName => $items): 
        $tabId = 'tab-' . md5($congregationName);
    ?>
        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= $tabId ?>" role="tabpanel" aria-labelledby="<?= $tabId ?>-tab">
            <div class="service-report-pane-card">
                <div class="d-lg-none px-3 py-3 border-bottom service-report-pane-head">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <div class="service-report-pane-title">
                                <i class="fas fa-church me-2"></i><?= htmlspecialchars($congregationName) ?>
                            </div>
                            <?php if ($hasMultipleCongregations): ?>
                                <div class="service-report-pane-hint mt-1">
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
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Dia/Hora</th>
                            <th>Dirigente</th>
                            <th>Total Pessoas</th>
                            <th>Criado por</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $r): ?>
                            <tr>
                                <td class="align-middle fw-bold" data-sort="<?= $r['date'] ?>"><?= date('d/m/Y', strtotime($r['date'])) ?></td>
                                <td class="align-middle"><?= date('H:i', strtotime($r['time'])) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($r['leader_name']) ?></td>
                                <td class="align-middle text-center"><span class="badge bg-info text-dark"><?= $r['total_attendance'] ?></span></td>
                                <td class="align-middle small text-muted"><?= htmlspecialchars($r['creator_name']) ?></td>
                                <td class="align-middle text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="showVisitors(<?= $r['id'] ?>)" title="Ver Visitantes">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <a href="/admin/service_reports/show/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/service_reports/edit/<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/admin/service_reports/delete/<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este relatório? Todas as ofertas e registros associados serão removidos.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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

<?php if (empty($groupedReports)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle me-2"></i> Nenhum relatório de culto registrado.
    </div>
<?php endif; ?>

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
            pagingType: 'full_numbers',
            columnDefs: [
                { orderable: false, targets: [5] } // Não ordenar ações
            ]
        });
        
        // Ajustar colunas ao mudar de aba
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        const carousel = document.querySelector('.service-report-tabs-carousel.multi #reportTabsContent');
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
<!-- Visitors Modal -->
<div class="modal fade" id="visitorsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Visitantes do Culto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div id="visitorsList" class="list-group">
                    <!-- Loaded via JS -->
                    <div class="text-center text-muted py-3">Carregando...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
function showVisitors(reportId) {
    const modal = new bootstrap.Modal(document.getElementById('visitorsModal'));
    const listContainer = document.getElementById('visitorsList');
    
    listContainer.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>';
    modal.show();
    
    fetch(`/admin/service_reports/visitors/${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                listContainer.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            
            if (data.length === 0) {
                listContainer.innerHTML = '<div class="text-center text-muted py-3">Nenhum visitante registrado neste relatório.</div>';
                return;
            }
            
            let html = '';
            data.forEach(v => {
                let name = v.name !== null ? v.name : '';
                let obs = v.observation !== null ? v.observation : '';
                
                // Escape HTML para segurança
                const escapeHtml = (unsafe) => {
                    return (unsafe || '').toString()
                         .replace(/&/g, "&amp;")
                         .replace(/</g, "&lt;")
                         .replace(/>/g, "&gt;")
                         .replace(/"/g, "&quot;")
                         .replace(/'/g, "&#039;");
                };

                html += `
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${escapeHtml(name)}</h6>
                        </div>
                        ${obs ? `<small class="text-muted">${escapeHtml(obs)}</small>` : ''}
                    </div>
                `;
            });
            listContainer.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            listContainer.innerHTML = '<div class="alert alert-danger">Erro ao carregar visitantes.</div>';
        });
}
</script>

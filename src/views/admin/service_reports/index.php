<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatórios de Culto</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/service_reports/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Novo Relatório
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
    #reportTabs.nav-tabs {
        border-bottom: none;
        gap: .4rem;
    }
    #reportTabs.nav-tabs .nav-link {
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 999px;
        padding: .45rem 1rem;
        font-weight: 700;
        font-size: .85rem;
        color: #495057;
        background: #fff;
    }
    #reportTabs.nav-tabs .nav-link:hover {
        border-color: rgba(179,0,0,0.3);
        color: #b30000;
        isolation: isolate;
    }
    #reportTabs.nav-tabs .nav-link.active {
        background: #b30000;
        border-color: #b30000;
        color: #fff;
    }
    #reportTabs.nav-tabs .nav-link .badge {
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }
    #reportTabs.nav-tabs .nav-link.active .badge {
        background: rgba(255,255,255,0.25);
        color: #fff;
    }

    .service-report-pane-card {
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.08);
        overflow: hidden;
        background: #fff;
    }
    .service-report-pane-head { background: #fafafa; }
    .service-report-pane-title {
        font-weight: 800;
        font-size: 1.05rem;
        letter-spacing: .01em;
        color: #1a1a1a;
    }
    .service-report-pane-hint {
        font-size: .72rem;
        letter-spacing: .06em;
        font-weight: 700;
        color: rgba(0,0,0,0.4);
        text-transform: uppercase;
    }
    .service-report-pane-hint i { color: #b30000; }

    .reports-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .reports-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
    }
    .count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .7rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    .modal-content { border-radius: 16px; border: none; }
    .modal-header { border-bottom: 1px solid rgba(0,0,0,0.07); }

    .table-responsive {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .table-responsive::-webkit-scrollbar {
        display: none;
    }

    @media (max-width: 991.98px) {
        .service-report-tabs-carousel {
            position: relative;
        }
        .service-report-tabs-carousel.multi::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #ff2a7a 0%, #b30000 52%, #d4af37 100%);
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
    }
</style>

<ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="reportTabs" role="tablist">
    <?php $first = true; foreach ($groupedReports as $congregationName => $items):
        $tabId = 'tab-' . md5($congregationName);
    ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $first ? 'active' : '' ?>" id="<?= $tabId ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= $tabId ?>" type="button" role="tab" aria-controls="<?= $tabId ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                <?= htmlspecialchars($congregationName) ?>
                <span class="badge ms-1"><?= count($items) ?></span>
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
                    <table class="table table-hover reports-table datatable" style="width:100%">
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
                                <td class="fw-bold" data-sort="<?= $r['date'] ?>"><?= date('d/m/Y', strtotime($r['date'])) ?></td>
                                <td><?= date('H:i', strtotime($r['time'])) ?></td>
                                <td><?= htmlspecialchars($r['leader_name']) ?></td>
                                <td><span class="count-pill"><i class="fas fa-users"></i> <?= $r['total_attendance'] ?></span></td>
                                <td class="small text-muted"><?= htmlspecialchars($r['creator_name']) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info icon-btn" onclick="showVisitors(<?= $r['id'] ?>)" title="Ver Visitantes">
                                        <i class="fas fa-users"></i>
                                    </button>
                                    <a href="/admin/service_reports/show/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/service_reports/edit/<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/service_reports/delete/<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-report" title="Excluir">
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

        // Confirmação de exclusão via SweetAlert (substitui confirm() nativo)
        $(document).on('click', '.btn-delete-report', function (e) {
            e.preventDefault();
            const href = $(this).attr('href');
            Swal.fire({
                title: 'Excluir relatório?',
                text: 'Todas as ofertas e registros associados serão removidos. Esta ação não pode ser desfeita.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
</script>
<!-- Visitors Modal -->
<div class="modal fade" id="visitorsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Visitantes do Culto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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

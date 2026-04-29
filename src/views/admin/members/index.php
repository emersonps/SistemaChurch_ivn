<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Membros</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <?php if (hasPermission('members.manage')): ?>
        <a href="/admin/members/import" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-file-import"></i> Importar Planilha
        </a>
        <?php endif; ?>
        <a href="/admin/members/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Novo Membro
        </a>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <?= $_SESSION['flash_success'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_GET['warning'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= htmlspecialchars($_GET['warning']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php
// Agrupar membros por congregação
$groupedMembers = [];
foreach ($members as $member) {
    $congregationName = $member['congregation_name'] ?? 'Sem Congregação';
    if (!isset($groupedMembers[$congregationName])) {
        $groupedMembers[$congregationName] = [];
    }
    $groupedMembers[$congregationName][] = $member;
}
ksort($groupedMembers); // Ordenar abas alfabeticamente
$hasMultipleCongregations = count($groupedMembers) > 1;
?>

<style>
    @media (max-width: 991.98px) {
        .member-tabs-carousel {
            position: relative;
        }
        .member-tabs-carousel.multi::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #ff2a7a 0%, #b30000 52%, #d4af37 100%);
            z-index: 2;
        }
        .member-tabs-carousel.multi #memberTabsContent {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        .member-tabs-carousel.multi #memberTabsContent::-webkit-scrollbar { display: none; }
        .member-tabs-carousel.multi #memberTabsContent > .tab-pane {
            display: block !important;
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            opacity: 1 !important;
            padding: .35rem;
        }
        .member-tabs-carousel.multi #memberTabsContent > .tab-pane.fade { transition: none; }
        .member-pane-card {
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            background: #fff;
        }
        .member-pane-head {
            background: linear-gradient(135deg, rgba(179,0,0,0.10), rgba(212,175,55,0.16));
        }
        .member-pane-title {
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: .01em;
            color: #2d1a21;
        }
        .member-pane-hint {
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 800;
            color: rgba(0,0,0,0.52);
            text-transform: uppercase;
        }
        .member-pane-hint i {
            color: #b30000;
        }
        .dataTables_wrapper .dataTables_filter {
            width: 100%;
            text-align: left;
            margin: .25rem 0 .5rem;
        }
        .dataTables_wrapper .dataTables_filter label {
            width: 100%;
            margin: 0;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
        }
    }
</style>

<ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="memberTabs" role="tablist">
    <?php $first = true; foreach ($groupedMembers as $congregationName => $congregationMembers): 
        $tabId = 'tab-' . md5($congregationName);
    ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $first ? 'active' : '' ?>" id="<?= $tabId ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= $tabId ?>" type="button" role="tab" aria-controls="<?= $tabId ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                <?= htmlspecialchars($congregationName) ?> 
                <span class="badge bg-secondary ms-1"><?= count($congregationMembers) ?></span>
            </button>
        </li>
    <?php $first = false; endforeach; ?>
</ul>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<div class="member-tabs-carousel <?= $hasMultipleCongregations ? 'multi' : '' ?>">
<div class="tab-content" id="memberTabsContent">
    <?php 
    $first = true;
    // Agrupar lógica de exibição aqui dentro
    foreach ($groupedMembers as $congregationName => $congregationMembers): 
        $tabId = 'tab-' . md5($congregationName);
    ?>
    <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= $tabId ?>" role="tabpanel" aria-labelledby="<?= $tabId ?>-tab">
        <div class="member-pane-card">
            <div class="d-lg-none px-3 py-3 border-bottom member-pane-head">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                        <div class="member-pane-title">
                            <i class="fas fa-church me-2"></i><?= htmlspecialchars($congregationName) ?>
                        </div>
                        <?php if ($hasMultipleCongregations): ?>
                            <div class="member-pane-hint mt-1">
                                <i class="fas fa-arrows-left-right me-2"></i>Deslize aqui para mudar
                            </div>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-dark"><?= count($congregationMembers) ?></span>
                </div>
            </div>
        <div class="table-responsive p-2">
            <table class="table table-striped table-hover table-sm datatable" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">Foto</th>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th class="d-none d-md-table-cell">Telefone</th>
                        <th class="d-none d-md-table-cell">Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($congregationMembers as $member): ?>
                    <tr>
                        <td class="align-middle">
                            <?php if (!empty($member['photo'])): ?>
                                <img src="/uploads/members/<?= $member['photo'] ?>" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white small shadow-sm" style="width: 40px; height: 40px;">
                                    <?= strtoupper(substr($member['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="align-middle fw-bold">
                            <?= htmlspecialchars($member['name']) ?>
                            <?php if (!empty($member['is_leader'])): ?>
                                <span class="badge bg-warning text-dark ms-1">Dirigente</span>
                            <?php endif; ?>
                        </td>
                        <td class="align-middle"><span class="badge bg-light text-dark border"><?= htmlspecialchars($member['role'] ?? 'Membro') ?></span></td>
                        <td class="align-middle d-none d-md-table-cell"><?= htmlspecialchars($member['phone'] ?? '-') ?></td>
                        <td class="align-middle d-none d-md-table-cell">
                            <?php 
                                $status = $member['status'] ?? 'active';
                                // Como antes era salvo o texto livre, vamos checar se é 'active', 'Congregando' ou outros.
                                // Tudo que não for Congregando/active será considerado inativo.
                                $isActive = ($status === 'active' || strtolower(trim($status)) === 'congregando');
                                
                                if ($isActive) {
                                    $statusLabel = 'Ativo(Congregando)';
                                } else {
                                    $statusLabel = 'Inativo(Desligado/Saiu)';
                                }
                            ?>
                            <span class="badge bg-<?= $isActive ? 'success' : 'secondary' ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        <td class="align-middle text-end">
                            <a href="/admin/members/show/<?= $member['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ficha">
                                <i class="fas fa-user me-1"></i> Ficha
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
        $first = false; 
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
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json',
                search: '',
                    searchPlaceholder: 'Pesquisar...'
            },
            order: [[1, 'asc']], // Ordenar pelo Nome (coluna 1)
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            responsive: true,
            paging: true,
            lengthChange: true,
            searching: true,
            info: true,
            pagingType: 'full_numbers',
            columnDefs: [
                { orderable: false, targets: [0, 5] } // Não ordenar por Foto (0) e Ações (5)
            ]
        });

        $('.dataTables_wrapper .dataTables_filter input')
            .addClass('form-control-lg border-primary shadow-sm')
            .attr('aria-label', 'Buscar por registros');
        
        // Ajustar colunas ao mudar de aba
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    });
</script>

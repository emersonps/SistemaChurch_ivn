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
?>

<ul class="nav nav-tabs mb-3" id="memberTabs" role="tablist">
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

<div class="tab-content" id="memberTabsContent">
    <?php 
    $first = true;
    // Agrupar lógica de exibição aqui dentro
    foreach ($groupedMembers as $congregationName => $congregationMembers): 
        $tabId = 'tab-' . md5($congregationName);
    ?>
    <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= $tabId ?>" role="tabpanel" aria-labelledby="<?= $tabId ?>-tab">
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
    <?php 
        $first = false; 
    endforeach; 
    ?>
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
        
        // Ajustar colunas ao mudar de aba
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    });
</script>

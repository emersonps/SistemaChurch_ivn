<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Estudos Bíblicos e Esboços</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/studies/create" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus"></i> Novo Estudo
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Operação realizada com sucesso.</div>
<?php endif; ?>

<?php $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin'; ?>
<?php $userId = $_SESSION['user_id'] ?? null; ?>
<?php $canManagePermission = function_exists('hasPermission') ? hasPermission('studies.manage') : false; ?>

<style>
    .study-desc-table {
        display: inline-block;
        max-width: 340px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: bottom;
    }
    .study-cover {
        width: 54px;
        height: 72px;
        border-radius: 10px;
        object-fit: contain;
        border: 1px solid rgba(0,0,0,0.1);
        background: #fff;
    }
    .study-cover-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: #6c757d;
    }
</style>

<div class="mb-3">
    <input type="search" class="form-control" id="studiesSearch" placeholder="Pesquisar por título, descrição, congregação..." autocomplete="off">
</div>

<div class="d-lg-none">
    <?php if (empty($studies)): ?>
        <div class="text-center py-5">
            <p class="text-muted mb-0">Nenhum estudo cadastrado.</p>
        </div>
    <?php else: ?>
        <div class="d-grid gap-2">
            <?php foreach ($studies as $s): ?>
                <?php
                $studyDescription = trim((string)($s['description'] ?? ''));
                $studyDescriptionText = $studyDescription === '' ? '-' : $studyDescription;
                $studyDescriptionShort = $studyDescription === '' ? '-' : mb_strimwidth($studyDescription, 0, 30, '...');
                $studyHasMoreDescription = $studyDescription !== '' && mb_strlen($studyDescription, 'UTF-8') > 30;
                $baseName = pathinfo((string)($s['file_path'] ?? ''), PATHINFO_FILENAME);
                $coverUrl = null;
                if ($baseName !== '') {
                    $coverDir = __DIR__ . '/../../../../public/uploads/studies/covers/';
                    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                        $candidate = $coverDir . $baseName . '.' . $ext;
                        if (is_file($candidate)) {
                            $coverUrl = '/uploads/studies/covers/' . $baseName . '.' . $ext;
                            break;
                        }
                    }
                }
                $isUnowned = !isset($s['created_by']) || $s['created_by'] === null || $s['created_by'] === '';
                $isOwner = $userId !== null && isset($s['created_by']) && (string)$s['created_by'] === (string)$userId;
                $canManage = $canManagePermission && ($isAdmin || ($userId !== null && $isUnowned) || $isOwner);
                ?>
                <div class="card shadow-sm study-item" data-search="<?= htmlspecialchars(mb_strtolower(($s['title'] ?? '') . ' ' . ($s['description'] ?? '') . ' ' . ($s['congregation_name'] ?? '') . ' ' . ($s['created_at'] ?? ''), 'UTF-8')) ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <?php if ($coverUrl): ?>
                                <img src="<?= htmlspecialchars($coverUrl) ?>" alt="Capa" class="study-cover">
                            <?php else: ?>
                                <div class="study-cover study-cover-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-danger"><?= htmlspecialchars($s['title']) ?></div>
                                <div class="small text-muted" title="<?= htmlspecialchars($studyDescription) ?>">
                                    <span class="study-desc-short"><?= htmlspecialchars($studyDescriptionShort) ?></span>
                                    <span class="study-desc-full d-none"><?= nl2br(htmlspecialchars($studyDescriptionText)) ?></span>
                                    <?php if ($studyHasMoreDescription): ?>
                                        <a href="#" class="study-desc-toggle ms-1" data-state="short">ver mais</a>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                                    <?php if ($s['congregation_name']): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($s['congregation_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Geral (Todas)</span>
                                    <?php endif; ?>
                                    <span class="badge bg-light text-dark border"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="/uploads/studies/<?= $s['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </a>
                                <?php if ($canManage): ?>
                                    <a href="/admin/studies/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i> Editar
                                    </a>
                                    <a href="/admin/studies/delete/<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?');">
                                        <i class="fas fa-trash me-1"></i> Excluir
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="table-responsive d-none d-lg-block">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th style="width: 70px;"></th>
                <th>Título</th>
                <th>Descrição</th>
                <th>Congregação</th>
                <th>Data</th>
                <th>Arquivo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($studies)): ?>
                <tr><td colspan="7" class="text-center">Nenhum estudo cadastrado.</td></tr>
            <?php else: ?>
                <?php foreach ($studies as $s): ?>
                <?php
                $baseName = pathinfo((string)($s['file_path'] ?? ''), PATHINFO_FILENAME);
                $coverUrl = null;
                if ($baseName !== '') {
                    $coverDir = __DIR__ . '/../../../../public/uploads/studies/covers/';
                    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                        $candidate = $coverDir . $baseName . '.' . $ext;
                        if (is_file($candidate)) {
                            $coverUrl = '/uploads/studies/covers/' . $baseName . '.' . $ext;
                            break;
                        }
                    }
                }
                $isUnowned = !isset($s['created_by']) || $s['created_by'] === null || $s['created_by'] === '';
                $isOwner = $userId !== null && isset($s['created_by']) && (string)$s['created_by'] === (string)$userId;
                $canManage = $canManagePermission && ($isAdmin || ($userId !== null && $isUnowned) || $isOwner);
                ?>
                <tr>
                    <td>
                        <?php if ($coverUrl): ?>
                            <img src="<?= htmlspecialchars($coverUrl) ?>" alt="Capa" class="study-cover">
                        <?php else: ?>
                            <div class="study-cover study-cover-placeholder">
                                <i class="fas fa-book"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($s['title']) ?></td>
                    <td>
                        <?php
                        $studyDescription = trim((string)($s['description'] ?? ''));
                        if ($studyDescription === '') {
                            echo '-';
                        } else {
                            $short = mb_strimwidth($studyDescription, 0, 30, '...');
                            $hasMore = mb_strlen($studyDescription, 'UTF-8') > 30;
                            echo '<span class="study-desc-short">' . htmlspecialchars($short) . '</span>';
                            echo '<span class="study-desc-full d-none">' . nl2br(htmlspecialchars($studyDescription)) . '</span>';
                            if ($hasMore) {
                                echo ' <a href="#" class="study-desc-toggle" data-state="short">ver mais</a>';
                            }
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($s['congregation_name']): ?>
                            <span class="badge bg-info"><?= htmlspecialchars($s['congregation_name']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Geral (Todas)</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
                    <td>
                        <a href="/uploads/studies/<?= $s['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                    </td>
                    <td>
                        <?php if ($canManage): ?>
                            <a href="/admin/studies/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit me-1"></i> Editar
                            </a>
                            <a href="/admin/studies/delete/<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?');">
                                <i class="fas fa-trash me-1"></i> Excluir
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('studiesSearch');
    if (!input) return;

    function normalize(v) {
        return String(v || '').toLowerCase().trim();
    }

    function filter() {
        var q = normalize(input.value);

        document.querySelectorAll('.study-item').forEach(function (item) {
            var hay = item.getAttribute('data-search') || '';
            item.style.display = q === '' || hay.indexOf(q) !== -1 ? '' : 'none';
        });

        document.querySelectorAll('.table-responsive table tbody tr').forEach(function (tr) {
            var hay = normalize(tr.textContent);
            tr.style.display = q === '' || hay.indexOf(q) !== -1 ? '' : 'none';
        });
    }

    input.addEventListener('input', filter);
    filter();
});

document.addEventListener('click', function (e) {
    var link = e.target.closest('.study-desc-toggle');
    if (!link) return;
    e.preventDefault();

    var container = link.parentElement;
    if (!container) return;

    var shortEl = container.querySelector('.study-desc-short');
    var fullEl = container.querySelector('.study-desc-full');
    if (!shortEl || !fullEl) return;

    var state = link.getAttribute('data-state') || 'short';
    var next = state === 'short' ? 'full' : 'short';

    if (next === 'full') {
        shortEl.classList.add('d-none');
        fullEl.classList.remove('d-none');
        link.textContent = 'ver menos';
        link.setAttribute('data-state', 'full');
    } else {
        fullEl.classList.add('d-none');
        shortEl.classList.remove('d-none');
        link.textContent = 'ver mais';
        link.setAttribute('data-state', 'short');
    }
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Estudos Bíblicos e Esboços</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/studies/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Novo Estudo
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Operação realizada com sucesso.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin'; ?>
<?php $userId = $_SESSION['user_id'] ?? null; ?>
<?php $canManagePermission = function_exists('hasPermission') ? hasPermission('studies.manage') : false; ?>
<?php
function studyTypeMeta($type) {
    $map = [
        'Estudo' => ['label' => 'Estudos', 'class' => 'type-estudo'],
        'Esboço' => ['label' => 'Esboços', 'class' => 'type-esboco'],
        'EBD' => ['label' => 'EBD', 'class' => 'type-ebd'],
        'Livro' => ['label' => 'Livros', 'class' => 'type-livro'],
    ];
    return $map[$type] ?? $map['Estudo'];
}
?>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .studies-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .studies-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
    }
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
    .visibility-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .visibility-pill.pill-cong { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .visibility-pill.pill-general { background: #eef0f2; color: #495057; }
    .type-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .type-pill.type-estudo { background: rgba(179,0,0,0.10); color: #b30000; }
    .type-pill.type-esboco { background: rgba(255,153,0,0.14); color: #b36b00; }
    .type-pill.type-ebd { background: rgba(25,135,84,0.12); color: #198754; }
    .type-pill.type-livro { background: rgba(111,66,193,0.12); color: #6f42c1; }
    .date-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .7rem;
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
    .filter-card .form-control {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .filter-card .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
    .study-desc-toggle {
        display: inline-block;
        font-size: .72rem;
        font-weight: 700;
        color: #b30000;
        text-decoration: none;
        white-space: nowrap;
    }
    .study-desc-toggle:hover {
        color: #8a0000;
        text-decoration: underline;
    }
</style>

<div class="member-form-card filter-card mb-3">
    <div class="p-3">
        <input type="search" class="form-control" id="studiesSearch" placeholder="Pesquisar por título, descrição, congregação..." autocomplete="off">
    </div>
</div>

<div class="d-lg-none">
    <?php if (empty($studies)): ?>
        <div class="member-form-card">
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Nenhum estudo cadastrado.</p>
            </div>
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
                <div class="member-form-card study-item" data-search="<?= htmlspecialchars(mb_strtolower(($s['title'] ?? '') . ' ' . ($s['description'] ?? '') . ' ' . ($s['congregation_name'] ?? '') . ' ' . ($s['created_at'] ?? ''), 'UTF-8')) ?>">
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <?php if ($coverUrl): ?>
                                <img src="<?= htmlspecialchars($coverUrl) ?>" alt="Capa" class="study-cover">
                            <?php else: ?>
                                <div class="study-cover study-cover-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?= htmlspecialchars($s['title']) ?></div>
                                <div class="small text-muted" title="<?= htmlspecialchars($studyDescription) ?>">
                                    <span class="study-desc-short"><?= htmlspecialchars($studyDescriptionShort) ?></span>
                                    <span class="study-desc-full d-none"><?= nl2br(htmlspecialchars($studyDescriptionText)) ?></span>
                                    <?php if ($studyHasMoreDescription): ?>
                                        <a href="#" class="study-desc-toggle ms-1" data-state="short">ver mais</a>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                                    <?php $typeMeta = studyTypeMeta($s['material_type'] ?? null); ?>
                                    <span class="type-pill <?= $typeMeta['class'] ?>"><?= htmlspecialchars($typeMeta['label']) ?></span>
                                    <?php if ($s['congregation_name']): ?>
                                        <span class="visibility-pill pill-cong"><?= htmlspecialchars($s['congregation_name']) ?></span>
                                    <?php else: ?>
                                        <span class="visibility-pill pill-general">Geral (Todas)</span>
                                    <?php endif; ?>
                                    <span class="date-pill"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="/admin/studies/view/<?= $s['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </a>
                                <?php if ($canManage): ?>
                                    <a href="/admin/studies/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
                                        <i class="fas fa-edit me-1"></i> Editar
                                    </a>
                                    <a href="/admin/studies/delete/<?= $s['id'] ?>" class="btn btn-sm btn-danger rounded-pill fw-semibold px-3 btn-delete-study" data-title="<?= htmlspecialchars($s['title']) ?>">
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

<div class="member-form-card d-none d-lg-block">
    <div class="table-responsive p-2">
        <table class="table table-hover studies-table" style="width:100%">
            <thead>
                <tr>
                    <th style="width: 70px;"></th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Congregação</th>
                    <th>Data</th>
                    <th>Arquivo</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($studies)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">Nenhum estudo cadastrado.</td></tr>
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
                    <tr class="study-item" data-search="<?= htmlspecialchars(mb_strtolower(($s['title'] ?? '') . ' ' . ($s['description'] ?? '') . ' ' . ($s['congregation_name'] ?? '') . ' ' . ($s['created_at'] ?? ''), 'UTF-8')) ?>">
                        <td>
                            <?php if ($coverUrl): ?>
                                <img src="<?= htmlspecialchars($coverUrl) ?>" alt="Capa" class="study-cover">
                            <?php else: ?>
                                <div class="study-cover study-cover-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?= htmlspecialchars($s['title']) ?></td>
                        <td>
                            <?php $typeMeta = studyTypeMeta($s['material_type'] ?? null); ?>
                            <span class="type-pill <?= $typeMeta['class'] ?>"><?= htmlspecialchars($typeMeta['label']) ?></span>
                        </td>
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
                                <span class="visibility-pill pill-cong"><?= htmlspecialchars($s['congregation_name']) ?></span>
                            <?php else: ?>
                                <span class="visibility-pill pill-general">Geral (Todas)</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
                        <td>
                            <a href="/admin/studies/view/<?= $s['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                        </td>
                        <td class="text-end">
                            <?php if ($canManage): ?>
                                <a href="/admin/studies/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/admin/studies/delete/<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-study" data-title="<?= htmlspecialchars($s['title']) ?>" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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

document.querySelectorAll('.btn-delete-study').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const title = btn.getAttribute('data-title');
        Swal.fire({
            title: 'Excluir estudo?',
            text: `Tem certeza que deseja excluir "${title}"? Esta ação não pode ser desfeita.`,
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

<?php include __DIR__ . '/../../layout/footer.php'; ?>

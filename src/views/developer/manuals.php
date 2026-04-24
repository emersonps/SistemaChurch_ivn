<?php include __DIR__ . '/layout_developer.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Gerenciar Manuais em Vídeo</h1>
        <p class="text-muted mb-0">Cadastre vídeos do YouTube por tema e defina exatamente quais perfis poderão vê-los.</p>
    </div>
    <a href="/admin/manual" class="btn btn-outline-primary">
        <i class="fas fa-play-circle me-1"></i> Ver Manual do Usuário
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (!empty($syncLocked)): ?>
    <div class="alert alert-warning">
        <div class="fw-semibold mb-1">Edição local bloqueada</div>
        <div>Os manuais desta instalação estão sendo controlados pela central. Para atualizar o conteúdo, use <a href="/developer/manual-sync" class="alert-link">Sincronizar Manuais</a>.</div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><?= empty($editing) ? 'Novo Vídeo' : 'Editar Vídeo' ?></div>
            <div class="card-body">
                <?php if (!empty($editing)): ?>
                    <div class="alert alert-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">Editando agora</div>
                            <div><?= htmlspecialchars($editing['title'] ?? '') ?></div>
                        </div>
                        <span class="badge bg-dark">ID <?= (int)($editing['id'] ?? 0) ?></span>
                    </div>
                <?php endif; ?>
                <form action="/developer/manuals" method="POST">
                    <?= csrf_field() ?>
                    <?php if (!empty($editing['id'])): ?>
                        <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Tema</label>
                        <input type="text" name="theme" class="form-control" value="<?= htmlspecialchars($editing['theme'] ?? '') ?>" required <?= !empty($syncLocked) ? 'disabled' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Título do Vídeo</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required <?= !empty($syncLocked) ? 'disabled' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link do YouTube</label>
                        <input type="url" name="youtube_url" class="form-control" value="<?= htmlspecialchars($editing['youtube_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=..." required <?= !empty($syncLocked) ? 'disabled' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-control" rows="4" <?= !empty($syncLocked) ? 'disabled' : '' ?>><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ordem de Exibição</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars((string)($editing['sort_order'] ?? 0)) ?>" <?= !empty($syncLocked) ? 'disabled' : '' ?>>
                    </div>

                    <?php $selectedTokens = $editing['target_tokens'] ?? []; ?>
                    <div class="mb-3">
                        <label class="form-label">Perfis Administrativos</label>
                        <?php foreach (array_filter($targetChoices, fn($c) => strpos($c['type'], 'admin_') === 0) as $choice): ?>
                            <?php $token = in_array($choice['type'], ['admin_all'], true) ? $choice['type'] : $choice['type'] . ':' . $choice['key']; ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="admin_targets[]" value="<?= htmlspecialchars($token) ?>" id="target_<?= md5($token) ?>" <?= in_array($token, $selectedTokens, true) ? 'checked' : '' ?> <?= !empty($syncLocked) ? 'disabled' : '' ?>>
                                <label class="form-check-label" for="target_<?= md5($token) ?>"><?= htmlspecialchars($choice['label']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Perfis do Portal do Membro</label>
                        <?php foreach (array_filter($targetChoices, fn($c) => strpos($c['type'], 'member_') === 0) as $choice): ?>
                            <?php $token = in_array($choice['type'], ['member_all'], true) ? $choice['type'] : $choice['type'] . ':' . $choice['key']; ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="member_targets[]" value="<?= htmlspecialchars($token) ?>" id="target_<?= md5($token) ?>" <?= in_array($token, $selectedTokens, true) ? 'checked' : '' ?> <?= !empty($syncLocked) ? 'disabled' : '' ?>>
                                <label class="form-check-label" for="target_<?= md5($token) ?>"><?= htmlspecialchars($choice['label']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= !isset($editing['is_active']) || (int)$editing['is_active'] === 1 ? 'checked' : '' ?> <?= !empty($syncLocked) ? 'disabled' : '' ?>>
                        <label class="form-check-label" for="is_active">Vídeo ativo</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" <?= !empty($syncLocked) ? 'disabled' : '' ?>>
                            <i class="fas fa-save me-1"></i> <?= empty($editing) ? 'Cadastrar Vídeo' : 'Salvar Alterações' ?>
                        </button>
                        <?php if (!empty($editing)): ?>
                            <a href="<?= htmlspecialchars($editing['youtube_url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger">
                                <i class="fab fa-youtube me-1"></i> Abrir Vídeo Atual
                            </a>
                            <a href="/developer/manuals" class="btn btn-outline-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Vídeos Cadastrados</span>
                <span class="badge bg-secondary"><?= count($videos ?? []) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($videos)): ?>
                    <div class="text-muted">Nenhum vídeo cadastrado ainda.</div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($videos as $video): ?>
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($video['title']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($video['theme']) ?></div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-<?= (int)$video['is_active'] === 1 ? 'success' : 'secondary' ?>">
                                                <?= (int)$video['is_active'] === 1 ? 'Ativo' : 'Inativo' ?>
                                            </span>
                                            <a href="/developer/manuals/edit/<?= (int)$video['id'] ?>" class="btn btn-sm btn-outline-primary <?= !empty($syncLocked) ? 'disabled' : '' ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="/developer/manuals/delete/<?= (int)$video['id'] ?>" method="POST" onsubmit="return <?= !empty($syncLocked) ? 'false' : "confirm('Deseja remover este vídeo do manual?');" ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" <?= !empty($syncLocked) ? 'disabled' : '' ?>>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="ratio ratio-16x9 mb-3">
                                        <iframe src="<?= htmlspecialchars($video['embed_url']) ?>" title="<?= htmlspecialchars($video['title']) ?>" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                    </div>

                                    <?php if (!empty($video['description'])): ?>
                                        <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($video['description'])) ?></p>
                                    <?php endif; ?>

                                    <div class="small text-muted mb-2">Perfis liberados</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach (($video['target_labels'] ?? []) as $label): ?>
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($label) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout_footer.php'; ?>

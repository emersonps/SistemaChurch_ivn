<?php $isPortalView = isset($_SESSION['member_id']) && !isset($_SESSION['user_id']); ?>
<?php $isDeveloperView = (($_SESSION['user_role'] ?? '') === 'developer') && isset($_SESSION['user_id']); ?>
<?php include $isPortalView ? __DIR__ . '/../portal/layout/header.php' : ($isDeveloperView ? __DIR__ . '/../developer/layout_developer.php' : __DIR__ . '/../layout/header.php'); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-1"><?= htmlspecialchars($manualTitle ?? 'Manual em Vídeo') ?></h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($manualSubtitle ?? 'Conteúdo em vídeo liberado para o seu perfil.') ?></p>
    </div>
</div>

<?php if (empty($videosByTheme)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Ainda não há vídeos do manual disponíveis para o seu perfil.
    </div>
<?php else: ?>
    <?php foreach ($videosByTheme as $theme => $videos): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($theme) ?></span>
                <span class="badge bg-primary rounded-pill"><?= count($videos) ?> vídeo(s)</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <?php foreach ($videos as $video): ?>
                        <div class="col-xl-6">
                            <div class="border rounded p-3 h-100">
                                <div class="ratio ratio-16x9 mb-3">
                                    <iframe src="<?= htmlspecialchars($video['embed_url']) ?>" title="<?= htmlspecialchars($video['title']) ?>" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                </div>
                                <h5 class="mb-2"><?= htmlspecialchars($video['title']) ?></h5>
                                <?php if (!empty($video['description'])): ?>
                                    <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($video['description'])) ?></p>
                                <?php endif; ?>
                                <a href="<?= htmlspecialchars($video['youtube_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger">
                                    <i class="fab fa-youtube me-1"></i> Abrir no YouTube
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include $isPortalView ? __DIR__ . '/../portal/layout/footer.php' : ($isDeveloperView ? __DIR__ . '/../developer/layout_footer.php' : __DIR__ . '/../layout/footer.php'); ?>

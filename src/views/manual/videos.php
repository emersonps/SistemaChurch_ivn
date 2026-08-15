<?php $isPortalView = isset($_SESSION['member_id']) && !isset($_SESSION['user_id']); ?>
<?php $isDeveloperView = (($_SESSION['user_role'] ?? '') === 'developer') && isset($_SESSION['user_id']); ?>
<?php include $isPortalView ? __DIR__ . '/../portal/layout/header.php' : ($isDeveloperView ? __DIR__ . '/../developer/layout_developer.php' : __DIR__ . '/../layout/header.php'); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h2 mb-1"><?= htmlspecialchars($manualTitle ?? 'Manual em Vídeo') ?></h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($manualSubtitle ?? 'Conteúdo em vídeo liberado para o seu perfil.') ?></p>
    </div>
</div>

<style>
    .manual-theme-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .manual-theme-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .manual-theme-title {
        font-weight: 800;
        font-size: 1rem;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        gap: .6rem;
    }
    .manual-theme-title .theme-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(179,0,0,0.10);
        color: #b30000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        flex: 0 0 auto;
    }
    .manual-count-pill {
        display: inline-block;
        padding: .25rem .7rem;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
        white-space: nowrap;
    }
    .manual-theme-body { padding: 1.25rem; }
    .manual-video-card {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        padding: 1rem;
        height: 100%;
        background: #fafafa;
    }
    .manual-video-title {
        font-weight: 700;
        font-size: .98rem;
        color: #1a1a1a;
        margin-bottom: .5rem;
    }
    .manual-video-desc {
        font-size: .86rem;
        color: #6c757d;
    }
    .manual-empty-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        text-align: center;
        padding: 3rem 1.5rem;
    }
</style>

<?php if (empty($videosByTheme)): ?>
    <div class="manual-empty-card">
        <i class="fas fa-video-slash fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-0">Ainda não há vídeos do manual disponíveis para o seu perfil.</p>
    </div>
<?php else: ?>
    <?php foreach ($videosByTheme as $theme => $videos): ?>
        <div class="manual-theme-card">
            <div class="manual-theme-header">
                <span class="manual-theme-title">
                    <span class="theme-icon"><i class="fas fa-graduation-cap"></i></span>
                    <?= htmlspecialchars($theme) ?>
                </span>
                <span class="manual-count-pill"><?= count($videos) ?> vídeo(s)</span>
            </div>
            <div class="manual-theme-body">
                <div class="row g-3">
                    <?php foreach ($videos as $video): ?>
                        <div class="col-xl-6">
                            <div class="manual-video-card">
                                <div class="ratio ratio-16x9 mb-3 rounded overflow-hidden">
                                    <iframe src="<?= htmlspecialchars($video['embed_url']) ?>" title="<?= htmlspecialchars($video['title']) ?>" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                </div>
                                <div class="manual-video-title"><?= htmlspecialchars($video['title']) ?></div>
                                <?php if (!empty($video['description'])): ?>
                                    <p class="manual-video-desc mb-3"><?= nl2br(htmlspecialchars($video['description'])) ?></p>
                                <?php endif; ?>
                                <a href="<?= htmlspecialchars($video['youtube_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold px-3">
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

<?php // Expects $videos (array of video_wall rows). Used by video_wall.php and embedded in gallery.php's Mural de Vídeos tab. ?>
<?php if (empty($videos)): ?>
    <div class="text-center text-muted py-5">Nenhum vídeo disponível ainda.</div>
<?php else: ?>
    <div class="row g-4 pb-5">
        <?php foreach ($videos as $video): ?>
            <?php
                $liveStatus = 'none';
                if (!empty($video['is_livestream']) && !empty($video['livestream_scheduled_at'])) {
                    $scheduled = strtotime($video['livestream_scheduled_at']);
                    if ($scheduled !== false) {
                        if ($scheduled > time()) {
                            $liveStatus = 'upcoming';
                        } else {
                            $liveStatus = (time() - $scheduled) < (4 * 3600) ? 'ao_vivo' : 'encerrado';
                        }
                    }
                }
            ?>
            <div class="col-md-4 col-sm-6" data-video-card data-category="<?= htmlspecialchars($video['category']) ?>" data-live-status="<?= $liveStatus ?>">
                <div class="vw-card">
                    <div class="vw-card-thumb">
                        <img src="https://img.youtube.com/vi/<?= htmlspecialchars($video['youtube_video_id']) ?>/hqdefault.jpg" alt="">
                        <span class="vw-card-category"><?= htmlspecialchars($video['category']) ?></span>
                        <?php if (!empty($video['is_livestream']) && !empty($video['livestream_scheduled_at'])): ?>
                            <span class="live-badge" style="position: absolute; top: .6rem; right: .6rem;" data-scheduled-at="<?= htmlspecialchars(formatLivestreamScheduledAtIso($video['livestream_scheduled_at'])) ?>"></span>
                        <?php endif; ?>
                        <a href="/mural-de-videos/assistir/<?= (int)$video['id'] ?>" class="vw-card-play" target="_blank" rel="noopener">
                            <i class="fas fa-circle-play"></i>
                        </a>
                    </div>
                    <div class="vw-card-body">
                        <div class="vw-card-title"><?= htmlspecialchars($video['title']) ?></div>
                        <div class="vw-card-meta">
                            <?= !empty($video['video_date']) ? date('d/m/Y', strtotime($video['video_date'])) : '' ?>
                            <?php if (!empty($video['speaker'])): ?> · <?= htmlspecialchars($video['speaker']) ?><?php endif; ?>
                        </div>
                        <?php if (!empty($video['description'])): ?>
                            <div class="vw-card-desc"><?= htmlspecialchars($video['description']) ?></div>
                        <?php endif; ?>
                        <a href="/mural-de-videos/assistir/<?= (int)$video['id'] ?>" class="btn btn-dark btn-sm mt-auto" target="_blank" rel="noopener">
                            <i class="fas fa-play me-1"></i> Assistir Agora
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

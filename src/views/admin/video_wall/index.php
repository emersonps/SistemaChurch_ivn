<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Mural de Vídeos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/video-wall/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Novo Vídeo
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<style>
    .vw-stat-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 14px;
        padding: 1rem 1.1rem;
        height: 100%;
    }
    .vw-stat-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #868e96;
        font-weight: 700;
    }
    .vw-stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #212529;
    }
    .vw-tile {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .vw-tile-thumb {
        position: relative;
        width: 100%;
        padding-top: 56.25%;
        background: #000;
        overflow: hidden;
    }
    .vw-tile-thumb img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .vw-tile-badges {
        position: absolute;
        top: .5rem;
        left: .5rem;
        right: .5rem;
        display: flex;
        justify-content: space-between;
        gap: .4rem;
    }
    .vw-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .25rem .55rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        background: rgba(0,0,0,0.65);
        color: #fff;
    }
    .vw-badge.is-featured { background: rgba(255,193,7,0.92); color: #212529; }
    .vw-tile-body { padding: .9rem 1rem; flex: 1; display: flex; flex-direction: column; }
    .vw-tile-title { font-weight: 700; font-size: .92rem; margin-bottom: .25rem; }
    .vw-tile-meta { font-size: .78rem; color: #868e96; margin-bottom: .5rem; }
    .vw-tile-actions { margin-top: auto; display: flex; gap: .4rem; }
    .icon-btn {
        width: 32px; height: 32px; display: inline-flex; align-items: center;
        justify-content: center; border-radius: 50%; padding: 0;
    }
</style>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="vw-stat-card">
            <div class="vw-stat-label">Total Vídeos</div>
            <div class="vw-stat-value"><?= (int)$stats['total'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vw-stat-card">
            <div class="vw-stat-label">Em Destaque</div>
            <div class="vw-stat-value"><?= (int)$stats['featured'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vw-stat-card">
            <div class="vw-stat-label">Categorias</div>
            <div class="vw-stat-value"><?= (int)$stats['categories'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vw-stat-card">
            <div class="vw-stat-label">Visualizações</div>
            <div class="vw-stat-value"><?= number_format((float)$stats['views'], 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<form method="GET" action="/admin/video-wall" class="row g-2 align-items-center mb-4">
    <div class="col-md-6">
        <input type="text" name="search" class="form-control" placeholder="Buscar vídeo..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-4">
        <select name="category" class="form-select" onchange="this.form.submit()">
            <option value="">Todas categorias</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $selectedCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-search me-1"></i> Buscar</button>
    </div>
</form>

<?php if (empty($videos)): ?>
    <div class="text-center text-muted py-5">Nenhum vídeo cadastrado ainda.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($videos as $video): ?>
            <div class="col-md-4 col-sm-6">
                <div class="vw-tile">
                    <div class="vw-tile-thumb">
                        <img src="https://img.youtube.com/vi/<?= htmlspecialchars($video['youtube_video_id']) ?>/hqdefault.jpg" alt="">
                        <div class="vw-tile-badges">
                            <span class="vw-badge"><?= htmlspecialchars($video['category']) ?></span>
                            <?php if (!empty($video['is_featured'])): ?>
                                <span class="vw-badge is-featured"><i class="fas fa-star"></i> Destaque</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="vw-tile-body">
                        <div class="vw-tile-title"><?= htmlspecialchars($video['title']) ?></div>
                        <div class="vw-tile-meta">
                            <?= !empty($video['video_date']) ? date('d/m/Y', strtotime($video['video_date'])) : '-' ?>
                            <?php if (!empty($video['speaker'])): ?> · <?= htmlspecialchars($video['speaker']) ?><?php endif; ?>
                            · <?= (int)$video['views'] ?> views
                        </div>
                        <div class="vw-tile-actions">
                            <form action="/admin/video-wall/toggle-featured/<?= (int)$video['id'] ?>" method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm <?= !empty($video['is_featured']) ? 'btn-warning' : 'btn-outline-warning' ?> icon-btn" title="<?= !empty($video['is_featured']) ? 'Remover destaque' : 'Marcar como destaque' ?>">
                                    <i class="fas fa-star"></i>
                                </button>
                            </form>
                            <a href="/admin/video-wall/edit/<?= (int)$video['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="/admin/video-wall/delete/<?= (int)$video['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Excluir este vídeo?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger icon-btn" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

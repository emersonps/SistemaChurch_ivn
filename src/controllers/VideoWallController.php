<?php
// src/controllers/VideoWallController.php

class VideoWallController {
    public function index() {
        requirePermission('video_wall.view');
        $db = (new Database())->connect();

        $search = trim((string)($_GET['search'] ?? ''));
        $category = trim((string)($_GET['category'] ?? ''));

        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(title LIKE ? OR speaker LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($category !== '') {
            $where[] = 'category = ?';
            $params[] = $category;
        }
        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare("SELECT * FROM video_wall $whereSql ORDER BY video_date DESC, created_at DESC");
        $stmt->execute($params);
        $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = [
            'total' => (int)$db->query('SELECT COUNT(*) FROM video_wall')->fetchColumn(),
            'featured' => (int)$db->query('SELECT COUNT(*) FROM video_wall WHERE is_featured = 1')->fetchColumn(),
            'categories' => count(getVideoWallCategories()),
            'views' => (int)$db->query('SELECT COALESCE(SUM(views), 0) FROM video_wall')->fetchColumn(),
        ];

        view('admin/video_wall/index', [
            'videos' => $videos,
            'stats' => $stats,
            'categories' => getVideoWallCategories(),
            'search' => $search,
            'selectedCategory' => $category,
        ]);
    }

    public function create() {
        requirePermission('video_wall.manage');
        view('admin/video_wall/create', ['categories' => getVideoWallCategories(), 'categoryRows' => $this->getCategoryRows()]);
    }

    public function store() {
        requirePermission('video_wall.manage');
        $this->saveFromRequest(null);
        $_SESSION['success'] = 'Vídeo cadastrado com sucesso.';
        redirect('/admin/video-wall');
    }

    public function edit($id) {
        requirePermission('video_wall.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT * FROM video_wall WHERE id = ?');
        $stmt->execute([$id]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$video) {
            redirect('/admin/video-wall');
        }

        view('admin/video_wall/edit', ['video' => $video, 'categories' => getVideoWallCategories(), 'categoryRows' => $this->getCategoryRows()]);
    }

    private function getCategoryRows() {
        $db = (new Database())->connect();
        return $db->query('SELECT id, name FROM video_wall_categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id) {
        requirePermission('video_wall.manage');
        $this->saveFromRequest($id);
        $_SESSION['success'] = 'Vídeo atualizado com sucesso.';
        redirect('/admin/video-wall');
    }

    public function delete($id) {
        requirePermission('video_wall.manage');
        $db = (new Database())->connect();
        $db->prepare('DELETE FROM video_wall WHERE id = ?')->execute([$id]);
        $_SESSION['success'] = 'Vídeo removido com sucesso.';
        redirect('/admin/video-wall');
    }

    public function toggleFeatured($id) {
        requirePermission('video_wall.manage');
        $db = (new Database())->connect();

        $stmt = $db->prepare('SELECT is_featured FROM video_wall WHERE id = ?');
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();
        if ($current === false) {
            redirect('/admin/video-wall');
        }

        $db->beginTransaction();
        try {
            // Only one video can be featured at a time.
            $db->exec('UPDATE video_wall SET is_featured = 0');
            if (!$current) {
                $db->prepare('UPDATE video_wall SET is_featured = 1 WHERE id = ?')->execute([$id]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }

        redirect('/admin/video-wall');
    }

    public function categoryStore() {
        requirePermission('video_wall.manage');
        verify_csrf();
        header('Content-Type: application/json; charset=utf-8');

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Informe um nome para a categoria.']);
            exit;
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT id FROM video_wall_categories WHERE name = ?');
        $stmt->execute([$name]);
        if ($stmt->fetchColumn()) {
            http_response_code(422);
            echo json_encode(['error' => 'Essa categoria já existe.']);
            exit;
        }

        $db->prepare('INSERT INTO video_wall_categories (name) VALUES (?)')->execute([$name]);
        echo json_encode(['category' => ['id' => (int)$db->lastInsertId(), 'name' => $name]]);
        exit;
    }

    public function categoryRename($id) {
        requirePermission('video_wall.manage');
        verify_csrf();
        header('Content-Type: application/json; charset=utf-8');

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Informe um nome para a categoria.']);
            exit;
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT name FROM video_wall_categories WHERE id = ?');
        $stmt->execute([$id]);
        $oldName = $stmt->fetchColumn();
        if ($oldName === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada.']);
            exit;
        }

        $dupStmt = $db->prepare('SELECT id FROM video_wall_categories WHERE name = ? AND id != ?');
        $dupStmt->execute([$name, $id]);
        if ($dupStmt->fetchColumn()) {
            http_response_code(422);
            echo json_encode(['error' => 'Já existe uma categoria com esse nome.']);
            exit;
        }

        $db->prepare('UPDATE video_wall_categories SET name = ? WHERE id = ?')->execute([$name, $id]);
        if ($oldName !== $name) {
            $db->prepare('UPDATE video_wall SET category = ? WHERE category = ?')->execute([$name, $oldName]);
        }
        echo json_encode(['category' => ['id' => (int)$id, 'name' => $name]]);
        exit;
    }

    public function categoryDelete($id) {
        requirePermission('video_wall.manage');
        verify_csrf();
        header('Content-Type: application/json; charset=utf-8');

        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT name FROM video_wall_categories WHERE id = ?');
        $stmt->execute([$id]);
        $name = $stmt->fetchColumn();
        if ($name === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada.']);
            exit;
        }

        $countStmt = $db->prepare('SELECT COUNT(*) FROM video_wall WHERE category = ?');
        $countStmt->execute([$name]);
        $inUse = (int)$countStmt->fetchColumn();
        if ($inUse > 0) {
            http_response_code(422);
            echo json_encode(['error' => 'Essa categoria está em uso por ' . $inUse . ' vídeo(s). Mova esses vídeos para outra categoria antes de excluí-la.']);
            exit;
        }

        $db->prepare('DELETE FROM video_wall_categories WHERE id = ?')->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // Public: /mural-de-videos — full list, filterable by category.
    public function publicIndex() {
        $db = (new Database())->connect();
        $category = trim((string)($_GET['category'] ?? ''));

        $where = '';
        $params = [];
        if ($category !== '') {
            $where = 'WHERE category = ?';
            $params[] = $category;
        }

        $stmt = $db->prepare("SELECT * FROM video_wall $where ORDER BY video_date DESC, created_at DESC");
        $stmt->execute($params);
        $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('public/video_wall', [
            'videos' => $videos,
            'categories' => getVideoWallCategories(),
            'selectedCategory' => $category,
            'siteProfile' => getChurchSiteProfileSettings(),
        ]);
    }

    // Public: counts a view then redirects to the actual YouTube URL —
    // used by "Assistir Agora" links so we have a lightweight local view
    // count without needing the YouTube Data API.
    public function watch($id) {
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT youtube_url FROM video_wall WHERE id = ?');
        $stmt->execute([$id]);
        $url = $stmt->fetchColumn();

        if (!$url) {
            redirect('/');
        }

        $db->prepare('UPDATE video_wall SET views = views + 1 WHERE id = ?')->execute([$id]);
        header('Location: ' . $url);
        exit;
    }

    private function saveFromRequest($id) {
        $db = (new Database())->connect();

        $title = trim((string)($_POST['title'] ?? ''));
        $youtubeUrl = trim((string)($_POST['youtube_url'] ?? ''));
        $category = trim((string)($_POST['category'] ?? ''));
        $speaker = trim((string)($_POST['speaker'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $videoDate = trim((string)($_POST['video_date'] ?? ''));

        if (!in_array($category, getVideoWallCategories(), true)) {
            $category = 'Mensagens';
        }
        if ($videoDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $videoDate)) {
            $videoDate = date('Y-m-d');
        }

        $videoId = extractYoutubeVideoId($youtubeUrl);
        if ($videoId === '') {
            $_SESSION['error'] = 'Não foi possível reconhecer esse link do YouTube. Cole a URL completa do vídeo.';
            redirect($id ? '/admin/video-wall/edit/' . $id : '/admin/video-wall/create');
        }

        if ($id) {
            $stmt = $db->prepare('UPDATE video_wall SET title = ?, youtube_url = ?, youtube_video_id = ?, category = ?, speaker = ?, description = ?, video_date = ? WHERE id = ?');
            $stmt->execute([$title, $youtubeUrl, $videoId, $category, $speaker, $description, $videoDate, $id]);
        } else {
            $stmt = $db->prepare('INSERT INTO video_wall (title, youtube_url, youtube_video_id, category, speaker, description, video_date) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $youtubeUrl, $videoId, $category, $speaker, $description, $videoDate]);
        }
    }
}

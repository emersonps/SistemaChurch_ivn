<?php
// src/controllers/GalleryController.php

class GalleryController {
    
    // ADMIN: Listar Álbuns
    public function index() {
        requirePermission('gallery.view');
        $db = (new Database())->connect();
        $albums = $db->query("SELECT * FROM photo_albums ORDER BY created_at DESC")->fetchAll();
        view('admin/gallery/index', ['albums' => $albums]);
    }

    // ADMIN: Criar Álbum (View)
    public function create() {
        requirePermission('gallery.manage');
        view('admin/gallery/create', ['categories' => getPhotoAlbumCategories(), 'categoryRows' => $this->getCategoryRows()]);
    }

    // ADMIN: Salvar Álbum
    public function store() {
        requirePermission('gallery.manage');
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $location = $_POST['location'];
        $category = trim((string)($_POST['category'] ?? ''));
        if ($category === '' || !in_array($category, getPhotoAlbumCategories(), true)) {
            $category = null;
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare("INSERT INTO photo_albums (title, description, event_date, location, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $event_date, $location, $category]);

        redirect('/admin/gallery');
    }

    // ADMIN: Editar Álbum (View)
    public function edit($id) {
        requirePermission('gallery.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM photo_albums WHERE id = ?");
        $stmt->execute([$id]);
        $album = $stmt->fetch();

        if (!$album) {
            redirect('/admin/gallery');
        }

        view('admin/gallery/edit', ['album' => $album, 'categories' => getPhotoAlbumCategories(), 'categoryRows' => $this->getCategoryRows()]);
    }

    // ADMIN: Atualizar Álbum
    public function update($id) {
        requirePermission('gallery.manage');
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $location = $_POST['location'];
        $category = trim((string)($_POST['category'] ?? ''));
        if ($category === '' || !in_array($category, getPhotoAlbumCategories(), true)) {
            $category = null;
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare("UPDATE photo_albums SET title=?, description=?, event_date=?, location=?, category=? WHERE id=?");
        $stmt->execute([$title, $description, $event_date, $location, $category, $id]);

        redirect('/admin/gallery');
    }

    private function getCategoryRows() {
        $db = (new Database())->connect();
        return $db->query('SELECT id, name FROM photo_album_categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function categoryStore() {
        requirePermission('gallery.manage');
        verify_csrf();
        header('Content-Type: application/json; charset=utf-8');

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Informe um nome para a categoria.']);
            exit;
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT id FROM photo_album_categories WHERE name = ?');
        $stmt->execute([$name]);
        if ($stmt->fetchColumn()) {
            http_response_code(422);
            echo json_encode(['error' => 'Essa categoria já existe.']);
            exit;
        }

        $db->prepare('INSERT INTO photo_album_categories (name) VALUES (?)')->execute([$name]);
        echo json_encode(['category' => ['id' => (int)$db->lastInsertId(), 'name' => $name]]);
        exit;
    }

    public function categoryRename($id) {
        requirePermission('gallery.manage');
        verify_csrf();
        header('Content-Type: application/json; charset=utf-8');

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Informe um nome para a categoria.']);
            exit;
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT name FROM photo_album_categories WHERE id = ?');
        $stmt->execute([$id]);
        $oldName = $stmt->fetchColumn();
        if ($oldName === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada.']);
            exit;
        }

        $dupStmt = $db->prepare('SELECT id FROM photo_album_categories WHERE name = ? AND id != ?');
        $dupStmt->execute([$name, $id]);
        if ($dupStmt->fetchColumn()) {
            http_response_code(422);
            echo json_encode(['error' => 'Já existe uma categoria com esse nome.']);
            exit;
        }

        $db->prepare('UPDATE photo_album_categories SET name = ? WHERE id = ?')->execute([$name, $id]);
        if ($oldName !== $name) {
            $db->prepare('UPDATE photo_albums SET category = ? WHERE category = ?')->execute([$name, $oldName]);
        }
        echo json_encode(['category' => ['id' => (int)$id, 'name' => $name]]);
        exit;
    }

    public function categoryDelete($id) {
        requirePermission('gallery.manage');
        verify_csrf();
        header('Content-Type: application/json; charset=utf-8');

        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT name FROM photo_album_categories WHERE id = ?');
        $stmt->execute([$id]);
        $name = $stmt->fetchColumn();
        if ($name === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada.']);
            exit;
        }

        $countStmt = $db->prepare('SELECT COUNT(*) FROM photo_albums WHERE category = ?');
        $countStmt->execute([$name]);
        $inUse = (int)$countStmt->fetchColumn();
        if ($inUse > 0) {
            http_response_code(422);
            echo json_encode(['error' => 'Essa categoria está em uso por ' . $inUse . ' álbum(ns). Mova esses álbuns para outra categoria antes de excluí-la.']);
            exit;
        }

        $db->prepare('DELETE FROM photo_album_categories WHERE id = ?')->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ADMIN: Gerenciar Fotos do Álbum
    public function manage($id) {
        requirePermission('gallery.manage');
        $db = (new Database())->connect();
        
        // Buscar Álbum
        $stmt = $db->prepare("SELECT * FROM photo_albums WHERE id = ?");
        $stmt->execute([$id]);
        $album = $stmt->fetch();

        if (!$album) redirect('/admin/gallery');

        // Buscar Fotos
        $stmt = $db->prepare("SELECT * FROM photos WHERE album_id = ?");
        $stmt->execute([$id]);
        $photos = $stmt->fetchAll();

        view('admin/gallery/manage', ['album' => $album, 'photos' => $photos]);
    }

    // ADMIN: Upload de Fotos
    public function upload($id) {
        requirePermission('gallery.manage');
        
        $db = (new Database())->connect();
        


        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $uploadDir = __DIR__ . '/../../public/uploads/gallery/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $filepath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $filepath)) {
                $stmt = $db->prepare("INSERT INTO photos (album_id, filename) VALUES (?, ?)");
                $stmt->execute([$id, $filename]);
            }
        }

        redirect("/admin/gallery/manage/$id");
    }

    // ADMIN: Excluir Foto
    public function deletePhoto($id) {
        requirePermission('gallery.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM photos WHERE id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetch();

        if ($photo) {
            $filepath = __DIR__ . '/../../public/uploads/gallery/' . $photo['filename'];
            if (file_exists($filepath)) unlink($filepath);

            $db->prepare("DELETE FROM photos WHERE id = ?")->execute([$id]);
            redirect("/admin/gallery/manage/" . $photo['album_id']);
        } else {
            redirect('/admin/gallery');
        }
    }

    // ADMIN: Excluir Álbum
    public function deleteAlbum($id) {
        requirePermission('gallery.manage');
        $db = (new Database())->connect();
        
        // Excluir arquivos físicos primeiro
        $stmt = $db->prepare("SELECT filename FROM photos WHERE album_id = ?");
        $stmt->execute([$id]);
        $photos = $stmt->fetchAll();

        foreach ($photos as $photo) {
            $filepath = __DIR__ . '/../../public/uploads/gallery/' . $photo['filename'];
            if (file_exists($filepath)) unlink($filepath);
        }

        // DELETE CASCADE cuidará dos registros no banco
        $db->prepare("DELETE FROM photo_albums WHERE id = ?")->execute([$id]);
        
        redirect('/admin/gallery');
    }

    // PÚBLICO: Página da Galeria
    public function publicIndex() {
        $db = (new Database())->connect();

        // Buscar Álbuns com suas fotos
        $albums = $db->query("SELECT * FROM photo_albums ORDER BY event_date DESC")->fetchAll();

        foreach ($albums as &$album) {
            $stmt = $db->prepare("SELECT * FROM photos WHERE album_id = ? ORDER BY id ASC");
            $stmt->execute([$album['id']]);
            $album['photos'] = $stmt->fetchAll();
        }
        unset($album);

        $categories = getPhotoAlbumCategories();
        $categoryCounts = array_fill_keys($categories, 0);
        $years = [];
        $flatPhotos = [];

        foreach ($albums as $album) {
            if (!empty($album['category']) && isset($categoryCounts[$album['category']])) {
                $categoryCounts[$album['category']]++;
            }

            $year = !empty($album['event_date']) ? (int)date('Y', strtotime($album['event_date'])) : (int)date('Y');
            $years[] = $year;

            $isFirst = true;
            foreach (($album['photos'] ?? []) as $photo) {
                $file = (string)($photo['filename'] ?? '');
                if ($file === '') continue;
                $flatPhotos[] = [
                    'url' => '/uploads/gallery/' . ltrim($file, '/'),
                    'year' => $year,
                    'category' => $album['category'] ?? '',
                    'album_id' => (int)$album['id'],
                    'album_title' => $album['title'] ?? '',
                    'is_first_of_album' => $isFirst,
                ];
                $isFirst = false;
            }
        }

        $photosByYear = [];
        foreach ($flatPhotos as $photo) {
            $photosByYear[$photo['year']][] = $photo;
        }
        krsort($photosByYear);

        $yearRange = !empty($years) ? (min($years) === max($years) ? (string)min($years) : min($years) . '-' . max($years)) : '';

        view('public/gallery', [
            'albums' => $albums,
            'categories' => $categories,
            'categoryCounts' => $categoryCounts,
            'photosByYear' => $photosByYear,
            'totalPhotoCount' => count($flatPhotos),
            'albumCount' => count($albums),
            'yearRange' => $yearRange,
        ]);
    }
}

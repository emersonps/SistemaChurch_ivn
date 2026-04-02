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
        view('admin/gallery/create');
    }

    // ADMIN: Salvar Álbum
    public function store() {
        requirePermission('gallery.manage');
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $location = $_POST['location'];

        $db = (new Database())->connect();
        $stmt = $db->prepare("INSERT INTO photo_albums (title, description, event_date, location) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $event_date, $location]);

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

        view('admin/gallery/edit', ['album' => $album]);
    }

    // ADMIN: Atualizar Álbum
    public function update($id) {
        requirePermission('gallery.manage');
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $location = $_POST['location'];

        $db = (new Database())->connect();
        $stmt = $db->prepare("UPDATE photo_albums SET title=?, description=?, event_date=?, location=? WHERE id=?");
        $stmt->execute([$title, $description, $event_date, $location, $id]);

        redirect('/admin/gallery');
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
            $stmt = $db->prepare("SELECT * FROM photos WHERE album_id = ? LIMIT 7");
            $stmt->execute([$album['id']]);
            $album['photos'] = $stmt->fetchAll();
        }

        view('public/gallery', ['albums' => $albums]);
    }
}

<?php
// src/controllers/SignatureController.php

class SignatureController {
    
    public function index() {
        requirePermission('signatures.view');
        $db = (new Database())->connect();
        
        $signatures = $db->query("SELECT * FROM signatures ORDER BY role_label ASC")->fetchAll();
        
        view('admin/signatures/index', ['signatures' => $signatures]);
    }

    public function store() {
        requirePermission('signatures.manage');
        
        $slug = $_POST['slug']; // Hidden field for existing ones, or new
        $name = $_POST['name'];
        $role_label = $_POST['role_label'];
        $id = $_POST['id'] ?? null;
        
        $db = (new Database())->connect();
        
        // Handle Image Upload
        $imagePath = null;
        if (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] == 0) {
            $uploadDir = __DIR__ . '/../../public/uploads/signatures/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $ext = pathinfo($_FILES['signature_image']['name'], PATHINFO_EXTENSION);
            $filename = $slug . '_' . uniqid() . '.' . $ext;
            
            // Remove old image if exists
            if ($id) {
                $old = $db->query("SELECT image_path FROM signatures WHERE id = $id")->fetchColumn();
                if ($old && file_exists($uploadDir . $old)) {
                    @unlink($uploadDir . $old);
                }
            }
            
            if (move_uploaded_file($_FILES['signature_image']['tmp_name'], $uploadDir . $filename)) {
                $imagePath = $filename;
            }
        }

        if ($id) {
            // Update
            $sql = "UPDATE signatures SET name = ?, role_label = ?";
            $params = [$name, $role_label];
            
            if ($imagePath) {
                $sql .= ", image_path = ?";
                $params[] = $imagePath;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        } else {
            // Insert (New custom signature)
            // Slug must be unique
            $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $role_label)));
            $slug = $baseSlug;
            
            // Verifica duplicidade e incrementa
            $counter = 1;
            while (true) {
                $check = $db->prepare("SELECT 1 FROM signatures WHERE slug = ?");
                $check->execute([$slug]);
                if (!$check->fetch()) {
                    break;
                }
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            // Renomear imagem se o slug mudou
            if ($imagePath && $slug !== $baseSlug) {
                $oldFilename = $imagePath;
                $newFilename = str_replace($baseSlug, $slug, $oldFilename);
                $uploadDir = __DIR__ . '/../../public/uploads/signatures/';
                if (file_exists($uploadDir . $oldFilename)) {
                    rename($uploadDir . $oldFilename, $uploadDir . $newFilename);
                    $imagePath = $newFilename;
                }
            }
            
            $stmt = $db->prepare("INSERT INTO signatures (slug, name, role_label, image_path) VALUES (?, ?, ?, ?)");
            $stmt->execute([$slug, $name, $role_label, $imagePath ?? '']);
        }

        redirect('/admin/signatures?success=1');
    }
    
    public function delete($id) {
        requirePermission('signatures.manage');
        $db = (new Database())->connect();
        
        // Get image to delete file
        $img = $db->query("SELECT image_path FROM signatures WHERE id = $id")->fetchColumn();
        if ($img && file_exists(__DIR__ . '/../../public/uploads/signatures/' . $img)) {
            @unlink(__DIR__ . '/../../public/uploads/signatures/' . $img);
        }
        
        $db->prepare("DELETE FROM signatures WHERE id = ?")->execute([$id]);
        redirect('/admin/signatures');
    }
}

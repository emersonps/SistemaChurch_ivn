<?php
// src/controllers/StudyController.php

class StudyController {
    
    private function requireAdmin() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }
    }
    
    private function requireMember() {
        if (!isset($_SESSION['member_id'])) {
            redirect('/portal/login');
        }
    }
    
    // ADMIN METHODS
    
    public function index() {
        requirePermission('studies.view');
        $db = (new Database())->connect();
        
        $studies = $db->query("SELECT s.*, c.name as congregation_name 
                               FROM studies s 
                               LEFT JOIN congregations c ON s.congregation_id = c.id 
                               ORDER BY s.created_at DESC")->fetchAll();
        
        view('admin/studies/index', ['studies' => $studies]);
    }
    
    public function create() {
        requirePermission('studies.manage');
        $db = (new Database())->connect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $congregation_id = !empty($_POST['congregation_id']) ? $_POST['congregation_id'] : null;
            
            // File Upload
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/studies/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                // Allow only PDF
                if (strtolower($ext) !== 'pdf') {
                    redirect('/admin/studies/create?error=invalid_type');
                }
                
                $filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename)) {
                    $stmt = $db->prepare("INSERT INTO studies (title, description, file_path, congregation_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $filename, $congregation_id]);
                    
                    redirect('/admin/studies?success=created');
                } else {
                    redirect('/admin/studies/create?error=upload_failed');
                }
            } else {
                redirect('/admin/studies/create?error=no_file');
            }
        }
        
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        view('admin/studies/create', ['congregations' => $congregations]);
    }
    
    public function delete($id) {
        requirePermission('studies.manage');
        $db = (new Database())->connect();
        
        $study = $db->query("SELECT * FROM studies WHERE id = $id")->fetch();
        if ($study) {
            // Delete file
            $filePath = __DIR__ . '/../../public/uploads/studies/' . $study['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $db->exec("DELETE FROM studies WHERE id = $id");
        }
        
        redirect('/admin/studies?success=deleted');
    }
    
    // PORTAL METHODS
    
    public function portalIndex() {
        $this->requireMember();
        $member_id = $_SESSION['member_id'];
        $db = (new Database())->connect();
        
        // Get Member's congregation
        $member = $db->query("SELECT congregation_id FROM members WHERE id = $member_id")->fetch();
        $congregation_id = $member['congregation_id'];
        
        // Fetch studies: Global (null) OR Member's Congregation
        $sql = "SELECT * FROM studies 
                WHERE congregation_id IS NULL OR congregation_id = ? 
                ORDER BY created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$congregation_id]);
        $studies = $stmt->fetchAll();
        
        view('portal/studies', ['studies' => $studies]);
    }
}

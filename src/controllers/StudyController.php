<?php
// src/controllers/StudyController.php

class StudyController {
    
    private function requireAdmin() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }
    }

    private function ensureStudiesCreatedByColumn($db): void {
        try {
            $db->query("SELECT created_by FROM studies LIMIT 1")->fetch();
            return;
        } catch (Exception $e) {
        }

        try {
            $db->exec("ALTER TABLE studies ADD COLUMN created_by INT NULL");
        } catch (Exception $e) {
        }
    }

    private function claimStudyIfUnowned($db, $studyId): void {
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            return;
        }

        $this->ensureStudiesCreatedByColumn($db);

        try {
            $stmt = $db->prepare("UPDATE studies SET created_by = ? WHERE id = ? AND (created_by IS NULL OR created_by = '')");
            $stmt->execute([$userId, $studyId]);
        } catch (Exception $e) {
        }
    }

    private function canManageStudy(array $study): bool {
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return true;
        }
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            return false;
        }
        if (!isset($study['created_by'])) {
            return false;
        }
        return (string)$study['created_by'] === (string)$userId;
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
            $createdBy = $_SESSION['user_id'] ?? null;
            $this->ensureStudiesCreatedByColumn($db);

            $hasCover = isset($_FILES['cover']) && ($_FILES['cover']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
            $coverExt = null;
            if ($hasCover) {
                $coverExt = strtolower((string)pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
                $allowedCoverExts = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($coverExt, $allowedCoverExts, true)) {
                    redirect('/admin/studies/create?error=invalid_cover');
                }
            }
            
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
                    $studyId = null;
                    try {
                        $stmt = $db->prepare("INSERT INTO studies (title, description, file_path, congregation_id, created_by) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $description, $filename, $congregation_id, $createdBy]);
                        $studyId = $db->lastInsertId();
                    } catch (Exception $e) {
                        $this->ensureStudiesCreatedByColumn($db);
                        try {
                            $stmt = $db->prepare("INSERT INTO studies (title, description, file_path, congregation_id, created_by) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$title, $description, $filename, $congregation_id, $createdBy]);
                            $studyId = $db->lastInsertId();
                        } catch (Exception $e2) {
                            $stmt = $db->prepare("INSERT INTO studies (title, description, file_path, congregation_id) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$title, $description, $filename, $congregation_id]);
                            $studyId = $db->lastInsertId();
                        }
                    }

                    if ($studyId !== null && $createdBy !== null) {
                        $this->ensureStudiesCreatedByColumn($db);
                        try {
                            $stmtOwner = $db->prepare("UPDATE studies SET created_by = ? WHERE id = ? AND (created_by IS NULL OR created_by = '')");
                            $stmtOwner->execute([$createdBy, $studyId]);
                        } catch (Exception $e) {
                        }
                    }

                    if ($hasCover) {
                        $coverDir = __DIR__ . '/../../public/uploads/studies/covers/';
                        if (!file_exists($coverDir)) mkdir($coverDir, 0777, true);

                        $baseName = pathinfo($filename, PATHINFO_FILENAME);
                        $coverFilename = $baseName . '.' . $coverExt;
                        move_uploaded_file($_FILES['cover']['tmp_name'], $coverDir . $coverFilename);
                    }
                    
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
    
    public function edit($id) {
        requirePermission('studies.manage');
        $db = (new Database())->connect();

        $stmt = $db->prepare("SELECT * FROM studies WHERE id = ?");
        $stmt->execute([$id]);
        $study = $stmt->fetch();
        if (!$study) {
            redirect('/admin/studies');
        }
        if (!isset($study['created_by']) || $study['created_by'] === null || $study['created_by'] === '') {
            $this->claimStudyIfUnowned($db, $id);
            $stmt = $db->prepare("SELECT * FROM studies WHERE id = ?");
            $stmt->execute([$id]);
            $study = $stmt->fetch();
            if (!$study) {
                redirect('/admin/studies');
            }
        }
        if (!$this->canManageStudy($study)) {
            redirect('/admin/studies');
        }

        $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        view('admin/studies/edit', ['study' => $study, 'congregations' => $congregations]);
    }

    public function update($id) {
        requirePermission('studies.manage');
        $db = (new Database())->connect();

        $stmt = $db->prepare("SELECT * FROM studies WHERE id = ?");
        $stmt->execute([$id]);
        $study = $stmt->fetch();
        if (!$study) {
            redirect('/admin/studies');
        }
        if (!isset($study['created_by']) || $study['created_by'] === null || $study['created_by'] === '') {
            $this->claimStudyIfUnowned($db, $id);
            $stmt = $db->prepare("SELECT * FROM studies WHERE id = ?");
            $stmt->execute([$id]);
            $study = $stmt->fetch();
            if (!$study) {
                redirect('/admin/studies');
            }
        }
        if (!$this->canManageStudy($study)) {
            redirect('/admin/studies');
        }

        $title = $_POST['title'];
        $description = $_POST['description'];
        $congregation_id = !empty($_POST['congregation_id']) ? $_POST['congregation_id'] : null;

        $hasCover = isset($_FILES['cover']) && ($_FILES['cover']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
        $coverExt = null;
        if ($hasCover) {
            $coverExt = strtolower((string)pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            $allowedCoverExts = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($coverExt, $allowedCoverExts, true)) {
                redirect('/admin/studies/edit/' . $id . '?error=invalid_cover');
            }
        }

        $uploadDir = __DIR__ . '/../../public/uploads/studies/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $coverDir = __DIR__ . '/../../public/uploads/studies/covers/';
        if (!file_exists($coverDir)) mkdir($coverDir, 0777, true);

        $currentFilePath = (string)$study['file_path'];
        $newFilePath = $currentFilePath;

        $oldBase = pathinfo($currentFilePath, PATHINFO_FILENAME);
        $oldCoverFiles = glob($coverDir . $oldBase . '.*') ?: [];

        $hasNewPdf = isset($_FILES['file']) && ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
        if ($hasNewPdf) {
            $ext = strtolower((string)pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                redirect('/admin/studies/edit/' . $id . '?error=invalid_type');
            }

            $newFilePath = uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $newFilePath)) {
                redirect('/admin/studies/edit/' . $id . '?error=upload_failed');
            }

            $oldPdfPath = $uploadDir . $currentFilePath;
            if (is_file($oldPdfPath)) {
                unlink($oldPdfPath);
            }

            $newBase = pathinfo($newFilePath, PATHINFO_FILENAME);
            if (!$hasCover && !empty($oldCoverFiles)) {
                $moved = false;
                foreach ($oldCoverFiles as $path) {
                    if (!is_file($path)) {
                        continue;
                    }
                    $extOld = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
                    $target = $coverDir . $newBase . '.' . $extOld;
                    @rename($path, $target);
                    $moved = true;
                    break;
                }
                foreach ($oldCoverFiles as $path) {
                    if (is_file($path)) {
                        unlink($path);
                    }
                }
            } else {
                foreach ($oldCoverFiles as $path) {
                    if (is_file($path)) {
                        unlink($path);
                    }
                }
            }
        }

        $base = pathinfo($newFilePath, PATHINFO_FILENAME);
        if ($hasCover) {
            foreach (glob($coverDir . $base . '.*') ?: [] as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
            $coverFilename = $base . '.' . $coverExt;
            move_uploaded_file($_FILES['cover']['tmp_name'], $coverDir . $coverFilename);
        }

        $stmtUp = $db->prepare("UPDATE studies SET title = ?, description = ?, congregation_id = ?, file_path = ? WHERE id = ?");
        $stmtUp->execute([$title, $description, $congregation_id, $newFilePath, $id]);

        redirect('/admin/studies?success=updated');
    }

    public function delete($id) {
        requirePermission('studies.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM studies WHERE id = ?");
        $stmt->execute([$id]);
        $study = $stmt->fetch();
        if ($study) {
            if (!isset($study['created_by']) || $study['created_by'] === null || $study['created_by'] === '') {
                $this->claimStudyIfUnowned($db, $id);
                $stmt = $db->prepare("SELECT * FROM studies WHERE id = ?");
                $stmt->execute([$id]);
                $study = $stmt->fetch();
                if (!$study) {
                    redirect('/admin/studies');
                }
            }
            if (!$this->canManageStudy($study)) {
                redirect('/admin/studies');
            }
            // Delete file
            $filePath = __DIR__ . '/../../public/uploads/studies/' . $study['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $baseName = pathinfo((string)$study['file_path'], PATHINFO_FILENAME);
            $coverDir = __DIR__ . '/../../public/uploads/studies/covers/';
            $pattern = $coverDir . $baseName . '.*';
            foreach (glob($pattern) ?: [] as $coverPath) {
                if (is_file($coverPath)) {
                    unlink($coverPath);
                }
            }
            
            $stmtDel = $db->prepare("DELETE FROM studies WHERE id = ?");
            $stmtDel->execute([$id]);
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

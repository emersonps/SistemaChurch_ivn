<?php
// src/controllers/ManualController.php

class ManualController {
    private function requireAdminUser() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }
    }

    private function requireMemberUser() {
        if (!isset($_SESSION['member_id'])) {
            redirect('/portal/login');
        }
    }

    private function requireDeveloper() {
        if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'developer')) {
            redirect('/admin/dashboard');
        }
    }

    private function extractYoutubeVideoId($url) {
        $url = trim((string)$url);
        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtube\.com/embed/|youtube\.com/shorts/|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function getAdminRoleChoices() {
        $rbac = require __DIR__ . '/../../config/rbac.php';
        $roles = $rbac['roles'] ?? [];
        $items = [['type' => 'admin_all', 'key' => '', 'label' => 'Todos os usuários administrativos']];
        foreach ($roles as $roleKey => $roleData) {
            $items[] = [
                'type' => 'admin_role',
                'key' => $roleKey,
                'label' => $roleData['label'] ?? ucfirst($roleKey)
            ];
        }
        return $items;
    }

    private function getMemberRoleChoices(PDO $db) {
        $items = [['type' => 'member_all', 'key' => '', 'label' => 'Todos os membros do portal']];
        $roles = ['Membro'];
        $rows = $db->query("SELECT DISTINCT role FROM members WHERE role IS NOT NULL AND TRIM(role) != '' ORDER BY role ASC")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($rows as $role) {
            $role = trim((string)$role);
            if ($role !== '' && !in_array($role, $roles, true)) {
                $roles[] = $role;
            }
        }

        foreach ($roles as $role) {
            $items[] = [
                'type' => 'member_role',
                'key' => $role,
                'label' => 'Membros com cargo: ' . $role
            ];
        }

        return $items;
    }

    private function getTargetChoices(PDO $db) {
        return array_merge($this->getAdminRoleChoices(), $this->getMemberRoleChoices($db));
    }

    private function getSelectedTargetsFromPost() {
        $targets = [];

        foreach ((array)($_POST['admin_targets'] ?? []) as $value) {
            if ($value === 'admin_all') {
                $targets[] = ['type' => 'admin_all', 'key' => null];
            } elseif (strpos((string)$value, 'admin_role:') === 0) {
                $targets[] = ['type' => 'admin_role', 'key' => substr((string)$value, 11)];
            }
        }

        foreach ((array)($_POST['member_targets'] ?? []) as $value) {
            if ($value === 'member_all') {
                $targets[] = ['type' => 'member_all', 'key' => null];
            } elseif (strpos((string)$value, 'member_role:') === 0) {
                $targets[] = ['type' => 'member_role', 'key' => substr((string)$value, 12)];
            }
        }

        $unique = [];
        foreach ($targets as $target) {
            $signature = $target['type'] . '|' . ($target['key'] ?? '');
            $unique[$signature] = $target;
        }

        return array_values($unique);
    }

    private function getManualVideosForAudience(PDO $db, $audienceType, $audienceKey = null) {
        $sql = "
            SELECT DISTINCT mv.*
            FROM manual_videos mv
            JOIN manual_video_targets mvt ON mvt.manual_video_id = mv.id
            WHERE mv.is_active = 1
              AND (
                    (mvt.target_type = ? AND (? IS NULL OR mvt.target_key = ?))
                 OR (? = 'admin_role' AND mvt.target_type = 'admin_all')
                 OR (? = 'member_role' AND mvt.target_type = 'member_all')
              )
            ORDER BY mv.theme ASC, mv.sort_order ASC, mv.title ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$audienceType, $audienceKey, $audienceKey, $audienceType, $audienceType]);
        $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($videos as $video) {
            $theme = trim((string)($video['theme'] ?? 'Geral'));
            if ($theme === '') {
                $theme = 'Geral';
            }
            $video['embed_url'] = 'https://www.youtube.com/embed/' . $video['youtube_video_id'];
            $grouped[$theme][] = $video;
        }

        return $grouped;
    }

    private function loadManageVideos(PDO $db) {
        $videos = $db->query("SELECT * FROM manual_videos ORDER BY theme ASC, sort_order ASC, title ASC")->fetchAll(PDO::FETCH_ASSOC);
        $stmtTargets = $db->query("SELECT manual_video_id, target_type, target_key FROM manual_video_targets ORDER BY id ASC");
        $targetsMap = [];
        foreach ($stmtTargets->fetchAll(PDO::FETCH_ASSOC) as $target) {
            $targetsMap[$target['manual_video_id']][] = $target;
        }

        $labels = [];
        foreach ($this->getTargetChoices($db) as $choice) {
            $labels[$choice['type'] . '|' . ($choice['key'] ?? '')] = $choice['label'];
        }

        foreach ($videos as &$video) {
            $videoTargets = $targetsMap[$video['id']] ?? [];
            $video['target_tokens'] = array_map(function ($target) {
                if (in_array($target['target_type'], ['admin_all', 'member_all'], true)) {
                    return $target['target_type'];
                }
                return $target['target_type'] . ':' . ($target['target_key'] ?? '');
            }, $videoTargets);
            $video['target_labels'] = array_values(array_filter(array_map(function ($target) use ($labels) {
                $signature = $target['target_type'] . '|' . ($target['target_key'] ?? '');
                return $labels[$signature] ?? null;
            }, $videoTargets)));
            $video['embed_url'] = 'https://www.youtube.com/embed/' . $video['youtube_video_id'];
        }
        unset($video);

        return $videos;
    }

    private function loadEditVideo(PDO $db, $id) {
        $stmt = $db->prepare("SELECT * FROM manual_videos WHERE id = ?");
        $stmt->execute([$id]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$video) {
            return null;
        }

        $stmtTargets = $db->prepare("SELECT target_type, target_key FROM manual_video_targets WHERE manual_video_id = ? ORDER BY id ASC");
        $stmtTargets->execute([$id]);
        $targets = $stmtTargets->fetchAll(PDO::FETCH_ASSOC);
        $video['target_tokens'] = array_map(function ($target) {
            if (in_array($target['target_type'], ['admin_all', 'member_all'], true)) {
                return $target['target_type'];
            }
            return $target['target_type'] . ':' . ($target['target_key'] ?? '');
        }, $targets);

        return $video;
    }

    public function index() {
        $this->requireAdminUser();
        $db = (new Database())->connect();
        $role = $_SESSION['user_role'] ?? 'admin';
        $videosByTheme = $this->getManualVideosForAudience($db, 'admin_role', $role);

        view('manual/videos', [
            'videosByTheme' => $videosByTheme,
            'manualTitle' => 'Manual do Sistema',
            'manualSubtitle' => 'Vídeos liberados para o seu perfil de usuário.'
        ]);
    }

    public function portal() {
        $this->requireMemberUser();
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT role FROM members WHERE id = ?");
        $stmt->execute([$_SESSION['member_id']]);
        $memberRole = $stmt->fetchColumn() ?: 'Membro';
        $videosByTheme = $this->getManualVideosForAudience($db, 'member_role', $memberRole);

        view('manual/videos', [
            'videosByTheme' => $videosByTheme,
            'manualTitle' => 'Manual do Portal do Membro',
            'manualSubtitle' => 'Conteúdos em vídeo liberados para o seu perfil no portal.'
        ]);
    }

    public function manage() {
        $this->requireDeveloper();
        $db = (new Database())->connect();
        $editing = !empty($_GET['edit']) ? $this->loadEditVideo($db, (int)$_GET['edit']) : null;

        view('developer/manuals', [
            'videos' => $this->loadManageVideos($db),
            'targetChoices' => $this->getTargetChoices($db),
            'editing' => $editing
        ]);
    }

    public function store() {
        $this->requireDeveloper();
        verify_csrf();

        $db = (new Database())->connect();
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $title = trim((string)($_POST['title'] ?? ''));
        $theme = trim((string)($_POST['theme'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $youtubeUrl = trim((string)($_POST['youtube_url'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $targets = $this->getSelectedTargetsFromPost();
        $videoId = $this->extractYoutubeVideoId($youtubeUrl);

        if ($title === '' || $theme === '' || $youtubeUrl === '' || !$videoId) {
            $_SESSION['error'] = 'Preencha tema, título e um link válido do YouTube.';
            redirect('/developer/manuals' . ($id ? '?edit=' . $id : ''));
            return;
        }

        if (empty($targets)) {
            $_SESSION['error'] = 'Selecione pelo menos um perfil de visualização.';
            redirect('/developer/manuals' . ($id ? '?edit=' . $id : ''));
            return;
        }

        $db->beginTransaction();
        try {
            if ($id) {
                $stmt = $db->prepare("UPDATE manual_videos SET title = ?, theme = ?, description = ?, youtube_url = ?, youtube_video_id = ?, sort_order = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$title, $theme, $description !== '' ? $description : null, $youtubeUrl, $videoId, $sortOrder, $isActive, $id]);
                $db->prepare("DELETE FROM manual_video_targets WHERE manual_video_id = ?")->execute([$id]);
                $manualId = $id;
            } else {
                $stmt = $db->prepare("INSERT INTO manual_videos (title, theme, description, youtube_url, youtube_video_id, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $theme, $description !== '' ? $description : null, $youtubeUrl, $videoId, $sortOrder, $isActive]);
                $manualId = (int)$db->lastInsertId();
            }

            $stmtTarget = $db->prepare("INSERT INTO manual_video_targets (manual_video_id, target_type, target_key) VALUES (?, ?, ?)");
            foreach ($targets as $target) {
                $stmtTarget->execute([$manualId, $target['type'], $target['key']]);
            }

            $db->commit();
            $_SESSION['success'] = $id ? 'Vídeo do manual atualizado com sucesso.' : 'Vídeo do manual criado com sucesso.';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Não foi possível salvar o vídeo do manual.';
        }

        redirect('/developer/manuals');
    }

    public function delete($id) {
        $this->requireDeveloper();
        verify_csrf();
        $db = (new Database())->connect();
        $stmt = $db->prepare("DELETE FROM manual_videos WHERE id = ?");
        $stmt->execute([(int)$id]);
        $_SESSION['success'] = 'Vídeo do manual removido com sucesso.';
        redirect('/developer/manuals');
    }
}

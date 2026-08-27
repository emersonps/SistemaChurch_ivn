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
}

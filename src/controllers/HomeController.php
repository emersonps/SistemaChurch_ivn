<?php
// src/controllers/HomeController.php

class HomeController {
    public function index() {
        $db = (new Database())->connect();
        
        // Buscar Banners Ativos
        try {
            $banners = $db->query("SELECT * FROM banners WHERE active = 1 ORDER BY display_order ASC, created_at DESC")->fetchAll();
        } catch (PDOException $e) {
            $banners = [];
        }

        $cultos = $db->query("SELECT * FROM events WHERE type = 'culto' AND (status = 'active' OR status IS NULL) ORDER BY event_date ASC")->fetchAll();
        
        // Buscar Convites Especiais (type = 'convite')
        $convites = $db->query("
            SELECT * FROM events 
            WHERE type = 'convite' 
            AND (status = 'active' OR status IS NULL) 
            ORDER BY event_date ASC 
            LIMIT 6
        ")->fetchAll();

        // Buscar Eventos por Congregação (EXCETO culto, convite e interno)
        $eventos = $db->query("
            SELECT * FROM events 
            WHERE type NOT IN ('culto', 'convite', 'interno')
            AND (status = 'active' OR status IS NULL) 
            ORDER BY event_date ASC
        ")->fetchAll();

        // Buscar Congregações (mostrando todas)
        $congregacoes = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        
        // Buscar Configurações de Layout do Site
        $site_settings = $db->query("SELECT * FROM site_settings LIMIT 1")->fetch();
        if (!$site_settings) {
            // Default caso a tabela esteja vazia
            $site_settings = [
                'theme_id' => 'theme-1',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6c757d',
                'font_family' => 'Inter, sans-serif',
                'hero_bg_image' => 'hero_theme_1.jpg'
            ];
        }

        view('public/home', [
            'banners' => $banners,
            'cultos' => $cultos,
            'eventos' => $eventos,
            'convites' => $convites,
            'congregacoes' => $congregacoes,
            'site_settings' => $site_settings
        ]);
    }
}

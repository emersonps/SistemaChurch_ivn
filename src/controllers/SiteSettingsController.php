<?php
// src/controllers/SiteSettingsController.php

class SiteSettingsController {
    
    // Configurações de Temas pré-definidos
    private $themes = [
        'theme-0' => [
            'name' => 'Original SGII',
            'primary_color' => '#d4af37', // Dourado
            'secondary_color' => '#b30000', // Vermelho
            'font_family' => 'Poppins, sans-serif',
            'hero_bg_image' => 'hero_theme_0.jpg',
            'description' => 'O layout padrão original do sistema, combinando vermelho e dourado com fonte moderna.'
        ],
        'theme-1' => [
            'name' => 'Clássico Azul',
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6c757d',
            'font_family' => 'Inter, sans-serif',
            'hero_bg_image' => 'hero_theme_1.jpg',
            'description' => 'Um layout tradicional, confiável e limpo, focado em tons de azul e tipografia moderna.'
        ],
        'theme-2' => [
            'name' => 'Elegância Dourada',
            'primary_color' => '#d4af37',
            'secondary_color' => '#343a40',
            'font_family' => 'Playfair Display, serif',
            'hero_bg_image' => 'hero_theme_2.jpg',
            'description' => 'Sofisticado e solene, utiliza fontes serifadas e tons dourados para um aspecto premium.'
        ],
        'theme-3' => [
            'name' => 'Esperança Verde',
            'primary_color' => '#198754',
            'secondary_color' => '#f8f9fa',
            'font_family' => 'Roboto, sans-serif',
            'hero_bg_image' => 'hero_theme_3.jpg',
            'description' => 'Traz a sensação de vida e renovação com verde vibrante e contrastes claros.'
        ],
        'theme-4' => [
            'name' => 'Paixão e Fogo',
            'primary_color' => '#dc3545',
            'secondary_color' => '#212529',
            'font_family' => 'Montserrat, sans-serif',
            'hero_bg_image' => 'hero_theme_4.jpg',
            'description' => 'Cores quentes que remetem ao Espírito Santo e fervor, com tipografia forte.'
        ],
        'theme-5' => [
            'name' => 'Realeza Púrpura',
            'primary_color' => '#6f42c1',
            'secondary_color' => '#e9ecef',
            'font_family' => 'Cinzel, serif',
            'hero_bg_image' => 'hero_theme_5.jpg',
            'description' => 'Um tema roxo marcante, simbolizando majestade e profundidade espiritual.'
        ],
        'theme-6' => [
            'name' => 'Luz do Amanhecer',
            'primary_color' => '#fd7e14',
            'secondary_color' => '#fff3cd',
            'font_family' => 'Nunito, sans-serif',
            'hero_bg_image' => 'hero_theme_6.jpg',
            'description' => 'Laranja suave e alegre, perfeito para igrejas jovens e dinâmicas.'
        ],
        'theme-7' => [
            'name' => 'Oceano Profundo',
            'primary_color' => '#0dcaf0',
            'secondary_color' => '#055160',
            'font_family' => 'Lato, sans-serif',
            'hero_bg_image' => 'hero_theme_7.jpg',
            'description' => 'Tons de ciano e azul escuro, transmitindo paz, calma e águas tranquilas.'
        ],
        'theme-8' => [
            'name' => 'Minimalista Dark',
            'primary_color' => '#212529',
            'secondary_color' => '#adb5bd',
            'font_family' => 'Poppins, sans-serif',
            'hero_bg_image' => 'hero_theme_8.jpg',
            'description' => 'Moderno, com alto contraste, focado em modo escuro para leitura confortável.'
        ],
        'theme-9' => [
            'name' => 'Terra e Natureza',
            'primary_color' => '#8b4513',
            'secondary_color' => '#fdf5e6',
            'font_family' => 'Lora, serif',
            'hero_bg_image' => 'hero_theme_9.jpg',
            'description' => 'Tons terrosos e acolhedores, lembrando raízes, estabilidade e comunhão.'
        ],
        'theme-10' => [
            'name' => 'Graça Rosa',
            'primary_color' => '#d63384',
            'secondary_color' => '#ffe4e6',
            'font_family' => 'Raleway, serif',
            'hero_bg_image' => 'hero_theme_10.jpg',
            'description' => 'Um toque de delicadeza e amor fraternal com tons rosados e fonte clássica.'
        ]
    ];

    public function index() {
        requirePermission('settings.view'); 
        
        $db = (new Database())->connect();
        
        try {
            $stmt = $db->query("SELECT * FROM site_settings LIMIT 1");
            $currentSettings = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $currentSettings = false;
        }
        
        if (!$currentSettings) {
            $currentSettings = [
                'theme_id' => 'theme-1',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6c757d',
                'font_family' => 'Inter, sans-serif',
                'hero_bg_image' => 'hero_theme_1.jpg'
            ];
        }

        view('admin/site_settings/index', [
            'currentSettings' => $currentSettings,
            'themes' => $this->themes
        ]);
    }

    public function updateTheme() {
        requirePermission('settings.view');
        
        $theme_id = $_POST['theme_id'] ?? 'theme-1';
        
        if (!array_key_exists($theme_id, $this->themes)) {
            $theme_id = 'theme-1';
        }
        
        $selectedTheme = $this->themes[$theme_id];
        
        $db = (new Database())->connect();
        
        // Verifica se há upload de nova imagem de fundo personalizada
        $hero_bg_image = $selectedTheme['hero_bg_image'];
        
        if (isset($_FILES['custom_hero_bg']) && $_FILES['custom_hero_bg']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/assets/uploads/themes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['custom_hero_bg']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($fileExt, $allowedExts)) {
                $newFileName = 'custom_hero_' . time() . '.' . $fileExt;
                if (move_uploaded_file($_FILES['custom_hero_bg']['tmp_name'], $uploadDir . $newFileName)) {
                    $hero_bg_image = $newFileName;
                }
            }
        }
        
        $stmt = $db->prepare("UPDATE site_settings SET 
            theme_id = ?, 
            primary_color = ?, 
            secondary_color = ?, 
            font_family = ?, 
            hero_bg_image = ?
        ");
        
        $stmt->execute([
            $theme_id,
            $selectedTheme['primary_color'],
            $selectedTheme['secondary_color'],
            $selectedTheme['font_family'],
            $hero_bg_image
        ]);
        
        $_SESSION['flash_success'] = "Layout do site atualizado com sucesso!";
        redirect('/admin/site-settings');
    }
}

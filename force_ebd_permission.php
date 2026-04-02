<?php
// public/force_ebd_permission.php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Faça login primeiro e depois acesse esta página.");
}

$user_id = $_SESSION['user_id'];
echo "<h1>Forçando Permissões EBD para Usuário ID: $user_id</h1>";

try {
    $db = (new Database())->connect();
    
    $perms = ['ebd.view', 'ebd.manage', 'ebd.lessons'];
    
    foreach ($perms as $slug) {
        // 1. Garantir que a permissão existe no catálogo
        $db->prepare("INSERT OR IGNORE INTO permissions (slug, label, description) VALUES (?, 'EBD Permission', 'Auto-generated')")
           ->execute([$slug]);
           
        // 2. Atribuir ao usuário
        $stmt = $db->prepare("INSERT OR IGNORE INTO user_permissions (user_id, permission_slug) VALUES (?, ?)");
        $stmt->execute([$user_id, $slug]);
        
        echo "Permissão '$slug' atribuída.<br>";
    }
    
    echo "<h2 style='color:green'>Concluído!</h2>";
    echo "<p><a href='/admin'>Voltar ao Painel</a> (Dê um F5 lá)</p>";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

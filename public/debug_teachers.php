<?php
// public/debug_teachers.php
require_once __DIR__ . '/../config/database.php';

echo "<h1>Diagnóstico de Professores EBD</h1>";

try {
    $db = (new Database())->connect();
    
    // 1. Verificar se a coluna existe
    echo "<h2>Estrutura da Tabela Members</h2>";
    $cols = $db->query("SHOW COLUMNS FROM members LIKE 'is_ebd_teacher'")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cols)) {
        echo "<p style='color:red'>❌ Coluna 'is_ebd_teacher' NÃO EXISTE na tabela members.</p>";
        echo "<p>Por favor, rode as migrações em /migrate.php</p>";
    } else {
        echo "<p style='color:green'>✅ Coluna 'is_ebd_teacher' encontrada.</p>";
        
        // 2. Listar membros marcados como professores
        echo "<h2>Membros marcados como Professores (is_ebd_teacher = 1)</h2>";
        $teachers = $db->query("SELECT id, name, status, is_ebd_teacher FROM members WHERE is_ebd_teacher = 1")->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($teachers)) {
            echo "<p style='color:orange'>⚠️ Nenhum membro encontrado com is_ebd_teacher = 1.</p>";
        } else {
            echo "<ul>";
            foreach ($teachers as $t) {
                echo "<li>ID: {$t['id']} - Nome: {$t['name']} - Status: {$t['status']}</li>";
            }
            echo "</ul>";
        }
        
        // 3. Listar todos os membros para conferência
        echo "<h2>Todos os Membros (Primeiros 10)</h2>";
        $all = $db->query("SELECT id, name, status, is_ebd_teacher FROM members LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1'><tr><th>ID</th><th>Nome</th><th>Status</th><th>Professor?</th></tr>";
        foreach ($all as $m) {
            $isProf = isset($m['is_ebd_teacher']) ? $m['is_ebd_teacher'] : 'N/A';
            echo "<tr><td>{$m['id']}</td><td>{$m['name']}</td><td>{$m['status']}</td><td>$isProf</td></tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

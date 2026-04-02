<?php
// public/migrate.php

// Configurar sessão igual ao sistema principal
$sessionPath = __DIR__ . '/../tmp';
if (file_exists($sessionPath)) {
    session_save_path($sessionPath);
}
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/database/MigrationRunner.php';

// Senha simples para proteger (altere para algo seguro)
$MIGRATION_PASSWORD = 'Overid@392216';

// Proteção adicional: Somente o usuário com role 'developer' pode acessar
$isDev = false;
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer') {
    $isDev = true;
} elseif (isset($_SESSION['user_id'])) {
    try {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if ($stmt->fetchColumn() === 'developer') {
            $isDev = true;
            $_SESSION['user_role'] = 'developer'; // Auto-fix session
        }
    } catch (Exception $e) {}
}

if (!$isDev) {
    http_response_code(403);
    die("Acesso negado. Apenas o desenvolvedor autorizado pode acessar esta ferramenta.");
}

if (isset($_POST['password']) && $_POST['password'] === $MIGRATION_PASSWORD) {
    try {
        $runner = new MigrationRunner();
        $runner->init();
        $log = $runner->run();
        
        echo "<h1>Resultado da Migração</h1>";
        echo "<ul>";
        foreach ($log as $entry) {
            $color = strpos($entry, '✅') !== false ? 'green' : (strpos($entry, '❌') !== false ? 'red' : 'black');
            echo "<li style='color:$color'>$entry</li>";
        }
        echo "</ul>";
        echo "<a href='/migrate.php'>Voltar</a>";
        
    } catch (Exception $e) {
        echo "<h1 style='color:red'>Erro Crítico</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Migration Tool</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        input { padding: 10px; width: 100%; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Atualizar Banco de Dados</h2>
        <p>Insira a senha de deploy para aplicar as alterações.</p>
        <form method="POST">
            <input type="password" name="password" placeholder="Senha de Migração" required>
            <button type="submit">Executar Migrações</button>
        </form>
    </div>
</body>
</html>

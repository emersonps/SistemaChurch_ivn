<?php
// public/check_users.php
require_once __DIR__ . '/../config/database.php';

echo "<h1>Lista de Usuários</h1>";

try {
    $db = (new Database())->connect();
    $users = $db->query("SELECT id, name, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr><th>ID</th><th>Nome</th><th>Email</th><th>Role</th></tr>";
    foreach ($users as $u) {
        echo "<tr>";
        echo "<td>{$u['id']}</td>";
        echo "<td>{$u['name']}</td>";
        echo "<td>{$u['email']}</td>";
        echo "<td>{$u['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    session_start();
    echo "<h2>Sessão Atual</h2>";
    echo "User ID Logado: " . ($_SESSION['user_id'] ?? 'Nenhum') . "<br>";
    echo "Nome Logado: " . ($_SESSION['user_name'] ?? 'Nenhum') . "<br>";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

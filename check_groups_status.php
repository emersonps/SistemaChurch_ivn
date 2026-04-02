<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

echo "<h1>Diagnóstico do Módulo de Grupos/Células</h1>";
echo "<p>Este script verifica se os arquivos necessários foram enviados e se o banco de dados foi atualizado.</p>";

// 1. Verificação de Arquivos
echo "<h3>1. Verificação de Arquivos no Servidor</h3>";
$files = [
    'src/controllers/GroupController.php',
    'src/views/admin/groups/index.php',
    'src/views/admin/groups/create.php',
    'src/views/admin/groups/edit.php',
    'src/views/admin/groups/show.php',
    'src/views/layout/header.php',
    'public/index.php',
    'database/migrations/20260311_123000_create_groups_tables.php'
];

echo "<ul>";
$allFilesOk = true;
foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    if (file_exists($path)) {
        echo "<li style='color:green'><strong>OK</strong>: $f</li>";
    } else {
        echo "<li style='color:red'><strong>ERRO</strong>: Arquivo não encontrado: $f (Verifique se fez o upload)</li>";
        $allFilesOk = false;
    }
}
echo "</ul>";

// 2. Verificação de Conteúdo Crítico (Header e Index)
echo "<h3>2. Verificação de Código (Amostragem)</h3>";
if (file_exists(__DIR__ . '/src/views/layout/header.php')) {
    $headerContent = file_get_contents(__DIR__ . '/src/views/layout/header.php');
    if (strpos($headerContent, 'admin/groups') !== false) {
        echo "<p style='color:green'>OK: Link para 'admin/groups' encontrado no header.php</p>";
    } else {
        echo "<p style='color:red'>ERRO: Link para 'admin/groups' NÃO encontrado no header.php. O arquivo pode estar desatualizado.</p>";
    }
}

if (file_exists(__DIR__ . '/public/index.php')) {
    $indexContent = file_get_contents(__DIR__ . '/public/index.php');
    if (strpos($indexContent, 'GroupController') !== false) {
        echo "<p style='color:green'>OK: Rotas para 'GroupController' encontradas no index.php</p>";
    } else {
        echo "<p style='color:red'>ERRO: Rotas para 'GroupController' NÃO encontradas no index.php. O arquivo pode estar desatualizado.</p>";
    }
}

// 3. Verificação de Banco de Dados
echo "<h3>3. Verificação de Banco de Dados</h3>";
try {
    $db = (new Database())->connect();
    
    // Tabela groups
    try {
        $db->query("SELECT 1 FROM `groups` LIMIT 1");
        echo "<p style='color:green'>OK: Tabela 'groups' existe e está acessível.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>ERRO: Tabela 'groups' NÃO existe. A migração não foi executada.</p>";
        echo "<p><strong>Ação Recomendada:</strong> Acesse <a href='/migrate.php' target='_blank'>/migrate.php</a> para criar as tabelas.</p>";
    }

    // Tabela migrations
    echo "<h4>Últimas Migrações Registradas:</h4>";
    try {
        $migs = $db->query("SELECT migration FROM migrations ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
        echo "<ul>";
        foreach ($migs as $m) {
            $isGroups = strpos($m, 'groups') !== false;
            $style = $isGroups ? "style='font-weight:bold; color:blue'" : "";
            echo "<li $style>$m " . ($isGroups ? "(Migração de Grupos)" : "") . "</li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Erro ao ler tabela migrations: " . $e->getMessage() . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>Erro Crítico de Conexão com Banco: " . $e->getMessage() . "</p>";
}

echo "<br><hr><p><em>Após verificar, apague este arquivo do servidor por segurança.</em></p>";

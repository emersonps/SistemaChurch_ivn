<?php
// public/setup_ebd.php

require_once __DIR__ . '/../config/database.php';

echo "<h1>Configurando EBD...</h1>";

try {
    $db = (new Database())->connect();
    
    // 1. Criar Tabelas
    $queries = [
        "CREATE TABLE IF NOT EXISTS ebd_classes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            min_age INTEGER,
            max_age INTEGER,
            congregation_id INTEGER,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (congregation_id) REFERENCES congregations(id)
        )",
        "CREATE TABLE IF NOT EXISTS ebd_students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            class_id INTEGER NOT NULL,
            member_id INTEGER NOT NULL,
            enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT 'active',
            FOREIGN KEY (class_id) REFERENCES ebd_classes(id),
            FOREIGN KEY (member_id) REFERENCES members(id)
        )",
        "CREATE TABLE IF NOT EXISTS ebd_teachers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            class_id INTEGER NOT NULL,
            member_id INTEGER NOT NULL,
            assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT 'active',
            FOREIGN KEY (class_id) REFERENCES ebd_classes(id),
            FOREIGN KEY (member_id) REFERENCES members(id)
        )",
        "CREATE TABLE IF NOT EXISTS ebd_lessons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            class_id INTEGER NOT NULL,
            lesson_date DATE NOT NULL,
            topic TEXT,
            notes TEXT,
            visitors_count INTEGER DEFAULT 0,
            bibles_count INTEGER DEFAULT 0,
            magazines_count INTEGER DEFAULT 0,
            offerings REAL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_by INTEGER,
            FOREIGN KEY (class_id) REFERENCES ebd_classes(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )",
        "CREATE TABLE IF NOT EXISTS ebd_attendance (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lesson_id INTEGER NOT NULL,
            student_id INTEGER NOT NULL,
            present INTEGER DEFAULT 0,
            brought_bible INTEGER DEFAULT 0,
            brought_magazine INTEGER DEFAULT 0,
            FOREIGN KEY (lesson_id) REFERENCES ebd_lessons(id),
            FOREIGN KEY (student_id) REFERENCES members(id)
        )"
    ];

    foreach ($queries as $sql) {
        $db->exec($sql);
    }
    echo "<p>Tabelas verificadas com sucesso.</p>";

    // 2. Inserir Permissões
    $perms = [
        ['ebd.view', 'Ver EBD', 'Visualizar módulo da Escola Bíblica'],
        ['ebd.manage', 'Gerenciar EBD', 'Gerenciar classes, alunos e professores'],
        ['ebd.lessons', 'Lançar Aulas/Chamada', 'Registrar aulas, presença e ofertas']
    ];

    $stmt = $db->prepare("INSERT OR IGNORE INTO permissions (slug, label, description) VALUES (?, ?, ?)");
    foreach ($perms as $p) {
        $stmt->execute([$p[0], $p[1], $p[2]]);
    }
    echo "<p>Permissões inseridas no catálogo.</p>";

    // 3. Atribuir ao usuário atual (Admin)
    // Se o usuário já estiver logado, pegamos o ID da sessão, senão tentamos o ID 1
    session_start();
    $userId = $_SESSION['user_id'] ?? 1;

    $stmtUser = $db->prepare("INSERT OR IGNORE INTO user_permissions (user_id, permission_slug) VALUES (?, ?)");
    
    foreach ($perms as $p) {
        $stmtUser->execute([$userId, $p[0]]);
    }
    
    echo "<p style='color:green'><strong>SUCESSO!</strong> Permissões atribuídas ao usuário ID: $userId.</p>";
    echo "<p>Pode voltar ao painel e atualizar a página (F5).</p>";
    echo "<a href='/admin'>Voltar ao Painel</a>";

} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

<?php
// scripts/setup_ebd_local.php

require_once __DIR__ . '/../config/database.php';

echo "Configurando EBD localmente...\n";

try {
    $db = (new Database())->connect();
    
    // 1. Criar Tabelas (caso não existam)
    $db->exec("CREATE TABLE IF NOT EXISTS ebd_classes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        min_age INTEGER,
        max_age INTEGER,
        congregation_id INTEGER,
        status TEXT DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (congregation_id) REFERENCES congregations(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS ebd_students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        class_id INTEGER NOT NULL,
        member_id INTEGER NOT NULL,
        enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status TEXT DEFAULT 'active',
        FOREIGN KEY (class_id) REFERENCES ebd_classes(id),
        FOREIGN KEY (member_id) REFERENCES members(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS ebd_teachers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        class_id INTEGER NOT NULL,
        member_id INTEGER NOT NULL,
        assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status TEXT DEFAULT 'active',
        FOREIGN KEY (class_id) REFERENCES ebd_classes(id),
        FOREIGN KEY (member_id) REFERENCES members(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS ebd_lessons (
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
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS ebd_attendance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lesson_id INTEGER NOT NULL,
        student_id INTEGER NOT NULL,
        present INTEGER DEFAULT 0,
        brought_bible INTEGER DEFAULT 0,
        brought_magazine INTEGER DEFAULT 0,
        FOREIGN KEY (lesson_id) REFERENCES ebd_lessons(id),
        FOREIGN KEY (student_id) REFERENCES members(id)
    )");
    
    echo "Tabelas verificadas.\n";

    // 2. Inserir Permissões
    $perms = [
        ['ebd.view', 'Ver EBD', 'Visualizar módulo da Escola Bíblica'],
        ['ebd.manage', 'Gerenciar EBD', 'Gerenciar classes, alunos e professores'],
        ['ebd.lessons', 'Lançar Aulas/Chamada', 'Registrar aulas, presença e ofertas']
    ];

    $stmt = $db->prepare("INSERT OR IGNORE INTO permissions (slug, label, description) VALUES (?, ?, ?)");
    foreach ($perms as $p) {
        $stmt->execute($p);
    }
    echo "Permissões inseridas.\n";

    // 3. Dar permissão para o usuário Admin (ID 1) e Dev (ID 2)
    // Se a tabela user_permissions usar (user_id, permission_slug) como PK
    $stmtUser = $db->prepare("INSERT OR IGNORE INTO user_permissions (user_id, permission_slug) VALUES (?, ?)");
    
    foreach ([1, 2] as $userId) {
        foreach ($perms as $p) {
            $stmtUser->execute([$userId, $p[0]]);
        }
    }
    echo "Permissões atribuídas aos usuários 1 (admin) e 2 (dev).\n";
    
    echo "Concluído! Pode testar agora.\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

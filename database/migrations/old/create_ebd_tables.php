<?php
// database/migrations/create_ebd_tables.php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = (new Database())->connect();
    
    // 1. EBD Classes
    // congregation_id is optional, if null = global class? Usually EBD is per congregation.
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
    echo "Tabela 'ebd_classes' verificada.\n";

    // 2. EBD Students (Enrollment)
    // Links a member to a class
    $db->exec("CREATE TABLE IF NOT EXISTS ebd_students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        class_id INTEGER NOT NULL,
        member_id INTEGER NOT NULL,
        enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status TEXT DEFAULT 'active',
        FOREIGN KEY (class_id) REFERENCES ebd_classes(id),
        FOREIGN KEY (member_id) REFERENCES members(id)
    )");
    echo "Tabela 'ebd_students' verificada.\n";

    // 3. EBD Teachers (Enrollment)
    // Links a member as a teacher to a class
    $db->exec("CREATE TABLE IF NOT EXISTS ebd_teachers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        class_id INTEGER NOT NULL,
        member_id INTEGER NOT NULL,
        assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status TEXT DEFAULT 'active',
        FOREIGN KEY (class_id) REFERENCES ebd_classes(id),
        FOREIGN KEY (member_id) REFERENCES members(id)
    )");
    echo "Tabela 'ebd_teachers' verificada.\n";

    // 4. EBD Lessons (Aulas/Encontros)
    // Records a specific class session (usually every Sunday)
    $db->exec("CREATE TABLE IF NOT EXISTS ebd_lessons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        class_id INTEGER NOT NULL,
        lesson_date DATE NOT NULL,
        topic TEXT, -- Tema da aula
        notes TEXT,
        visitors_count INTEGER DEFAULT 0,
        bibles_count INTEGER DEFAULT 0,
        magazines_count INTEGER DEFAULT 0,
        offerings REAL DEFAULT 0, -- Valor total da oferta da classe neste dia
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by INTEGER,
        FOREIGN KEY (class_id) REFERENCES ebd_classes(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    echo "Tabela 'ebd_lessons' verificada.\n";

    // 5. EBD Attendance (Chamada Individual)
    // Links a lesson to a student (presence)
    $db->exec("CREATE TABLE IF NOT EXISTS ebd_attendance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lesson_id INTEGER NOT NULL,
        student_id INTEGER NOT NULL,
        present INTEGER DEFAULT 0, -- 1 = Presente, 0 = Ausente
        brought_bible INTEGER DEFAULT 0, -- Trouxe bíblia?
        brought_magazine INTEGER DEFAULT 0, -- Trouxe revista?
        FOREIGN KEY (lesson_id) REFERENCES ebd_lessons(id),
        FOREIGN KEY (student_id) REFERENCES members(id)
    )");
    echo "Tabela 'ebd_attendance' verificada.\n";

    // MySQL Compatibility Script (Create separate SQL file for user)
    $mysql_sql = "
    -- EBD Tables for MySQL
    
    CREATE TABLE IF NOT EXISTS `ebd_classes` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(255) NOT NULL,
      `description` TEXT,
      `min_age` INT(11),
      `max_age` INT(11),
      `congregation_id` INT(11),
      `status` VARCHAR(50) DEFAULT 'active',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `congregation_id` (`congregation_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `ebd_students` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `class_id` INT(11) NOT NULL,
      `member_id` INT(11) NOT NULL,
      `enrolled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `status` VARCHAR(50) DEFAULT 'active',
      PRIMARY KEY (`id`),
      KEY `class_id` (`class_id`),
      KEY `member_id` (`member_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `ebd_teachers` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `class_id` INT(11) NOT NULL,
      `member_id` INT(11) NOT NULL,
      `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `status` VARCHAR(50) DEFAULT 'active',
      PRIMARY KEY (`id`),
      KEY `class_id` (`class_id`),
      KEY `member_id` (`member_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `ebd_lessons` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `class_id` INT(11) NOT NULL,
      `lesson_date` DATE NOT NULL,
      `topic` TEXT,
      `notes` TEXT,
      `visitors_count` INT(11) DEFAULT 0,
      `bibles_count` INT(11) DEFAULT 0,
      `magazines_count` INT(11) DEFAULT 0,
      `offerings` DOUBLE DEFAULT 0,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `created_by` INT(11),
      PRIMARY KEY (`id`),
      KEY `class_id` (`class_id`),
      KEY `created_by` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `ebd_attendance` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `lesson_id` INT(11) NOT NULL,
      `student_id` INT(11) NOT NULL,
      `present` TINYINT(1) DEFAULT 0,
      `brought_bible` TINYINT(1) DEFAULT 0,
      `brought_magazine` TINYINT(1) DEFAULT 0,
      PRIMARY KEY (`id`),
      KEY `lesson_id` (`lesson_id`),
      KEY `student_id` (`student_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    file_put_contents(__DIR__ . '/../update_ebd_mysql.sql', $mysql_sql);
    echo "Arquivo SQL para MySQL gerado em 'database/update_ebd_mysql.sql'.\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

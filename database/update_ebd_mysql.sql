
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
    
    -- Permissões para EBD
    INSERT IGNORE INTO `permissions` (`slug`, `label`, `description`) VALUES
    ('ebd.view', 'Ver EBD', 'Visualizar módulo da Escola Bíblica'),
    ('ebd.manage', 'Gerenciar EBD', 'Gerenciar classes, alunos e professores'),
    ('ebd.lessons', 'Lançar Aulas/Chamada', 'Registrar aulas, presença e ofertas');

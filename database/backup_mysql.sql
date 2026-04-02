-- MySQL Dump generated from SQLite
-- Generated: 2026-03-06 19:23:31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(255) NULL DEFAULT 'admin',
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `congregation_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `users`
INSERT INTO `users` VALUES ('1', 'admin', '$2y$10$/V.PWrZT.DfC2SErlSK/I.XgQBBRt0Ta3viahAai8hhT.hq70QO9a', 'admin', '2026-03-04 00:03:59', NULL), ('2', 'dev', '$2y$10$XKzYETa971iZ7pkNk/sBm.sp2BbbdnXwgw.d8lhSgHrdnFMJXvOVy', 'developer', '2026-03-05 00:26:20', NULL), ('3', 'secretaria_n2', '$2y$10$Dh4Oybo9krwROkyMxRnmh.9QdvIsk3b44fsG04IK01nOUJ3kEYSGi', 'secretary', '2026-03-05 23:08:53', '1'), ('4', 'dirigente_n2', '$2y$10$smBAAeb.iXkg8BQVCQNvbutNvs4al76VeUjobYW3yddtL28ULFahy', 'secretary', '2026-03-06 00:40:09', '1');

-- Table structure for `congregations`
DROP TABLE IF EXISTS `congregations`;
CREATE TABLE `congregations` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `name` TEXT NOT NULL,
  `address` TEXT NULL DEFAULT NULL,
  `leader_name` TEXT NULL DEFAULT NULL,
  `type` VARCHAR(255) NULL DEFAULT 'congregation',
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `opening_date` DATETIME NULL DEFAULT NULL,
  `phone` VARCHAR(255) NULL DEFAULT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `photo` TEXT NULL DEFAULT NULL,
  `zip_code` VARCHAR(255) NULL DEFAULT NULL,
  `city` VARCHAR(255) NULL DEFAULT NULL,
  `state` VARCHAR(255) NULL DEFAULT NULL,
  `service_schedule` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `congregations`
INSERT INTO `congregations` VALUES ('1', 'IMPVC NaÃƒÂ§ÃƒÂ£o 2', 'Rua Ademar de Barros, 20 - Sta Cruz', 'Ev. Patrik', 'headquarters', '2026-03-04 00:03:59', '2000-10-10', '', '', '69a87e0f636c1.jpg', '69028347', 'Manaus', 'AM', NULL), ('2', 'IMPVC Sede', 'Rua Brasil, 2002 - Pq das NaÃƒÂ§ÃƒÂµes', 'Pr. ClÃƒÂ­stenes', 'congregation', '2026-03-04 00:13:21', '1999-01-01', '', '', '69a87e1cc9cdf.jpg', '69028347', 'Manaus', 'AM', NULL), ('3', 'IMPVC - NaÃƒÂ§ÃƒÂ£o 1', 'Rua Beco 1, 30', 'Pr. Samuel', 'congregation', '2026-03-04 18:55:16', '1998-01-01', '(92) 99399-3999', 'nacao1@impvc.com.br', '69a88014d129b.jpg', '69033300', 'Manaus', 'AM', '[{\"day\":\"Domingo\",\"name\":\"EBD\",\"start_time\":\"08:00\",\"end_time\":\"10:00\"},{\"day\":\"Domingo\",\"name\":\"Culto de Fam\\u00edlia\",\"start_time\":\"18:00\",\"end_time\":\"20:00\"},{\"day\":\"Ter\\u00e7a\",\"name\":\"Culto de Doutrina\",\"start_time\":\"19:00\",\"end_time\":\"21:00\"},{\"day\":\"Quinta\",\"name\":\"Culto de Campanha\",\"start_time\":\"19:00\",\"end_time\":\"21:00\"},{\"day\":\"Sexta\",\"name\":\"Ora\\u00e7\\u00e3o\",\"start_time\":\"19:00\",\"end_time\":\"20:00\"},{\"day\":\"S\\u00e1bado\",\"name\":\"C\\u00edrculo de Ora\\u00e7\\u00e3o\",\"start_time\":\"06:00\",\"end_time\":\"08:00\"}]');

-- Table structure for `members`
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `congregation_id` INT(11) NULL DEFAULT NULL,
  `name` TEXT NOT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `phone` VARCHAR(255) NULL DEFAULT NULL,
  `birth_date` DATETIME NULL DEFAULT NULL,
  `baptism_date` DATETIME NULL DEFAULT NULL,
  `is_baptized` INT(11) NULL DEFAULT 0,
  `status` VARCHAR(255) NULL DEFAULT 'active',
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `gender` VARCHAR(255) NULL DEFAULT NULL,
  `cpf` VARCHAR(255) NULL DEFAULT NULL,
  `rg` VARCHAR(255) NULL DEFAULT NULL,
  `marital_status` VARCHAR(255) NULL DEFAULT NULL,
  `address` TEXT NULL DEFAULT NULL,
  `address_number` TEXT NULL DEFAULT NULL,
  `neighborhood` TEXT NULL DEFAULT NULL,
  `complement` TEXT NULL DEFAULT NULL,
  `reference_point` TEXT NULL DEFAULT NULL,
  `zip_code` VARCHAR(255) NULL DEFAULT NULL,
  `state` VARCHAR(255) NULL DEFAULT NULL,
  `city` VARCHAR(255) NULL DEFAULT NULL,
  `role` VARCHAR(255) NULL DEFAULT NULL,
  `nationality` TEXT NULL DEFAULT NULL,
  `birthplace` TEXT NULL DEFAULT NULL,
  `father_name` TEXT NULL DEFAULT NULL,
  `mother_name` TEXT NULL DEFAULT NULL,
  `children_count` INT(11) NULL DEFAULT 0,
  `profession` TEXT NULL DEFAULT NULL,
  `church_origin` TEXT NULL DEFAULT NULL,
  `admission_method` TEXT NULL DEFAULT NULL,
  `admission_date` DATETIME NULL DEFAULT NULL,
  `exit_date` DATETIME NULL DEFAULT NULL,
  `is_tither` INT(11) NULL DEFAULT 0,
  `photo` TEXT NULL DEFAULT NULL,
  `password` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `members`
INSERT INTO `members` VALUES ('3', '1', 'Emerson Pinheiro de Souza', 'emersonline2007@gmail.com', '(92) 99479-1168', '1982-03-07', '1999-01-01', '1', 'Congregando', '2026-03-04 19:24:45', 'M', '852.585.982-68', '15111333', 'Casado(a)', 'Rua Ademar de Barros', '20', 'Flores', 'Sta Cruz', '', '69028347', 'AM', 'Manaus', 'Evangelista', 'Brasileira', 'Manaus', '', 'Maria Pinheiro de Souza', '3', 'Analista de Sistemas', 'IPA', 'AclamaÃƒÂ§ÃƒÂ£o', '2026-03-04', NULL, '0', '69a886fd089b2.jpeg', '$2y$10$aWvx4e4pVPMfR5C7KWotA.hccB4N0uaYlZEf3olZPBB9VEhICEJza'), ('4', '3', 'Jesus da Silva Lima', '', '', '1982-01-01', NULL, '0', 'Congregando', '2026-03-04 20:21:26', 'M', '', '', 'Solteiro(a)', '', '', '', '', '', '', '', '', 'Membro', 'Brasileira', 'Manaus', 'JosÃƒÂ© da Silva', 'Maria da Silva', '0', '', '', 'AclamaÃƒÂ§ÃƒÂ£o', '2026-03-04', NULL, '0', '69a894463937c.jpeg', NULL), ('5', '2', 'MÃƒÂ¡rio Lima Costa', '', '', '2002-01-10', NULL, '0', 'Congregando', '2026-03-04 20:22:39', 'M', '', '', '', '', '', '', '', '', '', '', '', 'Membro', 'Brasileira', '', '', '', '0', '', '', 'AclamaÃƒÂ§ÃƒÂ£o', '2026-03-04', NULL, '0', '69a8948f5ff9f.jfif', NULL);

-- Table structure for `tithes`
DROP TABLE IF EXISTS `tithes`;
CREATE TABLE `tithes` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `member_id` INT(11) NULL DEFAULT NULL,
  `amount` DOUBLE NOT NULL,
  `payment_date` DATETIME NOT NULL,
  `payment_method` TEXT NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `type` VARCHAR(255) NULL DEFAULT 'dizimo',
  `service_report_id` INT(11) NULL DEFAULT NULL,
  `giver_name` TEXT NULL DEFAULT NULL,
  `congregation_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `tithes`
INSERT INTO `tithes` VALUES ('2', '3', '30', '2026-03-04', 'PIX', '', '2026-03-04 20:00:14', 'DÃƒÂ­zimo', NULL, NULL, '1'), ('3', '3', '10', '2026-03-04', 'PIX', '', '2026-03-04 20:00:59', 'Oferta', NULL, NULL, '1'), ('4', '4', '10', '2026-02-10', 'Dinheiro', '', '2026-03-04 20:01:13', 'Oferta', NULL, NULL, '3'), ('5', '5', '10', '2026-03-04', 'Dinheiro', '', '2026-03-04 20:01:24', 'Oferta', NULL, NULL, '2'), ('6', '5', '5', '2026-03-10', 'PIX', '', '2026-03-04 21:14:57', 'Oferta', NULL, NULL, '2'), ('7', NULL, '100', '2026-03-05', 'Dinheiro', 'Via RelatÃƒÂ³rio de Culto', '2026-03-05 23:34:14', 'DÃƒÂ­zimo', '1', 'Maria da Silva', '1'), ('8', NULL, '250', '2026-03-05', 'Dinheiro', 'Via RelatÃƒÂ³rio de Culto', '2026-03-05 23:34:14', 'DÃƒÂ­zimo', '1', 'Paulo da Silva', '1'), ('9', NULL, '34.44', '2026-03-05', 'Dinheiro', 'Via RelatÃƒÂ³rio de Culto', '2026-03-05 23:34:14', 'Oferta', '1', 'Marcos Lima', '1'), ('10', NULL, '56.99', '2026-03-05', 'Dinheiro', 'Via RelatÃƒÂ³rio de Culto', '2026-03-05 23:34:14', 'Oferta', '1', 'Geral', '1');

-- Table structure for `events`
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `title` TEXT NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `event_date` DATETIME NULL DEFAULT NULL,
  `location` TEXT NULL DEFAULT NULL,
  `type` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(255) NULL DEFAULT 'active',
  `recurring_days` TEXT NULL DEFAULT NULL,
  `end_time` TEXT NULL DEFAULT NULL,
  `congregation_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `events`
INSERT INTO `events` VALUES ('1', 'EDB', 'EBD Ã¢â‚¬â€œ Escola BÃƒÂ­blica Dominical | Domingos\r\nUm tempo de ensino sistemÃƒÂ¡tico da Palavra, aprendizado bÃƒÂ­blico e crescimento espiritual para todas as idades. Venha estudar as Escrituras e fortalecer sua fÃƒÂ©.', '1970-01-01 08:00', 'Templo Sede', 'culto', '2026-03-04 00:37:37', 'active', '[\"Domingo\"]', NULL, NULL), ('2', 'Culto de Doutrina', 'Culto de Doutrina Ã¢â‚¬â€œ Domingos, 19h00 ÃƒÂ s 21h00\r\nUm tempo dedicado ao ensino firme da Palavra, crescimento espiritual e fortalecimento dos fundamentos da fÃƒÂ©. Participe e aprofunde-se na verdade bÃƒÂ­blica.', '1970-01-01 19:00', 'Templo Sede', 'culto', '2026-03-04 00:44:28', 'active', '[\"Ter\\u00e7a\"]', '21:00', NULL), ('3', 'Culto de FamÃƒÂ­lia', 'Culto da FamÃƒÂ­lia Ã¢â‚¬â€œ Domingos, 18h ÃƒÂ s 20h\r\n\r\nUm momento de comunhÃƒÂ£o, adoraÃƒÂ§ÃƒÂ£o e Palavra para fortalecer os lares e edificar a famÃƒÂ­lia nos princÃƒÂ­pios de Deus. Traga sua famÃƒÂ­lia e participe.', '1970-01-01 18:00', 'Templo Sede', 'culto', '2026-03-04 00:50:33', 'active', '[\"Domingo\"]', NULL, NULL), ('4', 'Culto de OraÃƒÂ§ÃƒÂ£o', 'Culto de OraÃƒÂ§ÃƒÂ£o Ã¢â‚¬â€œ Domingos, 19h00 ÃƒÂ s 21h00\r\nUm momento de busca, intercessÃƒÂ£o e entrega diante de Deus. Venha fortalecer sua fÃƒÂ© e renovar suas forÃƒÂ§as em oraÃƒÂ§ÃƒÂ£o.', '1970-01-01 19:00', 'Templo Sede', 'culto', '2026-03-04 01:05:50', 'active', '[\"Quinta\"]', NULL, NULL), ('5', '1o AniversÃƒÂ¡rio da IMPVC - NaÃƒÂ§ÃƒÂ£o 1', '', '2026-08-20 19:00', 'Templo Sede', 'aniversario', '2026-03-04 01:19:53', 'active', '[\"Domingo\",\"S\\u00e1bado\"]', NULL, NULL), ('6', 'Congresso de Jovens ', 'Congresso de Jovens Ã¢â‚¬â€œ IMPVC\r\n\r\nUm tempo especial de avivamento, ensino e direcionamento para uma geraÃƒÂ§ÃƒÂ£o que deseja viver com propÃƒÂ³sito. O Congresso de Jovens da IMPVC Ã¢â‚¬â€œ Igreja EvangÃƒÂ©lica Igreja Missionária Pentecostal Vidas para Cristo Senhor das NaÃƒÂ§ÃƒÂµes ÃƒÂ© um chamado ao compromisso, ÃƒÂ  santidade e ao aprofundamento na Palavra.\r\n\r\nSerÃƒÂ£o dias de adoraÃƒÂ§ÃƒÂ£o, ministraÃƒÂ§ÃƒÂµes impactantes e comunhÃƒÂ£o, fortalecendo a identidade cristÃƒÂ£ e preparando jovens para influenciar sua geraÃƒÂ§ÃƒÂ£o com fÃƒÂ©, carÃƒÂ¡ter e ousadia.', '2026-10-10 19:00', 'Templo Sede', 'congresso', '2026-03-04 01:20:19', 'active', '[\"S\\u00e1bado\"]', NULL, NULL), ('7', 'CULTO DE JOVENS - MADUREIRA', 'Fomos convidados e convido toda a igreja a participar consco.', '2026-04-10 19:00', 'Rua Bela Vista, Compensa - N20', 'outro', '2026-03-04 21:20:05', 'active', '[\"Domingo\",\"S\\u00e1bado\"]', '21:00', NULL);

-- Table structure for `photo_albums`
DROP TABLE IF EXISTS `photo_albums`;
CREATE TABLE `photo_albums` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `title` TEXT NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `event_date` DATETIME NULL DEFAULT NULL,
  `location` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `photo_albums`
INSERT INTO `photo_albums` VALUES ('1', 'Congresso de Jovens 2024', 'Foram 2 dias de adoraÃƒÂ§ÃƒÂ£o, ministraÃƒÂ§ÃƒÂµes impactantes e comunhÃƒÂ£o, fortalecendo a identidade cristÃƒÂ£ e preparando jovens para influenciar sua geraÃƒÂ§ÃƒÂ£o com fÃƒÂ©, carÃƒÂ¡ter e ousadia.', '2025-02-20', 'Templo Sede', '2026-03-04 01:38:33'), ('2', 'AniversÃƒÂ¡rio NaÃƒÂ§ÃƒÂ£o 2', 'Foram dias de adoraÃƒÂ§ÃƒÂ£o, ministraÃƒÂ§ÃƒÂµes impactantes e comunhÃƒÂ£o, fortalecendo a identidade cristÃƒÂ£ e preparando jovens para influenciar sua geraÃƒÂ§ÃƒÂ£o com fÃƒÂ©, carÃƒÂ¡ter e ousadia.', '2025-02-20', 'NaÃƒÂ§ÃƒÂ£o 2', '2026-03-04 01:42:18'), ('3', 'SECOB', 'FOI UM PERÃƒÂODO DE GREANDE APRENDIZADO!', '2026-02-10', 'TEMPLO SEDE', '2026-03-04 21:21:46');

-- Table structure for `photos`
DROP TABLE IF EXISTS `photos`;
CREATE TABLE `photos` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `album_id` INT(11) NOT NULL,
  `filename` TEXT NOT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `photos`
INSERT INTO `photos` VALUES ('1', '1', '69a78d404b529.jpg', '2026-03-04 01:39:12'), ('2', '1', '69a78d4add95a.jpg', '2026-03-04 01:39:23'), ('3', '1', '69a78d527e27c.jpg', '2026-03-04 01:39:30'), ('4', '1', '69a78d5ccbe9f.jpg', '2026-03-04 01:39:41'), ('5', '1', '69a78d672609d.jpg', '2026-03-04 01:39:51'), ('6', '2', '69a78e09c5f2a.jpg', '2026-03-04 01:42:33'), ('7', '2', '69a78e1505c9a.jpg', '2026-03-04 01:42:45'), ('8', '2', '69a78e2081f39.jpg', '2026-03-04 01:42:56'), ('9', '2', '69a78e2ba780e.jpg', '2026-03-04 01:43:07'), ('10', '2', '69a78e3771d23.jpg', '2026-03-04 01:43:19'), ('11', '1', '69a78f9fe8cd8.jpg', '2026-03-04 01:49:20'), ('12', '1', '69a78faa34deb.jpg', '2026-03-04 01:49:30'), ('13', '2', '69a78fdf3ebb3.jpg', '2026-03-04 01:50:23'), ('14', '2', '69a78fe965864.jpg', '2026-03-04 01:50:33'), ('15', '3', '69a8a27a90c47.jpeg', '2026-03-04 21:22:02'), ('16', '3', '69a8a284dee60.jpeg', '2026-03-04 21:22:12'), ('17', '3', '69a8a28ab5c0c.jpeg', '2026-03-04 21:22:18');

-- Table structure for `system_payments`
DROP TABLE IF EXISTS `system_payments`;
CREATE TABLE `system_payments` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `reference_month` VARCHAR(255) NOT NULL,
  `amount` VARCHAR(255) NULL DEFAULT NULL,
  `payment_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(255) NULL DEFAULT 'paid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `system_payments`
INSERT INTO `system_payments` VALUES ('8', '2026-03', '185', '2026-03-04 22:08:33', 'paid'), ('9', '2026-04', '59.99', '2026-04-05 00:00:00', 'pending');

-- Table structure for `studies`
DROP TABLE IF EXISTS `studies`;
CREATE TABLE `studies` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `title` TEXT NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `file_path` TEXT NOT NULL,
  `congregation_id` INT(11) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `studies`
INSERT INTO `studies` VALUES ('1', 'Defendendo a Verdade - A Trindade ÃƒÂ© BÃƒÂ­blica.pdf', '', '69a989c74e430.pdf', NULL, '2026-03-05 13:48:55'), ('2', 'Namoro CristÃƒÂ£o', '', '69a98a2bb07f4.pdf', NULL, '2026-03-05 13:50:35'), ('3', 'Daniel 11', '', '69a98d6d17886.pdf', NULL, '2026-03-05 14:04:29');

-- Table structure for `banners`
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `title` TEXT NOT NULL,
  `image_path` TEXT NOT NULL,
  `link` TEXT NULL DEFAULT NULL,
  `display_order` INT(11) NULL DEFAULT 0,
  `active` INT(11) NULL DEFAULT 1,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `banners`
INSERT INTO `banners` VALUES ('3', 'Campanha', 'uploads/banners/69a9ae6e35aba_dia_7.jpg', '', '0', '0', '2026-03-05 16:25:18'), ('4', 'Campanha', 'uploads/banners/69a9aeb46b66f_5 dia.jpg', '', '1', '0', '2026-03-05 16:26:28'), ('5', 'Campanha', 'uploads/banners/69a9aee6dcce5_Banner Resgatando Vidas 4-7.jpg', '', '2', '0', '2026-03-05 16:27:19'), ('6', 'Culto de Jovens', 'uploads/banners/69a9af2ba1b78_Culto de Jovens.jpg', '', '3', '0', '2026-03-05 16:28:27'), ('7', 'EBD', 'uploads/banners/69a9afc54d802_EBD.jpg', '', '4', '0', '2026-03-05 16:31:01');

-- Table structure for `service_reports`
DROP TABLE IF EXISTS `service_reports`;
CREATE TABLE `service_reports` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `congregation_id` INT(11) NOT NULL,
  `date` DATETIME NOT NULL,
  `time` VARCHAR(255) NOT NULL,
  `leader_name` TEXT NULL DEFAULT NULL,
  `preacher_name` TEXT NULL DEFAULT NULL,
  `attendance_men` INT(11) NULL DEFAULT 0,
  `attendance_women` INT(11) NULL DEFAULT 0,
  `attendance_youth` INT(11) NULL DEFAULT 0,
  `attendance_children` INT(11) NULL DEFAULT 0,
  `attendance_visitors` INT(11) NULL DEFAULT 0,
  `total_attendance` INT(11) NULL DEFAULT 0,
  `notes` TEXT NULL DEFAULT NULL,
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `service_reports`
INSERT INTO `service_reports` VALUES ('1', '1', '2026-03-05', '19:00', 'Ev. Patrik', 'Ev. Patrik', '10', '10', '20', '10', '5', '55', '', '3', '2026-03-05 23:34:14');

-- Table structure for `service_people_actions`
DROP TABLE IF EXISTS `service_people_actions`;
CREATE TABLE `service_people_actions` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `service_report_id` INT(11) NOT NULL,
  `name` TEXT NOT NULL,
  `action_type` TEXT NOT NULL,
  `observation` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `service_people_actions`
INSERT INTO `service_people_actions` VALUES ('1', '1', 'Maria Silva', 'Visitante', 'Igreja Missionária Pentecostal Vidas para Cristo'), ('2', '1', 'Manoel Lira', 'Aceitou Jesus', ''), ('3', '1', 'Joel da Silva Santos', 'Reconciliado', ''), ('4', '1', 'Mariele Lima', 'Disciplinado', ''), ('5', '1', 'Carla Lima', 'Desligamento', 'Despedida'), ('6', '1', 'Paulo da Silva', 'Visitante', '');

-- Table structure for `permissions`
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `slug` TEXT NOT NULL,
  `label` TEXT NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `permissions`
INSERT INTO `permissions` VALUES ('1', 'dashboard.view', 'Ver Dashboard', 'Acesso ao painel principal'), ('2', 'members.view', 'Ver Membros', 'Visualizar lista de membros'), ('3', 'members.manage', 'Gerenciar Membros', 'Criar, editar e excluir membros'), ('4', 'congregations.view', 'Ver CongregaÃƒÂ§ÃƒÂµes', 'Visualizar lista de congregaÃƒÂ§ÃƒÂµes'), ('5', 'congregations.manage', 'Gerenciar CongregaÃƒÂ§ÃƒÂµes', 'Criar, editar e excluir congregaÃƒÂ§ÃƒÂµes'), ('6', 'financial.view', 'Ver FinanÃƒÂ§as', 'Visualizar dÃƒÂ­zimos e ofertas'), ('7', 'financial.manage', 'Gerenciar FinanÃƒÂ§as', 'LanÃƒÂ§ar dÃƒÂ­zimos e ofertas'), ('8', 'events.view', 'Ver Eventos', 'Visualizar agenda de eventos'), ('9', 'events.manage', 'Gerenciar Eventos', 'Criar, editar e excluir eventos'), ('10', 'gallery.view', 'Ver Galeria', 'Visualizar ÃƒÂ¡lbuns de fotos'), ('11', 'gallery.manage', 'Gerenciar Galeria', 'Upload e exclusÃƒÂ£o de fotos'), ('12', 'banners.view', 'Ver Banners', 'Visualizar banners do site'), ('13', 'banners.manage', 'Gerenciar Banners', 'Upload e ediÃƒÂ§ÃƒÂ£o de banners'), ('14', 'studies.view', 'Ver Estudos', 'Visualizar estudos bÃƒÂ­blicos'), ('15', 'studies.manage', 'Gerenciar Estudos', 'Publicar estudos bÃƒÂ­blicos'), ('16', 'service_reports.view', 'Ver RelatÃƒÂ³rios de Culto', 'Visualizar relatÃƒÂ³rios'), ('17', 'service_reports.manage', 'Gerenciar RelatÃƒÂ³rios', 'Criar relatÃƒÂ³rios de culto'), ('18', 'users.manage', 'Gerenciar UsuÃƒÂ¡rios', 'Criar e editar usuÃƒÂ¡rios do sistema'), ('19', 'system_payments.view', 'Ver Pagamento Sistema', 'Visualizar status do pagamento do sistema');

-- Table structure for `user_permissions`
DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `user_id` INT(11) NOT NULL  AUTO_INCREMENT,
  `permission_slug` TEXT NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `user_permissions`
INSERT INTO `user_permissions` VALUES ('4', 'banners.manage'), ('4', 'members.manage'), ('4', 'dashboard.view'), ('4', 'members.view'), ('4', 'service_reports.view');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

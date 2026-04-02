-- Atualização do Banco de Dados (MySQL)
-- Execute este script no phpMyAdmin para atualizar a estrutura das tabelas.

-- 1. Adicionar coluna 'banner_path' na tabela 'events' se não existir
-- Nota: O MySQL não suporta "IF NOT EXISTS" diretamente no ADD COLUMN em todas as versões.
-- Se a coluna já existir, o comando abaixo retornará um erro inofensivo "Duplicate column name".
ALTER TABLE events ADD COLUMN banner_path TEXT DEFAULT NULL;

-- 2. Tabela de Despesas (Expenses)
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `description` TEXT NOT NULL,
  `amount` DOUBLE NOT NULL,
  `expense_date` DATE NOT NULL,
  `category` TEXT NULL DEFAULT NULL,
  `congregation_id` INT(11) NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `congregation_id` (`congregation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabela de Fechamentos Financeiros (Financial Closures)
CREATE TABLE IF NOT EXISTS `financial_closures` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `congregation_id` INT(11) NULL DEFAULT NULL,
  `type` TEXT NOT NULL,
  `period` TEXT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_entries` DOUBLE NOT NULL,
  `total_tithes` DOUBLE NOT NULL,
  `total_offerings` DOUBLE NOT NULL,
  `total_expenses` DOUBLE NOT NULL,
  `balance` DOUBLE NOT NULL,
  `previous_balance` DOUBLE DEFAULT 0,
  `final_balance` DOUBLE NOT NULL,
  `status` TEXT DEFAULT 'Fechado',
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `congregation_id` (`congregation_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Garantir que a coluna 'type' em 'events' suporte o novo valor 'convite'
-- Se for VARCHAR(255), não precisa fazer nada.
-- Se for ENUM, execute o comando abaixo (descomente se necessário):
-- ALTER TABLE events MODIFY COLUMN type ENUM('culto', 'congresso', 'aniversario', 'convite', 'outro') DEFAULT NULL;

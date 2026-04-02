-- Update Script for MySQL/phpMyAdmin
-- Generated: 2026-03-09 22:44:57

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Update 'tithes' table structure if needed
DELIMITER //
CREATE PROCEDURE AddColumnIfNotExists()
BEGIN
    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tithes' AND COLUMN_NAME = 'giver_name') THEN
        ALTER TABLE `tithes` ADD COLUMN `giver_name` VARCHAR(255) DEFAULT NULL AFTER `congregation_id`;
    END IF;
END//
DELIMITER ;
CALL AddColumnIfNotExists();
DROP PROCEDURE AddColumnIfNotExists;

-- Table structure for `expenses`
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `description` TEXT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `category` TEXT NULL DEFAULT NULL,
  `congregation_id` INT(11) NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` DATE NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `expenses`
INSERT INTO `expenses` VALUES ('1', 'Água', '8', '2026-03-08', 'Outros', '1', '', '2026-03-09 22:23:01'), ('2', 'Pagamento Sistema - Mensalidade 2026-03', '185', '2026-03-04', 'Contas Fixas', '2', 'Sincronização automática de pagamentos passados', '2026-03-09 22:32:03');

-- Table structure for `financial_closures`
DROP TABLE IF EXISTS `financial_closures`;
CREATE TABLE `financial_closures` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT,
  `congregation_id` INT(11) NULL DEFAULT NULL,
  `type` TEXT NOT NULL,
  `period` TEXT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_entries` DECIMAL(10,2) NOT NULL,
  `total_tithes` DECIMAL(10,2) NOT NULL,
  `total_offerings` DECIMAL(10,2) NOT NULL,
  `total_expenses` DECIMAL(10,2) NOT NULL,
  `balance` DECIMAL(10,2) NOT NULL,
  `previous_balance` DECIMAL(10,2) NULL DEFAULT 0,
  `final_balance` DECIMAL(10,2) NOT NULL,
  `status` TEXT NULL DEFAULT 'Fechado',
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` DATE NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for `financial_closures`

COMMIT;

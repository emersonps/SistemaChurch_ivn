-- Update Script for MySQL/phpMyAdmin - Events Banner
-- Generated: 2026-03-09 23:06:07

DELIMITER //
CREATE PROCEDURE AddBannerColumn()
BEGIN
    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'events' AND COLUMN_NAME = 'banner_path') THEN
        ALTER TABLE `events` ADD COLUMN `banner_path` TEXT DEFAULT NULL AFTER `end_time`;
    END IF;
END//
DELIMITER ;
CALL AddBannerColumn();
DROP PROCEDURE AddBannerColumn;


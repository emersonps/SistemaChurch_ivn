<?php
class CreateMemberDocuments {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS member_documents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                type VARCHAR(50) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS member_documents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                member_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                type TEXT NOT NULL,
                file_path TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        }
    }
    public function down(PDO $db) {
        $db->exec("DROP TABLE IF EXISTS member_documents");
    }
}

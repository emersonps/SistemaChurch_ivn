<?php
require_once __DIR__ . '/../config/database.php';

echo "Creating 'studies' table...\n";

$db = (new Database())->connect();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

if ($driver === 'sqlite') {
    $sql = "CREATE TABLE IF NOT EXISTS studies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        file_path TEXT NOT NULL,
        congregation_id INTEGER,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (congregation_id) REFERENCES congregations(id)
    )";
} else {
    // MySQL
    $sql = "CREATE TABLE IF NOT EXISTS studies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        file_path VARCHAR(255) NOT NULL,
        congregation_id INT,
        created_by INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (congregation_id) REFERENCES congregations(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
}

try {
    $db->exec($sql);
    echo "Table 'studies' created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}

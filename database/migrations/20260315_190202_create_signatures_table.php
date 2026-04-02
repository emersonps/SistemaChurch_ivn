<?php
class CreateSignaturesTable {
    public function up($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS signatures (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            role_label VARCHAR(100) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS signatures (
                id INT AUTO_INCREMENT PRIMARY KEY,
                slug VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(100) NOT NULL,
                role_label VARCHAR(100) NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        }

        $pdo->exec($sql);

        // Inserir registros padrão (placeholders) se não existirem
        $defaults = [
            ['president', 'Pr. Fulano de Tal', 'Pastor Presidente'],
            ['vice_president', 'Pr. Sicrano', 'Vice-Presidente'],
            ['secretary', 'Ir. Beltrana', 'Secretária'],
            ['treasurer', 'Ir. Tesoureiro', 'Tesoureiro']
        ];

        $stmt = $pdo->prepare("INSERT INTO signatures (slug, name, role_label, image_path) VALUES (?, ?, ?, '')");
        
        foreach ($defaults as $d) {
            try {
                // Tenta inserir apenas se não existir (slug unique vai barrar duplicatas)
                // Como SQLite antigo não tem INSERT IGNORE simples, usamos try/catch
                $stmt->execute($d);
            } catch (Exception $e) {
                // Ignora duplicata
            }
        }
    }

    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS signatures");
    }
}

<?php

class AddDocumentTypesToSignatures {
    
    public function up($pdo) {
        // Check if column already exists
        $columnExists = false;
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $stmt = $pdo->query("SHOW COLUMNS FROM signatures LIKE 'document_types'");
            $columnExists = $stmt->fetch() !== false;
        } else {
            $stmt = $pdo->query("PRAGMA table_info(signatures)");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                if ($col['name'] === 'document_types') {
                    $columnExists = true;
                    break;
                }
            }
        }
        
        if (!$columnExists) {
            if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $sql = "ALTER TABLE signatures ADD COLUMN document_types TEXT NULL AFTER image_path";
            } else {
                $sql = "ALTER TABLE signatures ADD COLUMN document_types TEXT";
            }
            $pdo->exec($sql);
            echo "Migration completed: Added document_types column to signatures table\n";
        } else {
            echo "Column document_types already exists in signatures table\n";
        }
    }
    
    public function down($pdo) {
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = "ALTER TABLE signatures DROP COLUMN document_types";
        } else {
            $sql = "ALTER TABLE signatures DROP COLUMN document_types";
        }
        
        try {
            $pdo->exec($sql);
            echo "Rollback completed: Removed document_types column from signatures table\n";
        } catch (PDOException $e) {
            echo "Error rolling back: " . $e->getMessage() . "\n";
        }
    }
}

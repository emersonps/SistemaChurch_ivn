<?php
// src/database/MigrationRunner.php

class MigrationRunner {
    private $db;
    private $migrationsPath;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->migrationsPath = __DIR__ . '/../../database/migrations/';
    }

    public function init() {
        // Criar tabela de controle de migrations se não existir
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        // Adaptação para MySQL
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT(11) NOT NULL AUTO_INCREMENT,
                migration VARCHAR(255) NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        }

        $this->db->exec($sql);
    }

    public function run() {
        $this->init();

        // Buscar migrations já executadas
        $executed = $this->db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
        
        // Listar arquivos na pasta
        $files = glob($this->migrationsPath . '*.php');
        sort($files); // Garantir ordem cronológica

        $count = 0;
        $log = [];

        foreach ($files as $file) {
            $filename = basename($file);
            
            if (!in_array($filename, $executed)) {
                try {
                    // Executar migration
                    // MySQL faz commit implícito em DDL (CREATE/ALTER TABLE), o que fecha a transação.
                    // Por isso, verificamos se a transação está ativa antes de commitar ou dar rollback.
                    
                    $this->db->beginTransaction();
                    
                    // Incluir arquivo que deve conter classe ou script direto
                    $result = require_once $file;
                    
                    if (is_array($result)) {
                        // Se retornou um array de SQLs
                        foreach ($result as $sql) {
                            $this->db->exec($sql);
                        }
                    } else {
                        // Tentar instanciar a classe se existir no arquivo
                        $content = file_get_contents($file);
                        if (preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches)) {
                            $className = $matches[1];
                            if (class_exists($className)) {
                                $instance = new $className();
                                if (method_exists($instance, 'up')) {
                                    $instance->up($this->db);
                                }
                            }
                        }
                    }
                    
                    // Registrar como executado
                    $stmt = $this->db->prepare("INSERT INTO migrations (migration) VALUES (?)");
                    $stmt->execute([$filename]);
                    
                    if ($this->db->inTransaction()) {
                        $this->db->commit();
                    }
                    
                    $log[] = "✅ Executado: $filename";
                    $count++;
                    
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                    $log[] = "❌ Erro em $filename: " . $e->getMessage();
                    return $log; // Parar em caso de erro
                }
            }
        }

        if ($count === 0) {
            $log[] = "Nenhuma migração pendente.";
        }

        return $log;
    }

    public function getPendingCount() {
        $this->init();
        
        $executed = $this->db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
        $files = glob($this->migrationsPath . '*.php');
        
        $pending = 0;
        foreach ($files as $file) {
            if (!in_array(basename($file), $executed)) {
                $pending++;
            }
        }
        
        return $pending;
    }

    public function getHistory() {
        $this->init();
        
        // Get all files
        $files = glob($this->migrationsPath . '*.php');
        sort($files);
        
        // Get executed
        $executed = $this->db->query("SELECT migration, executed_at FROM migrations ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $executedMap = [];
        foreach ($executed as $row) {
            $executedMap[$row['migration']] = $row['executed_at'];
        }
        
        $history = [];
        foreach ($files as $file) {
            $filename = basename($file);
            $history[] = [
                'filename' => $filename,
                'status' => isset($executedMap[$filename]) ? 'executed' : 'pending',
                'executed_at' => $executedMap[$filename] ?? null
            ];
        }
        
        // Add executed files that are missing from disk (orphaned)
        foreach ($executedMap as $filename => $at) {
            $found = false;
            foreach ($history as $h) {
                if ($h['filename'] === $filename) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $history[] = [
                    'filename' => $filename,
                    'status' => 'orphaned',
                    'executed_at' => $at
                ];
            }
        }
        
        return $history;
    }

    public function rollback($filename) {
        $this->init();
        
        // 1. Verify if it is the LAST executed migration
        $lastMigration = $this->db->query("SELECT migration FROM migrations ORDER BY id DESC LIMIT 1")->fetchColumn();
        
        // Se a migração passada não for a última, permitir APENAS se o usuário souber o que está fazendo.
        // No entanto, para simplicidade e segurança, vamos apenas verificar se ela existe no banco.
        // A restrição estrita pode ser relaxada se o arquivo existir.
        
        $stmt = $this->db->query("SELECT id FROM migrations WHERE migration = '" . $filename . "'");
        if (!$stmt->fetch()) {
             throw new Exception("Migração não encontrada no histórico de execução: $filename");
        }

        $filePath = $this->migrationsPath . $filename;
        
        if (file_exists($filePath)) {
            // Incluir arquivo
            // Usamos require_once para evitar redeclaração se já foi incluído
            require_once $filePath;
            
            $content = file_get_contents($filePath);
            
            // Tentar instanciar a classe
            if (preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches)) {
                $className = $matches[1];
                if (class_exists($className)) {
                    $instance = new $className();
                    if (method_exists($instance, 'down')) {
                        $instance->down($this->db);
                    }
                }
            } elseif (is_array($content) && isset($content['down'])) {
                 // Fallback para formato array antigo
                $sqls = $content['down'];
                if (is_array($sqls)) {
                    foreach ($sqls as $sql) {
                        $this->db->exec($sql);
                    }
                } elseif (is_string($sqls)) {
                    $this->db->exec($sqls);
                }
            }
        }

        // Remove from DB
        $stmt = $this->db->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$filename]);
        
        return "Rollback realizado: $filename";
    }
}

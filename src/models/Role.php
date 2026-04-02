<?php
class Role {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name) {
        // Verificar se já existe
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            return false; // Já existe
        }

        $stmt = $this->db->prepare("INSERT INTO roles (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function update($oldName, $newName) {
        // Verificar se novo nome já existe (se for diferente do atual)
        if ($oldName !== $newName) {
            $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$newName]);
            if ($stmt->fetch()) {
                return false; // Novo nome já está em uso
            }
        }

        $stmt = $this->db->prepare("UPDATE roles SET name = ?, updated_at = CURRENT_TIMESTAMP WHERE name = ?");
        return $stmt->execute([$newName, $oldName]);
    }

    public function delete($name) {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE name = ?");
        return $stmt->execute([$name]);
    }
}

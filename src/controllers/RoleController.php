<?php

// Ensure Role model is loaded if autoloader fails
if (!class_exists('Role')) {
    require_once __DIR__ . '/../models/Role.php';
}

class RoleController {
    private $roleModel;

    public function __construct() {
        $this->roleModel = new Role();
    }

    public function list() {
        header('Content-Type: application/json');
        try {
            $roles = $this->roleModel->getAll();
            echo json_encode($roles);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function create() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name']) || empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome do cargo é obrigatório']);
            return;
        }

        try {
            if ($this->roleModel->create($data['name'])) {
                echo json_encode(['success' => true, 'message' => 'Cargo criado com sucesso']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Cargo já existe ou erro ao criar']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function update() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['old_name']) || !isset($data['new_name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nomes antigo e novo são obrigatórios']);
            return;
        }

        try {
            if ($this->roleModel->update($data['old_name'], $data['new_name'])) {
                echo json_encode(['success' => true, 'message' => 'Cargo atualizado com sucesso']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Erro ao atualizar cargo (nome já existe?)']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function delete() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome do cargo é obrigatório']);
            return;
        }

        try {
            if ($this->roleModel->delete($data['name'])) {
                echo json_encode(['success' => true, 'message' => 'Cargo excluído com sucesso']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Erro ao excluir cargo']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

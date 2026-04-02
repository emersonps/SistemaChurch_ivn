<?php
// setup_database.php
require_once 'config/database.php';

echo "Iniciando configuração do banco de dados...\n";

$db = (new Database())->connect();

// Tabela de Usuários (Admin)
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Tabela de Igrejas/Congregações
$db->exec("CREATE TABLE IF NOT EXISTS congregations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    address TEXT,
    leader_name TEXT,
    type TEXT DEFAULT 'congregation', -- 'headquarters' or 'congregation'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Tabela de Membros
$db->exec("CREATE TABLE IF NOT EXISTS members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    congregation_id INTEGER,
    name TEXT NOT NULL,
    email TEXT,
    phone TEXT,
    birth_date DATE,
    baptism_date DATE,
    is_baptized INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active', -- active, inactive, disciplined
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(congregation_id) REFERENCES congregations(id)
)");

// Tabela de Dízimos
$db->exec("CREATE TABLE IF NOT EXISTS tithes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER,
    amount REAL NOT NULL,
    payment_date DATE NOT NULL,
    payment_method TEXT, -- cash, transfer, pix
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(member_id) REFERENCES members(id)
)");

// Tabela de Eventos
$db->exec("CREATE TABLE IF NOT EXISTS events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    event_date DATETIME,
    location TEXT,
    type TEXT, -- service, congress, anniversary
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Inserir usuário admin padrão se não existir
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $password = password_hash('admin', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password) VALUES ('admin', ?)");
    $stmt->execute([$password]);
    echo "Usuário 'admin' criado com senha 'admin'.\n";
}

// Inserir igreja sede padrão se não existir
$stmt = $db->prepare("SELECT COUNT(*) FROM congregations WHERE type = 'headquarters'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $db->exec("INSERT INTO congregations (name, address, type) VALUES ('Igreja Sede', 'Endereço Principal', 'headquarters')");
    echo "Igreja Sede criada.\n";
}

echo "Banco de dados configurado com sucesso em database/SistemaChurch.db\n";

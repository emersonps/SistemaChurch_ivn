-- Tabela de Grupos / Células
CREATE TABLE IF NOT EXISTS groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    leader_id INT, -- Líder (FK members)
    host_id INT,   -- Anfitrião (FK members, opcional)
    address VARCHAR(255),
    meeting_day VARCHAR(20), -- Ex: Segunda-feira
    meeting_time TIME,
    congregation_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (host_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (congregation_id) REFERENCES congregations(id) ON DELETE SET NULL
);

-- Tabela de Membros do Grupo
CREATE TABLE IF NOT EXISTS group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    member_id INT NOT NULL,
    role ENUM('member', 'assistant', 'host') DEFAULT 'member',
    joined_at DATE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_member (group_id, member_id)
);

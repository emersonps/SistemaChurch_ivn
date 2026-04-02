-- Script para corrigir a tabela de permissões de usuários (MySQL)
-- Execute este script no phpMyAdmin se estiver com problemas para salvar permissões adicionais.

-- 1. Tabela de Permissões (Catálogo)
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(100) NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Popular permissões padrão (caso não existam)
INSERT IGNORE INTO `permissions` (`slug`, `label`, `description`) VALUES
('dashboard.view', 'Ver Dashboard', 'Acesso ao painel principal'),
('members.view', 'Ver Membros', 'Visualizar lista de membros'),
('members.manage', 'Gerenciar Membros', 'Criar, editar e excluir membros'),
('congregations.view', 'Ver Congregações', 'Visualizar lista de congregações'),
('congregations.manage', 'Gerenciar Congregações', 'Criar, editar e excluir congregações'),
('financial.view', 'Ver Finanças', 'Visualizar dízimos e ofertas'),
('financial.manage', 'Gerenciar Finanças', 'Lançar dízimos e ofertas'),
('events.view', 'Ver Eventos', 'Visualizar agenda de eventos'),
('events.manage', 'Gerenciar Eventos', 'Criar, editar e excluir eventos'),
('gallery.view', 'Ver Galeria', 'Visualizar álbuns de fotos'),
('gallery.manage', 'Gerenciar Galeria', 'Upload e exclusão de fotos'),
('banners.view', 'Ver Banners', 'Visualizar banners do site'),
('banners.manage', 'Gerenciar Banners', 'Upload e edição de banners'),
('studies.view', 'Ver Estudos', 'Visualizar estudos bíblicos'),
('studies.manage', 'Gerenciar Estudos', 'Publicar estudos bíblicos'),
('service_reports.view', 'Ver Relatórios de Culto', 'Visualizar relatórios'),
('service_reports.manage', 'Gerenciar Relatórios', 'Criar relatórios de culto'),
('users.manage', 'Gerenciar Usuários', 'Criar e editar usuários do sistema'),
('system_payments.view', 'Ver Pagamento Sistema', 'Visualizar status do pagamento do sistema');

-- 2. Corrigir Tabela de Permissões de Usuários (Vínculo)
-- Se a tabela estiver errada (user_id como PK única), ela precisa ser recriada.
DROP TABLE IF EXISTS `user_permissions`;

CREATE TABLE `user_permissions` (
  `user_id` INT(11) NOT NULL,
  `permission_slug` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`user_id`, `permission_slug`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_user_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

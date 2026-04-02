-- Inserir permissões da EBD (Execute no phpMyAdmin)
INSERT IGNORE INTO `permissions` (`slug`, `label`, `description`) VALUES
('ebd.view', 'Ver EBD', 'Visualizar módulo da Escola Bíblica'),
('ebd.manage', 'Gerenciar EBD', 'Gerenciar classes, alunos e professores'),
('ebd.lessons', 'Lançar Aulas/Chamada', 'Registrar aulas, presença e ofertas');

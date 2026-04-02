<?php
// database/migrations/20260310_120100_ebd_permissions.php

return [
    "INSERT IGNORE INTO permissions (slug, label, description) VALUES
    ('ebd.view', 'Ver EBD', 'Visualizar módulo da Escola Bíblica'),
    ('ebd.manage', 'Gerenciar EBD', 'Gerenciar classes, alunos e professores'),
    ('ebd.lessons', 'Lançar Aulas/Chamada', 'Registrar aulas, presença e ofertas');"
];

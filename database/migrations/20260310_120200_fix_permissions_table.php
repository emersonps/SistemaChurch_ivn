<?php
// database/migrations/20260310_120200_fix_permissions_table.php

return [
    "DROP TABLE IF EXISTS user_permissions;",
    
    "CREATE TABLE user_permissions (
        user_id INT(11) NOT NULL,
        permission_slug VARCHAR(100) NOT NULL,
        PRIMARY KEY (user_id, permission_slug),
        KEY user_id (user_id),
        CONSTRAINT fk_user_permissions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

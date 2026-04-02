<?php
// database/migrations/20260310_120300_financial_tables.php

return [
    "CREATE TABLE IF NOT EXISTS expenses (
      id INT(11) NOT NULL AUTO_INCREMENT,
      description TEXT NOT NULL,
      amount DOUBLE NOT NULL,
      expense_date DATE NOT NULL,
      category TEXT NULL DEFAULT NULL,
      congregation_id INT(11) NULL DEFAULT NULL,
      notes TEXT NULL DEFAULT NULL,
      created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY congregation_id (congregation_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS financial_closures (
      id INT(11) NOT NULL AUTO_INCREMENT,
      congregation_id INT(11) NULL DEFAULT NULL,
      type TEXT NOT NULL,
      period TEXT NOT NULL,
      start_date DATE NOT NULL,
      end_date DATE NOT NULL,
      total_entries DOUBLE NOT NULL,
      total_tithes DOUBLE NOT NULL,
      total_offerings DOUBLE NOT NULL,
      total_expenses DOUBLE NOT NULL,
      balance DOUBLE NOT NULL,
      previous_balance DOUBLE DEFAULT 0,
      final_balance DOUBLE NOT NULL,
      status VARCHAR(50) DEFAULT 'Fechado',
      notes TEXT NULL DEFAULT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      created_by INT(11) NULL DEFAULT NULL,
      PRIMARY KEY (id),
      KEY congregation_id (congregation_id),
      KEY created_by (created_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

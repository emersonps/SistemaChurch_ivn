<?php
// Sessao via banco de dados, em vez de arquivo em tmp/. Hospedagens com
// multiplos nos/processos de servidor atras de um balanceador nao
// necessariamente compartilham disco local entre si — uma sessao gravada
// em arquivo pelo no que atendeu o login pode nunca ser lida pelo no que
// atende a proxima requisicao, derrubando o usuario de volta pro login
// mesmo com a senha certa. O banco ja e compartilhado e consistente entre
// quaisquer nos (e o que faz o resto do app funcionar), entao vira a fonte
// confiavel pra sessao tambem. Ver src/models/DbSessionHandler.php.

class CreateSessionsTable {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS sessions (
                    id VARCHAR(128) NOT NULL PRIMARY KEY,
                    data MEDIUMTEXT,
                    last_activity INT NOT NULL,
                    INDEX idx_sessions_last_activity (last_activity)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS sessions (
                    id VARCHAR(128) PRIMARY KEY,
                    data TEXT,
                    last_activity INTEGER NOT NULL
                )
            ");
            $db->exec('CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions(last_activity)');
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS sessions');
    }
}

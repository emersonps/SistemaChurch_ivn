<?php
// database/migrations/add_type_to_system_payments.php
//
// Acompanha cobrancas de recorrencia vindas da central (contrato vinculado
// a esta instancia) — agora a central tambem replica a linha da adesao
// (paga uma unica vez, no aceite/pagamento do contrato) junto com as
// mensalidades. O campo distingue 'adesao' de 'recurring' pra exibicao.

class AddTypeToSystemPayments {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $exists = $db->query("SHOW COLUMNS FROM system_payments LIKE 'type'")->fetchAll(PDO::FETCH_ASSOC);
            if (empty($exists)) {
                $db->exec("ALTER TABLE system_payments ADD COLUMN type VARCHAR(10) NOT NULL DEFAULT 'recurring'");
            }
        } else {
            $cols = $db->query('PRAGMA table_info(system_payments)')->fetchAll(PDO::FETCH_ASSOC);
            $existingNames = array_column($cols, 'name');
            if (!in_array('type', $existingNames, true)) {
                $db->exec("ALTER TABLE system_payments ADD COLUMN type TEXT NOT NULL DEFAULT 'recurring'");
            }
        }
    }

    public function down($db) {
        try {
            $db->exec('ALTER TABLE system_payments DROP COLUMN type');
        } catch (Throwable $e) {
            // Melhor esforco.
        }
    }
}

<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $db = (new Database())->connect();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    $ignore = $driver === 'sqlite' ? 'OR IGNORE' : 'IGNORE';
    $db->exec("INSERT $ignore INTO permissions (slug, label, description) VALUES
        ('settings.manage', 'Gerenciar Configurações', 'Gerenciar configurações do sistema'),
        ('settings.system.view', 'Ver Configurações do Sistema', 'Visualizar configurações gerais do sistema'),
        ('financial_accounts.manage', 'Gerenciar Contas/Caixas', 'Gerenciar contas bancárias e caixas'),
        ('financial_ofx.manage', 'Conciliação OFX', 'Importar e conciliar arquivos OFX'),
        ('system_payments.manage', 'Gerenciar Pagamento do Sistema', 'Registrar e gerenciar pagamento da plataforma'),
        ('developer.access', 'Acesso de Desenvolvedor', 'Acesso às ferramentas do desenvolvedor'),
        ('users.view', 'Ver Usuários', 'Visualizar lista de usuários')
    ");
    echo 'Permissões ausentes adicionadas.';
} catch (Exception $e) {
    echo 'Erro ao adicionar permissões: ' . $e->getMessage();
}

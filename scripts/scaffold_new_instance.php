<?php
// scripts/scaffold_new_instance.php
//
// Prepara uma pasta de instancia nova pra ja funcionar igual as outras no
// pipeline de deploy (mesmo mecanismo usado nesta sessao pra recuperar
// impvc/ieadsena/IBR/inge/iedjc/osegredoeafe). Automatiza tudo que e so
// arquivo local; o que exige acesso a paineis externos (GitHub, Hostinger,
// Central) fica documentado como checklist no final.
//
// Uso:
//   php scripts/scaffold_new_instance.php --name=SistemaChurch_x --domain=x.com.br
//   php scripts/scaffold_new_instance.php --name=SistemaChurch_x --domain=x.com.br --repo=https://github.com/emersonps/SistemaChurch_x.git

function fail($message, $code = 1) {
    fwrite(STDERR, $message . PHP_EOL);
    exit($code);
}

function normalizePath($path) {
    $path = str_replace(['\\', '//'], ['/', '/'], (string)$path);
    return rtrim($path, '/');
}

function parseArgs(array $argv) {
    $args = ['name' => null, 'domain' => null, 'repo' => null, 'root' => null, 'template' => null];
    foreach ($argv as $i => $raw) {
        if ($i === 0) {
            continue;
        }
        foreach (array_keys($args) as $key) {
            if (strpos($raw, "--{$key}=") === 0) {
                $args[$key] = substr($raw, strlen("--{$key}="));
            }
        }
    }
    return $args;
}

$args = parseArgs($argv);
if (empty($args['name']) || empty($args['domain'])) {
    fail("Uso: php scripts/scaffold_new_instance.php --name=SistemaChurch_x --domain=x.com.br [--repo=https://github.com/...] [--root=D:/] [--template=SistemaChurch_ieadsena]");
}

$name = $args['name'];
$domain = $args['domain'];
$root = normalizePath($args['root'] ?? dirname(dirname(__DIR__)));
$templateName = $args['template'] ?? 'SistemaChurch_ieadsena';
$templatePath = "{$root}/{$templateName}";
$repoUrl = $args['repo'] ?? "https://github.com/emersonps/{$name}.git";
$targetPath = "{$root}/{$name}";

echo "==> Instância: {$name}" . PHP_EOL;
echo "==> Domínio: {$domain}" . PHP_EOL;
echo "==> Pasta destino: {$targetPath}" . PHP_EOL;
echo PHP_EOL;

// 1) Clonar o repositório, se ainda não existir localmente.
if (is_dir($targetPath)) {
    echo "[1/5] Pasta já existe, não vou clonar de novo." . PHP_EOL;
} else {
    echo "[1/5] Clonando {$repoUrl}..." . PHP_EOL;
    $cmd = 'git clone ' . escapeshellarg($repoUrl) . ' ' . escapeshellarg($targetPath) . ' 2>&1';
    passthru($cmd, $exitCode);
    if ($exitCode !== 0) {
        fail("Falha ao clonar. Confirme que o repositório {$repoUrl} já existe no GitHub (esse script não cria repositórios).");
    }
}

// 2) config/database.php placeholder — nunca é commitado (gitignored em
// todos os projetos desta família), só precisa existir pra
// sync_core_to_instances.php reconhecer a pasta como projeto válido.
$configDir = "{$targetPath}/config";
$configFile = "{$configDir}/database.php";
if (!is_dir($configDir)) {
    mkdir($configDir, 0777, true);
}
if (is_file($configFile)) {
    echo "[2/5] config/database.php já existe, não mexi." . PHP_EOL;
} else {
    $placeholder = <<<PHP
<?php
// Placeholder local — nunca commitado (config/database.php é gitignored).
// Existe só pro sync_core_to_instances.php reconhecer esta pasta como um
// projeto válido; as credenciais reais ficam no servidor de produção.
class Database {
    public function connect() {
        throw new Exception('Config de banco local não configurada neste clone de staging.');
    }
}
PHP;
    file_put_contents($configFile, $placeholder);
    echo "[2/5] config/database.php (placeholder) criado." . PHP_EOL;
}

// 3) .github/workflows/deploy.yml gerado a partir do template, trocando o
// domínio nas 6 ocorrências hardcoded.
$templateWorkflow = "{$templatePath}/.github/workflows/deploy.yml";
$targetWorkflowDir = "{$targetPath}/.github/workflows";
$targetWorkflow = "{$targetWorkflowDir}/deploy.yml";

if (!is_file($templateWorkflow)) {
    fail("Template não encontrado: {$templateWorkflow}. Ajuste --template= pra apontar pra uma instância existente com deploy.yml.");
}

if (is_file($targetWorkflow)) {
    echo "[3/5] deploy.yml já existe na instância, não sobrescrevi." . PHP_EOL;
} else {
    $workflowContent = file_get_contents($templateWorkflow);
    $templateDomain = null;
    if (preg_match('#domains/([a-zA-Z0-9.-]+)/public_html#', $workflowContent, $m)) {
        $templateDomain = $m[1];
    }
    if ($templateDomain === null) {
        fail("Não consegui identificar o domínio dentro do template {$templateWorkflow}.");
    }

    $newContent = str_replace($templateDomain, $domain, $workflowContent);
    if (!is_dir($targetWorkflowDir)) {
        mkdir($targetWorkflowDir, 0777, true);
    }
    file_put_contents($targetWorkflow, $newContent);
    $occurrences = substr_count($workflowContent, $templateDomain);
    echo "[3/5] deploy.yml gerado a partir de {$templateName} ({$occurrences}x '{$templateDomain}' -> '{$domain}')." . PHP_EOL;
}

// 4) Popular com o core atual (mesmo script usado o resto desta sessão).
echo "[4/5] Sincronizando com o core atual..." . PHP_EOL;
$syncScript = __DIR__ . '/sync_core_to_instances.php';
$cmd = 'php ' . escapeshellarg($syncScript) . ' --root=' . escapeshellarg($root) . ' --targets=' . escapeshellarg($name) . ' 2>&1';
passthru($cmd, $exitCode);
if ($exitCode !== 0) {
    fail("sync_core_to_instances.php terminou com erro — confira a saída acima.");
}

// 5) Checklist do que só pode ser feito manualmente.
echo PHP_EOL;
echo "[5/5] Arquivos locais prontos. Passos que só você (ou eu, com acesso aos" . PHP_EOL;
echo "painéis) pode fazer:" . PHP_EOL;
echo PHP_EOL;
echo "  [ ] Criar o repositório '{$name}' no GitHub, se ainda não existir," . PHP_EOL;
echo "      e dar o primeiro push: cd {$targetPath} && git add -A && git commit -m \"Setup inicial\" && git push -u origin main" . PHP_EOL;
echo "  [ ] Configurar os secrets do repositório no GitHub (Settings > Secrets > Actions):" . PHP_EOL;
echo "        HOSTINGER_HOST, HOSTINGER_PORT, HOSTINGER_USER, HOSTINGER_SSH_KEY" . PHP_EOL;
echo "        CORE_REPOSITORY (emersonps/SistemaChurch_central), CORE_SSH_KEY" . PHP_EOL;
echo "        CENTRAL_ACTIVITY_URL, DEPLOY_REPORT_TOKEN (mesmos valores usados nos outros repositórios)" . PHP_EOL;
echo "  [ ] Apontar o domínio '{$domain}' pra essa aplicação no painel do Hostinger." . PHP_EOL;
echo "  [ ] Cadastrar a instância em /admin/instances no Central (nome, código, token da API)." . PHP_EOL;
echo "  [ ] Depois do primeiro push, conferir /admin/activity no Central pra ver o deploy chegando." . PHP_EOL;

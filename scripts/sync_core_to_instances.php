<?php

function fail($message, $code = 1) {
    fwrite(STDERR, $message . PHP_EOL);
    exit($code);
}

function normalizePath($path) {
    $path = str_replace(['\\', '//'], ['/', '/'], (string)$path);
    return rtrim($path, '/');
}

function parseArgs(array $argv) {
    $args = [
        'root' => null,
        'targets' => [],
        'dry_run' => false,
        'verbose' => false,
    ];

    foreach ($argv as $i => $raw) {
        if ($i === 0) {
            continue;
        }

        if ($raw === '--dry-run') {
            $args['dry_run'] = true;
            continue;
        }
        if ($raw === '--verbose') {
            $args['verbose'] = true;
            continue;
        }
        if (strpos($raw, '--root=') === 0) {
            $args['root'] = substr($raw, strlen('--root='));
            continue;
        }
        if (strpos($raw, '--targets=') === 0) {
            $list = substr($raw, strlen('--targets='));
            $parts = array_filter(array_map('trim', explode(',', $list)));
            $args['targets'] = array_values(array_unique($parts));
            continue;
        }
    }

    return $args;
}

function listTargetsFromRoot($rootDir, $excludeDirName) {
    $targets = [];
    $entries = scandir($rootDir);
    foreach ($entries as $entry) {
        if (!is_string($entry) || $entry === '.' || $entry === '..') {
            continue;
        }
        if ($entry === $excludeDirName) {
            continue;
        }
        if (strpos($entry, 'SistemaChurch_') !== 0) {
            continue;
        }
        $full = $rootDir . '/' . $entry;
        if (is_dir($full) && isProjectRoot($full)) {
            $targets[] = $full;
        }
    }
    sort($targets);
    return $targets;
}

function isProjectRoot($dir) {
    $dir = normalizePath($dir);
    if (!is_dir($dir)) {
        return false;
    }

    $hasSrc = is_file($dir . '/src/helpers.php');
    $hasConfig = is_file($dir . '/config/database.php');
    $hasEntry = is_file($dir . '/public/index.php') || is_file($dir . '/index.php');

    return $hasSrc && $hasConfig && $hasEntry;
}

function iterFiles($baseDir, $relativeSubpath) {
    $baseDir = normalizePath($baseDir);
    $relativeSubpath = ltrim((string)$relativeSubpath, '/');
    $root = $relativeSubpath === '' ? $baseDir : ($baseDir . '/' . $relativeSubpath);

    if (!file_exists($root)) {
        return [];
    }

    if (is_file($root)) {
        return [$relativeSubpath];
    }

    $paths = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        $full = normalizePath($fileInfo->getPathname());
        $rel = substr($full, strlen($baseDir) + 1);
        $paths[] = $rel;
    }
    sort($paths);
    return $paths;
}

function filesAreEqual($src, $dst) {
    if (!is_file($dst)) {
        return false;
    }
    if (filesize($src) !== filesize($dst)) {
        return false;
    }
    return hash_file('sha1', $src) === hash_file('sha1', $dst);
}

$args = parseArgs($argv);
$sourceDir = normalizePath(dirname(__DIR__));
$parentRoot = normalizePath(dirname($sourceDir));
$excludeDirName = basename($sourceDir);

$targetsRoot = $args['root'] !== null ? normalizePath($args['root']) : $parentRoot;
$driveRoot = null;
if (preg_match('/^([a-zA-Z]):/', $sourceDir, $m)) {
    $driveRoot = normalizePath(strtoupper($m[1]) . ':/');
}

if (!is_dir($targetsRoot)) {
    fail('Diretório root inválido: ' . $targetsRoot);
}

$targets = $args['targets'];
if (count($targets) === 0) {
    $rootsToTry = [$targetsRoot];
    if ($args['root'] === null && $driveRoot !== null && $driveRoot !== $targetsRoot) {
        $rootsToTry[] = $driveRoot;
    }

    foreach ($rootsToTry as $rootTry) {
        if (!is_dir($rootTry)) {
            continue;
        }

        $targets = listTargetsFromRoot($rootTry, $excludeDirName);
        if (count($targets) > 0) {
            $targetsRoot = $rootTry;
            break;
        }
    }
} else {
    $targets = array_map(function ($t) use ($targetsRoot) {
        $t = normalizePath($t);
        if (strpos($t, '/') === false) {
            return $targetsRoot . '/' . $t;
        }
        return $t;
    }, $targets);
}

if (count($targets) > 0) {
    $targets = array_values(array_filter($targets, function ($t) use ($args) {
        $ok = isProjectRoot($t);
        if (!$ok && !empty($args['verbose'])) {
            fwrite(STDERR, "[skip] Alvo não parece um projeto SistemaChurch válido: {$t}" . PHP_EOL);
        }
        return $ok;
    }));
}

if (count($targets) === 0) {
    $hint = 'Nenhum projeto alvo encontrado.';
    $hint .= ' Use --targets=SistemaChurch_x,SistemaChurch_y';
    $hint .= ' ou --root=D:/pasta';
    if ($driveRoot !== null) {
        $hint .= ' (procurado em ' . $parentRoot . ' e ' . $driveRoot . ')';
    }
    fail($hint);
}

$includes = [
    'config',
    'src',
    'public/index.php',
    'public/.htaccess',
    '.htaccess',
    'database/migrations',
    'database/*.sql',
    'harpa_crista',
];

$sourceFiles = [];
foreach ($includes as $item) {
    if (strpos($item, '*') !== false) {
        $glob = glob($sourceDir . '/' . $item) ?: [];
        foreach ($glob as $path) {
            $path = normalizePath($path);
            if (is_file($path)) {
                $sourceFiles[] = substr($path, strlen($sourceDir) + 1);
            }
        }
        continue;
    }
    $sourceFiles = array_merge($sourceFiles, iterFiles($sourceDir, $item));
}
$sourceFiles = array_values(array_unique($sourceFiles));
sort($sourceFiles);

if (count($sourceFiles) === 0) {
    fail('Nenhum arquivo encontrado para sincronizar. Verifique os caminhos de include.');
}

$copied = 0;
$skipped = 0;
$errors = 0;

foreach ($targets as $targetDir) {
    $targetDir = normalizePath($targetDir);
    if (!is_dir($targetDir)) {
        if ($args['verbose']) {
            fwrite(STDERR, "[skip] Alvo não é diretório: {$targetDir}" . PHP_EOL);
        }
        continue;
    }

    echo "==> Sincronizando para: {$targetDir}" . PHP_EOL;

    foreach ($sourceFiles as $rel) {
        $src = $sourceDir . '/' . $rel;
        $dst = $targetDir . '/' . $rel;

        if (!is_file($src)) {
            continue;
        }

        if (filesAreEqual($src, $dst)) {
            $skipped++;
            continue;
        }

        $dstDir = dirname($dst);
        if (!is_dir($dstDir)) {
            if (!$args['dry_run'] && !mkdir($dstDir, 0777, true) && !is_dir($dstDir)) {
                $errors++;
                fwrite(STDERR, "[erro] Falha ao criar pasta: {$dstDir}" . PHP_EOL);
                continue;
            }
        }

        if ($args['verbose']) {
            echo ($args['dry_run'] ? "[dry] " : "") . "{$rel}" . PHP_EOL;
        }

        if (!$args['dry_run']) {
            if (!copy($src, $dst)) {
                $errors++;
                fwrite(STDERR, "[erro] Falha ao copiar: {$rel}" . PHP_EOL);
                continue;
            }
        }

        $copied++;
    }
}

echo PHP_EOL;
echo "Concluído." . PHP_EOL;
echo "- Atualizados: {$copied}" . PHP_EOL;
echo "- Iguais (pulados): {$skipped}" . PHP_EOL;
echo "- Erros: {$errors}" . PHP_EOL;

exit($errors > 0 ? 2 : 0);

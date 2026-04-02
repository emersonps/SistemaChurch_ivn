<?php
// revert_text.php

$directories = [
    __DIR__ . '/../src',
    __DIR__ . '/../public',
];

$replacements = [
    'IMPVC' => 'IEADSENA',
    'Igreja Missionária Pentecostal Vidas em Cristo' => 'Igreja Evangélica Assembleia de Deus Senhor das Nações',
    'Igreja Missionária Pentecostal' => 'Igreja Evangélica Assembleia de Deus', // For shorter usages
];

function scanAndReplace($dir, $replacements) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = $file->getExtension();
            if (in_array($ext, ['php', 'json', 'html', 'js', 'css'])) {
                $path = $file->getPathname();
                $content = file_get_contents($path);
                $originalContent = $content;
                
                foreach ($replacements as $search => $replace) {
                    $content = str_replace($search, $replace, $content);
                }
                
                if ($content !== $originalContent) {
                    file_put_contents($path, $content);
                    echo "Updated: $path\n";
                }
            }
        }
    }
}

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        scanAndReplace($dir, $replacements);
    }
}

echo "Revert complete.\n";

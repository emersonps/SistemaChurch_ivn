<?php
// replace_text_v2.php

$directories = [
    __DIR__ . '/src',
    __DIR__ . '/public',
];

$replacements = [
    'Igreja Evangélica Assembleia de Deus Vidas em Cristo' => 'Igreja Missionária Pentecostal Vidas em Cristo',
    'Igreja Evangélica Assembleia de Deus' => 'Igreja Missionária Pentecostal',
    'Assembleia de Deus' => 'Igreja Missionária Pentecostal', // Broad replacement, verify context?
    'ieadsena' => 'impvc',
];

function scanAndReplace($dir, $replacements) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = $file->getExtension();
            if (in_array($ext, ['php', 'json', 'html'])) {
                $path = $file->getPathname();
                $content = file_get_contents($path);
                $originalContent = $content;
                
                foreach ($replacements as $search => $replace) {
                    // Case insensitive for some? No, stick to sensitive for names.
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

echo "Replacement v2 complete.\n";

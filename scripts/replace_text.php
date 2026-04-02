<?php
// replace_text.php

$directories = [
    __DIR__ . '/src',
    __DIR__ . '/public',
    // __DIR__ . '/scripts', // Optional
];

$replacements = [
    'IEADSENA' => 'IMPVC',
    'Igreja Evangélica Assembléia de Deus Senhor das Nações' => 'Igreja Missionária Pentecostal Vidas em Cristo',
    'Assembléia de Deus Senhor das Nações' => 'Igreja Missionária Pentecostal Vidas em Cristo', // Fallback
    'Senhor das Nações' => 'Vidas em Cristo', // Shorter version if used alone? Maybe risky. Let's stick to full names first.
    'Igreja Evangélica Assembleia de Deus Senhor das Nações' => 'Igreja Missionária Pentecostal Vidas em Cristo', // Without accent
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

echo "Replacement complete.\n";

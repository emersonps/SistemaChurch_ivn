<?php
declare(strict_types=1);

function replaceOrFail(string $path, string $old, string $new): void
{
    $content = file_get_contents($path);
    if ($content === false) {
        throw new RuntimeException("Nao foi possivel ler: {$path}");
    }

    if (strpos($content, $old) === false) {
        throw new RuntimeException("Trecho nao encontrado em: {$path}");
    }

    $updated = str_replace($old, $new, $content);
    if ($updated === $content) {
        throw new RuntimeException("Nenhuma alteracao aplicada em: {$path}");
    }

    file_put_contents($path, $updated);
}

$projects = [
    'D:/SistemaChurch_impvc',
    'D:/SistemaChurch_ieadsena',
];

foreach ($projects as $project) {
    replaceOrFail(
        $project . '/src/views/public/home.php',
        "<title>IVN - Igreja Vida Nova</title>",
        "<title><?= htmlspecialchars((\$siteProfile['alias'] ?? 'Igreja') . ' - ' . (\$siteProfile['name'] ?? 'Nossa Igreja')) ?></title>"
    );
    replaceOrFail(
        $project . '/src/views/public/home.php',
        "alt=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?>\" onerror=\"this.style.display='none'\">",
        "alt=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?>\" onerror=\"this.style.display='none'\">"
    );
    replaceOrFail(
        $project . '/src/views/public/home.php',
        "<span class=\"d-none d-md-inline\"><?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?></span>",
        "<span class=\"d-none d-md-inline\"><?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?></span>"
    );
    replaceOrFail(
        $project . '/src/views/public/home.php',
        "<?= htmlspecialchars(\$siteProfile['name'] ?? 'Igreja Vida Nova') ?>",
        "<?= htmlspecialchars(\$siteProfile['name'] ?? 'Nossa Igreja') ?>"
    );

    replaceOrFail(
        $project . '/src/views/public/layout/header.php',
        "<title><?= \$seo_title ?? ((\$siteProfile['alias'] ?? 'IVN') . ' - ' . (\$siteProfile['name'] ?? 'Igreja Vida Nova')) ?></title>",
        "<title><?= \$seo_title ?? ((\$siteProfile['alias'] ?? 'Igreja') . ' - ' . (\$siteProfile['name'] ?? 'Nossa Igreja')) ?></title>"
    );
    replaceOrFail(
        $project . '/src/views/public/layout/header.php',
        "<meta name=\"description\" content=\"<?= \$seo_description ?? ('A ' . (\$siteProfile['alias'] ?? 'IVN') . ' é uma comunidade cristã comprometida com a proclamação do Evangelho, edificação da família e adoração a Deus.') ?>\">",
        "<meta name=\"description\" content=\"<?= \$seo_description ?? ('A ' . (\$siteProfile['name'] ?? \$siteProfile['alias'] ?? 'igreja') . ' é uma comunidade cristã comprometida com a proclamação do Evangelho, edificação da família e adoração a Deus.') ?>\">"
    );
    replaceOrFail(
        $project . '/src/views/public/layout/header.php',
        "<meta name=\"keywords\" content=\"<?= htmlspecialchars(implode(', ', array_unique(array_filter(['igreja', 'assembleia de deus', \$siteProfile['alias'] ?? 'IVN', 'culto', 'evangelho', 'jesus', 'família', 'adoração'])))) ?>\">",
        "<meta name=\"keywords\" content=\"<?= htmlspecialchars(implode(', ', array_unique(array_filter(['igreja', 'assembleia de deus', \$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'igreja', 'culto', 'evangelho', 'jesus', 'família', 'adoração'])))) ?>\">"
    );
    replaceOrFail(
        $project . '/src/views/public/layout/header.php',
        "<meta name=\"author\" content=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?>\">",
        "<meta name=\"author\" content=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?>\">"
    );
    replaceOrFail(
        $project . '/src/views/public/layout/header.php',
        "<meta property=\"og:title\" content=\"<?= \$seo_title ?? ((\$siteProfile['alias'] ?? 'IVN') . ' - ' . (\$siteProfile['name'] ?? 'Igreja Vida Nova')) ?>\">",
        "<meta property=\"og:title\" content=\"<?= \$seo_title ?? ((\$siteProfile['alias'] ?? 'Igreja') . ' - ' . (\$siteProfile['name'] ?? 'Nossa Igreja')) ?>\">"
    );
    replaceOrFail(
        $project . '/src/views/public/layout/header.php',
        "<meta name=\"apple-mobile-web-app-title\" content=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?>\">",
        "<meta name=\"apple-mobile-web-app-title\" content=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?>\">"
    );
    replaceOrFail(
        $project . '/src/views/public/layout/header.php',
        "alt=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?>\" height=\"50\" class=\"me-2\">",
        "alt=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?>\" height=\"50\" class=\"me-2\">"
    );
    replaceOrFail(
        $project . '/src/views/public/layout/header.php',
        "<span class=\"d-none d-md-block fw-bold\"><?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?></span>",
        "<span class=\"d-none d-md-block fw-bold\"><?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?></span>"
    );

    replaceOrFail(
        $project . '/src/views/public/gallery.php',
        "<title>Galeria de Fotos - <?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?></title>",
        "<title>Galeria de Fotos - <?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?></title>"
    );
    replaceOrFail(
        $project . '/src/views/public/gallery.php',
        "alt=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?> Logo\">",
        "alt=\"<?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?> Logo\">"
    );
    replaceOrFail(
        $project . '/src/views/public/gallery.php',
        "<?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?>",
        "<?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?>"
    );

    replaceOrFail(
        $project . '/src/views/public/layout/footer.php',
        "<h5><?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?></h5>",
        "<h5><?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?></h5>"
    );
    replaceOrFail(
        $project . '/src/views/public/layout/footer.php',
        "&copy; <?= date('Y') ?> <?= htmlspecialchars(\$siteProfile['alias'] ?? 'IVN') ?>. Todos os direitos reservados.",
        "&copy; <?= date('Y') ?> <?= htmlspecialchars(\$siteProfile['alias'] ?? \$siteProfile['name'] ?? 'Igreja') ?>. Todos os direitos reservados."
    );
}

echo "Branding publico sincronizado.\n";

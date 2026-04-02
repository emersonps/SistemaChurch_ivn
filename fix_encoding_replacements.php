<?php
$files = [
    'database/migrations/20260315_201538_create_settings_table.php',
    'public/cron_birthdays.php',
    'public/manifest.json',
    'seed_events.php',
    'seed_members.php',
    'seed_service_reports.php',
    'src/controllers/SettingsController.php',
    'src/views/admin/dashboard.php',
    'src/views/admin/financial/report.php',
    'src/views/admin/login.php',
    'src/views/admin/members/card.php',
    'src/views/layout/header.php',
    'src/views/portal/card.php',
    'src/views/portal/layout/header.php',
    'src/views/portal/login.php',
    'src/views/portal/register.php',
    'src/views/public/gallery.php',
    'src/views/public/home.php',
    'src/views/public/layout/footer.php',
    'src/views/public/layout/header.php',
    'test_insert_tithe.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Replacements for the name
        $content = str_replace('Igreja Evangélica Assembleia de Deus Senhor das Nações', 'Igreja Missionária Pentecostal Vidas em Cristo', $content);
        $content = str_replace('Assembleia de Deus Senhor das Nações', 'Igreja Missionária Pentecostal Vidas em Cristo', $content);
        $content = str_replace('Igreja Evangélica Assembleia de Deus', 'Igreja Missionária Pentecostal Vidas em Cristo', $content);
        $content = str_replace('Assembleia de Deus', 'Igreja Missionária Pentecostal Vidas em Cristo', $content);
        
        // Replacements for IEADSENA
        $content = str_replace('IEADSENA', 'IMPVC', $content);
        $content = str_replace('ieadsena', 'impvc', $content);
        $content = str_replace('Ieadsena', 'Impvc', $content);
        
        file_put_contents($file, $content);
        echo "Updated: $file\n";
    }
}
?>
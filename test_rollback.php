<?php
require 'src/database/MigrationRunner.php';
require 'config/database.php';

$runner = new MigrationRunner();
try {
    echo $runner->rollback('20260321_120000_create_site_settings_table.php');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

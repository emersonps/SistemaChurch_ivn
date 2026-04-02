<?php
$db = new PDO('sqlite:database/SistemaChurch.db');
$db->exec("UPDATE congregations SET name = REPLACE(name, 'IEADSENA', 'IMPVC')");
$db->exec("UPDATE congregations SET name = REPLACE(name, 'Assembleia de Deus', 'Igreja Missionária Pentecostal Vidas em Cristo')");
$db->exec("UPDATE events SET location = REPLACE(location, 'IEADSENA', 'IMPVC'), description = REPLACE(description, 'IEADSENA', 'IMPVC')");
$db->exec("UPDATE events SET description = REPLACE(description, 'Assembleia de Deus Senhor das Nações', 'Igreja Missionária Pentecostal Vidas em Cristo')");
$db->exec("UPDATE events SET description = REPLACE(description, 'Assembleia de Deus', 'Igreja Missionária Pentecostal Vidas em Cristo')");
echo 'DB updated';
?>
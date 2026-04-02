<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Mocking groups and group members...\n";

$groups = ['Ministério de Louvor', 'Grupo de Jovens', 'Grupo de Mulheres', 'Grupo de Homens'];
$group_ids = [];
foreach ($groups as $g) {
    $stmt = $pdo->prepare("INSERT INTO `groups` (name, description) VALUES (?, ?)");
    $stmt->execute([$g, "Descrição para " . $g]);
    $group_ids[] = $pdo->lastInsertId();
}

$stmt = $pdo->query("SELECT id FROM members LIMIT 30");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($group_ids as $g_id) {
    // add 5 members to each group
    $keys = array_rand($members, 5);
    foreach ($keys as $k) {
        try {
            $stmt = $pdo->prepare("INSERT INTO group_members (group_id, member_id, role) VALUES (?, ?, 'member')");
            $stmt->execute([$g_id, $members[$k]['id']]);
        } catch(Exception $e) {}
    }
}

echo "Created 4 groups with members.\n";

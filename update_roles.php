<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Updating member roles...\n";

// Ensure we have the job_titles we want
$titles = ['Pastor', 'Presbítero', 'Diácono', 'Evangelista', 'Missionário', 'Cooperador'];
foreach ($titles as $t) {
    $stmt = $pdo->prepare("SELECT id FROM job_titles WHERE name = ?");
    $stmt->execute([$t]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO job_titles (name) VALUES (?)");
        $stmt->execute([$t]);
    }
}

// Get some random members to promote
$stmt = $pdo->query("SELECT id FROM members WHERE role = 'Membro' ORDER BY RAND() LIMIT 20");
$members_to_promote = $stmt->fetchAll(PDO::FETCH_COLUMN);

$i = 0;
foreach ($members_to_promote as $mid) {
    // distribute the roles
    $role = $titles[$i % count($titles)];
    $stmt = $pdo->prepare("UPDATE members SET role = ? WHERE id = ?");
    $stmt->execute([$role, $mid]);
    $i++;
}

echo "Promoted 20 members to various roles.\n";

echo "Assigning teachers to EBD classes...\n";

// Get all EBD classes
$stmt = $pdo->query("SELECT id, congregation_id FROM ebd_classes");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($classes as $cls) {
    // check if this class already has a teacher
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ebd_teachers WHERE class_id = ?");
    $stmt->execute([$cls['id']]);
    $has_teacher = $stmt->fetchColumn();
    
    if ($has_teacher == 0) {
        // Find a member from the same congregation to be the teacher
        $stmt = $pdo->prepare("SELECT id FROM members WHERE congregation_id = ? ORDER BY RAND() LIMIT 1");
        $stmt->execute([$cls['congregation_id']]);
        $teacher_id = $stmt->fetchColumn();
        
        if ($teacher_id) {
            $stmt = $pdo->prepare("INSERT INTO ebd_teachers (class_id, member_id, assigned_at, status) VALUES (?, ?, NOW(), 'active')");
            $stmt->execute([$cls['id'], $teacher_id]);
            
            // update member flag
            $pdo->query("UPDATE members SET is_ebd_teacher=1 WHERE id=$teacher_id");
            echo "Assigned teacher $teacher_id to class {$cls['id']}\n";
        }
    }
}

echo "Done!\n";

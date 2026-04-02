<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Mocking photos...\n";

// Get members
$stmt = $pdo->query("SELECT id FROM members WHERE photo IS NULL LIMIT 20");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

$photos_dir = __DIR__ . '/public/assets/uploads/members/';
if (!is_dir($photos_dir)) {
    mkdir($photos_dir, 0777, true);
}

// create a dummy image to use as photo
$dummy_image_path = $photos_dir . 'dummy.jpg';
if (!file_exists($dummy_image_path)) {
    $img = imagecreatetruecolor(200, 200);
    $bg = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
    $fg = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bg);
    imagestring($img, 5, 50, 90, "Foto", $fg);
    imagejpeg($img, $dummy_image_path);
    imagedestroy($img);
}

foreach ($members as $m) {
    $filename = uniqid() . '.jpg';
    copy($dummy_image_path, $photos_dir . $filename);
    $stmt = $pdo->prepare("UPDATE members SET photo = ? WHERE id = ?");
    $stmt->execute([$filename, $m['id']]);
}
echo "Added photos for " . count($members) . " members.\n";

// create some dummy banners for events
$events_dir = __DIR__ . '/public/assets/uploads/banners/';
if (!is_dir($events_dir)) {
    mkdir($events_dir, 0777, true);
}
$stmt = $pdo->query("SELECT id FROM events WHERE banner_path IS NULL LIMIT 5");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($events as $e) {
    $filename = 'event_' . uniqid() . '.jpg';
    copy($dummy_image_path, $events_dir . $filename);
    $stmt = $pdo->prepare("UPDATE events SET banner_path = ? WHERE id = ?");
    $stmt->execute([$filename, $e['id']]);
}
echo "Added banners for " . count($events) . " events.\n";

// create dummy pdf for studies
$studies_dir = __DIR__ . '/public/assets/uploads/studies/';
if (!is_dir($studies_dir)) {
    mkdir($studies_dir, 0777, true);
}
$dummy_pdf_path = $studies_dir . 'mock_study.pdf';
if (!file_exists($dummy_pdf_path)) {
    file_put_contents($dummy_pdf_path, "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Resources <<\n/Font <<\n/F1 4 0 R\n>>\n>>\n/Contents 5 0 R\n>>\nendobj\n4 0 obj\n<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n>>\nendobj\n5 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 24 Tf\n100 700 Td\n(Mock PDF) Tj\nET\nendstream\nendobj\ntrailer\n<<\n/Root 1 0 R\n>>\n%%EOF");
}

echo "Photos mocked successfully.\n";

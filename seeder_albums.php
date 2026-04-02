<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Mocking photo albums and photos...\n";

// create an album
$stmt = $pdo->prepare("INSERT INTO photo_albums (title, description, event_date, location) VALUES (?, ?, ?, ?)");
$stmt->execute(['Culto de Aniversário', 'Fotos do culto especial', date('Y-m-d H:i:s'), 'Sede']);
$album_id = $pdo->lastInsertId();

$photos_dir = __DIR__ . '/public/assets/uploads/gallery/';
if (!is_dir($photos_dir)) {
    mkdir($photos_dir, 0777, true);
}

// create a dummy image to use as photo
$dummy_image_path = $photos_dir . 'dummy_gallery.jpg';
if (!file_exists($dummy_image_path)) {
    $img = imagecreatetruecolor(800, 600);
    $bg = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
    $fg = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bg);
    imagestring($img, 5, 350, 290, "Galeria", $fg);
    imagejpeg($img, $dummy_image_path);
    imagedestroy($img);
}

for ($i = 0; $i < 10; $i++) {
    $filename = uniqid() . '.jpg';
    copy($dummy_image_path, $photos_dir . $filename);
    $stmt = $pdo->prepare("INSERT INTO photos (album_id, filename) VALUES (?, ?)");
    $stmt->execute([$album_id, $filename]);
}

echo "Created 1 photo album with 10 photos.\n";

<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Starting Media Seeding...\n";

// BANNERS
echo "Creating banners...\n";
$stmt = $pdo->query("SELECT id FROM events LIMIT 10");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$banners_dir = __DIR__ . '/public/assets/uploads/banners/';
if (!is_dir($banners_dir)) {
    mkdir($banners_dir, 0777, true);
}

// create dummy image
$dummy_image_path = $banners_dir . 'dummy_banner.jpg';
if (!file_exists($dummy_image_path)) {
    $img = imagecreatetruecolor(1200, 400);
    $bg = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
    $fg = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bg);
    imagestring($img, 5, 550, 190, "Banner", $fg);
    imagejpeg($img, $dummy_image_path);
    imagedestroy($img);
}

foreach ($events as $e) {
    $filename = 'event_' . uniqid() . '.jpg';
    copy($dummy_image_path, $banners_dir . $filename);
    $stmt = $pdo->prepare("UPDATE events SET banner_path = ? WHERE id = ?");
    $stmt->execute([$filename, $e['id']]);
}

// ALBUMS AND PHOTOS
echo "Creating albums and photos...\n";
$photos_dir = __DIR__ . '/public/assets/uploads/gallery/';
if (!is_dir($photos_dir)) {
    mkdir($photos_dir, 0777, true);
}

// create dummy photo
$dummy_photo_path = $photos_dir . 'dummy_photo.jpg';
if (!file_exists($dummy_photo_path)) {
    $img = imagecreatetruecolor(800, 600);
    $bg = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
    $fg = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bg);
    imagestring($img, 5, 350, 290, "Galeria", $fg);
    imagejpeg($img, $dummy_photo_path);
    imagedestroy($img);
}

$album_names = ['Retiro 2023', 'Batismo nas Águas', 'Culto de Missões', 'Conferência de Jovens'];
foreach ($album_names as $an) {
    $stmt = $pdo->prepare("INSERT INTO photo_albums (title, description, event_date) VALUES (?, ?, NOW())");
    $stmt->execute([$an, "Fotos do evento $an"]);
    $album_id = $pdo->lastInsertId();
    
    for($i=0; $i<8; $i++) {
        $filename = uniqid() . '.jpg';
        copy($dummy_photo_path, $photos_dir . $filename);
        $stmt = $pdo->prepare("INSERT INTO photos (album_id, filename) VALUES (?, ?)");
        $stmt->execute([$album_id, $filename]);
    }
}

echo "Media seeded successfully!\n";

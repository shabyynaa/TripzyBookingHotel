<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require '../config/db.php';

$location = $_GET['loc'] ?? 'all';
$id       = $_GET['id']  ?? null;

// Get detail satu hotel
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$id]);
    $hotel = $stmt->fetch();

    if (!$hotel) {
        echo json_encode(['error' => 'Hotel tidak ditemukan']);
        exit;
    }

    // Ambil kamar
    $stmt2 = $pdo->prepare("SELECT * FROM rooms WHERE hotel_id = ?");
    $stmt2->execute([$id]);
    $hotel['rooms'] = $stmt2->fetchAll();

    // Ambil review
    $stmt3 = $pdo->prepare("SELECT * FROM reviews WHERE hotel_id = ?");
    $stmt3->execute([$id]);
    $hotel['reviews'] = $stmt3->fetchAll();

    // Decode JSON fields
    $hotel['facilities'] = json_decode($hotel['facilities']);
    foreach ($hotel['rooms'] as &$room) {
        $room['facilities'] = json_decode($room['facilities']);
    }

    echo json_encode($hotel);
    exit;
}

// Get semua hotel / filter by lokasi
if ($location === 'all') {
    $stmt = $pdo->query("SELECT * FROM hotels ORDER BY rating DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE location = ? ORDER BY rating DESC");
    $stmt->execute([$location]);
}

$hotels = $stmt->fetchAll();

// Decode JSON fields + ambil harga kamar termurah
foreach ($hotels as &$hotel) {
    $hotel['facilities'] = json_decode($hotel['facilities']);

    $stmt2 = $pdo->prepare("SELECT MIN(price) as min_price FROM rooms WHERE hotel_id = ?");
    $stmt2->execute([$hotel['id']]);
    $hotel['min_price'] = $stmt2->fetch()['min_price'];
}

echo json_encode($hotels);
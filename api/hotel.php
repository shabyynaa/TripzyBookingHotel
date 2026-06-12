<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
session_start();
require '../config/db.php';

$method   = $_SERVER['REQUEST_METHOD'];
$location = $_GET['loc'] ?? 'all';
$id       = $_GET['id']  ?? null;

// ── GET ──
if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
        $stmt->execute([$id]);
        $hotel = $stmt->fetch();
        if (!$hotel) { echo json_encode(['error' => 'Hotel tidak ditemukan']); exit; }

        $stmt2 = $pdo->prepare("SELECT * FROM rooms WHERE hotel_id = ?");
        $stmt2->execute([$id]);
        $hotel['rooms'] = $stmt2->fetchAll();

        $stmt3 = $pdo->prepare("SELECT * FROM reviews WHERE hotel_id = ?");
        $stmt3->execute([$id]);
        $hotel['reviews_list'] = $stmt3->fetchAll();

        $hotel['facilities'] = json_decode($hotel['facilities']);
        foreach ($hotel['rooms'] as &$room) $room['facilities'] = json_decode($room['facilities']);

        echo json_encode($hotel);
        exit;
    }

    if ($location === 'all') {
        $stmt = $pdo->query("SELECT * FROM hotels ORDER BY rating DESC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM hotels WHERE location = ? ORDER BY rating DESC");
        $stmt->execute([$location]);
    }
    $hotels = $stmt->fetchAll();
    foreach ($hotels as &$hotel) {
        $hotel['facilities'] = json_decode($hotel['facilities']);
        $stmt2 = $pdo->prepare("SELECT MIN(price) as min_price FROM rooms WHERE hotel_id = ?");
        $stmt2->execute([$hotel['id']]);
        $hotel['min_price'] = $stmt2->fetch()['min_price'];
    }
    echo json_encode($hotels);
    exit;
}

// Cek admin untuk write
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// ── POST ──
if ($method === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO hotels (name, location, thumb_url, rating, review_count, facilities) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['name'], $data['location'], $data['thumb_url'],
        $data['rating'], $data['review_count'], $data['facilities']
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

    // ── PUT ──
    if ($method === 'PUT' && $id) {
        $stmtCount = $pdo->prepare("SELECT COUNT(*) as cnt FROM reviews WHERE hotel_id = ?");
        $stmtCount->execute([$id]);
        $reviewCount = $stmtCount->fetch()['cnt'];

        $stmt = $pdo->prepare("UPDATE hotels SET name=?, location=?, thumb_url=?, rating=?, review_count=?, facilities=? WHERE id=?");
        $stmt->execute([
            $data['name'], $data['location'], $data['thumb_url'],
            $data['rating'], $reviewCount, $data['facilities'], $id
        ]);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['error' => 'Method tidak dikenali']);
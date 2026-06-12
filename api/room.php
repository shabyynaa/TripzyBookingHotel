<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
session_start();
require '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? null;

// ── GET ──
if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        $room = $stmt->fetch();
        if ($room) $room['facilities'] = json_decode($room['facilities']);
        echo json_encode($room ?: ['error' => 'Not found']);
    } else {
        $stmt = $pdo->query("SELECT * FROM rooms ORDER BY hotel_id, id");
        $rooms = $stmt->fetchAll();
        foreach ($rooms as &$r) $r['facilities'] = json_decode($r['facilities']);
        echo json_encode($rooms);
    }
    exit;
}

// Cek admin untuk operasi write
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// ── POST ──
if ($method === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO rooms (hotel_id, name, price, img, facilities) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['hotel_id'],
        $data['name'],
        $data['price'],
        $data['img'] ?? '',
        $data['facilities'] ?? '[]',
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// ── PUT ──
if ($method === 'PUT' && $id) {
    $stmt = $pdo->prepare("UPDATE rooms SET hotel_id=?, name=?, price=?, img=?, facilities=? WHERE id=?");
    $stmt->execute([
        $data['hotel_id'],
        $data['name'],
        $data['price'],
        $data['img'] ?? '',
        $data['facilities'] ?? '[]',
        $id,
    ]);
    echo json_encode(['success' => true]);
    exit;
}

// ── DELETE ──
if ($method === 'DELETE' && $id) {
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Method tidak dikenali']);
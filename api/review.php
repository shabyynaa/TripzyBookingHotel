<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');
session_start();
require '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: ambil review by hotel_id ──
if ($method === 'GET') {
    $hotelId = $_GET['hotel_id'] ?? null;

    if (!$hotelId) {
        echo json_encode(['success' => false, 'message' => 'hotel_id diperlukan']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT r.*, u.full_name as user_name
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.hotel_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$hotelId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// ── POST: user submit review ──
if ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Belum login']);
        exit;
    }

    $data    = json_decode(file_get_contents('php://input'), true);
    $hotelId = $data['hotel_id'] ?? null;
    $rating  = $data['rating']   ?? null;
    $comment = $data['comment']  ?? '';

    if (!$hotelId || !$rating) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }

    // Cek apakah user sudah pernah booking hotel ini
    $stmtCheck = $pdo->prepare("
        SELECT id FROM bookings 
        WHERE user_id = ? AND hotel_id = ? AND status = 'confirmed'
    ");
    $stmtCheck->execute([$_SESSION['user_id'], $hotelId]);
    if (!$stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Kamu belum pernah menginap di hotel ini']);
        exit;
    }

    // Cek apakah sudah pernah review hotel ini
    $stmtDup = $pdo->prepare("
        SELECT id FROM reviews WHERE user_id = ? AND hotel_id = ?
    ");
    $stmtDup->execute([$_SESSION['user_id'], $hotelId]);
    if ($stmtDup->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Kamu sudah pernah mereview hotel ini']);
        exit;
    }

    // Simpan review
    $reviewer = $_SESSION['full_name'];
    $stmt = $pdo->prepare("
        INSERT INTO reviews (hotel_id, user_id, reviewer, rating, comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$hotelId, $_SESSION['user_id'], $reviewer, $rating, $comment]);

    // Update rating hotel otomatis
    $stmtRating = $pdo->prepare("
        UPDATE hotels SET 
            rating = (SELECT AVG(rating) FROM reviews WHERE hotel_id = ?),
            review_count = (SELECT COUNT(*) FROM reviews WHERE hotel_id = ?)
        WHERE id = ?
    ");
    $stmtRating->execute([$hotelId, $hotelId, $hotelId]);

    echo json_encode(['success' => true, 'message' => 'Review berhasil dikirim!']);
    exit;
}

echo json_encode(['error' => 'Method tidak dikenali']);
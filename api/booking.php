<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');
session_start();
require '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET BOOKING ──
if ($method === 'GET') {
    $userId      = $_GET['user_id']  ?? null;
    $bookingCode = $_GET['code']     ?? null;

    // Get satu booking by code
    if ($bookingCode) {
        $stmt = $pdo->prepare("
            SELECT b.*, h.name as hotel_name, r.name as room_name
            FROM bookings b
            JOIN hotels h ON b.hotel_id = h.id
            JOIN rooms r  ON b.room_id  = r.id
            WHERE b.booking_code = ?
        ");
        $stmt->execute([$bookingCode]);
        $booking = $stmt->fetch();
        if ($booking) {
            echo json_encode(['success' => true, 'booking' => $booking]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan']);
        }
        exit;
    }

    // Get semua booking by user
    if ($userId) {
        $stmt = $pdo->prepare("
            SELECT b.*, h.name as hotel_name, r.name as room_name
            FROM bookings b
            JOIN hotels h ON b.hotel_id = h.id
            JOIN rooms r  ON b.room_id  = r.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$userId]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    echo json_encode([]);
    exit;
}

// ── POST — BUAT BOOKING BARU ──
if ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Belum login']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $hotelId       = $data['hotel_id']       ?? null;
    $roomId        = $data['room_id']         ?? null;
    $guestName     = $data['guest_name']      ?? '';
    $guestKtp      = $data['guest_ktp']       ?? '';
    $guestPhone    = $data['guest_phone']     ?? '';
    $roomCount     = $data['room_count']      ?? 1;
    $adultCount    = $data['adult_count']     ?? 1;
    $childCount    = $data['child_count']     ?? 0;
    $checkin       = $data['checkin']         ?? null;
    $checkout      = $data['checkout']        ?? null;
    $totalPrice    = $data['total_price']     ?? 0;
    $paymentMethod = $data['payment_method']  ?? '';
    $specialReq    = $data['special_req']     ?? '';

    if (!$hotelId || !$roomId || !$guestName || !$totalPrice) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }

    // Generate booking code unik
    $bookingCode = 'TRPZ-' . strtoupper(substr(uniqid(), -6));

    $stmt = $pdo->prepare("
        INSERT INTO bookings 
        (booking_code, user_id, hotel_id, room_id, guest_name, guest_ktp, guest_phone,
         room_count, adult_count, child_count, checkin, checkout, total_price, 
         payment_method, special_req, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
    ");
    $stmt->execute([
        $bookingCode, $_SESSION['user_id'], $hotelId, $roomId,
        $guestName, $guestKtp, $guestPhone,
        $roomCount, $adultCount, $childCount,
        $checkin, $checkout, $totalPrice,
        $paymentMethod, $specialReq
    ]);

    // Ambil data booking lengkap untuk ditampilkan di receipt
    $stmt2 = $pdo->prepare("
        SELECT b.*, h.name as hotel_name, r.name as room_name
        FROM bookings b
        JOIN hotels h ON b.hotel_id = h.id
        JOIN rooms r  ON b.room_id  = r.id
        WHERE b.booking_code = ?
    ");
    $stmt2->execute([$bookingCode]);
    $booking = $stmt2->fetch();

    echo json_encode(['success' => true, 'booking' => $booking]);
    exit;
}

echo json_encode(['error' => 'Method tidak dikenali']);
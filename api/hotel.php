<?php
// ============================================================
//  hotel.php — REVISI
//  - Bisa nerima data biasa (JSON) MAUPUN data + file (multipart/form-data)
//  - Selalu balas JSON walau ada error tak terduga, jadi gak ada lagi
//    "Unexpected end of JSON input" di sisi admin.php
// ============================================================

ini_set('display_errors', '0'); // jangan tampilin error PHP sebagai HTML, biar response tetap JSON murni
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require '../config/db.php';

// ---- helper: kirim JSON terus langsung berhenti ----
function sendJson($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// ---- tangkap exception/error yang gak ke-handle -> tetap balas JSON, bukan halaman HTML kosong ----
set_exception_handler(function (Throwable $e) {
    sendJson(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
});

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $err['message']]);
    }
});

$method   = $_SERVER['REQUEST_METHOD'];
$location = $_GET['loc'] ?? 'all';
$id       = $_GET['id']  ?? null;

// ── GET (publik, gak perlu login) ──
if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
        $stmt->execute([$id]);
        $hotel = $stmt->fetch();
        if (!$hotel) sendJson(['error' => 'Hotel tidak ditemukan']);

        $stmt2 = $pdo->prepare("SELECT * FROM rooms WHERE hotel_id = ?");
        $stmt2->execute([$id]);
        $hotel['rooms'] = $stmt2->fetchAll();

        $stmt3 = $pdo->prepare("SELECT * FROM reviews WHERE hotel_id = ?");
        $stmt3->execute([$id]);
        $hotel['reviews_list'] = $stmt3->fetchAll();

        $hotel['facilities'] = json_decode($hotel['facilities']);
        foreach ($hotel['rooms'] as &$room) $room['facilities'] = json_decode($room['facilities']);

        sendJson($hotel);
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
    sendJson($hotels);
}

// ── Mulai sini wajib login sebagai admin ──
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'admin') {
    sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
}

// ── Deteksi: request ini multipart (ada file) atau JSON biasa ──
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isMultipart = stripos($contentType, 'multipart/form-data') !== false;

if ($isMultipart) {
    $data = $_POST;               // field teks nempel di $_POST, file nempel di $_FILES
} else {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
}

// ── DELETE: hapus hotel ──
if ($method === 'DELETE' && $id) {
    $stmt = $pdo->prepare("SELECT id, thumb_url FROM hotels WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) sendJson(['success' => false, 'message' => 'Hotel tidak ditemukan'], 404);

    // Hapus thumbnail dari server kalau ada
    if (!empty($existing['thumb_url'])) {
        $thumbPath = __DIR__ . '/..' . $existing['thumb_url'];
        if (file_exists($thumbPath)) unlink($thumbPath);
    }

    // Hapus rooms & reviews terkait dulu (kalau belum pakai ON DELETE CASCADE)
    $pdo->prepare("DELETE FROM rooms WHERE hotel_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM reviews WHERE hotel_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM hotels WHERE id = ?")->execute([$id]);

    sendJson(['success' => true, 'message' => 'Hotel berhasil dihapus']);
}

// ── Validasi field wajib (hanya untuk POST / PUT) ──
foreach (['name', 'location', 'rating'] as $field) {
    if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
        sendJson(['success' => false, 'message' => "Field '$field' wajib diisi"], 400);
    }
}

// ── Normalisasi facilities: terima "Wifi, Pool, Breakfast" ATAU JSON array string ──
function normalizeFacilities($raw): string {
    if (is_array($raw)) return json_encode($raw);
    $trimmed = trim((string) $raw);
    $decoded = json_decode($trimmed, true);
    if (is_array($decoded)) return json_encode($decoded);
    $parts = array_values(array_filter(array_map('trim', explode(',', $trimmed))));
    return json_encode($parts);
}
$facilitiesJson = normalizeFacilities($data['facilities'] ?? '[]');

// ── Proses upload thumbnail (kalau ada file baru di request ini) ──
function handleThumbnailUpload(): ?string {
    if (!isset($_FILES['thumb_url']) || $_FILES['thumb_url']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // gak ada file baru, biarin nilai lama
    }
    $file = $_FILES['thumb_url'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        sendJson(['success' => false, 'message' => 'Upload gagal (kode error ' . $file['error'] . ')'], 400);
    }

    $allowedExt  = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : '';

    if (!in_array($ext, $allowedExt, true) || ($mime && !in_array($mime, $allowedMime, true))) {
        sendJson(['success' => false, 'message' => 'Format file harus jpg, jpeg, png, atau webp'], 400);
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        sendJson(['success' => false, 'message' => 'Ukuran file maksimal 5MB'], 400);
    }

    $uploadDir = __DIR__ . '/../uploads/hotels/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        // jaga-jaga: file yang keupload gak bisa dieksekusi sebagai PHP
        file_put_contents($uploadDir . '.htaccess', "php_flag engine off\n");
    }

    $filename    = uniqid('hotel_', true) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        sendJson(['success' => false, 'message' => 'Gagal menyimpan file ke server'], 500);
    }

    // Sesuaikan path ini kalau struktur folder di server kamu beda
    return '/uploads/hotels/' . $filename;
}

// ── POST: tambah hotel baru, ATAU update kalau ada "id" ──
// (update lewat POST -bukan PUT- karena PHP cuma parsing $_FILES otomatis kalau method-nya POST)
if ($method === 'POST') {
    $newThumb = handleThumbnailUpload();
    $editId   = $data['id'] ?? null;

    if ($editId) {
        $stmt = $pdo->prepare("SELECT thumb_url FROM hotels WHERE id = ?");
        $stmt->execute([$editId]);
        $existing = $stmt->fetch();
        if (!$existing) sendJson(['success' => false, 'message' => 'Hotel tidak ditemukan'], 404);

        $thumbUrl = $newThumb ?? $existing['thumb_url'];

        $stmtCount = $pdo->prepare("SELECT COUNT(*) as cnt FROM reviews WHERE hotel_id = ?");
        $stmtCount->execute([$editId]);
        $reviewCount = $stmtCount->fetch()['cnt'];

        $stmt = $pdo->prepare("UPDATE hotels SET name=?, location=?, thumb_url=?, rating=?, review_count=?, facilities=? WHERE id=?");
        $stmt->execute([$data['name'], $data['location'], $thumbUrl, $data['rating'], $reviewCount, $facilitiesJson, $editId]);

        sendJson(['success' => true, 'id' => $editId]);
    }

    $stmt = $pdo->prepare("INSERT INTO hotels (name, location, thumb_url, rating, review_count, facilities) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data['name'], $data['location'], $newThumb ?? '', $data['rating'], $data['review_count'] ?? 0, $facilitiesJson]);

    sendJson(['success' => true, 'id' => $pdo->lastInsertId()]);
}

// ── PUT: tetap didukung untuk update TANPA ganti file (kirim JSON biasa) ──
if ($method === 'PUT' && $id) {
    $stmt = $pdo->prepare("SELECT thumb_url FROM hotels WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) sendJson(['success' => false, 'message' => 'Hotel tidak ditemukan'], 404);

    $thumbUrl = $data['thumb_url'] ?? $existing['thumb_url'];

    $stmtCount = $pdo->prepare("SELECT COUNT(*) as cnt FROM reviews WHERE hotel_id = ?");
    $stmtCount->execute([$id]);
    $reviewCount = $stmtCount->fetch()['cnt'];

    $stmt = $pdo->prepare("UPDATE hotels SET name=?, location=?, thumb_url=?, rating=?, review_count=?, facilities=? WHERE id=?");
    $stmt->execute([$data['name'], $data['location'], $thumbUrl, $data['rating'], $reviewCount, $facilitiesJson, $id]);

    sendJson(['success' => true]);
}

sendJson(['success' => false, 'message' => 'Method tidak dikenali'], 405);

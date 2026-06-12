<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file   = $_FILES['image'];
$folder = $_POST['folder'] ?? 'uploads'; // 'hotels' atau 'rooms'
$ext    = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Format file tidak didukung']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File terlalu besar (max 5MB)']);
    exit;
}

// Buat folder kalau belum ada
$uploadDir = __DIR__ . '/../img/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename = uniqid($folder . '_') . '.' . $ext;
$destPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => true, 'path' => 'img/' . $filename]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
}
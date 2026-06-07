<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');
session_start();
require '../config/db.php';

$action = $_GET['action'] ?? '';
$data   = json_decode(file_get_contents('php://input'), true);

// ── REGISTER ──
if ($action === 'register') {
    $username  = trim($data['username'] ?? '');
    $password  = trim($data['password'] ?? '');
    $full_name = trim($data['full_name'] ?? '');
    $ktp       = trim($data['ktp'] ?? '');
    $phone     = trim($data['phone'] ?? '');

    if (!$username || !$password || !$full_name || !$ktp || !$phone) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username sudah dipakai']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt   = $pdo->prepare("INSERT INTO users (username, password, full_name, ktp, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $hashed, $full_name, $ktp, $phone]);

    echo json_encode(['success' => true, 'message' => 'Registrasi berhasil']);
    exit;
}

// ── LOGIN ──
if ($action === 'login') {
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');

    if (!$username || !$password) {
        echo json_encode(['success' => false, 'message' => 'Username dan password wajib diisi']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Username atau password salah']);
        exit;
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    echo json_encode([
        'success' => true,
        'user'    => [
            'id'        => $user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'],
            'ktp'       => $user['ktp'],
            'phone'     => $user['phone'] ?? '',
            'role'      => $user['role'] ?? '',
        ]
    ]);
    exit;
}

// ── CEK SESSION (me) ──
if ($action === 'me') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Belum login']);
        exit;
    }
    echo json_encode([
        'success' => true,
        'user'    => [
            'id'        => $_SESSION['user_id'],
            'username'  => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role'      => $_SESSION['role'],
            'phone'     => $_SESSION['phone'],
            'ktp'       => $_SESSION['ktp'],
        ]
    ]);
    exit;
}

// ── LOGOUT ──
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
    exit;
}

echo json_encode(['error' => 'Action tidak dikenali']);
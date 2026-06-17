<?php
$host     = 'sql103.infinityfree.com'; // cek di cPanel kamu
$dbname   = 'if0_42189740_fp_pweb_bookinghotel'; // nama DB dari cPanel
$username = 'if0_42189740'; // username dari cPanel
$password = 'Gk4NeJYLOtSvBY';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Koneksi gagal: ' . $e->getMessage()]));
}
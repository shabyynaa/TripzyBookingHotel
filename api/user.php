<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$stmt  = $pdo->query("SELECT id, username, full_name, phone, ktp, role, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
echo json_encode($users);
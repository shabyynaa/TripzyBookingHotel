<?php
require 'db.php';
$stmt = $pdo->query("SELECT COUNT(*) as total FROM hotels");
$result = $stmt->fetch();
echo "Koneksi berhasil! Total hotel: " . $result['total'];
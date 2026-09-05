<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadaan') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("UPDATE permintaan_barang SET status = 'Approved_Biro_Umum' WHERE id = ? AND (status = 'Diproses_Vendor' OR status = 'Confirmed_by_Vendor')");
    $stmt->execute([$id]);
}

header("Location: dashboard.php?status=success");
exit;
<?php
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemohon') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("UPDATE permintaan_barang SET status = 'Pending', catatan_biro_umum = NULL WHERE id = ? AND user_id = ? AND status = 'Rejected'");
    $stmt->execute([$id, $_SESSION['user_id']]);
}
header("Location: dashboard.php?status=success");
exit;
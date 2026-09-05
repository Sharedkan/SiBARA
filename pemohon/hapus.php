<?php
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemohon') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM permintaan_barang WHERE id = ? AND user_id = ? AND status = 'Pending'");
    $stmt->execute([$id, $_SESSION['user_id']]);
}
header("Location: dashboard.php?status=success");
exit;
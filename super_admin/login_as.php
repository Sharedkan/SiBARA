<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $targetUser = $stmt->fetch();

    if ($targetUser) {
        // Simpan state admin asli jika diperlukan, atau langsung Timpa session dengan user tujuan
        $_SESSION['user_id'] = $targetUser['id'];
        $_SESSION['nama']    = $targetUser['nama'];
        $_SESSION['role']    = $targetUser['role'];
        $_SESSION['bidang']  = $targetUser['bidang'];

        // Arahkan ke root untuk memicu smart router sesuai role baru
        header("Location: ../index.php");
        exit;
    }
}

header("Location: dashboard.php");
exit;
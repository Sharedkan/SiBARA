<?php
require_once '../config/database.php';

// Pastikan yang mengakses adalah Super Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && !isset($_SESSION['is_impersonating']))) {
    die("Akses ditolak. Anda harus login sebagai Super Admin.");
}

$target_id = $_GET['id'] ?? null;
if (!$target_id) {
    header("Location: ../super_admin/dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$target_id]);
$targetUser = $stmt->fetch();

if (!$targetUser) {
    die("Pengguna tidak ditemukan.");
}

// Jika belum mencatat admin asli, simpan ID admin asli terlebih dahulu
if (!isset($_SESSION['admin_original_id'])) {
    $_SESSION['admin_original_id'] = $_SESSION['user_id'];
    $_SESSION['admin_original_nama'] = $_SESSION['nama'];
}

// Timpa sesi dengan data user yang dituju
$_SESSION['user_id'] = $targetUser['id'];
$_SESSION['nama']    = $targetUser['nama'];
$_SESSION['role']    = $targetUser['role'];
$_SESSION['bidang']  = $targetUser['bidang'];
$_SESSION['is_impersonating'] = true;

// Arahkan ke dashboard user tujuan
header("Location: ../index.php");
exit;
?>
<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
// Mencegah super admin menghapus akunnya sendiri
if ($id && $id != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: dashboard.php");
exit;
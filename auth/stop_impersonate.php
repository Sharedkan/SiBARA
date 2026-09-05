<?php
require_once '../config/database.php';

if (isset($_SESSION['admin_original_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_original_id']]);
    $admin = $stmt->fetch();

    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['nama']    = $admin['nama'];
        $_SESSION['role']    = $admin['role'];
        $_SESSION['bidang']  = $admin['bidang'];
        
        unset($_SESSION['is_impersonating']);
        unset($_SESSION['admin_original_id']);
        unset($_SESSION['admin_original_nama']);

        header("Location: ../super_admin/dashboard.php");
        exit;
    }
}
header("Location: ../index.php");
exit;
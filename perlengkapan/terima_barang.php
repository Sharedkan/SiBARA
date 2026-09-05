<?php
require_once '../config/database.php';

// Validasi akses role perlengkapan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'perlengkapan') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $pdo->beginTransaction();

        // Pastikan status sebelumnya valid untuk diterima
        $stmtCheck = $pdo->prepare("SELECT status FROM permintaan_barang WHERE id = ?");
        $stmtCheck->execute([$id]);
        $req = $stmtCheck->fetch();

        if ($req && ($req['status'] === 'Diproses_Vendor' || $req['status'] === 'Confirmed_by_Vendor')) {
            // Ubah status menjadi Completed
            $stmtUpdate = $pdo->prepare("UPDATE permintaan_barang SET status = 'Completed' WHERE id = ?");
            $stmtUpdate->execute([$id]);

            $pdo->commit();
            header("Location: dashboard.php?status=success");
            exit;
        } else {
            $pdo->rollBack();
            die("Aksi tidak valid atau barang sudah diterima.");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Terjadi kesalahan: " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>
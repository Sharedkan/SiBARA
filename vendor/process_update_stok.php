<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permintaan_id = $_POST['permintaan_id'];
    $items_vendor  = $_POST['items'] ?? [];
    $vendor_user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        foreach ($items_vendor as $detail_id => $data) {
            $jumlah_vendor = $data['jumlah_vendor'];

            $stmtCheck = $pdo->prepare("SELECT nama_barang, jumlah_approved FROM detail_permintaan WHERE id = ?");
            $stmtCheck->execute([$detail_id]);
            $old = $stmtCheck->fetch();

            if ($old && $old['jumlah_approved'] != $jumlah_vendor) {
                $logDesc = "Vendor menyesuaikan stok '{$old['nama_barang']}' dari ACC Biro Umum ({$old['jumlah_approved']}) menjadi {$jumlah_vendor}";
                $stmtLog = $pdo->prepare("INSERT INTO riwayat_perubahan (permintaan_id, aktor, aktor_id, deskripsi_perubahan) VALUES (?, 'Vendor', ?, ?)");
                $stmtLog->execute([$permintaan_id, $vendor_user_id, $logDesc]);
            }

            $stmtUpd = $pdo->prepare("UPDATE detail_permintaan SET jumlah_vendor = ? WHERE id = ?");
            $stmtUpd->execute([$jumlah_vendor, $detail_id]);
        }

        $stmtStatus = $pdo->prepare("UPDATE permintaan_barang SET status = 'Confirmed_by_Vendor' WHERE id = ?");
        $stmtStatus->execute([$permintaan_id]);

        $pdo->commit();
        header("Location: dashboard.php?status=confirmed");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'biro_umum') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permintaan_id = $_POST['permintaan_id'];
    $catatan       = $_POST['catatan'];
    $items_revisi  = $_POST['items'] ?? [];
    $biro_umum_id  = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        foreach ($items_revisi as $detail_id => $data) {
            $jumlah_approved = $data['jumlah_approved'];

            $stmtCheck = $pdo->prepare("SELECT nama_barang, jumlah_approved FROM detail_permintaan WHERE id = ?");
            $stmtCheck->execute([$detail_id]);
            $old = $stmtCheck->fetch();

            if ($old && $old['jumlah_approved'] != $jumlah_approved) {
                $logDesc = "Biro Umum mengubah jumlah '{$old['nama_barang']}' dari {$old['jumlah_approved']} menjadi {$jumlah_approved}";
                $stmtLog = $pdo->prepare("INSERT INTO riwayat_perubahan (permintaan_id, aktor, aktor_id, deskripsi_perubahan) VALUES (?, 'Biro_Umum', ?, ?)");
                $stmtLog->execute([$permintaan_id, $biro_umum_id, $logDesc]);
            }

            $stmtUpd = $pdo->prepare("UPDATE detail_permintaan SET jumlah_approved = ? WHERE id = ?");
            $stmtUpd->execute([$jumlah_approved, $detail_id]);
        }

        $stmtStatus = $pdo->prepare("UPDATE permintaan_barang SET status = 'Approved_Biro_Umum', catatan_biro_umum = ? WHERE id = ?");
        $stmtStatus->execute([$catatan, $permintaan_id]);

        $pdo->commit();
        header("Location: dashboard.php?status=success");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
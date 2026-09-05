<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'perlengkapan') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permintaan_id = $_POST['permintaan_id'];
    $sumber_dana   = $_POST['sumber_dana'];

    try {
        $pdo->beginTransaction();

        $stmtReq = $pdo->prepare("SELECT user_id, vendor_id FROM permintaan_barang WHERE id = ?");
        $stmtReq->execute([$permintaan_id]);
        $req = $stmtReq->fetch();

        if (!$req) throw new Exception("Data tidak ditemukan.");

        $bidang_id = $req['user_id'];
        $vendor_id = $req['vendor_id'];

        $stmtDetail = $pdo->prepare("SELECT * FROM detail_permintaan WHERE permintaan_id = ?");
        $stmtDetail->execute([$permintaan_id]);
        $items = $stmtDetail->fetchAll();

        $stmtInv = $pdo->prepare("INSERT INTO inventaris (kode_inventaris, nama_barang, spesifikasi, bidang_pemegang_id, tanggal_pembelian, sumber_dana, status_kelayakan, vendor_pengadaan_id) VALUES (?, ?, ?, ?, CURDATE(), ?, 'Baik', ?)");

        foreach ($items as $item) {
            if ($item['sifat_barang'] === 'Tidak_Habis_Pakai' && $item['jumlah_vendor'] > 0) {
                for ($i = 1; $i <= $item['jumlah_vendor']; $i++) {
                    $kode_unik = "INV/" . date('Y') . "/" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
                    $stmtInv->execute([
                        $kode_unik,
                        $item['nama_barang'],
                        $item['spesifikasi'],
                        $bidang_id,
                        $sumber_dana,
                        $vendor_id
                    ]);
                }
            }
        }

        $stmtUpd = $pdo->prepare("UPDATE permintaan_barang SET status = 'Completed', sumber_dana = ? WHERE id = ?");
        $stmtUpd->execute([$sumber_dana, $permintaan_id]);

        $pdo->commit();
        header("Location: dashboard.php?status=completed");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
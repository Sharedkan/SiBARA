<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../auth/login.php");
    exit;
}

$vendor_id = $_SESSION['user_id'];

try {
    // Ambil data pengadaan yang hanya ditugaskan ke vendor yang sedang login
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama, u.bidang 
        FROM permintaan_barang p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.vendor_id = ? AND p.status IN ('Diproses_Vendor', 'Confirmed_by_Vendor', 'Completed')
        ORDER BY p.id DESC
    ");
    $stmt->execute([$vendor_id]);
    $pengajuans = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Vendor - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <?php if (isset($_SESSION['is_impersonating'])): ?>
        <div class="alert alert-warning text-center m-0 rounded-0 py-2" role="alert">
            Anda sedang melihat sebagai <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong> (Mode Impersonasi). 
            <a href="../auth/stop_impersonate.php" class="btn btn-sm btn-dark ms-3">Kembali ke Akun Super Admin</a>
        </div>
    <?php endif; ?>

    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand fw-bold">SiBARA - Panel Vendor (Mitra Pengadaan)</span>
            <div>
                <span class="text-white me-3">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../auth/logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <h4>Daftar Pesanan / Pengadaan Barang Masuk</h4>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Status pengadaan berhasil diperbarui.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm mt-3">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Pemohon / Bidang</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status Pesanan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pengajuans) > 0): ?>
                            <?php foreach ($pengajuans as $p): ?>
                                <?php 
                                    $raw_status = trim($p['status'] ?? '');
                                ?>
                                <tr>
                                    <td>#<?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['nama']) ?> (<?= htmlspecialchars($p['bidang'] ?? '-') ?>)</td>
                                    <td><?= $p['tanggal_pengajuan'] ?></td>
                                    <td>
                                        <?php if ($raw_status === 'Diproses_Vendor' || $raw_status === 'Confirmed_by_Vendor'): ?>
                                            <span class="badge bg-primary">Sedang Dikerjakan / Diproses</span>
                                        <?php elseif ($raw_status === 'Completed'): ?>
                                            <span class="badge bg-success">Selesai / Diterima</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($raw_status) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="detail_pesanan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat Detail Item</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">Belum ada pesanan atau penugasan pengadaan barang untuk Anda.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
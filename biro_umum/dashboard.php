<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'biro_umum') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT p.*, u.nama, u.bidang 
        FROM permintaan_barang p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.id DESC
    ");
    $pengajuans = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Biro Umum - SiBARA</title>
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
            <span class="navbar-brand fw-bold">SiBARA - Panel Biro Umum (Approver)</span>
            <div>
                <span class="text-white me-3">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../auth/logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <h4>Daftar Pengajuan Barang Masuk</h4>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Pengajuan berhasil diproses.
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
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pengajuans) > 0): ?>
                            <?php foreach ($pengajuans as $p): ?>
                                <tr>
                                    <td>#<?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['nama']) ?> (<?= htmlspecialchars($p['bidang'] ?? '-') ?>)</td>
                                    <td><?= $p['tanggal_pengajuan'] ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($p['status']) {
                                                'Pending' => 'warning text-dark',
                                                'Approved_Biro_Umum' => 'info',
                                                'Confirmed_by_Vendor' => 'primary',
                                                'Completed' => 'success',
                                                'Rejected' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?= $p['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="review.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Review / Proses</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">Belum ada pengajuan barang.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
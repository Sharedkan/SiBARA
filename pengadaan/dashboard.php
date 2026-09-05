<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadaan') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    // Ambil semua data selain status Pending
    $stmt = $pdo->query("
        SELECT p.*, u.nama, u.bidang 
        FROM permintaan_barang p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.status != 'Pending'
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
    <title>Dashboard Pengadaan - SiBARA</title>
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
            <span class="navbar-brand fw-bold">SiBARA - Panel Pengadaan</span>
            <div>
                <span class="text-white me-3">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../auth/logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <h4>Manajemen Pengadaan Barang</h4>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                Pengadaan berhasil diperbarui.
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
                            <th>Sumber Dana</th>
                            <th>Status Terkini</th>
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
                                    <td><?= htmlspecialchars($p['sumber_dana'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($raw_status === 'Approved_Biro_Umum'): ?>
                                            <span class="badge bg-info text-dark">Approved Biro Umum</span>
                                        <?php elseif ($raw_status === 'Diproses_Vendor' || $raw_status === 'Confirmed_by_Vendor'): ?>
                                            <span class="badge bg-primary">Diproses Vendor</span>
                                        <?php elseif ($raw_status === 'Completed'): ?>
                                            <span class="badge bg-success">Selesai</span>
                                        <?php elseif ($raw_status === 'Rejected'): ?>
                                            <span class="badge bg-danger">Ditolak</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($raw_status ?: 'Belum Diproses') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($raw_status === 'Diproses_Vendor' || $raw_status === 'Confirmed_by_Vendor'): ?>
                                            <a href="batal_proses.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger mb-1" onclick="return confirm('Batalkan proses pengadaan ini?')">Batalkan</a>
                                            <a href="proses.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning mb-1">Edit</a>
                                        <?php elseif ($raw_status === 'Completed'): ?>
                                            <span class="text-success fw-bold">Selesai</span>
                                        <?php else: ?>
                                            <!-- Tombol Default / Fallback agar selalu muncul tombol proses jika status selain di atas -->
                                            <a href="proses.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Proses Pengadaan</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">Belum ada data pengadaan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            if (url.searchParams.has('status')) {
                url.searchParams.delete('status');
                window.history.replaceState({path: url.href}, '', url.href);
            }
        }
    </script>
</body>
</html>
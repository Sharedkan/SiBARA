<?php
require_once 'config/database.php';

try {
    // 1. Ambil Statistik Ringkasan Status
    $stmtStats = $pdo->query("
        SELECT 
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as total_pending,
            SUM(CASE WHEN status = 'Approved_Biro_Umum' THEN 1 ELSE 0 END) as total_approved_biro,
            SUM(CASE WHEN status = 'Confirmed_by_Vendor' THEN 1 ELSE 0 END) as total_vendor,
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as total_completed,
            COUNT(*) as total_pengajuan
        FROM permintaan_barang
    ");
    $stats = $stmtStats->fetch();

    // 2. Ambil Statistik Pengajuan Berdasarkan Bidang / Pemohon
    $stmtBidang = $pdo->query("
        SELECT 
            u.bidang, 
            u.nama as nama_pemohon,
            COUNT(p.id) as jumlah_pengajuan,
            SUM(CASE WHEN p.status = 'Completed' THEN 1 ELSE 0 END) as selesai,
            SUM(CASE WHEN p.status = 'Pending' THEN 1 ELSE 0 END) as pending
        FROM users u
        LEFT JOIN permintaan_barang p ON u.id = p.user_id
        WHERE u.role = 'pemohon'
        GROUP BY u.id, u.bidang, u.nama
    ");
    $data_bidang = $stmtBidang->fetchAll();

    // 3. Ambil Data Pengajuan Terbaru
    $stmtLatest = $pdo->query("
        SELECT p.id, u.nama, u.bidang, p.tanggal_pengajuan, p.status 
        FROM permintaan_barang p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.tanggal_pengajuan DESC
        LIMIT 5
    ");
    $latest_pengajuans = $stmtLatest->fetchAll();

} catch (Exception $e) {
    die("Error memuat data dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Statistik SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">SiBARA - Dashboard Statistik Pengadaan</a>
            <a href="auth/login.php" class="btn btn-light btn-sm">Login Sistem</a>
        </div>
    </nav>

    <div class="container pb-5">
        <!-- Kartu Ringkasan Statistik -->
        <div class="row text-white mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-secondary p-3 shadow-sm">
                    <h5>Total Pengajuan</h5>
                    <h3><?= $stats['total_pengajuan'] ?? 0 ?></h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-dark p-3 shadow-sm">
                    <h5>Menunggu (Pending)</h5>
                    <h3><?= $stats['total_pending'] ?? 0 ?></h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white p-3 shadow-sm">
                    <h5>Proses Vendor</h5>
                    <h3><?= $stats['total_vendor'] ?? 0 ?></h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success p-3 shadow-sm">
                    <h5>Selesai (Completed)</h5>
                    <h3><?= $stats['total_completed'] ?? 0 ?></h3>
                </div>
            </div>
        </div>

        <!-- Tabel Statistik Per Bidang -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Statistik Pengajuan Berdasarkan Bidang</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama Bidang / Unit</th>
                            <th>Penanggung Jawab</th>
                            <th>Total Pengajuan</th>
                            <th>Status Pending</th>
                            <th>Status Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($data_bidang) > 0): ?>
                            <?php foreach ($data_bidang as $b): ?>
                                <tr>
                                    <td><?= htmlspecialchars($b['bidang'] ?? 'Umum') ?></td>
                                    <td><?= htmlspecialchars($b['nama_pemohon']) ?></td>
                                    <td><span class="badge bg-primary"><?= $b['jumlah_pengajuan'] ?></span></td>
                                    <td><span class="badge bg-warning text-dark"><?= $b['pending'] ?></span></td>
                                    <td><span class="badge bg-success"><?= $b['selesai'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data bidang pemohon.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Aktivitas Pengajuan Terbaru -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">5 Aktivitas Pengajuan Terbaru</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bidang / Pemohon</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status Terkini</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($latest_pengajuans) > 0): ?>
                            <?php foreach ($latest_pengajuans as $lp): ?>
                                <tr>
                                    <td>#<?= $lp['id'] ?></td>
                                    <td><?= htmlspecialchars($lp['nama']) ?> (<?= htmlspecialchars($lp['bidang']) ?>)</td>
                                    <td><?= $lp['tanggal_pengajuan'] ?></td>
                                    <td><span class="badge bg-info text-dark"><?= $lp['status'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Belum ada aktivitas pengajuan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
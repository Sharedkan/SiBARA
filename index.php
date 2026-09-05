<?php
require_once 'config/database.php';

// Jika user sudah login, arahkan ke dashboard sesuai role mereka masing-masing
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    switch ($role) {
        case 'super_admin':
            header("Location: super_admin/dashboard.php");
            break;
        case 'pemohon':
            header("Location: pemohon/dashboard.php");
            break;
        case 'biro_umum':
            header("Location: biro_umum/dashboard.php");
            break;
        case 'pengadaan':
            header("Location: pengadaan/dashboard.php");
            break;
        case 'vendor':
            header("Location: vendor/dashboard.php");
            break;
        case 'perlengkapan':
            header("Location: perlengkapan/dashboard.php");
            break;
        default:
            session_destroy();
            header("Location: auth/login.php?error=Role tidak valid.");
    }
    exit;
}

// Jika BELUM login, tampilkan halaman statistik publik secara langsung di root (index.php)
try {
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
} catch (Exception $e) {
    $stats = ['total_pengajuan' => 0, 'total_pending' => 0, 'total_vendor' => 0, 'total_completed' => 0];
    $data_bidang = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SiBARA - Dashboard Statistik Pengadaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SiBARA - Sistem Pengajuan & Aset</a>
            <a href="auth/login.php" class="btn btn-light btn-sm px-3">Login Sistem</a>
        </div>
    </nav>

    <div class="container pb-5">
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
    </div>
</body>
</html>
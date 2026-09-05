<?php
require_once '../config/database.php';

// Validasi akses role vendor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: dashboard.php");
    exit;
}

// Ambil data pengajuan dan pemohon
$stmt = $pdo->prepare("
    SELECT p.*, u.nama, u.bidang 
    FROM permintaan_barang p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$req = $stmt->fetch();

if (!$req) {
    header("Location: dashboard.php");
    exit;
}

// Ambil detail item barang
$stmtDetail = $pdo->prepare("SELECT * FROM detail_permintaan WHERE permintaan_id = ?");
$stmtDetail->execute([$id]);
$items = $stmtDetail->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan #<?= $req['id'] ?> - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light container py-5">
    <div class="card shadow-sm p-4">
        <h3 class="mb-3">Detail Pesanan Pengadaan #<?= $req['id'] ?></h3>
        <hr>
        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Pemohon:</strong> <?= htmlspecialchars($req['nama']) ?></p>
                <p><strong>Bidang:</strong> <?= htmlspecialchars($req['bidang'] ?? '-') ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Tanggal Pengajuan:</strong> <?= $req['tanggal_pengajuan'] ?></p>
                <p><strong>Sumber Dana:</strong> <?= htmlspecialchars($req['sumber_dana'] ?? '-') ?></p>
            </div>
        </div>

        <h5 class="mt-4 mb-3">Daftar Barang yang Dipesan</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Spesifikasi</th>
                        <th>Jumlah Dipesan</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                            <td><?= htmlspecialchars($item['spesifikasi'] ?: '-') ?></td>
                            <td><?= $item['jumlah_vendor'] > 0 ? $item['jumlah_vendor'] : $item['jumlah_approved'] ?></td>
                            <td><?= htmlspecialchars($item['satuan']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>
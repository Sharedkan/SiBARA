<?php
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemohon') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM permintaan_barang WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$req = $stmt->fetch();

if (!$req) {
    header("Location: dashboard.php");
    exit;
}

$stmtDetail = $pdo->prepare("SELECT * FROM detail_permintaan WHERE permintaan_id = ?");
$stmtDetail->execute([$id]);
$items = $stmtDetail->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pengajuan - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light container py-5">
    <h2>Detail Pengajuan #<?= $req['id'] ?></h2>
    <p><strong>Status:</strong> <span class="badge bg-secondary"><?= $req['status'] ?></span></p>
    <?php if (!empty($req['catatan_biro_umum'])): ?>
        <div class="alert alert-warning"><strong>Catatan Biro Umum:</strong> <?= htmlspecialchars($req['catatan_biro_umum']) ?></div>
    <?php endif; ?>

    <table class="table table-bordered bg-white shadow-sm mt-3 align-middle">
        <thead class="table-dark">
            <tr>
                <th>Nama Barang</th>
                <th>Spesifikasi</th>
                <th>Jumlah Minta</th>
                <th>Jumlah ACC</th>
                <th>Satuan</th>
                <th>Sifat</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($item['spesifikasi'] ?: '-') ?></td>
                    <td><?= $item['jumlah_minta'] ?></td>
                    <td>
                        <?php if ($req['status'] === 'Pending'): ?>
                            <span class="text-muted fst-italic">Masih Pending</span>
                        <?php else: ?>
                            <span class="badge bg-success"><?= $item['jumlah_approved'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['satuan']) ?></td>
                    <td><?= htmlspecialchars($item['sifat_barang']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
</body>
</html>
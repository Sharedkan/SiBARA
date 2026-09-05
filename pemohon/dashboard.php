<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemohon') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'];
$bidang_user = $_SESSION['bidang'] ?? 'Bidang Umum';

try {
    $stmt = $pdo->prepare("SELECT * FROM permintaan_barang WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $pengajuans = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pemohon - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<?php if (isset($_SESSION['is_impersonating'])): ?>
    <div class="alert alert-warning text-center m-0 rounded-0 py-2" role="alert">
        Anda sedang melihat sebagai <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong> (Mode Impersonasi). 
        <a href="../auth/stop_impersonate.php" class="btn btn-sm btn-dark ms-3">Kembali ke Akun Super Admin</a>
    </div>
<?php endif; ?>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand fw-bold">SiBARA - Panel Pemohon (<?= htmlspecialchars($bidang_user) ?>)</span>
            <div>
                <span class="text-white me-3">Halo, <?= htmlspecialchars($nama_user) ?></span>
                <a href="../auth/logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Daftar Pengajuan Barang</h4>
            <a href="ajukan_barang.php" class="btn btn-success">+ Buat Pengajuan Baru</a>
        </div>

        

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Pengajuan barang berhasil diproses.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status Pengajuan</th>
                            <th>Sumber Dana</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pengajuans) > 0): ?>
                            <?php foreach ($pengajuans as $p): ?>
                                <tr>
                                    <td>#<?= $p['id'] ?></td>
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
                                    <td><?= htmlspecialchars($p['sumber_dana'] ?? '-') ?></td>
                                    <td>
                                        <a href="detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-info mb-1">Detail</a>
                                        
                                        <?php if ($p['status'] === 'Pending'): ?>
                                            <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning mb-1">Edit</a>
                                            <a href="hapus.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger mb-1" onclick="return confirm('Yakin ingin menghapus pengajuan ini?')">Hapus</a>
                                        <?php endif; ?>

                                        <?php if ($p['status'] === 'Rejected'): ?>
                                            <a href="ajukan_ulang.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-success mb-1" onclick="return confirm('Ajukan ulang permintaan ini?')">Ajukan Ulang</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">Belum ada pengajuan barang yang dibuat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
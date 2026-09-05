<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadaan') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT p.*, u.nama, u.bidang FROM permintaan_barang p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$req = $stmt->fetch();

if (!$req) {
    header("Location: dashboard.php");
    exit;
}

$stmtDetail = $pdo->prepare("SELECT * FROM detail_permintaan WHERE permintaan_id = ?");
$stmtDetail->execute([$id]);
$items = $stmtDetail->fetchAll();

$stmtVendor = $pdo->query("SELECT id, nama FROM users WHERE role = 'vendor'");
$vendors = $stmtVendor->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendor_id = $_POST['vendor_id'] ?? null;

    try {
        $pdo->beginTransaction();

        // 1. Sinkronkan jumlah vendor sesuai jumlah approved
        $stmtUpdDetail = $pdo->prepare("UPDATE detail_permintaan SET jumlah_vendor = jumlah_approved WHERE permintaan_id = ?");
        $stmtUpdDetail->execute([$id]);

        // 2. Simpan vendor_id yang dipilih dan ubah status menjadi Diproses_Vendor
        $stmtReq = $pdo->prepare("UPDATE permintaan_barang SET status = 'Diproses_Vendor', vendor_id = ? WHERE id = ?");
        $stmtReq->execute([$vendor_id, $id]);

        $pdo->commit();
        
        header("Location: dashboard.php?status=success");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Proses Pengadaan - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light container py-5">
    <h2>Proses Pengadaan #<?= $req['id'] ?></h2>
    <p><strong>Pemohon:</strong> <?= htmlspecialchars($req['nama']) ?> (<?= htmlspecialchars($req['bidang'] ?? '-') ?>)</p>
    <p><strong>Sumber Dana:</strong> <?= htmlspecialchars($req['sumber_dana'] ?? '-') ?></p>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Pilih Mitra Vendor</label>
            <select name="vendor_id" class="form-select" required>
                <option value="">-- Pilih Vendor --</option>
                <?php foreach ($vendors as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= ($req['vendor_id'] ?? '') == $v['id'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <table class="table table-bordered align-middle mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Nama Barang</th>
                    <th>Spesifikasi</th>
                    <th>Jumlah ACC Biro Umum</th>
                    <th>Jumlah Proses Vendor (Terkunci)</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                        <td><?= htmlspecialchars($item['spesifikasi'] ?: '-') ?></td>
                        <td><?= $item['jumlah_approved'] ?></td>
                        <td>
                            <input type="number" class="form-control" value="<?= $item['jumlah_approved'] ?>" readonly disabled>
                        </td>
                        <td><?= htmlspecialchars($item['satuan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Kirim ke Vendor & Proses</button>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </form>
</body>
</html>
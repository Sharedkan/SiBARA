<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'biro_umum') {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi']; // approve atau reject
    $catatan = trim($_POST['catatan_biro_umum'] ?? '');
    $sumber_dana = trim($_POST['sumber_dana'] ?? '');
    $approved_items = $_POST['approved_items'] ?? [];

    try {
        $pdo->beginTransaction();

        if ($aksi === 'approve') {
            $status_baru = 'Approved_Biro_Umum';
            
            // Update jumlah approved per item
            $stmtUpdDetail = $pdo->prepare("UPDATE detail_permintaan SET jumlah_approved = ? WHERE id = ?");
            foreach ($approved_items as $detail_id => $jml_acc) {
                $stmtUpdDetail->execute([$jml_acc, $detail_id]);
            }

            $stmtReq = $pdo->prepare("UPDATE permintaan_barang SET status = ?, sumber_dana = ?, catatan_biro_umum = ? WHERE id = ?");
            $stmtReq->execute([$status_baru, $sumber_dana, $catatan, $id]);

        } else {
            $status_baru = 'Rejected';
            $stmtReq = $pdo->prepare("UPDATE permintaan_barang SET status = ?, catatan_biro_umum = ? WHERE id = ?");
            $stmtReq->execute([$status_baru, $catatan, $id]);
        }

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
    <title>Review Pengajuan - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light container py-5">
    <h2>Review Pengajuan #<?= $req['id'] ?></h2>
    <p><strong>Pemohon:</strong> <?= htmlspecialchars($req['nama']) ?> (<?= htmlspecialchars($req['bidang'] ?? '-') ?>)</p>
    <p><strong>Tanggal:</strong> <?= $req['tanggal_pengajuan'] ?></p>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Sumber Dana</label>
            <input type="text" name="sumber_dana" class="form-control" value="<?= htmlspecialchars($req['sumber_dana'] ?? '') ?>" placeholder="Cth: Anggaran Rutin / APBD">
        </div>

        <table class="table table-bordered align-middle mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Nama Barang</th>
                    <th>Spesifikasi</th>
                    <th>Jumlah Minta</th>
                    <th>Jumlah ACC Biro Umum</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                        <td><?= htmlspecialchars($item['spesifikasi'] ?: '-') ?></td>
                        <td><?= $item['jumlah_minta'] ?></td>
                        <td>
                            <input type="number" name="approved_items[<?= $item['id'] ?>]" class="form-control" value="<?= $item['jumlah_approved'] ?: $item['jumlah_minta'] ?>" min="0" required>
                        </td>
                        <td><?= htmlspecialchars($item['satuan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mb-3">
            <label class="form-label">Catatan / Alasan (Wajib diisi jika ditolak)</label>
            <textarea name="catatan_biro_umum" class="form-control" rows="2"><?= htmlspecialchars($req['catatan_biro_umum'] ?? '') ?></textarea>
        </div>

        <button type="submit" name="aksi" value="approve" class="btn btn-success">Setujui (Approve)</button>
        <button type="submit" name="aksi" value="reject" class="btn btn-danger" onclick="return confirm('Yakin ingin menolak pengajuan ini?')">Tolak (Reject)</button>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </form>
</body>
</html>
<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemohon') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

// Pastikan data milik user yang login dan statusnya masih Pending
$stmt = $pdo->prepare("SELECT * FROM permintaan_barang WHERE id = ? AND user_id = ? AND status = 'Pending'");
$stmt->execute([$id, $user_id]);
$req = $stmt->fetch();

if (!$req) {
    header("Location: dashboard.php?error=Data tidak ditemukan atau tidak dapat diedit.");
    exit;
}

// Ambil detail barang terkait
$stmtDetail = $pdo->prepare("SELECT * FROM detail_permintaan WHERE permintaan_id = ?");
$stmtDetail->execute([$id]);
$items = $stmtDetail->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_items = $_POST['items'] ?? [];

    try {
        $pdo->beginTransaction();

        // Hapus detail lama lalu masukkan yang diperbarui
        $stmtDel = $pdo->prepare("DELETE FROM detail_permintaan WHERE permintaan_id = ?");
        $stmtDel->execute([$id]);

        $stmtDetailIns = $pdo->prepare("INSERT INTO detail_permintaan (permintaan_id, nama_barang, spesifikasi, jumlah_minta, jumlah_approved, jumlah_vendor, satuan, sifat_barang, keterangan) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)");

        foreach ($new_items as $item) {
            $stmtDetailIns->execute([
                $id,
                $item['nama_barang'],
                $item['spesifikasi'],
                $item['jumlah_minta'],
                $item['jumlah_minta'], 
                $item['satuan'],
                $item['sifat_barang'],
                $item['keterangan']
            ]);
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
    <title>Edit Pengajuan - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light container py-5">
    <h2>Edit Pengajuan Barang #<?= $req['id'] ?></h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 rounded shadow-sm">
        <div id="item-container">
            <?php foreach ($items as $index => $item): ?>
                <div class="row item-row mb-3 border-bottom pb-3">
                    <div class="col-md-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="items[<?= $index ?>][nama_barang]" class="form-control" value="<?= htmlspecialchars($item['nama_barang']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Spesifikasi</label>
                        <input type="text" name="items[<?= $index ?>][spesifikasi]" class="form-control" value="<?= htmlspecialchars($item['spesifikasi']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jumlah Minta</label>
                        <input type="number" name="items[<?= $index ?>][jumlah_minta]" class="form-control" value="<?= $item['jumlah_minta'] ?>" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="items[<?= $index ?>][satuan]" class="form-control" value="<?= htmlspecialchars($item['satuan']) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sifat Barang</label>
                        <select name="items[<?= $index ?>][sifat_barang]" class="form-select" required>
                            <option value="Habis_Pakai" <?= $item['sifat_barang'] === 'Habis_Pakai' ? 'selected' : '' ?>>Habis Pakai</option>
                            <option value="Tidak_Habis_Pakai" <?= $item['sifat_barang'] === 'Tidak_Habis_Pakai' ? 'selected' : '' ?>>Tidak Habis Pakai</option>
                        </select>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Keterangan</label>
                        <textarea name="items[<?= $index ?>][keterangan]" class="form-control" rows="1"><?= htmlspecialchars($item['keterangan']) ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</body>
</html>
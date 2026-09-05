<?php
require_once '../config/database.php';

// Validasi akses role pemohon
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemohon') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $items = $_POST['items'] ?? [];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO permintaan_barang (user_id, status) VALUES (?, 'Pending')");
        $stmt->execute([$user_id]);
        $permintaan_id = $pdo->lastInsertId();

        $stmtDetail = $pdo->prepare("INSERT INTO detail_permintaan (permintaan_id, nama_barang, spesifikasi, jumlah_minta, jumlah_approved, jumlah_vendor, satuan, sifat_barang, keterangan) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)");

        foreach ($items as $item) {
            $stmtDetail->execute([
                $permintaan_id,
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
        
        // Redirect otomatis ke dashboard pemohon setelah sukses
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
    <title>Form Pengajuan Barang - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light container py-5">
    <h2>Form Pengajuan Permintaan Barang</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 rounded shadow-sm">
        <div id="item-container">
            <div class="row item-row mb-3 border-bottom pb-3">
                <div class="col-md-3">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="items[0][nama_barang]" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Spesifikasi</label>
                    <input type="text" name="items[0][spesifikasi]" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jumlah Minta</label>
                    <input type="number" name="items[0][jumlah_minta]" class="form-control" min="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Satuan</label>
                    <input type="text" name="items[0][satuan]" class="form-control" placeholder="Pcs/Unit/Rim" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sifat Barang</label>
                    <select name="items[0][sifat_barang]" class="form-select" required>
                        <option value="Habis_Pakai">Habis Pakai</option>
                        <option value="Tidak_Habis_Pakai">Tidak Habis Pakai</option>
                    </select>
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">Keterangan</label>
                    <textarea name="items[0][keterangan]" class="form-control" rows="1"></textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
    </form>
</body>
</html>
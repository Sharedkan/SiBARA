<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadaan') {
    header("Location: ../auth/login.php");
    exit;
}

$permintaan_id = $_GET['id'] ?? null;
if (!$permintaan_id) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendor_id = $_POST['vendor_id'];

    $stmt = $pdo->prepare("UPDATE permintaan_barang SET vendor_id = ?, status = 'Sent_to_Vendor' WHERE id = ?");
    $stmt.execute([$vendor_id, $permintaan_id]);
    
    header("Location: dashboard.php?status=sent");
    exit;
}

$stmtVendors = $pdo->query("SELECT * FROM vendor WHERE jenis_vendor IN ('pengadaan_barang', 'keduanya')");
$vendors = $stmtVendors->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Vendor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <h2>Pilih Vendor untuk Pengadaan</h2>
    <form method="POST" class="mt-4 bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Pilih Vendor Mitra</label>
            <select name="vendor_id" class="form-select" required>
                <option value="">-- Pilih Vendor --</option>
                <?php foreach ($vendors as $v): ?>
                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nama_vendor']) ?> (<?= htmlspecialchars($v['kontak']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Teruskan ke Vendor</button>
    </form>
</body>
</html>
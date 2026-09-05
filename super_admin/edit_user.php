<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role  = $_POST['role'];
    $bidang = trim($_POST['bidang']) ?: NULL;

    if (!empty($_POST['password'])) {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmtUpdate = $pdo->prepare("UPDATE users SET nama = ?, email = ?, password = ?, role = ?, bidang = ? WHERE id = ?");
        $stmtUpdate->execute([$nama, $email, $pass, $role, $bidang, $id]);
    } else {
        $stmtUpdate = $pdo->prepare("UPDATE users SET nama = ?, email = ?, role = ?, bidang = ? WHERE id = ?");
        $stmtUpdate->execute([$nama, $email, $role, $bidang, $id]);
    }

    header("Location: dashboard.php?status=success");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pengguna - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light container py-5">
    <div class="card shadow-sm p-4" style="max-width: 600px; margin: auto;">
        <h3>Edit Pengguna: #<?= $user['id'] ?></h3>
        <form method="POST" class="mt-3">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak ingin mengubah password)</small></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Role Akses</label>
                <select name="role" class="form-select" required>
                    <option value="pemohon" <?= $user['role'] === 'pemohon' ? 'selected' : '' ?>>Pemohon</option>
                    <option value="biro_umum" <?= $user['role'] === 'biro_umum' ? 'selected' : '' ?>>Biro Umum</option>
                    <option value="pengadaan" <?= $user['role'] === 'pengadaan' ? 'selected' : '' ?>>Pengadaan</option>
                    <option value="vendor" <?= $user['role'] === 'vendor' ? 'selected' : '' ?>>Vendor</option>
                    <option value="perlengkapan" <?= $user['role'] === 'perlengkapan' ? 'selected' : '' ?>>Perlengkapan</option>
                    <option value="super_admin" <?= $user['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Bidang</label>
                <input type="text" name="bidang" class="form-control" value="<?= htmlspecialchars($user['bidang'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>
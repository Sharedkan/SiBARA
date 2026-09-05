<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses Tambah User Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role  = $_POST['role'];
    $bidang = $_POST['bidang'] ?? NULL;

    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, bidang) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $pass, $role, $bidang]);
        $success = "Pengguna baru berhasil ditambahkan.";
    } catch (Exception $e) {
        $error = "Gagal menambah user: " . $e->getMessage();
    }
}

// Ambil Statistik
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalReq = $pdo->query("SELECT COUNT(*) FROM permintaan_barang")->fetchColumn();
$totalInv = $pdo->query("SELECT COUNT(*) FROM inventaris")->fetchColumn();

// Ambil Data Users
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

// Ambil Seluruh Pengajuan
$pengajuans = $pdo->query("SELECT p.*, u.nama, u.bidang FROM permintaan_barang p JOIN users u ON p.user_id = u.id ORDER BY p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Super Admin - SiBARA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand fw-bold">SiBARA - Panel Super Administrator</span>
            <div>
                <span class="text-white me-3">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Kartu Statistik -->
        <div class="row text-white mb-4">
            <div class="col-md-4 mb-3">
                <div class="card bg-primary p-3 shadow-sm">
                    <h5>Total Pengguna</h5>
                    <h3><?= $totalUsers ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-success p-3 shadow-sm">
                    <h5>Total Pengajuan Barang</h5>
                    <h3><?= $totalReq ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-info p-3 shadow-sm">
                    <h5>Total Aset Inventaris</h5>
                    <h3><?= $totalInv ?></h3>
                </div>
            </div>
        </div>

        <!-- Form Tambah Pengguna -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Tambah Pengguna Sistem Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="tambah_user" value="1">
                    <div class="col-md-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Role Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="pemohon">Pemohon</option>
                            <option value="biro_umum">Biro Umum</option>
                            <option value="pengadaan">Pengadaan</option>
                            <option value="vendor">Vendor</option>
                            <option value="perlengkapan">Perlengkapan</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bidang (Opsional)</label>
                        <input type="text" name="bidang" class="form-control" placeholder="Cth: Akademik">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-dark">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Pengguna Sistem -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Manajemen Pengguna Sistem</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Bidang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>#<?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['nama']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><span class="badge bg-secondary"><?= $u['role'] ?></span></td>
                                <td><?= htmlspecialchars($u['bidang'] ?? '-') ?></td>
                                <td>
    <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning mb-1">Edit</a>
    
    <?php if ($u['id'] != $_SESSION['user_id']): ?>
        <a href="hapus_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger mb-1" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">Hapus</a>
    <?php endif; ?>

    <a href="../auth/login_as.php?id=<?= $u['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info mb-1" onclick="return confirm('Buka tab baru sebagai <?= htmlspecialchars($u['nama']) ?>?')">Login As</a>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Monitoring Seluruh Pengajuan -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Monitoring Seluruh Pengajuan Barang</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Pemohon / Bidang</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Sumber Dana</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pengajuans) > 0): ?>
                            <?php foreach ($pengajuans as $p): ?>
                                <?php 
                                    $raw_status = trim($p['status'] ?? '');
                                    
                                    // Logika penentuan label dan warna status
                                    if ($raw_status === 'Diproses_Vendor' || $raw_status === 'Confirmed_by_Vendor') {
                                        $label_status = 'On Proses';
                                        $badge_color = 'primary';
                                    } elseif ($raw_status === 'Approved_Biro_Umum') {
                                        $label_status = 'Approved Biro Umum';
                                        $badge_color = 'info text-dark';
                                    } elseif ($raw_status === 'Completed') {
                                        $label_status = 'Selesai';
                                        $badge_color = 'success';
                                    } elseif ($raw_status === 'Rejected') {
                                        $label_status = 'Ditolak';
                                        $badge_color = 'danger';
                                    } elseif ($raw_status === 'Pending') {
                                        $label_status = 'Pending';
                                        $badge_color = 'warning text-dark';
                                    } else {
                                        $label_status = $raw_status ?: 'On Proses';
                                        $badge_color = 'secondary';
                                    }
                                ?>
                                <tr>
                                    <td>#<?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['nama']) ?> (<?= htmlspecialchars($p['bidang'] ?? 'Umum') ?>)</td>
                                    <td><?= $p['tanggal_pengajuan'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $badge_color ?>">
                                            <?= $label_status ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($p['sumber_dana'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data pengajuan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
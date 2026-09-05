<?php
require_once '../config/database.php';

$password_plain = 'password123';
$hashed_password = password_hash($password_plain, PASSWORD_DEFAULT);

$users = [
    ['Pemohon Bidang', 'pemohon@app.com', $hashed_password, 'pemohon', 'Bidang Akademik'],
    ['Staff Biro Umum', 'biro@app.com', $hashed_password, 'biro_umum', NULL],
    ['Staff Pengadaan', 'pengadaan@app.com', $hashed_password, 'pengadaan', NULL],
    ['Mitra Vendor', 'vendor@app.com', $hashed_password, 'vendor', NULL],
    ['Staff Biro Perlengkapan', 'perlengkapan@app.com', $hashed_password, 'perlengkapan', NULL],
];

try {
    $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, bidang) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE password=VALUES(password)");

    foreach ($users as $user) {
        $stmt->execute($user);
    }

    echo "<h3>Akun uji coba berhasil dibuat!</h3>";
    echo "<p>Password untuk semua akun di atas adalah: <b>password123</b></p>";
    echo "<ul>";
    echo "<li>Pemohon: pemohon@app.com</li>";
    echo "<li>Biro Umum: biro@app.com</li>";
    echo "<li>Pengadaan: pengadaan@app.com</li>";
    echo "<li>Vendor: vendor@app.com</li>";
    echo "<li>Perlengkapan: perlengkapan@app.com</li>";
    echo "</ul>";
    echo "<a href='login.php'>Menuju Halaman Login</a>";
} catch (Exception $e) {
    echo "Gagal membuat akun: " . $e->getMessage();
}
?>
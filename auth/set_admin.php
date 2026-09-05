<?php
require_once '../config/database.php';

$nama = 'Super Administrator';
$email = 'admin@app.com';
$password_plain = 'admin';
$hashed_password = password_hash($password_plain, PASSWORD_DEFAULT);
$role = 'super_admin';

// Cek apakah email admin sudah terdaftar
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Jika sudah ada, update password dan role-nya
    $update = $pdo->prepare("UPDATE users SET nama = ?, password = ?, role = ? WHERE email = ?");
    $update->execute([$nama, $hashed_password, $role, $email]);
    echo "<h3>Akun Super Admin berhasil diperbarui!</h3>";
} else {
    // Jika belum ada, masukkan sebagai data baru
    $insert = $pdo->prepare("INSERT INTO users (nama, email, password, role, bidang) VALUES (?, ?, ?, ?, NULL)");
    $insert->execute([$nama, $email, $hashed_password, $role]);
    echo "<h3>Akun Super Admin baru berhasil dibuat!</h3>";
}

echo "<p>Email: <b>admin@app.com</b></p>";
echo "<p>Password: <b>admin</b></p>";
echo "<a href='login.php'>Menuju Halaman Login</a>";
?>
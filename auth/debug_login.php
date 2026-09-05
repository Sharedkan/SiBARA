<?php
require_once '../config/database.php';

echo "<h3>Diagnostik Sistem Login</h3>";

// 1. Cek Koneksi Database
try {
    $test = $pdo->query("SELECT 1");
    echo "<p style='color:green;'>[OK] Koneksi database berhasil.</p>";
} catch (Exception $e) {
    die("<p style='color:red;'>[GAGAL] Koneksi database error: " . $e->getMessage() . "</p>");
}

// 2. Cek apakah tabel users ada dan ada isinya
try {
    $stmt = $pdo->query("SELECT id, nama, email, role FROM users");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<p style='color:green;'>[OK] Tabel 'users' ditemukan dan berisi " . count($users) . " pengguna:</p>";
        echo "<ul>";
        foreach ($users as $u) {
            echo "<li><b>{$u['email']}</b> (Role: {$u['role']}, Nama: {$u['nama']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>[PERINGATAN] Tabel 'users' kosong! Jalankan kembali skrip `seed_users.php`.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>[GAGAL] Tabel 'users' tidak ditemukan atau error: " . $e->getMessage() . "</p>";
}

// 3. Test Simulasi Verifikasi Password
$email_test = 'pemohon@app.com';
$password_test = 'password123';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email_test]);
$user_test = $stmt->fetch();

if ($user_test) {
    echo "<p style='color:green;'>[OK] Akun tes <b>{$email_test}</b> ditemukan di database.</p>";
    if (password_verify($password_test, $user_test['password'])) {
        echo "<p style='color:green;'>[OK] Fungsi `password_verify()` BERHASIL. Password cocok.</p>";
    } else {
        echo "<p style='color:red;'>[GAGAL] Fungsi `password_verify()` GAGAL. Hash password di database tidak cocok dengan 'password123'. Silakan jalankan ulang `seed_users.php`.</p>";
    }
} else {
    echo "<p style='color:red;'>[GAGAL] Akun tes {$email_test} tidak ada di database.</p>";
}
?>
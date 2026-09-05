<?php
require_once '../config/database.php';

// Tentukan base URL aplikasi secara otomatis
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . "://" . $host . rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            header("Location: login.php?error=Email tidak terdaftar");
            exit;
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['bidang']  = $user['bidang'];

            // Redirect aman menggunakan absolute URL root
            header("Location: " . $baseUrl . "/index.php");
            exit;
        } else {
            header("Location: login.php?error=Password salah");
            exit;
        }
    } catch (Exception $e) {
        die("Database Error: " . $e->getMessage());
    }
} else {
    header("Location: login.php");
    exit;
}
?>
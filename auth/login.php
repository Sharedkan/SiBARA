<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Pengadaan & Inventaris</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
        <h2 class="text-center mb-4">Login SiBARA</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form action="process_login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
        </form>
    </div>
</body>
</html>
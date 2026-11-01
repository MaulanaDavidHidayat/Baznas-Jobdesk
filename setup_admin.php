<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah ada akun, jangan izinkan pembuatan admin melalui file ini.
$totalAdmin = (int) $pdo
    ->query('SELECT COUNT(*) FROM users')
    ->fetchColumn();

if ($totalAdmin > 0) {
    http_response_code(403);
    exit(
        'Akun admin sudah tersedia. '
        . 'Hapus atau nonaktifkan file setup_admin.php.'
    );
}

$error = '';
$nama = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if ($nama === '' || $username === '') {
        $error = 'Nama dan username wajib diisi.';
    } elseif (strlen($username) > 50) {
        $error = 'Username maksimal 50 karakter.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        try {
            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                'INSERT INTO users (nama, username, password, role)
                 VALUES (:nama, :username, :password, :role)'
            );

            $stmt->execute([
                'nama' => $nama,
                'username' => $username,
                'password' => $hash,
                'role' => 'admin',
            ]);

            redirect('login.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'Username sudah digunakan.';
            } else {
                throw $e;
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#005030">
    <title>Buat Admin · BAZNAS</title>
    <link
        rel="icon"
        type="image/png"
        href="assets/favicon.png"
    >
    <link rel="apple-touch-icon" href="assets/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>

<body>
<div class="login-page">
    <section class="login-visual">
        <div class="login-logo">
            <img
                class="brand-logo"
                src="assets/favicon.png"
                alt="Logo BAZNAS"
            >
            <div>
                <strong style="color:white">BAZNAS</strong>
                <small style="color:rgba(255,255,255,.75)">
                    Pengaturan Administrator
                </small>
            </div>
        </div>

        <div>
            <h1>Buat akun administrator.</h1>
            <p>
                Buat akun pertama untuk mulai mengelola sistem.
            </p>
        </div>

        <div class="login-visual-footer">
            Pembuatan akun awal
        </div>
    </section>

    <section class="login-panel">
        <div class="login-form">
            <h2>Admin pertama</h2>
            <p>Lengkapi data akun administrator.</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="setup_admin.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <div class="form-group">
                    <label for="nama">Nama lengkap</label>
                    <input
                        class="form-control"
                        id="nama"
                        name="nama"
                        maxlength="100"
                        value="<?= e($nama) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        class="form-control"
                        id="username"
                        name="username"
                        maxlength="50"
                        value="<?= e($username) ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        class="form-control"
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                    <small class="form-hint">
                        Minimal 8 karakter.
                    </small>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_password">
                        Konfirmasi password
                    </label>
                    <input
                        class="form-control"
                        type="password"
                        id="konfirmasi_password"
                        name="konfirmasi_password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button class="btn btn-primary btn-block" type="submit">
                    Buat Akun Admin
                </button>
            </form>
        </div>
    </section>
</div>
</body>
</html>

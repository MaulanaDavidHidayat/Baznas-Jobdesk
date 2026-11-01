<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, langsung ke dashboard.
if (!empty($_SESSION['user']['id_user'])) {
    redirect('index.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id_user, nama, username, password, role
             FROM users
             WHERE username = :username
             LIMIT 1'
        );

        $stmt->execute([
            'username' => $username,
        ]);

        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Username tidak ditemukan.';
        } else {
            $passwordCocok = password_verify(
                $password,
                $user['password']
            );

            // Dukungan akun lama yang masih memakai hash MD5.
            if (
                !$passwordCocok &&
                preg_match('/^[a-f0-9]{32}$/i', $user['password'])
            ) {
                $passwordCocok = hash_equals(
                    strtolower($user['password']),
                    md5($password)
                );

                if ($passwordCocok) {
                    $upgrade = $pdo->prepare(
                        'UPDATE users
                         SET password = :password
                         WHERE id_user = :id'
                    );

                    $upgrade->execute([
                        'password' => password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        ),
                        'id' => (int) $user['id_user'],
                    ]);
                }
            }

            if ($passwordCocok) {
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id_user' => (int) $user['id_user'],
                    'nama' => $user['nama'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                ];

                // Token baru setelah login.
                $_SESSION['csrf_token'] = bin2hex(
                    random_bytes(32)
                );

                redirect('index.php');
            }

            $error = 'Password salah.';
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

    <title>Login Admin · BAZNAS</title>

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
                    Badan Amil Zakat Nasional
                </small>
            </div>
        </div>

        <div>
            <h1>Berbagi Kebaikan, Membangun Umat</h1>

            <p>
                Sistem informasi untuk membantu pencatatan data guru,
                infak, pengeluaran, dan laporan secara teratur.
            </p>
        </div>

        <div class="login-visual-footer">
            Sistem Informasi Infak BAZNAS
        </div>
    </section>

    <section class="login-panel">
        <div class="login-form">

            <div class="login-form-brand"><img src="assets/favicon.png" alt="Logo BAZNAS"><strong>BAZNAS</strong><small>Badan Amil Zakat Nasional</small></div>
            <h2>Login Admin</h2>

            <p>
                Silakan masuk untuk melanjutkan ke sistem.
            </p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <div class="form-group">
                    <label for="username">Username</label>

                    <input
                        class="form-control"
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        value="<?= e($username) ?>"
                        autocomplete="username"
                        maxlength="50"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="password-wrapper">
                        <input
                            class="form-control"
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            class="password-toggle"
                            type="button"
                            data-password-toggle="password"
                        >
                            Tampilkan
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary btn-block"
                >
                    Login
                </button>
            </form>

            <p class="text-muted text-center mt-4">
                © 2026 BAZNAS. Semua hak dilindungi.
            </p>

        </div>
    </section>

</div>
</body>
</html>

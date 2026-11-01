<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode permintaan tidak diizinkan.');
}

verify_csrf();

$id = (int) ($_POST['id_user'] ?? 0);
$nama = trim((string) ($_POST['nama'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$role = (string) ($_POST['role'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$konfirmasi = (string) ($_POST['konfirmasi_password'] ?? '');

$cek = $pdo->prepare(
    'SELECT id_user FROM users WHERE id_user = :id LIMIT 1'
);
$cek->execute(['id' => $id]);

if (!$cek->fetch()) {
    flash('danger', 'Data admin tidak ditemukan.');
    redirect('index.php');
}

$errors = [];

if ($nama === '') {
    $errors[] = 'Nama wajib diisi.';
} elseif (strlen($nama) > 100) {
    $errors[] = 'Nama maksimal 100 karakter.';
}

if ($username === '') {
    $errors[] = 'Username wajib diisi.';
} elseif (strlen($username) > 50) {
    $errors[] = 'Username maksimal 50 karakter.';
}

if (!in_array($role, available_roles(), true)) {
    $errors[] = 'Role tidak valid.';
}

if ($password !== '') {
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    } elseif ($password !== $konfirmasi) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }
}

if ($errors !== []) {
    flash('danger', implode(' ', $errors));
    redirect('edit.php?id=' . $id);
}

try {
    if ($password !== '') {
        $stmt = $pdo->prepare(
            'UPDATE users SET
                nama = :nama,
                username = :username,
                password = :password,
                role = :role
             WHERE id_user = :id'
        );

        $stmt->execute([
            'nama' => $nama,
            'username' => $username,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            ),
            'role' => $role,
            'id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare(
            'UPDATE users SET
                nama = :nama,
                username = :username,
                role = :role
             WHERE id_user = :id'
        );

        $stmt->execute([
            'nama' => $nama,
            'username' => $username,
            'role' => $role,
            'id' => $id,
        ]);
    }

    if ((int) ($_SESSION['user']['id_user'] ?? 0) === $id) {
        $_SESSION['user']['nama'] = $nama;
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['role'] = $role;
    }

    flash('success', 'Data admin berhasil diperbarui.');
    redirect('index.php');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash('danger', 'Username sudah digunakan.');
        redirect('edit.php?id=' . $id);
    }

    throw $e;
}

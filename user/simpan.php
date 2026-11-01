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

$nama = trim((string) ($_POST['nama'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$role = (string) ($_POST['role'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$konfirmasi = (string) ($_POST['konfirmasi_password'] ?? '');

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

if (strlen($password) < 8) {
    $errors[] = 'Password minimal 8 karakter.';
} elseif ($password !== $konfirmasi) {
    $errors[] = 'Konfirmasi password tidak cocok.';
}

if ($errors !== []) {
    flash('danger', implode(' ', $errors));
    redirect('tambah.php');
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO users (nama, username, password, role)
         VALUES (:nama, :username, :password, :role)'
    );

    $stmt->execute([
        'nama' => $nama,
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role,
    ]);

    flash('success', 'Akun admin berhasil ditambahkan.');
    redirect('index.php');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash('danger', 'Username sudah digunakan.');
        redirect('tambah.php');
    }

    throw $e;
}

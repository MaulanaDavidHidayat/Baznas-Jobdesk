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
$currentId = (int) ($_SESSION['user']['id_user'] ?? 0);

if ($id <= 0) {
    flash('danger', 'Data admin tidak ditemukan.');
    redirect('index.php');
}

if ($id === $currentId) {
    flash('danger', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
    redirect('index.php');
}

$totalUser = (int) $pdo
    ->query('SELECT COUNT(*) FROM users')
    ->fetchColumn();

if ($totalUser <= 1) {
    flash('danger', 'Minimal harus ada satu akun admin.');
    redirect('index.php');
}

$stmt = $pdo->prepare(
    'DELETE FROM users WHERE id_user = :id'
);
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    flash('warning', 'Data admin tidak ditemukan.');
} else {
    flash('success', 'Akun admin berhasil dihapus.');
}

redirect('index.php');

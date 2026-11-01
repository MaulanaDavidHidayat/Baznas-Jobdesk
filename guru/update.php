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

$id = (int) ($_POST['id_guru'] ?? 0);
$namaGuru = trim((string) ($_POST['nama_guru'] ?? ''));

$cek = $pdo->prepare(
    'SELECT id_guru FROM guru WHERE id_guru = :id LIMIT 1'
);
$cek->execute(['id' => $id]);

if (!$cek->fetch()) {
    flash('danger', 'Data guru tidak ditemukan.');
    redirect('index.php');
}

$errors = [];

if ($namaGuru === '') {
    $errors[] = 'Nama guru wajib diisi.';
} elseif (strlen($namaGuru) > 100) {
    $errors[] = 'Nama guru maksimal 100 karakter.';
}

if ($errors !== []) {
    flash('danger', implode(' ', $errors));
    redirect('edit.php?id=' . $id);
}

try {
    $stmt = $pdo->prepare(
        'UPDATE guru SET nama_guru = :nama_guru WHERE id_guru = :id'
    );

    $stmt->execute([
        'nama_guru' => $namaGuru,
        'id' => $id,
    ]);

    flash('success', 'Data guru berhasil diperbarui.');
    redirect('index.php');
} catch (PDOException $e) {
    throw $e;
}

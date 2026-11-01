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

$namaGuru = trim((string) ($_POST['nama_guru'] ?? ''));

$errors = [];

if ($namaGuru === '') {
    $errors[] = 'Nama guru wajib diisi.';
} elseif (strlen($namaGuru) > 100) {
    $errors[] = 'Nama guru maksimal 100 karakter.';
}

if ($errors !== []) {
    flash('danger', implode(' ', $errors));
    redirect('tambah.php');
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO guru (nama_guru) VALUES (:nama_guru)'
    );

    $stmt->execute([
        'nama_guru' => $namaGuru,
    ]);

    flash('success', 'Data guru berhasil ditambahkan.');
    redirect('index.php');
} catch (PDOException $e) {
    throw $e;
}

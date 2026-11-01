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

$id = (int) ($_POST['id_pengeluaran'] ?? 0);
$tanggal = trim((string) ($_POST['tanggal'] ?? ''));
$keterangan = trim((string) ($_POST['keterangan'] ?? ''));
$jumlahRaw = trim((string) ($_POST['jumlah'] ?? ''));

$cek = $pdo->prepare(
    'SELECT id_pengeluaran
     FROM pengeluaran
     WHERE id_pengeluaran = :id
     LIMIT 1'
);
$cek->execute(['id' => $id]);

if (!$cek->fetch()) {
    flash('danger', 'Data pengeluaran tidak ditemukan.');
    redirect('index.php');
}

$errors = [];
$jumlah = 0.0;

$tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);

if (
    !$tanggalObj ||
    $tanggalObj->format('Y-m-d') !== $tanggal
) {
    $errors[] = 'Tanggal tidak valid.';
}

if ($keterangan === '') {
    $errors[] = 'Keterangan wajib diisi.';
} elseif (strlen($keterangan) > 255) {
    $errors[] = 'Keterangan maksimal 255 karakter.';
}

if (!is_numeric($jumlahRaw)) {
    $errors[] = 'Jumlah pengeluaran harus berupa angka.';
} else {
    $jumlah = (float) $jumlahRaw;

    if ($jumlah <= 0) {
        $errors[] = 'Jumlah pengeluaran harus lebih dari nol.';
    } elseif ($jumlah > 9999999999999.99) {
        $errors[] = 'Jumlah pengeluaran terlalu besar.';
    }
}

if ($errors !== []) {
    flash('danger', implode(' ', $errors));
    redirect('edit.php?id=' . $id);
}

$stmt = $pdo->prepare(
    'UPDATE pengeluaran SET
        tanggal = :tanggal,
        keterangan = :keterangan,
        jumlah = :jumlah
     WHERE id_pengeluaran = :id'
);

$stmt->execute([
    'tanggal' => $tanggal,
    'keterangan' => $keterangan,
    'jumlah' => $jumlah,
    'id' => $id,
]);

flash('success', 'Data pengeluaran berhasil diperbarui.');
redirect('index.php');

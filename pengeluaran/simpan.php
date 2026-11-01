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

$tanggal = trim((string) ($_POST['tanggal'] ?? ''));
$keterangan = trim((string) ($_POST['keterangan'] ?? ''));
$jumlahRaw = trim((string) ($_POST['jumlah'] ?? ''));

$errors = [];
$jumlah = 0.0;

$tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);

if (
    !$tanggalObj ||
    $tanggalObj->format('Y-m-d') !== $tanggal
) {
    $errors[] = 'Tanggal tidak valid.';
} elseif (!tanggal_di_bulan_ini($tanggal)) {
    $errors[] = 'Tanggal hanya boleh pada bulan ini ('
        . label_periode_bulan((string) date('n')) . ').';
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
    redirect('tambah.php');
}

$stmt = $pdo->prepare(
    'INSERT INTO pengeluaran
        (tanggal, keterangan, jumlah, id_user)
     VALUES
        (:tanggal, :keterangan, :jumlah, :id_user)'
);

$stmt->execute([
    'tanggal' => $tanggal,
    'keterangan' => $keterangan,
    'jumlah' => $jumlah,
    'id_user' => (int) ($_SESSION['user']['id_user'] ?? 0) ?: null,
]);

flash('success', 'Data pengeluaran berhasil ditambahkan.');
redirect('index.php');

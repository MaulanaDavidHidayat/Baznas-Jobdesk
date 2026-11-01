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

$idGuru = (int) ($_POST['id_guru'] ?? 0);
$tanggal = trim((string) ($_POST['tanggal'] ?? ''));
$jumlahRaw = trim((string) ($_POST['jumlah'] ?? ''));
$keterangan = trim((string) ($_POST['keterangan'] ?? ''));

$errors = [];

$cekGuru = $pdo->prepare(
    'SELECT id_guru FROM guru WHERE id_guru = :id LIMIT 1'
);
$cekGuru->execute(['id' => $idGuru]);

if (!$cekGuru->fetch()) {
    $errors[] = 'Guru yang dipilih tidak valid.';
}

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

if (!is_numeric($jumlahRaw)) {
    $errors[] = 'Jumlah infaq harus berupa angka.';
} else {
    $jumlah = (float) $jumlahRaw;

    if ($jumlah <= 0) {
        $errors[] = 'Jumlah infaq harus lebih dari nol.';
    } elseif ($jumlah > 9999999999999.99) {
        $errors[] = 'Jumlah infaq terlalu besar.';
    }
}

if (strlen($keterangan) > 255) {
    $errors[] = 'Keterangan maksimal 255 karakter.';
}

if ($errors !== []) {
    flash('danger', implode(' ', $errors));
    redirect('tambah.php');
}

$stmt = $pdo->prepare(
    'INSERT INTO infaq
        (id_guru, tanggal, jumlah, keterangan)
     VALUES
        (:id_guru, :tanggal, :jumlah, :keterangan)'
);

$stmt->execute([
    'id_guru' => $idGuru,
    'tanggal' => $tanggal,
    'jumlah' => $jumlah,
    'keterangan' => $keterangan === '' ? null : $keterangan,
]);

flash('success', 'Data infaq berhasil ditambahkan.');
redirect('index.php');

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

$id = (int) ($_POST['id_infaq'] ?? 0);
$idGuru = (int) ($_POST['id_guru'] ?? 0);
$tanggal = trim((string) ($_POST['tanggal'] ?? ''));
$jumlahRaw = trim((string) ($_POST['jumlah'] ?? ''));
$keterangan = trim((string) ($_POST['keterangan'] ?? ''));

$cekInfaq = $pdo->prepare(
    'SELECT id_infaq FROM infaq WHERE id_infaq = :id LIMIT 1'
);
$cekInfaq->execute(['id' => $id]);

if (!$cekInfaq->fetch()) {
    flash('danger', 'Data Infaq tidak ditemukan.');
    redirect('index.php');
}

$errors = [];
$jumlah = 0.0;

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
}

if (!is_numeric($jumlahRaw)) {
    $errors[] = 'Jumlah Infaq harus berupa angka.';
} else {
    $jumlah = (float) $jumlahRaw;

    if ($jumlah <= 0) {
        $errors[] = 'Jumlah Infaq harus lebih dari nol.';
    } elseif ($jumlah > 9999999999999.99) {
        $errors[] = 'Jumlah Infaq terlalu besar.';
    }
}

if (strlen($keterangan) > 255) {
    $errors[] = 'Keterangan maksimal 255 karakter.';
}

if ($errors !== []) {
    flash('danger', implode(' ', $errors));
    redirect('edit.php?id=' . $id);
}

$stmt = $pdo->prepare(
    'UPDATE infaq SET
        id_guru = :id_guru,
        tanggal = :tanggal,
        jumlah = :jumlah,
        keterangan = :keterangan
     WHERE id_infaq = :id'
);

$stmt->execute([
    'id_guru' => $idGuru,
    'tanggal' => $tanggal,
    'jumlah' => $jumlah,
    'keterangan' => $keterangan === '' ? null : $keterangan,
    'id' => $id,
]);

flash('success', 'Data Infaq berhasil diperbarui.');
redirect('index.php');

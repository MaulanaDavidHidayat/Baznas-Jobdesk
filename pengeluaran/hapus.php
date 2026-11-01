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

if ($id <= 0) {
    flash('danger', 'Data pengeluaran tidak ditemukan.');
    redirect('index.php');
}

$stmt = $pdo->prepare(
    'DELETE FROM pengeluaran WHERE id_pengeluaran = :id'
);
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    flash('warning', 'Data pengeluaran tidak ditemukan.');
} else {
    flash('success', 'Data pengeluaran berhasil dihapus.');
}

redirect('index.php');

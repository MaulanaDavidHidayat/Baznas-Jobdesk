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

if ($id <= 0) {
    flash('danger', 'Data infaq tidak ditemukan.');
    redirect('index.php');
}

$stmt = $pdo->prepare(
    'DELETE FROM infaq WHERE id_infaq = :id'
);
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    flash('warning', 'Data infaq tidak ditemukan.');
} else {
    flash('success', 'Data infaq berhasil dihapus.');
}

redirect('index.php');

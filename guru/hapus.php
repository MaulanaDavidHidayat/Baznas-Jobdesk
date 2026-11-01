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

if ($id <= 0) {
    flash('danger', 'Data guru tidak ditemukan.');
    redirect('index.php');
}

$cek = $pdo->prepare(
    'SELECT COUNT(*) FROM infaq WHERE id_guru = :id'
);
$cek->execute(['id' => $id]);

if ((int) $cek->fetchColumn() > 0) {
    flash(
        'danger',
        'Guru tidak dapat dihapus karena masih memiliki data infaq.'
    );
    redirect('index.php');
}

try {
    $stmt = $pdo->prepare(
        'DELETE FROM guru WHERE id_guru = :id'
    );
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        flash('warning', 'Data guru tidak ditemukan.');
    } else {
        flash('success', 'Data guru berhasil dihapus.');
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash(
            'danger',
            'Guru tidak dapat dihapus karena masih terkait data lain.'
        );
    } else {
        throw $e;
    }
}

redirect('index.php');

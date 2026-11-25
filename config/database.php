<?php
declare(strict_types=1);

$host = 'sql307.infinityfree.com';
$dbname = 'if0_43305523_db_baznas';
$dbuser = 'if0_43305523';
$dbpass = 'Baznas12345';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);

    exit(
        'Koneksi database gagal. Periksa pengaturan di '
        . 'config/database.php dan pastikan database sudah diimpor.'
    );
}
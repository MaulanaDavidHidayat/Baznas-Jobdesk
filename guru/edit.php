<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT id_guru, nama_guru
     FROM guru
     WHERE id_guru = :id
     LIMIT 1'
);
$stmt->execute(['id' => $id]);

$guru = $stmt->fetch();

if (!$guru) {
    flash('danger', 'Data guru tidak ditemukan.');
    redirect('index.php');
}

$pageTitle = 'Edit Guru';
$activePage = 'guru';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Edit Guru</h1>
        <p>Perbarui data guru yang sudah terdaftar.</p>
    </div>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h2>Form Data Guru</h2>
        <p>Kolom bertanda <span class="required">*</span> wajib diisi.</p>
    </div>

    <div class="form-card-body">
        <form method="POST" action="update.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >
            <input
                type="hidden"
                name="id_guru"
                value="<?= (int) $guru['id_guru'] ?>"
            >

            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_guru">
                        Nama Guru <span class="required">*</span>
                    </label>
                    <input
                        class="form-control"
                        type="text"
                        id="nama_guru"
                        name="nama_guru"
                        maxlength="100"
                        value="<?= e($guru['nama_guru']) ?>"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="form-actions">
                <div class="form-actions-left">
                    <a href="index.php" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>

                <div class="form-actions-right">
                    <button class="btn btn-primary" type="submit">
                        Perbarui Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

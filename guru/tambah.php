<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$pageTitle = 'Tambah Guru';
$activePage = 'guru';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Tambah Guru</h1>
        <p>Lengkapi data guru yang akan dicatat infaqnya.</p>
    </div>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h2>Form Data Guru</h2>
        <p>Kolom bertanda <span class="required">*</span> wajib diisi.</p>
    </div>

    <div class="form-card-body">
        <form method="POST" action="simpan.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
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
                        placeholder="Nama lengkap guru"
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
                        Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

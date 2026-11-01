<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$pageTitle = 'Tambah Admin';
$activePage = 'user';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Tambah Admin</h1>
        <p>Buat akun administrator baru untuk sistem infak.</p>
    </div>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h2>Form Data Admin</h2>
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
                    <label for="nama">
                        Nama Lengkap <span class="required">*</span>
                    </label>
                    <input
                        class="form-control"
                        type="text"
                        id="nama"
                        name="nama"
                        maxlength="100"
                        placeholder="Nama lengkap admin"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="username">
                        Username <span class="required">*</span>
                    </label>
                    <input
                        class="form-control"
                        type="text"
                        id="username"
                        name="username"
                        maxlength="50"
                        placeholder="Username untuk masuk"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="role">
                        Role <span class="required">*</span>
                    </label>
                    <select
                        class="form-control"
                        id="role"
                        name="role"
                        required
                    >
                        <?php foreach (available_roles() as $role): ?>
                            <option value="<?= e($role) ?>">
                                <?= e(role_label($role)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">
                        Role menentukan hak akses akun.
                    </small>
                </div>

                <div class="form-group">
                    <label for="password">
                        Password <span class="required">*</span>
                    </label>
                    <div class="password-wrapper">
                        <input
                            class="form-control"
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            placeholder="Minimal 8 karakter"
                            autocomplete="new-password"
                            required
                        >
                        <button
                            class="password-toggle"
                            type="button"
                            data-password-toggle="password"
                        >
                            Tampilkan
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_password">
                        Konfirmasi Password <span class="required">*</span>
                    </label>
                    <div class="password-wrapper">
                        <input
                            class="form-control"
                            type="password"
                            id="konfirmasi_password"
                            name="konfirmasi_password"
                            minlength="8"
                            placeholder="Ulangi password"
                            autocomplete="new-password"
                            required
                        >
                        <button
                            class="password-toggle"
                            type="button"
                            data-password-toggle="konfirmasi_password"
                        >
                            Tampilkan
                        </button>
                    </div>
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

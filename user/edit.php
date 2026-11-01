<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT id_user, nama, username, role
     FROM users
     WHERE id_user = :id
     LIMIT 1'
);
$stmt->execute(['id' => $id]);

$user = $stmt->fetch();

if (!$user) {
    flash('danger', 'Data admin tidak ditemukan.');
    redirect('index.php');
}

$pageTitle = 'Edit Admin';
$activePage = 'user';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Edit Admin</h1>
        <p>Perbarui data akun administrator.</p>
    </div>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h2>Form Data Admin</h2>
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
                name="id_user"
                value="<?= (int) $user['id_user'] ?>"
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
                        value="<?= e($user['nama']) ?>"
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
                        value="<?= e($user['username']) ?>"
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
                            <option
                                value="<?= e($role) ?>"
                                <?= $role === $user['role'] ? 'selected' : '' ?>
                            >
                                <?= e(role_label($role)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">
                        Role menentukan hak akses akun.
                    </small>
                </div>

                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <div class="password-wrapper">
                        <input
                            class="form-control"
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            placeholder="Kosongkan jika tidak diubah"
                            autocomplete="new-password"
                        >
                        <button
                            class="password-toggle"
                            type="button"
                            data-password-toggle="password"
                        >
                            Tampilkan
                        </button>
                    </div>
                    <small class="form-hint">
                        Minimal 8 karakter. Biarkan kosong untuk
                        mempertahankan password lama.
                    </small>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_password">
                        Konfirmasi Password Baru
                    </label>
                    <div class="password-wrapper">
                        <input
                            class="form-control"
                            type="password"
                            id="konfirmasi_password"
                            name="konfirmasi_password"
                            minlength="8"
                            placeholder="Ulangi password baru"
                            autocomplete="new-password"
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
                        Perbarui Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

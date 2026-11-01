<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT id_pengeluaran, tanggal, keterangan, jumlah, id_user
     FROM pengeluaran
     WHERE id_pengeluaran = :id
     LIMIT 1'
);
$stmt->execute(['id' => $id]);

$pengeluaran = $stmt->fetch();

if (!$pengeluaran) {
    flash('danger', 'Data pengeluaran tidak ditemukan.');
    redirect('index.php');
}

$pageTitle = 'Edit Pengeluaran';
$activePage = 'pengeluaran';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Edit Pengeluaran</h1>
        <p>Perbarui catatan pengeluaran yang sudah tersimpan.</p>
    </div>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h2>Form Data Pengeluaran</h2>
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
                name="id_pengeluaran"
                value="<?= (int) $pengeluaran['id_pengeluaran'] ?>"
            >

            <div class="form-grid">
                <div class="form-group">
                    <label for="tanggal">
                        Tanggal <span class="required">*</span>
                    </label>
                    <input
                        class="form-control"
                        type="date"
                        id="tanggal"
                        name="tanggal"
                        value="<?= e($pengeluaran['tanggal']) ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="jumlah">
                        Jumlah (Rp) <span class="required">*</span>
                    </label>
                    <input
                        class="form-control"
                        type="number"
                        id="jumlah"
                        name="jumlah"
                        min="0.01"
                        step="0.01"
                        value="<?= e((string) (float) $pengeluaran['jumlah']) ?>"
                        required
                    >
                    <span
                        class="amount-preview"
                        data-amount-preview="jumlah"
                    >Rp 0</span>
                </div>

                <div class="form-group full-width">
                    <label for="keterangan">
                        Keterangan <span class="required">*</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="keterangan"
                        name="keterangan"
                        maxlength="255"
                        required
                    ><?= e($pengeluaran['keterangan']) ?></textarea>
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

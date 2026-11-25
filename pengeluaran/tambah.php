<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$pageTitle = 'Tambah Pengeluaran';
$activePage = 'pengeluaran';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Tambah Pengeluaran</h1>
        <p>Catat penggunaan dana infak BAZNAS.</p>
    </div>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h2>Form Data Pengeluaran</h2>
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
                    <label for="tanggal">
                        Tanggal <span class="required">*</span>
                    </label>
                    <input
                        class="form-control"
                        type="date"
                        id="tanggal"
                        name="tanggal"
                        value="<?= e(date('Y-m-d')) ?>"
                        required
                        autofocus
                    >
                    <small class="form-hint">
                        Pilih tanggal sesuai transaksi.
                    </small>
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
                        placeholder="Contoh: 150000"
                        required
                    >
                    <span
                        class="amount-preview"
                        data-amount-preview="jumlah"
                    >Rp 0</span>
                    <small class="form-hint">
                        Masukkan nominal tanpa titik atau koma pemisah ribuan.
                    </small>
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
                        placeholder="Jelaskan keperluan pengeluaran"
                        required
                    ></textarea>
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

<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$daftarGuru = $pdo
    ->query(
        'SELECT id_guru, nama_guru
         FROM guru
         ORDER BY nama_guru ASC'
    )
    ->fetchAll();

[$awalBulan, $akhirBulan] = batas_bulan_ini();
$periodeBulan = label_periode_bulan((string) date('n'));

$pageTitle = 'Tambah Infaq';
$activePage = 'infaq';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Tambah Infaq</h1>
        <p>Catat Infaq baru yang diterima dari guru.</p>
    </div>
</div>

<?php if (empty($daftarGuru)): ?>
    <div class="alert alert-warning">
        Belum ada data guru. Tambahkan guru terlebih dahulu sebelum
        mencatat Infaq.
        <a href="../guru/tambah.php" class="fw-bold text-primary">
            Tambah Guru
        </a>
    </div>
<?php endif; ?>

<div class="form-card">
    <div class="form-card-header">
        <h2>Form Data Infaq</h2>
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
                    <label for="id_guru">
                        Nama Guru <span class="required">*</span>
                    </label>
                    <select
                        class="form-control"
                        id="id_guru"
                        name="id_guru"
                        required
                        <?= empty($daftarGuru) ? 'disabled' : '' ?>
                    >
                        <option value="">Pilih guru</option>
                        <?php foreach ($daftarGuru as $guru): ?>
                            <option value="<?= (int) $guru['id_guru'] ?>">
                                <?= e($guru['nama_guru']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

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
                        min="<?= e($awalBulan) ?>"
                        max="<?= e($akhirBulan) ?>"
                        required
                    >
                    <small class="form-hint">
                        Tanggal hanya bisa diisi pada bulan ini
                        (<?= e($periodeBulan) ?>).
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
                        placeholder="Contoh: 50000"
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

                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <input
                        class="form-control"
                        type="text"
                        id="keterangan"
                        name="keterangan"
                        maxlength="255"
                        placeholder="Keterangan (Opsional)"
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
                    <button
                        class="btn btn-primary"
                        type="submit"
                        <?= empty($daftarGuru) ? 'disabled' : '' ?>
                    >
                        Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

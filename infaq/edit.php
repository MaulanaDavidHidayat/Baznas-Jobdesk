<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT id_infaq, id_guru, tanggal, jumlah, keterangan
     FROM infaq
     WHERE id_infaq = :id
     LIMIT 1'
);
$stmt->execute(['id' => $id]);

$infaq = $stmt->fetch();

if (!$infaq) {
    flash('danger', 'Data infaq tidak ditemukan.');
    redirect('index.php');
}

$daftarGuru = $pdo
    ->query(
        'SELECT id_guru, nama_guru
         FROM guru
         ORDER BY nama_guru ASC'
    )
    ->fetchAll();

$sudahInfaq = [];
$stmtInfaq = $pdo->query(
    'SELECT id_guru, SUBSTRING(tanggal, 1, 7) AS periode
     FROM infaq
     GROUP BY id_guru, SUBSTRING(tanggal, 1, 7)'
);

foreach ($stmtInfaq->fetchAll() as $row) {
    $sudahInfaq[$row['periode']][] = (int) $row['id_guru'];
}

$periodeAwal = substr($infaq['tanggal'], 0, 7);
$sudahPilih = in_array(
    (int) $infaq['id_guru'],
    $sudahInfaq[$periodeAwal] ?? [],
    true
);

$pageTitle = 'Edit infaq';
$activePage = 'infaq';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Edit infaq</h1>
        <p>Perbarui catatan infaq yang sudah tersimpan.</p>
    </div>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h2>Form Data infaq</h2>
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
                name="id_infaq"
                value="<?= (int) $infaq['id_infaq'] ?>"
            >

            <div class="form-grid">
                <div class="form-group">
                    <label for="id_guru">
                        Nama Guru <span class="required">*</span>
                    </label>
                    <select
                        class="form-control<?= $sudahPilih ? ' pilihan-sudah-infaq' : '' ?>"
                        id="id_guru"
                        name="id_guru"
                        data-infaq-bulan="<?= e(json_encode($sudahInfaq, JSON_THROW_ON_ERROR)) ?>"
                        required
                    >
                        <option value="">Pilih guru</option>
                        <?php foreach ($daftarGuru as $guru): ?>
                            <?php $sudah = in_array(
                                (int) $guru['id_guru'],
                                $sudahInfaq[$periodeAwal] ?? [],
                                true
                            ); ?>
                            <option
                                value="<?= (int) $guru['id_guru'] ?>"
                                data-nama-guru="<?= e($guru['nama_guru']) ?>"
                                class="<?= $sudah ? 'sudah-infaq' : '' ?>"
                                <?= (int) $guru['id_guru'] === (int) $infaq['id_guru'] ? 'selected' : '' ?>
                            >
                                <?= e($guru['nama_guru']) ?><?= $sudah ? ' · Sudah infaq' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint" data-infaq-status></small>
                    <small class="form-hint">
                        Latar hijau dan tanda "Sudah infaq" menandakan guru
                        sudah infaq pada bulan yang dipilih di kolom tanggal.
                    </small>
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
                        value="<?= e($infaq['tanggal']) ?>"
                        required
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
                        value="<?= e((string) (float) $infaq['jumlah']) ?>"
                        required
                    >
                    <span
                        class="amount-preview"
                        data-amount-preview="jumlah"
                    >Rp 0</span>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <input
                        class="form-control"
                        type="text"
                        id="keterangan"
                        name="keterangan"
                        maxlength="255"
                        value="<?= e($infaq['keterangan']) ?>"
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
                    <button class="btn btn-primary" type="submit">
                        Perbarui Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

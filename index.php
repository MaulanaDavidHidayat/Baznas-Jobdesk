<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$bulan = filter_bulan_dipilih('semua');
$periodeLabel = label_periode_bulan($bulan);

// Ringkasan Infaq pada periode terpilih
$infaqWhere = [];
$infaqParams = [];

$infaqBulanSql = where_bulan(
    $bulan,
    'i.tanggal',
    $infaqParams,
    'dash_infaq_bulan'
);

if ($infaqBulanSql !== null) {
    $infaqWhere[] = $infaqBulanSql;
}

$infaqWhereSql = $infaqWhere !== []
    ? ' WHERE ' . implode(' AND ', $infaqWhere)
    : '';

$summaryInfaqStmt = $pdo->prepare(
    'SELECT
        COUNT(*) AS jumlah_data,
        COALESCE(SUM(i.jumlah), 0) AS total_nilai,
        COUNT(DISTINCT i.id_guru) AS jumlah_guru
     FROM infaq i' . $infaqWhereSql
);
$summaryInfaqStmt->execute($infaqParams);
$summaryInfaq = $summaryInfaqStmt->fetch() ?: [];

$totalInfaq = (float) ($summaryInfaq['total_nilai'] ?? 0);
$jumlahInfaq = (int) ($summaryInfaq['jumlah_data'] ?? 0);
$totalGuruInfaq = (int) ($summaryInfaq['jumlah_guru'] ?? 0);

// Ringkasan pengeluaran pada periode terpilih
$pengeluaranWhere = [];
$pengeluaranParams = [];

$pengeluaranBulanSql = where_bulan(
    $bulan,
    'p.tanggal',
    $pengeluaranParams,
    'dash_pengeluaran_bulan'
);

if ($pengeluaranBulanSql !== null) {
    $pengeluaranWhere[] = $pengeluaranBulanSql;
}

$pengeluaranWhereSql = $pengeluaranWhere !== []
    ? ' WHERE ' . implode(' AND ', $pengeluaranWhere)
    : '';

$summaryPengeluaranStmt = $pdo->prepare(
    'SELECT
        COUNT(*) AS jumlah_data,
        COALESCE(SUM(p.jumlah), 0) AS total_nilai
     FROM pengeluaran p' . $pengeluaranWhereSql
);
$summaryPengeluaranStmt->execute($pengeluaranParams);
$summaryPengeluaran = $summaryPengeluaranStmt->fetch() ?: [];

$totalPengeluaran = (float) ($summaryPengeluaran['total_nilai'] ?? 0);
$jumlahPengeluaran = (int) ($summaryPengeluaran['jumlah_data'] ?? 0);

// Saldo Infaq
$saldo = $totalInfaq - $totalPengeluaran;

// Transaksi Infaq terbaru
$sqlInfaq = "
    SELECT
        i.id_infaq,
        i.tanggal,
        i.jumlah,
        i.keterangan,
        g.nama_guru
    FROM infaq i
    INNER JOIN guru g ON i.id_guru = g.id_guru"
    . $infaqWhereSql .
    " ORDER BY i.tanggal DESC, i.id_infaq DESC
    LIMIT 5
";

$infaqStmt = $pdo->prepare($sqlInfaq);
$infaqStmt->execute($infaqParams);
$infaqTerbaru = $infaqStmt->fetchAll();

// Pengeluaran terbaru
$sqlPengeluaran = "
    SELECT
        p.id_pengeluaran,
        p.tanggal,
        p.keterangan,
        p.jumlah,
        u.nama AS nama_admin
    FROM pengeluaran p
    LEFT JOIN users u ON p.id_user = u.id_user"
    . $pengeluaranWhereSql .
    " ORDER BY p.tanggal DESC, p.id_pengeluaran DESC
    LIMIT 5
";

$pengeluaranStmt = $pdo->prepare($sqlPengeluaran);
$pengeluaranStmt->execute($pengeluaranParams);
$pengeluaranTerbaru = $pengeluaranStmt->fetchAll();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
$basePath = '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="welcome-banner">
    <div>
        <span class="welcome-eyebrow">SISTEM INFORMASI BAZNAS</span>

        <h1>
            Assalamu'alaikum, <?= e(current_user_name()) ?>.
        </h1>

        <p>
            Ringkasan pengelolaan Infaq dan pengeluaran BAZNAS
            periode <?= e($periodeLabel) ?> per <?= e(date('d/m/Y')) ?>.
            Pastikan setiap transaksi dicatat dengan teliti agar
            laporan tetap akurat.
        </p>

        <div class="welcome-actions">
            <a href="infaq/tambah.php" class="btn btn-light">
                + Catat Infaq
            </a>

            <a href="pengeluaran/tambah.php" class="btn btn-ghost">
                + Catat Pengeluaran
            </a>
        </div>
    </div>

    <div class="welcome-badge">
        <img
            src="assets/favicon.png"
            alt="Logo BAZNAS"
            class="welcome-logo"
        >

        <div>
            <strong>BAZNAS</strong>
            <span>Badan Amil Zakat Nasional</span>
        </div>
    </div>
</div>

<!-- Filter periode -->
<form class="filter-bar dashboard-filter" method="GET" action="index.php">
    <div class="filter-controls">
        <label class="filter-label" for="bulan">Periode</label>

        <select
            class="form-control"
            id="bulan"
            name="bulan"
            aria-label="Filter bulan"
            data-auto-filter
        >
            <option
                value="semua"
                <?= $bulan === 'semua' ? 'selected' : '' ?>
            >
                Semua Bulan
            </option>

            <?php foreach (daftar_bulan() as $angka => $nama): ?>
                <option
                    value="<?= $angka ?>"
                    <?= $bulan === (string) $angka ? 'selected' : '' ?>
                >
                    <?= e($nama) ?> <?= e(date('Y')) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn btn-primary" type="submit">
            Terapkan
        </button>
    </div>

    <span class="filter-hint">
        Menampilkan: <strong><?= e($periodeLabel) ?></strong>
    </span>
</form>

<!-- Statistik -->
<div class="stats-grid">

    <div class="stat-card blue">
        <div class="stat-top">
            <span class="stat-label">Saldo Keuangan</span>
            <span class="stat-icon">▤</span>
        </div>

        <strong class="stat-value">
            <?= e(rupiah($saldo)) ?>
        </strong>

        <span class="stat-description">
            Infaq dikurangi pengeluaran pada periode ini
        </span>
    </div>

    <div class="stat-card green">
        <div class="stat-top">
            <span class="stat-label">Total Infaq</span>
            <span class="stat-icon">＋</span>
        </div>

        <strong class="stat-value">
            <?= e(rupiah($totalInfaq)) ?>
        </strong>

        <span class="stat-description">
            <?= number_format($jumlahInfaq, 0, ',', '.') ?> catatan ·
            <?= e($periodeLabel) ?>
        </span>
    </div>

    <div class="stat-card red">
        <div class="stat-top">
            <span class="stat-label">Total Pengeluaran</span>
            <span class="stat-icon">↗</span>
        </div>

        <strong class="stat-value">
            <?= e(rupiah($totalPengeluaran)) ?>
        </strong>

        <span class="stat-description">
            <?= number_format($jumlahPengeluaran, 0, ',', '.') ?> catatan ·
            <?= e($periodeLabel) ?>
        </span>
    </div>

    <div class="stat-card orange">
        <div class="stat-top">
            <span class="stat-label">Guru Sudah Infaq</span>
            <span class="stat-icon">♙</span>
        </div>

        <strong class="stat-value">
            <?= number_format($totalGuruInfaq, 0, ',', '.') ?>
        </strong>

        <span class="stat-description">
            Guru yang berinfaq pada periode ini
        </span>
    </div>

</div>

<h2 class="section-title">Aktivitas Terbaru</h2>

<!-- Transaksi Infaq terbaru -->
<div class="card mb-4">

    <div class="card-header">
        <div>
            <h2>Infaq Terbaru</h2>
            <p class="text-muted mt-1">
                Lima catatan Infaq terakhir · <?= e($periodeLabel) ?>.
            </p>
        </div>

        <a href="infaq/index.php" class="btn btn-secondary btn-sm">
            Lihat Semua
        </a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr text-align="center">
                    <th>No.</th>
                    <th>Nama Guru</th>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($infaqTerbaru)): ?>
                    <tr>
                        <td colspan="5" class="table-empty">
                            Belum ada data Infaq pada
                            <?= e($periodeLabel) ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($infaqTerbaru as $index => $row): ?>
                        <tr text-align="center">
                            <td><?= $index + 1 ?></td>

                            <td>
                                <strong>
                                    <?= e($row['nama_guru']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= e($row['keterangan'] ?: '-') ?>
                            </td>

                            <td class="nowrap">
                                <strong class="text-primary">
                                    <?= e(rupiah($row['jumlah'])) ?>
                                </strong>
                            </td>
                            <td>
                                <?php
                                $daftarBulan = [
                                    1 => 'Januari',
                                    'Februari',
                                    'Maret',
                                    'April',
                                    'Mei',
                                    'Juni',
                                    'Juli',
                                    'Agustus',
                                    'September',
                                    'Oktober',
                                    'November',
                                    'Desember'
                                ];

                                $timestamp = strtotime($row['tanggal']);

                                $tanggalFormat = date('d', $timestamp) . ' ' .
                                    $daftarBulan[(int) date('n', $timestamp)] . ' ' .
                                    date('Y', $timestamp);
                                ?>
                                <?= e($tanggalFormat) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Pengeluaran terbaru -->
<div class="card">

    <div class="card-header">
        <div>
            <h2>Pengeluaran Terbaru</h2>
            <p class="text-muted mt-1">
                Lima catatan pengeluaran terakhir · <?= e($periodeLabel) ?>.
            </p>
        </div>

        <a
            href="pengeluaran/index.php"
            class="btn btn-secondary btn-sm"
        >
            Lihat Semua
        </a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr text-align="center">
                    <th>No.</th>
                    <th>Admin</th>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($pengeluaranTerbaru)): ?>
                    <tr>
                        <td colspan="5" class="table-empty">
                            Belum ada data pengeluaran pada
                            <?= e($periodeLabel) ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pengeluaranTerbaru as $index => $row): ?>
                        <tr text-align="center">
                            <td><?= $index + 1 ?></td>

                            <td>
                                <?= e($row['nama_admin'] ?? 'Admin tidak tersedia') ?>
                            </td>
                            
                            <td>
                                <?= e($row['keterangan']) ?>
                            </td>

                            <td class="nowrap">
                                <strong class="text-danger">
                                    <?= e(rupiah($row['jumlah'])) ?>
                                </strong>
                            </td>
                            <td>
                                <?php
                                $daftarBulan = [
                                    1 => 'Januari',
                                    'Februari',
                                    'Maret',
                                    'April',
                                    'Mei',
                                    'Juni',
                                    'Juli',
                                    'Agustus',
                                    'September',
                                    'Oktober',
                                    'November',
                                    'Desember'
                                ];

                                $timestamp = strtotime($row['tanggal']);

                                $tanggalFormat = date('d', $timestamp) . ' ' .
                                    $daftarBulan[(int) date('n', $timestamp)] . ' ' .
                                    date('Y', $timestamp);
                                ?>
                                <?= e($tanggalFormat) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

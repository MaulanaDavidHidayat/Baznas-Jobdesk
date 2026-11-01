<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$defaultFrom = date('Y-m-01');
$defaultTo = date('Y-m-d');

$from = trim((string) ($_GET['from'] ?? $defaultFrom));
$to = trim((string) ($_GET['to'] ?? $defaultTo));

$fromObj = DateTime::createFromFormat('Y-m-d', $from);
$toObj = DateTime::createFromFormat('Y-m-d', $to);

if (!$fromObj || $fromObj->format('Y-m-d') !== $from) {
    $from = $defaultFrom;
}

if (!$toObj || $toObj->format('Y-m-d') !== $to) {
    $to = $defaultTo;
}

if ($from > $to) {
    [$from, $to] = [$to, $from];
}

$params = [
    'from' => $from,
    'to' => $to,
];

$totalInfaqStmt = $pdo->prepare(
    'SELECT COALESCE(SUM(jumlah), 0)
     FROM infaq
     WHERE tanggal BETWEEN :from AND :to'
);
$totalInfaqStmt->execute($params);
$totalInfaq = (float) $totalInfaqStmt->fetchColumn();

$totalPengeluaranStmt = $pdo->prepare(
    'SELECT COALESCE(SUM(jumlah), 0)
     FROM pengeluaran
     WHERE tanggal BETWEEN :from AND :to'
);
$totalPengeluaranStmt->execute($params);
$totalPengeluaran = (float) $totalPengeluaranStmt->fetchColumn();

$saldo = $totalInfaq - $totalPengeluaran;

$jumlahInfaqStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM infaq
     WHERE tanggal BETWEEN :from AND :to'
);
$jumlahInfaqStmt->execute($params);
$jumlahInfaq = (int) $jumlahInfaqStmt->fetchColumn();

$jumlahPengeluaranStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM pengeluaran
     WHERE tanggal BETWEEN :from AND :to'
);
$jumlahPengeluaranStmt->execute($params);
$jumlahPengeluaran = (int) $jumlahPengeluaranStmt->fetchColumn();

$totalTransaksi = $jumlahInfaq + $jumlahPengeluaran;

$infaqStmt = $pdo->prepare(
    'SELECT i.tanggal, i.jumlah, i.keterangan, g.nama_guru
     FROM infaq i
     INNER JOIN guru g ON i.id_guru = g.id_guru
     WHERE i.tanggal BETWEEN :from AND :to
     ORDER BY i.tanggal DESC, i.id_infaq DESC'
);
$infaqStmt->execute($params);
$daftarInfaq = $infaqStmt->fetchAll();

$pengeluaranStmt = $pdo->prepare(
    'SELECT p.tanggal, p.keterangan, p.jumlah, u.nama AS nama_admin
     FROM pengeluaran p
     LEFT JOIN users u ON p.id_user = u.id_user
     WHERE p.tanggal BETWEEN :from AND :to
     ORDER BY p.tanggal DESC, p.id_pengeluaran DESC'
);
$pengeluaranStmt->execute($params);
$daftarPengeluaran = $pengeluaranStmt->fetchAll();

$pageTitle = 'Laporan';
$activePage = 'laporan';
$basePath = '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Laporan</h1>
        <p>
            Rekapitulasi Infaq dan pengeluaran pada periode yang dipilih.
        </p>
    </div>

    <div class="heading-actions">
        <button class="btn btn-secondary" type="button" data-print>
            Cetak Laporan
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="filter-bar" method="GET" action="laporan.php">
            <div class="search-form">
                <input
                    class="form-control"
                    type="date"
                    name="from"
                    value="<?= e($from) ?>"
                    aria-label="Tanggal awal"
                    required
                >

                <input
                    class="form-control"
                    type="date"
                    name="to"
                    value="<?= e($to) ?>"
                    aria-label="Tanggal akhir"
                    required
                >

                <button class="btn btn-primary" type="submit">
                    Terapkan
                </button>

                <a href="laporan.php" class="btn btn-secondary">
                    Reset
                </a>
            </div>
        </form>

        <p class="text-muted">
            Periode:
            <?= e(date('d-m-Y', strtotime($from))) ?>
            s.d.
            <?= e(date('d-m-Y', strtotime($to))) ?>
        </p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-top">
            <span class="stat-label">Total Infaq</span>
            <span class="stat-icon">＋</span>
        </div>

        <strong class="stat-value">
            <?= e(rupiah($totalInfaq)) ?>
        </strong>

        <span class="stat-description">
            <?= number_format($jumlahInfaq, 0, ',', '.') ?> transaksi
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
            <?= number_format($jumlahPengeluaran, 0, ',', '.') ?> transaksi
        </span>
    </div>

    <div class="stat-card blue">
        <div class="stat-top">
            <span class="stat-label">Saldo Periode</span>
            <span class="stat-icon">▤</span>
        </div>

        <strong class="stat-value">
            <?= e(rupiah($saldo)) ?>
        </strong>

        <span class="stat-description">
            Infaq dikurangi pengeluaran
        </span>
    </div>

    <div class="stat-card orange">
        <div class="stat-top">
            <span class="stat-label">Jumlah Transaksi</span>
            <span class="stat-icon">≡</span>
        </div>

        <strong class="stat-value">
            <?= number_format($totalTransaksi, 0, ',', '.') ?>
        </strong>

        <span class="stat-description">
            Seluruh transaksi pada periode ini
        </span>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <div>
            <h2>Rincian Infaq</h2>
            <p class="text-muted mt-1">
                Infaq yang diterima pada periode ini.
            </p>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Nama Guru</th>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($daftarInfaq)): ?>
                    <tr>
                        <td colspan="5" class="table-empty">
                            Tidak ada Infaq pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarInfaq as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <?= e(date(
                                    'd-m-Y',
                                    strtotime($row['tanggal'])
                                )) ?>
                            </td>
                            <td>
                                <strong><?= e($row['nama_guru']) ?></strong>
                            </td>
                            <td><?= e($row['keterangan'] ?: '-') ?></td>
                            <td class="text-right">
                                <strong class="text-primary">
                                    <?= e(rupiah($row['jumlah'])) ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h2>Rincian Pengeluaran</h2>
            <p class="text-muted mt-1">
                Pengeluaran yang dicatat pada periode ini.
            </p>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Admin</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($daftarPengeluaran)): ?>
                    <tr>
                        <td colspan="5" class="table-empty">
                            Tidak ada pengeluaran pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarPengeluaran as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <?= e(date(
                                    'd-m-Y',
                                    strtotime($row['tanggal'])
                                )) ?>
                            </td>
                            <td><?= e($row['keterangan']) ?></td>
                            <td>
                                <?= e($row['nama_admin'] ?? 'Admin tidak tersedia') ?>
                            </td>
                            <td class="text-right">
                                <strong class="text-danger">
                                    <?= e(rupiah($row['jumlah'])) ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

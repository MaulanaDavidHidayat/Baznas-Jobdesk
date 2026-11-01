<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$q = trim((string) ($_GET['q'] ?? ''));
$bulan = filter_bulan_dipilih('sekarang');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;

$where = [];
$params = [];

$bulanSql = where_bulan(
    $bulan,
    'p.tanggal',
    $params,
    'pengeluaran_bulan'
);

if ($bulanSql !== null) {
    $where[] = $bulanSql;
}

if ($q !== '') {
    $where[] = '(p.keterangan LIKE :q_ket OR u.nama LIKE :q_nama)';
    $params['q_ket'] = '%' . $q . '%';
    $params['q_nama'] = '%' . $q . '%';
}

$whereSql = $where !== []
    ? ' WHERE ' . implode(' AND ', $where)
    : '';

$fromSql = ' FROM pengeluaran p
    LEFT JOIN users u ON p.id_user = u.id_user';

$summaryStmt = $pdo->prepare(
    'SELECT
        COUNT(*) AS jumlah_data,
        COALESCE(SUM(p.jumlah), 0) AS total_nilai'
    . $fromSql . $whereSql
);
$summaryStmt->execute($params);
$summary = $summaryStmt->fetch() ?: [];

$total = (int) ($summary['jumlah_data'] ?? 0);
$totalNilai = (float) ($summary['total_nilai'] ?? 0);

$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    'SELECT p.id_pengeluaran, p.tanggal, p.keterangan, p.jumlah,
            u.nama AS nama_admin'
    . $fromSql . $whereSql .
    ' ORDER BY p.tanggal DESC, p.id_pengeluaran DESC
      LIMIT :limit OFFSET :offset'
);

foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$daftarPengeluaran = $stmt->fetchAll();

$periodeLabel = label_periode_bulan($bulan);
$isFiltered = $q !== '' || $bulan !== (string) date('n');

$pageTitle = 'Data Pengeluaran';
$activePage = 'pengeluaran';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Data Pengeluaran</h1>
        <p>Catatan pengeluaran dana infak pada periode <?= e($periodeLabel) ?>.</p>
    </div>

    <div class="heading-actions">
        <a href="tambah.php" class="btn btn-primary">
            + Catat Pengeluaran
        </a>
    </div>
</div>

<div class="summary-grid">
    <div class="stat-card red">
        <div class="stat-top">
            <span class="stat-label">Total Pengeluaran</span>
            <span class="stat-icon">↗</span>
        </div>

        <strong class="stat-value">
            <?= e(rupiah($totalNilai)) ?>
        </strong>

        <span class="stat-description">
            <?= e($periodeLabel) ?>
        </span>
    </div>

    <div class="stat-card orange">
        <div class="stat-top">
            <span class="stat-label">Jumlah Catatan</span>
            <span class="stat-icon">≡</span>
        </div>

        <strong class="stat-value">
            <?= number_format($total, 0, ',', '.') ?>
        </strong>

        <span class="stat-description">
            Catatan pengeluaran pada periode ini
        </span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h2>Daftar Pengeluaran</h2>
            <p class="text-muted mt-1">
                <?= number_format($total, 0, ',', '.') ?> catatan ·
                Total <?= e(rupiah($totalNilai)) ?> ·
                <?= e($periodeLabel) ?>
            </p>
        </div>
    </div>

    <div class="card-body">
        <form class="filter-bar" method="GET" action="index.php">
            <div class="search-form">
                <input
                    class="form-control"
                    type="text"
                    name="q"
                    value="<?= e($q) ?>"
                    placeholder="Cari keterangan atau admin..."
                >

                <select
                    class="form-control"
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

                <?php if ($isFiltered): ?>
                    <a href="index.php" class="btn btn-secondary">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
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
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($daftarPengeluaran)): ?>
                    <tr>
                        <td colspan="6" class="table-empty">
                            Belum ada data pengeluaran pada
                            <?= e($periodeLabel) ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarPengeluaran as $index => $row): ?>
                        <tr>
                            <td><?= $offset + $index + 1 ?></td>

                            <td>
                                <?= e($row['nama_admin'] ?? 'Admin tidak tersedia') ?>
                            </td>

                            <td><?= e($row['keterangan']) ?></td>

                            <td>
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

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="edit.php?id=<?= (int) $row['id_pengeluaran'] ?>"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        Edit
                                    </a>

                                    <form method="POST" action="hapus.php">
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(csrf_token()) ?>"
                                        >
                                        <input
                                            type="hidden"
                                            name="id_pengeluaran"
                                            value="<?= (int) $row['id_pengeluaran'] ?>"
                                        >
                                        <button
                                            class="btn btn-outline-danger btn-sm"
                                            type="submit"
                                            data-confirm="Hapus catatan pengeluaran tanggal <?= e(date('d-m-Y', strtotime($row['tanggal']))) ?>?"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

        <div class="table-footer">
            <span>
                Halaman <?= $page ?> dari <?= $totalPages ?>
            </span>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= e(http_build_query([
                        'q' => $q,
                        'bulan' => $bulan,
                        'page' => $page - 1,
                    ])) ?>">
                        &laquo;
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= e(http_build_query([
                            'q' => $q,
                            'bulan' => $bulan,
                            'page' => $i,
                        ])) ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= e(http_build_query([
                        'q' => $q,
                        'bulan' => $bulan,
                        'page' => $page + 1,
                    ])) ?>">
                        &raquo;
                    </a>
                <?php endif; ?>
            </div>
        </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

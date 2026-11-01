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
    'i.tanggal',
    $params,
    'infaq_bulan'
);

if ($bulanSql !== null) {
    $where[] = $bulanSql;
}

if ($q !== '') {
    $where[] = '(g.nama_guru LIKE :q_guru OR i.keterangan LIKE :q_ket)';
    $params['q_guru'] = '%' . $q . '%';
    $params['q_ket'] = '%' . $q . '%';
}

$whereSql = $where !== []
    ? ' WHERE ' . implode(' AND ', $where)
    : '';

$fromSql = ' FROM infaq i
    INNER JOIN guru g ON i.id_guru = g.id_guru';

$summaryStmt = $pdo->prepare(
    'SELECT
        COUNT(*) AS jumlah_data,
        COALESCE(SUM(i.jumlah), 0) AS total_nilai,
        COUNT(DISTINCT i.id_guru) AS jumlah_guru'
    . $fromSql . $whereSql
);
$summaryStmt->execute($params);
$summary = $summaryStmt->fetch() ?: [];

$total = (int) ($summary['jumlah_data'] ?? 0);
$totalNilai = (float) ($summary['total_nilai'] ?? 0);
$totalGuru = (int) ($summary['jumlah_guru'] ?? 0);

$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    'SELECT i.id_infaq, i.tanggal, i.jumlah, i.keterangan, g.nama_guru'
    . $fromSql . $whereSql .
    ' ORDER BY i.tanggal DESC, i.id_infaq DESC
      LIMIT :limit OFFSET :offset'
);

foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$daftarInfaq = $stmt->fetchAll();

$periodeLabel = label_periode_bulan($bulan);
$isFiltered = $q !== '' || $bulan !== (string) date('n');

$pageTitle = 'Data Infaq';
$activePage = 'infaq';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Data Infaq</h1>
        <p>Catatan Infaq guru pada periode <?= e($periodeLabel) ?>.</p>
    </div>

    <div class="heading-actions">
        <a href="tambah.php" class="btn btn-primary">
            + Catat Infaq
        </a>
    </div>
</div>

<div class="summary-grid">
    <div class="stat-card green">
        <div class="stat-top">
            <span class="stat-label">Total Infaq</span>
            <span class="stat-icon">＋</span>
        </div>

        <strong class="stat-value">
            <?= e(rupiah($totalNilai)) ?>
        </strong>

        <span class="stat-description">
            <?= e($periodeLabel) ?>
        </span>
    </div>

    <div class="stat-card blue">
        <div class="stat-top">
            <span class="stat-label">Guru Sudah Infaq</span>
            <span class="stat-icon">♙</span>
        </div>

        <strong class="stat-value">
            <?= number_format($totalGuru, 0, ',', '.') ?>
        </strong>

        <span class="stat-description">
            Guru yang mencatat Infaq pada periode ini
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
            Catatan Infaq pada periode ini
        </span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h2>Daftar Infaq</h2>
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
                    placeholder="Cari nama guru atau keterangan..."
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
                    <th>Nama Guru</th>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($daftarInfaq)): ?>
                    <tr>
                        <td colspan="6" class="table-empty">
                            Belum ada data Infaq pada
                            <?= e($periodeLabel) ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarInfaq as $index => $row): ?>
                        <tr text-align="center">
                            <td><?= $offset + $index + 1 ?></td>

                            <td>
                                <strong><?= e($row['nama_guru']) ?></strong>
                            </td>

                            <td><?= e($row['keterangan'] ?: '-') ?></td>

                            <td>
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

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="edit.php?id=<?= (int) $row['id_infaq'] ?>"
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
                                            name="id_infaq"
                                            value="<?= (int) $row['id_infaq'] ?>"
                                        >
                                        <button
                                            class="btn btn-outline-danger btn-sm"
                                            type="submit"
                                            data-confirm="Hapus catatan Infaq tanggal <?= e(date('d-m-Y', strtotime($row['tanggal']))) ?>?"
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

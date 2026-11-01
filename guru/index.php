<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$q = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;

$where = [];
$params = [];

if ($q !== '') {
    $where[] = 'nama_guru LIKE :q_nama';
    $params['q_nama'] = '%' . $q . '%';
}

$whereSql = $where !== []
    ? ' WHERE ' . implode(' AND ', $where)
    : '';

$summaryStmt = $pdo->prepare(
    'SELECT COUNT(*) AS jumlah_data FROM guru' . $whereSql
);
$summaryStmt->execute($params);
$summary = $summaryStmt->fetch() ?: [];

$total = (int) ($summary['jumlah_data'] ?? 0);

$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    'SELECT id_guru, nama_guru
     FROM guru'
    . $whereSql .
    ' ORDER BY nama_guru ASC, id_guru ASC
      LIMIT :limit OFFSET :offset'
);

foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$daftarGuru = $stmt->fetchAll();

$pageTitle = 'Data Guru';
$activePage = 'guru';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Data Guru</h1>
        <p>Kelola data guru yang terdaftar dalam sistem infaq.</p>
    </div>

    <div class="heading-actions">
        <a href="tambah.php" class="btn btn-primary">
            + Tambah Guru
        </a>
    </div>
</div>

<div class="summary-grid">
    <div class="stat-card green">
        <div class="stat-top">
            <span class="stat-label">Total Guru</span>
            <span class="stat-icon">♙</span>
        </div>

        <strong class="stat-value">
            <?= number_format($total, 0, ',', '.') ?>
        </strong>

        <span class="stat-description">
            Guru terdaftar dalam sistem
        </span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h2>Daftar Guru</h2>
            <p class="text-muted mt-1">
                Total <?= number_format($total, 0, ',', '.') ?> guru.
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
                    placeholder="Cari nama guru..."
                >

                <button class="btn btn-primary" type="submit">
                    Cari
                </button>

                <?php if ($q !== ''): ?>
                    <a href="index.php" class="btn btn-secondary">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="table table-center">
            <thead>
                <tr text-align="center">
                    <th>No.</th>
                    <th>Nama Guru</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($daftarGuru)): ?>
                    <tr>
                        <td colspan="3" class="table-empty">
                            Belum ada data guru yang cocok.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarGuru as $index => $row): ?>
                        <tr text-align="center">
                            <td><?= $offset + $index + 1 ?></td>

                            <td>
                                <strong><?= e($row['nama_guru']) ?></strong>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="edit.php?id=<?= (int) $row['id_guru'] ?>"
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
                                            name="id_guru"
                                            value="<?= (int) $row['id_guru'] ?>"
                                        >
                                        <button
                                            class="btn btn-outline-danger btn-sm"
                                            type="submit"
                                            data-confirm="Hapus data guru <?= e($row['nama_guru']) ?>?"
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
                            'page' => $i,
                        ])) ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= e(http_build_query([
                        'q' => $q,
                        'page' => $page + 1,
                    ])) ?>">
                        &raquo;
                    </a>
                <?php endif; ?>
            </div>
        </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

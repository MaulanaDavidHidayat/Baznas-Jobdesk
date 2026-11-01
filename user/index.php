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
    $where[] = '(nama LIKE :q_nama OR username LIKE :q_user)';
    $params['q_nama'] = '%' . $q . '%';
    $params['q_user'] = '%' . $q . '%';
}

$whereSql = $where !== []
    ? ' WHERE ' . implode(' AND ', $where)
    : '';

$countStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM users' . $whereSql
);
$countStmt->execute($params);

$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    'SELECT id_user, nama, username, role
     FROM users'
    . $whereSql .
    ' ORDER BY nama ASC, id_user ASC
      LIMIT :limit OFFSET :offset'
);

foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$daftarUser = $stmt->fetchAll();
$currentId = (int) ($_SESSION['user']['id_user'] ?? 0);

$pageTitle = 'Data Admin';
$activePage = 'user';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Data Admin</h1>
        <p>Kelola akun administrator sistem infak BAZNAS.</p>
    </div>

    <div class="heading-actions">
        <a href="tambah.php" class="btn btn-primary">
            + Tambah Admin
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h2>Daftar Admin</h2>
            <p class="text-muted mt-1">
                Total <?= number_format($total, 0, ',', '.') ?> akun.
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
                    placeholder="Cari nama atau username..."
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
        <table class="table">
            <thead>
                <tr text-align="center">
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($daftarUser)): ?>
                    <tr text-align="center">
                        <td colspan="6" class="table-empty">
                            Belum ada data admin yang cocok.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarUser as $index => $row): ?>
                        <tr>
                            <td><?= $offset + $index + 1 ?></td>

                            <td>
                                <strong><?= e($row['nama']) ?></strong>
                            </td>

                            <td><strong><?= e($row['username']) ?></strong></td>

                            <td>
                                <span class="badge badge-info">
                                    <?= e(role_label((string) $row['role'])) ?>
                                </span>
                            </td>

                            <td>
                                <?php if ((int) $row['id_user'] === $currentId): ?>
                                    <span class="badge badge-success">
                                        Akun Anda
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">
                                        Administrator
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="edit.php?id=<?= (int) $row['id_user'] ?>"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        Edit
                                    </a>

                                    <?php if ((int) $row['id_user'] !== $currentId): ?>
                                        <form method="POST" action="hapus.php">
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e(csrf_token()) ?>"
                                            >
                                            <input
                                                type="hidden"
                                                name="id_user"
                                                value="<?= (int) $row['id_user'] ?>"
                                            >
                                            <button
                                                class="btn btn-outline-danger btn-sm"
                                                type="submit"
                                                data-confirm="Hapus akun admin <?= e($row['nama']) ?>?"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    <?php endif; ?>
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

<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$activePage = $activePage ?? '';
$basePath = $basePath ?? '';

$flashMessage = get_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#005030">

    <title><?= e($pageTitle) ?> · BAZNAS</title>

    <link
        rel="icon"
        type="image/png"
        href="<?= $basePath ?>assets/favicon.png"
    >
    <link
        rel="apple-touch-icon"
        href="<?= $basePath ?>assets/favicon.png"
    >

    <link
        rel="stylesheet"
        href="<?= $basePath ?>assets/css/style.css"
    >

    <script
        src="<?= $basePath ?>assets/js/app.js"
        defer
    ></script>
</head>

<body>
<div class="app-shell">

    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?= $basePath ?>dashboard.php">
            <img
                class="brand-logo"
                src="<?= $basePath ?>assets/favicon.png"
                alt="Logo BAZNAS"
            >

            <span>
                <strong>BAZNAS</strong>
                <small>Badan Amil Zakat Nasional</small>
            </span>
        </a>

        <div class="sidebar-label">MENU UTAMA</div>

        <nav class="nav-menu">
            <a
                class="<?= $activePage === 'dashboard' ? 'active' : '' ?>"
                href="<?= $basePath ?>dashboard.php"
            >
                <span class="nav-icon">⌂</span>
                Dashboard
            </a>

            <a
                class="<?= $activePage === 'guru' ? 'active' : '' ?>"
                href="<?= $basePath ?>guru/index.php"
            >
                <span class="nav-icon">♙</span>
                Data Guru
            </a>

            <a
                class="<?= $activePage === 'infaq' ? 'active' : '' ?>"
                href="<?= $basePath ?>infaq/index.php"
            >
                <span class="nav-icon">＋</span>
                Data Infaq
            </a>

            <a
                class="<?= $activePage === 'pengeluaran' ? 'active' : '' ?>"
                href="<?= $basePath ?>pengeluaran/index.php"
            >
                <span class="nav-icon">↗</span>
                Data Pengeluaran
            </a>

            <a
                class="<?= $activePage === 'user' ? 'active' : '' ?>"
                href="<?= $basePath ?>user/index.php"
            >
                <span class="nav-icon">♙</span>
                Data Admin
            </a>

            <a
                class="<?= $activePage === 'laporan' ? 'active' : '' ?>"
                href="<?= $basePath ?>laporan.php"
            >
                <span class="nav-icon">▤</span>
                Laporan
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-note">
                <span class="note-icon">✓</span>

                <div>
                    <strong>Kelola dengan aman</strong>
                    <small>
                        Catat setiap transaksi dengan teliti.
                    </small>
                </div>
            </div>

            <form
                class="logout-form"
                method="POST"
                action="<?= $basePath ?>logout.php"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <button
                    class="nav-logout"
                    type="submit"
                    data-confirm="Yakin ingin keluar dari akun?"
                >
                    <span class="nav-icon">⇥</span>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <button
                class="menu-toggle"
                type="button"
                data-sidebar-toggle
                aria-label="Buka menu"
            >
                ☰
            </button>

            <div class="breadcrumb">
                <span>BAZNAS</span>
                <span class="crumb-separator">/</span>
                <strong><?= e($pageTitle) ?></strong>
            </div>

            <div class="user-menu">
                <div class="avatar">
                    <?= e(strtoupper(substr(current_user_name(), 0, 1))) ?>
                </div>

                <div>
                    <strong><?= e(current_user_name()) ?></strong>
                    <small><?= e(role_label(current_user_role())) ?></small>
                </div>
            </div>
        </header>

        <section class="page-content">
            <?php if ($flashMessage): ?>
                <div
                    class="alert alert-<?= e($flashMessage['type']) ?>"
                    role="status"
                >
                    <?= e($flashMessage['message']) ?>
                </div>
            <?php endif; ?>
<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

function rupiah(float|int|string|null $value): string
{
    return 'Rp ' . number_format(
        (float) ($value ?? 0),
        0,
        ',',
        '.'
    );
}

function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals(
            $_SESSION['csrf_token'] ?? '',
            $token
        )
    ) {
        http_response_code(419);

        exit(
            'Permintaan tidak valid atau sesi formulir '
            . 'sudah berakhir. Silakan kembali dan coba lagi.'
        );
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;

    unset($_SESSION['flash']);

    return $flash;
}

function require_login(): void
{
    if (empty($_SESSION['user'])) {
        $scriptDir = dirname(
            $_SERVER['SCRIPT_NAME'] ?? '/'
        );

        $modules = [
            'guru',
            'infaq',
            'pengeluaran',
            'user',
        ];

        if (
            in_array(
                basename($scriptDir),
                $modules,
                true
            )
        ) {
            $scriptDir = dirname($scriptDir);
        }

        redirect(
            rtrim($scriptDir, '/') . '/login.php'
        );
    }
}

function current_user_name(): string
{
    return $_SESSION['user']['nama'] ?? 'Admin';
}

function current_user_role(): string
{
    return $_SESSION['user']['role'] ?? 'admin';
}

function available_roles(): array
{
    return ['admin'];
}

function role_label(string $role): string
{
    return $role === '' ? '-' : ucfirst($role);
}

function daftar_bulan(): array
{
    return [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
}

function filter_bulan_dipilih(string $default = 'sekarang'): string
{
    $default = $default === 'sekarang'
        ? (string) date('n')
        : $default;

    $bulan = $_GET['bulan'] ?? $default;

    if (!is_string($bulan)) {
        return $default;
    }

    if ($bulan === 'semua') {
        return 'semua';
    }

    $angka = (int) $bulan;

    if (
        $angka >= 1 &&
        $angka <= 12 &&
        (string) $angka === $bulan
    ) {
        return $bulan;
    }

    return $default;
}

function label_periode_bulan(string $bulan): string
{
    if ($bulan === 'semua') {
        return 'Semua Bulan';
    }

    $daftar = daftar_bulan();
    $angka = (int) $bulan;

    return ($daftar[$angka] ?? '-') . ' ' . date('Y');
}

function where_bulan(
    string $bulan,
    string $kolom,
    array &$params,
    string $prefix = 'bulan'
): ?string {
    if ($bulan === 'semua') {
        return null;
    }

    $awal = sprintf(
        '%04d-%02d-01',
        (int) date('Y'),
        (int) $bulan
    );

    $params[$prefix . '_awal'] = $awal;
    $params[$prefix . '_akhir'] = date(
        'Y-m-t',
        strtotime($awal)
    );

    return "{$kolom} BETWEEN :{$prefix}_awal"
        . " AND :{$prefix}_akhir";
}

function batas_bulan_ini(): array
{
    return [date('Y-m-01'), date('Y-m-t')];
}

function tanggal_di_bulan_ini(string $tanggal): bool
{
    return substr($tanggal, 0, 7) === date('Y-m');
}
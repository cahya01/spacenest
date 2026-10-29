<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? 'mahasiswa';
    header('Location: ' . $role . '/dashboard.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$notice = $_SESSION['auth_notice'] ?? '';
unset($_SESSION['auth_notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    $allowedRoles = ['mahasiswa', 'dosen', 'admin', 'superadmin'];

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } elseif ($username === '' || $password === '' || !in_array($role, $allowedRoles, true)) {
        $error = 'Masukkan nama pengguna, kata sandi, dan jenis akses yang valid.';
    } else {
        try {
            $connection = getDbConnection();
            $statement = $connection->prepare('SELECT id, username, password, role FROM users WHERE username = ? AND role = ? LIMIT 1');
            $statement->bind_param('ss', $username, $role);
            $statement->execute();
            $account = $statement->get_result()->fetch_assoc();
            $statement->close();

            if (!$account) {
                $error = 'Nama pengguna atau kata sandi tidak sesuai.';
            } else {
                $storedPassword = (string) $account['password'];
                $passwordInfo = password_get_info($storedPassword);
                $isHashedPassword = $passwordInfo['algo'] !== null;
                $passwordMatches = $isHashedPassword
                    ? password_verify($password, $storedPassword)
                    : hash_equals($storedPassword, $password);

                if (!$passwordMatches) {
                    $error = 'Nama pengguna atau kata sandi tidak sesuai.';
                } else {
                    if (!$isHashedPassword || password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $update = $connection->prepare('UPDATE users SET password = ? WHERE id = ?');
                        $accountId = (int) $account['id'];
                        $update->bind_param('si', $newHash, $accountId);
                        $update->execute();
                        $update->close();
                    }

                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'username' => $account['username'],
                        'role' => $account['role'],
                    ];
                    header('Location: ' . $account['role'] . '/dashboard.php');
                    exit;
                }
            }

            $connection->close();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            error_log('SIPAK login error: ' . $exception->getMessage());
            $error = 'Layanan masuk sedang mengalami kendala. Silakan coba kembali beberapa saat lagi.';
        }
    }
}

function loginEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIPAK</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body class="login-page">
    <main class="login-layout">
        <section class="login-welcome" aria-labelledby="welcome-title">
            <a class="login-brand" href="index.php">
                <img src="assets/img/WhatsApp%20Image%202026-10-16%20at%2020.53.26.jpeg" alt="">
                <span class="login-brand-name">SIPAK</span>
                <span class="login-brand-divider"></span>
                <span class="login-brand-caption">Sistem Informasi Pengajuan<br>Akademik Kampus</span>
            </a>

            <div class="welcome-copy">
                <p class="welcome-eyebrow">UNIVERSITAS MUHAMMADIYAH INDONESIA</p>
                <h1 id="welcome-title">Selamat Datang di <strong>SIPAK</strong></h1>
                <p class="welcome-description">Solusi mudah untuk berbagai layanan pengajuan akademik dan administrasi mahasiswa.</p>

                <ul class="welcome-benefits">
                    <li><span aria-hidden="true">▤</span> Mudah<br>Digunakan</li>
                    <li><span aria-hidden="true">◇</span> Aman &amp;<br>Terpercaya</li>
                    <li><span aria-hidden="true">◷</span> Proses Lebih<br>Cepat</li>
                </ul>
            </div>

            <p class="welcome-quote">“Bersama Mewujudkan Layanan Akademik<br>yang Lebih Baik”</p>
        </section>

        <section class="login-panel" aria-labelledby="login-title">
            <div class="login-card">
                <img class="login-card-logo" src="assets/img/WhatsApp%20Image%202026-10-16%20at%2020.53.26.jpeg" alt="Logo Universitas Muhammadiyah Indonesia">
                <h2 id="login-title">SIPAK</h2>
                <p class="login-card-subtitle">Sistem Informasi Pengajuan<br>Akademik Kampus</p>
                <p class="login-instruction">Silakan masuk ke akun Anda untuk melanjutkan</p>

                <?php if ($notice !== ''): ?>
                    <p class="auth-feedback success" role="status"><?= loginEscape($notice) ?></p>
                <?php endif; ?>
                <?php if ($error !== ''): ?>
                    <p class="auth-feedback error" role="alert"><?= loginEscape($error) ?></p>
                <?php endif; ?>

                <form method="POST" action="login.php" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= loginEscape($_SESSION['csrf_token']) ?>">
                    <label class="login-input-wrap" for="username">
                        <span aria-hidden="true">♙</span>
                        <input type="text" id="username" name="username" placeholder="NIM / Username / Email" autocomplete="username" value="<?= loginEscape($_POST['username'] ?? '') ?>" required>
                    </label>

                    <label class="login-input-wrap" for="password">
                        <span aria-hidden="true">▣</span>
                        <input type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" required>
                    </label>

                    <button class="login-submit" type="submit">Masuk <span aria-hidden="true">→</span></button>

                    <div class="role-divider"><span>atau masuk sebagai</span></div>
                    <fieldset class="role-picker">
                        <legend class="visually-hidden">Pilih jenis akses</legend>
                        <label class="role-option">
                            <input type="radio" name="role" value="mahasiswa" <?= ($_POST['role'] ?? 'mahasiswa') === 'mahasiswa' ? 'checked' : '' ?>>
                            <span class="role-option-content"><span aria-hidden="true">♧</span>Mahasiswa</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="dosen" <?= ($_POST['role'] ?? '') === 'dosen' ? 'checked' : '' ?>>
                            <span class="role-option-content"><span aria-hidden="true">♙</span>Dosen</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'checked' : '' ?>>
                            <span class="role-option-content"><span aria-hidden="true">⚙</span>Admin</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="superadmin" <?= ($_POST['role'] ?? '') === 'superadmin' ? 'checked' : '' ?>>
                            <span class="role-option-content"><span aria-hidden="true">⬡</span>Superadmin</span>
                        </label>
                    </fieldset>
                </form>
                <p class="auth-switch">Belum punya akun mahasiswa? <a href="daftar.php">Daftar sekarang</a></p>
            </div>
        </section>
    </main>
</body>
</html>

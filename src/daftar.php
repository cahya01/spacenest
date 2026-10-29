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
$nim = '';
$fakultas = '';
$jurusan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $fakultas = trim($_POST['fakultas'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } elseif (!preg_match('/^[A-Za-z0-9-]{4,30}$/D', $nim)) {
        $error = 'NIM harus terdiri dari 4–30 karakter berupa huruf, angka, atau tanda hubung.';
    } elseif ($fakultas === '' || strlen($fakultas) > 600) {
        $error = 'Silakan isi nama fakultas dengan benar (maksimal 150 karakter).';
    } elseif ($jurusan === '' || strlen($jurusan) > 600) {
        $error = 'Silakan isi nama jurusan/program studi dengan benar (maksimal 150 karakter).';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'Kata sandi harus memiliki panjang 8–72 karakter.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'Konfirmasi kata sandi tidak sama.';
    } else {
        try {
            $connection = getDbConnection();
            $check = $connection->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $check->bind_param('s', $nim);
            $check->execute();
            $existingAccount = $check->get_result()->fetch_assoc();
            $check->close();

            if ($existingAccount) {
                $error = 'NIM tersebut sudah terdaftar. Silakan masuk atau gunakan NIM yang lain.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'mahasiswa';
                $insert = $connection->prepare('INSERT INTO users (username, password, role, fakultas, jurusan) VALUES (?, ?, ?, ?, ?)');
                $insert->bind_param('sssss', $nim, $passwordHash, $role, $fakultas, $jurusan);
                $insert->execute();
                $insert->close();
                $connection->close();

                $_SESSION['auth_notice'] = 'Pendaftaran berhasil. Silakan masuk menggunakan NIM dan kata sandi Anda.';
                header('Location: login.php');
                exit;
            }

            $connection->close();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            error_log('SPACENEST student registration error: ' . $exception->getMessage());
            $error = 'Pendaftaran belum dapat diproses. Pastikan MySQL berjalan dan database yang dikonfigurasi sudah dibuat, lalu coba kembali.';
        }
    }
}

function registrationEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mahasiswa | SPACENEST</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body class="login-page">
    <main class="login-layout">
        <section class="login-welcome" aria-labelledby="welcome-title">
            <a class="login-brand" href="index.php">
                <img src="assets/img/WhatsApp%20Image%202026-10-16%20at%2020.53.26.jpeg" alt="">
                <span class="login-brand-name">SPACENEST</span>
                <span class="login-brand-divider"></span>
                <span class="login-brand-caption">SPACENEST · System for Faculty Space & Resource Management</span>
            </a>

            <div class="welcome-copy">
                <p class="welcome-eyebrow">AKUN KHUSUS MAHASISWA</p>
                <h1 id="welcome-title">Mulai Gunakan <strong>SPACENEST</strong></h1>
                <p class="welcome-description">Buat akun mahasiswa untuk mengajukan layanan akademik dan memantau prosesnya melalui satu portal.</p>

                <ul class="welcome-benefits">
                    <li><span aria-hidden="true">▤</span> Pendaftaran<br>Mudah</li>
                    <li><span aria-hidden="true">◇</span> Data Akun<br>Terlindungi</li>
                    <li><span aria-hidden="true">◷</span> Pantau<br>Pengajuan</li>
                </ul>
            </div>

            <p class="welcome-quote">“Bersama Mewujudkan Layanan Akademik<br>yang Lebih Baik”</p>
        </section>

        <section class="login-panel" aria-labelledby="register-title">
            <div class="login-card register-card">
                <img class="login-card-logo" src="assets/img/WhatsApp%20Image%202026-10-16%20at%2020.53.26.jpeg" alt="Logo Universitas Muhammadiyah Indonesia">
                <h2 id="register-title">Daftar Mahasiswa</h2>
                <p class="login-card-subtitle">Buat akun SPACENEST untuk mulai melaporkan dan memantau fasilitas</p>
                <p class="login-instruction">Pendaftaran hanya tersedia untuk mahasiswa</p>

                <?php if ($error !== ''): ?>
                    <p class="auth-feedback error" role="alert"><?= registrationEscape($error) ?></p>
                <?php endif; ?>

                <form method="POST" action="daftar.php" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= registrationEscape($_SESSION['csrf_token']) ?>">
                    <label class="login-input-wrap" for="nim">
                        <span aria-hidden="true">♙</span>
                        <input type="text" id="nim" name="nim" placeholder="NIM" autocomplete="username" inputmode="text" pattern="[A-Za-z0-9\-]{4,30}" minlength="4" maxlength="30" value="<?= registrationEscape($nim) ?>" required>
                    </label>

                    <label class="login-input-wrap" for="fakultas">
                        <span aria-hidden="true">▤</span>
                        <input type="text" id="fakultas" name="fakultas" placeholder="Fakultas" autocomplete="organization" maxlength="150" value="<?= registrationEscape($fakultas) ?>" required>
                    </label>

                    <label class="login-input-wrap" for="jurusan">
                        <span aria-hidden="true">▧</span>
                        <input type="text" id="jurusan" name="jurusan" placeholder="Jurusan / Program Studi" maxlength="150" value="<?= registrationEscape($jurusan) ?>" required>
                    </label>

                    <label class="login-input-wrap" for="password">
                        <span aria-hidden="true">▣</span>
                        <input type="password" id="password" name="password" placeholder="Kata sandi (minimal 8 karakter)" autocomplete="new-password" minlength="8" maxlength="72" required>
                    </label>

                    <label class="login-input-wrap" for="password_confirmation">
                        <span aria-hidden="true">▣</span>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi" autocomplete="new-password" minlength="8" maxlength="72" required>
                    </label>

                    <button class="login-submit" type="submit">Daftar sebagai Mahasiswa <span aria-hidden="true">→</span></button>
                </form>
                <p class="auth-switch">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
            </div>
        </section>
    </main>
</body>
</html>

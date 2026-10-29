<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['mahasiswa']);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/dashboard_layout.php';

$username = $_SESSION['user']['username'] ?? '';
$profile = null;
$profileError = '';

try {
    $connection = getDbConnection();
    $role = 'mahasiswa';
    $statement = $connection->prepare('SELECT username, fakultas, jurusan FROM users WHERE username = ? AND role = ? LIMIT 1');
    $statement->bind_param('ss', $username, $role);
    $statement->execute();
    $profile = $statement->get_result()->fetch_assoc();
    $statement->close();
    $connection->close();

    if (!$profile) {
        $profileError = 'Data profil mahasiswa tidak ditemukan.';
    }
} catch (mysqli_sql_exception | RuntimeException $exception) {
    error_log('SIPAK student profile error: ' . $exception->getMessage());
    $profileError = 'Data profil belum dapat dimuat. Silakan coba kembali beberapa saat lagi.';
}

dashboardRenderStart('mahasiswa', $username, 'Profil Mahasiswa', 'Cari profil atau layanan...', $profileError);
?>
<section class="dashboard-page-heading profile-page-heading">
    <div>
        <span class="panel-kicker">AKUN MAHASISWA</span>
        <h1>Profil Saya</h1>
        <p>Periksa NIM, fakultas, dan program studi yang terdaftar di akun SIPAK Anda.</p>
    </div>
</section>

<?php if ($profile !== null): ?>
    <section class="profile-overview" aria-label="Ringkasan akun mahasiswa">
        <div class="profile-avatar" aria-hidden="true"><?php echo dashboardEscape(strtoupper(substr($profile['username'], 0, 1))); ?></div>
        <div class="profile-overview-copy">
            <span>AKUN MAHASISWA</span>
            <h2><?php echo dashboardEscape($profile['username']); ?></h2>
            <p>Data akademik yang Anda masukkan saat mendaftar.</p>
        </div>
        <span class="profile-status"><span aria-hidden="true">✓</span> Terdaftar</span>
    </section>

    <section class="dashboard-panel profile-details-panel" aria-labelledby="profile-details-title">
        <div class="dashboard-panel-heading">
            <div>
                <span class="panel-kicker">INFORMASI AKUN</span>
                <h2 id="profile-details-title">Data Akademik</h2>
            </div>
            <span class="profile-readonly-label">Data akun</span>
        </div>

        <dl class="profile-details">
            <div class="profile-detail-item">
                <dt><span class="profile-detail-icon" aria-hidden="true">♙</span>NIM</dt>
                <dd><?php echo dashboardEscape($profile['username']); ?></dd>
            </div>
            <div class="profile-detail-item">
                <dt><span class="profile-detail-icon" aria-hidden="true">▤</span>Fakultas</dt>
                <dd><?php echo dashboardEscape($profile['fakultas'] ?: 'Belum diisi'); ?></dd>
            </div>
            <div class="profile-detail-item">
                <dt><span class="profile-detail-icon" aria-hidden="true">▧</span>Jurusan / Program Studi</dt>
                <dd><?php echo dashboardEscape($profile['jurusan'] ?: 'Belum diisi'); ?></dd>
            </div>
        </dl>

        <p class="profile-help"><span aria-hidden="true">ⓘ</span> Pastikan fakultas dan program studi yang tertera sudah sesuai. Jika ada data yang perlu diperbaiki, hubungi admin atau bagian akademik kampus.</p>
    </section>
<?php endif; ?>

<?php dashboardRenderEnd(); ?>

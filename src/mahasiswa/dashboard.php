<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['mahasiswa']);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/dashboard_layout.php';

$username = $_SESSION['user']['username'] ?? 'Mahasiswa';
$data = dashboardLoadDataSafely('mahasiswa', $username);
dashboardRenderStart('mahasiswa', $username, 'Dashboard Mahasiswa', 'Cari pengajuan, layanan, atau informasi...', $data['error'] ?? '');
?>
<section class="dashboard-welcome student-welcome">
    <div>
        <span class="dashboard-eyebrow">SPACENEST</span>
        <h1>Selamat datang, <?php echo dashboardEscape($username); ?>!</h1>
        <p>Pantau laporan fasilitas dan temukan informasi yang dibutuhkan dengan lebih mudah.</p>
        <a class="dashboard-primary-link" href="buat_pengajuan.php">Buat Pengajuan <span aria-hidden="true">→</span></a>
    </div>
    <div class="welcome-decoration" aria-hidden="true">
        <img src="../assets/img/logo-umindonesia.svg" alt="">
    </div>
</section>

<section class="dashboard-metrics" aria-label="Ringkasan pengajuan">
    <?php
    dashboardMetricCard('Total Pengajuan', $data['summary']['total'], '▤', 'metric-blue', 'Pengajuan Anda');
    dashboardMetricCard('Diajukan', $data['summary']['diajukan'], '◷', 'metric-yellow', 'Menunggu diproses');
    dashboardMetricCard('Disetujui', $data['summary']['disetujui'], '✓', 'metric-green', 'Pengajuan selesai');
    dashboardMetricCard('Ditolak', $data['summary']['ditolak'], '!', 'metric-red', 'Perlu ditinjau');
    ?>
</section>

<div class="dashboard-columns student-columns">
    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div><span class="panel-kicker">LAYANAN</span><h2>Layanan Pengajuan</h2></div>
            <a href="buat_pengajuan.php">Semua layanan <span aria-hidden="true">→</span></a>
        </div>
        <div class="service-shortcuts">
            <a href="buat_pengajuan.php" class="service-shortcut"><span class="shortcut-icon shortcut-blue">▤</span><span><strong>Pengajuan Akademik</strong><small>Buat pengajuan baru</small></span><b aria-hidden="true">›</b></a>
            <a href="riwayat_pengajuan.php" class="service-shortcut"><span class="shortcut-icon shortcut-purple">◷</span><span><strong>Riwayat Pengajuan</strong><small>Lihat status pengajuan Anda</small></span><b aria-hidden="true">›</b></a>
            <a href="profil.php" class="service-shortcut"><span class="shortcut-icon shortcut-green">♙</span><span><strong>Profil Saya</strong><small>Periksa data akun Anda</small></span><b aria-hidden="true">›</b></a>
        </div>
    </section>

    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div><span class="panel-kicker">AKTIVITAS</span><h2>Pengajuan Terbaru</h2></div>
            <a href="riwayat_pengajuan.php">Lihat riwayat <span aria-hidden="true">→</span></a>
        </div>
        <?php dashboardRecentTable($data['recent'], false); ?>
    </section>
</div>
<?php dashboardRenderEnd(); ?>

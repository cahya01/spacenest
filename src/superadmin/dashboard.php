<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['superadmin']);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/dashboard_layout.php';

$username = $_SESSION['user']['username'] ?? 'Superadmin';
$data = dashboardLoadDataSafely('superadmin', $username);
dashboardRenderStart('superadmin', $username, 'Dashboard Superadmin', 'Cari pengguna, role, atau pengaturan...', $data['error'] ?? '');
?>
<div class="dashboard-page-heading">
    <div><span class="panel-kicker">KONTROL SISTEM</span><h1>Dashboard Superadmin</h1><p>Kelola pengguna dan pantau aktivitas aplikasi SIPAK.</p></div>
    <span class="dashboard-date desktop-date"><?php echo dashboardEscape(date('l, d F Y')); ?></span>
</div>

<section class="dashboard-metrics" aria-label="Ringkasan sistem">
    <?php
    dashboardMetricCard('Total Pengguna', $data['users']['total'], '♙', 'metric-blue', 'Akun terdaftar');
    dashboardMetricCard('Mahasiswa', $data['users']['mahasiswa'], '▣', 'metric-green', 'Akun mahasiswa');
    dashboardMetricCard('Dosen', $data['users']['dosen'], '♧', 'metric-purple', 'Akun dosen');
    dashboardMetricCard('Admin', $data['users']['admin'], '⚙', 'metric-yellow', 'Akun admin');
    ?>
</section>

<div class="dashboard-columns superadmin-columns">
    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div><span class="panel-kicker">AKTIVITAS SISTEM</span><h2>Status Pengajuan</h2></div>
        </div>
        <div class="system-total">
            <span class="system-total-icon">▤</span>
            <span><strong><?php echo dashboardEscape($data['summary']['total']); ?></strong><small>Total pengajuan tercatat</small></span>
        </div>
        <div class="system-status-list">
            <?php
            $systemStatuses = [
                ['Diajukan', $data['summary']['diajukan'], 'status-dot-blue'],
                ['Diproses', $data['summary']['diproses'], 'status-dot-yellow'],
                ['Disetujui', $data['summary']['disetujui'], 'status-dot-green'],
                ['Ditolak', $data['summary']['ditolak'], 'status-dot-red'],
            ];
            foreach ($systemStatuses as $status):
            ?>
                <div><span><i class="<?php echo dashboardEscape($status[2]); ?>"></i><?php echo dashboardEscape($status[0]); ?></span><strong><?php echo dashboardEscape($status[1]); ?></strong></div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div><span class="panel-kicker">MANAJEMEN</span><h2>Akses Cepat</h2></div>
        </div>
        <div class="admin-quick-links superadmin-quick-links">
            <a href="pengguna.php"><span class="quick-link-icon shortcut-blue">♙</span><span><strong>Kelola Pengguna</strong><small>Tambah dan tinjau akun pengguna</small></span><b aria-hidden="true">›</b></a>
            <a href="role.php"><span class="quick-link-icon shortcut-purple">◇</span><span><strong>Kelola Role</strong><small>Atur hak akses aplikasi</small></span><b aria-hidden="true">›</b></a>
            <a href="pengaturan.php"><span class="quick-link-icon shortcut-yellow">⚙</span><span><strong>Pengaturan Sistem</strong><small>Konfigurasi aplikasi SIPAK</small></span><b aria-hidden="true">›</b></a>
        </div>
    </section>
</div>

<section class="dashboard-panel dashboard-recent-panel">
    <div class="dashboard-panel-heading">
        <div><span class="panel-kicker">PEMANTAUAN</span><h2>Pengajuan Terbaru</h2></div>
    </div>
    <?php dashboardRecentTable($data['recent'], true); ?>
</section>
<?php dashboardRenderEnd(); ?>

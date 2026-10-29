<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['dosen']);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/dashboard_layout.php';

$username = $_SESSION['user']['username'] ?? 'Dosen';
$data = dashboardLoadDataSafely('dosen', $username);
$pending = $data['summary']['diajukan'] + $data['summary']['diproses'];
dashboardRenderStart('dosen', $username, 'Dashboard Dosen', 'Cari pengajuan, nama mahasiswa, atau NIM...', $data['error'] ?? '');
?>
<div class="dashboard-page-heading">
    <div><span class="panel-kicker">RUANG KERJA DOSEN</span><h1>Dashboard Dosen</h1><p>Kelola dan verifikasi pengajuan mahasiswa dengan mudah.</p></div>
    <span class="dashboard-date desktop-date"><?php echo dashboardEscape(date('l, d F Y')); ?></span>
</div>

<section class="dashboard-metrics" aria-label="Ringkasan pengajuan">
    <?php
    dashboardMetricCard('Total Pengajuan', $data['summary']['total'], '▤', 'metric-blue', 'Seluruh pengajuan');
    dashboardMetricCard('Menunggu Verifikasi', $pending, '◷', 'metric-yellow', 'Perlu ditindaklanjuti');
    dashboardMetricCard('Disetujui', $data['summary']['disetujui'], '✓', 'metric-green', 'Sudah diverifikasi');
    dashboardMetricCard('Ditolak', $data['summary']['ditolak'], '!', 'metric-red', 'Perlu ditinjau');
    ?>
</section>

<div class="dashboard-columns lecturer-columns">
    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div><span class="panel-kicker">RINGKASAN</span><h2>Status Pengajuan</h2></div>
        </div>
        <div class="status-overview">
            <?php
            $statusMetrics = [
                ['Diajukan', $data['summary']['diajukan'], 'status-bar-blue'],
                ['Diproses', $data['summary']['diproses'], 'status-bar-yellow'],
                ['Disetujui', $data['summary']['disetujui'], 'status-bar-green'],
                ['Ditolak', $data['summary']['ditolak'], 'status-bar-red'],
            ];
            foreach ($statusMetrics as $metric):
                $percent = $data['summary']['total'] > 0 ? (int) round($metric[1] / $data['summary']['total'] * 100) : 0;
            ?>
                <div class="status-overview-row">
                    <div><span><?php echo dashboardEscape($metric[0]); ?></span><strong><?php echo dashboardEscape($metric[1]); ?></strong></div>
                    <div class="status-track"><span class="<?php echo dashboardEscape($metric[2]); ?>" style="width: <?php echo $percent; ?>%"></span></div>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="panel-action-link" href="daftar_pengajuan.php">Buka daftar pengajuan <span aria-hidden="true">→</span></a>
    </section>

    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div><span class="panel-kicker">TINDAKAN</span><h2>Perlu Verifikasi</h2></div>
            <a href="verifikasi.php">Lihat semua <span aria-hidden="true">→</span></a>
        </div>
        <?php
        $pendingRows = array_values(array_filter($data['recent'], function ($row) {
            return in_array($row['status'], ['diajukan', 'diproses'], true);
        }));
        dashboardRecentTable(array_slice($pendingRows, 0, 5), true);
        ?>
    </section>
</div>
<?php dashboardRenderEnd(); ?>

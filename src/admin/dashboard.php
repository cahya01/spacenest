<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['admin']);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/dashboard_layout.php';

$username = $_SESSION['user']['username'] ?? 'Admin';
$data = dashboardLoadDataSafely('admin', $username);
dashboardRenderStart('admin', $username, 'Dashboard Admin', 'Cari pengajuan, mahasiswa, atau layanan...', $data['error'] ?? '');
?>
<div class="dashboard-page-heading">
    <div><span class="panel-kicker">PUSAT OPERASIONAL SPACENEST</span><h1>Dashboard Admin</h1><p>Pantau laporan dan kelola fasilitas dalam satu tempat.</p></div>
    <span class="dashboard-date desktop-date"><?php echo dashboardEscape(date('l, d F Y')); ?></span>
</div>

<section class="dashboard-metrics" aria-label="Ringkasan operasional">
    <?php
    dashboardMetricCard('Total Pengajuan', $data['summary']['total'], '▤', 'metric-blue', 'Seluruh pengajuan');
    dashboardMetricCard('Total Pengguna', $data['users']['total'], '♙', 'metric-yellow', 'Akun terdaftar');
    dashboardMetricCard('Disetujui', $data['summary']['disetujui'], '✓', 'metric-green', 'Pengajuan selesai');
    dashboardMetricCard('Menunggu Proses', $data['summary']['diajukan'] + $data['summary']['diproses'], '◷', 'metric-red', 'Perlu ditindaklanjuti');
    ?>
</section>

<div class="dashboard-columns admin-columns">
    <section class="dashboard-panel category-panel">
        <div class="dashboard-panel-heading">
            <div><span class="panel-kicker">ANALITIK</span><h2>Pengajuan per Kategori</h2></div>
            <a href="laporan.php">Lihat laporan <span aria-hidden="true">→</span></a>
        </div>
        <?php if (!$data['categories']): ?>
            <p class="dashboard-empty-message">Data kategori pengajuan akan tampil di sini setelah ada pengajuan.</p>
        <?php else: ?>
            <div class="category-bars">
                <?php foreach ($data['categories'] as $category):
                    $maxCategory = max(array_column($data['categories'], 'total'));
                    $width = $maxCategory > 0 ? (int) round((int) $category['total'] / $maxCategory * 100) : 0;
                ?>
                    <div class="category-bar-row">
                        <div><span><?php echo dashboardEscape($category['kategori']); ?></span><strong><?php echo dashboardEscape($category['total']); ?></strong></div>
                        <div class="category-track"><span style="width: <?php echo $width; ?>%"></span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="admin-quick-links">
            <a href="kelola_pengajuan.php"><span class="quick-link-icon shortcut-blue">▤</span><span><strong>Kelola Pengajuan</strong><small>Periksa dan perbarui status</small></span><b aria-hidden="true">›</b></a>
            <a href="kelola_pengguna.php"><span class="quick-link-icon shortcut-purple">♙</span><span><strong>Kelola Pengguna</strong><small>Atur akun pengguna</small></span><b aria-hidden="true">›</b></a>
            <a href="kelola_kategori.php"><span class="quick-link-icon shortcut-green">▦</span><span><strong>Kelola Kategori</strong><small>Atur jenis layanan</small></span><b aria-hidden="true">›</b></a>
        </div>
    </section>

    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div><span class="panel-kicker">AKTIVITAS</span><h2>Pengajuan Terbaru</h2></div>
            <a href="kelola_pengajuan.php">Lihat semua <span aria-hidden="true">→</span></a>
        </div>
        <?php dashboardRecentTable($data['recent'], true); ?>
    </section>
</div>
<?php dashboardRenderEnd(); ?>

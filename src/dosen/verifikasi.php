<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['dosen']);

require_once __DIR__ . '/../includes/header.php';
?>
<aside class="sidebar">
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="daftar_pengajuan.php">Daftar Pengajuan</a></li>
            <li><a href="verifikasi.php">Verifikasi</a></li>
            <li><a href="../logout.php">Keluar</a></li>
        </ul>
    </nav>
</aside>
<main class="content">
    <h1>Verifikasi Pengajuan</h1>
    <div class="card">
        <h3>Pengajuan Cuti Mahasiswa</h3>
        <p>Dokumen: <a href="#">lihat file</a></p>
        <button type="button">Setujui</button>
        <button type="button">Tolak</button>
    </div>
</main>
<?php
require_once __DIR__ . '/../includes/footer.php';

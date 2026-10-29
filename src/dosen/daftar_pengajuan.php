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
    <h1>Daftar Pengajuan</h1>
    <table class="table-list">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Judul</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Andika</td>
                <td>Pengajuan Cuti</td>
                <td>Menunggu</td>
            </tr>
        </tbody>
    </table>
</main>
<?php
require_once __DIR__ . '/../includes/footer.php';

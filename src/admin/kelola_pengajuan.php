<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['admin']);

require_once __DIR__ . '/../includes/header.php';
?>
<aside class="sidebar">
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="kelola_pengajuan.php">Kelola Pengajuan</a></li>
            <li><a href="kelola_pengguna.php">Kelola Pengguna</a></li>
            <li><a href="kelola_kategori.php">Kelola Kategori</a></li>
            <li><a href="laporan.php">Laporan</a></li>
            <li><a href="../logout.php">Keluar</a></li>
        </ul>
    </nav>
</aside>
<main class="content">
    <h1>Kelola Pengajuan</h1>
    <table class="table-list">
        <thead>
            <tr>
                <th>No</th>
                <th>Pengaju</th>
                <th>Judul</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>1</td><td>Andika</td><td>Cuti</td><td>Disetujui</td></tr>
        </tbody>
    </table>
</main>
<?php
require_once __DIR__ . '/../includes/footer.php';

<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['superadmin']);

require_once __DIR__ . '/../includes/header.php';
?>
<aside class="sidebar">
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="pengguna.php">Pengguna</a></li>
            <li><a href="role.php">Role</a></li>
            <li><a href="pengaturan.php">Pengaturan</a></li>
            <li><a href="../logout.php">Keluar</a></li>
        </ul>
    </nav>
</aside>
<main class="content">
    <h1>Manajemen Pengguna</h1>
    <table class="table-list">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Andika</td><td>andika@example.com</td><td>Mahasiswa</td></tr>
        </tbody>
    </table>
</main>
<?php
require_once __DIR__ . '/../includes/footer.php';

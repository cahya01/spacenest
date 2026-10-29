<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['mahasiswa']);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<h1>Riwayat Pengajuan</h1>
<table class="table-list">
    <thead>
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Status</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Pengajuan Cuti</td>
            <td>Disetujui</td>
            <td>2026-10-01</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Pengajuan Surat Keterangan</td>
            <td>Diproses</td>
            <td>2026-10-12</td>
        </tr>
    </tbody>
</table>
<?php
require_once __DIR__ . '/../includes/footer.php';

<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';

require_once __DIR__ . '/../includes/header.php';
?>
<main class="content">
    <h1>Detail Pengajuan</h1>
    <div class="card">
        <p><strong>Judul:</strong> Pengajuan Cuti</p>
        <p><strong>Status:</strong> Diproses</p>
        <p><strong>Deskripsi:</strong> Permohonan cuti sementara untuk kepentingan akademik.</p>
    </div>
</main>
<?php
require_once __DIR__ . '/../includes/footer.php';

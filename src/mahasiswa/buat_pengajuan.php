<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
requireRole(['mahasiswa']);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<h1>Buat Pengajuan</h1>
<form class="form-box">
    <div>
        <label>Judul Pengajuan</label>
        <input type="text" name="judul" placeholder="Masukkan judul pengajuan">
    </div>
    <div>
        <label>Kategori</label>
        <select name="kategori">
            <option value="">Pilih kategori</option>
            <option value="cuti">Cuti</option>
            <option value="beasiswa">Beasiswa</option>
            <option value="surat">Surat Keterangan</option>
        </select>
    </div>
    <div>
        <label>Deskripsi</label>
        <textarea name="deskripsi" rows="5" placeholder="Jelaskan kebutuhan pengajuan..."></textarea>
    </div>
    <button type="submit">Simpan Pengajuan</button>
</form>
<?php
require_once __DIR__ . '/../includes/footer.php';

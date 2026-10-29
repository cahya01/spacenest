<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'] ?? 'Pengajuan baru';
    $status = 'Diajukan';

    echo '<h1>Pengajuan Tersimpan</h1>';
    echo '<p>Judul: ' . htmlspecialchars($judul) . '</p>';
    echo '<p>Status: ' . htmlspecialchars($status) . '</p>';
    echo '<p><a href="../mahasiswa/dashboard.php">Kembali ke Dashboard</a></p>';
    exit;
}

header('Location: ../mahasiswa/buat_pengajuan.php');
exit;

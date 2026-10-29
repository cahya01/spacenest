<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dokumen'])) {
    $uploadDir = __DIR__ . '/../storage/dokumen/';
    $fileName = basename($_FILES['dokumen']['name']);
    $targetFile = $uploadDir . time() . '_' . $fileName;

    if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $targetFile)) {
        echo '<h1>Dokumen berhasil diunggah</h1>';
        echo '<p>File: ' . htmlspecialchars($fileName) . '</p>';
        echo '<p><a href="../mahasiswa/dashboard.php">Kembali</a></p>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Dokumen</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Upload Dokumen</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="dokumen" required>
            <button type="submit">Unggah</button>
        </form>
    </div>
</body>
</html>

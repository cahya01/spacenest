<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireRole(array $allowedRoles): void
{
    if (!isset($_SESSION['user']['role'])) {
        header('Location: ../login.php');
        exit;
    }

    if (!in_array($_SESSION['user']['role'], $allowedRoles, true)) {
        http_response_code(403);
        echo '<h1>Akses ditolak</h1>';
        echo '<p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>';
        echo '<p><a href="../logout.php">Keluar</a></p>';
        exit;
    }
}

<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    $loginPath = dirname(__DIR__) . '/login.php';
    header('Location: ' . str_replace('\\', '/', $loginPath));
    exit;
}

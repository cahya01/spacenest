<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const SESSION_TIMEOUT = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

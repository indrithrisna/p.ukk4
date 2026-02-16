<?php
session_start();
require_once 'config/database.php';

// Redirect berdasarkan role jika sudah login
if (isLoggedIn()) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] == 'petugas') {
        header("Location: petugas/dashboard.php");
    } else {
        header("Location: peminjam/dashboard.php");
    }
    exit();
}

// Redirect langsung ke login jika belum login
header("Location: auth/login.php");
exit();
?>

<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'Logout', 'User keluar dari sistem');
}

session_destroy();
header("Location: ../index.php");
exit();
?>

<?php
include("../config/koneksi.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset();
session_destroy();

setcookie('admin_id', '', time() - 3600, '/');
setcookie('admin_nama', '', time() - 3600, '/');
setcookie('admin_user', '', time() - 3600, '/');

header("Location: /admin/login.php");
exit;
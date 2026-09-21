<?php
include("../config/koneksi.php");

session_unset();
session_destroy();

setcookie('admin_id', '', time() - 3600, '/');
setcookie('admin_nama', '', time() - 3600, '/');
setcookie('admin_user', '', time() - 3600, '/');

header("Location: /admin/login.php");
exit;
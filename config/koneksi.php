<?php

if (session_status() === PHP_SESSION_NONE) {
    if (is_dir('/tmp') && is_writable('/tmp')) {
        @session_save_path('/tmp');
    }
    @session_start();
}

function get_db_env($key, $default = '') {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    return $default;
}

$host     = get_db_env('DB_HOST', get_db_env('MYSQLHOST', 'localhost'));
$user     = get_db_env('DB_USER', get_db_env('MYSQLUSER', 'root'));
$password = get_db_env('DB_PASSWORD', get_db_env('MYSQLPASSWORD', ''));
$database = get_db_env('DB_NAME', get_db_env('MYSQLDATABASE', 'db_repository_dokumen'));
$port     = get_db_env('DB_PORT', get_db_env('MYSQLPORT', '3306'));

$koneksi = mysqli_connect($host, $user, $password, $database, (int)$port);

if (!$koneksi) {
    die("Koneksi gagal : " . mysqli_connect_error());
}

function is_admin_logged_in() {
    if (isset($_SESSION['id_admin']) && !empty($_SESSION['id_admin'])) {
        return true;
    }
    if (isset($_COOKIE['admin_id']) && !empty($_COOKIE['admin_id'])) {
        $_SESSION['id_admin'] = $_COOKIE['admin_id'];
        $_SESSION['nama']     = isset($_COOKIE['admin_nama']) ? $_COOKIE['admin_nama'] : 'Administrator';
        $_SESSION['username'] = isset($_COOKIE['admin_user']) ? $_COOKIE['admin_user'] : 'Admin';
        return true;
    }
    return false;
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        header("Location: /admin/login.php");
        exit;
    }
}
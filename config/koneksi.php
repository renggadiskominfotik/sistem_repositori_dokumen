<?php

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

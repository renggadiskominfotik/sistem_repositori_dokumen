<?php

$host     = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: "localhost");
$user     = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: "root");
$password = getenv('DB_PASSWORD') ?: (getenv('MYSQLPASSWORD') ?: "");
$database = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: "db_repository_dokumen");
$port     = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: 3306);

$koneksi = mysqli_connect($host, $user, $password, $database, (int)$port);

if (!$koneksi) {
    die("Koneksi gagal : " . mysqli_connect_error());
}
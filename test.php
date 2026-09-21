<?php
include 'config/koneksi.php';
if ($koneksi) {
    echo "<h2>Koneksi Database Berhasil!</h2>";
} else {
    echo "<h2>Koneksi Database Gagal!</h2>";
}
?>
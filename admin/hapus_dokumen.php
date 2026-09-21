<?php
session_start();

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

include("../config/koneksi.php");

// Ambil ID dari URL
$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data dokumen
$query = mysqli_query($koneksi, "SELECT * FROM dokumen WHERE id_dokumen='$id'");
$data = mysqli_fetch_assoc($query);

// Hapus file dari folder uploads
$file = "../assets/uploads/" . $data['nama_file'];

if (file_exists($file)) {
    unlink($file);
}

// Hapus data dari database
mysqli_query($koneksi, "DELETE FROM dokumen WHERE id_dokumen='$id'");

echo "<script>

alert('Dokumen berhasil dihapus');

window.location='dokumen.php';

</script>";
?>
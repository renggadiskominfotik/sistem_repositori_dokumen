<?php
include("../config/koneksi.php");
require_admin_login();

// Ambil ID dari URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : 0;

if ($id) {
    // Ambil data dokumen
    $query = mysqli_query($koneksi, "SELECT * FROM dokumen WHERE id_dokumen='$id'");
    if ($query && mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);

        // Hapus file dari folder uploads jika ada
        if (!empty($data['nama_file'])) {
            $file = "../assets/uploads/" . $data['nama_file'];
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        // Hapus data dari database
        mysqli_query($koneksi, "DELETE FROM dokumen WHERE id_dokumen='$id'");
    }
}

echo "<script>
alert('Dokumen berhasil dihapus');
window.location='/admin/dokumen.php';
</script>";
exit;
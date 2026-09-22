<?php
include("../config/koneksi.php");
require_admin_login();

// Ambil ID dari URL
$id = isset($_GET['id']) ? db_real_escape_string($koneksi, $_GET['id']) : 0;

if ($id) {
    // Ambil data dokumen
    $query = db_query($koneksi, "SELECT * FROM dokumen WHERE id_dokumen='$id'");
    if ($query && db_num_rows($query) > 0) {
        $data = db_fetch_assoc($query);

        // Hapus file dari folder uploads jika ada
        if (!empty($data['nama_file'])) {
            $file = "../assets/uploads/" . $data['nama_file'];
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        // Hapus data dari database
        db_query($koneksi, "DELETE FROM dokumen WHERE id_dokumen='$id'");
    }
}

echo "<script>
alert('Dokumen berhasil dihapus');
window.location='/admin/dokumen.php';
</script>";
exit;
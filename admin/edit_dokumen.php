<?php
include("../config/koneksi.php");
require_admin_login();

// Ambil ID dari URL
$id = db_real_escape_string($koneksi, $_GET['id']);

// Ambil data dokumen
$query = db_query($koneksi, "SELECT * FROM dokumen WHERE id_dokumen='$id'");
$data = db_fetch_assoc($query);

if (!$data) {
    echo "<script>
            alert('Dokumen tidak ditemukan');
            window.location='dokumen.php';
          </script>";
    exit;
}

// Proses Update
if (isset($_POST['update'])) {

    $judul = db_real_escape_string($koneksi, $_POST['judul']);
    $jenis = db_real_escape_string($koneksi, $_POST['kategori_surat']);
    $deskripsi = db_real_escape_string($koneksi, $_POST['deskripsi']);

    if ($_FILES['nama_file']['name'] != "") {

        $rawNamaFile = $_FILES['nama_file']['name'];
        $tmpFile = $_FILES['nama_file']['tmp_name'];

        move_uploaded_file($tmpFile, "../assets/uploads/" . $rawNamaFile);

        $namaFile = db_real_escape_string($koneksi, $rawNamaFile);

        db_query($koneksi, "UPDATE dokumen SET 
            judul='$judul', 
            jenis_dokumen='$jenis', 
            deskripsi='$deskripsi', 
            nama_file='$namaFile' 
            WHERE id_dokumen='$id'");

    } else {

        db_query($koneksi, "UPDATE dokumen SET 
            judul='$judul', 
            jenis_dokumen='$jenis', 
            deskripsi='$deskripsi' 
            WHERE id_dokumen='$id'");

    }

    echo "<script>
            alert('Dokumen berhasil diperbarui');
            window.location='dokumen.php';
          </script>";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dokumen - DISKOMINFOTIK Riau</title>

    <!-- Google Fonts: Playfair Display & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        
        <!-- Sidebar -->
        <?php include("partials/sidebar.php"); ?>

        <!-- Content Area -->
        <div class="col-md-9 p-5 admin-content-area">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="admin-page-header m-0">Edit Dokumen</h2>
                <a href="dokumen.php" class="btn btn-outline-secondary px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <hr class="mb-5 opacity-50">

            <!-- Form Card -->
            <div class="row justify-content-center">
                <div class="col-xl-9">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <form method="POST" enctype="multipart/form-data">

                            <div class="mb-4">
                                <label class="form-label">Judul Dokumen</label>
                                <input
                                    type="text"
                                    name="judul"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['judul']); ?>"
                                    required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Jenis Dokumen (Kategori Utama)</label>
                                <select name="kategori_surat" id="kategoriSuratSelect" class="form-select" required>
                                    <option value="">-- Pilih Jenis Dokumen --</option>
                                    <?php
                                    $kats = [
                                        "Regulasi Pusat",
                                        "Regulasi Daerah",
                                        "Kebijakan/Keputusan",
                                        "Petunjuk Teknis",
                                        "SOP",
                                        "Laporan Pengembangan Aplikasi",
                                        "Dokumen Perencanaan",
                                        "Dokumen Analisis & Perancangan Sistem",
                                        "Dokumentasi Aplikasi",
                                        "Laporan Pengujian Sistem",
                                        "Laporan Pemeliharaan Aplikasi",
                                        "Laporan Monitoring & Evaluasi",
                                        "Dokumen Keamanan Informasi",
                                        "Laporan Audit",
                                        "Dokumen SPBE/Pemerintahan Digital",
                                        "Surat Edaran",
                                        "Berita Acara",
                                        "Laporan Kegiatan",
                                        "Laporan Kinerja",
                                        "Dokumen Kerja Sama",
                                        "Dokumentasi Kegiatan",
                                        "Lainnya"
                                    ];
                                    $current_kat = isset($data['jenis_dokumen']) ? $data['jenis_dokumen'] : '';
                                    foreach($kats as $idx => $k){
                                        $sel = ($current_kat == $k) ? "selected" : "";
                                        echo "<option value=\"$k\" $sel>$k</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Deskripsi</label>
                                <textarea
                                    name="deskripsi"
                                    class="form-control"
                                    rows="4"><?= htmlspecialchars($data['deskripsi']); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">File Saat Ini</label>
                                <div class="d-flex align-items-center p-3 rounded-3 bg-light border border-light-subtle">
                                    <i class="bi bi-file-earmark-check-fill text-success fs-4 me-3"></i>
                                    <div class="overflow-hidden">
                                        <a href="../assets/uploads/<?= $data['nama_file']; ?>" target="_blank" class="fw-bold text-decoration-none text-dark hover-primary-link text-break">
                                            <?= htmlspecialchars($data['nama_file']); ?>
                                        </a>
                                        <small class="text-muted d-block">Klik untuk mengunduh atau meninjau file aktif</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Upload File Baru (Opsional)</label>
                                <input
                                    type="file"
                                    name="nama_file"
                                    class="form-control">
                                <div class="form-text text-muted mt-2">
                                    <i class="bi bi-info-circle me-1"></i> Biarkan kosong jika Anda tidak ingin memperbarui file dokumen.
                                </div>
                            </div>

                            <div class="pt-3 border-top border-light d-flex justify-content-end gap-2">
                                <a href="dokumen.php" class="btn btn-outline-secondary px-4 py-2 fw-semibold">Batal</a>
                                <button type="submit" name="update" class="btn btn-primary px-4 py-2 fw-semibold d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Perbarui Dokumen</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<?php include("partials/footer.php"); ?>
</body>
</html>
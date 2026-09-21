<?php
include("../config/koneksi.php");

if (!isset($_SESSION['id_admin'])) {
    header("Location: /admin/login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $judul     = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $jenis     = mysqli_real_escape_string($koneksi, $_POST['kategori_surat']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $tanggal   = date("Y-m-d");
    $id_admin  = $_SESSION['id_admin'];

    $rawNamaFile = isset($_FILES['nama_file']['name']) ? $_FILES['nama_file']['name'] : '';
    $tmpFile     = isset($_FILES['nama_file']['tmp_name']) ? $_FILES['nama_file']['tmp_name'] : '';

    if (!empty($rawNamaFile) && !empty($tmpFile)) {
        $targetDir = "../assets/uploads/";
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }
        @move_uploaded_file($tmpFile, $targetDir . $rawNamaFile);
    }

    $namaFile = mysqli_real_escape_string($koneksi, $rawNamaFile);

    mysqli_query($koneksi, "INSERT INTO dokumen 
    (judul, jenis_dokumen, deskripsi, nama_file, tanggal_upload, id_admin) 
    VALUES 
    ('$judul','$jenis','$deskripsi','$namaFile','$tanggal','$id_admin')");

    echo "<script>
    alert('Dokumen berhasil ditambahkan');
    window.location='/admin/dokumen.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dokumen - DISKOMINFOTIK Riau</title>

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
        <div class="col-md-9 p-4 p-md-5 admin-content-area">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="admin-page-header m-0">Upload Dokumen Baru</h2>
                <a href="/admin/dokumen.php" class="btn btn-outline-secondary px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <hr class="mb-4 opacity-50">

            <div class="row">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                        <form method="POST" enctype="multipart/form-data">

                            <div class="mb-4">
                                <label class="form-label">Judul Dokumen</label>
                                <input
                                    type="text"
                                    name="judul"
                                    class="form-control"
                                    placeholder="Masukkan judul lengkap dokumen..."
                                    required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Jenis Dokumen (Kategori Utama)</label>
                                <select name="kategori_surat" id="kategoriSuratSelect" class="form-select" required>
                                    <option value="">-- Pilih Jenis Dokumen --</option>
                                    <option value="Regulasi Pusat">1. Regulasi Pusat</option>
                                    <option value="Regulasi Daerah">2. Regulasi Daerah</option>
                                    <option value="Kebijakan/Keputusan">3. Kebijakan/Keputusan</option>
                                    <option value="Petunjuk Teknis">4. Petunjuk Teknis</option>
                                    <option value="SOP">5. SOP</option>
                                    <option value="Laporan Pengembangan Aplikasi">6. Laporan Pengembangan Aplikasi</option>
                                    <option value="Dokumen Perencanaan">7. Dokumen Perencanaan</option>
                                    <option value="Dokumen Analisis & Perancangan Sistem">8. Dokumen Analisis & Perancangan Sistem</option>
                                    <option value="Dokumentasi Aplikasi">9. Dokumentasi Aplikasi</option>
                                    <option value="Laporan Pengujian Sistem">10. Laporan Pengujian Sistem</option>
                                    <option value="Laporan Pemeliharaan Aplikasi">11. Laporan Pemeliharaan Aplikasi</option>
                                    <option value="Laporan Monitoring & Evaluasi">12. Laporan Monitoring & Evaluasi</option>
                                    <option value="Dokumen Keamanan Informasi">13. Dokumen Keamanan Informasi</option>
                                    <option value="Laporan Audit">14. Laporan Audit</option>
                                    <option value="Dokumen SPBE/Pemerintahan Digital">15. Dokumen SPBE/Pemerintahan Digital</option>
                                    <option value="Surat Edaran">16. Surat Edaran</option>
                                    <option value="Berita Acara">17. Berita Acara</option>
                                    <option value="Laporan Kegiatan">18. Laporan Kegiatan</option>
                                    <option value="Laporan Kinerja">19. Laporan Kinerja</option>
                                    <option value="Dokumen Kerja Sama">20. Dokumen Kerja Sama</option>
                                    <option value="Dokumentasi Kegiatan">21. Dokumentasi Kegiatan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Deskripsi Dokumen</label>
                                <textarea
                                    name="deskripsi"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Tuliskan deskripsi singkat mengenai isi dokumen ini..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Upload File</label>
                                <input
                                    type="file"
                                    name="nama_file"
                                    class="form-control"
                                    required>
                                <div class="form-text text-muted mt-2">
                                    <i class="bi bi-info-circle me-1"></i> Pastikan file berformat PDF, Word, Excel, atau format dokumen resmi lainnya.
                                </div>
                            </div>

                            <div class="pt-3 border-top border-light d-flex justify-content-end gap-2">
                                <a href="/admin/dokumen.php" class="btn btn-outline-secondary px-4 py-2 fw-semibold">Batal</a>
                                <button type="submit" name="simpan" class="btn btn-primary px-4 py-2 fw-semibold d-flex align-items-center gap-2">
                                    <i class="bi bi-save-fill"></i>
                                    <span>Simpan Dokumen</span>
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
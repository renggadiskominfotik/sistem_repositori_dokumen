<?php
include("../config/koneksi.php");
require_admin_login();

// Helper function for badges
function getBadgeClass($type) {
    switch ($type) {
        case 'Surat': return 'bg-primary-subtle text-primary border border-primary-subtle';
        case 'SOP': return 'bg-success-subtle text-success border border-success-subtle';
        case 'Peraturan': return 'bg-danger-subtle text-danger border border-danger-subtle';
        case 'Pedoman': return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        case 'Sejarah': return 'bg-info-subtle text-info-emphasis border border-info-subtle';
        default: return 'bg-secondary-subtle text-secondary border border-secondary-subtle';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dokumen - DISKOMINFOTIK Riau</title>

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

            <?php 
            $get_kat  = isset($_GET['kat_surat'])  ? $_GET['kat_surat']  : '';
            $get_nama = isset($_GET['nama_surat']) ? $_GET['nama_surat'] : '';
            $get_cari = isset($_GET['cari'])       ? $_GET['cari']       : '';

            if (!function_exists('buildSmartSearchClauseAdmin')) {
                function buildSmartSearchClauseAdmin($text, $koneksi) {
                    $text = trim($text);
                    if (empty($text)) return "1=1";
                    
                    $clean_text = mysqli_real_escape_string($koneksi, $text);
                    $conds = [];
                    
                    // 1. Matched exact phrase / substring
                    $conds[] = "judul LIKE '%$clean_text%'";
                    $conds[] = "deskripsi LIKE '%$clean_text%'";
                    $conds[] = "jenis_dokumen LIKE '%$clean_text%'";
                    
                    // 2. Multi-word Token Matching
                    $words = array_filter(explode(' ', $text));
                    if (count($words) > 1) {
                        $word_ands = [];
                        foreach ($words as $w) {
                            $w_clean = trim($w);
                            if (strlen($w_clean) >= 2) {
                                $w_db = mysqli_real_escape_string($koneksi, $w_clean);
                                $word_ands[] = "(judul LIKE '%$w_db%' OR deskripsi LIKE '%$w_db%' OR jenis_dokumen LIKE '%$w_db%')";
                            }
                        }
                        if (!empty($word_ands)) {
                            $conds[] = "(" . implode(" AND ", $word_ands) . ")";
                        }
                    }
                    
                    // 3. Synonym / Kata Kunci Spesifik
                    $synonyms = [
                        'sakit'      => ['sakit', 'cuti sakit', 'izin sakit'],
                        'melahirkan' => ['melahirkan', 'cuti melahirkan', 'bersalin'],
                        'tahunan'    => ['tahunan', 'cuti tahunan'],
                        'besar'      => ['besar', 'cuti besar'],
                        'penting'    => ['penting', 'alasan penting'],
                        'permohonan' => ['permohonan', 'izin', 'rekomendasi', 'fasilitas', 'dana'],
                        'sk'         => ['sk', 'keputusan', 'pengangkatan', 'pemberhentian', 'pejabat', 'tim', 'penugasan'],
                        'edaran'     => ['edaran', 'libur', 'wfh', 'seragam', 'jadwal'],
                        'undangan'   => ['undangan', 'rapat', 'sosialisasi', 'pelatihan', 'peresmian']
                    ];
                    
                    foreach ($synonyms as $key => $values) {
                        if (stripos($text, $key) !== false) {
                            foreach ($values as $val) {
                                $val_db = mysqli_real_escape_string($koneksi, $val);
                                $conds[] = "judul LIKE '%$val_db%' OR deskripsi LIKE '%$val_db%'";
                            }
                        }
                    }
                    
                    return "(" . implode(" OR ", array_unique($conds)) . ")";
                }
            }
            ?>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h2 class="admin-page-header m-0">
                        <?= !empty($get_nama) ? htmlspecialchars($get_nama) : (!empty($get_kat) ? htmlspecialchars($get_kat) : 'Kelola Dokumen'); ?>
                    </h2>
                    <?php if (!empty($get_kat) || !empty($get_nama) || !empty($get_cari)): ?>
                        <small class="text-primary fw-semibold d-block mt-1">
                            <i class="bi bi-funnel-fill me-1"></i> Filter: 
                            <?= !empty($get_kat) ? 'Kategori: <strong>' . htmlspecialchars($get_kat) . '</strong>' : ''; ?>
                            <?= !empty($get_nama) ? ' &bull; Surat: <strong>' . htmlspecialchars($get_nama) . '</strong>' : ''; ?>
                            <?= !empty($get_cari) ? ' &bull; Kata Kunci: <strong>"' . htmlspecialchars($get_cari) . '"</strong>' : ''; ?>
                            <a href="dokumen.php" class="ms-2 text-danger text-decoration-none fw-bold small">(<i class="bi bi-x-circle-fill"></i> Reset Filter)</a>
                        </small>
                    <?php endif; ?>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" action="dokumen.php" class="d-flex gap-2">
                        <?php if (!empty($get_kat)): ?>
                            <input type="hidden" name="kat_surat" value="<?= htmlspecialchars($get_kat); ?>">
                        <?php endif; ?>
                        <?php if (!empty($get_nama)): ?>
                            <input type="hidden" name="nama_surat" value="<?= htmlspecialchars($get_nama); ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <input type="text" name="cari" class="form-control" placeholder="Cari dokumen..." value="<?= htmlspecialchars($get_cari); ?>">
                            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                    <a href="tambah_dokumen.php" class="btn btn-primary px-3 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2 text-nowrap">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah</span>
                    </a>
                </div>
            </div>

            <hr class="mb-4 opacity-50">

            <!-- Responsive Table Container -->
            <div class="table-responsive-custom shadow-sm border-0">
                <table class="table admin-table align-middle table-hover">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Judul Dokumen</th>
                            <th width="180">Kategori Surat</th>
                            <th width="180">Tanggal Upload</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $sql = "SELECT * FROM dokumen WHERE 1";

                        if (!empty($get_cari)) {
                            $sql .= " AND " . buildSmartSearchClauseAdmin($get_cari, $koneksi);
                        }

                        if (!empty($get_kat)) {
                            $kat_db = mysqli_real_escape_string($koneksi, $get_kat);
                            $sql .= " AND jenis_dokumen='$kat_db'";
                        }

                        if (!empty($get_nama)) {
                            $sql .= " AND " . buildSmartSearchClauseAdmin($get_nama, $koneksi);
                        }
                        $sql .= " ORDER BY id_dokumen DESC";
                        $query = mysqli_query($koneksi, $sql);
                        
                        if (mysqli_num_rows($query) == 0) {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'>Belum ada dokumen yang sesuai dengan filter.</td></tr>";
                        } else {
                            while($data = mysqli_fetch_assoc($query)){
                                $kat_display = $data['jenis_dokumen'];
                        ?>
                            <tr>
                                <td class="text-center fw-bold text-secondary"><?= $no++; ?></td>
                                <td>
                                    <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($data['judul']); ?></div>
                                    <small class="text-muted d-block line-clamp-2"><?= htmlspecialchars($data['deskripsi']); ?></small>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-2 fw-semibold <?= getBadgeClass($kat_display); ?>">
                                        <?= htmlspecialchars($kat_display); ?>
                                    </span>
                                </td>
                                <td class="text-secondary fw-semibold">
                                    <i class="bi bi-calendar3 text-primary me-2"></i>
                                    <?= date('d M Y', strtotime($data['tanggal_upload'])); ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info border-0 px-2 py-2 rounded-3" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#previewModal"
                                                data-judul="<?= htmlspecialchars($data['judul']); ?>"
                                                data-jenis="<?= htmlspecialchars($kat_display); ?>"
                                                data-badge="<?= getBadgeClass($kat_display); ?>"
                                                data-tanggal="<?= date('d M Y', strtotime($data['tanggal_upload'])); ?>"
                                                data-deskripsi="<?= htmlspecialchars($data['deskripsi']); ?>"
                                                data-file="<?= htmlspecialchars($data['nama_file']); ?>"
                                                title="Lihat / Pratinjau Dokumen">
                                            <i class="bi bi-eye-fill fs-6"></i>
                                        </button>
                                        <a href="edit_dokumen.php?id=<?= $data['id_dokumen']; ?>" 
                                           class="btn btn-sm btn-outline-warning border-0 px-2 py-2 rounded-3" 
                                           title="Edit Dokumen">
                                            <i class="bi bi-pencil-fill fs-6"></i>
                                        </a>
                                        <a href="hapus_dokumen.php?id=<?= $data['id_dokumen']; ?>" 
                                           class="btn btn-sm btn-outline-danger border-0 px-2 py-2 rounded-3" 
                                           onclick="return confirm('Yakin ingin menghapus dokumen ini?')" 
                                           title="Hapus Dokumen">
                                            <i class="bi bi-trash-fill fs-6"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            }
                        } 
                        ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>

<!-- MODAL PREVIEW DOKUMEN ADMIN -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span id="modalBadge" class="badge rounded-pill px-3 py-2 fw-semibold"></span>
                    <h5 class="modal-title fw-bold text-dark m-0" id="previewModalLabel">Detail & Pratinjau Dokumen</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="row g-4">
                    <!-- File Metadata Info -->
                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light border">
                            <h5 id="modalJudul" class="fw-bold text-dark mb-2"></h5>
                            <p id="modalDeskripsi" class="text-secondary small mb-3"></p>
                            <div class="d-flex flex-wrap align-items-center gap-3 text-muted small border-top pt-2">
                                <span><i class="bi bi-calendar3 text-primary me-1"></i> Tanggal Upload: <strong id="modalTanggal" class="text-dark"></strong></span>
                                <span><i class="bi bi-file-earmark-text text-primary me-1"></i> Nama File: <strong id="modalNamaFile" class="text-dark"></strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Viewer Container -->
                    <div class="col-12">
                        <div id="modalViewerContainer" class="rounded-3 border overflow-hidden bg-light d-flex align-items-center justify-content-center style-viewer-box" style="min-height: 500px;">
                            <!-- Content generated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-3 px-4 d-flex justify-content-between align-items-center">
                <a id="modalNewTabBtn" href="#" target="_blank" class="btn btn-outline-secondary rounded-3 d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Buka di Tab Baru</span>
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Tutup</button>
                    <a id="modalDownloadBtn" href="#" class="btn btn-primary rounded-3 px-4 d-flex align-items-center gap-2" download>
                        <i class="bi bi-cloud-arrow-down-fill"></i>
                        <span>Unduh Dokumen</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>
<!-- JSZip & docx-preview for direct browser rendering of DOCX documents -->
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.3/dist/docx-preview.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const previewModal = document.getElementById('previewModal');
    if (previewModal) {
        previewModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const judul = button.getAttribute('data-judul');
            const jenis = button.getAttribute('data-jenis');
            const badgeClass = button.getAttribute('data-badge');
            const tanggal = button.getAttribute('data-tanggal');
            const deskripsi = button.getAttribute('data-deskripsi') || 'Tidak ada deskripsi untuk dokumen ini.';
            const file = button.getAttribute('data-file');

            document.getElementById('modalJudul').textContent = judul;
            document.getElementById('modalDeskripsi').textContent = deskripsi;
            document.getElementById('modalTanggal').textContent = tanggal;
            document.getElementById('modalNamaFile').textContent = file;
            
            const modalBadge = document.getElementById('modalBadge');
            modalBadge.className = 'badge rounded-pill px-3 py-2 fw-semibold ' + badgeClass;
            modalBadge.textContent = jenis;

            const fileUrl = '../assets/uploads/' + encodeURIComponent(file);
            document.getElementById('modalDownloadBtn').href = fileUrl;
            document.getElementById('modalNewTabBtn').href = fileUrl;

            const container = document.getElementById('modalViewerContainer');
            const ext = file.split('.').pop().toLowerCase();

            if (['pdf'].includes(ext)) {
                container.className = 'rounded-3 border overflow-hidden bg-light';
                container.style.minHeight = '550px';
                container.innerHTML = `<iframe src="${fileUrl}#toolbar=1" class="w-100 h-100 border-0" style="min-height: 550px;"></iframe>`;
            } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
                container.className = 'rounded-3 border bg-dark d-flex align-items-center justify-content-center p-3 text-center';
                container.style.minHeight = '450px';
                container.innerHTML = `<img src="${fileUrl}" alt="${judul}" class="img-fluid rounded shadow-sm" style="max-height: 520px; object-fit: contain;">`;
            } else if (['mp4', 'webm', 'ogg'].includes(ext)) {
                container.className = 'rounded-3 border bg-black d-flex align-items-center justify-content-center';
                container.style.minHeight = '400px';
                container.innerHTML = `<video controls class="w-100 rounded" style="max-height: 500px;"><source src="${fileUrl}">Browser Anda tidak mendukung pemutar video.</video>`;
            } else if (['docx', 'doc'].includes(ext)) {
                container.className = 'rounded-3 border bg-light d-flex flex-column align-items-center justify-content-center p-4';
                container.style.minHeight = '500px';
                container.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Memuat...</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Memuat Pratinjau Dokumen Word...</h6>
                        <p class="text-muted small m-0">Sedang mengonversi dan menampilkan isi berkas .${ext.toUpperCase()}</p>
                    </div>
                `;

                fetch(fileUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('File tidak ditemukan');
                        return response.blob();
                    })
                    .then(blob => {
                        container.className = 'rounded-3 border bg-white p-3 overflow-auto style-docx-viewer';
                        container.style.maxHeight = '600px';
                        container.style.minHeight = '500px';
                        container.innerHTML = '<div id="docxTarget" class="w-100"></div>';
                        const docxTarget = document.getElementById('docxTarget');

                        if (typeof docx !== 'undefined' && docx.renderAsync) {
                            docx.renderAsync(blob, docxTarget, null, {
                                className: "docx-wrapper",
                                inWrapper: true,
                                ignoreWidth: false,
                                ignoreHeight: false,
                                experimental: true
                            }).catch(err => {
                                console.error('DOCX render error:', err);
                                renderDocxFallback();
                            });
                        } else {
                            renderDocxFallback();
                        }
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        renderDocxFallback();
                    });

                function renderDocxFallback() {
                    container.className = 'rounded-3 border bg-light d-flex flex-column align-items-center justify-content-center p-5 text-center';
                    container.style.minHeight = '350px';
                    container.innerHTML = `
                        <div class="mb-3">
                            <i class="bi bi-file-earmark-word text-primary" style="font-size: 4.5rem;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Pratinjau .${ext.toUpperCase()} Tidak Tersedia</h5>
                        <p class="text-muted mb-4 fs-6" style="max-width: 480px;">
                            Silakan unduh dokumen untuk melihat isinya secara lengkap di aplikasi Microsoft Word.
                        </p>
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <a href="${fileUrl}" download class="btn btn-primary rounded-3 px-4 py-2 fw-semibold">
                                <i class="bi bi-cloud-arrow-down-fill me-1"></i> Unduh Dokumen
                            </a>
                        </div>
                    `;
                }
            } else {
                container.className = 'rounded-3 border bg-light d-flex flex-column align-items-center justify-content-center p-5 text-center';
                container.style.minHeight = '350px';
                container.innerHTML = `
                    <div class="mb-3">
                        <i class="bi bi-file-earmark-text text-primary" style="font-size: 4.5rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Pratinjau Berkas .${ext.toUpperCase()}</h5>
                    <p class="text-muted mb-4 fs-6" style="max-width: 480px;">
                        Dokumen dengan format <strong>.${ext.toUpperCase()}</strong> dapat diunduh untuk dibuka pada perangkat Anda.
                    </p>
                    <div class="d-flex gap-2 flex-wrap justify-content-center">
                        <a href="${fileUrl}" download class="btn btn-primary rounded-3 px-4 py-2 fw-semibold">
                            <i class="bi bi-cloud-arrow-down-fill me-1"></i> Unduh Dokumen
                        </a>
                    </div>
                `;
            }
        });
    }
});
</script>
</body>
</html>
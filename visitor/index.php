<?php
include("../config/koneksi.php");

$cari       = isset($_GET['cari'])       ? $_GET['cari']       : "";
$katSurat   = isset($_GET['kat_surat'])  ? $_GET['kat_surat']  : "";
$namaSurat  = isset($_GET['nama_surat']) ? $_GET['nama_surat'] : "";

// Data Jenis Dokumen & Icon
$jenis_dokumen_list = [
    'Regulasi Pusat' => ['icon' => 'bi-file-earmark-ruled-fill'],
    'Regulasi Daerah' => ['icon' => 'bi-journal-richtext'],
    'Kebijakan/Keputusan' => ['icon' => 'bi-file-earmark-check-fill'],
    'Petunjuk Teknis' => ['icon' => 'bi-journal-code'],
    'SOP' => ['icon' => 'bi-card-checklist'],
    'Laporan Pengembangan Aplikasi' => ['icon' => 'bi-code-square'],
    'Dokumen Perencanaan' => ['icon' => 'bi-kanban-fill'],
    'Dokumen Analisis & Perancangan Sistem' => ['icon' => 'bi-diagram-3-fill'],
    'Dokumentasi Aplikasi' => ['icon' => 'bi-book-fill'],
    'Laporan Pengujian Sistem' => ['icon' => 'bi-bug-fill'],
    'Laporan Pemeliharaan Aplikasi' => ['icon' => 'bi-tools'],
    'Laporan Monitoring & Evaluasi' => ['icon' => 'bi-graph-up-arrow'],
    'Dokumen Keamanan Informasi' => ['icon' => 'bi-shield-lock-fill'],
    'Laporan Audit' => ['icon' => 'bi-clipboard-data-fill'],
    'Dokumen SPBE/Pemerintahan Digital' => ['icon' => 'bi-building-fill-gear'],
    'Surat Edaran' => ['icon' => 'bi-megaphone-fill'],
    'Berita Acara' => ['icon' => 'bi-file-earmark-text-fill'],
    'Laporan Kegiatan' => ['icon' => 'bi-calendar-event-fill'],
    'Laporan Kinerja' => ['icon' => 'bi-award-fill'],
    'Dokumen Kerja Sama' => ['icon' => 'bi-handbag-fill'],
    'Dokumentasi Kegiatan' => ['icon' => 'bi-camera-video-fill'],
];

// Fetch all documents grouped by category in one safe query
$db_docs_by_kat = [];
if (isset($koneksi) && $koneksi) {
    try {
        $res = @mysqli_query($koneksi, "SELECT DISTINCT jenis_dokumen, judul FROM dokumen ORDER BY id_dokumen DESC");
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $kat = $r['jenis_dokumen'];
                $jdl = $r['judul'];
                if (!empty($kat) && !empty($jdl)) {
                    if (!isset($db_docs_by_kat[$kat])) {
                        $db_docs_by_kat[$kat] = [];
                    }
                    if (!in_array($jdl, $db_docs_by_kat[$kat])) {
                        $db_docs_by_kat[$kat][] = $jdl;
                    }
                }
            }
        }
    } catch (Throwable $e) {
        // Safe fallback
    }
}


// Helper Function untuk Pencarian Pintar (Smart Tokenized & Synonym Search)
function buildSmartSearchClause($text, $koneksi) {
    $text = trim($text);
    if (empty($text)) return "1=1";
    
    $clean_text = mysqli_real_escape_string($koneksi, $text);
    $conds = [];
    
    // 1. Matched exact phrase / substring
    $conds[] = "judul LIKE '%$clean_text%'";
    $conds[] = "deskripsi LIKE '%$clean_text%'";
    $conds[] = "jenis_dokumen LIKE '%$clean_text%'";
    
    // 2. Multi-word Token Matching (misal "surat sakit" -> harus mengandung "surat" AND "sakit")
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
    
    // 3. Synonym / Kata Kunci Spesifik (misal "sakit" -> pencarian peka pada cuti sakit)
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

// Build SQL Query based on filters
$sql = "SELECT * FROM dokumen WHERE 1";

if ($cari != "") {
    $sql .= " AND " . buildSmartSearchClause($cari, $koneksi);
}

if ($katSurat != "") {
    $katSurat_db = mysqli_real_escape_string($koneksi, $katSurat);
    $sql .= " AND jenis_dokumen='$katSurat_db'";
}

if ($namaSurat != "") {
    $sql .= " AND " . buildSmartSearchClause($namaSurat, $koneksi);
}

$sql .= " ORDER BY id_dokumen DESC";
$query = mysqli_query($koneksi, $sql);

// Helper function for badges
function getBadgeClass($type) {
    switch ($type) {
        case 'Surat':     return 'bg-primary-subtle text-primary border border-primary-subtle';
        case 'SOP':       return 'bg-success-subtle text-success border border-success-subtle';
        case 'Peraturan': return 'bg-danger-subtle text-danger border border-danger-subtle';
        case 'Pedoman':   return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        case 'Sejarah':   return 'bg-info-subtle text-info-emphasis border border-info-subtle';
        default:          return 'bg-secondary-subtle text-secondary border border-secondary-subtle';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repository Dokumen - DISKOMINFOTIK Riau</title>

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

        <!-- =============================================
             SIDEBAR PENGUNJUNG (Persis seperti Admin Layout)
        ============================================= -->
        <!-- Header Topbar untuk Mobile (Layar Handphone < 768px) -->
        <div class="mobile-topbar d-md-none p-3 d-flex justify-content-between align-items-center bg-primary text-white shadow-sm sticky-top" style="background: linear-gradient(135deg, #44ACFF 0%, #0077e6 100%) !important; z-index: 1020;">
            <div class="d-flex align-items-center gap-2">
                <img src="../assets/img/logo.png" alt="Logo" width="30" height="38">
                <div>
                    <div class="fw-bold fs-6 lh-1">DISKOMINFOTIK</div>
                    <div class="small opacity-75" style="font-size: 10px; letter-spacing: 1px;">PROVINSI RIAU</div>
                </div>
            </div>
            <button class="btn btn-light text-primary fw-bold shadow-sm d-flex align-items-center gap-2 py-1 px-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#visitorSidebarOffcanvas" aria-controls="visitorSidebarOffcanvas">
                <i class="bi bi-list fs-5"></i>
                <span>Kategori</span>
            </button>
        </div>

        <!-- Sidebar -->
        <div class="offcanvas-md offcanvas-start sidebar text-white col-md-3" tabindex="-1" id="visitorSidebarOffcanvas" aria-labelledby="visitorSidebarOffcanvasLabel" data-bs-scroll="true" data-bs-backdrop="false">
            
            <!-- Header Offcanvas khusus Mobile -->
            <div class="offcanvas-header d-md-none border-bottom border-white-50 p-3">
                <h5 class="offcanvas-title text-white fw-bold d-flex align-items-center gap-2" id="visitorSidebarOffcanvasLabel">
                    <img src="../assets/img/logo.png" alt="Logo" width="24" height="30">
                    DISKOMINFOTIK RIAU
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#visitorSidebarOffcanvas" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body flex-column p-3 p-md-0 pt-md-3">
                <!-- Brand Box (Desktop) -->
                <div class="sidebar-brand-box d-none d-md-flex align-items-center justify-content-between mb-4 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="../assets/img/logo.png" alt="Logo DISKOMINFOTIK" width="46" height="56">
                        <div>
                            <div class="logo-title">DISKOMINFOTIK</div>
                            <div class="logo-subtitle">Provinsi Riau</div>
                        </div>
                    </div>
                    <img src="../assets/img/image.png" alt="Logo Diskominfo" width="48" height="48" style="object-fit: contain;">
                </div>

                <!-- Divider Desktop -->
                <div class="sidebar-divider mb-4 d-none d-md-block"></div>

                <ul class="nav flex-column pt-2 w-100">
                    <li>
                        <a href="/visitor/index.php" class="nav-link <?= (empty($katSurat) && empty($namaSurat) && empty($jenis) && empty($cari)) ? 'active' : ''; ?>">
                            <i class="bi bi-folder-fill"></i>
                            <span>Semua Dokumen</span>
                        </a>
                    </li>

                    <!-- Fitur Jenis Dokumen -->
                    <li>
                        <a href="#jenisDokumenCollapseVisitor" class="nav-link d-flex align-items-center justify-content-between <?= (!empty($katSurat) || !empty($namaSurat)) ? 'active' : ''; ?>" data-bs-toggle="collapse" data-bs-target="#jenisDokumenCollapseVisitor" role="button" aria-expanded="<?= (!empty($katSurat) || !empty($namaSurat)) ? 'true' : 'false'; ?>">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-layers-fill"></i>
                                <span>Jenis Dokumen</span>
                            </div>
                            <i class="bi bi-chevron-down small sub-arrow"></i>
                        </a>
                        <div class="collapse <?= (!empty($katSurat) || !empty($namaSurat)) ? 'show' : ''; ?>" id="jenisDokumenCollapseVisitor">
                            <ul class="list-unstyled sidebar-submenu">
                                <?php foreach ($jenis_dokumen_list as $kat_title => $kat_data): 
                                    $is_this_kat = ($katSurat === $kat_title);
                                    $collapse_id = "vsub_" . preg_replace('/[^a-zA-Z0-9]/', '', $kat_title);
                                    $db_docs = isset($db_docs_by_kat[$kat_title]) ? $db_docs_by_kat[$kat_title] : [];
                                ?>
                                    <li class="sidebar-sub-item">
                                        <a href="#<?= $collapse_id ?>" class="sidebar-sub-toggle <?= $is_this_kat ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#<?= $collapse_id ?>" role="button" aria-expanded="<?= $is_this_kat ? 'true' : 'false' ?>">
                                            <span class="d-flex align-items-center gap-2">
                                                <i class="bi <?= $kat_data['icon'] ?>"></i>
                                                <span><?= htmlspecialchars($kat_title) ?></span>
                                            </span>
                                            <i class="bi bi-chevron-down sub-arrow"></i>
                                        </a>
                                        <div class="collapse <?= $is_this_kat ? 'show' : '' ?>" id="<?= $collapse_id ?>">
                                            <div class="sidebar-nested-sub">
                                                <a href="/visitor/index.php?kat_surat=<?= urlencode($kat_title) ?>" class="sidebar-nested-link <?= ($katSurat === $kat_title && empty($namaSurat)) ? 'active' : '' ?>" style="font-weight: 600; opacity: 0.95;">
                                                    <i class="bi bi-grid-3x3-gap-fill me-1"></i> Semua <?= htmlspecialchars($kat_title) ?>
                                                </a>
                                                <?php if (empty($db_docs)): ?>
                                                    <span class="sidebar-nested-link text-white-50 fst-italic py-1" style="font-size: 0.8rem; pointer-events: none;">(Belum ada dokumen)</span>
                                                <?php else: ?>
                                                    <?php foreach ($db_docs as $item): 
                                                        $is_item_active = ($namaSurat === $item && $katSurat === $kat_title);
                                                    ?>
                                                        <a href="/visitor/index.php?kat_surat=<?= urlencode($kat_title) ?>&nama_surat=<?= urlencode($item) ?>" class="sidebar-nested-link <?= $is_item_active ? 'active' : '' ?>">
                                                            <?= htmlspecialchars($item) ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- =============================================
             CONTENT AREA PENGUNJUNG
        ============================================= -->
        <div class="col-md-9 p-4 p-md-5 admin-content-area">

            <!-- SEARCH BAR -->
            <div class="row justify-content-center mb-4">
                <div class="col-lg-9">
                    <form method="GET" id="searchForm" class="card border-0 shadow-sm rounded-4 p-2 bg-white">
                        <div class="input-group align-items-center">
                            <span class="input-group-text bg-white border-0 ps-3">
                                <i class="bi bi-search text-muted fs-5"></i>
                            </span>
                            <input
                                type="search"
                                name="cari"
                                id="searchInput"
                                class="form-control border-0 py-3 fs-6 shadow-none"
                                placeholder="Cari dokumen berdasarkan judul atau deskripsi..."
                                value="<?= htmlspecialchars($cari); ?>"
                                autocomplete="off">
                            
                            <?php if($cari != ""){ ?>
                                <button type="button" id="clearSearchBtn" class="btn btn-link text-muted pe-3 text-decoration-none" title="Hapus Pencarian">
                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                </button>
                            <?php } ?>


                            <?php if($katSurat != ""){ ?>
                                <input type="hidden" name="kat_surat" value="<?= htmlspecialchars($katSurat); ?>">
                            <?php } ?>
                            <?php if($namaSurat != ""){ ?>
                                <input type="hidden" name="nama_surat" value="<?= htmlspecialchars($namaSurat); ?>">
                            <?php } ?>
                            
                            <button class="btn btn-primary px-4 rounded-3 fw-semibold ms-1" type="submit">
                                Cari Dokumen
                            </button>
                        </div>
                    </form>
                </div>
            </div>



            <!-- ACTIVE FILTER BAR -->
            <?php if (!empty($katSurat) || !empty($namaSurat) || !empty($cari)): ?>
                <div class="alert alert-info border-0 shadow-sm rounded-3 d-flex align-items-center justify-content-between p-3 mb-4">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <i class="bi bi-funnel-fill text-primary fs-5"></i>
                        <span class="fw-bold text-dark">Filter Aktif:</span>
                        <?php if (!empty($katSurat)): ?>
                            <span class="badge bg-primary rounded-pill px-3 py-2">Jenis Dokumen: <?= htmlspecialchars($katSurat); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($namaSurat)): ?>
                            <span class="badge bg-success rounded-pill px-3 py-2">Dokumen: <?= htmlspecialchars($namaSurat); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($cari)): ?>
                            <span class="badge bg-dark rounded-pill px-3 py-2">Pencarian: "<?= htmlspecialchars($cari); ?>"</span>
                        <?php endif; ?>
                    </div>
                    <a href="index.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold ms-2">
                        <i class="bi bi-x-circle-fill me-1"></i> Reset
                    </a>
                </div>
            <?php endif; ?>

            <!-- HEADING & COUNT -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="fw-bold text-dark m-0">
                    <?php 
                    if (!empty($namaSurat)) echo htmlspecialchars($namaSurat);
                    elseif (!empty($katSurat)) echo htmlspecialchars($katSurat);
                    else echo "Daftar Dokumen Surat";
                    ?>
                </h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                    <?= mysqli_num_rows($query); ?> Dokumen Ditemukan
                </span>
            </div>

            <!-- DAFTAR DOKUMEN GRID -->
            <div class="row">
                <?php if(mysqli_num_rows($query) == 0) { ?>
                    <div class="col-12 text-center py-5">
                        <div class="py-4 bg-white rounded-4 shadow-sm p-4">
                            <i class="bi bi-folder-x text-muted" style="font-size: 5rem;"></i>
                            <h4 class="mt-3 fw-bold text-dark">Dokumen Tidak Ditemukan</h4>
                            <p class="text-secondary">Maaf, dokumen yang Anda cari tidak tersedia atau belum diunggah.</p>
                            <?php if ($cari != "" || $namaSurat != "" || $katSurat != "") { ?>
                                <a href="index.php" class="btn btn-primary rounded-pill px-4 py-2 mt-2 fw-semibold">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Filter
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <?php while($data = mysqli_fetch_assoc($query)){ 
                        $kat_display = !empty($data['kategori_surat']) ? $data['kategori_surat'] : $data['jenis_dokumen'];
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 custom-card shadow-sm border-0 bg-white">
                                <div class="card-body p-4 d-flex flex-column">
                                    <!-- Badge & Date -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge rounded-pill px-3 py-2 fw-semibold <?= getBadgeClass($kat_display); ?>">
                                            <?= htmlspecialchars($kat_display); ?>
                                        </span>
                                        <small class="text-muted d-flex align-items-center gap-1">
                                            <i class="bi bi-calendar3 text-primary"></i>
                                            <?= date('d M Y', strtotime($data['tanggal_upload'])); ?>
                                        </small>
                                    </div>
                                    
                                    <!-- Title -->
                                    <h5 class="card-title fw-bold text-dark mb-2 line-clamp-2" title="<?= htmlspecialchars($data['judul']); ?>">
                                        <?= htmlspecialchars($data['judul']); ?>
                                    </h5>
                                    
                                    <!-- Description -->
                                    <p class="card-text text-secondary mb-4 flex-grow-1 fs-6">
                                        <?= !empty($data['deskripsi']) ? nl2br(htmlspecialchars($data['deskripsi'])) : '<em class="text-muted">Tidak ada deskripsi untuk dokumen ini.</em>'; ?>
                                    </p>
                                    
                                    <!-- Action Buttons -->
                                    <div class="mt-auto pt-3 border-top border-light d-flex gap-2">
                                        <button type="button" 
                                                class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2 rounded-3 btn-preview-modern" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#previewModal"
                                                data-judul="<?= htmlspecialchars($data['judul']); ?>"
                                                data-jenis="<?= htmlspecialchars($kat_display); ?>"
                                                data-badge="<?= getBadgeClass($kat_display); ?>"
                                                data-tanggal="<?= date('d M Y', strtotime($data['tanggal_upload'])); ?>"
                                                data-deskripsi="<?= htmlspecialchars($data['deskripsi']); ?>"
                                                data-file="<?= htmlspecialchars($data['nama_file']); ?>">
                                            <i class="bi bi-eye-fill fs-5"></i>
                                            <span>Lihat Dokumen</span>
                                        </button>

                                        <a href="../assets/uploads/<?= urlencode($data['nama_file']); ?>" 
                                           class="btn btn-outline-primary d-flex align-items-center justify-content-center px-3 py-2 rounded-3 btn-download-modern" 
                                           download
                                           title="Unduh Dokumen">
                                            <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>

        </div>

    </div>
</div>

<!-- MODAL PREVIEW DOKUMEN -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- JSZip & docx-preview for direct browser rendering of DOCX documents -->
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.3/dist/docx-preview.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // FITUR OTOMATIS REFRESH PENCARIAN
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const clearSearchBtn = document.getElementById('clearSearchBtn');

    if (searchInput && searchForm) {
        const hasInitialQuery = "<?= addslashes($cari); ?>" !== "";

        searchInput.addEventListener('input', function () {
            if (this.value.trim() === '' && hasInitialQuery) {
                searchForm.submit();
            }
        });

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchForm.submit();
            });
        }
    }

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

            const fileUrl = '/assets/uploads/' + encodeURIComponent(file);
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
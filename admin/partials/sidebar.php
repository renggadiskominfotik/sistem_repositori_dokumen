<?php
$current_page = basename($_SERVER['PHP_SELF']);
$get_kat = isset($_GET['kat_surat']) ? $_GET['kat_surat'] : '';
$get_nama = isset($_GET['nama_surat']) ? $_GET['nama_surat'] : '';

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
?>

<!-- Header Topbar untuk Mobile (Layar Handphone < 768px) -->
<div class="mobile-topbar d-md-none p-3 d-flex justify-content-between align-items-center bg-primary text-white shadow-sm sticky-top" style="background: linear-gradient(135deg, #44ACFF 0%, #0077e6 100%) !important; z-index: 1020;">
    <div class="d-flex align-items-center gap-2">
        <img src="../assets/img/logo.png" alt="Logo" width="30" height="38">
        <div>
            <div class="fw-bold fs-6 lh-1">DISKOMINFOTIK</div>
            <div class="small opacity-75" style="font-size: 10px; letter-spacing: 1px;">PROVINSI RIAU</div>
        </div>
    </div>
    <button class="btn btn-light text-primary fw-bold shadow-sm d-flex align-items-center gap-2 py-1 px-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-controls="adminSidebarOffcanvas">
        <i class="bi bi-list fs-5"></i>
        <span>Menu</span>
    </button>
</div>

<!-- Sidebar (Desktop sebagai Col-md-3 biasa, Mobile sebagai Offcanvas Slide) -->
<div class="offcanvas-md offcanvas-start sidebar text-white col-md-3" tabindex="-1" id="adminSidebarOffcanvas" aria-labelledby="adminSidebarOffcanvasLabel" data-bs-scroll="true" data-bs-backdrop="false">
    
    <!-- Header Offcanvas khusus Mobile -->
    <div class="offcanvas-header d-md-none border-bottom border-white-50 p-3">
        <h5 class="offcanvas-title text-white fw-bold d-flex align-items-center gap-2" id="adminSidebarOffcanvasLabel">
            <img src="../assets/img/logo.png" alt="Logo" width="24" height="30">
            DISKOMINFOTIK RIAU
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-label="Close"></button>
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
                <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="dokumen.php" class="nav-link <?= ($current_page == 'dokumen.php' && empty($get_kat) && empty($get_nama) || $current_page == 'edit_dokumen.php') ? 'active' : ''; ?>">
                    <i class="bi bi-folder-fill"></i>
                    <span>Kelola Dokumen</span>
                </a>
            </li>
            <li>
                <a href="tambah_dokumen.php" class="nav-link <?= ($current_page == 'tambah_dokumen.php') ? 'active' : ''; ?>">
                    <i class="bi bi-cloud-arrow-up-fill"></i>
                    <span>Upload Dokumen</span>
                </a>
            </li>

            <!-- Fitur Jenis Dokumen -->
            <li>
                <a href="#jenisDokumenCollapse" class="nav-link d-flex align-items-center justify-content-between <?= (!empty($get_kat) || !empty($get_nama)) ? 'active' : ''; ?>" data-bs-toggle="collapse" role="button" aria-expanded="<?= (!empty($get_kat) || !empty($get_nama)) ? 'true' : 'false'; ?>">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-layers-fill"></i>
                        <span>Jenis Dokumen</span>
                    </div>
                    <i class="bi bi-chevron-down small sub-arrow"></i>
                </a>
                <div class="collapse <?= (!empty($get_kat) || !empty($get_nama)) ? 'show' : ''; ?>" id="jenisDokumenCollapse">
                    <ul class="list-unstyled sidebar-submenu">
                        <?php foreach ($jenis_dokumen_list as $kat_title => $kat_data): 
                            $is_this_kat = ($get_kat === $kat_title);
                            $collapse_id = "sub_" . preg_replace('/[^a-zA-Z0-9]/', '', $kat_title);

                            // Fetch documents created by admin in database for this category
                            $db_docs = [];
                            if (isset($koneksi) && $koneksi) {
                                $kat_db_esc = mysqli_real_escape_string($koneksi, $kat_title);
                                $res = mysqli_query($koneksi, "SELECT DISTINCT judul FROM dokumen WHERE jenis_dokumen='$kat_db_esc' ORDER BY id_dokumen DESC");
                                if ($res) {
                                    while ($r = mysqli_fetch_assoc($res)) {
                                        if (!empty($r['judul']) && !in_array($r['judul'], $db_docs)) {
                                            $db_docs[] = $r['judul'];
                                        }
                                    }
                                }
                            }
                        ?>
                            <li class="sidebar-sub-item">
                                <a href="#<?= $collapse_id ?>" class="sidebar-sub-toggle <?= $is_this_kat ? 'active' : '' ?>" data-bs-toggle="collapse" role="button" aria-expanded="<?= $is_this_kat ? 'true' : 'false' ?>">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi <?= $kat_data['icon'] ?>"></i>
                                        <span><?= htmlspecialchars($kat_title) ?></span>
                                    </span>
                                    <i class="bi bi-chevron-down sub-arrow"></i>
                                </a>
                                <div class="collapse <?= $is_this_kat ? 'show' : '' ?>" id="<?= $collapse_id ?>">
                                    <div class="sidebar-nested-sub">
                                        <a href="dokumen.php?kat_surat=<?= urlencode($kat_title) ?>" class="sidebar-nested-link <?= ($get_kat === $kat_title && empty($get_nama)) ? 'active' : '' ?>" style="font-weight: 600; opacity: 0.95;">
                                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> Semua <?= htmlspecialchars($kat_title) ?>
                                        </a>
                                        <?php if (empty($db_docs)): ?>
                                            <span class="sidebar-nested-link text-white-50 fst-italic py-1" style="font-size: 0.8rem; pointer-events: none;">(Belum ada dokumen)</span>
                                        <?php else: ?>
                                            <?php foreach ($db_docs as $item): 
                                                $is_item_active = ($get_nama === $item && $get_kat === $kat_title);
                                            ?>
                                                <a href="dokumen.php?kat_surat=<?= urlencode($kat_title) ?>&nama_surat=<?= urlencode($item) ?>" class="sidebar-nested-link <?= $is_item_active ? 'active' : '' ?>">
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

            <li>
                <a href="logout.php" class="nav-link <?= ($current_page == 'logout.php') ? 'active' : ''; ?>">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</div>
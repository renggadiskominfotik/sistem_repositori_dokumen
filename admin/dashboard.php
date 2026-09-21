<?php
include("../config/koneksi.php");
require_admin_login();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - DISKOMINFOTIK Riau</title>

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
                <h2 class="admin-page-header m-0">Dashboard Administrator</h2>
                <small class="text-secondary fw-semibold d-none d-md-block">
                    <i class="bi bi-calendar3 text-primary"></i> <?= date('d M Y'); ?>
                </small>
            </div>
            
            <hr class="mb-4 opacity-50">

            <!-- Welcome Greeting Card -->
            <div class="welcome-card mb-5 border-0">
                <h4 class="fw-bold mb-2">Selamat Datang Kembali, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h4>
                <p class="opacity-75 mb-0">Kelola dan atur data repositori dokumen resmi DISKOMINFOTIK Provinsi Riau dengan aman dan cepat.</p>
            </div>

            <!-- Stats Widgets -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <a href="dokumen.php" class="text-decoration-none">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-secondary fw-semibold mb-1">Total Dokumen</h6>
                                    <?php
                                    $jumlah = mysqli_query($koneksi,"SELECT * FROM dokumen");
                                    ?>
                                    <h2 class="fw-extrabold mb-0 text-dark">
                                        <?php echo mysqli_num_rows($jumlah); ?>
                                    </h2>
                                </div>
                                <div class="stat-icon-box">
                                    <i class="bi bi-folder-fill"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<?php include("partials/footer.php"); ?>
</body>
</html>
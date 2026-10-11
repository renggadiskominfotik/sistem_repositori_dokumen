<?php
include("../config/koneksi.php");
require_admin_login();

$id_admin = isset($_SESSION['id_admin']) ? (int)$_SESSION['id_admin'] : 1;
$pesan_sukses = "";
$pesan_error = "";

// Ambil data admin saat ini
$qAdmin = db_query($koneksi, "SELECT * FROM admin WHERE id_admin='$id_admin' LIMIT 1");
$adminData = db_fetch_assoc($qAdmin);

if (!$adminData) {
    // Fallback jika id_admin tidak cocok
    $qAdmin = db_query($koneksi, "SELECT * FROM admin ORDER BY id_admin ASC LIMIT 1");
    $adminData = db_fetch_assoc($qAdmin);
    if ($adminData) {
        $id_admin = (int)$adminData['id_admin'];
    }
}

if (isset($_POST['update_profil'])) {
    $nama = trim(db_real_escape_string($koneksi, $_POST['nama']));
    $username = trim(db_real_escape_string($koneksi, $_POST['username']));
    $pass_lama = trim($_POST['password_lama']);
    $pass_baru = trim($_POST['password_baru']);
    $pass_konfirmasi = trim($_POST['password_konfirmasi']);

    if (empty($nama) || empty($username)) {
        $pesan_error = "Nama dan Username tidak boleh kosong!";
    } else {
        // Cek apakah username sudah dipakai admin lain
        $cekUser = db_query($koneksi, "SELECT id_admin FROM admin WHERE username='$username' AND id_admin != '$id_admin'");
        if (db_num_rows($cekUser) > 0) {
            $pesan_error = "Username '$username' sudah digunakan oleh pengguna lain. Silakan gunakan username berbeda.";
        } else {
            // Jika user ingin mengganti password
            if (!empty($pass_baru)) {
                // Verifikasi password lama
                if (empty($pass_lama)) {
                    $pesan_error = "Masukkan kata sandi lama Anda untuk mengonfirmasi perubahan kata sandi.";
                } else if ($pass_lama !== $adminData['password']) {
                    $pesan_error = "Kata sandi lama yang Anda masukkan salah!";
                } else if (strlen($pass_baru) < 5) {
                    $pesan_error = "Kata sandi baru minimal harus 5 karakter!";
                } else if ($pass_baru !== $pass_konfirmasi) {
                    $pesan_error = "Konfirmasi kata sandi baru tidak cocok!";
                } else {
                    $safe_pass_baru = db_real_escape_string($koneksi, $pass_baru);
                    $upd = db_query($koneksi, "UPDATE admin SET nama='$nama', username='$username', password='$safe_pass_baru' WHERE id_admin='$id_admin'");
                    if ($upd) {
                        $_SESSION['nama'] = $nama;
                        $_SESSION['username'] = $username;
                        setcookie('admin_nama', $nama, time() + 604800, '/');
                        setcookie('admin_user', $username, time() + 604800, '/');
                        $adminData['nama'] = $nama;
                        $adminData['username'] = $username;
                        $adminData['password'] = $pass_baru;
                        $pesan_sukses = "Profil, username, dan kata sandi berhasil diperbarui!";
                    } else {
                        $pesan_error = "Gagal memperbarui data. Silakan coba kembali.";
                    }
                }
            } else {
                // Hanya update nama & username
                $upd = db_query($koneksi, "UPDATE admin SET nama='$nama', username='$username' WHERE id_admin='$id_admin'");
                if ($upd) {
                    $_SESSION['nama'] = $nama;
                    $_SESSION['username'] = $username;
                    setcookie('admin_nama', $nama, time() + 604800, '/');
                    setcookie('admin_user', $username, time() + 604800, '/');
                    $adminData['nama'] = $nama;
                    $adminData['username'] = $username;
                    $pesan_sukses = "Profil dan username berhasil diperbarui!";
                } else {
                    $pesan_error = "Gagal memperbarui data. Silakan coba kembali.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun & Kata Sandi - DISKOMINFOTIK Riau</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

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
                <div>
                    <h2 class="admin-page-header m-0">Pengaturan Akun</h2>
                    <p class="text-secondary small m-0 mt-1">Ubah nama, username, dan kata sandi login administrator</p>
                </div>
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>

            <hr class="mb-4 opacity-50">

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 p-3 mb-4">
                    <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                    <div>
                        <strong class="d-block text-success">Berhasil!</strong>
                        <span class="text-dark small"><?= htmlspecialchars($pesan_sukses); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 p-3 mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-3 text-danger"></i>
                    <div>
                        <strong class="d-block text-danger">Perhatian!</strong>
                        <span class="text-dark small"><?= htmlspecialchars($pesan_error); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
                        <form method="POST" action="">
                            
                            <!-- Bagian 1: Data Identitas -->
                            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-person-badge-fill text-primary"></i>
                                <span>Identitas Pengguna</span>
                            </h5>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Nama Lengkap Administrator</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-person text-primary"></i></span>
                                    <input type="text" name="nama" class="form-control bg-light border-start-0 rounded-end-3 py-2" value="<?= htmlspecialchars($adminData['nama'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark">Username Akun</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-at text-primary"></i></span>
                                    <input type="text" name="username" class="form-control bg-light border-start-0 rounded-end-3 py-2" value="<?= htmlspecialchars($adminData['username'] ?? ''); ?>" required autocomplete="off">
                                </div>
                                <small class="text-muted">Username ini digunakan untuk masuk / login ke panel admin.</small>
                            </div>

                            <hr class="my-4 opacity-50">

                            <!-- Bagian 2: Kata Sandi -->
                            <h5 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-shield-lock-fill text-primary"></i>
                                <span>Ganti Kata Sandi</span>
                            </h5>
                            <p class="text-secondary small mb-4">Kosongkan kolom kata sandi baru jika Anda tidak ingin mengubah kata sandi saat ini.</p>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Kata Sandi Lama</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-key-fill text-primary"></i></span>
                                    <input type="password" name="password_lama" id="password_lama" class="form-control bg-light border-start-0 border-end-0 py-2" placeholder="Wajib diisi hanya jika mengubah kata sandi">
                                    <button class="btn btn-light border border-start-0 rounded-end-3" type="button" onclick="togglePass('password_lama', this)">
                                        <i class="bi bi-eye text-muted"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Kata Sandi Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-lock-fill text-primary"></i></span>
                                    <input type="password" name="password_baru" id="password_baru" class="form-control bg-light border-start-0 border-end-0 py-2" placeholder="Minimal 5 karakter">
                                    <button class="btn btn-light border border-start-0 rounded-end-3" type="button" onclick="togglePass('password_baru', this)">
                                        <i class="bi bi-eye text-muted"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark">Konfirmasi Kata Sandi Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-check2-circle text-primary"></i></span>
                                    <input type="password" name="password_konfirmasi" id="password_konfirmasi" class="form-control bg-light border-start-0 border-end-0 py-2" placeholder="Ulangi kata sandi baru">
                                    <button class="btn btn-light border border-start-0 rounded-end-3" type="button" onclick="togglePass('password_konfirmasi', this)">
                                        <i class="bi bi-eye text-muted"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex gap-2 pt-2">
                                <button type="submit" name="update_profil" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                                    <i class="bi bi-save2-fill"></i>
                                    <span>Simpan Perubahan</span>
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                                    Batal
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- Info Box Bantuan -->
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary-subtle p-3 text-primary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-info-circle-fill fs-4"></i>
                            </div>
                            <h6 class="fw-bold m-0 text-dark">Tips Keamanan</h6>
                        </div>
                        <ul class="text-secondary small ps-3 mb-0" style="line-height: 1.7;">
                            <li>Gunakan kombinasi huruf besar, huruf kecil, dan angka untuk kata sandi yang kuat.</li>
                            <li>Pastikan Anda mengingat username dan kata sandi baru yang telah disimpan.</li>
                            <li>Setelah memperbarui username atau kata sandi, sesi login Anda akan tetap aktif secara otomatis.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<?php include("partials/footer.php"); ?>

<script>
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash-fill text-primary';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye text-muted';
    }
}
</script>
</body>
</html>

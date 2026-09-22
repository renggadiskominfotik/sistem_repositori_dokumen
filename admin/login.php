<?php
include("../config/koneksi.php");

// Jika admin sudah login, langsung ke dashboard
if (is_admin_logged_in()) {
    header("Location: /admin/dashboard.php");
    exit;
}

// Proses login
if (isset($_POST['login'])) {

    $username = db_real_escape_string($koneksi, $_POST['username']);
    $password = db_real_escape_string($koneksi, $_POST['password']);

    $query = db_query(
        $koneksi,
        "SELECT * FROM admin WHERE username='$username' AND password='$password'"
    );

    if (db_num_rows($query) > 0) {

        $data = db_fetch_assoc($query);

        $_SESSION['id_admin'] = $data['id_admin'];
        $_SESSION['nama']     = $data['nama'];
        $_SESSION['username'] = $data['username'];

        setcookie('admin_id', $data['id_admin'], time() + 604800, '/');
        setcookie('admin_nama', $data['nama'], time() + 604800, '/');
        setcookie('admin_user', $data['username'], time() + 604800, '/');

        header("Location: /admin/dashboard.php");
        exit;

    } else {

        echo "<script>
                alert('Username atau Password Salah!');
              </script>";

    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - DISKOMINFOTIK Riau</title>

    <!-- Google Fonts: Playfair Display & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<style>
    /* ===== LOGIN BACKGROUND ===== */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    .login-bg {
        min-height: 100vh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow-x: hidden;
        /* Background foto Pekanbaru */
        background-image: url('../assets/img/bg-pekanbaru.png');
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        padding: 2rem 1rem;
        box-sizing: border-box;
    }

    /* Dark overlay agar form tetap terbaca */
    .login-bg::before {
        content: '';
        position: fixed;
        inset: 0;
        background: linear-gradient(
            135deg,
            rgba(5, 20, 60, 0.35) 0%,
            rgba(10, 40, 100, 0.26) 50%,
            rgba(0, 10, 40, 0.31) 100%
        );
        z-index: 0;
    }

    .login-bg > .container {
        position: relative;
        z-index: 1;
    }

    /* ===== RESPONSIVE CARD ===== */
    .login-card {
        width: 100%;
        max-width: 460px;
        margin: 0 auto;
    }

    /* Kecilkan padding di layar kecil */
    @media (max-width: 576px) {
        .login-bg {
            padding: 1.5rem 0.75rem;
            background-attachment: scroll; /* fixed tidak optimal di mobile */
        }
        .login-card-body {
            padding: 1.5rem !important;
        }
        .login-card-header {
            padding: 2rem 1rem !important;
        }
        .login-card-header h3 {
            font-size: 1.2rem !important;
        }
        .login-back-link {
            font-size: 0.85rem;
        }
    }

    @media (min-width: 576px) and (max-width: 768px) {
        .login-card {
            max-width: 420px;
        }
    }

    /* Subtle glow on card */
    .login-card .card {
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255,255,255,0.06) !important;
        border-radius: 1rem !important;
        backdrop-filter: blur(10px);
    }

    /* Custom Input Group Border & Focus Styling */
    .custom-input-group {
        border: 1.5px solid #cbd5e1;
        border-radius: 0.6rem;
        transition: all 0.2s ease-in-out;
        background-color: #f8fafc;
        overflow: hidden;
    }
    
    .custom-input-group:focus-within {
        border-color: #0d6efd;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
    }

    .custom-input-group .input-group-text {
        background-color: transparent !important;
        border: none !important;
        color: #64748b;
        transition: color 0.2s ease;
    }

    .custom-input-group:focus-within .input-group-text {
        color: #0d6efd !important;
    }

    .custom-input-group .form-control {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        color: #1e293b;
        font-weight: 500;
    }

    .custom-input-group .btn-toggle-eye {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }

    .custom-label {
        font-weight: 600;
        color: #334155;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
    }

    /* Back link styling */
    .login-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #fff !important;
        background: rgba(255, 255, 255, 0.18);
        border: 1.5px solid rgba(255, 255, 255, 0.5);
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-shadow: 0 1px 4px rgba(0,0,0,0.4);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        backdrop-filter: blur(6px);
        transition: all 0.25s ease;
    }
    .login-back-link:hover {
        background: rgba(255, 255, 255, 0.30);
        border-color: rgba(255, 255, 255, 0.85);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        transform: translateY(-1px);
        color: #fff !important;
    }
    .login-back-link:active {
        transform: translateY(0);
    }
</style>

<body class="login-bg">
<!-- Foto background: Ikon Pekanbaru (Patung Sepasang Kekasih) -->

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 login-card">

            <!-- Login Card -->
            <div class="card border-0 overflow-hidden bg-white">
                
                <!-- Card Header with custom design -->
                <div class="login-card-header py-5 px-4 text-center text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #052c65 100%); position: relative;">
                    <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                        <img src="../assets/img/logo.png" alt="Logo Riau" width="50" height="60" style="filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));">
                        <img src="../assets/img/image.png" alt="Logo Diskominfo" width="55" height="55" style="filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15)); object-fit: contain;">
                    </div>
                    <h3 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; letter-spacing: -0.5px;">DISKOMINFOTIK</h3>
                    <p class="mb-0 text-uppercase tracking-wider fw-semibold opacity-75" style="font-size: 11px; letter-spacing: 1.5px;">Provinsi Riau</p>
                    <small class="d-block mt-2 opacity-50">Login Administrator</small>
                </div>

                <!-- Card Body -->
                <div class="login-card-body card-body p-4 p-md-5">
                    <form method="POST">

                        <div class="mb-4">
                            <label class="form-label custom-label">Username</label>
                            <div class="input-group custom-input-group py-1 px-2">
                                <span class="input-group-text py-2 px-3">
                                    <i class="bi bi-person fs-5"></i>
                                </span>
                                <input
                                    type="text"
                                    name="username"
                                    class="form-control py-2"
                                    placeholder="Masukkan username"
                                    required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label custom-label">Password</label>
                            <div class="input-group custom-input-group py-1 px-2">
                                <span class="input-group-text py-2 px-3">
                                    <i class="bi bi-lock fs-5"></i>
                                </span>
                                <input
                                    type="password"
                                    name="password"
                                    id="passwordInput"
                                    class="form-control py-2"
                                    placeholder="Masukkan password"
                                    required>
                                <button
                                    type="button"
                                    class="btn btn-toggle-eye py-2 px-3 text-muted"
                                    id="togglePassword"
                                    style="cursor: pointer;"
                                    title="Tampilkan / Sembunyikan Password">
                                    <i class="bi bi-eye-slash fs-5" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            name="login"
                            class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm mt-3 d-flex align-items-center justify-content-center gap-2 transition"
                            style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border: none;">
                            <i class="bi bi-box-arrow-in-right fs-5"></i>
                            <span>Masuk ke Dashboard</span>
                        </button>

                    </form>
                </div>

            </div>

            <!-- Footer indicator -->
            <div class="text-center mt-4">
                <a href="../visitor/index.php" class="text-decoration-none login-back-link">
                    <i class="bi bi-arrow-left-circle-fill"></i>
                    Kembali ke Portal Pengunjung
                </a>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const toggleIcon = document.getElementById('toggleIcon');

    if (togglePassword && passwordInput && toggleIcon) {
        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

            if (isPassword) {
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
                togglePassword.classList.replace('text-muted', 'text-primary');
            } else {
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
                togglePassword.classList.replace('text-primary', 'text-muted');
            }
        });
    }
});
</script>

</body>
</html>
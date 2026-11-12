<?php
/**
 * Halaman Login Pengguna (Admin & User)
 * StrikeZone Fishing Tackle Marketplace
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// -------------------------------------------------------
// FITUR DARURAT: Reset & Auto-Login Admin
// Akses: /login.php?_fix=strikezone2026
// HAPUS bagian ini setelah berhasil login!
// -------------------------------------------------------
if (isset($_GET['_fix']) && $_GET['_fix'] === 'strikezone2026') {
    $fixEmail  = 'admin@strikezone.com';
    $fixPass   = 'Admin@2026';
    $fixHash   = '$2y$10$.zk7/RNt68xJL5MhBfe6Yu5Jm0rtySDxzeXEHDhEqUibraqPRkXXC';
    $fixNama   = 'Admin StrikeZone';
    $fixMsg    = '';

    if ($conn) {
        // Buat tabel jika belum ada
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `nama` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `no_hp` VARCHAR(20) NOT NULL DEFAULT '',
            `alamat` TEXT DEFAULT NULL,
            `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
            `foto` VARCHAR(255) DEFAULT 'default_user.png',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Hapus admin lama lalu insert ulang
        mysqli_query($conn, "DELETE FROM users WHERE email = '$fixEmail'");
        $ins = mysqli_prepare($conn, "INSERT INTO users (nama,email,password,no_hp,alamat,role) VALUES (?,?,?,?,?,'admin')");
        $hp  = '081234567890'; $al = 'Jakarta, Indonesia';
        mysqli_stmt_bind_param($ins, 'sssss', $fixNama, $fixEmail, $fixHash, $hp, $al);
        mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);

        // Verifikasi lalu auto-login
        $chk = mysqli_prepare($conn, "SELECT id,nama,email,password,role FROM users WHERE email=? LIMIT 1");
        mysqli_stmt_bind_param($chk, 's', $fixEmail);
        mysqli_stmt_execute($chk);
        $usr = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
        mysqli_stmt_close($chk);

        if ($usr && password_verify($fixPass, $usr['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $usr['id'];
            $_SESSION['user_nama']  = $usr['nama'];
            $_SESSION['user_email'] = $usr['email'];
            $_SESSION['user_role']  = $usr['role'];
            set_flash('success', 'Password admin berhasil direset! Selamat datang, ' . $usr['nama'] . '!');
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        } else {
            $fixMsg = '❌ Gagal verifikasi password. Hubungi developer.';
        }
    } else {
        $fixMsg = '❌ Koneksi database gagal: ' . mysqli_connect_error();
    }

    die('<div style="font-family:sans-serif;padding:40px;background:#0b192c;color:#f8fafc;min-height:100vh;">
        <h2 style="color:#f87171">' . htmlspecialchars($fixMsg) . '</h2>
        <a href="' . BASE_URL . '/login.php" style="color:#38bdf8">← Kembali ke Login</a>
    </div>');
}
// -------------------------------------------------------

// Bila sudah login, langsung arahkan ke halaman sesuai role
if (is_logged_in()) {
    header('Location: ' . BASE_URL . (is_admin() ? '/admin/index.php' : '/dashboard.php'));
    exit;
}

$error = '';
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($emailInput === '' || $password === '') {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {

        // ── AKUN ADMIN DARURAT (hardcoded, tidak butuh DB) ──────────────
        $daruratAccounts = [
            'admin@strikezone.com' => [
                'password' => 'admin123',
                'id'       => 1,
                'nama'     => 'Admin StrikeZone',
                'role'     => 'admin',
            ],
            'budi@buyer.com' => [
                'password' => 'admin123',
                'id'       => 3,
                'nama'     => 'Budi Pemancing',
                'role'     => 'user',
            ],
        ];

        if (isset($daruratAccounts[$emailInput]) && $daruratAccounts[$emailInput]['password'] === $password) {
            $acc = $daruratAccounts[$emailInput];
            session_regenerate_id(true);
            $_SESSION['user_id']    = $acc['id'];
            $_SESSION['user_nama']  = $acc['nama'];
            $_SESSION['user_email'] = $emailInput;
            $_SESSION['user_role']  = $acc['role'];
            set_flash('success', 'Selamat datang kembali, ' . $acc['nama'] . '!');
            header('Location: ' . BASE_URL . ($acc['role'] === 'admin' ? '/admin/index.php' : '/dashboard.php'));
            exit;
        }
        // ── AKHIR AKUN DARURAT ───────────────────────────────────────────

        if ($conn) {
            $stmt = mysqli_prepare($conn, "SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $emailInput);
                mysqli_stmt_execute($stmt);
                $res  = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($res);
                mysqli_stmt_close($stmt);

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['user_nama']  = $user['nama'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role']  = $user['role'];
                    set_flash('success', 'Selamat datang kembali, ' . $user['nama'] . '!');
                    header('Location: ' . BASE_URL . ($user['role'] === 'admin' ? '/admin/index.php' : '/dashboard.php'));
                    exit;
                }
            }
        }

        $error = 'Email atau kata sandi salah. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Akun - StrikeZone Fishing Tackle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --primary-light: #38bdf8;
            --dark-ocean: #0b192c;
            --dark-card: #1e293b;
            --text-muted: #94a3b8;
        }
        body {
            background: radial-gradient(circle at 20% 15%, #1e3e62 0%, #0b192c 60%);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-wrapper {
            max-width: 920px;
            width: 100%;
        }
        .login-card {
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
        }
        .login-aside {
            background: linear-gradient(160deg, #0369a1 0%, #0b192c 120%);
            position: relative;
            overflow: hidden;
        }
        .login-aside::after {
            content: '';
            position: absolute;
            top: -40%;
            right: -30%;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, transparent 70%);
        }
        .form-control {
            background-color: #0b192c;
            border: 1px solid #334155;
            color: #f8fafc;
            padding: 0.7rem 0.9rem;
        }
        .form-control:focus {
            background-color: #0b192c;
            border-color: var(--primary);
            color: #f8fafc;
            box-shadow: 0 0 0 0.2rem rgba(2, 132, 199, 0.25);
        }
        .form-control::placeholder { color: #64748b; }
        .input-group-text {
            background-color: #0b192c;
            border: 1px solid #334155;
            color: #64748b;
        }
        .btn-brand {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            border: none;
            font-weight: 700;
        }
        .btn-brand:hover { background: linear-gradient(135deg, #38bdf8, #0369a1); color: #fff; }
        .brand-badge {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 6px 14px;
            border-radius: 30px;
            display: inline-block;
            font-weight: 600;
            letter-spacing: 0.4px;
            font-size: 0.78rem;
        }
        .demo-box {
            background: rgba(2, 132, 199, 0.09);
            border: 1px dashed rgba(56, 189, 248, 0.35);
            border-radius: 14px;
        }
        .demo-box code { color: #fbbf24; }
    </style>
</head>
<body>
    <div class="container py-4 d-flex justify-content-center">
        <div class="login-wrapper">
            <div class="card login-card">
                <div class="row g-0">
                    <!-- Panel Kiri: Branding -->
                    <div class="col-lg-5 login-aside text-white d-flex flex-column justify-content-between p-4 p-md-5">
                        <div>
                            <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="StrikeZone Logo" height="46" class="mb-4">
                            <h3 class="fw-bold mb-2">Portal Akun<br>StrikeZone</h3>
                            <p class="small text-white-50 mb-4">
                                Masuk untuk mengelola katalog alat pancing, memantau pesanan, dan berbelanja perlengkapan memancing favorit Anda.
                            </p>
                            <ul class="list-unstyled small d-flex flex-column gap-2 text-white-50">
                                <li><i class="bi bi-shield-check text-warning me-2"></i>100% Produk Original Bergaransi</li>
                                <li><i class="bi bi-truck me-2 text-info"></i>Pengiriman Seluruh Indonesia</li>
                                <li><i class="bi bi-box-seam me-2 text-success"></i>Gratis Tabung Paralon PVC Guard</li>
                            </ul>
                        </div>
                        <a href="<?= BASE_URL ?>/index.php" class="text-white-50 small text-decoration-none mt-4">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Produk
                        </a>
                    </div>

                    <!-- Panel Kanan: Form Login -->
                    <div class="col-lg-7 p-4 p-md-5">
                        <span class="brand-badge text-info mb-3">
                            <i class="bi bi-water me-1"></i> STRIKEZONE FISHING TACKLE
                        </span>
                        <h4 class="fw-bold mb-1">Masuk ke Akun Anda</h4>
                        <p class="text-secondary small mb-4">Silakan masukkan email dan kata sandi Anda.</p>

                        <?php display_flash(); ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small" role="alert">
                                <i class="bi bi-exclamation-octagon-fill"></i>
                                <div><?= htmlspecialchars($error) ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= BASE_URL ?>/login.php" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="nama@email.com"
                                           value="<?= htmlspecialchars($emailInput) ?>" required autofocus>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-secondary small fw-bold">Kata Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Masukkan kata sandi" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-brand btn-lg w-100 text-white py-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Login Sekarang
                            </button>
                        </form>

                        <p class="text-center text-secondary small mt-3 mb-0">
                            Belum punya akun?
                            <a href="<?= BASE_URL ?>/register.php" class="text-info fw-bold text-decoration-none">Daftar sebagai User</a>
                        </p>

                        <div class="demo-box p-3 mt-4">
                            <div class="small fw-bold text-info mb-2">
                                <i class="bi bi-key-fill me-1"></i> Akun Uji Coba:
                            </div>
                            <div class="row g-2 small text-secondary">
                                <div class="col-sm-6">
                                    <strong class="text-light d-block">Admin</strong>
                                    <code>admin@strikezone.com</code><br>
                                    <code>admin123</code>
                                </div>
                                <div class="col-sm-6">
                                    <strong class="text-light d-block">User</strong>
                                    <code>budi@buyer.com</code><br>
                                    <code>admin123</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-center text-secondary small mt-3 mb-0">
                &copy; <?= date('Y') ?> StrikeZone Fishing Tackle. Hak Cipta Dilindungi.
            </p>
        </div>
    </div>

    <script>
        // Tampilkan / sembunyikan kata sandi
        const toggleBtn = document.getElementById('togglePassword');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const input = document.getElementById('inputPassword');
                const icon = document.getElementById('toggleIcon');
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.classList.toggle('bi-eye', !show);
                icon.classList.toggle('bi-eye-slash', show);
            });
        }
    </script>
</body>
</html>

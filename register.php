<?php
/**
 * Pendaftaran akun pengguna
 * Akun yang dibuat melalui halaman ini selalu memiliki role user.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . (is_admin() ? '/admin/index.php' : '/dashboard.php'));
    exit;
}

if (empty($_SESSION['register_csrf'])) {
    $_SESSION['register_csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$nama = '';
$email = '';
$noHp = '';
$alamat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $noHp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['register_csrf'], $csrfToken)) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } elseif ($nama === '' || strlen($nama) > 100) {
        $error = 'Nama wajib diisi dan maksimal 100 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $error = 'Masukkan alamat email yang valid.';
    } elseif ($noHp === '' || strlen($noHp) > 20) {
        $error = 'Nomor HP wajib diisi dan maksimal 20 karakter.';
    } elseif (strlen($password) < 8) {
        $error = 'Kata sandi minimal 8 karakter.';
    } elseif ($password !== $konfirmasiPassword) {
        $error = 'Konfirmasi kata sandi tidak sama.';
    } else {
        $check = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? LIMIT 1');
        if (!$check) {
            $error = 'Pendaftaran belum dapat diproses. Silakan coba lagi.';
        } else {
            mysqli_stmt_bind_param($check, 's', $email);
            mysqli_stmt_execute($check);
            $existingUser = mysqli_stmt_get_result($check);
            $emailExists = mysqli_fetch_assoc($existingUser);
            mysqli_stmt_close($check);

            if ($emailExists) {
                $error = 'Email tersebut sudah terdaftar. Silakan login atau gunakan email lain.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, 'user')"
                );

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $passwordHash, $noHp, $alamat);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        unset($_SESSION['register_csrf']);
                        set_flash('success', 'Pendaftaran berhasil. Silakan login dengan akun baru Anda.');
                        header('Location: ' . BASE_URL . '/login.php');
                        exit;
                    }

                    $duplicateEmail = mysqli_stmt_errno($stmt) === 1062;
                    mysqli_stmt_close($stmt);
                    $error = $duplicateEmail
                        ? 'Email tersebut sudah terdaftar. Silakan gunakan email lain.'
                        : 'Pendaftaran gagal disimpan. Silakan coba lagi.';
                } else {
                    $error = 'Pendaftaran belum dapat diproses. Silakan coba lagi.';
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
    <title>Daftar Akun - StrikeZone Fishing Tackle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: radial-gradient(circle at 20% 15%, #1e3e62 0%, #0b192c 60%);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .register-card {
            width: 100%;
            max-width: 600px;
            background: rgba(15, 23, 42, 0.94);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
        }
        .form-control {
            background-color: #0b192c;
            border: 1px solid #334155;
            color: #f8fafc;
            padding: 0.7rem 0.9rem;
        }
        .form-control:focus {
            background-color: #0b192c;
            border-color: #0284c7;
            color: #f8fafc;
            box-shadow: 0 0 0 0.2rem rgba(2, 132, 199, 0.25);
        }
        .form-control::placeholder { color: #64748b; }
        .input-group-text {
            background-color: #0b192c;
            border: 1px solid #334155;
            color: #94a3b8;
        }
        .btn-brand {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            border: 0;
            font-weight: 700;
        }
        .btn-brand:hover { background: linear-gradient(135deg, #38bdf8, #0369a1); color: #fff; }
    </style>
</head>
<body>
    <main class="container py-4 d-flex justify-content-center">
        <section class="register-card p-4 p-md-5">
            <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">
                <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="StrikeZone" height="42" class="mb-4">
            </a>
            <div class="mb-4">
                <span class="badge rounded-pill bg-info bg-opacity-25 text-info border border-info border-opacity-50 mb-3">
                    <i class="bi bi-person-plus me-1"></i> AKUN PENGGUNA
                </span>
                <h1 class="h3 fw-bold mb-2">Buat Akun StrikeZone</h1>
                <p class="text-secondary mb-0">Daftar untuk berbelanja dan memantau pesanan Anda.</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/register.php" autocomplete="on">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['register_csrf']) ?>">

                <div class="mb-3">
                    <label for="nama" class="form-label small fw-bold text-secondary">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input id="nama" type="text" name="nama" class="form-control" maxlength="100"
                               placeholder="Nama Anda" value="<?= htmlspecialchars($nama) ?>" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold text-secondary">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input id="email" type="email" name="email" class="form-control" maxlength="100"
                               placeholder="nama@email.com" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="no_hp" class="form-label small fw-bold text-secondary">Nomor HP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input id="no_hp" type="tel" name="no_hp" class="form-control" maxlength="20"
                               placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($noHp) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label small fw-bold text-secondary">Alamat <span class="fw-normal">(opsional)</span></label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="2" placeholder="Alamat pengiriman"><?= htmlspecialchars($alamat) ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label small fw-bold text-secondary">Kata Sandi</label>
                        <input id="password" type="password" name="password" class="form-control"
                               minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
                    </div>
                    <div class="col-md-6">
                        <label for="konfirmasi_password" class="form-label small fw-bold text-secondary">Ulangi Kata Sandi</label>
                        <input id="konfirmasi_password" type="password" name="konfirmasi_password" class="form-control"
                               minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-brand btn-lg w-100 text-white">
                    <i class="bi bi-person-check me-2"></i>Daftar Akun User
                </button>
            </form>

            <p class="text-center text-secondary small mt-4 mb-0">
                Sudah punya akun?
                <a href="<?= BASE_URL ?>/login.php" class="text-info fw-bold text-decoration-none">Login di sini</a>
            </p>
        </section>
    </main>
</body>
</html>

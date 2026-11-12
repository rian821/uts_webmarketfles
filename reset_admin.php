<?php
/**
 * Script Reset Password Admin - StrikeZone
 * HAPUS FILE INI setelah berhasil digunakan!
 */
require_once __DIR__ . '/config/database.php';

$secretKey = $_GET['key'] ?? '';
$validKey  = 'strikezone2026reset'; // kunci aman

if ($secretKey !== $validKey) {
    die('<h2 style="color:red;font-family:sans-serif;">❌ Akses ditolak. Tambahkan ?key=strikezone2026reset di URL.</h2>');
}

$action  = $_POST['action'] ?? '';
$message = '';
$msgType = 'info';

// --- Cek koneksi database ---
if (!$conn) {
    die('<h2 style="color:red;font-family:sans-serif;">❌ Koneksi database gagal: ' . htmlspecialchars(mysqli_connect_error()) . '</h2>');
}

// --- Proses reset password ---
if ($action === 'reset') {
    $email       = trim($_POST['email'] ?? 'admin@strikezone.com');
    $newPassword = trim($_POST['new_password'] ?? 'admin123');

    if (strlen($newPassword) < 6) {
        $message = '❌ Password minimal 6 karakter.';
        $msgType = 'danger';
    } else {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE email = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ss', $hash, $email);
            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                $message = "✅ Password untuk <strong>{$email}</strong> berhasil direset ke: <code>{$newPassword}</code>";
                $msgType = 'success';
            } else {
                $message = "⚠️ Tidak ada baris yang diupdate. Pastikan email <strong>{$email}</strong> terdaftar di database.";
                $msgType = 'warning';
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = '❌ Query gagal: ' . htmlspecialchars(mysqli_error($conn));
            $msgType = 'danger';
        }
    }
}

// --- Proses insert admin jika belum ada ---
if ($action === 'insert_admin') {
    $email    = 'admin@strikezone.com';
    $nama     = 'Admin StrikeZone Fishing';
    $password = 'admin123';
    $hash     = password_hash($password, PASSWORD_BCRYPT);
    $no_hp    = '081234567890';
    $alamat   = 'Ruko Sentra Bahari No. 12, Jakarta Utara';
    $role     = 'admin';

    // Cek dulu apakah sudah ada
    $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($checkStmt, 's', $email);
    mysqli_stmt_execute($checkStmt);
    $checkRes = mysqli_stmt_get_result($checkStmt);
    $exists   = mysqli_fetch_assoc($checkRes);
    mysqli_stmt_close($checkStmt);

    if ($exists) {
        // Update saja
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, role = 'admin' WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $hash, $email);
        mysqli_stmt_execute($stmt);
        $message = "✅ Admin sudah ada, password direset ke <code>admin123</code> dan role dikonfirmasi sebagai <code>admin</code>.";
        $msgType = 'success';
        mysqli_stmt_close($stmt);
    } else {
        // Insert baru
        $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssss', $nama, $email, $hash, $no_hp, $alamat, $role);
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Akun admin <strong>{$email}</strong> berhasil dibuat dengan password <code>{$password}</code>.";
            $msgType = 'success';
        } else {
            $message = '❌ Gagal insert: ' . htmlspecialchars(mysqli_error($conn));
            $msgType = 'danger';
        }
        mysqli_stmt_close($stmt);
    }
}

// --- Ambil data users saat ini ---
$users = [];
$res = mysqli_query($conn, "SELECT id, nama, email, role, created_at FROM users ORDER BY id ASC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password - StrikeZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #0b192c; color: #f8fafc; font-family: system-ui, sans-serif; }
        .card { background: #1e293b; border: 1px solid #334155; }
        code { color: #fbbf24; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4 rounded-4 shadow-lg">
                <h4 class="fw-bold text-info mb-1"><i class="bi bi-shield-lock me-2"></i>Reset Password Admin</h4>
                <p class="text-secondary small mb-4">StrikeZone Fishing Tackle &mdash; Tool Darurat</p>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $msgType ?> py-2"><?= $message ?></div>
                <?php endif; ?>

                <!-- Form Reset Password -->
                <form method="POST" action="?key=<?= htmlspecialchars($secretKey) ?>" class="mb-4">
                    <input type="hidden" name="action" value="reset">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-secondary">Email</label>
                            <input type="email" name="email" class="form-control bg-dark text-light border-secondary"
                                   value="admin@strikezone.com">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-secondary">Password Baru</label>
                            <input type="text" name="new_password" class="form-control bg-dark text-light border-secondary"
                                   value="admin123">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="bi bi-key-fill me-1"></i> Reset Password
                    </button>
                </form>

                <!-- Tombol Insert/Fix Admin -->
                <form method="POST" action="?key=<?= htmlspecialchars($secretKey) ?>" class="mb-4">
                    <input type="hidden" name="action" value="insert_admin">
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-person-fill-add me-1"></i> Fix / Buat Akun Admin (admin@strikezone.com / admin123)
                    </button>
                    <div class="text-secondary small mt-1">Jika akun admin belum ada, tombol ini akan membuatnya. Jika sudah ada, hanya mereset password.</div>
                </form>

                <!-- Tabel Users -->
                <h6 class="text-info fw-bold mt-3"><i class="bi bi-people-fill me-1"></i> Daftar Users di Database</h6>
                <?php if (empty($users)): ?>
                    <div class="alert alert-warning small">⚠️ Tabel users kosong atau tabel belum ada.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-sm table-bordered small">
                            <thead class="table-secondary text-dark">
                                <tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Dibuat</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['nama']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'info' ?>"><?= $u['role'] ?></span></td>
                                    <td><?= $u['created_at'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="alert alert-danger small mt-3 py-2">
                    <i class="bi bi-trash-fill me-1"></i>
                    <strong>PENTING:</strong> Hapus file <code>reset_admin.php</code> dari server setelah selesai digunakan!
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

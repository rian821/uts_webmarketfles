<?php
/**
 * FIX ADMIN - StrikeZone Fishing Tackle
 * Script darurat: buat/reset akun admin baru
 * ⚠️ HAPUS FILE INI setelah selesai!
 */

require_once __DIR__ . '/config/database.php';

// =============================================
//  KONFIGURASI AKUN ADMIN BARU
// =============================================
$NEW_EMAIL    = 'superadmin@strikezone.com';
$NEW_PASSWORD = 'Strike@2026';
$NEW_NAMA     = 'Super Admin StrikeZone';
$NEW_HP       = '081200000001';
$NEW_ALAMAT   = 'Jakarta, Indonesia';
// =============================================

$results = [];

if (!$conn) {
    die('<div style="font-family:sans-serif;padding:30px;background:#1e293b;color:#f87171;min-height:100vh;">
        <h2>❌ Koneksi Database Gagal</h2>
        <p>' . htmlspecialchars(mysqli_connect_error()) . '</p>
    </div>');
}

// --- Jalankan otomatis saat halaman dibuka ---

// 1. Buat tabel users jika belum ada
$createTable = "CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $createTable);
$results[] = ['label' => 'Cek/Buat Tabel users', 'status' => 'ok', 'msg' => 'Tabel users siap.'];

// 2. Generate hash password
$hashBaru = password_hash($NEW_PASSWORD, PASSWORD_BCRYPT);
$results[] = ['label' => 'Generate Hash Password', 'status' => 'ok', 'msg' => 'Hash bcrypt berhasil dibuat.'];

// 3. Hapus dulu akun lama admin@strikezone.com jika ada (bersihkan)
$delOld = mysqli_prepare($conn, "DELETE FROM users WHERE email = 'admin@strikezone.com'");
mysqli_stmt_execute($delOld);
$delOldRows = mysqli_stmt_affected_rows($delOld);
mysqli_stmt_close($delOld);
$results[] = ['label' => 'Hapus akun lama (admin@strikezone.com)', 'status' => 'ok',
              'msg' => $delOldRows > 0 ? "Dihapus ($delOldRows baris)." : 'Tidak ada / sudah bersih.'];

// 4. Hapus akun superadmin lama jika sudah ada
$delNew = mysqli_prepare($conn, "DELETE FROM users WHERE email = ?");
mysqli_stmt_bind_param($delNew, 's', $NEW_EMAIL);
mysqli_stmt_execute($delNew);
mysqli_stmt_close($delNew);

// 5. Insert akun admin BARU
$stmt = mysqli_prepare($conn,
    "INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, 'admin')"
);
mysqli_stmt_bind_param($stmt, 'sssss', $NEW_NAMA, $NEW_EMAIL, $hashBaru, $NEW_HP, $NEW_ALAMAT);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    $results[] = ['label' => 'Insert Akun Admin Baru', 'status' => 'ok',
                  'msg' => "Berhasil! ID user = $newId"];
} else {
    $results[] = ['label' => 'Insert Akun Admin Baru', 'status' => 'err',
                  'msg' => 'GAGAL: ' . htmlspecialchars(mysqli_error($conn))];
}
mysqli_stmt_close($stmt);

// 6. Verifikasi — ambil kembali dari DB dan cek password_verify
$check = mysqli_prepare($conn, "SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($check, 's', $NEW_EMAIL);
mysqli_stmt_execute($check);
$res  = mysqli_stmt_get_result($check);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($check);

if ($user && password_verify($NEW_PASSWORD, $user['password'])) {
    $results[] = ['label' => 'Verifikasi password_verify()', 'status' => 'ok',
                  'msg' => '✅ Password cocok! Login PASTI berhasil.'];
} else {
    $results[] = ['label' => 'Verifikasi password_verify()', 'status' => 'err',
                  'msg' => '❌ Password TIDAK cocok — ada masalah serius di server.'];
}

// 7. Daftar semua user sekarang
$allUsers = [];
$resAll = mysqli_query($conn, "SELECT id, nama, email, role, created_at FROM users ORDER BY id ASC");
if ($resAll) {
    while ($row = mysqli_fetch_assoc($resAll)) $allUsers[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Fix Admin - StrikeZone</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  body { background:#0b192c; color:#f8fafc; font-family:system-ui,sans-serif; min-height:100vh; }
  .card { background:#1e293b; border:1px solid #334155; border-radius:16px; }
  code  { color:#fbbf24; }
  .cred-box { background:#0f2744; border:2px solid #0ea5e9; border-radius:12px; }
</style>
</head>
<body>
<div class="container py-5">
<div class="row justify-content-center">
<div class="col-lg-8">

  <div class="card p-4 shadow-lg mb-4">
    <h4 class="text-info fw-bold mb-1"><i class="bi bi-wrench-adjustable-circle me-2"></i>Fix Admin &mdash; StrikeZone</h4>
    <p class="text-secondary small">Script dijalankan otomatis. Semua langkah ditampilkan di bawah.</p>

    <!-- Log proses -->
    <h6 class="text-warning fw-bold mt-3 mb-2"><i class="bi bi-terminal me-1"></i>Log Proses:</h6>
    <ul class="list-unstyled small">
      <?php foreach ($results as $r): ?>
      <li class="mb-1">
        <span class="badge bg-<?= $r['status'] === 'ok' ? 'success' : 'danger' ?> me-2">
          <?= $r['status'] === 'ok' ? '✓ OK' : '✗ ERR' ?>
        </span>
        <strong><?= htmlspecialchars($r['label']) ?>:</strong>
        <?= $r['msg'] ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Kredensial Baru -->
  <div class="cred-box p-4 mb-4 text-center">
    <div class="mb-2"><i class="bi bi-person-badge-fill text-info fs-1"></i></div>
    <h5 class="fw-bold text-white mb-3">🎉 Gunakan Kredensial Baru Ini untuk Login:</h5>
    <div class="row g-3 justify-content-center">
      <div class="col-sm-6">
        <div class="bg-dark rounded-3 p-3">
          <div class="text-secondary small mb-1">📧 Email</div>
          <code class="fs-6"><?= htmlspecialchars($NEW_EMAIL) ?></code>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="bg-dark rounded-3 p-3">
          <div class="text-secondary small mb-1">🔑 Password</div>
          <code class="fs-6"><?= htmlspecialchars($NEW_PASSWORD) ?></code>
        </div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-lg mt-4 px-5 fw-bold">
      <i class="bi bi-box-arrow-in-right me-2"></i>Coba Login Sekarang
    </a>
  </div>

  <!-- Tabel semua user -->
  <div class="card p-4">
    <h6 class="text-info fw-bold mb-3"><i class="bi bi-people-fill me-1"></i>Semua Akun di Database:</h6>
    <?php if (empty($allUsers)): ?>
      <div class="alert alert-warning small">⚠️ Tidak ada user di database.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-dark table-sm table-bordered small mb-0">
        <thead class="table-secondary text-dark">
          <tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Dibuat</th></tr>
        </thead>
        <tbody>
          <?php foreach ($allUsers as $u): ?>
          <tr <?= $u['email'] === $NEW_EMAIL ? 'class="table-success text-dark"' : '' ?>>
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

    <div class="alert alert-danger small mt-3 mb-0 py-2">
      <i class="bi bi-exclamation-triangle-fill me-1"></i>
      <strong>WAJIB:</strong> Hapus file <code>fix_admin.php</code> dari server setelah berhasil login!
    </div>
  </div>

</div>
</div>
</div>
</body>
</html>

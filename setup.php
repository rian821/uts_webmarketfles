<?php
/**
 * Setup Otomatis Database Marketplace Alat Pancing
 * Menyiapkan Database, Tabel, dan Data Awal secara Instan
 */

$hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = ($hostName === 'localhost' || $hostName === '127.0.0.1' || strpos($hostName, 'localhost:') !== false);

$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$appDir  = str_replace('\\', '/', realpath(__DIR__));
$baseDir = str_replace($docRoot, '', $appDir);
$baseDir = '/' . trim($baseDir, '/');
if ($baseDir === '/') {
    $baseDir = '';
}

// Konfigurasi Database
if ($isLocal) {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'db_pancing_marketplace';
} else {
    // Pada InfinityFree nama database sudah disediakan di vPanel
    $db_host = 'sql300.infinityfree.com';
    $db_user = 'if0_38000000';
    $db_pass = 'KATA_SANDI_ANDA';
    $db_name = 'if0_38000000_pancing';
}

$status = null;
$message = '';
$logs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
    // 1. Hubungkan ke MySQL Server
    $link = @mysqli_connect($db_host, $db_user, $db_pass);
    if (!$link) {
        $status = 'error';
        $message = 'Gagal terhubung ke MySQL Server: ' . mysqli_connect_error();
        $logs[] = 'Koneksi ke MySQL Server gagal. Periksa apakah MySQL di XAMPP sudah berjalan.';
    } else {
        // 2. Buat Database jika di lokal
        if ($isLocal) {
            $sqlCreateDb = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            if (mysqli_query($link, $sqlCreateDb)) {
                $logs[] = "Database `$db_name` berhasil dibuat / sudah ada.";
            } else {
                $logs[] = "Gagal membuat database: " . mysqli_error($link);
            }
        }

        // 3. Pilih Database
        if (!mysqli_select_db($link, $db_name)) {
            $status = 'error';
            $message = "Gagal memilih database `$db_name`: " . mysqli_error($link);
            $logs[] = "Tidak dapat mengakses database `$db_name`.";
        } else {
            // 4. Baca file database.sql
            $sqlFile = __DIR__ . '/database.sql';
            if (!file_exists($sqlFile)) {
                $status = 'error';
                $message = "File database.sql tidak ditemukan di {$sqlFile}.";
            } else {
                $sqlContent = file_get_contents($sqlFile);

                // Matikan mode exception agar error SQL dapat ditangkap & dilaporkan
                mysqli_report(MYSQLI_REPORT_OFF);

                // Eksekusi multi query
                if (mysqli_multi_query($link, $sqlContent)) {
                    $sqlError = '';
                    do {
                        // Store first result set
                        if ($result = mysqli_store_result($link)) {
                            mysqli_free_result($result);
                        }
                        if (mysqli_errno($link) != 0) {
                            $sqlError = mysqli_error($link);
                            break;
                        }
                    } while (mysqli_more_results($link) && mysqli_next_result($link));

                    if ($sqlError === '') {
                        $status = 'success';
                        $message = "Instalasi database `$db_name` berhasil diselesaikan!";
                        $logs[] = "Struktur tabel berhasil dibuat (users, categories, products, orders, order_items, product_reviews).";
                        $logs[] = "Data awal produk alat pancing, kategori, dan pesanan uji coba telah ditambahkan.";
                    } else {
                        $status = 'error';
                        $message = "Terjadi kesalahan saat mengeksekusi skrip SQL: " . $sqlError;
                        $logs[] = "Periksa kembali isi file database.sql karena skrip berhenti di tengah proses.";
                    }
                } else {
                    $status = 'error';
                    $message = "Terjadi kesalahan saat mengeksekusi skrip SQL: " . mysqli_error($link);
                }
            }
        }
        mysqli_close($link);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - StrikeZone Fishing Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at 50% 20%, #1e3e62 0%, #0b192c 100%);
            color: #f8fafc;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
        }
        .setup-card {
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }
        .code-box {
            background: #020617;
            border: 1px solid #1e293b;
            border-radius: 10px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="setup-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-25 text-info rounded-circle p-3 mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-water fs-1"></i>
                        </div>
                        <h2 class="fw-bold mb-1">STRIKEZONE FISHING</h2>
                        <p class="text-secondary">Pusat Setup Otomatis Database Marketplace Alat Pancing</p>
                    </div>

                    <?php if ($status === 'success'): ?>
                        <div class="alert alert-success d-flex align-items-start gap-3 p-3">
                            <i class="bi bi-check-circle-fill fs-3 text-success flex-shrink-0"></i>
                            <div>
                                <h5 class="fw-bold mb-1">Setup Berhasil Sempurna!</h5>
                                <p class="mb-2"><?= htmlspecialchars($message) ?></p>
                                <ul class="mb-0 ps-3 small">
                                    <?php foreach ($logs as $log): ?>
                                        <li><?= htmlspecialchars($log) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="card bg-dark border-secondary p-3 mb-4">
                            <h6 class="fw-bold text-info mb-2"><i class="bi bi-key-fill me-2"></i>Akun Login Penjual / Admin Default:</h6>
                            <div class="row g-2 small text-secondary">
                                <div class="col-md-6">
                                    <div class="p-2 bg-black rounded">
                                        <strong>Admin / Penjual:</strong><br>
                                        Email: <code class="text-warning">admin@strikezone.com</code><br>
                                        Password: <code class="text-warning">admin123</code>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2 bg-black rounded">
                                        <strong>Pembeli Uji Coba:</strong><br>
                                        Email: <code class="text-info">budi@buyer.com</code><br>
                                        Password: <code class="text-info">admin123</code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="<?= $baseDir ?>/index.php" class="btn btn-primary btn-lg px-4 fw-bold">
                                <i class="bi bi-shop me-2"></i> Buka Marketplace
                            </a>
                            <a href="<?= $baseDir ?>/admin/index.php" class="btn btn-outline-info btn-lg px-4 fw-bold">
                                <i class="bi bi-speedometer2 me-2"></i> Masuk Admin & Kelola Produk
                            </a>
                        </div>

                    <?php else: ?>
                        <?php if ($status === 'error'): ?>
                            <div class="alert alert-danger d-flex align-items-start gap-3 p-3 mb-4">
                                <i class="bi bi-exclamation-triangle-fill fs-3 text-danger flex-shrink-0"></i>
                                <div>
                                    <h5 class="fw-bold mb-1">Gagal Menjalankan Setup</h5>
                                    <p class="mb-2"><?= htmlspecialchars($message) ?></p>
                                    <ul class="mb-0 ps-3 small">
                                        <?php foreach ($logs as $log): ?>
                                            <li><?= htmlspecialchars($log) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card bg-dark border-secondary p-3 mb-4">
                            <h6 class="text-info fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Informasi Target Database:</h6>
                            <div class="row g-2 small text-light">
                                <div class="col-sm-6">Host: <code><?= htmlspecialchars($db_host) ?></code></div>
                                <div class="col-sm-6">User: <code><?= htmlspecialchars($db_user) ?></code></div>
                                <div class="col-sm-6">Nama Database: <code><?= htmlspecialchars($db_name) ?></code></div>
                                <div class="col-sm-6">Tipe: <code>MySQLi (MariaDB/MySQL)</code></div>
                            </div>
                        </div>

                        <p class="text-secondary small mb-4">
                            Tombol di bawah ini akan membuat database baru, membuat struktur 5+ tabel relasional (users, categories, products, orders, order_items, reviews), serta memasukkan katalog produk pancing populer (Shimano, Daiwa, Rapala, Ryobi, Duraking).
                        </p>

                        <form method="POST">
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow">
                                <i class="bi bi-play-circle-fill me-2"></i> Mulai Inisialisasi Database Sekarang
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4 pt-3 border-top border-secondary">
                        <small class="text-secondary">StrikeZone Fishing Tackle &copy; <?= date('Y') ?> - Sistem Marketplace Tradisional PHP Native</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

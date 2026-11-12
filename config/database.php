<?php
/**
 * Konfigurasi Database & Pengaturan Aplikasi Marketplace Alat Pancing
 * Kompatibel dengan: XAMPP Localhost & Hosting InfinityFree
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Deteksi Lingkungan Server (Local vs InfinityFree / Live)
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = ($httpHost === 'localhost' || $httpHost === '127.0.0.1' || strpos($httpHost, 'localhost:') !== false);

// 2. Tentukan Direktori Basis (BASE_URL) secara Otomatis & Dinamis
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$appDir  = str_replace('\\', '/', realpath(dirname(__DIR__)));
$baseDir = str_replace($docRoot, '', $appDir);
$baseDir = '/' . trim($baseDir, '/');
if ($baseDir === '/') {
    $baseDir = '';
}
define('BASE_URL', $baseDir);

// 3. Konfigurasi Kredensial MySQL
if ($isLocal) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'db_pancing_marketplace');
} else {
    // Kredensial InfinityFree (Sesuaikan dengan data dari vPanel Anda)
    define('DB_HOST', 'sql300.infinityfree.com'); // Ganti dengan MySQL Hostname InfinityFree
    define('DB_USER', 'if0_38000000');           // Ganti dengan MySQL Username InfinityFree
    define('DB_PASS', 'KATA_SANDI_ANDA');        // Ganti dengan MySQL Password InfinityFree
    define('DB_NAME', 'if0_38000000_pancing');   // Ganti dengan Database Name InfinityFree
}

// 4. Inisialisasi Koneksi MySQLi
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 5. Cek Koneksi & Penanganan Ramah Pengguna
if (!$conn) {
    $dbError = mysqli_connect_error();
    $currentScript = $_SERVER['PHP_SELF'] ?? '';

    // Jika bukan sedang membuka file setup.php, tampilkan petunjuk ramah
    if (strpos($currentScript, 'setup.php') === false) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Koneksi Database Belum Dikonfigurasi - StrikeZone Fishing</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
            <style>
                body {
                    background: radial-gradient(circle at top, #1e3e62 0%, #0b192c 100%);
                    color: #f1f5f9;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
                }
                .card-setup {
                    background: rgba(15, 23, 42, 0.9);
                    backdrop-filter: blur(12px);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                }
                .brand-badge {
                    background: linear-gradient(135deg, #0ea5e9, #0284c7);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 30px;
                    display: inline-block;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                }
            </style>
        </head>
        <body>
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">
                        <div class="card card-setup p-4 p-md-5 shadow-24 text-center">
                            <div class="mb-3">
                                <span class="brand-badge mb-3">
                                    <i class="bi bi-water me-2"></i>STRIKEZONE FISHING TACKLE
                                </span>
                            </div>
                            <div class="my-3">
                                <i class="bi bi-database-fill-slash text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="fw-bold mb-2">Database Belum Siap / Belum Terkoneksi</h2>
                            <p class="text-secondary mb-4">
                                Aplikasi belum dapat terhubung ke database <code><?= htmlspecialchars(DB_NAME) ?></code> di server <code><?= htmlspecialchars(DB_HOST) ?></code>.
                                <br><small class="text-danger fw-semibold">(Pesan: <?= htmlspecialchars($dbError) ?>)</small>
                            </p>

                            <div class="alert alert-dark text-start border-secondary p-3 mb-4">
                                <h6 class="fw-bold text-info mb-2"><i class="bi bi-lightbulb-fill me-2"></i>Panduan Cepat Menjalankan:</h6>
                                <ol class="mb-0 ps-3 small text-light opacity-90">
                                    <li>Buka <strong>XAMPP Control Panel</strong> dan pastikan modul <strong>Apache</strong> serta <strong>MySQL</strong> berstatus <span class="badge bg-success">Running</span>.</li>
                                    <li>Jalankan fitur <strong>Instalasi Database Otomatis</strong> dengan menekan tombol biru di bawah. Sistem akan membuat database <code><?= htmlspecialchars(DB_NAME) ?></code> dan mengisi data katalog produk alat pancing secara otomatis.</li>
                                    <li>Atau Anda juga dapat mengimpor file <code>database.sql</code> secara manual melalui <a href="http://localhost/phpmyadmin" target="_blank" class="text-info fw-bold">phpMyAdmin</a>.</li>
                                </ol>
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                <a href="<?= BASE_URL ?>/setup.php" class="btn btn-primary btn-lg px-4 fw-bold shadow">
                                    <i class="bi bi-magic me-2"></i> Jalankan Setup Database Otomatis
                                </a>
                                <a href="http://localhost/phpmyadmin" target="_blank" class="btn btn-outline-light btn-lg px-4">
                                    <i class="bi bi-box-arrow-up-right me-2"></i> Buka phpMyAdmin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
} else {
    // Set charset UTF-8
    mysqli_set_charset($conn, 'utf8mb4');
}

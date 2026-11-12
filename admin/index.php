<?php
/**
 * Dashboard Admin
 * StrikeZone Fishing Tackle Marketplace
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Hanya admin yang boleh mengakses panel ini
require_admin();

// Ambil Statistik
$totalProduk = 0;
$totalPesanan = 0;
$totalPendapatan = 0;
$totalStokRendah = 0;

$qProd = mysqli_query($conn, "SELECT COUNT(*) as jml, SUM(CASE WHEN stok <= 5 THEN 1 ELSE 0 END) as low_stock FROM products");
if ($qProd && $row = mysqli_fetch_assoc($qProd)) {
    $totalProduk = (int)$row['jml'];
    $totalStokRendah = (int)$row['low_stock'];
}

$qOrd = mysqli_query($conn, "SELECT COUNT(*) as jml, SUM(total_bayar) as omset FROM orders");
if ($qOrd && $row = mysqli_fetch_assoc($qOrd)) {
    $totalPesanan = (int)$row['jml'];
    $totalPendapatan = (float)$row['omset'];
}

// Ambil 5 Pesanan Terbaru
$recentOrders = [];
$qRecent = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC LIMIT 5");
if ($qRecent) {
    while ($r = mysqli_fetch_assoc($qRecent)) {
        $recentOrders[] = $r;
    }
}

// Ambil 5 Produk dengan Penjualan Terbanyak
$topProducts = [];
$qTop = mysqli_query($conn, "SELECT p.*, c.nama_kategori FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.terjual DESC LIMIT 5");
if ($qTop) {
    while ($r = mysqli_fetch_assoc($qTop)) {
        $topProducts[] = $r;
    }
}

// Produk Best Seller untuk etalase (diutamakan Shimano Stradic)
$bestSeller = null;
$qBest = mysqli_query($conn, "SELECT p.*, c.nama_kategori FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.nama_produk LIKE '%Stradic%' ORDER BY p.terjual DESC LIMIT 1");
if ($qBest) {
    $bestSeller = mysqli_fetch_assoc($qBest);
}
if (!$bestSeller) {
    $qBest = mysqli_query($conn, "SELECT p.*, c.nama_kategori FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.terjual DESC LIMIT 1");
    if ($qBest) {
        $bestSeller = mysqli_fetch_assoc($qBest);
    }
}

$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header Dashboard -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="bi bi-speedometer2 text-info me-2"></i>Dashboard Admin
        </h2>
        <p class="text-secondary small mb-0">Kelola inventaris perlengkapan pancing, pantau pesanan dan transaksi toko</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/produk-tambah.php" class="btn btn-primary fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
        </a>
        <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-outline-info fw-semibold">
            <i class="bi bi-receipt me-1"></i> Kelola Pesanan
        </a>
    </div>
</div>

<!-- Navigasi Tab Admin -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-primary btn-sm px-3 rounded-pill">
        <i class="bi bi-graph-up me-1"></i> Ringkasan Toko
    </a>
    <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill text-light">
        <i class="bi bi-box-seam me-1"></i> Kelola Produk (CRUD)
    </a>
    <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill text-light">
        <i class="bi bi-truck me-1"></i> Daftar Transaksi Pesanan
    </a>
</div>

<!-- Kartu Statistik Utama -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Total Produk</span>
                <span class="bg-primary bg-opacity-25 text-info p-2 rounded-3">
                    <i class="bi bi-box-seam fs-4"></i>
                </span>
            </div>
            <h3 class="fw-bold text-white mb-0"><?= number_format($totalProduk) ?></h3>
            <small class="text-secondary">Tersedia di katalog toko</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Total Pesanan</span>
                <span class="bg-success bg-opacity-25 text-success p-2 rounded-3">
                    <i class="bi bi-bag-check fs-4"></i>
                </span>
            </div>
            <h3 class="fw-bold text-white mb-0"><?= number_format($totalPesanan) ?></h3>
            <small class="text-secondary">Transaksi pemesanan masuk</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Total Omset</span>
                <span class="bg-info bg-opacity-25 text-primary-light p-2 rounded-3">
                    <i class="bi bi-cash-stack fs-4"></i>
                </span>
            </div>
            <h3 class="fw-bold text-info mb-0"><?= format_rupiah($totalPendapatan) ?></h3>
            <small class="text-secondary">Akumulasi penjualan</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Stok Kritis</span>
                <span class="bg-warning bg-opacity-25 text-warning p-2 rounded-3">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </span>
            </div>
            <h3 class="fw-bold text-warning mb-0"><?= number_format($totalStokRendah) ?></h3>
            <small class="text-secondary">Produk sisa &le; 5 unit</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Tabel Pesanan Terbaru -->
    <div class="col-lg-7">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0">
                    <i class="bi bi-receipt text-info me-2"></i>Pesanan Pemesanan Terbaru
                </h5>
                <a href="<?= BASE_URL ?>/admin/pesanan.php" class="text-info small text-decoration-none">
                    Lihat Semua &rarr;
                </a>
            </div>

            <?php if (empty($recentOrders)): ?>
                <p class="text-secondary small my-4 text-center">Belum ada transaksi pemesanan.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Pembeli</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $ro): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>/invoice.php?inv=<?= $ro['nomor_invoice'] ?>" class="text-info fw-bold text-decoration-none">
                                            <?= htmlspecialchars($ro['nomor_invoice']) ?>
                                        </a>
                                        <div class="text-secondary" style="font-size: 0.72rem;"><?= date('d/m/Y H:i', strtotime($ro['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <strong class="text-light small d-block"><?= htmlspecialchars($ro['nama_lengkap']) ?></strong>
                                        <span class="text-secondary" style="font-size: 0.75rem;"><?= htmlspecialchars($ro['kota']) ?></span>
                                    </td>
                                    <td class="text-light small fw-bold">
                                        <?= format_rupiah($ro['total_bayar']) ?>
                                    </td>
                                    <td>
                                        <?php if ($ro['status'] === 'selesai'): ?>
                                            <span class="badge bg-success bg-opacity-25 text-success small">Selesai</span>
                                        <?php elseif ($ro['status'] === 'diproses'): ?>
                                            <span class="badge bg-info bg-opacity-25 text-info small">Diproses</span>
                                        <?php elseif ($ro['status'] === 'dikirim'): ?>
                                            <span class="badge bg-primary bg-opacity-25 text-light small">Dikirim</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-25 text-warning small">Menunggu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/invoice.php?inv=<?= $ro['nomor_invoice'] ?>" class="btn btn-outline-info btn-sm p-1" title="Buka Nota">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kolom Kanan: Best Seller & Produk Terlaris -->
    <div class="col-lg-5">
        <!-- Best Seller -->
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold text-white mb-3">
                <i class="bi bi-trophy-fill text-warning me-2"></i>Best Seller
            </h5>
            <?php if ($bestSeller): ?>
                <div class="text-center mb-3">
                    <img src="<?= get_product_image_url($bestSeller['gambar']) ?>" alt="<?= htmlspecialchars($bestSeller['nama_produk']) ?>"
                         class="img-fluid rounded-3 border border-secondary" style="width: 100%; max-height: 240px; object-fit: cover;">
                </div>
                <p class="fw-bold text-warning mb-3">
                    <i class="bi bi-trophy-fill me-1"></i>Best Seller: <?= htmlspecialchars($bestSeller['nama_produk']) ?>
                </p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-info fw-bold"><?= format_rupiah($bestSeller['harga']) ?></span>
                    <span class="text-secondary small"><i class="bi bi-bag-check me-1"></i><?= $bestSeller['terjual'] ?> Terjual</span>
                </div>
                <a href="<?= BASE_URL ?>/detail.php?id=<?= $bestSeller['id'] ?>" class="btn btn-outline-info btn-sm w-100 mt-3 fw-semibold">
                    <i class="bi bi-eye me-1"></i> Lihat Produk
                </a>
            <?php else: ?>
                <p class="text-secondary text-center py-4 mb-0">Belum ada produk.</p>
            <?php endif; ?>
        </div>

        <!-- Produk Terlaris -->
        <div class="card card-custom p-4">
            <h5 class="fw-bold text-white mb-3">
                <i class="bi bi-trophy text-warning me-2"></i>Produk Piranti Paling Diminati
            </h5>

            <div class="d-flex flex-column gap-3">
                <?php foreach ($topProducts as $idx => $tp): ?>
                    <div class="d-flex align-items-center gap-3 p-2 bg-dark rounded-3 border border-secondary border-opacity-50">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-1 fs-6">#<?= $idx + 1 ?></span>
                        <img src="<?= get_product_image_url($tp['gambar']) ?>" alt="<?= htmlspecialchars($tp['nama_produk']) ?>" class="rounded-2" style="width: 48px; height: 48px; object-fit: cover;">
                        <div class="flex-grow-1 overflow-hidden">
                            <strong class="text-white small text-truncate d-block"><?= htmlspecialchars($tp['nama_produk']) ?></strong>
                            <span class="text-info small fw-bold"><?= format_rupiah($tp['harga']) ?></span>
                            <span class="text-secondary small">&bull; Stok: <?= $tp['stok'] ?></span>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge bg-success bg-opacity-25 text-success small">
                                <?= $tp['terjual'] ?> Laku
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

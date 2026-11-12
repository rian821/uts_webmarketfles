<?php
/**
 * Halaman Utama - Katalog Produk Alat Pancing (Daftar Produk)
 * Marketplace Tradisional StrikeZone Fishing
 */

$pageTitle = 'Katalog Alat Pancing Terlengkap & Original';
require_once __DIR__ . '/includes/header.php';

// Ambil parameter filter & pencarian
$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$brand = isset($_GET['brand']) ? sanitize($_GET['brand']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'latest';

// Bangun query SQL dinamis
$whereClauses = ["1=1"];
$params = [];
$types = "";

if ($catId > 0) {
    $whereClauses[] = "p.category_id = " . (int)$catId;
}

if (!empty($brand)) {
    $cleanBrand = mysqli_real_escape_string($conn, $brand);
    $whereClauses[] = "p.brand = '$cleanBrand'";
}

if (!empty($search)) {
    $cleanSearch = mysqli_real_escape_string($conn, $search);
    $whereClauses[] = "(p.nama_produk LIKE '%$cleanSearch%' OR p.deskripsi LIKE '%$cleanSearch%' OR p.brand LIKE '%$cleanSearch%')";
}

$whereSql = implode(' AND ', $whereClauses);

// Pengurutan (Sorting)
$orderSql = match ($sort) {
    'price_low' => 'p.harga ASC',
    'price_high' => 'p.harga DESC',
    'popular' => 'p.terjual DESC',
    'rating' => 'p.rating DESC',
    default => 'p.id DESC'
};

$sql = "SELECT p.*, c.nama_kategori, c.icon as cat_icon 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $whereSql 
        ORDER BY $orderSql";

$result = mysqli_query($conn, $sql);
$products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

// Ambil daftar brand unik untuk filter
$resBrands = mysqli_query($conn, "SELECT DISTINCT brand FROM products ORDER BY brand ASC");
$brandList = [];
if ($resBrands) {
    while ($bRow = mysqli_fetch_assoc($resBrands)) {
        $brandList[] = $bRow['brand'];
    }
}
?>

<!-- Hero Banner -->
<div class="hero-banner mb-4 text-white">
    <div class="row align-items-center">
        <div class="col-lg-7">
            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-2 rounded-pill fw-bold mb-3">
                <i class="bi bi-water me-1"></i> MARKETPLACE PERALATAN MEMANCING
            </span>
            <h1 class="display-6 fw-extrabold mb-3">Tackle Berkualitas Tinggi untuk Sensasi Strike Terbaik</h1>
            <p class="text-light opacity-90 fs-6 mb-4 leading-relaxed">
                Temukan joran solid carbon lentur, reel bertenaga tinggi tahan air asin, senar PE anti-putus, dan umpan predator terlengkap dari Shimano, Daiwa, Rapala, hingga Ryobi.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="#katalog" class="btn btn-primary btn-lg px-4 py-2 fw-bold">
                    <i class="bi bi-compass me-2"></i> Jelajahi Produk
                </a>
                <a href="<?= BASE_URL ?>/admin/produk-tambah.php" class="btn btn-outline-light btn-lg px-4 py-2 fw-semibold">
                    <i class="bi bi-plus-circle me-2"></i> Pasang Iklan Produk
                </a>
            </div>
        </div>
        <div class="col-lg-5 text-center d-none d-lg-block">
            <div class="p-3 bg-dark bg-opacity-50 rounded-4 border border-secondary shadow-lg">
                <img src="<?= BASE_URL ?>/assets/img/products/Shimano_Stradic.jpg" alt="Shimano Stradic FL 4000XG" class="img-fluid rounded-3" style="width: 100%; max-height: 250px; object-fit: contain;">
                <div class="mt-2 text-info fw-bold small">
                    <i class="bi bi-award-fill me-1 text-warning"></i> Best Seller: Shimano Stradic FL 4000XG
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Keunggulan Singkat -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 text-center h-100">
            <i class="bi bi-shield-check text-info fs-2 mb-2"></i>
            <h6 class="fw-bold mb-1 small text-white">100% Produk Original</h6>
            <small class="text-secondary">Distributor resmi & bergaransi</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 text-center h-100">
            <i class="bi bi-box-seam text-warning fs-2 mb-2"></i>
            <h6 class="fw-bold mb-1 small text-white">Gratis Paralon Guard</h6>
            <small class="text-secondary">Packing joran anti patah</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 text-center h-100">
            <i class="bi bi-truck text-success fs-2 mb-2"></i>
            <h6 class="fw-bold mb-1 small text-white">Kirim Cepat Seluruh RI</h6>
            <small class="text-secondary">JNE, J&T, SiCepat & Cargo</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 text-center h-100">
            <i class="bi bi-headset text-primary fs-2 mb-2"></i>
            <h6 class="fw-bold mb-1 small text-white">Konsultasi Angler</h6>
            <small class="text-secondary">Bantuan pilih spek piranti</small>
        </div>
    </div>
</div>

<!-- Section Katalog Produk -->
<div id="katalog" class="pt-2">
    <!-- Header Filter & Navigasi Kategori -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-white mb-1">
                <i class="bi bi-grid-fill text-info me-2"></i>Katalog Perlengkapan Pancing
            </h3>
            <p class="text-secondary small mb-0">
                Menampilkan <strong><?= count($products) ?></strong> piranti pancing pilihan
                <?php if (!empty($search)): ?>
                    untuk pencarian "<span class="text-info"><?= htmlspecialchars($search) ?></span>"
                <?php endif; ?>
            </p>
        </div>

        <!-- Filter Brand & Sort -->
        <div class="d-flex flex-wrap gap-2">
            <!-- Brand Filter -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle btn-sm text-light px-3 py-2" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-tag me-1"></i> Brand: <?= !empty($brand) ? htmlspecialchars($brand) : 'Semua' ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item <?= empty($brand) ? 'active bg-primary' : '' ?>" href="<?= BASE_URL ?>/index.php?cat=<?= $catId ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>">Semua Brand</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <?php foreach ($brandList as $b): ?>
                        <li>
                            <a class="dropdown-item <?= $brand === $b ? 'active bg-primary' : '' ?>" href="<?= BASE_URL ?>/index.php?cat=<?= $catId ?>&brand=<?= urlencode($b) ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>">
                                <?= htmlspecialchars($b) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Sort By -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle btn-sm text-light px-3 py-2" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-sort-down me-1"></i> Urutkan
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item <?= $sort === 'latest' ? 'active bg-primary' : '' ?>" href="<?= BASE_URL ?>/index.php?cat=<?= $catId ?>&brand=<?= urlencode($brand) ?>&search=<?= urlencode($search) ?>&sort=latest">Terbaru</a></li>
                    <li><a class="dropdown-item <?= $sort === 'popular' ? 'active bg-primary' : '' ?>" href="<?= BASE_URL ?>/index.php?cat=<?= $catId ?>&brand=<?= urlencode($brand) ?>&search=<?= urlencode($search) ?>&sort=popular">Paling Banyak Terjual</a></li>
                    <li><a class="dropdown-item <?= $sort === 'rating' ? 'active bg-primary' : '' ?>" href="<?= BASE_URL ?>/index.php?cat=<?= $catId ?>&brand=<?= urlencode($brand) ?>&search=<?= urlencode($search) ?>&sort=rating">Rating Tertinggi</a></li>
                    <li><a class="dropdown-item <?= $sort === 'price_low' ? 'active bg-primary' : '' ?>" href="<?= BASE_URL ?>/index.php?cat=<?= $catId ?>&brand=<?= urlencode($brand) ?>&search=<?= urlencode($search) ?>&sort=price_low">Harga: Rendah ke Tinggi</a></li>
                    <li><a class="dropdown-item <?= $sort === 'price_high' ? 'active bg-primary' : '' ?>" href="<?= BASE_URL ?>/index.php?cat=<?= $catId ?>&brand=<?= urlencode($brand) ?>&search=<?= urlencode($search) ?>&sort=price_high">Harga: Tinggi ke Rendah</a></li>
                </ul>
            </div>

            <?php if (!empty($search) || !empty($brand) || $catId > 0): ?>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-danger btn-sm px-3 py-2" title="Reset Filter">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Category Pills Navigation -->
    <div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom border-secondary border-opacity-25">
        <a href="<?= BASE_URL ?>/index.php" class="category-pill <?= empty($catId) ? 'active' : '' ?>">
            <i class="bi bi-collection"></i> Semua Kategori
        </a>
        <?php foreach ($navCategories as $cat): ?>
            <a href="<?= BASE_URL ?>/index.php?cat=<?= $cat['id'] ?>&brand=<?= urlencode($brand) ?>&sort=<?= $sort ?>" class="category-pill <?= $catId == $cat['id'] ? 'active' : '' ?>">
                <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-tag') ?>"></i>
                <?= htmlspecialchars($cat['nama_kategori']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Grid Produk -->
    <?php if (empty($products)): ?>
        <div class="card card-custom p-5 text-center my-4">
            <div class="my-3">
                <i class="bi bi-search text-secondary display-3"></i>
            </div>
            <h4 class="fw-bold text-white mb-2">Produk Tidak Ditemukan</h4>
            <p class="text-secondary mb-4">
                Tidak ada perlengkapan pancing yang cocok dengan kriteria filter atau kata kunci pencarian Anda.
            </p>
            <div>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-arrow-counterclockwise me-2"></i> Lihat Semua Produk Pancing
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $prod): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                    <div class="card card-custom product-card">
                        <!-- Wrapper Gambar -->
                        <div class="product-img-wrapper">
                            <img src="<?= get_product_image_url($prod['gambar']) ?>" alt="<?= htmlspecialchars($prod['nama_produk']) ?>" loading="lazy">
                            
                            <!-- Badge Brand -->
                            <span class="badge-brand">
                                <?= htmlspecialchars($prod['brand']) ?>
                            </span>

                            <!-- Badge Kategori Mini -->
                            <span class="position-absolute bottom-0 start-0 m-2 badge bg-dark bg-opacity-75 text-info border border-secondary small">
                                <i class="bi <?= htmlspecialchars($prod['cat_icon'] ?? 'bi-tag') ?> me-1"></i>
                                <?= htmlspecialchars($prod['nama_kategori'] ?? 'Alat Pancing') ?>
                            </span>
                        </div>

                        <!-- Info Card Body -->
                        <div class="card-body p-3 d-flex flex-direction-column flex-column justify-content-between">
                            <div>
                                <!-- Rating & Terjual -->
                                <div class="d-flex align-items-center justify-content-between small text-secondary mb-2">
                                    <div class="text-warning">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="fw-bold text-light ms-1"><?= number_format($prod['rating'], 1) ?></span>
                                    </div>
                                    <div class="small">
                                        <i class="bi bi-bag-check me-1"></i><?= $prod['terjual'] ?> Terjual
                                    </div>
                                </div>

                                <!-- Nama Produk -->
                                <h5 class="mb-2">
                                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $prod['id'] ?>" class="product-title" title="<?= htmlspecialchars($prod['nama_produk']) ?>">
                                        <?= htmlspecialchars($prod['nama_produk']) ?>
                                    </a>
                                </h5>

                                <!-- Cuplikan Singkat Spek -->
                                <?php if (!empty($prod['spesifikasi'])): ?>
                                    <p class="text-secondary small mb-3 text-truncate" title="<?= htmlspecialchars($prod['spesifikasi']) ?>">
                                        <i class="bi bi-gear text-info me-1"></i><?= htmlspecialchars($prod['spesifikasi']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <!-- Harga & Stok -->
                                <div class="d-flex align-items-baseline justify-content-between mb-3">
                                    <div>
                                        <span class="product-price"><?= format_rupiah($prod['harga']) ?></span>
                                    </div>
                                    <div>
                                        <?php if ($prod['stok'] > 5): ?>
                                            <span class="badge badge-stock bg-success bg-opacity-25 text-success border border-success border-opacity-50">
                                                Stok <?= $prod['stok'] ?>
                                            </span>
                                        <?php elseif ($prod['stok'] > 0): ?>
                                            <span class="badge badge-stock bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50">
                                                Sisa <?= $prod['stok'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-stock bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50">
                                                Habis
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2">
                                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $prod['id'] ?>" class="btn btn-outline-primary btn-sm flex-grow-1 fw-semibold">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                    <?php if ($prod['stok'] > 0): ?>
                                        <form action="<?= BASE_URL ?>/cart.php" method="POST" class="m-0">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                            <input type="hidden" name="qty" value="1">
                                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold" title="Tambah Langsung ke Keranjang">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm px-3 disabled" disabled>
                                            <i class="bi bi-cart-x"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

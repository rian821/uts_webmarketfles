<?php
/**
 * Halaman Detail Produk Alat Pancing
 * Menampilkan rincian teknis, galeri, tombol beli, spesifikasi, dan ulasan
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Ambil data produk
$sql = "SELECT p.*, c.nama_kategori, c.slug as cat_slug, u.nama as nama_penjual, u.no_hp as no_seller 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE p.id = $productId LIMIT 1";

$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    set_flash('danger', 'Produk alat pancing tidak ditemukan atau sudah dihapus.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Proses Tambah Ulasan Testimoni Pembeli
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_review') {
    $reviewerName = sanitize($_POST['nama_penilai'] ?? 'Angler Sahabat');
    $rating = (int)($_POST['rating'] ?? 5);
    $comment = sanitize($_POST['komentar'] ?? '');

    if (!empty($reviewerName) && !empty($comment)) {
        $stmtReview = mysqli_prepare($conn, "INSERT INTO product_reviews (product_id, nama_penilai, rating, komentar) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmtReview, "isis", $productId, $reviewerName, $rating, $comment);
        mysqli_stmt_execute($stmtReview);
        mysqli_stmt_close($stmtReview);

        set_flash('success', 'Terima kasih! Ulasan testimoni Anda telah dipublikasikan.');
        header('Location: ' . BASE_URL . '/detail.php?id=' . $productId . '#reviews');
        exit;
    }
}

// Ambil Ulasan Produk
$resReviews = mysqli_query($conn, "SELECT * FROM product_reviews WHERE product_id = $productId ORDER BY id DESC");
$reviews = [];
if ($resReviews) {
    while ($r = mysqli_fetch_assoc($resReviews)) {
        $reviews[] = $r;
    }
}

// Ambil Produk Rekomendasi Terkait
$catId = (int)$product['category_id'];
$resRelated = mysqli_query($conn, "SELECT * FROM products WHERE category_id = $catId AND id != $productId LIMIT 4");
$relatedProducts = [];
if ($resRelated) {
    while ($rel = mysqli_fetch_assoc($resRelated)) {
        $relatedProducts[] = $rel;
    }
}

$pageTitle = $product['nama_produk'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-secondary text-decoration-none"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?cat=<?= $product['category_id'] ?>" class="text-secondary text-decoration-none"><?= htmlspecialchars($product['nama_kategori']) ?></a></li>
        <li class="breadcrumb-item active text-info" aria-current="page"><?= htmlspecialchars($product['nama_produk']) ?></li>
    </ol>
</nav>

<!-- Detail Section -->
<div class="row g-4 mb-5">
    <!-- Kolom Kiri: Galeri Gambar Produk -->
    <div class="col-lg-5">
        <div class="card card-custom p-3 overflow-hidden text-center sticky-top" style="top: 90px; z-index: 1;">
            <div class="position-relative bg-dark rounded-4 overflow-hidden mb-3" style="aspect-ratio: 1/1;">
                <img src="<?= get_product_image_url($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" class="w-100 h-100 object-fit-cover rounded-4 shadow">
                <span class="position-absolute top-0 end-0 m-3 badge bg-dark bg-opacity-75 border border-secondary text-light px-3 py-2 fs-6">
                    <?= htmlspecialchars($product['brand']) ?>
                </span>
                <span class="position-absolute bottom-0 start-0 m-3 badge bg-success bg-opacity-75 text-white px-3 py-2 small">
                    <i class="bi bi-shield-check me-1"></i> 100% Original
                </span>
            </div>

            <!-- Mini Banner Proteksi Tabung -->
            <div class="alert alert-dark border-secondary p-2 mb-0 d-flex align-items-center gap-2 small text-start">
                <i class="bi bi-box-seam-fill text-warning fs-4"></i>
                <div>
                    <strong class="text-light">Packing Tabung PVC Ekstra Aman:</strong>
                    <div class="text-secondary small">Barang terlindungi dari tekanan & benturan selama transit.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rincian Produk, Pembelian & Spesifikasi -->
    <div class="col-lg-7">
        <div class="card card-custom p-4 p-md-4">
            <!-- Brand & Kategori Badge -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-1">
                    <?= htmlspecialchars($product['nama_kategori']) ?>
                </span>
                <span class="badge bg-secondary bg-opacity-25 text-light px-3 py-1">
                    Brand: <?= htmlspecialchars($product['brand']) ?>
                </span>
                <span class="badge bg-dark border border-secondary text-secondary px-3 py-1">
                    Kode: SKZ-<?= str_pad($product['id'], 4, '0', STR_PAD_LEFT) ?>
                </span>
            </div>

            <!-- Judul Produk -->
            <h2 class="fw-bold text-white mb-2"><?= htmlspecialchars($product['nama_produk']) ?></h2>

            <!-- Rating & Penjualan -->
            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-secondary border-opacity-50">
                <div class="d-flex align-items-center text-warning">
                    <?php 
                    $stars = round($product['rating']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $stars ? '<i class="bi bi-star-fill me-1"></i>' : '<i class="bi bi-star me-1 text-secondary"></i>';
                    }
                    ?>
                    <span class="text-light fw-bold ms-1"><?= number_format($product['rating'], 1) ?></span>
                </div>
                <span class="text-secondary">&bull;</span>
                <span class="text-secondary small"><i class="bi bi-chat-left-text me-1 text-info"></i><?= count($reviews) ?> Ulasan</span>
                <span class="text-secondary">&bull;</span>
                <span class="text-secondary small"><i class="bi bi-bag-check me-1 text-success"></i><?= $product['terjual'] ?> Terjual</span>
            </div>

            <!-- Harga & Status Ketersediaan -->
            <div class="p-3 bg-dark rounded-3 border border-secondary mb-4">
                <div class="d-flex flex-wrap align-items-baseline justify-content-between gap-2">
                    <div>
                        <div class="text-secondary small">Harga Spesial Marketplace:</div>
                        <h1 class="product-price display-6 fw-bold mb-0 text-info">
                            <?= format_rupiah($product['harga']) ?>
                        </h1>
                    </div>
                    <div class="text-end">
                        <div class="text-secondary small mb-1">Status Ketersediaan:</div>
                        <?php if ($product['stok'] > 5): ?>
                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 fs-6 px-3 py-1">
                                <i class="bi bi-check-circle me-1"></i> Stok Siap Kirim (<?= $product['stok'] ?> Unit)
                            </span>
                        <?php elseif ($product['stok'] > 0): ?>
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 fs-6 px-3 py-1">
                                <i class="bi bi-exclamation-circle me-1"></i> Stok Terbatas (Sisa <?= $product['stok'] ?> Unit)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 fs-6 px-3 py-1">
                                <i class="bi bi-x-circle me-1"></i> Stok Habis
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form Pemesanan / Keranjang Belanja -->
            <?php if ($product['stok'] > 0): ?>
                <form action="<?= BASE_URL ?>/cart.php" method="POST" class="mb-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-4">
                            <label class="form-label text-secondary small fw-bold mb-1">Jumlah Pemesanan:</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary btn-qty-minus text-light px-3">-</button>
                                <input type="number" name="qty" class="form-control text-center quantity-input fw-bold bg-dark text-white" value="1" min="1" max="<?= $product['stok'] ?>" required>
                                <button type="button" class="btn btn-outline-secondary btn-qty-plus text-light px-3">+</button>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label text-secondary small fw-bold mb-1">Berat Paket:</label>
                            <div class="p-2 bg-dark rounded border border-secondary text-secondary small">
                                <i class="bi bi-weight me-1 text-info"></i> <?= $product['berat_gram'] ?> Gram (Termasuk Pipa & Bubble Wrap)
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1 fw-bold py-3 shadow">
                            <i class="bi bi-cart-plus-fill me-2"></i> Tambahkan ke Keranjang
                        </button>
                        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20StrikeZone,%20saya%20tertarik%20dengan%20produk:%20<?= urlencode($product['nama_produk']) ?>%20(<?= urlencode(format_rupiah($product['harga'])) ?>)" target="_blank" class="btn btn-outline-success btn-lg px-4 fw-semibold py-3" title="Tanya Penjual via WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger border-danger p-3 mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Maaf, stok perlengkapan pancing ini saat ini sedang kosong. Silakan pantau kembali atau hubungi penjual.
                </div>
            <?php endif; ?>

            <!-- Tab Spesifikasi & Deskripsi -->
            <ul class="nav nav-pills mb-3 border-bottom border-secondary border-opacity-50 pb-2" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4" id="tab-desc" data-bs-toggle="pill" data-bs-target="#content-desc" type="button" role="tab">
                        <i class="bi bi-file-text me-1"></i> Deskripsi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4" id="tab-spec" data-bs-toggle="pill" data-bs-target="#content-spec" type="button" role="tab">
                        <i class="bi bi-gear-wide-connected me-1"></i> Spesifikasi Teknis
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4" id="tab-reviews" data-bs-toggle="pill" data-bs-target="#content-reviews" type="button" role="tab">
                        <i class="bi bi-chat-square-quote me-1"></i> Ulasan Pembeli (<?= count($reviews) ?>)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <!-- Deskripsi Tab -->
                <div class="tab-pane fade show active" id="content-desc" role="tabpanel">
                    <div class="text-light leading-relaxed fs-6">
                        <?= nl2br(htmlspecialchars($product['deskripsi'])) ?>
                    </div>
                    <div class="card bg-dark border-secondary p-3 mt-3">
                        <h6 class="fw-bold text-info mb-2"><i class="bi bi-check-circle-fill me-2"></i>Jaminan Keaslian Toko:</h6>
                        <p class="small text-secondary mb-0">
                            Semua peralatan pancing yang kami jual terjamin 100% orisinal langsung dari pabrik dan agen distributor resmi di Indonesia. Garansi penggantian barang baru bila ditemukan cacat produksi saat kemasan tabung pertama kali dibuka.
                        </p>
                    </div>
                </div>

                <!-- Spesifikasi Teknis Tab -->
                <div class="tab-pane fade" id="content-spec" role="tabpanel">
                    <?php if (!empty($product['spesifikasi'])): ?>
                        <div class="table-responsive">
                            <table class="table table-custom table-bordered">
                                <tbody>
                                    <?php 
                                    $specs = explode('|', $product['spesifikasi']);
                                    foreach ($specs as $spec): 
                                        $parts = explode(':', $spec, 2);
                                        if (count($parts) === 2):
                                    ?>
                                        <tr>
                                            <th class="w-35 text-info bg-dark"><?= htmlspecialchars(trim($parts[0])) ?></th>
                                            <td><?= htmlspecialchars(trim($parts[1])) ?></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2"><?= htmlspecialchars(trim($spec)) ?></td>
                                        </tr>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                    <tr>
                                        <th class="text-info bg-dark">Berat Total Pengiriman</th>
                                        <td><?= $product['berat_gram'] ?> Gram</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-secondary small">Spesifikasi teknis produk belum ditambahkan.</p>
                    <?php endif; ?>
                </div>

                <!-- Ulasan Pembeli Tab -->
                <div class="tab-pane fade" id="content-reviews" role="tabpanel">
                    <div class="mb-4">
                        <h6 class="fw-bold text-white mb-3">Tulis Ulasan & Rating Anda:</h6>
                        <form action="<?= BASE_URL ?>/detail.php?id=<?= $productId ?>" method="POST" class="p-3 bg-dark rounded-3 border border-secondary">
                            <input type="hidden" name="action" value="add_review">
                            <div class="row g-2 mb-2">
                                <div class="col-sm-7">
                                    <input type="text" name="nama_penilai" class="form-control form-control-sm" placeholder="Nama Anda / Alias Angler" required>
                                </div>
                                <div class="col-sm-5">
                                    <select name="rating" class="form-select form-select-sm" required>
                                        <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                                        <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
                                        <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                                        <option value="2">⭐⭐ (2 - Kurang)</option>
                                        <option value="1">⭐ (1 - Kecewa)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2">
                                <textarea name="komentar" class="form-control form-control-sm" rows="2" placeholder="Bagikan pengalaman tarikan piranti ini saat memancing..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-info btn-sm fw-bold">
                                <i class="bi bi-send me-1"></i> Kirim Ulasan
                            </button>
                        </form>
                    </div>

                    <h6 class="fw-bold text-white mb-3">Ulasan Angler Lain:</h6>
                    <?php if (empty($reviews)): ?>
                        <p class="text-secondary small">Belum ada ulasan untuk produk ini. Jadilah yang pertama memberikan testimoni!</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($reviews as $rev): ?>
                                <div class="p-3 bg-dark bg-opacity-75 rounded-3 border border-secondary border-opacity-50">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-light small"><?= htmlspecialchars($rev['nama_penilai']) ?></strong>
                                        <div class="text-warning small">
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <?= $s <= $rev['rating'] ? '★' : '☆' ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="text-secondary small mb-1"><?= htmlspecialchars($rev['komentar']) ?></p>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?= date('d M Y, H:i', strtotime($rev['created_at'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rekomendasi Terkait -->
<?php if (!empty($relatedProducts)): ?>
    <div class="mt-5">
        <h4 class="fw-bold text-white mb-4">
            <i class="bi bi-compass-fill text-info me-2"></i>Piranti Sejenis di Kategori <?= htmlspecialchars($product['nama_kategori']) ?>
        </h4>
        <div class="row g-4">
            <?php foreach ($relatedProducts as $rel): ?>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-custom product-card">
                        <div class="product-img-wrapper">
                            <img src="<?= get_product_image_url($rel['gambar']) ?>" alt="<?= htmlspecialchars($rel['nama_produk']) ?>">
                            <span class="badge-brand"><?= htmlspecialchars($rel['brand']) ?></span>
                        </div>
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="mb-2">
                                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $rel['id'] ?>" class="product-title" style="height: 2.6rem;">
                                        <?= htmlspecialchars($rel['nama_produk']) ?>
                                    </a>
                                </h6>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="product-price fs-6"><?= format_rupiah($rel['harga']) ?></span>
                                    <span class="badge bg-success bg-opacity-25 text-success small">Stok <?= $rel['stok'] ?></span>
                                </div>
                                <a href="<?= BASE_URL ?>/detail.php?id=<?= $rel['id'] ?>" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                                    Lihat Piranti
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

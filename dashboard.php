<?php
/**
 * Dashboard Akun Pengguna (User)
 * StrikeZone Fishing Tackle Marketplace
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Hanya untuk pengguna yang sudah login
require_login();

$user = current_user();

// Riwayat pesanan milik pengguna ini
$orders = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user['id']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $orders[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$totalPesanan = count($orders);
$totalBelanja = 0.0;
foreach ($orders as $o) {
    $totalBelanja += (float)$o['total_bayar'];
}

// Produk Best Seller untuk etalase (diutamakan Shimano Stradic)
$featured = null;
$qF = mysqli_query($conn, "SELECT p.*, c.nama_kategori FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.nama_produk LIKE '%Stradic%' ORDER BY p.terjual DESC LIMIT 1");
if ($qF) {
    $featured = mysqli_fetch_assoc($qF);
}
if (!$featured) {
    $qF = mysqli_query($conn, "SELECT p.*, c.nama_kategori FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.terjual DESC LIMIT 1");
    if ($qF) {
        $featured = mysqli_fetch_assoc($qF);
    }
}

$pageTitle = 'Dashboard Akun Saya';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Sambutan -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="bi bi-person-badge text-info me-2"></i>Halo, <?= htmlspecialchars($user['nama']) ?>!
        </h2>
        <p class="text-secondary small mb-0">
            Selamat datang di dashboard akun Anda.
            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 ms-1">
                <?= strtoupper(htmlspecialchars($user['role'])) ?>
            </span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary fw-semibold">
            <i class="bi bi-shop me-1"></i> Jelajahi Katalog
        </a>
        <?php if (is_admin()): ?>
            <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-info fw-semibold">
                <i class="bi bi-speedometer2 me-1"></i> Panel Admin
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Kartu Statistik -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Total Pesanan</span>
                <span class="bg-primary bg-opacity-25 text-info p-2 rounded-3"><i class="bi bi-receipt fs-4"></i></span>
            </div>
            <h3 class="fw-bold text-white mb-0"><?= number_format($totalPesanan) ?></h3>
            <small class="text-secondary">Transaksi atas nama akun ini</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Total Belanja</span>
                <span class="bg-info bg-opacity-25 text-primary-light p-2 rounded-3"><i class="bi bi-cash-stack fs-4"></i></span>
            </div>
            <h3 class="fw-bold text-info mb-0"><?= format_rupiah($totalBelanja) ?></h3>
            <small class="text-secondary">Akumulasi nilai transaksi</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Item Keranjang</span>
                <span class="bg-warning bg-opacity-25 text-warning p-2 rounded-3"><i class="bi bi-cart3 fs-4"></i></span>
            </div>
            <h3 class="fw-bold text-warning mb-0"><?= get_cart_count() ?></h3>
            <small class="text-secondary">Produk siap di-checkout</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Status Akun</span>
                <span class="bg-success bg-opacity-25 text-success p-2 rounded-3"><i class="bi bi-shield-check fs-4"></i></span>
            </div>
            <h3 class="fw-bold text-success mb-0" style="font-size: 1.35rem;">Aktif</h3>
            <small class="text-secondary"><?= htmlspecialchars($user['email']) ?></small>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Riwayat Pesanan -->
    <div class="col-lg-7">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0">
                    <i class="bi bi-clock-history text-info me-2"></i>Riwayat Pesanan Saya
                </h5>
                <a href="<?= BASE_URL ?>/cart.php" class="text-info small text-decoration-none">Keranjang &rarr;</a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="text-center text-secondary py-5">
                    <i class="bi bi-bag-x fs-1 d-block mb-2"></i>
                    <p class="mb-3">Anda belum memiliki riwayat pesanan.</p>
                    <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary btn-sm px-4 fw-semibold">
                        <i class="bi bi-shop me-1"></i> Mulai Belanja
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $od): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>/invoice.php?inv=<?= $od['nomor_invoice'] ?>" class="text-info fw-bold text-decoration-none">
                                            <?= htmlspecialchars($od['nomor_invoice']) ?>
                                        </a>
                                        <div class="text-secondary" style="font-size: 0.72rem;"><?= date('d/m/Y H:i', strtotime($od['created_at'])) ?></div>
                                    </td>
                                    <td class="text-light small fw-bold"><?= format_rupiah($od['total_bayar']) ?></td>
                                    <td>
                                        <?php
                                        $badge = match ($od['status']) {
                                            'selesai' => 'bg-success bg-opacity-25 text-success',
                                            'diproses' => 'bg-info bg-opacity-25 text-info',
                                            'dikirim' => 'bg-primary bg-opacity-25 text-light',
                                            'dibatalkan' => 'bg-danger bg-opacity-25 text-danger',
                                            default => 'bg-warning bg-opacity-25 text-warning',
                                        };
                                        $label = match ($od['status']) {
                                            'selesai' => 'Selesai',
                                            'diproses' => 'Diproses',
                                            'dikirim' => 'Dikirim',
                                            'dibatalkan' => 'Dibatalkan',
                                            default => 'Menunggu Bayar',
                                        };
                                        ?>
                                        <span class="badge <?= $badge ?> small"><?= $label ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/invoice.php?inv=<?= $od['nomor_invoice'] ?>" class="btn btn-outline-info btn-sm p-1" title="Lihat Nota">
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

    <!-- Best Seller -->
    <div class="col-lg-5">
        <div class="card card-custom p-4 h-100">
            <h5 class="fw-bold text-white mb-3">
                <i class="bi bi-trophy-fill text-warning me-2"></i>Best Seller
            </h5>

            <?php if ($featured): ?>
                <div class="text-center mb-3">
                    <img src="<?= get_product_image_url($featured['gambar']) ?>" alt="<?= htmlspecialchars($featured['nama_produk']) ?>"
                         class="img-fluid rounded-3 border border-secondary" style="width: 100%; max-height: 280px; object-fit: cover;">
                </div>
                <p class="fw-bold text-warning mb-2">
                    <i class="bi bi-trophy-fill me-1"></i>Best Seller: <?= htmlspecialchars($featured['nama_produk']) ?>
                </p>
                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 mb-2">
                    <?= htmlspecialchars($featured['nama_kategori'] ?? 'Alat Pancing') ?>
                </span>
                <div class="d-flex align-items-center gap-3 mb-3 small">
                    <span class="text-warning"><i class="bi bi-star-fill"></i> <span class="text-light fw-bold"><?= number_format($featured['rating'], 1) ?></span></span>
                    <span class="text-secondary"><i class="bi bi-bag-check me-1"></i><?= $featured['terjual'] ?> Terjual</span>
                </div>
                <div class="product-price fs-4 mb-3"><?= format_rupiah($featured['harga']) ?></div>
                <a href="<?= BASE_URL ?>/detail.php?id=<?= $featured['id'] ?>" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-eye me-1"></i> Lihat Detail Produk
                </a>
            <?php else: ?>
                <p class="text-secondary text-center py-5 mb-0">Belum ada produk pada katalog.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

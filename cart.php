<?php
/**
 * Halaman Keranjang Belanja (Shopping Cart)
 * Mengelola penambahan produk, perubahan kuantitas, penghapusan, dan subtotal
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Inisialisasi Keranjang Belanja
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Tangani Action POST / GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Tambah Produk ke Keranjang
    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));

        if ($productId > 0) {
            // Ambil data produk terbaru dari database
            $stmt = mysqli_prepare($conn, "SELECT id, nama_produk, brand, harga, stok, gambar, berat_gram FROM products WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $productId);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $prod = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);

            if ($prod) {
                if ($prod['stok'] <= 0) {
                    set_flash('danger', 'Maaf, stok produk ini sedang kosong!');
                } else {
                    $currentQtyInCart = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId]['qty'] : 0;
                    $newQty = $currentQtyInCart + $qty;

                    if ($newQty > $prod['stok']) {
                        set_flash('warning', 'Jumlah melebihi stok yang tersedia. Kami menambahkan maksimal stok tersisa (' . $prod['stok'] . ' unit).');
                        $newQty = $prod['stok'];
                    } else {
                        set_flash('success', 'Berhasil menambahkan "' . htmlspecialchars($prod['nama_produk']) . '" ke keranjang belanja.');
                    }

                    $_SESSION['cart'][$productId] = [
                        'id' => $prod['id'],
                        'nama_produk' => $prod['nama_produk'],
                        'brand' => $prod['brand'],
                        'harga' => (float)$prod['harga'],
                        'gambar' => $prod['gambar'],
                        'berat_gram' => (int)$prod['berat_gram'],
                        'stok_max' => (int)$prod['stok'],
                        'qty' => $newQty
                    ];
                }
            } else {
                set_flash('danger', 'Produk tidak ditemukan!');
            }
        }
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    // 2. Update Kuantitas
    if ($action === 'update') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 1);

        if (isset($_SESSION['cart'][$productId])) {
            $max = $_SESSION['cart'][$productId]['stok_max'];
            if ($qty <= 0) {
                unset($_SESSION['cart'][$productId]);
                set_flash('info', 'Produk telah dihapus dari keranjang.');
            } elseif ($qty > $max) {
                $_SESSION['cart'][$productId]['qty'] = $max;
                set_flash('warning', 'Kuantitas disesuaikan dengan stok maksimal (' . $max . ' unit).');
            } else {
                $_SESSION['cart'][$productId]['qty'] = $qty;
                set_flash('success', 'Jumlah produk berhasil diperbarui.');
            }
        }
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }
}

// Action Hapus Item Tunggal via GET
if ($action === 'remove') {
    $productId = (int)($_GET['id'] ?? 0);
    if (isset($_SESSION['cart'][$productId])) {
        $namaItem = $_SESSION['cart'][$productId]['nama_produk'];
        unset($_SESSION['cart'][$productId]);
        set_flash('info', 'Produk "' . htmlspecialchars($namaItem) . '" telah dikeluarkan dari keranjang.');
    }
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

// Action Kosongkan Keranjang
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    set_flash('info', 'Keranjang belanja Anda telah dikosongkan.');
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

$cart = get_cart();
$cartTotal = get_cart_total();
$totalBerat = 0;
foreach ($cart as $item) {
    $totalBerat += ($item['berat_gram'] * $item['qty']);
}

$pageTitle = 'Keranjang Belanja';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-secondary text-decoration-none"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
        <li class="breadcrumb-item active text-info" aria-current="page">Keranjang Belanja</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="fw-bold text-white mb-0">
        <i class="bi bi-cart3 text-info me-2"></i>Keranjang Belanja Anda
    </h2>
    <?php if (!empty($cart)): ?>
        <a href="<?= BASE_URL ?>/cart.php?action=clear" class="btn btn-outline-danger btn-sm btn-confirm-delete" data-message="Kosongkan semua barang di keranjang?">
            <i class="bi bi-trash3 me-1"></i> Kosongkan Keranjang
        </a>
    <?php endif; ?>
</div>

<?php if (empty($cart)): ?>
    <!-- Keranjang Kosong -->
    <div class="card card-custom p-5 text-center my-4">
        <div class="my-4">
            <i class="bi bi-cart-x text-secondary" style="font-size: 5rem;"></i>
        </div>
        <h4 class="fw-bold text-white mb-2">Keranjang Belanja Masih Kosong</h4>
        <p class="text-secondary mb-4">
            Anda belum menambahkan peralatan pancing apa pun ke dalam keranjang belanja.
        </p>
        <div>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary btn-lg px-4 fw-bold">
                <i class="bi bi-shop me-2"></i> Belanja Peralatan Pancing Sekarang
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Keranjang Berisi Produk -->
    <div class="row g-4 mb-5">
        <!-- Kolom Daftar Item -->
        <div class="col-lg-8">
            <div class="card card-custom p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Perlengkapan Pancing</th>
                                <th class="text-center" style="width: 18%;">Harga Satuan</th>
                                <th class="text-center" style="width: 17%;">Jumlah</th>
                                <th class="text-end" style="width: 20%;">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $id => $item): 
                                $sub = $item['harga'] * $item['qty'];
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= get_product_image_url($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>" class="rounded-3 border border-secondary" style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <a href="<?= BASE_URL ?>/detail.php?id=<?= $id ?>" class="text-white fw-bold text-decoration-none hover-white d-block mb-1 small">
                                                    <?= htmlspecialchars($item['nama_produk']) ?>
                                                </a>
                                                <span class="badge bg-dark border border-secondary text-secondary small">
                                                    <?= htmlspecialchars($item['brand']) ?>
                                                </span>
                                                <small class="text-secondary ms-1">(<?= $item['berat_gram'] ?>g)</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-light small fw-medium">
                                        <?= format_rupiah($item['harga']) ?>
                                    </td>
                                    <td>
                                        <form action="<?= BASE_URL ?>/cart.php" method="POST" class="d-flex align-items-center justify-content-center m-0">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?= $id ?>">
                                            <div class="input-group input-group-sm" style="max-width: 110px;">
                                                <button type="button" class="btn btn-outline-secondary btn-qty-minus text-light px-2">-</button>
                                                <input type="number" name="qty" class="form-control text-center quantity-input fw-bold bg-dark text-white p-1" value="<?= $item['qty'] ?>" min="1" max="<?= $item['stok_max'] ?>" onchange="this.form.submit()">
                                                <button type="button" class="btn btn-outline-secondary btn-qty-plus text-light px-2">+</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-end text-info fw-bold">
                                        <?= format_rupiah($sub) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/cart.php?action=remove&id=<?= $id ?>" class="text-danger hover-opacity fs-5" title="Hapus Item">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary">
                    <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-light btn-sm fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Lanjut Pilih Piranti Lain
                    </a>
                    <span class="text-secondary small">
                        <i class="bi bi-box me-1"></i> Total Bobot Pengiriman: <strong><?= number_format($totalBerat) ?> Gram</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Kolom Ringkasan Belanja -->
        <div class="col-lg-4">
            <div class="card card-custom p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-secondary">
                    <i class="bi bi-receipt text-info me-2"></i>Ringkasan Belanja
                </h5>

                <div class="d-flex justify-content-between text-secondary mb-2 small">
                    <span>Total Item Piranti:</span>
                    <span class="text-white fw-bold"><?= get_cart_count() ?> Unit</span>
                </div>

                <div class="d-flex justify-content-between text-secondary mb-2 small">
                    <span>Total Subtotal:</span>
                    <span class="text-white fw-bold"><?= format_rupiah($cartTotal) ?></span>
                </div>

                <div class="d-flex justify-content-between text-secondary mb-3 small">
                    <span>Kemasan Pipa Guard:</span>
                    <span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>GRATIS</span>
                </div>

                <hr class="border-secondary opacity-50 my-3">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div class="text-secondary small">Total Sementara:</div>
                        <h4 class="product-price mb-0 text-info fw-bold"><?= format_rupiah($cartTotal) ?></h4>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow mb-2">
                    <i class="bi bi-credit-card-2-front me-2"></i> Lanjut ke Checkout &rarr;
                </a>

                <div class="p-2 bg-dark rounded border border-secondary text-secondary small text-center mt-2">
                    <i class="bi bi-lock-fill text-warning me-1"></i> Transaksi Aman dengan Transaksi Database MySQL
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

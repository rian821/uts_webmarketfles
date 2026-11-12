<?php
/**
 * Halaman Checkout & Pembuatan Transaksi Pemesanan
 * Menyimpan data pemesanan ke tabel orders & order_items serta memotong stok
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$cart = get_cart();
if (empty($cart)) {
    set_flash('warning', 'Keranjang belanja Anda masih kosong. Silakan pilih produk terlebih dahulu.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$cartTotal = get_cart_total();
$totalBerat = 0;
foreach ($cart as $item) {
    $totalBerat += ($item['berat_gram'] * $item['qty']);
}

// Opsi Kurir Pengiriman & Biaya
$shippingOptions = [
    'JNE Regular (Kemasan Tabung Paralon PVC Guard)' => 35000,
    'J&T Express Cargo Khusus Piranti & Joran' => 45000,
    'SiCepat Best Next Day (Prioritas Turnamen)' => 60000,
    'Ambil Langsung di Gudang Toko (Bengkulu)' => 0
];

$defaultShippingCost = 35000;

// Proses Form Checkout (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaLengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $noWhatsapp  = sanitize($_POST['no_whatsapp'] ?? '');
    $email       = sanitize($_POST['email'] ?? '');
    $alamat      = sanitize($_POST['alamat_lengkap'] ?? '');
    $kota        = sanitize($_POST['kota'] ?? '');
    $kodePos     = sanitize($_POST['kode_pos'] ?? '');
    $metodeKirim = sanitize($_POST['metode_pengiriman'] ?? '');
    $metodeBayar = sanitize($_POST['metode_pembayaran'] ?? '');
    $catatan     = sanitize($_POST['catatan'] ?? '');

    $ongkir = isset($shippingOptions[$metodeKirim]) ? $shippingOptions[$metodeKirim] : $defaultShippingCost;
    $totalBayar = $cartTotal + $ongkir;

    // Validasi Sederhana
    if (empty($namaLengkap) || empty($noWhatsapp) || empty($email) || empty($alamat) || empty($kota) || empty($kodePos)) {
        set_flash('danger', 'Harap lengkapi semua kolom bertanda bintang (*) pada formulir pemesanan.');
    } else {
        // Mulai Transaksi Database (ACID)
        mysqli_begin_transaction($conn);

        try {
            // 1. Verifikasi Stok Terakhir untuk Semua Item
            foreach ($cart as $prodId => $item) {
                $checkStmt = mysqli_prepare($conn, "SELECT stok, nama_produk FROM products WHERE id = ? FOR UPDATE");
                mysqli_stmt_bind_param($checkStmt, "i", $prodId);
                mysqli_stmt_execute($checkStmt);
                $resStock = mysqli_stmt_get_result($checkStmt);
                $currProd = mysqli_fetch_assoc($resStock);
                mysqli_stmt_close($checkStmt);

                if (!$currProd || $currProd['stok'] < $item['qty']) {
                    throw new Exception("Maaf, stok produk \"" . ($currProd['nama_produk'] ?? 'Item') . "\" tidak mencukupi untuk jumlah pemesanan Anda.");
                }
            }

            // 2. Generate Nomor Invoice Unik
            $nomorInvoice = generate_invoice_number();
            $userId = $_SESSION['user_id'] ?? null;

            // 3. Insert ke tabel orders
            $sqlOrder = "INSERT INTO orders (
                            nomor_invoice, user_id, nama_lengkap, no_whatsapp, email, 
                            alamat_lengkap, kota, kode_pos, metode_pengiriman, ongkos_kirim, 
                            metode_pembayaran, catatan, total_produk, total_bayar, status
                         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu_pembayaran')";

            $stmtOrder = mysqli_prepare($conn, $sqlOrder);
            mysqli_stmt_bind_param(
                $stmtOrder, 
                "sisssssssdssdd", 
                $nomorInvoice, 
                $userId, 
                $namaLengkap, 
                $noWhatsapp, 
                $email, 
                $alamat, 
                $kota, 
                $kodePos, 
                $metodeKirim, 
                $ongkir, 
                $metodeBayar, 
                $catatan, 
                $cartTotal, 
                $totalBayar
            );
            
            if (!mysqli_stmt_execute($stmtOrder)) {
                throw new Exception("Gagal menyimpan data pesanan utama: " . mysqli_error($conn));
            }

            $orderId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmtOrder);

            // 4. Insert ke tabel order_items & Kurangi Stok Produk
            $sqlItem = "INSERT INTO order_items (order_id, product_id, nama_produk, harga_satuan, qty, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtItem = mysqli_prepare($conn, $sqlItem);

            $sqlUpdateStock = "UPDATE products SET stok = stok - ?, terjual = terjual + ? WHERE id = ?";
            $stmtStock = mysqli_prepare($conn, $sqlUpdateStock);

            foreach ($cart as $prodId => $item) {
                $subtotalItem = $item['harga'] * $item['qty'];

                // Insert item
                mysqli_stmt_bind_param($stmtItem, "iisdid", $orderId, $prodId, $item['nama_produk'], $item['harga'], $item['qty'], $subtotalItem);
                mysqli_stmt_execute($stmtItem);

                // Update stok
                mysqli_stmt_bind_param($stmtStock, "iii", $item['qty'], $item['qty'], $prodId);
                mysqli_stmt_execute($stmtStock);
            }

            mysqli_stmt_close($stmtItem);
            mysqli_stmt_close($stmtStock);

            // 5. Commit Transaksi Database
            mysqli_commit($conn);

            // 6. Kosongkan Keranjang Belanja
            $_SESSION['cart'] = [];

            // Arahkan ke Faktur Invoice
            set_flash('success', 'Pesanan Anda berhasil dibuat dengan nomor invoice ' . $nomorInvoice);
            header('Location: ' . BASE_URL . '/invoice.php?inv=' . urlencode($nomorInvoice));
            exit;

        } catch (Exception $e) {
            // Rollback jika terjadi kesalahan
            mysqli_rollback($conn);
            set_flash('danger', 'Transaksi Gagal: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Checkout Pesanan';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-secondary text-decoration-none">Beranda</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/cart.php" class="text-secondary text-decoration-none">Keranjang</a></li>
        <li class="breadcrumb-item active text-info" aria-current="page">Checkout</li>
    </ol>
</nav>

<h2 class="fw-bold text-white mb-4">
    <i class="bi bi-shield-check text-info me-2"></i>Checkout & Penyelesaian Pesanan
</h2>

<form action="<?= BASE_URL ?>/checkout.php" method="POST">
    <div class="row g-4 mb-5">
        <!-- Kolom Kiri: Formulir Data Pembeli & Pengiriman -->
        <div class="col-lg-7">
            <div class="card card-custom p-4 mb-4">
                <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-secondary">
                    <i class="bi bi-person-lines-fill text-info me-2"></i>1. Informasi Penerima Paket
                </h5>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-secondary small fw-bold">Nama Lengkap Pembeli *</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Budi Santoso" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">No. WhatsApp / Telepon *</label>
                        <input type="tel" name="no_whatsapp" class="form-control" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['no_whatsapp'] ?? '') ?>" required>
                        <small class="text-secondary" style="font-size: 0.75rem;">Untuk konfirmasi status pengiriman & nomor resi</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">Alamat Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-secondary small fw-bold">Alamat Lengkap Pengiriman *</label>
                        <textarea name="alamat_lengkap" class="form-control" rows="3" placeholder="Nama Jalan, No. Rumah, RT/RW, Kelurahan, Kecamatan" required><?= htmlspecialchars($_POST['alamat_lengkap'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label text-secondary small fw-bold">Kota / Kabupaten *</label>
                        <input type="text" name="kota" class="form-control" placeholder="Contoh: Kota Bengkulu" value="<?= htmlspecialchars($_POST['kota'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-bold">Kode Pos *</label>
                        <input type="text" name="kode_pos" class="form-control" placeholder="Contoh: 38224" value="<?= htmlspecialchars($_POST['kode_pos'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <!-- Bagian Kurir & Pembayaran -->
            <div class="card card-custom p-4">
                <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-secondary">
                    <i class="bi bi-truck text-info me-2"></i>2. Pengiriman & Metode Pembayaran
                </h5>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">Pilih Kurir Logistik Piranti *</label>
                    <select name="metode_pengiriman" id="metode_pengiriman" class="form-select" data-subtotal="<?= $cartTotal ?>" required>
                        <?php foreach ($shippingOptions as $kurir => $cost): ?>
                            <option value="<?= htmlspecialchars($kurir) ?>" data-cost="<?= $cost ?>" <?= $cost === $defaultShippingCost ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kurir) ?> &mdash; <?= $cost === 0 ? 'GRATIS' : format_rupiah($cost) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">Pilih Cara Pembayaran *</label>
                    <select name="metode_pembayaran" class="form-select" required>
                        <option value="BCA Virtual Account (Otomatis)">BCA Virtual Account (Verifikasi Otomatis)</option>
                        <option value="Mandiri Virtual Account">Mandiri Livin' Virtual Account</option>
                        <option value="BRI BRIVA Online">BRI BRIVA Virtual Account</option>
                        <option value="QRIS Instant Pay (GoPay/OVO/ShopeePay/Dana)">QRIS Instant Pay (Scan Semua E-Wallet & Bank)</option>
                        <option value="COD (Bayar Tunai di Tempat)">COD (Bayar Tunai saat Paket Tiba di Rumah)</option>
                    </select>
                </div>

                <div>
                    <label class="form-label text-secondary small fw-bold">Catatan Khusus Penjual (Opsional)</label>
                    <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Mohon tabung paralon dilakban fragile dengan rapat."><?= htmlspecialchars($_POST['catatan'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Rincian Pesanan & Pembayaran -->
        <div class="col-lg-5">
            <div class="card card-custom p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-secondary">
                    <i class="bi bi-receipt-cutoff text-info me-2"></i>Ringkasan Tagihan
                </h5>

                <!-- Daftar Singkat Produk -->
                <div class="d-flex flex-column gap-2 mb-3 max-h-300 overflow-auto">
                    <?php foreach ($cart as $c): ?>
                        <div class="d-flex justify-content-between align-items-center small p-2 bg-dark rounded border border-secondary border-opacity-50">
                            <div>
                                <span class="text-white fw-bold d-block"><?= htmlspecialchars($c['nama_produk']) ?></span>
                                <span class="text-secondary"><?= $c['qty'] ?> x <?= format_rupiah($c['harga']) ?></span>
                            </div>
                            <span class="text-info fw-bold"><?= format_rupiah($c['harga'] * $c['qty']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between text-secondary mb-2 small">
                    <span>Subtotal Produk:</span>
                    <span class="text-white fw-bold"><?= format_rupiah($cartTotal) ?></span>
                </div>

                <div class="d-flex justify-content-between text-secondary mb-2 small">
                    <span>Biaya Ongkir Kurir:</span>
                    <span class="text-white fw-bold" id="shipping-cost-display"><?= format_rupiah($defaultShippingCost) ?></span>
                </div>

                <div class="d-flex justify-content-between text-secondary mb-3 small">
                    <span>Proteksi Tabung Paralon:</span>
                    <span class="text-success fw-bold"><i class="bi bi-shield-check me-1"></i>GRATIS Rp 0</span>
                </div>

                <hr class="border-secondary opacity-50 my-3">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div class="text-secondary small">Total yang Harus Dibayar:</div>
                        <h3 class="product-price mb-0 text-info fw-bold" id="grand-total-display">
                            <?= format_rupiah($cartTotal + $defaultShippingCost) ?>
                        </h3>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow mb-2">
                    <i class="bi bi-bag-check-fill me-2"></i> Buat Pesanan Sekarang &rarr;
                </button>

                <div class="text-center mt-3">
                    <small class="text-secondary">
                        <i class="bi bi-lock-fill text-warning me-1"></i> Data pesanan Anda tersimpan secara aman di database relasional.
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

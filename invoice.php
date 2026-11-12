<?php
/**
 * Halaman Faktur / Invoice Resmi Pemesanan
 * StrikeZone Fishing Tackle
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$invoiceNo = sanitize($_GET['inv'] ?? '');

if (empty($invoiceNo)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Ambil data order
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE nomor_invoice = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $invoiceNo);
mysqli_stmt_execute($stmt);
$resOrder = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($resOrder);
mysqli_stmt_close($stmt);

if (!$order) {
    set_flash('danger', 'Invoice pesanan tidak ditemukan!');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Ambil item order
$orderId = (int)$order['id'];
$resItems = mysqli_query($conn, "SELECT oi.*, p.gambar, p.brand FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $orderId");
$orderItems = [];
if ($resItems) {
    while ($it = mysqli_fetch_assoc($resItems)) {
        $orderItems[] = $it;
    }
}

// Status badge helper
$statusBadge = match($order['status']) {
    'menunggu_pembayaran', 'pending' => '<span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-clock-history me-1"></i> Menunggu Pembayaran</span>',
    'diproses' => '<span class="badge bg-info text-dark px-3 py-2"><i class="bi bi-gear-wide me-1"></i> Pesanan Sedang Diproses</span>',
    'dikirim' => '<span class="badge bg-primary text-white px-3 py-2"><i class="bi bi-truck me-1"></i> Sedang Dalam Pengiriman</span>',
    'selesai' => '<span class="badge bg-success text-white px-3 py-2"><i class="bi bi-check2-circle me-1"></i> Pesanan Selesai</span>',
    'dibatalkan' => '<span class="badge bg-danger text-white px-3 py-2"><i class="bi bi-x-circle me-1"></i> Dibatalkan</span>',
    default => '<span class="badge bg-secondary px-3 py-2">' . htmlspecialchars($order['status']) . '</span>'
};

$pageTitle = 'Invoice ' . $order['nomor_invoice'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Tombol Navigasi & Cetak (Sembunyi saat cetak) -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 no-print">
    <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-light btn-sm fw-semibold">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Marketplace
    </a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-info btn-sm fw-semibold">
            <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
        </button>
        <?php
        $waMsg = "Halo Admin StrikeZone Fishing, saya ingin konfirmasi pembayaran untuk Invoice #" . $order['nomor_invoice'] . " dengan total tagihan " . format_rupiah($order['total_bayar']) . " atas nama " . $order['nama_lengkap'] . ". Terima kasih.";
        ?>
        <a href="https://wa.me/6281234567890?text=<?= urlencode($waMsg) ?>" target="_blank" class="btn btn-success btn-sm fw-semibold">
            <i class="bi bi-whatsapp me-1"></i> Konfirmasi via WhatsApp
        </a>
    </div>
</div>

<!-- Lembar Faktur Invoice Resmi -->
<div class="card invoice-card p-4 p-md-5 mb-5 border-0">
    <!-- Header Invoice -->
    <div class="row align-items-center pb-4 mb-4 border-bottom">
        <div class="col-sm-7">
            <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="StrikeZone Fishing" height="48" class="mb-2">
            <div class="text-muted small">
                <strong>STRIKEZONE FISHING TACKLE</strong><br>
                Fibrian - Jl. Raya Bengkulu No. 12, Kota Bengkulu, Bengkulu 38224<br>
                WhatsApp: +62 812-3456-7890 | Email: billing@strikezonefishing.com
            </div>
        </div>
        <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
            <h3 class="fw-extrabold text-primary mb-1">FAKTUR PESANAN</h3>
            <div class="text-dark fw-bold mb-1">No: <?= htmlspecialchars($order['nomor_invoice']) ?></div>
            <div class="text-muted small mb-2">Tanggal: <?= date('d M Y, H:i', strtotime($order['created_at'])) ?> WIB</div>
            <div><?= $statusBadge ?></div>
        </div>
    </div>

    <!-- Informasi Pembeli & Pengiriman -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6">
            <h6 class="text-uppercase text-muted fw-bold small mb-2">Ditagihkan & Dikirimkan Kepada:</h6>
            <div class="fw-bold fs-6 text-dark"><?= htmlspecialchars($order['nama_lengkap']) ?></div>
            <div class="text-secondary small">
                <i class="bi bi-whatsapp text-success me-1"></i> <?= htmlspecialchars($order['no_whatsapp']) ?><br>
                <i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($order['email']) ?><br>
                <i class="bi bi-geo-alt me-1 text-danger"></i> <?= nl2br(htmlspecialchars($order['alamat_lengkap'])) ?><br>
                <?= htmlspecialchars($order['kota']) ?> &mdash; <?= htmlspecialchars($order['kode_pos']) ?>
            </div>
        </div>
        <div class="col-sm-6">
            <h6 class="text-uppercase text-muted fw-bold small mb-2">Metode Logistik & Pembayaran:</h6>
            <div class="p-3 bg-light rounded-3 small">
                <div class="mb-2">
                    <span class="text-muted">Kurir Pengiriman:</span><br>
                    <strong class="text-dark"><i class="bi bi-truck text-primary me-1"></i> <?= htmlspecialchars($order['metode_pengiriman']) ?></strong>
                </div>
                <div>
                    <span class="text-muted">Metode Pembayaran:</span><br>
                    <strong class="text-dark"><i class="bi bi-credit-card text-success me-1"></i> <?= htmlspecialchars($order['metode_pembayaran']) ?></strong>
                </div>
                <?php if (!empty($order['catatan'])): ?>
                    <div class="mt-2 pt-2 border-top">
                        <span class="text-muted">Catatan Pembeli:</span><br>
                        <em><?= htmlspecialchars($order['catatan']) ?></em>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Produk -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 50%;">Deskripsi Perlengkapan Pancing</th>
                    <th class="text-end" style="width: 15%;">Harga</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-end" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($orderItems as $item): 
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <strong class="text-dark"><?= htmlspecialchars($item['nama_produk']) ?></strong>
                            <?php if (!empty($item['brand'])): ?>
                                <span class="badge bg-secondary ms-1 small"><?= htmlspecialchars($item['brand']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-dark"><?= format_rupiah($item['harga_satuan']) ?></td>
                        <td class="text-center text-dark fw-bold"><?= $item['qty'] ?></td>
                        <td class="text-end fw-bold text-dark"><?= format_rupiah($item['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Subtotal Produk:</td>
                    <td class="text-end fw-bold"><?= format_rupiah($order['total_produk']) ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Biaya Pengiriman (Ongkir):</td>
                    <td class="text-end fw-bold"><?= format_rupiah($order['ongkos_kirim']) ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Proteksi Pipa PVC Guard:</td>
                    <td class="text-end text-success fw-bold">GRATIS</td>
                </tr>
                <tr class="table-primary">
                    <td colspan="4" class="text-end fw-extrabold fs-5 text-dark">TOTAL TAGIHAN:</td>
                    <td class="text-end fw-extrabold fs-5 text-primary"><?= format_rupiah($order['total_bayar']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Instruksi Pembayaran -->
    <div class="row g-3">
        <div class="col-md-7">
            <div class="p-3 bg-light rounded-3 small">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle-fill text-primary me-1"></i> Instruksi Pembayaran:</h6>
                <p class="mb-1 text-muted">Silakan selesaikan pembayaran sesuai total tagihan di atas ke rekening resmi toko:</p>
                <ul class="mb-2 ps-3 text-dark">
                    <li><strong>BCA:</strong> <code>873-098-1122</code> a.n. PT STRIKEZONE INDONESIA</li>
                    <li><strong>Mandiri:</strong> <code>168-00-554433-2</code> a.n. PT STRIKEZONE INDONESIA</li>
                    <li><strong>BRI:</strong> <code>0122-01-002345-50-8</code> a.n. PT STRIKEZONE INDONESIA</li>
                </ul>
                <div class="text-muted">Setelah transfer, mohon klik tombol <strong>Konfirmasi via WhatsApp</strong> di atas atau kirimkan bukti transfer ke <strong>+62 812-3456-7890</strong>.</div>
            </div>
        </div>
        <div class="col-md-5 text-end d-flex flex-column justify-content-end">
            <div class="small text-muted mb-4">Bengkulu, <?= date('d F Y') ?></div>
            <div class="fw-bold text-dark mb-1">STRIKEZONE FISHING TACKLE</div>
            <div class="small text-success"><i class="bi bi-person-badge-fill me-1"></i>Fibrian - Penjual Terverifikasi</div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

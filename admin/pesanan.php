<?php
/**
 * Halaman Kelola Transaksi Pesanan Masuk
 * StrikeZone Fishing Tackle Admin
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Hanya admin yang boleh mengakses panel ini
require_admin();

// Update Status Pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = sanitize($_POST['status'] ?? '');

    $allowedStatus = ['pending', 'menunggu_pembayaran', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
    if ($orderId > 0 && in_array($newStatus, $allowedStatus)) {
        $stmtUp = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmtUp, "si", $newStatus, $orderId);
        if (mysqli_stmt_execute($stmtUp)) {
            set_flash('success', 'Status pesanan ID #' . $orderId . ' berhasil diubah menjadi: ' . strtoupper($newStatus));
        } else {
            set_flash('danger', 'Gagal memperbarui status: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($stmtUp);
    }
    header('Location: ' . BASE_URL . '/admin/pesanan.php');
    exit;
}

// Filter Status
$filterStatus = sanitize($_GET['status'] ?? '');
$whereSql = "1=1";
if (!empty($filterStatus)) {
    $cleanStatus = mysqli_real_escape_string($conn, $filterStatus);
    $whereSql .= " AND status = '$cleanStatus'";
}

$sql = "SELECT * FROM orders WHERE $whereSql ORDER BY id DESC";
$res = mysqli_query($conn, $sql);
$orders = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $orders[] = $row;
    }
}

$pageTitle = 'Kelola Transaksi Pesanan';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="bi bi-truck text-info me-2"></i>Kelola Transaksi & Pengiriman
        </h2>
        <p class="text-secondary small mb-0">Pantau pembayaran, ubah status pesanan, dan cetak faktur pengiriman</p>
    </div>
</div>

<!-- Navigasi Tab Admin -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill text-light">
        <i class="bi bi-graph-up me-1"></i> Ringkasan Toko
    </a>
    <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill text-light">
        <i class="bi bi-box-seam me-1"></i> Kelola Produk (CRUD)
    </a>
    <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-primary btn-sm px-3 rounded-pill">
        <i class="bi bi-truck me-1"></i> Daftar Transaksi Pesanan
    </a>
</div>

<!-- Filter Kategori Status -->
<div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom border-secondary border-opacity-25">
    <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-sm <?= empty($filterStatus) ? 'btn-info' : 'btn-outline-secondary text-light' ?>">
        Semua Pesanan
    </a>
    <a href="<?= BASE_URL ?>/admin/pesanan.php?status=menunggu_pembayaran" class="btn btn-sm <?= $filterStatus === 'menunggu_pembayaran' ? 'btn-warning' : 'btn-outline-secondary text-light' ?>">
        Menunggu Pembayaran
    </a>
    <a href="<?= BASE_URL ?>/admin/pesanan.php?status=diproses" class="btn btn-sm <?= $filterStatus === 'diproses' ? 'btn-info' : 'btn-outline-secondary text-light' ?>">
        Sedang Diproses
    </a>
    <a href="<?= BASE_URL ?>/admin/pesanan.php?status=dikirim" class="btn btn-sm <?= $filterStatus === 'dikirim' ? 'btn-primary' : 'btn-outline-secondary text-light' ?>">
        Dalam Pengiriman
    </a>
    <a href="<?= BASE_URL ?>/admin/pesanan.php?status=selesai" class="btn btn-sm <?= $filterStatus === 'selesai' ? 'btn-success' : 'btn-outline-secondary text-light' ?>">
        Pesanan Selesai
    </a>
</div>

<!-- Tabel Daftar Pesanan -->
<div class="card card-custom p-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-white mb-0">Total Transaksi: <?= count($orders) ?> Pesanan</h5>
    </div>

    <?php if (empty($orders)): ?>
        <div class="p-5 text-center text-secondary">
            <i class="bi bi-bag-x fs-1 d-block mb-2"></i>
            Tidak ada transaksi pesanan yang sesuai dengan filter.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 15%;">No. Invoice</th>
                        <th style="width: 20%;">Pembeli & Tujuan</th>
                        <th style="width: 15%;">Metode Logistik</th>
                        <th style="width: 15%;">Total Tagihan</th>
                        <th style="width: 20%;">Status Transaksi</th>
                        <th style="width: 15%;" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/invoice.php?inv=<?= $ord['nomor_invoice'] ?>" class="text-info fw-bold text-decoration-none">
                                    <?= htmlspecialchars($ord['nomor_invoice']) ?>
                                </a>
                                <div class="text-secondary small"><?= date('d M Y, H:i', strtotime($ord['created_at'])) ?> WIB</div>
                            </td>
                            <td>
                                <strong class="text-white d-block small"><?= htmlspecialchars($ord['nama_lengkap']) ?></strong>
                                <span class="text-secondary small d-block"><?= htmlspecialchars($ord['kota']) ?></span>
                                <a href="https://wa.me/<?= preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $ord['no_whatsapp'])) ?>" target="_blank" class="text-success small text-decoration-none">
                                    <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($ord['no_whatsapp']) ?>
                                </a>
                            </td>
                            <td class="small text-light">
                                <i class="bi bi-box-seam text-info me-1"></i> <?= htmlspecialchars($ord['metode_pengiriman']) ?><br>
                                <small class="text-secondary"><?= htmlspecialchars($ord['metode_pembayaran']) ?></small>
                            </td>
                            <td class="text-info fw-bold small">
                                <?= format_rupiah($ord['total_bayar']) ?>
                            </td>
                            <td>
                                <form action="<?= BASE_URL ?>/admin/pesanan.php" method="POST" class="d-flex align-items-center gap-1 m-0">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="menunggu_pembayaran" <?= $ord['status'] === 'menunggu_pembayaran' ? 'selected' : '' ?>>Menunggu Bayar</option>
                                        <option value="diproses" <?= $ord['status'] === 'diproses' ? 'selected' : '' ?>>Diproses (Packing)</option>
                                        <option value="dikirim" <?= $ord['status'] === 'dikirim' ? 'selected' : '' ?>>Dikirim Kurir</option>
                                        <option value="selesai" <?= $ord['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                        <option value="dibatalkan" <?= $ord['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/invoice.php?inv=<?= $ord['nomor_invoice'] ?>" class="btn btn-outline-info btn-sm fw-semibold" title="Buka / Cetak Invoice">
                                    <i class="bi bi-printer me-1"></i> Invoice
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

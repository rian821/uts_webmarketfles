<?php
/**
 * Halaman Kelola Data Produk (CRUD Read)
 * StrikeZone Fishing Tackle Admin
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Hanya admin yang boleh mengakses panel ini
require_admin();

$search = sanitize($_GET['search'] ?? '');
$catId = (int)($_GET['cat'] ?? 0);

$where = ["1=1"];
if (!empty($search)) {
    $clean = mysqli_real_escape_string($conn, $search);
    $where[] = "(p.nama_produk LIKE '%$clean%' OR p.brand LIKE '%$clean%')";
}
if ($catId > 0) {
    $where[] = "p.category_id = $catId";
}

$whereSql = implode(' AND ', $where);
$sql = "SELECT p.*, c.nama_kategori 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $whereSql 
        ORDER BY p.id DESC";

$res = mysqli_query($conn, $sql);
$products = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $products[] = $row;
    }
}

// Ambil Kategori untuk Filter
$allCats = [];
$qCat = mysqli_query($conn, "SELECT id, nama_kategori FROM categories ORDER BY nama_kategori ASC");
if ($qCat) {
    while ($c = mysqli_fetch_assoc($qCat)) {
        $allCats[] = $c;
    }
}

$pageTitle = 'Kelola Produk Alat Pancing (CRUD)';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="bi bi-box-seam text-info me-2"></i>Kelola Data Produk Pancing (CRUD)
        </h2>
        <p class="text-secondary small mb-0">Tambah, perbarui, dan hapus data inventaris peralatan toko pancing</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/admin/produk-tambah.php" class="btn btn-primary fw-bold shadow">
            <i class="bi bi-plus-circle me-1"></i> Tambah Produk Baru
        </a>
    </div>
</div>

<!-- Navigasi Tab Admin -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill text-light">
        <i class="bi bi-graph-up me-1"></i> Ringkasan Toko
    </a>
    <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-primary btn-sm px-3 rounded-pill">
        <i class="bi bi-box-seam me-1"></i> Kelola Produk (CRUD)
    </a>
    <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill text-light">
        <i class="bi bi-truck me-1"></i> Daftar Transaksi Pesanan
    </a>
</div>

<!-- Card Filter & Search -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari nama piranti atau merek..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select name="cat" class="form-select form-select-sm">
                <option value="0">Semua Kategori</option>
                <?php foreach ($allCats as $ac): ?>
                    <option value="<?= $ac['id'] ?>" <?= $catId == $ac['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ac['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
            <?php if (!empty($search) || $catId > 0): ?>
                <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline-secondary btn-sm">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Tabel Data Produk -->
<div class="card card-custom p-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-white mb-0">Total Inventaris: <?= count($products) ?> Produk</h5>
    </div>

    <?php if (empty($products)): ?>
        <div class="p-5 text-center text-secondary">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Tidak ada data produk yang sesuai filter.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 10%;">Gambar</th>
                        <th style="width: 30%;">Nama & Merek Piranti</th>
                        <th style="width: 15%;">Kategori</th>
                        <th style="width: 13%;">Harga</th>
                        <th style="width: 10%;" class="text-center">Stok</th>
                        <th style="width: 7%;" class="text-center">Terjual</th>
                        <th style="width: 10%;" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($products as $p): 
                    ?>
                        <tr>
                            <td class="text-center text-secondary small"><?= $no++ ?></td>
                            <td>
                                <img src="<?= get_product_image_url($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" class="rounded-3 border border-secondary" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <strong class="text-white d-block small"><?= htmlspecialchars($p['nama_produk']) ?></strong>
                                <span class="badge bg-secondary bg-opacity-50 text-light small"><?= htmlspecialchars($p['brand']) ?></span>
                                <small class="text-secondary ms-1">(<?= $p['berat_gram'] ?>g)</small>
                            </td>
                            <td>
                                <span class="badge bg-dark border border-secondary text-info small">
                                    <?= htmlspecialchars($p['nama_kategori'] ?? 'Lainnya') ?>
                                </span>
                            </td>
                            <td class="text-info fw-bold small">
                                <?= format_rupiah($p['harga']) ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['stok'] > 5): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success small"><?= $p['stok'] ?></span>
                                <?php elseif ($p['stok'] > 0): ?>
                                    <span class="badge bg-warning bg-opacity-25 text-warning small"><?= $p['stok'] ?> (Kritis)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger small">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-secondary small">
                                <?= $p['terjual'] ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-outline-light" title="Pratinjau di Toko">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/produk-edit.php?id=<?= $p['id'] ?>" class="btn btn-outline-info" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/produk-hapus.php?id=<?= $p['id'] ?>" class="btn btn-outline-danger btn-confirm-delete" data-message="Hapus produk '<?= htmlspecialchars($p['nama_produk']) ?>'?" title="Hapus Produk">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

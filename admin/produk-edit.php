<?php
/**
 * Halaman Edit Data Produk (CRUD Update)
 * StrikeZone Fishing Tackle Admin
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Hanya admin yang boleh mengakses panel ini
require_admin();

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    header('Location: ' . BASE_URL . '/admin/produk.php');
    exit;
}

// Ambil data produk
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$product) {
    set_flash('danger', 'Produk tidak ditemukan!');
    header('Location: ' . BASE_URL . '/admin/produk.php');
    exit;
}

// Ambil Kategori
$categories = [];
$categoryIds = [];
$resCat = mysqli_query($conn, "SELECT id, nama_kategori FROM categories ORDER BY id ASC");
if ($resCat) {
    while ($c = mysqli_fetch_assoc($resCat)) {
        $categories[] = $c;
        $categoryIds[] = (int)$c['id'];
    }
}

$presetImages = [
    'default_product.svg' => 'Gambar Default',
    'Shimano_Stradic.jpg' => 'Shimano Stradic FL 4000XG',
    'p1_shimano_cruzar.svg' => 'Joran Pancing Carbon (Rod)',
    'p3_shimano_stradic.svg' => 'Preset Spinning Reel Hagane',
    'p4_daiwa_bg.svg' => 'Preset Saltwater Heavy Duty Reel',
    'p6_duraking_pe.svg' => 'Preset Senar Braided PE X8',
    'p8_rapala_lure.svg' => 'Preset Umpan Minnow Rapala Lure',
    'p9_soft_frog.svg' => 'Preset Umpan Kodok Soft Frog',
    'p10_metal_jig.svg' => 'Preset Metal Jig Glow In The Dark',
    'p11_mustad_hooks.svg' => 'Preset Kail Baja Karbon Box',
    'p12_tas_joran.svg' => 'Preset Tas Tabung Hardcase Joran',
];
$presetImg = 'keep_current';

// Proses Update Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaProduk  = trim($_POST['nama_produk'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 1);
    $brand       = trim($_POST['brand'] ?? '');
    $harga       = filter_var($_POST['harga'] ?? null, FILTER_VALIDATE_FLOAT);
    $stok        = filter_var($_POST['stok'] ?? null, FILTER_VALIDATE_INT);
    $beratGram   = filter_var($_POST['berat_gram'] ?? null, FILTER_VALIDATE_INT);
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $spesifikasi = trim($_POST['spesifikasi'] ?? '');
    $presetImg   = $_POST['preset_img'] ?? 'keep_current';

    // Tampilkan kembali nilai yang dikirim admin jika validasi gagal.
    $product['nama_produk'] = $namaProduk;
    $product['category_id'] = $categoryId;
    $product['brand'] = $brand;
    $product['harga'] = $harga === false ? ($_POST['harga'] ?? '') : $harga;
    $product['stok'] = $stok === false ? ($_POST['stok'] ?? '') : $stok;
    $product['berat_gram'] = $beratGram === false ? ($_POST['berat_gram'] ?? '') : $beratGram;
    $product['deskripsi'] = $deskripsi;
    $product['spesifikasi'] = $spesifikasi;

    $validationError = '';
    if ($namaProduk === '' || $brand === '' || $harga === false || $harga <= 0) {
        $validationError = 'Nama produk, merek, dan harga wajib diisi dengan benar.';
    } elseif ($stok === false || $stok < 0 || $beratGram === false || $beratGram < 0) {
        $validationError = 'Stok dan berat harus berupa angka nol atau lebih.';
    } elseif (!in_array($categoryId, $categoryIds, true)) {
        $validationError = 'Kategori produk tidak valid.';
    } elseif ($presetImg !== 'keep_current' && !array_key_exists($presetImg, $presetImages)) {
        $validationError = 'Pilihan gambar preset tidak valid.';
    }

    $gambarName = $product['gambar'];
    $newImagePath = null;
    $upload = $_FILES['gambar_file'] ?? null;

    if ($validationError === '' && $upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($upload['error'] !== UPLOAD_ERR_OK) {
            $validationError = 'Upload foto gagal. Silakan pilih ulang file foto.';
        } elseif ($upload['size'] > 5 * 1024 * 1024) {
            $validationError = 'Ukuran foto maksimal 5 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($upload['tmp_name']);
            $allowedMime = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];

            if (!isset($allowedMime[$mime])) {
                $validationError = 'Format foto harus JPG, PNG, atau WebP.';
            } else {
                $newFileName = 'prod_' . bin2hex(random_bytes(12)) . '.' . $allowedMime[$mime];
                $targetPath = __DIR__ . '/../assets/img/products/' . $newFileName;
                if (move_uploaded_file($upload['tmp_name'], $targetPath)) {
                    $gambarName = $newFileName;
                    $newImagePath = $targetPath;
                } else {
                    $validationError = 'Foto tidak berhasil disimpan. Periksa izin folder aset.';
                }
            }
        }
    } elseif ($validationError === '' && $presetImg !== 'keep_current') {
        $presetPath = __DIR__ . '/../assets/img/products/' . $presetImg;
        if (is_file($presetPath)) {
            $gambarName = $presetImg;
        } else {
            $validationError = 'File gambar preset tidak ditemukan.';
        }
    }

    if ($validationError !== '') {
        if ($newImagePath && is_file($newImagePath)) {
            unlink($newImagePath);
        }
        set_flash('danger', $validationError);
    } else {
        $sqlUpdate = "UPDATE products SET 
                        category_id = ?, nama_produk = ?, brand = ?, harga = ?, 
                        stok = ?, berat_gram = ?, deskripsi = ?, spesifikasi = ?, gambar = ? 
                      WHERE id = ?";

        $stmtUp = mysqli_prepare($conn, $sqlUpdate);
        mysqli_stmt_bind_param(
            $stmtUp, 
            "issdiisssi", 
            $categoryId, 
            $namaProduk, 
            $brand, 
            $harga, 
            $stok, 
            $beratGram, 
            $deskripsi, 
            $spesifikasi, 
            $gambarName, 
            $productId
        );

        if (mysqli_stmt_execute($stmtUp)) {
            mysqli_stmt_close($stmtUp);
            if ($newImagePath && str_starts_with($product['gambar'], 'prod_')) {
                $oldImagePath = __DIR__ . '/../assets/img/products/' . basename($product['gambar']);
                if (is_file($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            set_flash('success', 'Perubahan produk "' . $namaProduk . '" berhasil disimpan!');
            header('Location: ' . BASE_URL . '/admin/produk.php');
            exit;
        } else {
            mysqli_stmt_close($stmtUp);
            if ($newImagePath && is_file($newImagePath)) {
                unlink($newImagePath);
            }
            set_flash('danger', 'Gagal memperbarui data: ' . mysqli_error($conn));
        }
    }
}

$pageTitle = 'Edit Produk ' . $product['nama_produk'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="bi bi-pencil-square text-info me-2"></i>Perbarui Data Produk Piranti
        </h2>
        <p class="text-secondary small mb-0">Ubah harga, stok ketersediaan, atau deskripsi teknis piranti</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Tabel
    </a>
</div>

<div class="row justify-content-center mb-5">
    <div class="col-lg-9">
        <div class="card card-custom p-4 p-md-5">
            <form action="<?= BASE_URL ?>/admin/produk-edit.php?id=<?= $productId ?>" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Foto Saat Ini -->
                    <div class="col-12 text-center p-3 bg-dark rounded-3 border border-secondary mb-2">
                        <span class="text-secondary small d-block mb-2">Gambar Piranti Saat Ini:</span>
                        <img id="productImagePreview" src="<?= get_product_image_url($product['gambar']) ?>" alt="Preview foto produk" class="rounded-3 border border-secondary" style="max-width: 100%; max-height: 220px; object-fit: contain;">
                    </div>

                    <!-- Nama Produk -->
                    <div class="col-12">
                        <label class="form-label text-secondary small fw-bold">Nama Lengkap Piranti *</label>
                        <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($product['nama_produk']) ?>" required>
                    </div>

                    <!-- Kategori & Brand -->
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">Kategori Produk *</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">Merek / Brand *</label>
                        <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($product['brand']) ?>" required>
                    </div>

                    <!-- Harga, Stok & Berat -->
                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-bold">Harga Jual (Rp) *</label>
                        <input type="number" name="harga" class="form-control" value="<?= (int)$product['harga'] ?>" min="1000" step="1000" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-bold">Jumlah Stok Unit *</label>
                        <input type="number" name="stok" class="form-control" value="<?= $product['stok'] ?>" min="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-bold">Bobot Paket (Gram)</label>
                        <input type="number" name="berat_gram" class="form-control" value="<?= $product['berat_gram'] ?>">
                    </div>

                    <!-- Spesifikasi -->
                    <div class="col-12">
                        <label class="form-label text-secondary small fw-bold">Spesifikasi Teknis (Format: Label: Nilai | Label: Nilai)</label>
                        <input type="text" name="spesifikasi" class="form-control" value="<?= htmlspecialchars($product['spesifikasi'] ?? '') ?>">
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12">
                        <label class="form-label text-secondary small fw-bold">Deskripsi Lengkap *</label>
                        <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($product['deskripsi']) ?></textarea>
                    </div>

                    <!-- Upload Gambar Baru -->
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">Ganti Foto (Upload File Baru)</label>
                        <input type="file" name="gambar_file" id="productImageFile" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <small class="text-secondary" style="font-size: 0.75rem;">Format JPG, PNG, WebP; maksimal 5 MB. Biarkan kosong untuk mempertahankan foto.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">Atau Pilih Gambar dari Aset</label>
                        <select name="preset_img" id="presetImage" class="form-select">
                            <option value="keep_current">-- Tetap Gunakan Gambar Saat Ini --</option>
                            <?php foreach ($presetImages as $imageFile => $imageLabel): ?>
                                <option value="<?= htmlspecialchars($imageFile) ?>" <?= $presetImg === $imageFile ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($imageLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-secondary" style="font-size: 0.75rem;">Pilih Shimano Stradic untuk menggunakan Shimano_Stradic.jpg.</small>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top border-secondary d-flex gap-2 justify-content-end">
                        <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-5 fw-bold">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const imagePreview = document.getElementById('productImagePreview');
    const imageFileInput = document.getElementById('productImageFile');
    const presetImageSelect = document.getElementById('presetImage');
    const currentImageUrl = imagePreview.src;

    imageFileInput.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (file) {
            imagePreview.src = URL.createObjectURL(file);
            presetImageSelect.value = 'keep_current';
        }
    });

    presetImageSelect.addEventListener('change', function () {
        if (this.value === 'keep_current') {
            imagePreview.src = currentImageUrl;
            imageFileInput.value = '';
        } else {
            imagePreview.src = '<?= BASE_URL ?>/assets/img/products/' + encodeURIComponent(this.value);
            imageFileInput.value = '';
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

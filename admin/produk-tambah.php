<?php
/**
 * Halaman Tambah Produk Baru (CRUD Create)
 * StrikeZone Fishing Tackle Admin
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Hanya admin yang boleh mengakses panel ini
require_admin();

// Ambil Kategori untuk Pilihan Dropdown
$categories = [];
$resCat = mysqli_query($conn, "SELECT id, nama_kategori FROM categories ORDER BY id ASC");
if ($resCat) {
    while ($c = mysqli_fetch_assoc($resCat)) {
        $categories[] = $c;
    }
}

// Proses Simpan Produk Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaProduk  = sanitize($_POST['nama_produk'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 1);
    $brand       = sanitize($_POST['brand'] ?? '');
    $harga       = (float)($_POST['harga'] ?? 0);
    $stok        = (int)($_POST['stok'] ?? 0);
    $beratGram   = (int)($_POST['berat_gram'] ?? 500);
    $deskripsi   = sanitize($_POST['deskripsi'] ?? '');
    $spesifikasi = sanitize($_POST['spesifikasi'] ?? '');
    $presetImg   = sanitize($_POST['preset_img'] ?? '');

    // Validasi Dasar
    if (empty($namaProduk) || empty($brand) || $harga <= 0) {
        set_flash('danger', 'Harap lengkapi nama produk, brand, dan harga dengan benar.');
    } else {
        // Buat slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $namaProduk)));
        $slug .= '-' . rand(100, 999);

        // Upload Gambar atau Gunakan Preset
        $gambarName = 'default_product.svg';

        if (!empty($_FILES['gambar_file']['name'])) {
            $file = $_FILES['gambar_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

            if (in_array($ext, $allowedExt)) {
                $newFileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $targetPath = __DIR__ . '/../assets/img/products/' . $newFileName;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $gambarName = $newFileName;
                }
            }
        } elseif (!empty($presetImg)) {
            $gambarName = $presetImg;
        }

        // Simpan ke Database
        $userId = 1; // Default admin / seller id
        $sqlInsert = "INSERT INTO products (user_id, category_id, nama_produk, slug, brand, harga, stok, berat_gram, deskripsi, spesifikasi, gambar) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sqlInsert);
        mysqli_stmt_bind_param(
            $stmt, 
            "iisssdiisss", 
            $userId, 
            $categoryId, 
            $namaProduk, 
            $slug, 
            $brand, 
            $harga, 
            $stok, 
            $beratGram, 
            $deskripsi, 
            $spesifikasi, 
            $gambarName
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            set_flash('success', 'Produk piranti pancing "' . htmlspecialchars($namaProduk) . '" berhasil ditambahkan!');
            header('Location: ' . BASE_URL . '/admin/produk.php');
            exit;
        } else {
            set_flash('danger', 'Gagal menyimpan produk: ' . mysqli_error($conn));
        }
    }
}

$pageTitle = 'Tambah Produk Pancing Baru';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="bi bi-plus-circle text-info me-2"></i>Tambah Perlengkapan Pancing Baru
        </h2>
        <p class="text-secondary small mb-0">Isi formulir spesifikasi produk untuk menambah katalog toko</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Tabel Produk
    </a>
</div>

<div class="row justify-content-center mb-5">
    <div class="col-lg-9">
        <div class="card card-custom p-4 p-md-5">
            <form action="<?= BASE_URL ?>/admin/produk-tambah.php" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Nama Produk -->
                    <div class="col-12">
                        <label class="form-label text-secondary small fw-bold">Nama Lengkap Piranti Pancing *</label>
                        <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Shimano Vanford 2500 Spinning Reel HG" required>
                    </div>

                    <!-- Kategori & Brand -->
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">Kategori Produk *</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">Merek / Brand *</label>
                        <input type="text" name="brand" class="form-control" placeholder="Contoh: Shimano, Daiwa, Rapala, Ryobi, Mustad" required>
                    </div>

                    <!-- Harga, Stok & Berat -->
                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-bold">Harga Jual (Rp) *</label>
                        <input type="number" name="harga" class="form-control" placeholder="Contoh: 450000" min="1000" step="1000" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-bold">Jumlah Stok Unit *</label>
                        <input type="number" name="stok" class="form-control" placeholder="Contoh: 15" min="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-bold">Bobot Paket (Gram)</label>
                        <input type="number" name="berat_gram" class="form-control" placeholder="Contoh: 400" value="400">
                    </div>

                    <!-- Spesifikasi Teknis -->
                    <div class="col-12">
                        <label class="form-label text-secondary small fw-bold">Spesifikasi Teknis (Format: Label: Nilai | Label: Nilai)</label>
                        <input type="text" name="spesifikasi" class="form-control" placeholder="Contoh: Panjang: 180cm | Line: 10-20 LBS | Action: Medium Fast | Guides: Fuji Ring">
                        <small class="text-secondary" style="font-size: 0.75rem;">Pisahkan setiap poin spesifikasi dengan tanda pipa tegak ( | ).</small>
                    </div>

                    <!-- Deskripsi Produk -->
                    <div class="col-12">
                        <label class="form-label text-secondary small fw-bold">Deskripsi Lengkap Produk *</label>
                        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan keunggulan piranti, material pembuatan, rekomendasi spot mancing (kolam, laut, sungai), dan jaminan produk..." required></textarea>
                    </div>

                    <!-- Upload Gambar / Preset Gambar -->
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">Upload File Foto / Gambar Sendiri</label>
                        <input type="file" name="gambar_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg">
                        <small class="text-secondary" style="font-size: 0.75rem;">Format didukung: JPG, PNG, WEBP, SVG.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">Atau Pilih Preset Gambar Vektor</label>
                        <select name="preset_img" class="form-select">
                            <option value="p1_shimano_cruzar.svg">Preset Joran Pancing Carbon (Rod)</option>
                            <option value="p3_shimano_stradic.svg">Preset Spinning Reel Hagane</option>
                            <option value="p4_daiwa_bg.svg">Preset Saltwater Heavy Duty Reel</option>
                            <option value="p6_duraking_pe.svg">Preset Senar Braided PE X8</option>
                            <option value="p8_rapala_lure.svg">Preset Umpan Minnow Rapala Lure</option>
                            <option value="p9_soft_frog.svg">Preset Umpan Kodok Soft Frog</option>
                            <option value="p10_metal_jig.svg">Preset Metal Jig Glow In The Dark</option>
                            <option value="p11_mustad_hooks.svg">Preset Kail Baja Karbon Box</option>
                            <option value="p12_tas_joran.svg">Preset Tas Tabung Hardcase Joran</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top border-secondary d-flex gap-2 justify-content-end">
                        <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-5 fw-bold">
                            <i class="bi bi-save me-1"></i> Simpan Produk Piranti
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

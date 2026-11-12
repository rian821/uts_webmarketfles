-- ============================================================
-- DATABASE MARKETPLACE ALAT PANCING TRADISIONAL
-- Nama Database: db_pancing_marketplace
-- Pengembang: Sistem Marketplace Alat Pancing Modern
-- Kompatibel: MySQL 5.7+ / MariaDB 10.4+ / InfinityFree
-- ============================================================
--
-- PENTING UNTUK HOSTING (InfinityFree / cPanel):
-- Baris `CREATE DATABASE` dan `USE` sengaja TIDAK dipakai di file ini karena
-- shared hosting tidak mengizinkan pembuatan database lewat SQL dan nama
-- database-nya berbeda (contoh: if0_xxxxxxx_pancing).
-- Cara import di InfinityFree: buka phpMyAdmin di vPanel, PILIH database
-- Anda lebih dulu di panel kiri, lalu klik tab "Import" dan pilih file ini.
-- (Di XAMPP: buat/pilih database `db_pancing_marketplace`, lalu Import.)

-- --------------------------------------------------------
-- 1. Tabel: users (Pengguna: Penjual / Admin dan Pembeli)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `product_reviews`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `alamat` TEXT DEFAULT NULL,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `foto` VARCHAR(255) DEFAULT 'default_user.png',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabel: categories (Kategori Alat Pancing)
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `deskripsi` TEXT DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-tag',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabel: products (Katalog Produk Alat Pancing)
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  `nama_produk` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `brand` VARCHAR(80) NOT NULL,
  `harga` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `stok` INT(11) NOT NULL DEFAULT 0,
  `berat_gram` INT(11) NOT NULL DEFAULT 500,
  `deskripsi` TEXT NOT NULL,
  `spesifikasi` TEXT DEFAULT NULL,
  `gambar` VARCHAR(255) NOT NULL DEFAULT 'default_product.png',
  `rating` DECIMAL(2, 1) NOT NULL DEFAULT 4.8,
  `terjual` INT(11) NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_products_user` (`user_id`),
  KEY `fk_products_category` (`category_id`),
  CONSTRAINT `fk_products_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabel: orders (Transaksi Pemesanan)
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nomor_invoice` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT(11) DEFAULT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `no_whatsapp` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `alamat_lengkap` TEXT NOT NULL,
  `kota` VARCHAR(100) NOT NULL,
  `kode_pos` VARCHAR(10) NOT NULL,
  `metode_pengiriman` VARCHAR(80) NOT NULL,
  `ongkos_kirim` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `metode_pembayaran` VARCHAR(80) NOT NULL,
  `catatan` TEXT DEFAULT NULL,
  `total_produk` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `total_bayar` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pending', 'menunggu_pembayaran', 'diproses', 'dikirim', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_orders_user` (`user_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Tabel: order_items (Detail Produk dalam Pesanan)
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `nama_produk` VARCHAR(200) NOT NULL,
  `harga_satuan` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `qty` INT(11) NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_order` (`order_id`),
  KEY `fk_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Tabel: product_reviews (Ulasan Produk / Testimoni)
-- --------------------------------------------------------
CREATE TABLE `product_reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `nama_penilai` VARCHAR(100) NOT NULL,
  `rating` INT(1) NOT NULL DEFAULT 5,
  `komentar` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_reviews_product` (`product_id`),
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATA AWAL (SEEDING DATA)
-- ============================================================

-- Data Pengguna: Penjual/Admin Toko & Pembeli Uji Coba
-- Password hash menggunakan password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `alamat`, `role`) VALUES
(1, 'Admin StrikeZone Fishing', 'admin@strikezone.com', '$2y$10$Ud0StC8Xokgoa6gruGHD8ui/WXjzTI2uw931DYKgIKzLI4TGoRzf.', '081234567890', 'Ruko Sentra Bahari No. 12, Jakarta Utara', 'admin'),
(2, 'Pak Samsul Nelayan', 'samsul@seller.com', '$2y$10$Ud0StC8Xokgoa6gruGHD8ui/WXjzTI2uw931DYKgIKzLI4TGoRzf.', '085712345678', 'Jl. Muara Baru Raya No. 45, Jakarta', 'user'),
(3, 'Budi Pemancing', 'budi@buyer.com', '$2y$10$Ud0StC8Xokgoa6gruGHD8ui/WXjzTI2uw931DYKgIKzLI4TGoRzf.', '089876543210', 'Jl. Danau Sunter Barat No. 8, Jakarta', 'user');

-- Data Kategori Produk
INSERT INTO `categories` (`id`, `nama_kategori`, `slug`, `deskripsi`, `icon`) VALUES
(1, 'Joran Pancing (Rods)', 'joran-pancing', 'Pilihan joran tegek, spinning, casting, dan jigging dari bahan solid carbon berkekuatan tinggi.', 'bi-slash-lg'),
(2, 'Reel Pancing (Reels)', 'reel-pancing', 'Reel spinning, baitcasting, dan overhead halus tahan korosi air asin dengan sistem drag bertenaga.', 'bi-disc'),
(3, 'Senar Pancing (Lines)', 'senar-pancing', 'Senar braided PE 8x/9x, fluorocarbon leader, dan monofilament berkualitas anti putus.', 'bi-soundwave'),
(4, 'Umpan & Lure (Baits)', 'umpan-lure', 'Koleksi umpan tiruan minnow, soft frog, metal jig glow in the dark, dan popper predator.', 'bi-water'),
(5, 'Kail & Aksesoris (Tackle)', 'kail-aksesoris', 'Kail baja karbon tajam, tackle box anti air, tang split ring, dan sarung joran pelindung.', 'bi-tools');

-- Data Produk Alat Pancing Realistis
INSERT INTO `products` (`id`, `user_id`, `category_id`, `nama_produk`, `slug`, `brand`, `harga`, `stok`, `berat_gram`, `deskripsi`, `spesifikasi`, `gambar`, `rating`, `terjual`, `is_featured`) VALUES
(1, 1, 1, 'Shimano Cruzar 2602 Solid Carbon Rod 180cm', 'shimano-cruzar-2602-solid-carbon-rod-180cm', 'Shimano', 385000.00, 25, 450, 
'Joran pancing legendaris Shimano Cruzar 2602 dengan material solid carbon yang terkenal sangat tangguh dan lentur. Dirancang khusus untuk pemancing kolam galatama ikan mas, bawal, maupun mancing muara liar.', 
'Panjang: 180 cm (6.0 ft) | Sambungan: 2 Bagian (Middle Joint) | Action: Medium | Line Weight: 10 - 20 LBS | Lure Weight: 10 - 30 Gram | Ring Guide: Hard Chromium Ceramic Coated | Handle: Eva Grip Ergonomis', 'p1_shimano_cruzar.svg', 4.9, 142, 1),

(2, 1, 1, 'Daiwa Tatula XT Baitcasting Rod 662MB', 'daiwa-tatula-xt-baitcasting-rod-662mb', 'Daiwa', 1450000.00, 12, 380, 
'Joran baitcasting kelas turnamen dari Daiwa dengan teknologi blank X45 Bias Carbon Technology yang meminimalisir puntiran saat melontarkan umpan dan mengunci monster predator air tawar.', 
'Panjang: 198 cm (6.6 ft) | Sambungan: 2 Section | Line: 8 - 17 LBS | Lure: 1/4 - 3/4 oz | Blank: High Volume Fiber Carbon | Guides: Fuji Aluminum Oxide | Reel Seat: Daiwa Exclusive Ergonomic', 'p2_daiwa_tatula_rod.svg', 5.0, 68, 1),

(3, 1, 2, 'Shimano Stradic FL 4000XG Saltwater Spinning Reel', 'shimano-stradic-fl-4000xg-saltwater-spinning-reel', 'Shimano', 2850000.00, 8, 550, 
'Reel spinning papan atas yang dilengkapi teknologi Hagane Gear, MicroModule Gear II, dan SilentDrive untuk putaran ultra halus tanpa getaran. Perlindungan X-Protect menjaga bagian internal dari karat air laut.', 
'Gear Ratio: 6.2:1 | Max Drag: 11.0 kg (24 lbs) | Ball Bearings: 6 S A-RB + 1 Roller | Bobot: 280 Gram | Line Capacity (PE/m): #1.5/320m, #2.0/240m | Handle: One-Piece Screw In CNC Aluminum', 'Shimano_Stradic.jpg', 4.9, 89, 1),

(4, 1, 2, 'Daiwa BG Black Gold 3000 Heavy Duty Saltwater', 'daiwa-bg-black-gold-3000-heavy-duty-saltwater', 'Daiwa', 1620000.00, 15, 600, 
'Daiwa BG adalah reel tempur berbadan logam HardBodyz anodized hitam dan emas yang terkenal sangat bandel untuk pertarungan sengit melawan ikan laut perkasa seperti kuwe/GT, tenggiri, dan kakap merah.', 
'Gear Ratio: 5.6:1 | Max Drag: 7.0 kg | Ball Bearings: 6 BB + 1 RB | Bodi: Full Anodized Machined Aluminum | Rotor: Air Rotor Ringan Dinamis | Spool: ABS Dynamic Cut Aluminum', 'p4_daiwa_bg.svg', 4.8, 115, 1),

(5, 1, 2, 'Ryobi Ultra Power 1000 Ultralight Fishing Reel', 'ryobi-ultra-power-1000-ultralight-fishing-reel', 'Ryobi', 475000.00, 20, 320, 
'Reel spesialis ultralight (UL) memancing sensasi tarikan ikan target kecil hingga sedang dengan bobot super ringan. Spool dangkal (shallow spool) membuat pelemparan lure mikro terasa sangat mulus dan jauh.', 
'Gear Ratio: 5.2:1 | Max Drag: 3.0 kg Carbon Drag System | Ball Bearings: 6 High Grade Steel | Bobot: 165 Gram | Handle: CNC Power Handle Metal Knob | Cocok Senar PE 0.4 - 0.8', 'p5_ryobi_reel.svg', 4.7, 93, 1),

(6, 1, 3, 'Duraking Natuna PE X8 Multicolor Braided Line 300m', 'duraking-natuna-pe-x8-multicolor-braided-line-300m', 'Duraking', 195000.00, 40, 200, 
'Senar PE rajutan 8 helai dengan lapisan Teflon coating ekstra licin, minim gesekan pada ring guide, serta zero-stretch sensitif terhadap gigitan halus ikan di kedalaman laut.', 
'Panjang: 300 Meter | Tipe: Multicolor (Ganti warna tiap 10 meter) | Ukuran: PE 2.5 (Test 35 LBS / Diameter 0.25mm) | Bahan: 100% UHMWPE Fiber Premium | Karakter: Lembut, Kuat Simpul, Cepat Tenggelam', 'p6_duraking_pe.svg', 4.9, 210, 1),

(7, 1, 3, 'Seaguar Blue Label 100% Fluorocarbon Leader 30LB', 'seaguar-blue-label-fluorocarbon-leader-30lb', 'Seaguar', 165000.00, 35, 150, 
'Leader fluorocarbon nomor 1 di dunia buatan Jepang dengan indeks refraksi hampir identik dengan air sehingga tidak terlihat oleh mata ikan. Memiliki ketahanan abrasi tingkat tinggi terhadap gigi tajam predator.', 
'Panjang: 30 Meter Spool | Kekuatan: 30 LBS (13.6 kg) | Diameter: 0.47 mm | Karakter: 100% Pure Fluorocarbon, Sinking Fast, UV Resistant, Anti Gigitan Karang', 'p7_seaguar_leader.svg', 4.8, 77, 0),

(8, 1, 4, 'Rapala X-Rap Magnum Deep Diver 30 Saltwater Lure', 'rapala-x-rap-magnum-deep-diver-30-saltwater-lure', 'Rapala', 235000.00, 18, 180, 
'Umpan diving trolling air asin legendaris dengan lidah bibir selam besar yang mampu menyelam hingga kedalaman 9 meter (30 kaki). Aksi goyangan agresif terbukti memicu insting memangsa ikan pelagis besar.', 
'Panjang: 16 cm | Bobot: 72 Gram | Kedalaman Selam: Hingga 9 Meter | Kecepatan Trolling: Hingga 13 Knots | Kail: 2 Kail Treble VMC 4X Strong Permasteel Anti Karat | Pola: Red Head Chrome Holographic', 'p8_rapala_lure.svg', 5.0, 54, 1),

(9, 1, 4, 'Soft Frog Killer Lure Umpan Kodok Casting Gabus Toman', 'soft-frog-killer-lure-umpan-kodok-casting-gabus-toman', 'Maguro', 45000.00, 60, 80, 
'Umpan kodok tiruan berbahan karet silikon elastis dengan pelindung kail ganda anti-nyangkut di eceng gondok atau tanaman air. Dilengkapi spinner blade di ekor yang menciptakan cipratan air berisik.', 
'Panjang: 4.5 cm | Bobot: 9 Gram | Action: Topwater Floating / Walk The Dog | Kail: Double Hook Tajam Baja Karbon Tinggi | Motif: Laser Natural Frog Green & Yellow Belly', 'p9_soft_frog.svg', 4.8, 320, 0),

(10, 1, 4, 'Metal Jig GID Glow In The Dark 60g Speed Jigging', 'metal-jig-gid-glow-in-the-dark-60g-speed-jigging', 'Pioneer', 55000.00, 45, 120, 
'Umpan besi timah metal jig dengan lapisan fosfor Glow In The Dark berkualitas tinggi yang menyala terang di laut dalam atau malam hari. Bentuk asimetris menciptakan aksi jatuh berkelebat seperti ikan terluka.', 
'Bobot: 60 Gram | Panjang: 9.5 cm | Fitur: Fosfor Hijau Zebra Glow Reaktif Cahaya UV | Target: Tenggiri, Tongkol, GT, Kerapu, Kampur | Termasuk: Solid Ring Baja', 'p10_metal_jig.svg', 4.7, 185, 0),

(11, 1, 5, 'Mustad UltraPoint Chinu Ringed Black Hook Box', 'mustad-ultrapoint-chinu-ringed-black-hook-box', 'Mustad', 65000.00, 50, 100, 
'Kail pancing legendaris dari Norwegia seri Chinu dengan mata berlubang (ringed) dan ujung UltraPoint runcing kimiawi yang menjamin penetrasi cepat ke bibir ikan tanpa merobek daging.', 
'Isi: 50 Pcs per Kotak | Ukuran: No. 5 & No. 6 Tersedia | Warna: Black Nickel Anti Karat | Material: High Carbon Steel Tempurung Ditekan | Fitur: Kirbed Bend, Needle Point', 'p11_mustad_hooks.svg', 4.9, 160, 0),

(12, 1, 5, 'Tas Joran Pancing Hardcase Waterproof Tabung 120cm', 'tas-joran-pancing-hardcase-waterproof-tabung-120cm', 'Relix', 175000.00, 14, 850, 
'Tas pelindung joran pancing model tabung pipa kaku berbalut kain cordura d600 anti air dengan busa pelindung tebal. Menjaga joran kesayangan Anda dari benturan saat perjalanan dan bagasi motor/mobil.', 
'Panjang: 120 cm | Diameter: 10 cm | Kapasitas: Muat 3 - 4 Joran Tanpa Reel | Fitur: Tali Selempang Busa Empuk, Resleting Ganda YKK, Saku Eksternal untuk Aksesoris', 'p12_tas_joran.svg', 4.9, 74, 1);

-- Data Ulasan / Testimoni Pembeli
INSERT INTO `product_reviews` (`product_id`, `nama_penilai`, `rating`, `komentar`) VALUES
(1, 'Hendra Wijaya', 5, 'Joran Shimano Cruzar mantap sekali! Dipakai narik patin 8 kg di kolam tetap melengkung indah dan tidak patah. Packing pipa sangat aman.'),
(1, 'Agus Pemancing Liar', 5, 'Barang original 100%. Kelenturan pas, gagang busa empuk tidak licin waktu tangan basah.'),
(3, 'Kapten Ronald', 5, 'Shimano Stradic 4000XG memang rajanya reel spinning. Suara drag merdu, putarannya halus sekali saat jigging di laut.'),
(4, 'Deni Saltwater', 5, 'Daiwa BG bodi metal kokoh, sudah dites fight tenggiri 12 kg tidak keder sama sekali.'),
(6, 'Rian Angler', 5, 'Senar Duraking Natuna X8 licin lemparannya jauh banget, warna multicolor memudahkan ukur kedalaman spot.');

-- Data Contoh Transaksi Pemesanan
INSERT INTO `orders` (`id`, `nomor_invoice`, `user_id`, `nama_lengkap`, `no_whatsapp`, `email`, `alamat_lengkap`, `kota`, `kode_pos`, `metode_pengiriman`, `ongkos_kirim`, `metode_pembayaran`, `catatan`, `total_produk`, `total_bayar`, `status`, `created_at`) VALUES
(1, 'INV-20261106-0001', 3, 'Budi Pemancing', '089876543210', 'budi@buyer.com', 'Jl. Danau Sunter Barat No. 8, RT 03/RW 10', 'Jakarta Utara', '14350', 'JNE Regular (Kemasan Tabung Paralon PVC Aman)', 35000.00, 'BCA Virtual Account', 'Tolong joran dipacking pipa paralon tebal ya min.', 770000.00, 805000.00, 'diproses', '2026-11-06 14:20:00'),
(2, 'INV-20261106-0002', 3, 'Ahmad Santoso', '081345678912', 'ahmad@strike.com', 'Komplek Pelabuhan Ratu Asri Blok C4', 'Sukabumi', '43364', 'J&T Express Cargo', 45000.00, 'QRIS Instant Pay', 'Siap pakai mancing akhir pekan.', 2850000.00, 2895000.00, 'selesai', '2026-11-06 15:45:00');

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `nama_produk`, `harga_satuan`, `qty`, `subtotal`) VALUES
(1, 1, 1, 'Shimano Cruzar 2602 Solid Carbon Rod 180cm', 385000.00, 2, 770000.00),
(2, 2, 3, 'Shimano Stradic FL 4000XG Saltwater Spinning Reel', 2850000.00, 1, 2850000.00);

<?php
/**
 * Header Template - StrikeZone Fishing Tackle
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$cartCount = get_cart_count();
$currentCategory = $_GET['cat'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Ambil Kategori untuk Menu Navigasi
$navCategories = [];
if ($conn) {
    $resNav = mysqli_query($conn, "SELECT id, nama_kategori, slug, icon FROM categories ORDER BY id ASC");
    if ($resNav) {
        while ($row = mysqli_fetch_assoc($resNav)) {
            $navCategories[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>StrikeZone Fishing Tackle | Marketplace Alat Pancing Modern</title>
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons 1.11.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body>

    <!-- Top Announcement Bar -->
    <div class="py-2 text-center text-light small fw-medium" style="background: linear-gradient(90deg, #0369a1, #0284c7, #0f766e);">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-none d-md-block">
                <i class="bi bi-shield-check me-1 text-warning"></i> 100% Produk Original & Bergaransi Resmi
            </div>
            <div class="mx-auto mx-md-0">
                <i class="bi bi-box-seam me-1 text-warning"></i> <strong>Gratis Tabung Paralon PVC Guard</strong> Pelindung Joran ke Seluruh Indonesia!
            </div>
            <div class="d-none d-lg-block">
                <a href="<?= BASE_URL ?>/admin/index.php" class="text-white text-decoration-none hover-underline">
                    <i class="bi bi-shop me-1"></i> Portal Penjual / Admin &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php">
                <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="StrikeZone Logo" class="navbar-brand-logo">
            </a>

            <!-- Mobile Toggler -->
            <div class="d-flex align-items-center gap-2 d-lg-none">
                <!-- Mobile Cart Icon -->
                <a href="<?= BASE_URL ?>/cart.php" class="btn btn-outline-light position-relative btn-sm px-3">
                    <i class="bi bi-cart3 fs-5"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-dismiss="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <!-- Navbar Menu Items -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- Search Bar -->
                <form class="d-flex mx-lg-4 my-3 my-lg-0 flex-grow-1" role="search" action="<?= BASE_URL ?>/index.php" method="GET">
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input class="form-control border-start-0 ps-0" type="search" name="search" placeholder="Cari joran, reel, senar PE, rapala lure, mata kail..." value="<?= htmlspecialchars($searchQuery) ?>" aria-label="Search">
                        <button class="btn btn-primary px-3 fw-semibold" type="submit">Cari</button>
                    </div>
                </form>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link <?= empty($currentCategory) && empty($searchQuery) ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                            <i class="bi bi-house-door me-1"></i> Semua Produk
                        </a>
                    </li>

                    <!-- Dropdown Kategori -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= !empty($currentCategory) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid me-1"></i> Kategori Alat
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-secondary">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php">Semua Kategori</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <?php foreach ($navCategories as $nc): ?>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 <?= $currentCategory == $nc['id'] ? 'active bg-primary' : '' ?>" href="<?= BASE_URL ?>/index.php?cat=<?= $nc['id'] ?>">
                                        <i class="bi <?= htmlspecialchars($nc['icon'] ?? 'bi-tag') ?> text-info"></i>
                                        <?= htmlspecialchars($nc['nama_kategori']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                    <!-- Keranjang Belanja Desktop -->
                    <li class="nav-item ms-lg-2 d-none d-lg-block">
                        <a href="<?= BASE_URL ?>/cart.php" class="btn btn-outline-info position-relative d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="fw-semibold">Keranjang</span>
                            <?php if ($cartCount > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?= $cartCount ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill">0</span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <?php if (is_logged_in()): ?>
                        <?php $navUser = current_user(); ?>
                        <!-- Menu Kelola Produk (Hanya Admin) -->
                        <?php if (is_admin()): ?>
                            <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                                <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill">
                                    <i class="bi bi-sliders"></i>
                                    <span>Kelola Produk (CRUD)</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Dropdown Akun & Logout -->
                        <li class="nav-item dropdown ms-lg-2 mt-2 mt-lg-0">
                            <a class="btn btn-outline-info dropdown-toggle d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                                <span><?= htmlspecialchars($navUser['nama']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-secondary">
                                <li>
                                    <h6 class="dropdown-header text-info">
                                        <i class="bi bi-shield-lock me-1"></i> Role: <?= strtoupper(htmlspecialchars($navUser['role'])) ?>
                                    </h6>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/dashboard.php">
                                        <i class="bi bi-speedometer2 me-2"></i> Dashboard Saya
                                    </a>
                                </li>
                                <?php if (is_admin()): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/index.php">
                                            <i class="bi bi-sliders me-2"></i> Panel Admin
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/pesanan.php">
                                            <i class="bi bi-truck me-2"></i> Kelola Pesanan
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Tombol Login -->
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                            <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span>Login</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <main class="py-4">
        <div class="container">
            <?php display_flash(); ?>

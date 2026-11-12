<?php
/**
 * Helper & Utility Functions
 * Marketplace Alat Pancing StrikeZone
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Format Angka menjadi Mata Uang Rupiah (Contoh: Rp 385.000)
 */
function format_rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Buat Nomor Invoice Unik
 */
function generate_invoice_number() {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
}

/**
 * Ambil Keranjang Belanja dari Session
 */
function get_cart() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

/**
 * Hitung Jumlah Total Item di Keranjang Belanja
 */
function get_cart_count() {
    $cart = get_cart();
    $count = 0;
    foreach ($cart as $item) {
        $count += (int)($item['qty'] ?? 0);
    }
    return $count;
}

/**
 * Hitung Total Nilai Belanja di Keranjang
 */
function get_cart_total() {
    $cart = get_cart();
    $total = 0;
    foreach ($cart as $item) {
        $total += ((float)($item['harga'] ?? 0) * (int)($item['qty'] ?? 0));
    }
    return $total;
}

/**
 * Set Pesan Flash (Alert)
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

/**
 * Tampilkan Pesan Flash jika ada
 */
function display_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = match($flash['type']) {
            'success' => 'bi-check-circle-fill',
            'danger' => 'bi-exclamation-octagon-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            default => 'bi-info-circle-fill'
        };
        echo '
        <div class="alert alert-' . htmlspecialchars($flash['type']) . ' alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm my-3" role="alert">
            <i class="bi ' . $icon . ' fs-5"></i>
            <div>' . htmlspecialchars($flash['message']) . '</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

/**
 * Helper untuk menentukan URL Gambar Produk
 */
function get_product_image_url($gambar) {
    $imgPath = __DIR__ . '/../assets/img/products/' . $gambar;
    if (!empty($gambar) && file_exists($imgPath)) {
        return BASE_URL . '/assets/img/products/' . htmlspecialchars($gambar);
    }
    return BASE_URL . '/assets/img/products/default_product.svg';
}

/**
 * Cek status autentikasi user
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'nama' => $_SESSION['user_nama'] ?? 'Pengguna',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user'
    ];
}

/**
 * Wajib login: arahkan ke halaman login jika belum masuk
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Wajib login sebagai admin: tolak user biasa
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'Halaman tersebut hanya dapat diakses oleh Administrator.');
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

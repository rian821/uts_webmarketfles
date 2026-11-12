<?php
/**
 * Halaman Hapus Produk (CRUD Delete)
 * StrikeZone Fishing Tackle Admin
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Hanya admin yang boleh mengakses panel ini
require_admin();

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId > 0) {
    // Ambil info nama & file gambar
    $stmt = mysqli_prepare($conn, "SELECT nama_produk, gambar FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $prod = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($prod) {
        $namaProduk = $prod['nama_produk'];
        $gambar = $prod['gambar'];

        // Hapus file custom jika bukan file svg preset sistem
        if (!empty($gambar) && strpos($gambar, 'prod_') === 0) {
            $filePath = __DIR__ . '/../assets/img/products/' . $gambar;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // Hapus dari database
        $stmtDel = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmtDel, "i", $productId);
        if (mysqli_stmt_execute($stmtDel)) {
            set_flash('success', 'Produk "' . htmlspecialchars($namaProduk) . '" berhasil dihapus dari katalog.');
        } else {
            set_flash('danger', 'Gagal menghapus produk: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($stmtDel);
    } else {
        set_flash('danger', 'Data produk tidak ditemukan.');
    }
}

header('Location: ' . BASE_URL . '/admin/produk.php');
exit;

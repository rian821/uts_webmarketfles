<?php
// Script fix database lokal - jalankan via CLI
$conn = @mysqli_connect('127.0.0.1', 'root', '', '', 3306);

if (!$conn) {
    die("GAGAL KONEKSI: " . mysqli_connect_error() . "\n");
}

echo "Koneksi MySQL OK\n";

// Buat database jika belum ada
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `db_pancing_marketplace` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Database siap\n";

// Pilih database
mysqli_select_db($conn, 'db_pancing_marketplace');

// Buat tabel users jika belum ada
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `no_hp` VARCHAR(20) NOT NULL DEFAULT '',
    `alamat` TEXT DEFAULT NULL,
    `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
    `foto` VARCHAR(255) DEFAULT 'default_user.png',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "Tabel users siap\n";

// Generate hash baru
$email    = 'admin@strikezone.com';
$password = 'admin123';
$hash     = password_hash($password, PASSWORD_BCRYPT);
$nama     = 'Admin StrikeZone Fishing';
$hp       = '081234567890';
$alamat   = 'Ruko Sentra Bahari No. 12, Jakarta Utara';

// Hapus admin lama & insert baru
mysqli_query($conn, "DELETE FROM users WHERE email = 'admin@strikezone.com'");
$stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, 'admin')");
mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $hash, $hp, $alamat);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Verifikasi
$res  = mysqli_query($conn, "SELECT id, nama, email, password, role FROM users WHERE email='admin@strikezone.com' LIMIT 1");
$user = mysqli_fetch_assoc($res);

if ($user && password_verify($password, $user['password'])) {
    echo "=================================\n";
    echo "BERHASIL! Akun admin siap:\n";
    echo "Email   : admin@strikezone.com\n";
    echo "Password: admin123\n";
    echo "Role    : " . $user['role'] . "\n";
    echo "=================================\n";
} else {
    echo "GAGAL verifikasi password!\n";
}

mysqli_close($conn);

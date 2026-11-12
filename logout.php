<?php
/**
 * Proses Logout Pengguna
 * StrikeZone Fishing Tackle Marketplace
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Hapus seluruh data session
$_SESSION = [];

// Hapus cookie session bila ada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// Mulai session baru khusus untuk pesan flash
session_start();
set_flash('success', 'Anda telah berhasil logout dari akun.');

header('Location: ' . BASE_URL . '/login.php');
exit;

<?php
/**
 * File: admin/logout.php
 * Deskripsi: Skrip untuk menghapus session login dan keluar dari sistem admin.
 */

// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menghapus semua variabel session
$_SESSION = array();

// Menghapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Menghancurkan session
session_destroy();

// Mengarahkan kembali ke halaman login guru
header("Location: login.php");
exit();
?>

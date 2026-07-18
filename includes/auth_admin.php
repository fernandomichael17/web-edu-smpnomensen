<?php
/**
 * File: includes/auth_admin.php
 * Deskripsi: Skrip verifikasi session login guru/admin.
 *            Wajib di-include di setiap halaman admin yang butuh proteksi.
 */

// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mengecek apakah session admin ada dan bernilai true
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Jika tidak sah, arahkan kembali ke form login dengan parameter error
    header("Location: login.php?error=session_expired");
    exit();
}
?>

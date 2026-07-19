<?php
/**
 * File: includes/auth_siswa.php
 * Deskripsi: Skrip verifikasi session login siswa.
 *            Wajib di-include di setiap halaman siswa yang butuh proteksi.
 */

// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mengecek apakah session siswa ada dan bernilai true
if (!isset($_SESSION['siswa_logged_in']) || $_SESSION['siswa_logged_in'] !== true) {
    // Jika tidak sah, arahkan kembali ke form login siswa dengan parameter error
    header("Location: login.php?error=session_expired");
    exit();
}
?>

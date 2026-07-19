<?php
/**
 * File: siswa/logout.php
 * Deskripsi: Skrip untuk menghapus session login siswa dan keluar ke login.php.
 */

// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menghapus session siswa
unset($_SESSION['siswa_logged_in']);
unset($_SESSION['siswa_id']);
unset($_SESSION['siswa_nis']);
unset($_SESSION['siswa_nama']);
unset($_SESSION['siswa_kelas']);

// Jika session benar-benar kosong, hancurkan session
if (empty($_SESSION)) {
    session_destroy();
}

// Mengarahkan kembali ke login siswa
header("Location: login.php");
exit();
?>

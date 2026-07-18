<?php
/**
 * File: config.php
 * Deskripsi: Menghubungkan aplikasi web ke database MySQL menggunakan PDO.
 * 
 * Alasan menggunakan PDO: 
 * 1. Keamanan: PDO mendukung Prepared Statements yang sangat efektif mencegah SQL Injection (serangan database).
 * 2. Error Handling: Menggunakan mekanisme try-catch yang rapi untuk menangkap error tanpa memunculkan detail sensitif ke layar.
 * 3. Fleksibilitas: Jika nantinya database pindah (misal ke PostgreSQL), PDO lebih mudah diadaptasi karena mendukung banyak driver.
 */

$host = 'localhost';
$dbname = 'db_smp_nomensen_english';
$username = 'root'; // Username default XAMPP
$password = ''; // Password default XAMPP biasanya kosong

try {
    // Membuat instance PDO (koneksi)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Mengatur mode pelaporan error ke Exception agar bisa ditangkap blok catch
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mengatur default pengembalian data sebagai array asosiatif
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Menangkap error jika koneksi gagal dan menghentikan eksekusi script
    die("Koneksi database gagal: " . $e->getMessage());
}
?>

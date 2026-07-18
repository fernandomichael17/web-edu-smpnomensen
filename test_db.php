<?php
/**
 * File: test_db.php
 * Deskripsi: Halaman sederhana untuk memastikan file config.php dapat terhubung ke database.
 */

// Memanggil file config.php
require_once 'config.php';

// Jika eksekusi berhasil melewati 'require_once' tanpa memunculkan pesan error die(),
// maka variabel $pdo dari config.php telah berhasil dibuat.
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Koneksi Database</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            text-align: center;
            padding-top: 50px;
        }
        .success-box {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 20px;
            margin: 0 auto;
            width: 50%;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <div class="success-box">
        <h2>✅ Koneksi Database Berhasil!</h2>
        <p>File <code>config.php</code> telah terhubung ke database <strong>db_smp_nomensen_english</strong> dengan baik menggunakan PDO.</p>
    </div>

</body>
</html>

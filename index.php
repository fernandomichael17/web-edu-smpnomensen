<?php
/**
 * File: index.php
 * Deskripsi: Halaman Splash/Intro aplikasi pembelajaran Bahasa Inggris.
 *            Disesuaikan persis dengan rancangan storyboard proposal.
 */

// Jalur ke file logo sekolah (bisa diganti dengan ekstensi lain seperti .jpg atau .svg jika diperlukan)
$logo_path = 'assets/img/logo.png';
$has_logo = file_exists($logo_path);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi Pembelajaran Bahasa Inggris Berbasis Multimedia pada SMP Swasta Nommensen Menggunakan Metode MDLC.">
    <title>Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen</title>
    
    <!-- Memanggil CSS utama -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Header Atas (Sesuai Storyboard) -->
    <header class="top-header">
        Aplikasi Pembelajaran Bahasa Inggris
    </header>

    <!-- Konten Utama Tengah (Sesuai Storyboard) -->
    <main class="main-container">
        <!-- Logo Bulat Placeholder atau Gambar Logo Sekolah (Sesuai Storyboard) -->
        <div class="logo-circle">
            <?php if ($has_logo): ?>
                <img src="<?= $logo_path ?>" alt="Logo SMP Swasta Nommensen" class="logo-img">
            <?php else: ?>
                <span class="logo-text">LOGO</span>
            <?php endif; ?>
        </div>

        <!-- Judul & Selamat Datang -->
        <h1 class="welcome-text">
            Selamat Datang di Aplikasi Pembelajaran<br>
            Bahasa Inggris Berbasis Multimedia
        </h1>
        
        <!-- Nama Sekolah -->
        <p class="school-text">SMP Swasta Nommensen</p>

        <!-- Tombol Aksi Tumpuk Vertikal (Sesuai Storyboard) -->
        <div class="action-buttons">
            <a href="siswa/index.php" class="btn btn-primary" id="btn-siswa">Mulai Belajar</a>
            <a href="admin/login.php" class="btn btn-secondary" id="btn-guru">Login Guru</a>
        </div>
    </main>

    <!-- Footer Bawah (Sesuai Storyboard) -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

</body>
</html>

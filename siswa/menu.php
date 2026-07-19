<?php
/**
 * File: siswa/menu.php
 * Deskripsi: Halaman Menu Utama Siswa.
 *            Berisi 5 kartu navigasi modul belajar (Vocabulary, Grammar, Conversation, Kuis, Riwayat Nilai).
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

$page_title = 'Menu Utama';
$active_page = 'menu';

// Memanggil template header
require_once '../includes/header.php';

// Memanggil template sidebar
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <!-- Header Bagian Atas Panel -->
    <header class="siswa-header">
        <h1>Menu Utama</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <!-- Area Isi Konten -->
    <div class="siswa-content">
        <div style="margin-bottom: 2rem; text-align: center;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: #1f2937; margin-bottom: 0.5rem;">
                Halo, <?= htmlspecialchars($_SESSION['siswa_nama']) ?>! Pilih Materi yang Ingin Dipelajari
            </h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Silakan klik tombol Buka pada salah satu kartu materi di bawah ini untuk memulai belajar.</p>
        </div>

        <!-- Container Kartu Menu Utama (Sesuai Storyboard) -->
        <div class="menu-container">
            <!-- 1. Vocabulary Card -->
            <a href="vocabulary.php" class="menu-card">
                <div class="menu-card-header">
                    Vocabulary (Kosakata)
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-text">
                        Pelajari kosakata Bahasa Inggris
                    </div>
                    <div class="menu-card-btn">Buka</div>
                </div>
            </a>

            <!-- 2. Grammar Card -->
            <a href="grammar.php" class="menu-card">
                <div class="menu-card-header">
                    Grammar (Tata Bahasa)
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-text">
                        Belajar tata bahasa dasar
                    </div>
                    <div class="menu-card-btn">Buka</div>
                </div>
            </a>

            <!-- 3. Conversation Card -->
            <a href="conversation.php" class="menu-card">
                <div class="menu-card-header">
                    Conversation (Percakapan)
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-text">
                        Contoh percakapan sehari-hari
                    </div>
                    <div class="menu-card-btn">Buka</div>
                </div>
            </a>

            <!-- 4. Kuis & Latihan Soal Card -->
            <a href="kuis.php" class="menu-card">
                <div class="menu-card-header">
                    Kuis & Latihan Soal
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-text">
                        Uji pemahaman kamu disini
                    </div>
                    <div class="menu-card-btn">Buka</div>
                </div>
            </a>

            <!-- 5. Riwayat Nilai Card -->
            <a href="riwayat.php" class="menu-card">
                <div class="menu-card-header">
                    Riwayat Nilai
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-text">
                        Lihat hasil belajarmu
                    </div>
                    <div class="menu-card-btn">Buka</div>
                </div>
            </a>
        </div>
    </div>

<?php
// Memanggil template footer
require_once '../includes/footer.php';
?>

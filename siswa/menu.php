<?php
/**
 * File: siswa/menu.php
 * Deskripsi: Halaman Menu Utama Siswa.
 *            Berisi 5 kartu navigasi modul belajar (Vocabulary, Grammar, Conversation, Kuis, Riwayat Nilai).
 */

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
        <div style="margin-bottom: 2rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: #1f2937; margin-bottom: 0.5rem;">Halo, Mari Belajar Bahasa Inggris!</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Silakan pilih salah satu modul pembelajaran interaktif di bawah ini untuk memulai belajar.</p>
        </div>

        <!-- Grid Kartu Menu Utama -->
        <div class="menu-grid">
            <!-- 1. Vocabulary Card -->
            <a href="vocabulary.php" class="menu-card">
                <div class="menu-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <h3>Vocabulary</h3>
                <p>Belajar kumpulan kosakata baru bahasa Inggris beserta artinya dan dengarkan pelafalan suaranya.</p>
            </a>

            <!-- 2. Grammar Card -->
            <a href="grammar.php" class="menu-card">
                <div class="menu-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
                <h3>Grammar</h3>
                <p>Pelajari tata bahasa Inggris, rumus tenses (positif/negatif/tanya), contoh kalimat, dan latihannya.</p>
            </a>

            <!-- 3. Conversation Card -->
            <a href="conversation.php" class="menu-card">
                <div class="menu-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <h3>Conversation</h3>
                <p>Tonton video percakapan sehari-hari dan baca dialog interaktif antar tokoh bahasa Inggris.</p>
            </a>

            <!-- 4. Kuis & Latihan Soal Card -->
            <a href="kuis.php" class="menu-card">
                <div class="menu-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 11 12 14 22 4"></polyline>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </div>
                <h3>Kuis & Latihan Soal</h3>
                <p>Uji kemampuan belajarmu dengan menjawab latihan soal pilihan ganda interaktif beserta timer.</p>
            </a>

            <!-- 5. Riwayat Nilai Card -->
            <a href="riwayat.php" class="menu-card">
                <div class="menu-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                </div>
                <h3>Riwayat Nilai</h3>
                <p>Pantau hasil nilai kuis yang telah kamu kerjakan beserta ringkasan statistik perolehan skormu.</p>
            </a>
        </div>
    </div>

<?php
// Memanggil template footer
require_once '../includes/footer.php';
?>

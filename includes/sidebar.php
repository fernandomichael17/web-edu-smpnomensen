<?php
/**
 * File: includes/sidebar.php
 * Deskripsi: Panel navigasi samping untuk siswa.
 *            Menerima variabel $active_page dari halaman induk untuk menandai link aktif.
 */
$active_page = isset($active_page) ? $active_page : '';
?>
<!-- Sidebar Navigasi Siswa -->
<aside class="siswa-sidebar">
    <div class="siswa-sidebar-header">
        <h2>Nommensen English</h2>
    </div>
    
    <ul class="siswa-sidebar-menu">
        <li>
            <a href="menu.php" class="<?= $active_page === 'menu' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Menu Utama
            </a>
        </li>
        <li>
            <a href="vocabulary.php" class="<?= $active_page === 'vocabulary' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                Vocabulary
            </a>
        </li>
        <li>
            <a href="grammar.php" class="<?= $active_page === 'grammar' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                Grammar
            </a>
        </li>
        <li>
            <a href="conversation.php" class="<?= $active_page === 'conversation' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                Conversation
            </a>
        </li>
        <li>
            <a href="kuis.php" class="<?= $active_page === 'kuis' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                Kuis & Latihan Soal
            </a>
        </li>
        <li>
            <a href="riwayat.php" class="<?= $active_page === 'riwayat' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                Riwayat Nilai
            </a>
        </li>
    </ul>
    
    <div class="siswa-sidebar-footer" style="display: flex; flex-direction: column; gap: 0.75rem; align-items: flex-start; padding-left: 2rem;">
        <a href="../index.php" class="btn-sidebar-back">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Beranda
        </a>
        <a href="logout.php" class="btn-sidebar-back" style="color: #f87171;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Keluar (Logout)
        </a>
    </div>
</aside>

<?php
/**
 * File: siswa/conversation.php
 * Deskripsi: Halaman Pembelajaran Conversation (Placeholder).
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

$page_title = 'Conversation';
$active_page = 'conversation';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header">
        <h1>Conversation</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content">
        <div style="background: #ffffff; padding: 2rem; border-radius: 12px; border: 1px solid #cbd5e1;">
            <h2 style="font-family: 'Outfit', sans-serif; color: var(--accent-blue);">Modul Conversation</h2>
            <p style="color: #6b7280; margin-top: 0.5rem; line-height: 1.6;">Halaman pembelajaran Percakapan (Conversation) sedang dipersiapkan. Modul ini akan menyertakan pemutar video percakapan serta dialog teks interaktif antar tokoh.</p>
        </div>
    </div>

<?php
require_once '../includes/footer.php';
?>

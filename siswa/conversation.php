<?php
/**
 * File: siswa/conversation.php
 * Deskripsi: Halaman Pembelajaran Conversation (Percakapan) untuk Siswa.
 *            Menampilkan pemutar video dialog percakapan bahasa Inggris dan skrip teks dialognya.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

$page_title = 'Conversation';
$active_page = 'conversation';

// Fetch unit materi bertipe 'Conversation'
try {
    $stmt_materi = $pdo->prepare("SELECT * FROM tb_materi WHERE kategori = 'Conversation' ORDER BY id_materi ASC");
    $stmt_materi->execute();
    $units = $stmt_materi->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data materi: " . $e->getMessage());
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header">
        <h1>Conversation (Percakapan)</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content">
        <div style="margin-bottom: 2rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: #1f2937; margin-bottom: 0.5rem;">Percakapan Bahasa Inggris</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Tonton video percakapan di bawah ini, dengarkan cara mereka mengucapkan kalimat, lalu baca naskah teks dialog di bawahnya untuk mempelajari kosa kata percakapan.</p>
        </div>

        <?php if (empty($units)): ?>
            <div style="background: #ffffff; padding: 2rem; border-radius: 8px; border: 1px solid #cbd5e1; text-align: center; color: #6b7280;">
                Belum ada materi Conversation yang tersedia saat ini.
            </div>
        <?php else: ?>
            <?php foreach ($units as $unit): ?>
                <?php
                // Fetch video terkait unit percakapan ini
                try {
                    $stmt_video = $pdo->prepare("SELECT * FROM tb_video WHERE id_materi = :id LIMIT 1");
                    $stmt_video->execute(['id' => $unit['id_materi']]);
                    $video = $stmt_video->fetch();
                } catch (PDOException $e) {
                    $video = null;
                }
                ?>
                
                <!-- Menampilkan per Unit Conversation -->
                <div style="margin-bottom: 3.5rem; background: #ffffff; border: 2px solid #374151; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03);">
                    <!-- Header Unit -->
                    <div style="background-color: var(--accent-blue); color: #ffffff; padding: 1rem 1.5rem; font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.25rem; border-bottom: 2px solid #374151;">
                        <?= htmlspecialchars($unit['judul_materi']) ?>
                    </div>
                    
                    <div style="padding: 1.75rem;">
                        <!-- 1. Video Player Area -->
                        <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 2rem; background-color: #f1f5f9; padding: 1.5rem; border-radius: 6px; border: 1px solid var(--border-color);">
                            <?php if ($video): ?>
                                <video src="../assets/video/<?= htmlspecialchars($video['file_video']) ?>" controls style="width: 100%; max-width: 600px; border-radius: 6px; border: 2px solid #374151; background-color: #000000; box-shadow: 0 4px 10px rgba(0,0,0,0.15);"></video>
                                <div style="margin-top: 0.75rem; font-weight: 700; color: #475569; font-size: 0.9rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-right: 4px; color: var(--accent-blue);">
                                        <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                    </svg>
                                    <?= htmlspecialchars($video['keterangan'] ?? 'Video Percakapan') ?>
                                </div>
                            <?php else: ?>
                                <div style="width: 100%; max-width: 600px; height: 340px; background-color: #1e293b; color: #94a3b8; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 6px; border: 2px solid #374151;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; color: #64748b;">
                                        <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                    </svg>
                                    <span style="font-weight: 700; font-size: 1rem;">Video Belum Tersedia</span>
                                    <span style="font-size: 0.85rem; margin-top: 0.25rem;">Video media pembelajaran belum diunggah oleh guru.</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 2. Skrip Percakapan Dialog (Teks Tokoh A & B) -->
                        <div>
                            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--accent-blue); margin-bottom: 1rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                Dialogue Script
                            </h3>
                            
                            <div style="background-color: #fafaf9; border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);">
                                <?php
                                $lines = explode("\n", $unit['konten_teks']);
                                $has_lines = false;
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if ($line === '') continue;
                                    $has_lines = true;
                                    
                                    // Mencari posisi tanda titik dua untuk membedakan nama pembicara dan kalimatnya
                                    $colon_pos = strpos($line, ':');
                                    if ($colon_pos !== false) {
                                        $speaker = trim(substr($line, 0, $colon_pos));
                                        $dialog = trim(substr($line, $colon_pos + 1));
                                        
                                        // Tentukan warna/gaya balon teks/label berdasarkan pembicara A atau B
                                        $label_color = ($speaker === 'A') ? 'var(--accent-blue)' : '#0891b2'; // Biru vs Cyan
                                        ?>
                                        <div style="margin-bottom: 1rem; display: flex; gap: 0.75rem; font-size: 0.95rem; line-height: 1.6; align-items: flex-start;">
                                            <div style="font-weight: 800; color: <?= $label_color ?>; min-width: 35px; text-align: right; font-family: 'Outfit', sans-serif;"><?= htmlspecialchars($speaker) ?>:</div>
                                            <div style="color: #1f2937; background: #ffffff; padding: 0.35rem 0.85rem; border-radius: 4px; border: 1px solid var(--border-color); flex-grow: 1;"><?= htmlspecialchars($dialog) ?></div>
                                        </div>
                                        <?php
                                    } else {
                                        // Baris penjelasan naratif (tanpa nama tokoh)
                                        ?>
                                        <div style="margin-bottom: 1rem; font-size: 0.95rem; color: #475569; line-height: 1.6; font-style: italic; text-align: center; padding: 0.25rem 0;">
                                            <?= htmlspecialchars($line) ?>
                                        </div>
                                        <?php
                                    }
                                }
                                
                                if (!$has_lines) {
                                    echo '<div style="text-align: center; color: #6b7280; font-style: italic;">Naskah dialog percakapan kosong.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php
require_once '../includes/footer.php';
?>

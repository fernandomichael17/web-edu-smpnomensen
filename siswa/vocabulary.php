<?php
/**
 * File: siswa/vocabulary.php
 * Deskripsi: Halaman Pembelajaran Vocabulary (Kosakata) untuk Siswa.
 *            Menampilkan daftar kata bahasa Inggris, arti bahasa Indonesia, dan audio pelafalan.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

$page_title = 'Vocabulary';
$active_page = 'vocabulary';

// Fetch unit materi bertipe 'Vocabulary' beserta item audionya
try {
    // Ambil semua materi kategori Vocabulary
    $stmt_materi = $pdo->prepare("SELECT * FROM tb_materi WHERE kategori = 'Vocabulary' ORDER BY id_materi ASC");
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
        <h1>Vocabulary (Kosakata)</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content">
        <div style="margin-bottom: 2rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: #1f2937; margin-bottom: 0.5rem;">Daftar Kosakata</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Pelajari kosakata bahasa Inggris di bawah ini. Klik tombol **Putar Suara** untuk mendengar cara pelafalannya.</p>
        </div>

        <?php if (empty($units)): ?>
            <div style="background: #ffffff; padding: 2rem; border-radius: 8px; border: 1px solid #cbd5e1; text-align: center; color: #6b7280;">
                Belum ada materi Vocabulary yang tersedia saat ini.
            </div>
        <?php else: ?>
            <?php foreach ($units as $unit): ?>
                <!-- Menampilkan per Unit -->
                <div style="margin-bottom: 3rem;">
                    <div style="background-color: var(--accent-blue); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 8px 8px 0 0; font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.1rem;">
                        <?= htmlspecialchars($unit['judul_materi']) ?>
                    </div>
                    
                    <?php
                    // Ambil daftar kosakata (tb_audio) milik unit ini
                    try {
                        $stmt_audio = $pdo->prepare("SELECT * FROM tb_audio WHERE id_materi = :id_materi ORDER BY id_audio ASC");
                        $stmt_audio->execute(['id_materi' => $unit['id_materi']]);
                        $words = $stmt_audio->fetchAll();
                    } catch (PDOException $e) {
                        $words = [];
                    }
                    ?>
                    
                    <div class="table-container" style="margin-top: 0; border-radius: 0 0 8px 8px; border-top: none;">
                        <table class="table-materi">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Bahasa Inggris</th>
                                    <th style="width: 40%;">Arti (Bahasa Indonesia)</th>
                                    <th style="text-align: center; width: 20%;">Pelafalan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($words)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #6b7280; padding: 2rem;">
                                            Belum ada kosakata di dalam unit ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($words as $word): ?>
                                        <?php
                                        // Memisahkan keterangan "Inggris|Indonesia"
                                        $parts = explode('|', $word['keterangan']);
                                        $english = isset($parts[0]) ? trim($parts[0]) : '';
                                        $indonesian = isset($parts[1]) ? trim($parts[1]) : '';
                                        ?>
                                        <tr>
                                            <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($english) ?></td>
                                            <td><?= htmlspecialchars($indonesian) ?></td>
                                            <td style="text-align: center;">
                                                <button onclick="playAudio('<?= htmlspecialchars($word['file_audio']) ?>')" class="btn-sm btn-play">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 2px;">
                                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                                    </svg>
                                                    Putar Suara
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pemutar Audio Global (Tersembunyi) -->
    <audio id="vocab-audio-player" style="display: none;"></audio>

    <!-- Skrip Pemutaran Audio -->
    <script>
    function playAudio(filename) {
        const player = document.getElementById('vocab-audio-player');
        // File audio tersimpan di folder assets/audio/ di root, 
        // sehingga dari folder siswa/ kita gunakan ../assets/audio/
        player.src = '../assets/audio/' + filename;
        player.play().catch(function(error) {
            alert('File audio "' + filename + '" tidak ditemukan di folder assets/audio/ atau tidak didukung.');
        });
    }
    </script>

<?php
require_once '../includes/footer.php';
?>

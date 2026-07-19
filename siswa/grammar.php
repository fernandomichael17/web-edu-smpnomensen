<?php
/**
 * File: siswa/grammar.php
 * Deskripsi: Halaman Pembelajaran Grammar untuk Siswa.
 *            Menampilkan materi grammar, rumus, contoh kalimat, dan kuis latihan interaktif.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

$page_title = 'Grammar';
$active_page = 'grammar';

// Fetch unit materi bertipe 'Grammar'
try {
    $stmt_materi = $pdo->prepare("SELECT * FROM tb_materi WHERE kategori = 'Grammar' ORDER BY id_materi ASC");
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
        <h1>Grammar (Tata Bahasa)</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content">
        <div style="margin-bottom: 2rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: #1f2937; margin-bottom: 0.5rem;">Pembelajaran Tata Bahasa</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Pelajari rumus, pola kalimat, dan contoh penggunaan tata bahasa Inggris di bawah ini. Selesaikan latihan soal di akhir materi untuk menguji pemahamanmu.</p>
        </div>

        <?php if (empty($units)): ?>
            <div style="background: #ffffff; padding: 2rem; border-radius: 8px; border: 1px solid #cbd5e1; text-align: center; color: #6b7280;">
                Belum ada materi Grammar yang tersedia saat ini.
            </div>
        <?php else: ?>
            <?php foreach ($units as $index => $unit): ?>
                <?php
                // Decode konten JSON dari database
                $decoded = json_decode($unit['konten_teks'], true);
                $is_json = is_array($decoded);
                
                $definisi = $is_json ? ($decoded['definisi'] ?? '') : $unit['konten_teks'];
                $rumus_positif = $is_json ? ($decoded['rumus']['positif'] ?? '') : '';
                $rumus_negatif = $is_json ? ($decoded['rumus']['negatif'] ?? '') : '';
                $rumus_tanya = $is_json ? ($decoded['rumus']['tanya'] ?? '') : '';
                $contoh_list = $is_json ? ($decoded['contoh'] ?? []) : [];
                $latihan_list = $is_json ? ($decoded['latihan'] ?? []) : [];
                ?>
                
                <!-- Menampilkan per Unit Grammar -->
                <div style="margin-bottom: 3.5rem; background: #ffffff; border: 2px solid #374151; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03);">
                    <!-- Header Unit -->
                    <div style="background-color: var(--accent-blue); color: #ffffff; padding: 1rem 1.5rem; font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.25rem; border-bottom: 2px solid #374151;">
                        <?= htmlspecialchars($unit['judul_materi']) ?>
                    </div>
                    
                    <div style="padding: 1.75rem;">
                        <!-- 1. Definisi Materi -->
                        <div style="margin-bottom: 1.75rem;">
                            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.05rem; font-weight: 700; color: var(--accent-blue); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Definisi & Penggunaan</h3>
                            <p style="color: #374151; font-size: 0.95rem; line-height: 1.6;"><?= nl2br(htmlspecialchars($definisi)) ?></p>
                        </div>
                        
                        <!-- 2. Rumus Kalimat -->
                        <?php if ($rumus_positif || $rumus_negatif || $rumus_tanya): ?>
                            <div style="margin-bottom: 1.75rem;">
                                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.05rem; font-weight: 700; color: var(--accent-blue); margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Rumus Kalimat</h3>
                                <div class="table-container" style="margin-top: 0; border: 2px solid #374151; border-radius: 6px;">
                                    <table class="table-materi" style="border-collapse: collapse;">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%; font-weight: 700; background-color: #f1f5f9;">Jenis Kalimat</th>
                                                <th style="width: 75%; font-weight: 700; background-color: #f1f5f9;">Pola Rumus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-weight: 700; color: #16a34a; background-color: #fcfdfa; border-bottom: 1px solid var(--border-color);"><span style="display: inline-block; padding: 0.25rem 0.5rem; background-color: #d1fae5; border-radius: 4px;">Positif (+)</span></td>
                                                <td style="font-family: monospace; font-size: 1rem; color: #1e293b; background-color: #fcfdfa; border-bottom: 1px solid var(--border-color); font-weight: 600;"><?= htmlspecialchars($rumus_positif) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: 700; color: #dc2626; background-color: #fdfafc; border-bottom: 1px solid var(--border-color);"><span style="display: inline-block; padding: 0.25rem 0.5rem; background-color: #fee2e2; border-radius: 4px;">Negatif (-)</span></td>
                                                <td style="font-family: monospace; font-size: 1rem; color: #1e293b; background-color: #fdfafc; border-bottom: 1px solid var(--border-color); font-weight: 600;"><?= htmlspecialchars($rumus_negatif) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: 700; color: #2563eb; background-color: #fafcfd;"><span style="display: inline-block; padding: 0.25rem 0.5rem; background-color: #dbeafe; border-radius: 4px;">Tanya (?)</span></td>
                                                <td style="font-family: monospace; font-size: 1rem; color: #1e293b; background-color: #fafcfd; font-weight: 600;"><?= htmlspecialchars($rumus_tanya) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 3. Contoh Kalimat -->
                        <?php if (!empty($contoh_list)): ?>
                            <div style="margin-bottom: 2rem;">
                                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.05rem; font-weight: 700; color: var(--accent-blue); margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Contoh Kalimat</h3>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <?php foreach ($contoh_list as $contoh): ?>
                                        <?php
                                        $parts = explode('|', $contoh);
                                        $english = isset($parts[0]) ? trim($parts[0]) : '';
                                        $indonesian = isset($parts[1]) ? trim($parts[1]) : '';
                                        ?>
                                        <div style="background-color: #f8fafc; border-left: 4px solid var(--accent-blue); padding: 0.75rem 1.25rem; border-radius: 0 6px 6px 0;">
                                            <div style="font-weight: 700; color: var(--accent-blue); font-size: 1rem;"><?= htmlspecialchars($english) ?></div>
                                            <div style="font-style: italic; color: #475569; font-size: 0.9rem; margin-top: 0.25rem;">"<?= htmlspecialchars($indonesian) ?>"</div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 4. Latihan Soal Singkat -->
                        <?php if (!empty($latihan_list)): ?>
                            <div style="border-top: 1.5px dashed var(--border-color); padding-top: 1.75rem; margin-top: 2rem;">
                                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #d97706;">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                    Latihan Soal Singkat (Practice Exercise)
                                </h3>
                                <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 1.25rem;">Klik pada salah satu jawaban yang menurutmu benar untuk langsung melihat hasilnya.</p>
                                
                                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                                    <?php foreach ($latihan_list as $q_idx => $soal): ?>
                                        <div id="exercise-<?= $unit['id_materi'] ?>-<?= $q_idx ?>" style="background-color: #fafaf9; border: 1px solid var(--border-color); padding: 1.25rem; border-radius: 8px;">
                                            <!-- Pertanyaan -->
                                            <div style="font-weight: 700; font-size: 0.95rem; color: #1f2937; margin-bottom: 0.75rem; display: flex; gap: 0.5rem;">
                                                <span><?= $q_idx + 1 ?>.</span>
                                                <span><?= htmlspecialchars($soal['pertanyaan']) ?></span>
                                            </div>
                                            <!-- Pilihan -->
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem; margin-bottom: 0.75rem;">
                                                <?php foreach ($soal['pilihan'] as $opt_idx => $opt): ?>
                                                    <?php if (trim($opt) !== ''): ?>
                                                        <button 
                                                            onclick="checkAnswer(<?= $unit['id_materi'] ?>, <?= $q_idx ?>, '<?= htmlspecialchars(trim($opt)) ?>', '<?= htmlspecialchars(trim($soal['jawaban'])) ?>', this)" 
                                                            style="text-align: left; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.6rem 1rem; font-size: 0.9rem; font-family: inherit; font-weight: 500; cursor: pointer; color: #475569; transition: all 0.2s;"
                                                            class="opt-btn-<?= $unit['id_materi'] ?>-<?= $q_idx ?>"
                                                        >
                                                            <span style="font-weight: 700; color: var(--accent-blue); margin-right: 4px;"><?= chr(65 + $opt_idx) ?>.</span> 
                                                            <?= htmlspecialchars($opt) ?>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <!-- Hasil feedback -->
                                            <div id="feedback-<?= $unit['id_materi'] ?>-<?= $q_idx ?>" style="font-weight: 700; font-size: 0.85rem; display: none; align-items: center; gap: 0.25rem;"></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Skrip Latihan Grammar Interaktif -->
    <script>
    function checkAnswer(unitId, qIdx, selectedOpt, correctAns, clickedButton) {
        // Ambil semua tombol pilihan untuk pertanyaan ini
        const buttons = document.querySelectorAll('.opt-btn-' + unitId + '-' + qIdx);
        
        // Nonaktifkan semua tombol setelah diklik agar tidak bisa klik ulang
        buttons.forEach(function(btn) {
            btn.disabled = true;
            btn.style.cursor = 'default';
            
            // Cari tombol yang isinya jawaban benar untuk diwarnai hijau sebagai kunci jawaban
            const btnTextClean = btn.innerText.substring(3).trim(); // Hapus awalan huruf pilihan, misal "A. "
            if (btnTextClean === correctAns) {
                btn.style.borderColor = '#16a34a';
                btn.style.backgroundColor = '#d1fae5';
                btn.style.color = '#15803d';
            }
        });

        // Ambil penampung feedback
        const feedbackDiv = document.getElementById('feedback-' + unitId + '-' + qIdx);
        feedbackDiv.style.display = 'flex';

        const cleanSelected = clickedButton.innerText.substring(3).trim();
        
        // Jika jawaban benar
        if (cleanSelected === correctAns) {
            clickedButton.style.borderColor = '#16a34a';
            clickedButton.style.backgroundColor = '#d1fae5';
            clickedButton.style.color = '#15803d';
            feedbackDiv.style.color = '#15803d';
            feedbackDiv.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 2px;">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Jawaban Benar!
            `;
        } else {
            // Jika jawaban salah
            clickedButton.style.borderColor = '#ef4444';
            clickedButton.style.backgroundColor = '#fee2e2';
            clickedButton.style.color = '#b91c1c';
            feedbackDiv.style.color = '#b91c1c';
            feedbackDiv.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 2px;">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Jawaban Salah! (Kunci: ${correctAns})
            `;
        }
    }
    </script>

<?php
require_once '../includes/footer.php';
?>

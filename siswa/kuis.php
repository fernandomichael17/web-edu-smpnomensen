<?php
/**
 * File: siswa/kuis.php
 * Deskripsi: Halaman Daftar Kuis yang Tersedia untuk Siswa.
 *            Menampilkan daftar kuis, kategori, durasi waktu, dan jumlah soal.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

$page_title = 'Kuis & Latihan Soal';
$active_page = 'kuis';

// Fetch daftar kuis
try {
    $stmt = $pdo->query("SELECT * FROM tb_kuis ORDER BY id_kuis ASC");
    $quizzes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data kuis: " . $e->getMessage());
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header">
        <h1>Kuis & Latihan Soal</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content">
        <div style="margin-bottom: 2rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: #1f2937; margin-bottom: 0.5rem;">Daftar Kuis yang Tersedia</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Uji kemampuan bahasa Inggris Anda dengan menyelesaikan kuis yang tersedia di bawah ini. Pastikan untuk memperhatikan durasi waktu pengerjaan.</p>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'completed'): ?>
            <div style="background-color: #d1fae5; color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; border: 1px solid #10b981; margin-bottom: 2rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Selamat! Anda telah menyelesaikan kuis dengan sukses.
            </div>
        <?php endif; ?>

        <?php if (empty($quizzes)): ?>
            <div style="background: #ffffff; padding: 2.5rem; border-radius: 8px; border: 1px solid #cbd5e1; text-align: center; color: #6b7280;">
                Belum ada kuis yang tersedia saat ini.
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <?php foreach ($quizzes as $quiz): ?>
                    <?php
                    // HITUNG jumlah soal asli dari tb_soal (Mengabaikan kolom tb_kuis.jumlah_soal sesuai instruksi)
                    try {
                        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM tb_soal WHERE id_kuis = :id");
                        $stmt_count->execute(['id' => $quiz['id_kuis']]);
                        $jumlah_soal_asli = $stmt_count->fetchColumn();
                    } catch (PDOException $e) {
                        $jumlah_soal_asli = 0;
                    }
                    ?>
                    <div style="background: #ffffff; border: 2px solid #374151; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03); display: flex; flex-direction: column; justify-content: space-between;">
                        <!-- Header Kartu Kuis -->
                        <div style="background-color: #f1f5f9; border-bottom: 2px solid #374151; padding: 1rem 1.25rem;">
                            <span style="font-size: 0.75rem; background-color: var(--accent-blue); color: #ffffff; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 700; font-family: 'Outfit', sans-serif;">
                                <?= htmlspecialchars($quiz['kategori_materi']) ?>
                            </span>
                            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 800; color: #1e293b; margin-top: 0.6rem; line-height: 1.4;">
                                <?= htmlspecialchars($quiz['judul_kuis']) ?>
                            </h3>
                        </div>

                        <!-- Konten Detil -->
                        <div style="padding: 1.25rem; flex-grow: 1; display: flex; flex-direction: column; gap: 0.5rem; color: #475569; font-size: 0.9rem; font-weight: 500;">
                            <div style="display: flex; justify-content: space-between;">
                                <span>Durasi Waktu:</span>
                                <strong style="color: #1e293b;"><?= htmlspecialchars($quiz['waktu_pengerjaan']) ?> Menit</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 0.5rem;">
                                <span>Jumlah Soal:</span>
                                <strong style="color: #1e293b;"><?= $jumlah_soal_asli ?> Soal PG</strong>
                            </div>
                        </div>

                        <!-- Tombol Mulai -->
                        <div style="padding: 1.25rem; border-top: 1px solid #f1f5f9; background-color: #fcfcfc;">
                            <?php if ($jumlah_soal_asli > 0): ?>
                                <a href="kuis_kerjakan.php?id_kuis=<?= $quiz['id_kuis'] ?>" class="btn-sm btn-play" style="width: 100%; text-decoration: none; padding: 0.7rem; font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700;">
                                    Mulai Kerjakan Kuis
                                </a>
                            <?php else: ?>
                                <button class="btn-sm" style="width: 100%; background-color: #cbd5e1; color: #94a3b8; border: none; padding: 0.7rem; font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; cursor: not-allowed;" disabled>
                                    Soal Belum Tersedia
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php
require_once '../includes/footer.php';
?>

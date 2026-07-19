<?php
/**
 * File: siswa/riwayat.php
 * Deskripsi: Halaman Riwayat Nilai Kuis Siswa.
 *            Menampilkan kartu ringkasan (total kuis, rata-rata, nilai terbaik)
 *            dan tabel riwayat lengkap hasil pengerjaan kuis siswa secara real-time dari database.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

$id_siswa = $_SESSION['siswa_id'];

// 1. Ambil data statistik ringkasan
try {
    $stmt_stats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_kuis, 
            AVG(skor) as rata_rata, 
            MAX(skor) as nilai_terbaik 
        FROM tb_hasil 
        WHERE id_siswa = :id_siswa
    ");
    $stmt_stats->execute(['id_siswa' => $id_siswa]);
    $stats = $stmt_stats->fetch();
    
    $total_kuis = $stats['total_kuis'] ?? 0;
    $rata_rata = $stats['rata_rata'] !== null ? round($stats['rata_rata'], 1) : 0;
    $nilai_terbaik = $stats['nilai_terbaik'] ?? 0;
} catch (PDOException $e) {
    die("Gagal mengambil data statistik: " . $e->getMessage());
}

// 2. Ambil daftar riwayat lengkap hasil kuis
try {
    $stmt_history = $pdo->prepare("
        SELECT h.*, k.judul_kuis, k.kategori_materi 
        FROM tb_hasil h
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        WHERE h.id_siswa = :id_siswa
        ORDER BY h.waktu_selesai DESC
    ");
    $stmt_history->execute(['id_siswa' => $id_siswa]);
    $history = $stmt_history->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil riwayat kuis: " . $e->getMessage());
}

$page_title = 'Riwayat Nilai';
$active_page = 'riwayat';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header">
        <h1>Riwayat Nilai Kuis</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content">
        
        <!-- Grid Ringkasan Statistik Nilai -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
            <!-- Card 1: Total Kuis -->
            <div style="background: #ffffff; border: 2px solid #374151; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <div style="background-color: #eff6ff; color: var(--accent-blue); padding: 0.75rem; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #6b7280; text-transform: uppercase;">Total Kuis</div>
                    <div style="font-size: 1.75rem; font-weight: 800; color: #1e293b; font-family: 'Outfit', sans-serif;"><?= $total_kuis ?> Kali</div>
                </div>
            </div>

            <!-- Card 2: Rata-Rata Nilai -->
            <div style="background: #ffffff; border: 2px solid #374151; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <div style="background-color: #ecfdf5; color: #10b981; padding: 0.75rem; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #6b7280; text-transform: uppercase;">Rata-Rata Nilai</div>
                    <div style="font-size: 1.75rem; font-weight: 800; color: #1e293b; font-family: 'Outfit', sans-serif;"><?= $rata_rata ?></div>
                </div>
            </div>

            <!-- Card 3: Nilai Terbaik -->
            <div style="background: #ffffff; border: 2px solid #374151; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <div style="background-color: #fffbeb; color: #f59e0b; padding: 0.75rem; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #6b7280; text-transform: uppercase;">Nilai Terbaik</div>
                    <div style="font-size: 1.75rem; font-weight: 800; color: #1e293b; font-family: 'Outfit', sans-serif;"><?= $nilai_terbaik ?></div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Lengkap -->
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: #1f2937; margin-bottom: 1rem;">Tabel Riwayat Pengerjaan</h2>
        <div class="table-container" style="border: 2px solid #374151; border-radius: 8px;">
            <table class="table-materi">
                <thead>
                    <tr>
                        <th style="width: 30%; font-weight: 700;">Nama Kuis</th>
                        <th style="width: 15%; font-weight: 700;">Kategori</th>
                        <th style="width: 15%; font-weight: 700; text-align: center;">Skor</th>
                        <th style="width: 20%; font-weight: 700; text-align: center;">Status Kelulusan</th>
                        <th style="width: 20%; font-weight: 700; text-align: center;">Tanggal Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #6b7280; padding: 3rem 2rem;">
                                Anda belum pernah mengerjakan kuis apa pun. Silakan selesaikan salah satu kuis terlebih dahulu!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $row): ?>
                            <?php
                            // Syarat kelulusan KKM diasumsikan skor >= 70
                            $is_passed = $row['skor'] >= 70;
                            $status_label = $is_passed ? 'LULUS' : 'BELUM LULUS';
                            $status_bg = $is_passed ? '#d1fae5' : '#fee2e2';
                            $status_color = $is_passed ? '#15803d' : '#b91c1c';
                            
                            $formatted_date = date('d M Y - H:i', strtotime($row['waktu_selesai']));
                            ?>
                            <tr>
                                <td style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($row['judul_kuis']) ?></td>
                                <td>
                                    <span style="font-size: 0.75rem; background-color: #f1f5f9; color: #475569; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700;">
                                        <?= htmlspecialchars($row['kategori_materi']) ?>
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 800; font-size: 1.1rem; color: <?= $is_passed ? '#16a34a' : '#d97706' ?>;">
                                    <?= $row['skor'] ?>
                                </td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: 700; background-color: <?= $status_bg ?>; color: <?= $status_color ?>;">
                                        <?= $status_label ?>
                                    </span>
                                </td>
                                <td style="text-align: center; color: #475569; font-size: 0.85rem;"><?= $formatted_date ?> WIB</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

<?php
require_once '../includes/footer.php';
?>

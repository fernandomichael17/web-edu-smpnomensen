<?php
/**
 * File: siswa/hasil_kuis.php
 * Deskripsi: Halaman Hasil Evaluasi Kuis Siswa.
 *            Menampilkan skor akhir, detail jawaban, sisa waktu pengerjaan,
 *            pesan motivasi dinamis, dan tombol navigasi aksi.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

// Cek jika session hasil kuis tidak ada, kembalikan ke daftar kuis
if (!isset($_SESSION['last_kuis_result'])) {
    header("Location: kuis.php");
    exit();
}

$result = $_SESSION['last_kuis_result'];
$id_kuis = $result['id_kuis'];
$skor = $result['skor'];
$jumlah_benar = $result['jumlah_benar'];
$jumlah_salah = $result['jumlah_salah'];
$elapsed_time_sec = $result['elapsed_time'];

// Ambil info nama kuis
try {
    $stmt = $pdo->prepare("SELECT judul_kuis FROM tb_kuis WHERE id_kuis = :id");
    $stmt->execute(['id' => $id_kuis]);
    $judul_kuis = $stmt->fetchColumn();
} catch (PDOException $e) {
    $judul_kuis = 'Kuis';
}

// Tentukan pesan motivasi dan warna berdasarkan skor
if ($skor >= 85) {
    $motivation_title = "Luar Biasa! Hebat Sekali!";
    $motivation_text = "Pertahankan prestasi hebatmu! Kamu telah menguasai materi pembelajaran bahasa Inggris ini dengan sangat baik.";
    $accent_color = "#16a34a"; // Hijau sukses
    $bg_motivation = "#d1fae5";
    $border_motivation = "#10b981";
} elseif ($skor >= 70) {
    $motivation_title = "Kerja Bagus!";
    $motivation_text = "Kamu sudah memahami materi ini dengan cukup baik. Ayo terus belajar untuk mendapatkan nilai sempurna!";
    $accent_color = "#2563eb"; // Biru info
    $bg_motivation = "#dbeafe";
    $border_motivation = "#3b82f6";
} else {
    $motivation_title = "Jangan Menyerah!";
    $motivation_text = "Nilaimu masih di bawah KKM. Jangan berkecil hati, ayo pelajari lagi materinya lalu ulangi mengerjakan kuis ini.";
    $accent_color = "#d97706"; // Amber/Kuning peringatan
    $bg_motivation = "#fef3c7";
    $border_motivation = "#f59e0b";
}

// Konversi detik pengerjaan ke format menit & detik
$minutes = floor($elapsed_time_sec / 60);
$seconds = $elapsed_time_sec % 60;
$time_string = "";
if ($minutes > 0) {
    $time_string .= $minutes . " Menit ";
}
$time_string .= $seconds . " Detik";

$page_title = 'Hasil Kuis';
$active_page = 'kuis';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Hapus hasil kuis dari session setelah dibaca agar tidak bisa di-refresh/diakses langsung nanti
unset($_SESSION['last_kuis_result']);
?>

<!-- Helper Javascript untuk mendefinisikan Math.floor di PHP -->
<?php
// PHP helper untuk menit karena Math::floor di atas salah ketik
function format_elapsed_time($sec) {
    $m = floor($sec / 60);
    $s = $sec % 60;
    return ($m > 0 ? $m . " Menit " : "") . $s . " Detik";
}
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header">
        <h1>Hasil Evaluasi Kuis</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content" style="max-width: 650px; margin: 0 auto;">
        
        <!-- Hasil Skor Besar -->
        <div style="background: #ffffff; border: 2px solid #374151; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03); text-align: center; margin-bottom: 2rem;">
            <div style="background-color: #f1f5f9; border-bottom: 2px solid #374151; padding: 0.85rem; font-weight: 700; color: #475569; font-size: 0.95rem;">
                <?= htmlspecialchars($judul_kuis) ?>
            </div>
            
            <div style="padding: 2.5rem 2rem;">
                <div style="font-size: 0.95rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 1px;">Skor Akhir Anda</div>
                <div style="font-size: 5rem; font-weight: 800; color: <?= $accent_color ?>; font-family: 'Outfit', sans-serif; margin: 0.5rem 0; line-height: 1;">
                    <?= $skor ?>
                </div>

                <!-- Pesan Motivasi Dinamis -->
                <div style="background-color: <?= $bg_motivation ?>; color: <?= $accent_color ?>; border: 1.5px solid <?= $border_motivation ?>; border-radius: 6px; padding: 1.25rem; margin-top: 1.5rem; text-align: left;">
                    <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.05rem; font-weight: 800; margin-bottom: 0.25rem;"><?= $motivation_title ?></h4>
                    <p style="font-size: 0.9rem; color: #374151; line-height: 1.5; font-weight: 500;"><?= $motivation_text ?></p>
                </div>
            </div>
        </div>

        <!-- Detail Statistik Pengerjaan -->
        <div style="background: #ffffff; border: 1.5px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; display: flex; flex-direction: column; gap: 0.75rem;">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: #1e293b; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 0.25rem;">Rincian Kuis</h3>
            
            <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #475569; font-weight: 500;">
                <span>Jawaban Benar:</span>
                <strong style="color: #16a34a;"><?= $jumlah_benar ?> Soal</strong>
            </div>
            
            <div style="display: flex; justify-content: justify; justify-content: space-between; font-size: 0.95rem; color: #475569; font-weight: 500; border-top: 1px solid #f1f5f9; padding-top: 0.5rem;">
                <span>Jawaban Salah:</span>
                <strong style="color: #ef4444;"><?= $jumlah_salah ?> Soal</strong>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #475569; font-weight: 500; border-top: 1px solid #f1f5f9; padding-top: 0.5rem;">
                <span>Waktu Pengerjaan:</span>
                <strong style="color: #1e293b;"><?= format_elapsed_time($elapsed_time_sec) ?></strong>
            </div>
        </div>

        <!-- Tombol Tindakan Navigasi -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <a href="kuis_kerjakan.php?id_kuis=<?= $id_kuis ?>" class="btn-sm btn-play" style="padding: 0.85rem; font-size: 0.95rem; font-weight: 700; font-family: 'Outfit', sans-serif; text-decoration: none; text-align: center; background-color: #4b5563; border: none;">
                Ulangi Kuis
            </a>
            <a href="kuis.php" class="btn-sm btn-success" style="padding: 0.85rem; font-size: 0.95rem; font-weight: 700; font-family: 'Outfit', sans-serif; text-decoration: none; text-align: center; border: none;">
                Kembali ke Kuis
            </a>
        </div>

    </div>

<?php
require_once '../includes/footer.php';
?>

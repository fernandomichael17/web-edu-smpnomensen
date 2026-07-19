<?php
/**
 * File: admin/dashboard.php
 * Deskripsi: Halaman Dashboard Utama Administrator / Panel Guru.
 *            Menyajikan statistik ringkas siswa, materi, kuis, rata-rata nilai,
 *            dan log aktivitas gabungan terbaru (login, pengerjaan kuis, tambah materi).
 */

// Memroteksi halaman ini agar hanya bisa diakses oleh guru yang sudah login
require_once '../includes/auth_admin.php';

// Memanggil koneksi database
require_once '../config.php';

// 1. Menghitung Statistik Ringkas
try {
    $total_siswa = $pdo->query("SELECT COUNT(*) FROM tb_siswa")->fetchColumn();
    $total_materi = $pdo->query("SELECT COUNT(*) FROM tb_materi")->fetchColumn();
    $total_kuis = $pdo->query("SELECT COUNT(*) FROM tb_kuis")->fetchColumn();
    
    $avg_score_raw = $pdo->query("SELECT AVG(skor) FROM tb_hasil")->fetchColumn();
    $rata_rata_nilai = $avg_score_raw !== null ? round($avg_score_raw, 1) : 0;
} catch (PDOException $e) {
    die("Error database saat memuat statistik: " . $e->getMessage());
}

// 2. Fetch Log Aktivitas Terakhir (UNION dari beberapa sumber)
$activities = [];
try {
    $sql_union = "
        SELECT 'kuis' as tipe, h.waktu_selesai as tanggal, s.nama_siswa, s.kelas, k.judul_kuis as detail, h.skor
        FROM tb_hasil h
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        
        UNION ALL
        
        SELECT 'materi' as tipe, m.created_at as tanggal, '' as nama_siswa, '' as kelas, CONCAT(m.kategori, ' - ', m.judul_materi) as detail, 0 as skor
        FROM tb_materi m
        
        UNION ALL
        
        SELECT 'login' as tipe, s.last_login as tanggal, s.nama_siswa, s.kelas, 'Siswa Login' as detail, 0 as skor
        FROM tb_siswa s
        WHERE s.last_login IS NOT NULL
        
        ORDER BY tanggal DESC
        LIMIT 10
    ";
    
    $stmt_union = $pdo->query($sql_union);
    $activities = $stmt_union->fetchAll();
} catch (PDOException $e) {
    // Fail-safe jika union error
    $activities = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrator - Panel Guru</title>
    
    <!-- Impor Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Memanggil CSS Utama -->
    <link rel="stylesheet" href="../assets/css/style.css?v=1.0.2">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .admin-layout {
            display: flex;
            flex-direction: row;
            flex: 1;
            width: 100%;
        }

        /* Sidebar Navigasi Abu-Abu (Sesuai Storyboard) */
        .sidebar {
            width: 260px;
            background-color: #e5e7eb;
            color: #1f2937;
            padding: 1.5rem 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 2px solid #cbd5e1;
            flex-shrink: 0;
        }

        .sidebar-brand h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            font-weight: 800;
            color: #475569;
            letter-spacing: 1.5px;
            margin-bottom: 2rem;
            text-align: center;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 0.5rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-menu li {
            margin-bottom: 0.85rem;
        }

        .sidebar-menu a {
            color: #374151;
            text-decoration: none;
            padding: 0.75rem 1rem;
            border: 2px solid #9ca3af;
            border-radius: 4px;
            display: block;
            font-weight: 700;
            text-align: center;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
            font-size: 0.9rem;
        }

        .sidebar-menu a:hover {
            background-color: #f1f5f9;
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .sidebar-menu a.active {
            color: #ffffff;
            background-color: #4b5563;
            border-color: #374151;
        }

        .btn-logout-sidebar {
            background-color: #ef4444;
            color: #ffffff;
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            text-align: center;
            font-weight: 700;
            transition: background-color 0.2s;
            border: 2px solid #dc2626;
            margin-top: 1rem;
            display: block;
        }

        .btn-logout-sidebar:hover {
            background-color: #b91c1c;
        }

        /* Area Konten Utama */
        .main-content {
            flex-grow: 1;
            padding: 2.5rem 3rem;
            overflow-y: auto;
        }

        /* Card Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            border: 2px solid #374151;
            border-radius: 4px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .stat-card-header {
            background-color: #e5e7eb;
            border-bottom: 2px solid #374151;
            padding: 0.65rem 1rem;
            text-align: center;
            font-weight: 700;
            color: #374151;
            font-size: 0.95rem;
        }

        .stat-card-body {
            padding: 1.5rem;
            text-align: center;
            font-size: 1.8rem;
            font-weight: 800;
            color: #111827;
            font-family: 'Outfit', sans-serif;
        }

        /* Baris Aktivitas */
        .activity-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }

        .activity-row {
            display: flex;
            border: 2px solid #374151;
            border-radius: 4px;
            overflow: hidden;
        }

        .activity-row-gray {
            background-color: #e5e7eb;
        }

        .activity-row-white {
            background-color: #ffffff;
        }

        .activity-time {
            color: #374151;
            padding: 0.75rem 1rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-align: center;
            border-right: 2px solid #374151;
            min-width: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #cbd5e1;
        }

        .activity-text {
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            color: #1f2937;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

    <!-- Header Atas (Sesuai Storyboard) -->
    <header class="top-header">
        Dashboard Administrator - Panel Guru
    </header>

    <div class="admin-layout">
        <!-- Sidebar Navigasi Kiri (PERSIS 7 Menu Sesuai Tugas) -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h3>Admin Nommensen</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="kelola_materi.php">Kelola Materi</a></li>
                    <li><a href="upload_media.php">Upload Media</a></li>
                    <li><a href="#">Kelola Soal</a></li>
                    <li><a href="laporan_nilai.php">Laporan Nilai</a></li>
                    <li><a href="pengaturan.php">Pengaturan</a></li>
                    <li><a href="kelola_siswa.php">Kelola Data Siswa</a></li>
                </ul>
            </div>
            <!-- Tombol Keluar Sesi -->
            <a href="logout.php" class="btn-logout-sidebar" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Keluar (Logout)</a>
        </aside>

        <!-- Area Konten Utama Kanan -->
        <main class="main-content">
            <div style="margin-bottom: 2rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 800; color: #111827;">Selamat Datang, <?= htmlspecialchars($_SESSION['admin_nama']) ?>!</h2>
                <p style="color: #6b7280; font-size: 0.95rem; margin-top: 0.25rem;">Gunakan panel ini untuk mengelola siswa, mengunggah materi pembelajaran multimedia, dan mengevaluasi hasil nilai kuis.</p>
            </div>

            <!-- Grid Statistik Ringkas -->
            <div class="stats-grid">
                <!-- 1. Total Siswa -->
                <div class="stat-card">
                    <div class="stat-card-header">Total Siswa</div>
                    <div class="stat-card-body"><?= $total_siswa ?> Orang</div>
                </div>

                <!-- 2. Materi Aktif -->
                <div class="stat-card">
                    <div class="stat-card-header">Materi Aktif</div>
                    <div class="stat-card-body"><?= $total_materi ?> Unit</div>
                </div>

                <!-- 3. Kuis Tersedia -->
                <div class="stat-card">
                    <div class="stat-card-header">Kuis Tersedia</div>
                    <div class="stat-card-body"><?= $total_kuis ?> Set</div>
                </div>

                <!-- 4. Rata-rata Nilai -->
                <div class="stat-card">
                    <div class="stat-card-header">Rata-rata Nilai</div>
                    <div class="stat-card-body"><?= $rata_rata_nilai ?></div>
                </div>
            </div>

            <!-- Aktivitas Terakhir -->
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: #111827; margin-top: 2rem; border-bottom: 2px solid #cbd5e1; padding-bottom: 0.5rem;">
                Log Aktivitas Terakhir
            </h3>

            <div class="activity-container">
                <?php if (empty($activities)): ?>
                    <div style="padding: 2rem; text-align: center; color: #6b7280; border: 2px dashed #cbd5e1; border-radius: 4px;">
                        Belum ada riwayat aktivitas di sistem.
                    </div>
                <?php else: ?>
                    <?php foreach ($activities as $idx => $act): ?>
                        <?php 
                        $time_str = date('d M - H:i', strtotime($act['tanggal']));
                        $row_bg_class = ($idx % 2 === 0) ? 'activity-row-gray' : 'activity-row-white';
                        
                        $text = "";
                        if ($act['tipe'] === 'kuis') {
                            $text = "Siswa <strong>" . htmlspecialchars($act['nama_siswa']) . "</strong> (Kelas " . htmlspecialchars($act['kelas']) . ") selesai mengerjakan <strong>" . htmlspecialchars($act['detail']) . "</strong> dengan skor <strong>" . $act['skor'] . "</strong>";
                        } elseif ($act['tipe'] === 'materi') {
                            $text = "Materi baru ditambahkan: <strong>" . htmlspecialchars($act['detail']) . "</strong>";
                        } elseif ($act['tipe'] === 'login') {
                            $text = "Siswa <strong>" . htmlspecialchars($act['nama_siswa']) . "</strong> (Kelas " . htmlspecialchars($act['kelas']) . ") login ke sistem";
                        }
                        ?>
                        <div class="activity-row <?= $row_bg_class ?>">
                            <div class="activity-time"><?= $time_str ?></div>
                            <div class="activity-text"><?= $text ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Footer Bawah (Sesuai Storyboard) -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

</body>
</html>

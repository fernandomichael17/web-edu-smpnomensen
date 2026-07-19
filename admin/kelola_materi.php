<?php
/**
 * File: admin/kelola_materi.php
 * Deskripsi: Halaman Kelola Materi (Vocabulary, Grammar, Conversation) untuk Guru/Admin.
 *            Mendukung CRUD unit materi berdasarkan kategori/tab.
 */

// Memroteksi halaman admin
require_once '../includes/auth_admin.php';
require_once '../config.php';

$error_message = '';
$success_message = '';

// Tentukan kategori aktif dari parameter URL (Default: Vocabulary)
$kategori_aktif = $_GET['kategori'] ?? 'Vocabulary';
if (!in_array($kategori_aktif, ['Vocabulary', 'Grammar', 'Conversation'])) {
    $kategori_aktif = 'Vocabulary';
}

// Folder penyimpanan audio (jika ada file terkait yang perlu dihapus saat unit dihapus)
$upload_dir = '../assets/audio/';

// ---------------------------------------------------------
// PROSES TAMBAH / EDIT UNIT MATERI (POST)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_unit'])) {
    $id_materi = isset($_POST['id_materi']) ? intval($_POST['id_materi']) : 0;
    $judul_materi = trim($_POST['judul_materi'] ?? '');
    $konten_teks = trim($_POST['konten_teks'] ?? '');
    $id_guru = $_SESSION['admin_id'];

    if (empty($judul_materi)) {
        $error_message = "Judul materi tidak boleh kosong!";
    } else {
        if ($kategori_aktif === 'Grammar') {
            // Proses data terstruktur Grammar ke format JSON
            $contoh_arr = [];
            if (!empty($_POST['contoh_kalimat'])) {
                // Bersihkan baris kosong dan buat array kalimat
                $lines = explode("\n", $_POST['contoh_kalimat']);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line !== '') {
                        $contoh_arr[] = $line;
                    }
                }
            }

            $latihan_arr = [];
            if (isset($_POST['latihan_pertanyaan']) && is_array($_POST['latihan_pertanyaan'])) {
                for ($i = 0; $i < count($_POST['latihan_pertanyaan']); $i++) {
                    $pertanyaan = trim($_POST['latihan_pertanyaan'][$i] ?? '');
                    if ($pertanyaan !== '') {
                        $pilihan = [];
                        if (isset($_POST['latihan_pilihan'][$i]) && is_array($_POST['latihan_pilihan'][$i])) {
                            foreach ($_POST['latihan_pilihan'][$i] as $opt) {
                                $pilihan[] = trim($opt);
                            }
                        }
                        $jawaban = trim($_POST['latihan_jawaban'][$i] ?? '');
                        $latihan_arr[] = [
                            'pertanyaan' => $pertanyaan,
                            'pilihan' => $pilihan,
                            'jawaban' => $jawaban
                        ];
                    }
                }
            }

            $konten_data = [
                'definisi' => trim($_POST['definisi'] ?? ''),
                'rumus' => [
                    'positif' => trim($_POST['rumus_positif'] ?? ''),
                    'negatif' => trim($_POST['rumus_negatif'] ?? ''),
                    'tanya' => trim($_POST['rumus_tanya'] ?? '')
                ],
                'contoh' => $contoh_arr,
                'latihan' => $latihan_arr
            ];
            $konten_teks = json_encode($konten_data);
        }

        try {
            if ($id_materi > 0) {
                // Update materi
                $stmt = $pdo->prepare("UPDATE tb_materi SET judul_materi = :judul, konten_teks = :konten WHERE id_materi = :id");
                $stmt->execute(['judul' => $judul_materi, 'konten' => $konten_teks, 'id' => $id_materi]);
                $success_message = "Materi berhasil diperbarui!";
            } else {
                // Insert materi baru
                $stmt = $pdo->prepare("INSERT INTO tb_materi (kategori, judul_materi, konten_teks, id_guru) VALUES (:kategori, :judul, :konten, :id_guru)");
                $stmt->execute([
                    'kategori' => $kategori_aktif,
                    'judul' => $judul_materi,
                    'konten' => $konten_teks,
                    'id_guru' => $id_guru
                ]);
                $success_message = "Materi baru berhasil ditambahkan!";
            }
            // Redirect untuk menghindari submit berulang
            header("Location: kelola_materi.php?kategori=" . $kategori_aktif . "&success=" . urlencode($success_message));
            exit();
        } catch (PDOException $e) {
            $error_message = "Error database: " . $e->getMessage();
        }
    }
}

// Ambil pesan sukses dari redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// ---------------------------------------------------------
// PROSES HAPUS UNIT MATERI (GET)
// ---------------------------------------------------------
if (isset($_GET['delete_unit'])) {
    $id_materi = intval($_GET['delete_unit']);
    try {
        // Ambil semua file audio terkait unit ini untuk dihapus secara fisik di server
        $stmt_files = $pdo->prepare("SELECT file_audio FROM tb_audio WHERE id_materi = :id");
        $stmt_files->execute(['id' => $id_materi]);
        $files = $stmt_files->fetchAll(PDO::FETCH_COLUMN);
        foreach ($files as $file) {
            if ($file && file_exists($upload_dir . $file)) {
                @unlink($upload_dir . $file);
            }
        }

        // Hapus unit dari database (FOREIGN KEY cascade akan menghapus tb_audio dan tb_video terkait)
        $stmt = $pdo->prepare("DELETE FROM tb_materi WHERE id_materi = :id");
        $stmt->execute(['id' => $id_materi]);
        $success_message = "Materi berhasil dihapus!";
        header("Location: kelola_materi.php?kategori=" . $kategori_aktif . "&success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Error database saat menghapus: " . $e->getMessage();
    }
}

// ---------------------------------------------------------
// BACA DATA MATERI (GET)
// ---------------------------------------------------------
$unit_edit_data = null;
$definisi = '';
$rumus_positif = '';
$rumus_negatif = '';
$rumus_tanya = '';
$contoh_val = '';
$latihan_val = [];

if (isset($_GET['edit_unit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tb_materi WHERE id_materi = :id AND kategori = :kategori");
    $stmt->execute(['id' => intval($_GET['edit_unit_id']), 'kategori' => $kategori_aktif]);
    $unit_edit_data = $stmt->fetch();
    
    if ($unit_edit_data && $kategori_aktif === 'Grammar') {
        $decoded = json_decode($unit_edit_data['konten_teks'], true);
        if (is_array($decoded)) {
            $definisi = $decoded['definisi'] ?? '';
            $rumus_positif = $decoded['rumus']['positif'] ?? '';
            $rumus_negatif = $decoded['rumus']['negatif'] ?? '';
            $rumus_tanya = $decoded['rumus']['tanya'] ?? '';
            $contoh_val = isset($decoded['contoh']) ? implode("\n", $decoded['contoh']) : '';
            $latihan_val = $decoded['latihan'] ?? [];
        } else {
            $definisi = $unit_edit_data['konten_teks'];
        }
    }
}

// Fetch semua unit materi sesuai kategori aktif
try {
    $stmt_materi = $pdo->prepare("SELECT * FROM tb_materi WHERE kategori = :kategori ORDER BY id_materi ASC");
    $stmt_materi->execute(['kategori' => $kategori_aktif]);
    $materi_list = $stmt_materi->fetchAll();
} catch (PDOException $e) {
    $materi_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Materi - Nommensen Admin</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Memanggil CSS Utama -->
    <link rel="stylesheet" href="../assets/css/style.css?v=1.0.1">
    
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

        /* Sidebar Sederhana Admin Sesuai Gambar Storyboard */
        .sidebar {
            width: 250px;
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
            font-size: 0.95rem;
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

        /* Tabs Navigation */
        .tabs-nav {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 2rem;
        }

        .tab-link {
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            font-weight: 700;
            color: #64748b;
            border: 2px solid transparent;
            border-bottom: none;
            border-radius: 6px 6px 0 0;
            margin-bottom: -2px;
            transition: all 0.2s;
            font-family: 'Outfit', sans-serif;
        }

        .tab-link:hover {
            color: var(--accent-blue);
            background-color: #f8fafc;
        }

        .tab-link.active {
            color: var(--accent-blue);
            background-color: #ffffff;
            border-color: #cbd5e1 #cbd5e1 #ffffff #cbd5e1;
        }

        .form-inline {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }

        .form-control {
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
            border: 1.5px solid var(--border-color);
            border-radius: 6px;
            outline: none;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: var(--accent-blue);
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 0.85rem 1.25rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            padding: 0.85rem 1.25rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <!-- Header Atas (Sesuai Storyboard) -->
    <header class="top-header">
        Dashboard Administrator - Panel Guru
    </header>

    <!-- Pembungkus Layout Tengah -->
    <div class="admin-layout">
        <!-- Sidebar Navigasi Kiri (Sesuai Storyboard) -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h3>Admin</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="kelola_materi.php" class="active">Kelola Materi</a></li>
                    <li><a href="upload_media.php">Upload Media</a></li>
                    <li><a href="#">Kelola Soal</a></li>
                    <li><a href="#">Laporan Nilai</a></li>
                    <li><a href="#">Pengaturan</a></li>
                </ul>
            </div>
            <!-- Tombol Keluar Sesi -->
            <a href="logout.php" class="btn-logout-sidebar">Keluar (Logout)</a>
        </aside>

        <!-- Area Konten Utama Kanan -->
        <main class="main-content">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 700; color: #111827; margin-bottom: 1.5rem;">
                Kelola Materi Pembelajaran
            </h2>

            <!-- Tampilkan Alert Pesan -->
            <?php if ($error_message !== ''): ?>
                <div class="alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <?php if ($success_message !== ''): ?>
                <div class="alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <!-- Navigasi Tab Kategori Kategori -->
            <nav class="tabs-nav">
                <a href="kelola_materi.php?kategori=Vocabulary" class="tab-link <?= $kategori_aktif === 'Vocabulary' ? 'active' : '' ?>">Vocabulary</a>
                <a href="kelola_materi.php?kategori=Grammar" class="tab-link <?= $kategori_aktif === 'Grammar' ? 'active' : '' ?>">Grammar</a>
                <a href="kelola_materi.php?kategori=Conversation" class="tab-link <?= $kategori_aktif === 'Conversation' ? 'active' : '' ?>">Conversation</a>
            </nav>

            <?php if ($kategori_aktif === 'Vocabulary'): ?>
                <!-- ===================================================================
                     TAB 1: VOCABULARY
                     =================================================================== -->
                <!-- Form Input/Edit Unit Vocabulary -->
                <div class="form-card">
                    <div class="form-title"><?= $unit_edit_data ? 'Edit Unit Vocabulary' : 'Tambah Unit Vocabulary Baru' ?></div>
                    <form action="kelola_materi.php?kategori=Vocabulary" method="POST" class="form-inline">
                        <?php if ($unit_edit_data): ?>
                            <input type="hidden" name="id_materi" value="<?= $unit_edit_data['id_materi'] ?>">
                        <?php endif; ?>
                        
                        <div style="display: flex; flex-direction: column; flex-grow: 1;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Nama Unit (Contoh: Unit 1: School Objects):</label>
                            <input type="text" name="judul_materi" class="form-control" placeholder="Masukkan nama unit..." required value="<?= htmlspecialchars($unit_edit_data['judul_materi'] ?? '') ?>">
                        </div>

                        <div style="display: flex; flex-direction: column; flex-grow: 2;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Deskripsi Singkat Unit:</label>
                            <input type="text" name="konten_teks" class="form-control" placeholder="Masukkan deskripsi..." value="<?= htmlspecialchars($unit_edit_data['konten_teks'] ?? '') ?>">
                        </div>

                        <button type="submit" name="save_unit" class="btn-sm btn-success" style="padding: 0.75rem 1.25rem;">
                            <?= $unit_edit_data ? 'Update Unit' : 'Tambah Unit' ?>
                        </button>
                        
                        <?php if ($unit_edit_data): ?>
                            <a href="kelola_materi.php?kategori=Vocabulary" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; padding: 0.75rem 1.25rem;">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Tabel Daftar Unit Vocabulary -->
                <h3>Daftar Unit Pembelajaran Vocabulary</h3>
                <div class="table-container">
                    <table class="table-materi">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Nama Unit</th>
                                <th style="width: 40%;">Deskripsi Unit</th>
                                <th style="text-align: center; width: 30%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($materi_list)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #6b7280; padding: 2rem;">
                                        Belum ada unit vocabulary yang terdaftar.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($materi_list as $unit): ?>
                                    <tr>
                                        <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($unit['judul_materi']) ?></td>
                                        <td style="color: #4b5563; font-size: 0.9rem;"><?= htmlspecialchars($unit['konten_teks'] ?? '-') ?></td>
                                        <td style="text-align: center;">
                                            <!-- Hubungkan langsung ke halaman upload_media.php -->
                                            <a href="upload_media.php?id_materi=<?= $unit['id_materi'] ?>" class="btn-sm btn-play" style="background-color: #2563eb; color: white;">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 2px;">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"></path>
                                                </svg>
                                                Upload Media
                                            </a>
                                            <a href="kelola_materi.php?kategori=Vocabulary&edit_unit_id=<?= $unit['id_materi'] ?>" class="btn-sm btn-edit">Edit</a>
                                            <a href="kelola_materi.php?kategori=Vocabulary&delete_unit=<?= $unit['id_materi'] ?>" class="btn-sm btn-delete" onclick="return confirm('Menghapus unit akan menghapus seluruh kosakata di dalamnya secara permanen! Apakah Anda yakin?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($kategori_aktif === 'Grammar'): ?>
                <!-- ===================================================================
                     TAB 2: GRAMMAR
                     =================================================================== -->
                <!-- Form Input/Edit Unit Grammar -->
                <div class="form-card">
                    <div class="form-title"><?= $unit_edit_data ? 'Edit Unit Grammar' : 'Tambah Unit Grammar Baru' ?></div>
                    <form action="kelola_materi.php?kategori=Grammar" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <?php if ($unit_edit_data): ?>
                            <input type="hidden" name="id_materi" value="<?= $unit_edit_data['id_materi'] ?>">
                        <?php endif; ?>
                        
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Nama Unit Grammar (Contoh: Unit 1: Simple Present Tense):</label>
                            <input type="text" name="judul_materi" class="form-control" placeholder="Masukkan nama unit..." required value="<?= htmlspecialchars($unit_edit_data['judul_materi'] ?? '') ?>" style="width: 100%;">
                        </div>

                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Definisi Singkat Grammar:</label>
                            <textarea name="definisi" class="form-control" rows="3" placeholder="Jelaskan penggunaan tense/grammar ini..." required style="width: 100%; resize: vertical;"><?= htmlspecialchars($definisi) ?></textarea>
                        </div>

                        <!-- Rumus Grid -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; color: #16a34a;">Rumus Kalimat Positif (+):</label>
                                <input type="text" name="rumus_positif" class="form-control" placeholder="Contoh: S + V1 (s/es)" required value="<?= htmlspecialchars($rumus_positif) ?>">
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; color: #dc2626;">Rumus Kalimat Negatif (-):</label>
                                <input type="text" name="rumus_negatif" class="form-control" placeholder="Contoh: S + do/does + not + V1" required value="<?= htmlspecialchars($rumus_negatif) ?>">
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; color: #2563eb;">Rumus Kalimat Tanya (?):</label>
                                <input type="text" name="rumus_tanya" class="form-control" placeholder="Contoh: Do/Does + S + V1?" required value="<?= htmlspecialchars($rumus_tanya) ?>">
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                Contoh Kalimat (Satu kalimat per baris, pisahkan bahasa Inggris dan Indonesia dengan karakter '|'):
                            </label>
                            <textarea name="contoh_kalimat" class="form-control" rows="4" placeholder="Contoh:&#10;I eat rice | Saya makan nasi&#10;She does not study | Dia tidak belajar" required style="width: 100%; resize: vertical;"><?= htmlspecialchars($contoh_val) ?></textarea>
                        </div>

                        <!-- Latihan Soal -->
                        <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 0.5rem;">
                            <h4 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; color: var(--accent-blue); margin-bottom: 1rem;">Latihan Soal Singkat (Pilihan Ganda)</h4>
                            
                            <?php for ($i = 0; $i < 2; $i++): ?>
                                <?php 
                                $q_text = $latihan_val[$i]['pertanyaan'] ?? '';
                                $q_opts = $latihan_val[$i]['pilihan'] ?? ['', '', '', ''];
                                $q_ans = $latihan_val[$i]['jawaban'] ?? '';
                                ?>
                                <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                                    <div style="font-weight: 700; font-size: 0.9rem; margin-bottom: 0.75rem; color: #475569;">Soal #<?= $i + 1 ?></div>
                                    <div style="display: flex; flex-direction: column; margin-bottom: 0.75rem;">
                                        <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem;">Pertanyaan:</label>
                                        <input type="text" name="latihan_pertanyaan[]" class="form-control" placeholder="Contoh: He ___ to market every Sunday." value="<?= htmlspecialchars($q_text) ?>" style="width: 100%;">
                                    </div>
                                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; margin-bottom: 0.75rem;">
                                        <div>
                                            <input type="text" name="latihan_pilihan[<?= $i ?>][]" class="form-control" placeholder="Pilihan A" style="width: 100%; padding: 0.5rem;" value="<?= htmlspecialchars($q_opts[0] ?? '') ?>">
                                        </div>
                                        <div>
                                            <input type="text" name="latihan_pilihan[<?= $i ?>][]" class="form-control" placeholder="Pilihan B" style="width: 100%; padding: 0.5rem;" value="<?= htmlspecialchars($q_opts[1] ?? '') ?>">
                                        </div>
                                        <div>
                                            <input type="text" name="latihan_pilihan[<?= $i ?>][]" class="form-control" placeholder="Pilihan C" style="width: 100%; padding: 0.5rem;" value="<?= htmlspecialchars($q_opts[2] ?? '') ?>">
                                        </div>
                                        <div>
                                            <input type="text" name="latihan_pilihan[<?= $i ?>][]" class="form-control" placeholder="Pilihan D" style="width: 100%; padding: 0.5rem;" value="<?= htmlspecialchars($q_opts[3] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; max-width: 250px;">
                                        <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem;">Kunci Jawaban:</label>
                                        <input type="text" name="latihan_jawaban[]" class="form-control" placeholder="Contoh: goes" value="<?= htmlspecialchars($q_ans) ?>">
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <div>
                            <button type="submit" name="save_unit" class="btn-sm btn-success" style="padding: 0.75rem 1.5rem; font-size: 0.95rem;">
                                <?= $unit_edit_data ? 'Update Unit Grammar' : 'Simpan Unit Grammar Baru' ?>
                            </button>
                            <?php if ($unit_edit_data): ?>
                                <a href="kelola_materi.php?kategori=Grammar" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; padding: 0.75rem 1.5rem; margin-left: 0.5rem;">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <h3>Daftar Unit Pembelajaran Grammar</h3>
                <div class="table-container">
                    <table class="table-materi">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Nama Unit</th>
                                <th style="width: 50%;">Deskripsi / Konten Ringkas</th>
                                <th style="text-align: center; width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($materi_list)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #6b7280; padding: 2rem;">
                                        Belum ada materi Grammar. Klik form di atas untuk menambah materi baru.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($materi_list as $unit): ?>
                                    <?php
                                    $desc = '-';
                                    $decoded = json_decode($unit['konten_teks'], true);
                                    if (is_array($decoded)) {
                                        $desc = $decoded['definisi'] ?? '';
                                    } else {
                                        $desc = $unit['konten_teks'] ?? '';
                                    }
                                    if (strlen($desc) > 80) {
                                        $desc = substr(htmlspecialchars($desc), 0, 80) . '...';
                                    } else {
                                        $desc = htmlspecialchars($desc);
                                    }
                                    ?>
                                    <tr>
                                        <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($unit['judul_materi']) ?></td>
                                        <td style="color: #4b5563; font-size: 0.9rem;"><?= $desc ?></td>
                                        <td style="text-align: center;">
                                            <a href="kelola_materi.php?kategori=Grammar&edit_unit_id=<?= $unit['id_materi'] ?>" class="btn-sm btn-edit">Edit</a>
                                            <a href="kelola_materi.php?kategori=Grammar&delete_unit=<?= $unit['id_materi'] ?>" class="btn-sm btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus materi ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($kategori_aktif === 'Conversation'): ?>
                <!-- ===================================================================
                     TAB 3: CONVERSATION (PLACEHOLDER MINGGU DEPAN)
                     =================================================================== -->
                <div class="form-card">
                    <div class="form-title"><?= $unit_edit_data ? 'Edit Unit Conversation' : 'Tambah Unit Conversation Baru' ?></div>
                    <form action="kelola_materi.php?kategori=Conversation" method="POST" class="form-inline">
                        <?php if ($unit_edit_data): ?>
                            <input type="hidden" name="id_materi" value="<?= $unit_edit_data['id_materi'] ?>">
                        <?php endif; ?>
                        
                        <div style="display: flex; flex-direction: column; flex-grow: 1;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Nama Unit Percakapan:</label>
                            <input type="text" name="judul_materi" class="form-control" placeholder="Masukkan nama unit..." required value="<?= htmlspecialchars($unit_edit_data['judul_materi'] ?? '') ?>">
                        </div>

                        <div style="display: flex; flex-direction: column; flex-grow: 2;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Deskripsi Percakapan:</label>
                            <input type="text" name="konten_teks" class="form-control" placeholder="Masukkan penjelasan..." value="<?= htmlspecialchars($unit_edit_data['konten_teks'] ?? '') ?>">
                        </div>

                        <button type="submit" name="save_unit" class="btn-sm btn-success" style="padding: 0.75rem 1.25rem;">
                            <?= $unit_edit_data ? 'Update Unit' : 'Tambah Unit' ?>
                        </button>
                        
                        <?php if ($unit_edit_data): ?>
                            <a href="kelola_materi.php?kategori=Conversation" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; padding: 0.75rem 1.25rem;">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <h3>Daftar Unit Pembelajaran Conversation</h3>
                <div class="table-container">
                    <table class="table-materi">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Nama Unit</th>
                                <th style="width: 40%;">Deskripsi</th>
                                <th style="text-align: center; width: 30%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($materi_list)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #6b7280; padding: 2rem;">
                                        Belum ada materi Conversation. Silakan tambah lewat form di atas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($materi_list as $unit): ?>
                                    <tr>
                                        <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($unit['judul_materi']) ?></td>
                                        <td style="color: #4b5563; font-size: 0.9rem;"><?= htmlspecialchars($unit['konten_teks'] ?? '-') ?></td>
                                        <td style="text-align: center;">
                                            <a href="upload_media.php?id_materi=<?= $unit['id_materi'] ?>" class="btn-sm btn-play" style="background-color: #2563eb; color: white;">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 2px;">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"></path>
                                                </svg>
                                                Upload Media
                                            </a>
                                            <a href="kelola_materi.php?kategori=Conversation&edit_unit_id=<?= $unit['id_materi'] ?>" class="btn-sm btn-edit">Edit</a>
                                            <a href="kelola_materi.php?kategori=Conversation&delete_unit=<?= $unit['id_materi'] ?>" class="btn-sm btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus materi percakapan ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div> <!-- Penutup .admin-layout -->

    <!-- Footer Bawah -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

</body>
</html>

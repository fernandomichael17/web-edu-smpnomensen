<?php
/**
 * File: admin/materi_vocabulary.php
 * Deskripsi: Halaman kelola materi Vocabulary untuk Guru/Admin.
 *            Mendukung CRUD unit dan CRUD kata beserta upload file audio.
 */

// Memroteksi halaman admin
require_once '../includes/auth_admin.php';
require_once '../config.php';

$error_message = '';
$success_message = '';

// Folder penyimpanan audio
$upload_dir = '../assets/audio/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ---------------------------------------------------------
// PROSES ASET / AKSI DATA (POST)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. TAMBAH / EDIT UNIT MATERI
    if (isset($_POST['save_unit'])) {
        $id_materi = isset($_POST['id_materi']) ? intval($_POST['id_materi']) : 0;
        $judul_materi = trim($_POST['judul_materi'] ?? '');
        $konten_teks = trim($_POST['konten_teks'] ?? '');
        $id_guru = $_SESSION['admin_id'];

        if (empty($judul_materi)) {
            $error_message = "Judul unit tidak boleh kosong!";
        } else {
            try {
                if ($id_materi > 0) {
                    // Update
                    $stmt = $pdo->prepare("UPDATE tb_materi SET judul_materi = :judul, konten_teks = :konten WHERE id_materi = :id");
                    $stmt->execute(['judul' => $judul_materi, 'konten' => $konten_teks, 'id' => $id_materi]);
                    $success_message = "Unit materi berhasil diupdate!";
                } else {
                    // Insert
                    $stmt = $pdo->prepare("INSERT INTO tb_materi (kategori, judul_materi, konten_teks, id_guru) VALUES ('Vocabulary', :judul, :konten, :id_guru)");
                    $stmt->execute(['judul' => $judul_materi, 'konten' => $konten_teks, 'id_guru' => $id_guru]);
                    $success_message = "Unit materi baru berhasil ditambahkan!";
                }
            } catch (PDOException $e) {
                $error_message = "Error database: " . $e->getMessage();
            }
        }
    }

    // 2. TAMBAH / EDIT KATA (VOCABULARY ITEM)
    if (isset($_POST['save_word'])) {
        $id_audio = isset($_POST['id_audio']) ? intval($_POST['id_audio']) : 0;
        $id_materi = intval($_POST['id_materi']);
        $english = trim($_POST['english'] ?? '');
        $indonesian = trim($_POST['indonesian'] ?? '');
        
        if (empty($english) || empty($indonesian)) {
            $error_message = "Kata Inggris dan Arti Indonesia wajib diisi!";
        } else {
            $keterangan = $english . '|' . $indonesian;
            $file_audio_name = '';

            // Handle file upload
            if (isset($_FILES['file_audio']) && $_FILES['file_audio']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['file_audio']['tmp_name'];
                $orig_name = $_FILES['file_audio']['name'];
                $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                
                // Batasi tipe file audio
                $allowed_exts = ['mp3', 'wav', 'ogg', 'm4a'];
                if (!in_array($ext, $allowed_exts)) {
                    $error_message = "Hanya file audio (mp3, wav, ogg, m4a) yang diperbolehkan!";
                } else {
                    // Generate nama file unik agar tidak bertumpukan
                    $file_audio_name = time() . '_' . uniqid() . '.' . $ext;
                    if (!move_uploaded_file($file_tmp, $upload_dir . $file_audio_name)) {
                        $error_message = "Gagal memindahkan file audio terunggah.";
                        $file_audio_name = '';
                    }
                }
            }

            if (empty($error_message)) {
                try {
                    if ($id_audio > 0) {
                        // EDIT KATA
                        if ($file_audio_name !== '') {
                            // Hapus file audio lama dari server jika diganti
                            $stmt_old = $pdo->prepare("SELECT file_audio FROM tb_audio WHERE id_audio = :id");
                            $stmt_old->execute(['id' => $id_audio]);
                            $old_file = $stmt_old->fetchColumn();
                            if ($old_file && file_exists($upload_dir . $old_file)) {
                                @unlink($upload_dir . $old_file);
                            }

                            // Update kata beserta file audio baru
                            $stmt = $pdo->prepare("UPDATE tb_audio SET file_audio = :file, keterangan = :ket WHERE id_audio = :id");
                            $stmt->execute(['file' => $file_audio_name, 'ket' => $keterangan, 'id' => $id_audio]);
                        } else {
                            // Update kata saja tanpa ganti audio
                            $stmt = $pdo->prepare("UPDATE tb_audio SET keterangan = :ket WHERE id_audio = :id");
                            $stmt->execute(['ket' => $keterangan, 'id' => $id_audio]);
                        }
                        $success_message = "Kata berhasil diperbarui!";
                    } else {
                        // TAMBAH KATA BARU
                        if ($file_audio_name === '') {
                            $error_message = "File audio pelafalan wajib diupload untuk kata baru!";
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO tb_audio (id_materi, file_audio, keterangan) VALUES (:id_materi, :file, :ket)");
                            $stmt->execute(['id_materi' => $id_materi, 'file' => $file_audio_name, 'ket' => $keterangan]);
                            $success_message = "Kosakata baru berhasil ditambahkan ke unit ini!";
                        }
                    }
                } catch (PDOException $e) {
                    $error_message = "Error database: " . $e->getMessage();
                }
            }
        }
    }
}

// ---------------------------------------------------------
// PROSES ASET / AKSI DATA (GET)
// ---------------------------------------------------------
// HAPUS UNIT MATERI
if (isset($_GET['delete_unit'])) {
    $id_materi = intval($_GET['delete_unit']);
    try {
        // Ambil semua file audio terkait unit ini untuk dihapus dari server
        $stmt_files = $pdo->prepare("SELECT file_audio FROM tb_audio WHERE id_materi = :id");
        $stmt_files->execute(['id' => $id_materi]);
        $files = $stmt_files->fetchAll(PDO::FETCH_COLUMN);
        foreach ($files as $file) {
            if ($file && file_exists($upload_dir . $file)) {
                @unlink($upload_dir . $file);
            }
        }

        // Hapus unit dari database (otomatis CASCADE akan menghapus tb_audio terkait di MySQL)
        $stmt = $pdo->prepare("DELETE FROM tb_materi WHERE id_materi = :id");
        $stmt->execute(['id' => $id_materi]);
        $success_message = "Unit materi berhasil dihapus!";
    } catch (PDOException $e) {
        $error_message = "Error saat menghapus: " . $e->getMessage();
    }
}

// HAPUS KATA (VOCABULARY ITEM)
if (isset($_GET['delete_word'])) {
    $id_audio = intval($_GET['delete_word']);
    $id_materi = intval($_GET['id_materi'] ?? 0);
    try {
        // Ambil nama file audio untuk dihapus dari server
        $stmt_file = $pdo->prepare("SELECT file_audio FROM tb_audio WHERE id_audio = :id");
        $stmt_file->execute(['id' => $id_audio]);
        $file = $stmt_file->fetchColumn();
        if ($file && file_exists($upload_dir . $file)) {
            @unlink($upload_dir . $file);
        }

        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM tb_audio WHERE id_audio = :id");
        $stmt->execute(['id' => $id_audio]);
        $success_message = "Kata berhasil dihapus dari unit ini!";
    } catch (PDOException $e) {
        $error_message = "Error database: " . $e->getMessage();
    }
}

// ---------------------------------------------------------
// BACA DATA UNTUK TAMPILAN
// ---------------------------------------------------------
$action = $_GET['action'] ?? 'list';
$unit_data = null;
$word_edit_data = null;

if ($action === 'edit_unit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tb_materi WHERE id_materi = :id AND kategori = 'Vocabulary'");
    $stmt->execute(['id' => intval($_GET['id'])]);
    $unit_data = $stmt->fetch();
}

if ($action === 'manage_words' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tb_materi WHERE id_materi = :id AND kategori = 'Vocabulary'");
    $stmt->execute(['id' => intval($_GET['id'])]);
    $unit_data = $stmt->fetch();
    
    if (!$unit_data) {
        header("Location: materi_vocabulary.php");
        exit();
    }
    
    // Ambil list kata di unit ini
    $stmt_words = $pdo->prepare("SELECT * FROM tb_audio WHERE id_materi = :id ORDER BY id_audio ASC");
    $stmt_words->execute(['id' => $unit_data['id_materi']]);
    $words = $stmt_words->fetchAll();

    // Jika sedang mengedit kata tertentu
    if (isset($_GET['edit_word_id'])) {
        $stmt_w = $pdo->prepare("SELECT * FROM tb_audio WHERE id_audio = :id");
        $stmt_w->execute(['id' => intval($_GET['edit_word_id'])]);
        $word_edit_data = $stmt_w->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Vocabulary - Nommensen Admin</title>
    
    <!-- Memanggil Google Fonts -->
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
            background-color: #e5e7eb; /* Abu-abu terang sesuai storyboard */
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
            border: 2px solid #9ca3af; /* Kotak outline sesuai gambar storyboard */
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
            background-color: #4b5563; /* Tombol aktif abu-abu gelap sesuai gambar storyboard */
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

        /* Konten Utama */
        .main-content {
            flex-grow: 1;
            padding: 3rem;
            overflow-y: auto;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .header-content h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            color: var(--accent-blue);
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
                    <li><a href="materi_vocabulary.php" class="active">Kelola Materi</a></li>
                    <li><a href="#">Upload Media</a></li>
                    <li><a href="#">Kelola Soal</a></li>
                    <li><a href="#">Laporan Nilai</a></li>
                    <li><a href="#">Pengaturan</a></li>
                </ul>
            </div>
            <!-- Tombol Keluar Sesi -->
            <a href="logout.php" class="btn-logout-sidebar">Keluar (Logout)</a>
        </aside>

        <!-- Area Konten Utama Kanan (Sesuai Storyboard) -->
        <main class="main-content">
            <header class="header-content">
                <h1>Kelola Vocabulary</h1>
                <div class="user-info" style="font-size: 0.9rem; color: var(--text-muted);">
                    Login sebagai: <strong><?= htmlspecialchars($_SESSION['admin_nama']) ?></strong>
                </div>
            </header>

        <!-- Tampilkan Alert Pesan -->
        <?php if ($error_message !== ''): ?>
            <div class="alert-error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <?php if ($success_message !== ''): ?>
            <div class="alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <!-- ===================================================================
             TAMPILAN 1: KELOLA KOSAKATA DI DALAM UNIT (ACTION = MANAGE_WORDS)
             =================================================================== -->
        <?php if ($action === 'manage_words' && $unit_data): ?>
            <div style="margin-bottom: 1.5rem;">
                <a href="materi_vocabulary.php" class="btn-sm btn-play" style="background-color: #4b5563; text-decoration: none;">
                    &larr; Kembali ke Daftar Unit
                </a>
            </div>

            <!-- Form Tambah / Edit Kata -->
            <div class="form-card">
                <div class="form-title">
                    <?= $word_edit_data ? 'Edit Kata Kosakata' : 'Tambah Kata Kosakata Baru' ?> 
                    di <strong><?= htmlspecialchars($unit_data['judul_materi']) ?></strong>
                </div>
                
                <form action="materi_vocabulary.php?action=manage_words&id=<?= $unit_data['id_materi'] ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_materi" value="<?= $unit_data['id_materi'] ?>">
                    
                    <?php if ($word_edit_data): ?>
                        <input type="hidden" name="id_audio" value="<?= $word_edit_data['id_audio'] ?>">
                        <?php 
                        $parts = explode('|', $word_edit_data['keterangan']);
                        $english_val = $parts[0] ?? '';
                        $indonesian_val = $parts[1] ?? '';
                        ?>
                    <?php else: ?>
                        <?php $english_val = ''; $indonesian_val = ''; ?>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Bahasa Inggris:</label>
                            <input type="text" name="english" class="form-control" placeholder="Contoh: Book" required value="<?= htmlspecialchars($english_val) ?>">
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Arti (Bahasa Indonesia):</label>
                            <input type="text" name="indonesian" class="form-control" placeholder="Contoh: Buku" required value="<?= htmlspecialchars($indonesian_val) ?>">
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                File Audio Pelafalan (mp3):
                                <?php if ($word_edit_data): ?>
                                    <span style="font-size: 0.8rem; color: #d97706;">(Biarkan kosong jika tidak diganti)</span>
                                <?php endif; ?>
                            </label>
                            <input type="file" name="file_audio" class="form-control" <?= $word_edit_data ? '' : 'required' ?> accept="audio/*">
                        </div>
                    </div>

                    <button type="submit" name="save_word" class="btn-sm btn-success">
                        <?= $word_edit_data ? 'Update Kata' : 'Simpan Kata Baru' ?>
                    </button>
                    <?php if ($word_edit_data): ?>
                        <a href="materi_vocabulary.php?action=manage_words&id=<?= $unit_data['id_materi'] ?>" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; margin-left: 0.5rem;">Batal</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabel Daftar Kata -->
            <h3>Daftar Kosakata dalam Unit Ini</h3>
            <div class="table-container">
                <table class="table-materi">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Bahasa Inggris</th>
                            <th style="width: 30%;">Arti Indonesia</th>
                            <th style="width: 20%;">Audio</th>
                            <th style="text-align: center; width: 20%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($words)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #6b7280; padding: 2rem;">
                                    Belum ada kata di dalam unit ini. Silakan tambahkan lewat form di atas.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($words as $word): ?>
                                <?php
                                $parts = explode('|', $word['keterangan']);
                                $english = $parts[0] ?? '';
                                $indonesian = $parts[1] ?? '';
                                ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($english) ?></td>
                                    <td><?= htmlspecialchars($indonesian) ?></td>
                                    <td>
                                        <audio src="../assets/audio/<?= htmlspecialchars($word['file_audio']) ?>" controls style="max-width: 180px; height: 32px;"></audio>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="materi_vocabulary.php?action=manage_words&id=<?= $unit_data['id_materi'] ?>&edit_word_id=<?= $word['id_audio'] ?>" class="btn-sm btn-edit">Edit</a>
                                        <a href="materi_vocabulary.php?delete_word=<?= $word['id_audio'] ?>&id_materi=<?= $unit_data['id_materi'] ?>" class="btn-sm btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus kata ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <!-- ===================================================================
             TAMPILAN 2: DAFTAR UNIT & TAMBAH/EDIT UNIT (DEFAULT)
             =================================================================== -->
        <?php else: ?>
            <!-- Form Input Unit Baru / Edit Unit -->
            <div class="form-card">
                <div class="form-title"><?= $unit_data ? 'Edit Unit Vocabulary' : 'Tambah Unit Vocabulary Baru' ?></div>
                <form action="materi_vocabulary.php" method="POST" class="form-inline">
                    <?php if ($unit_data): ?>
                        <input type="hidden" name="id_materi" value="<?= $unit_data['id_materi'] ?>">
                    <?php endif; ?>
                    
                    <div style="display: flex; flex-direction: column; flex-grow: 1;">
                        <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Nama Unit (Contoh: Unit 1: School Objects):</label>
                        <input type="text" name="judul_materi" class="form-control" placeholder="Masukkan nama unit..." required value="<?= htmlspecialchars($unit_data['judul_materi'] ?? '') ?>">
                    </div>

                    <div style="display: flex; flex-direction: column; flex-grow: 2;">
                        <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Deskripsi Singkat Unit (Optional):</label>
                        <input type="text" name="konten_teks" class="form-control" placeholder="Masukkan deskripsi..." value="<?= htmlspecialchars($unit_data['konten_teks'] ?? '') ?>">
                    </div>

                    <button type="submit" name="save_unit" class="btn-sm btn-success" style="padding: 0.75rem 1.25rem;">
                        <?= $unit_data ? 'Update Unit' : 'Tambah Unit' ?>
                    </button>
                    
                    <?php if ($unit_data): ?>
                        <a href="materi_vocabulary.php" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; padding: 0.75rem 1.25rem;">Batal</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabel Daftar Unit -->
            <h3>Daftar Unit Pembelajaran Vocabulary</h3>
            
            <?php
            // Fetch semua unit
            try {
                $stmt = $pdo->query("SELECT * FROM tb_materi WHERE kategori = 'Vocabulary' ORDER BY id_materi ASC");
                $all_units = $stmt->fetchAll();
            } catch (PDOException $e) {
                $all_units = [];
            }
            ?>

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
                        <?php if (empty($all_units)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #6b7280; padding: 2rem;">
                                    Belum ada unit vocabulary yang terdaftar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_units as $unit): ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($unit['judul_materi']) ?></td>
                                    <td style="color: #4b5563; font-size: 0.9rem;"><?= htmlspecialchars($unit['konten_teks'] ?? '-') ?></td>
                                    <td style="text-align: center;">
                                        <a href="materi_vocabulary.php?action=manage_words&id=<?= $unit['id_materi'] ?>" class="btn-sm btn-play" style="background-color: #2563eb; color: white;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 2px;">
                                                <path d="M12 5v14M5 12h14"></path>
                                            </svg>
                                            Kelola Kata
                                        </a>
                                        <a href="materi_vocabulary.php?action=edit_unit&id=<?= $unit['id_materi'] ?>" class="btn-sm btn-edit">Edit</a>
                                        <a href="materi_vocabulary.php?delete_unit=<?= $unit['id_materi'] ?>" class="btn-sm btn-delete" onclick="return confirm('Menghapus unit akan menghapus seluruh kosakata di dalamnya secara permanen! Apakah Anda yakin?')">Hapus</a>
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

    <!-- Footer Bawah (Sesuai Storyboard) -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

</body>
</html>

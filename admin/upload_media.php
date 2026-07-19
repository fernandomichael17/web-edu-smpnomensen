<?php
/**
 * File: admin/upload_media.php
 * Deskripsi: Halaman Upload Media (Audio pelafalan Vocabulary dan Video percakapan Conversation).
 *            Mendukung CRUD kata dan upload audio untuk materi Vocabulary.
 */

// Memroteksi halaman admin
require_once '../includes/auth_admin.php';
require_once '../config.php';

$error_message = '';
$success_message = '';

// Folder penyimpanan media
$audio_upload_dir = '../assets/audio/';
$video_upload_dir = '../assets/video/';

// Pastikan folder penyimpanan media ada
if (!is_dir($audio_upload_dir)) {
    mkdir($audio_upload_dir, 0777, true);
}
if (!is_dir($video_upload_dir)) {
    mkdir($video_upload_dir, 0777, true);
}

// Baca id_materi dari parameter URL (jika ada)
$id_materi = isset($_GET['id_materi']) ? intval($_GET['id_materi']) : 0;
$unit_data = null;

if ($id_materi > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tb_materi WHERE id_materi = :id");
        $stmt->execute(['id' => $id_materi]);
        $unit_data = $stmt->fetch();
    } catch (PDOException $e) {
        $error_message = "Error database: " . $e->getMessage();
    }
}

// ---------------------------------------------------------
// PROSES TAMBAH / EDIT KATA VOCABULARY (POST)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_word'])) {
    $id_audio = isset($_POST['id_audio']) ? intval($_POST['id_audio']) : 0;
    $english = trim($_POST['english'] ?? '');
    $indonesian = trim($_POST['indonesian'] ?? '');
    
    if (empty($english) || empty($indonesian)) {
        $error_message = "Kata Inggris dan Arti Indonesia wajib diisi!";
    } else {
        $keterangan = $english . '|' . $indonesian;
        $file_audio_name = '';

        // Tangani unggah file audio
        if (isset($_FILES['file_audio']) && $_FILES['file_audio']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['file_audio']['tmp_name'];
            $orig_name = $_FILES['file_audio']['name'];
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            
            $allowed_exts = ['mp3', 'wav', 'ogg', 'm4a'];
            if (!in_array($ext, $allowed_exts)) {
                $error_message = "Hanya file audio (mp3, wav, ogg, m4a) yang diperbolehkan!";
            } else {
                // Generate nama file acak unik
                $file_audio_name = time() . '_' . uniqid() . '.' . $ext;
                if (!move_uploaded_file($file_tmp, $audio_upload_dir . $file_audio_name)) {
                    $error_message = "Gagal memindahkan file audio ke server.";
                    $file_audio_name = '';
                }
            }
        }

        if (empty($error_message)) {
            try {
                if ($id_audio > 0) {
                    // UPDATE KATA
                    if ($file_audio_name !== '') {
                        // Hapus file audio lama dari server
                        $stmt_old = $pdo->prepare("SELECT file_audio FROM tb_audio WHERE id_audio = :id");
                        $stmt_old->execute(['id' => $id_audio]);
                        $old_file = $stmt_old->fetchColumn();
                        if ($old_file && file_exists($audio_upload_dir . $old_file)) {
                            @unlink($audio_upload_dir . $old_file);
                        }

                        $stmt = $pdo->prepare("UPDATE tb_audio SET file_audio = :file, keterangan = :ket WHERE id_audio = :id");
                        $stmt->execute(['file' => $file_audio_name, 'ket' => $keterangan, 'id' => $id_audio]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE tb_audio SET keterangan = :ket WHERE id_audio = :id");
                        $stmt->execute(['ket' => $keterangan, 'id' => $id_audio]);
                    }
                    $success_message = "Kosakata berhasil diperbarui!";
                } else {
                    // INSERT KATA BARU
                    if ($file_audio_name === '') {
                        $error_message = "File audio pelafalan wajib diupload!";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO tb_audio (id_materi, file_audio, keterangan) VALUES (:id_materi, :file, :ket)");
                        $stmt->execute(['id_materi' => $id_materi, 'file' => $file_audio_name, 'ket' => $keterangan]);
                        $success_message = "Kosakata baru berhasil ditambahkan!";
                    }
                }
                if (empty($error_message)) {
                    header("Location: upload_media.php?id_materi=" . $id_materi . "&success=" . urlencode($success_message));
                    exit();
                }
            } catch (PDOException $e) {
                $error_message = "Error database: " . $e->getMessage();
            }
        }
    }
}

// ---------------------------------------------------------
// PROSES HAPUS KATA VOCABULARY (GET)
// ---------------------------------------------------------
if (isset($_GET['delete_word'])) {
    $id_audio = intval($_GET['delete_word']);
    try {
        // Hapus file audio fisik dari server
        $stmt_file = $pdo->prepare("SELECT file_audio FROM tb_audio WHERE id_audio = :id");
        $stmt_file->execute(['id' => $id_audio]);
        $file = $stmt_file->fetchColumn();
        if ($file && file_exists($audio_upload_dir . $file)) {
            @unlink($audio_upload_dir . $file);
        }

        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM tb_audio WHERE id_audio = :id");
        $stmt->execute(['id' => $id_audio]);
        $success_message = "Kosakata berhasil dihapus!";
        header("Location: upload_media.php?id_materi=" . $id_materi . "&success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Error database saat menghapus: " . $e->getMessage();
    }
}

// ---------------------------------------------------------
// PROSES TAMBAH / EDIT VIDEO CONVERSATION (POST)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_video'])) {
    $id_video = isset($_POST['id_video']) ? intval($_POST['id_video']) : 0;
    $keterangan = trim($_POST['keterangan'] ?? '');
    $file_video_name = '';

    // Tangani unggah file video
    if (isset($_FILES['file_video']) && $_FILES['file_video']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_video']['tmp_name'];
        $orig_name = $_FILES['file_video']['name'];
        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['mp4', 'webm', 'ogg'];
        if (!in_array($ext, $allowed_exts)) {
            $error_message = "Hanya file video (mp4, webm, ogg) yang diperbolehkan!";
        } else {
            // Generate nama file acak unik
            $file_video_name = time() . '_' . uniqid() . '.' . $ext;
            if (!move_uploaded_file($file_tmp, $video_upload_dir . $file_video_name)) {
                $error_message = "Gagal memindahkan file video ke server.";
                $file_video_name = '';
            }
        }
    }

    if (empty($error_message)) {
        try {
            if ($id_video > 0) {
                // UPDATE VIDEO
                if ($file_video_name !== '') {
                    // Hapus file video lama dari server
                    $stmt_old = $pdo->prepare("SELECT file_video FROM tb_video WHERE id_video = :id");
                    $stmt_old->execute(['id' => $id_video]);
                    $old_file = $stmt_old->fetchColumn();
                    if ($old_file && file_exists($video_upload_dir . $old_file)) {
                        @unlink($video_upload_dir . $old_file);
                    }

                    $stmt = $pdo->prepare("UPDATE tb_video SET file_video = :file, keterangan = :ket WHERE id_video = :id");
                    $stmt->execute(['file' => $file_video_name, 'ket' => $keterangan, 'id' => $id_video]);
                } else {
                    $stmt = $pdo->prepare("UPDATE tb_video SET keterangan = :ket WHERE id_video = :id");
                    $stmt->execute(['ket' => $keterangan, 'id' => $id_video]);
                }
                $success_message = "Video berhasil diperbarui!";
            } else {
                // INSERT VIDEO BARU
                if ($file_video_name === '') {
                    $error_message = "File video percakapan wajib diupload!";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO tb_video (id_materi, file_video, keterangan) VALUES (:id_materi, :file, :ket)");
                    $stmt->execute(['id_materi' => $id_materi, 'file' => $file_video_name, 'ket' => $keterangan]);
                    $success_message = "Video baru berhasil diupload!";
                }
            }
            if (empty($error_message)) {
                header("Location: upload_media.php?id_materi=" . $id_materi . "&success=" . urlencode($success_message));
                exit();
            }
        } catch (PDOException $e) {
            $error_message = "Error database: " . $e->getMessage();
        }
    }
}

// ---------------------------------------------------------
// PROSES HAPUS VIDEO CONVERSATION (GET)
// ---------------------------------------------------------
if (isset($_GET['delete_video'])) {
    $id_video = intval($_GET['delete_video']);
    try {
        // Hapus file video fisik dari server
        $stmt_file = $pdo->prepare("SELECT file_video FROM tb_video WHERE id_video = :id");
        $stmt_file->execute(['id' => $id_video]);
        $file = $stmt_file->fetchColumn();
        if ($file && file_exists($video_upload_dir . $file)) {
            @unlink($video_upload_dir . $file);
        }

        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM tb_video WHERE id_video = :id");
        $stmt->execute(['id' => $id_video]);
        $success_message = "Video berhasil dihapus!";
        header("Location: upload_media.php?id_materi=" . $id_materi . "&success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Error database saat menghapus: " . $e->getMessage();
    }
}

// Ambil pesan sukses redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// ---------------------------------------------------------
// BACA DATA KATA & MEDIA UNTUK UNIT AKTIF
// ---------------------------------------------------------
$word_edit_data = null;
$video_edit_data = null;
$words = [];
$videos = [];

if ($unit_data) {
    if ($unit_data['kategori'] === 'Vocabulary') {
        // Ambil list kata di unit ini
        try {
            $stmt_words = $pdo->prepare("SELECT * FROM tb_audio WHERE id_materi = :id ORDER BY id_audio ASC");
            $stmt_words->execute(['id' => $id_materi]);
            $words = $stmt_words->fetchAll();
        } catch (PDOException $e) {
            $words = [];
        }

        // Jika sedang mengedit kata tertentu
        if (isset($_GET['edit_word_id'])) {
            $stmt_w = $pdo->prepare("SELECT * FROM tb_audio WHERE id_audio = :id");
            $stmt_w->execute(['id' => intval($_GET['edit_word_id'])]);
            $word_edit_data = $stmt_w->fetch();
        }
    } elseif ($unit_data['kategori'] === 'Conversation') {
        // Ambil list video di unit ini
        try {
            $stmt_videos = $pdo->prepare("SELECT * FROM tb_video WHERE id_materi = :id ORDER BY id_video ASC");
            $stmt_videos->execute(['id' => $id_materi]);
            $videos = $stmt_videos->fetchAll();
        } catch (PDOException $e) {
            $videos = [];
        }

        // Jika sedang mengedit video tertentu
        if (isset($_GET['edit_video_id'])) {
            $stmt_v = $pdo->prepare("SELECT * FROM tb_video WHERE id_video = :id");
            $stmt_v->execute(['id' => intval($_GET['edit_video_id'])]);
            $video_edit_data = $stmt_v->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Media - Nommensen Admin</title>
    
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
                    <li><a href="kelola_materi.php">Kelola Materi</a></li>
                    <li><a href="upload_media.php" class="active">Upload Media</a></li>
                    <li><a href="kelola_soal.php">Kelola Soal</a></li>
                    <li><a href="laporan_nilai.php">Laporan Nilai</a></li>
                    <li><a href="pengaturan.php">Pengaturan</a></li>
                    <li><a href="kelola_siswa.php">Kelola Data Siswa</a></li>
                </ul>
            </div>
            <!-- Tombol Keluar Sesi -->
            <a href="logout.php" class="btn-logout-sidebar">Keluar (Logout)</a>
        </aside>

        <!-- Area Konten Utama Kanan -->
        <main class="main-content">
            <!-- Tampilkan Alert Pesan -->
            <?php if ($error_message !== ''): ?>
                <div class="alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <?php if ($success_message !== ''): ?>
                <div class="alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <!-- ===================================================================
                 KONDISI 1: UNIT MATERI DIPILIH (id_materi > 0)
                 =================================================================== -->
            <?php if ($unit_data): ?>
                <div style="margin-bottom: 1.5rem;">
                    <a href="upload_media.php" class="btn-sm btn-play" style="background-color: #4b5563; text-decoration: none;">
                        &larr; Kembali ke Pilihan Materi
                    </a>
                </div>

                <!-- A. KELOLA AUDIO (UNTUK MATERI VOCABULARY) -->
                <?php if ($unit_data['kategori'] === 'Vocabulary'): ?>
                    <div class="form-card">
                        <div class="form-title">
                            <?= $word_edit_data ? 'Edit Kata & File Audio' : 'Upload Media Audio Baru' ?> 
                            untuk <strong><?= htmlspecialchars($unit_data['judul_materi']) ?></strong>
                        </div>
                        
                        <form action="upload_media.php?id_materi=<?= $unit_data['id_materi'] ?>" method="POST" enctype="multipart/form-data">
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
                                <?= $word_edit_data ? 'Update Kata & Audio' : 'Simpan & Upload Audio' ?>
                            </button>
                            <?php if ($word_edit_data): ?>
                                <a href="upload_media.php?id_materi=<?= $unit_data['id_materi'] ?>" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; margin-left: 0.5rem;">Batal</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <h3>Daftar Media Audio (Kosakata) di Unit Ini</h3>
                    <div class="table-container">
                        <table class="table-materi">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Bahasa Inggris</th>
                                    <th style="width: 30%;">Arti Indonesia</th>
                                    <th style="width: 25%;">Audio</th>
                                    <th style="text-align: center; width: 15%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($words)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #6b7280; padding: 2rem;">
                                            Belum ada file audio terunggah untuk unit ini. Silakan unggah lewat form di atas.
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
                                                <a href="upload_media.php?id_materi=<?= $unit_data['id_materi'] ?>&edit_word_id=<?= $word['id_audio'] ?>" class="btn-sm btn-edit">Edit</a>
                                                <a href="upload_media.php?id_materi=<?= $unit_data['id_materi'] ?>&delete_word=<?= $word['id_audio'] ?>" class="btn-sm btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus file media beserta data kata ini?')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <!-- B. KELOLA VIDEO (UNTUK MATERI CONVERSATION) -->
                <?php elseif ($unit_data['kategori'] === 'Conversation'): ?>
                    <div class="form-card">
                        <div class="form-title">
                            <?= $video_edit_data ? 'Edit Keterangan / Ganti Video' : 'Upload Media Video Baru' ?> 
                            untuk <strong><?= htmlspecialchars($unit_data['judul_materi']) ?></strong>
                        </div>
                        
                        <form action="upload_media.php?id_materi=<?= $unit_data['id_materi'] ?>" method="POST" enctype="multipart/form-data">
                            <?php if ($video_edit_data): ?>
                                <input type="hidden" name="id_video" value="<?= $video_edit_data['id_video'] ?>">
                                <?php $keterangan_val = $video_edit_data['keterangan'] ?? ''; ?>
                            <?php else: ?>
                                <?php $keterangan_val = ''; ?>
                            <?php endif; ?>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Judul / Keterangan Video:</label>
                                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Dialog Percakapan Greeting" required value="<?= htmlspecialchars($keterangan_val) ?>">
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                        Pilih File Video (mp4, webm):
                                        <?php if ($video_edit_data): ?>
                                            <span style="font-size: 0.8rem; color: #d97706;">(Biarkan kosong jika tidak diganti)</span>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="file_video" class="form-control" <?= $video_edit_data ? '' : 'required' ?> accept="video/*">
                                </div>
                            </div>

                            <button type="submit" name="save_video" class="btn-sm btn-success">
                                <?= $video_edit_data ? 'Update Video' : 'Simpan & Upload Video' ?>
                            </button>
                            <?php if ($video_edit_data): ?>
                                <a href="upload_media.php?id_materi=<?= $unit_data['id_materi'] ?>" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; margin-left: 0.5rem;">Batal</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <h3>Daftar Media Video (Percakapan) di Unit Ini</h3>
                    <div class="table-container">
                        <table class="table-materi">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Judul / Keterangan</th>
                                    <th style="width: 45%;">Preview Video</th>
                                    <th style="text-align: center; width: 20%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($videos)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #6b7280; padding: 2rem;">
                                            Belum ada file video terunggah untuk unit ini. Silakan unggah lewat form di atas.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($videos as $video): ?>
                                        <tr>
                                            <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($video['keterangan']) ?></td>
                                            <td>
                                                <video src="../assets/video/<?= htmlspecialchars($video['file_video']) ?>" controls style="max-width: 250px; border-radius: 4px; background: #000000;"></video>
                                            </td>
                                            <td style="text-align: center;">
                                                <a href="upload_media.php?id_materi=<?= $unit_data['id_materi'] ?>&edit_video_id=<?= $video['id_video'] ?>" class="btn-sm btn-edit">Edit</a>
                                                <a href="upload_media.php?id_materi=<?= $unit_data['id_materi'] ?>&delete_video=<?= $video['id_video'] ?>" class="btn-sm btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus file video beserta datanya?')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <!-- C. KELOLA GRAMMAR (TIDAK BUTUH MEDIA) -->
                <?php elseif ($unit_data['kategori'] === 'Grammar'): ?>
                    <div style="background-color: #fef3c7; border: 1px solid #f59e0b; color: #78350f; padding: 2rem; border-radius: 8px; text-align: center;">
                        <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; margin-bottom: 0.5rem;">Materi Grammar</h3>
                        <p style="font-size: 0.95rem;">Pembelajaran tata bahasa (Grammar) disajikan dalam bentuk materi teks terstruktur dan tidak membutuhkan unggah media audio maupun video tambahan.</p>
                    </div>
                <?php endif; ?>

            <!-- ===================================================================
                 KONDISI 2: TAMPILAN UTAMA PILIH MATERI (DEFAULT)
                 =================================================================== -->
            <?php else: ?>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">
                    Upload & Kelola Media
                </h2>
                <p style="color: #6b7280; font-size: 0.95rem; margin-bottom: 2rem;">Silakan pilih salah satu unit materi pembelajaran di bawah ini untuk mulai mengunggah/mengelola media pembelajaran.</p>

                <?php
                // Fetch unit materi yang butuh media (Vocabulary dan Conversation)
                try {
                    $stmt_voc = $pdo->query("SELECT * FROM tb_materi WHERE kategori = 'Vocabulary' ORDER BY id_materi ASC");
                    $voc_units = $stmt_voc->fetchAll();
                    
                    $stmt_conv = $pdo->query("SELECT * FROM tb_materi WHERE kategori = 'Conversation' ORDER BY id_materi ASC");
                    $conv_units = $stmt_conv->fetchAll();
                } catch (PDOException $e) {
                    $voc_units = [];
                    $conv_units = [];
                }
                ?>

                <!-- 1. Kategori Vocabulary (Audio) -->
                <div style="margin-bottom: 2.5rem;">
                    <div style="background-color: var(--accent-blue); color: #ffffff; padding: 0.65rem 1.25rem; border-radius: 6px 6px 0 0; font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem;">
                        Materi Kategori: Vocabulary (Unggah File Audio Pelafalan)
                    </div>
                    <div class="table-container" style="margin-top: 0; border-radius: 0 0 8px 8px; border-top: none;">
                        <table class="table-materi">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Nama Unit</th>
                                    <th style="width: 50%;">Deskripsi</th>
                                    <th style="text-align: center; width: 20%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($voc_units)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #6b7280; padding: 1.5rem;">Belum ada unit materi vocabulary. Silakan buat unit terlebih dahulu di menu Kelola Materi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($voc_units as $unit): ?>
                                        <tr>
                                            <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($unit['judul_materi']) ?></td>
                                            <td style="font-size: 0.9rem; color: #4b5563;"><?= htmlspecialchars($unit['konten_teks'] ?? '-') ?></td>
                                            <td style="text-align: center;">
                                                <a href="upload_media.php?id_materi=<?= $unit['id_materi'] ?>" class="btn-sm btn-play" style="background-color: #2563eb; color: white;">
                                                    Kelola Audio & Kata
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Kategori Conversation (Video) -->
                <div style="margin-bottom: 1.5rem;">
                    <div style="background-color: #1e293b; color: #ffffff; padding: 0.65rem 1.25rem; border-radius: 6px 6px 0 0; font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem;">
                        Materi Kategori: Conversation (Unggah File Video Percakapan)
                    </div>
                    <div class="table-container" style="margin-top: 0; border-radius: 0 0 8px 8px; border-top: none;">
                        <table class="table-materi">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Nama Unit</th>
                                    <th style="width: 50%;">Deskripsi</th>
                                    <th style="text-align: center; width: 20%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($conv_units)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #6b7280; padding: 1.5rem;">Belum ada unit materi conversation. Silakan buat unit terlebih dahulu di menu Kelola Materi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($conv_units as $unit): ?>
                                        <tr>
                                            <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($unit['judul_materi']) ?></td>
                                            <td style="font-size: 0.9rem; color: #4b5563;"><?= htmlspecialchars($unit['konten_teks'] ?? '-') ?></td>
                                            <td style="text-align: center;">
                                                <a href="upload_media.php?id_materi=<?= $unit['id_materi'] ?>" class="btn-sm btn-play" style="background-color: #2563eb; color: white;">
                                                    Kelola Video (Upload)
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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

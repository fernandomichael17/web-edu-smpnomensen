<?php
/**
 * File: admin/kelola_soal.php
 * Deskripsi: Halaman Pengelolaan Kuis dan Butir Soal Kuis (CRUD).
 *            Memungkinkan guru menambah/mengubah/menghapus kuis, mengaitkannya ke materi,
 *            serta mengelola (tambah/edit/hapus) soal pilihan ganda di tiap kuis.
 */

// Memroteksi halaman ini agar hanya bisa diakses oleh guru yang sudah login
require_once '../includes/auth_admin.php';

// Memanggil koneksi database
require_once '../config.php';

$success_message = '';
$error_message = '';

// Baca parameter kuis & tab aktif
$id_kuis_aktif = isset($_GET['id_kuis']) ? intval($_GET['id_kuis']) : 0;
$tab_aktif = isset($_GET['tab']) ? $_GET['tab'] : 'kuis';

// ===================================================================
// 1. HANDLER CRUD KUIS (POST & GET)
// ===================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_kuis'])) {
    $id_kuis = isset($_POST['id_kuis']) ? intval($_POST['id_kuis']) : 0;
    $judul_kuis = trim($_POST['judul_kuis'] ?? '');
    $id_materi = isset($_POST['id_materi']) && $_POST['id_materi'] !== '' ? intval($_POST['id_materi']) : null;
    $waktu_pengerjaan = isset($_POST['waktu_pengerjaan']) ? intval($_POST['waktu_pengerjaan']) : 10;
    $nilai_lulus = isset($_POST['nilai_lulus']) ? intval($_POST['nilai_lulus']) : 70;
    $id_guru = $_SESSION['admin_id'];

    if (empty($judul_kuis)) {
        $error_message = "Judul kuis tidak boleh kosong!";
    } else {
        // Tentukan kategori_materi secara otomatis berdasarkan materi terkait
        $kategori_materi = 'Campuran';
        if ($id_materi !== null && $id_materi > 0) {
            try {
                $stmt_m = $pdo->prepare("SELECT kategori FROM tb_materi WHERE id_materi = :id");
                $stmt_m->execute(['id' => $id_materi]);
                $kat = $stmt_m->fetchColumn();
                if ($kat) {
                    $kategori_materi = $kat;
                }
            } catch (PDOException $e) {
                $kategori_materi = 'Campuran';
            }
        }

        try {
            if ($id_kuis > 0) {
                // UPDATE KUIS
                $stmt_update = $pdo->prepare("
                    UPDATE tb_kuis 
                    SET judul_kuis = :judul, kategori_materi = :kat, waktu_pengerjaan = :waktu, id_materi = :id_materi, nilai_lulus = :nilai_lulus
                    WHERE id_kuis = :id
                ");
                $stmt_update->execute([
                    'judul' => $judul_kuis,
                    'kat' => $kategori_materi,
                    'waktu' => $waktu_pengerjaan,
                    'id_materi' => $id_materi,
                    'nilai_lulus' => $nilai_lulus,
                    'id' => $id_kuis
                ]);
                $success_message = "Data kuis berhasil diperbarui!";
            } else {
                // INSERT KUIS BARU
                $stmt_insert = $pdo->prepare("
                    INSERT INTO tb_kuis (judul_kuis, kategori_materi, waktu_pengerjaan, id_materi, nilai_lulus, id_guru) 
                    VALUES (:judul, :kat, :waktu, :id_materi, :nilai_lulus, :id_guru)
                ");
                $stmt_insert->execute([
                    'judul' => $judul_kuis,
                    'kat' => $kategori_materi,
                    'waktu' => $waktu_pengerjaan,
                    'id_materi' => $id_materi,
                    'nilai_lulus' => $nilai_lulus,
                    'id_guru' => $id_guru
                ]);
                $success_message = "Kuis baru berhasil dibuat!";
            }
            header("Location: kelola_soal.php?tab=kuis&success=" . urlencode($success_message));
            exit();
        } catch (PDOException $e) {
            $error_message = "Gagal memproses data kuis: " . $e->getMessage();
        }
    }
}

if (isset($_GET['delete_kuis'])) {
    $delete_id = intval($_GET['delete_kuis']);
    try {
        $stmt_del = $pdo->prepare("DELETE FROM tb_kuis WHERE id_kuis = :id");
        $stmt_del->execute(['id' => $delete_id]);
        $success_message = "Kuis berhasil dihapus!";
        header("Location: kelola_soal.php?tab=kuis&success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Gagal menghapus kuis: " . $e->getMessage();
    }
}

// ===================================================================
// 2. HANDLER CRUD SOAL (POST & GET)
// ===================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_soal'])) {
    $id_soal = isset($_POST['id_soal']) ? intval($_POST['id_soal']) : 0;
    $id_kuis = intval($_POST['id_kuis']);
    $pertanyaan = trim($_POST['pertanyaan'] ?? '');
    $opsi_a = trim($_POST['opsi_a'] ?? '');
    $opsi_b = trim($_POST['opsi_b'] ?? '');
    $opsi_c = trim($_POST['opsi_c'] ?? '');
    $opsi_d = trim($_POST['opsi_d'] ?? '');
    $jawaban_benar = trim($_POST['jawaban_benar'] ?? '');

    if (empty($pertanyaan) || empty($opsi_a) || empty($opsi_b) || empty($opsi_c) || empty($opsi_d) || empty($jawaban_benar)) {
        $error_message = "Semua field pertanyaan, opsi A-D, dan kunci jawaban wajib diisi!";
    } else {
        try {
            if ($id_soal > 0) {
                // UPDATE SOAL
                $stmt_update = $pdo->prepare("
                    UPDATE tb_soal 
                    SET pertanyaan = :p, opsi_a = :a, opsi_b = :b, opsi_c = :c, opsi_d = :d, jawaban_benar = :j 
                    WHERE id_soal = :id
                ");
                $stmt_update->execute([
                    'p' => $pertanyaan,
                    'a' => $opsi_a,
                    'b' => $opsi_b,
                    'c' => $opsi_c,
                    'd' => $opsi_d,
                    'j' => $jawaban_benar,
                    'id' => $id_soal
                ]);
                $success_message = "Soal berhasil diperbarui!";
            } else {
                // INSERT SOAL BARU
                $stmt_insert = $pdo->prepare("
                    INSERT INTO tb_soal (id_kuis, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban_benar) 
                    VALUES (:id_kuis, :p, :a, :b, :c, :d, :j)
                ");
                $stmt_insert->execute([
                    'id_kuis' => $id_kuis,
                    'p' => $pertanyaan,
                    'a' => $opsi_a,
                    'b' => $opsi_b,
                    'c' => $opsi_c,
                    'd' => $opsi_d,
                    'j' => $jawaban_benar
                ]);
                $success_message = "Soal baru berhasil ditambahkan ke kuis!";
            }
            header("Location: kelola_soal.php?tab=soal&id_kuis=" . $id_kuis . "&success=" . urlencode($success_message));
            exit();
        } catch (PDOException $e) {
            $error_message = "Gagal memproses data soal: " . $e->getMessage();
        }
    }
}

if (isset($_GET['delete_soal'])) {
    $delete_id = intval($_GET['delete_soal']);
    $id_kuis = intval($_GET['id_kuis']);
    try {
        $stmt_del = $pdo->prepare("DELETE FROM tb_soal WHERE id_soal = :id");
        $stmt_del->execute(['id' => $delete_id]);
        $success_message = "Soal berhasil dihapus dari kuis!";
        header("Location: kelola_soal.php?tab=soal&id_kuis=" . $id_kuis . "&success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Gagal menghapus soal: " . $e->getMessage();
    }
}

// Menangkap parameter sukses redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// ===================================================================
// 3. READ DATA UNTUK EDIT & DROPDOWN FILTER
// ===================================================================
$kuis_edit_data = null;
if ($tab_aktif === 'kuis' && isset($_GET['edit_kuis_id'])) {
    try {
        $stmt_k = $pdo->prepare("SELECT * FROM tb_kuis WHERE id_kuis = :id");
        $stmt_k->execute(['id' => intval($_GET['edit_kuis_id'])]);
        $kuis_edit_data = $stmt_k->fetch();
    } catch (PDOException $e) {
        $error_message = "Error loading kuis edit data: " . $e->getMessage();
    }
}

$soal_edit_data = null;
if ($tab_aktif === 'soal' && isset($_GET['edit_soal_id'])) {
    try {
        $stmt_s = $pdo->prepare("SELECT * FROM tb_soal WHERE id_soal = :id");
        $stmt_s->execute(['id' => intval($_GET['edit_soal_id'])]);
        $soal_edit_data = $stmt_s->fetch();
    } catch (PDOException $e) {
        $error_message = "Error loading soal edit data: " . $e->getMessage();
    }
}

// Fetch semua kuis
try {
    $quizzes = $pdo->query("SELECT * FROM tb_kuis ORDER BY id_kuis ASC")->fetchAll();
} catch (PDOException $e) {
    $quizzes = [];
}

// Fetch semua materi (untuk dropdown kuis terkait)
try {
    $materi_list = $pdo->query("SELECT id_materi, kategori, judul_materi FROM tb_materi ORDER BY kategori ASC, id_materi ASC")->fetchAll();
} catch (PDOException $e) {
    $materi_list = [];
}

// Fetch soal untuk kuis aktif
$questions = [];
$active_quiz_title = '';
if ($id_kuis_aktif > 0) {
    try {
        $stmt_title = $pdo->prepare("SELECT judul_kuis FROM tb_kuis WHERE id_kuis = :id");
        $stmt_title->execute(['id' => $id_kuis_aktif]);
        $active_quiz_title = $stmt_title->fetchColumn();

        $stmt_qs = $pdo->prepare("SELECT * FROM tb_soal WHERE id_kuis = :id ORDER BY id_soal ASC");
        $stmt_qs->execute(['id' => $id_kuis_aktif]);
        $questions = $stmt_qs->fetchAll();
    } catch (PDOException $e) {
        $questions = [];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kuis & Soal - Nommensen Admin</title>
    
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

        /* Sidebar Navigasi Abu-Abu */
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

    <div class="admin-layout">
        <!-- Sidebar Navigasi Kiri (PERSIS 7 Menu Sesuai Tugas) -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h3>Admin Nommensen</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="kelola_materi.php">Kelola Materi</a></li>
                    <li><a href="upload_media.php">Upload Media</a></li>
                    <li><a href="kelola_soal.php" class="active">Kelola Soal</a></li>
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
            
            <!-- Tab Menu di Bagian Atas Halaman -->
            <nav class="tab-nav" style="margin-bottom: 2rem;">
                <a href="kelola_soal.php?tab=kuis" class="tab-link <?= $tab_aktif === 'kuis' ? 'active' : '' ?>">1. Daftar Sesi Kuis</a>
                <?php if ($id_kuis_aktif > 0): ?>
                    <a href="kelola_soal.php?tab=soal&id_kuis=<?= $id_kuis_aktif ?>" class="tab-link <?= $tab_aktif === 'soal' ? 'active' : '' ?>">
                        2. Butir Soal (<?= htmlspecialchars($active_quiz_title) ?>)
                    </a>
                <?php else: ?>
                    <span class="tab-link" style="color: #94a3b8; cursor: not-allowed; opacity: 0.6;">2. Butir Soal (Pilih Kuis Dahulu)</span>
                <?php endif; ?>
            </nav>

            <!-- Pesan Umpan Balik Sukses/Error -->
            <?php if (!empty($error_message)): ?>
                <div class="alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <!-- ===================================================================
                 TAB 1: KELOLA KUIS (SESI KUIS)
                 =================================================================== -->
            <?php if ($tab_aktif === 'kuis'): ?>
                
                <!-- Form Buat/Edit Kuis -->
                <div class="form-card" style="border: 2px solid #374151; border-radius: 6px; padding: 1.5rem; margin-bottom: 2.5rem; background: #ffffff;">
                    <div class="form-title" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.15rem; color: var(--accent-blue); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                        <?= $kuis_edit_data ? 'Edit Detail Sesi Kuis' : 'Buat Sesi Kuis Baru' ?>
                    </div>
                    
                    <form action="kelola_soal.php?tab=kuis" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <?php if ($kuis_edit_data): ?>
                            <input type="hidden" name="id_kuis" value="<?= $kuis_edit_data['id_kuis'] ?>">
                        <?php endif; ?>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                            <!-- Judul Kuis -->
                            <div style="display: flex; flex-direction: column; flex-grow: 2;">
                                <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Judul Sesi Kuis:</label>
                                <input type="text" name="judul_kuis" class="form-control" placeholder="Contoh: Kuis 1: Simple Present Tense" required value="<?= htmlspecialchars($kuis_edit_data['judul_kuis'] ?? '') ?>">
                            </div>

                            <!-- Keterkaitan Materi Pembelajaran -->
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Kaitkan ke Unit Materi (Opsional):</label>
                                <select name="id_materi" class="form-control">
                                    <option value="">-- Tidak Terikat / Campuran --</option>
                                    <?php foreach ($materi_list as $m): ?>
                                        <option value="<?= $m['id_materi'] ?>" <?= isset($kuis_edit_data) && intval($kuis_edit_data['id_materi']) === intval($m['id_materi']) ? 'selected' : '' ?>>
                                            [<?= $m['kategori'] ?>] <?= htmlspecialchars($m['judul_materi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Durasi Waktu -->
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Durasi Waktu (Menit):</label>
                                <input type="number" name="waktu_pengerjaan" class="form-control" min="1" required value="<?= htmlspecialchars($kuis_edit_data['waktu_pengerjaan'] ?? 10) ?>">
                            </div>

                            <!-- KKM / Nilai Lulus -->
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Batas Nilai Lulus (KKM):</label>
                                <input type="number" name="nilai_lulus" class="form-control" min="0" max="100" required value="<?= htmlspecialchars($kuis_edit_data['nilai_lulus'] ?? 70) ?>">
                            </div>
                        </div>

                        <div>
                            <button type="submit" name="save_kuis" class="btn-sm btn-success" style="padding: 0.75rem 1.5rem; font-weight: 700; border: none; cursor: pointer;">
                                <?= $kuis_edit_data ? 'Update Sesi Kuis' : 'Buat Kuis Baru' ?>
                            </button>
                            <?php if ($kuis_edit_data): ?>
                                <a href="kelola_soal.php?tab=kuis" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; padding: 0.75rem 1.5rem; font-weight: 700; text-align: center; border-radius: 4px; border: 2px solid #4b5563; margin-left: 0.5rem;">
                                    Batal
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Tabel Daftar Sesi Kuis -->
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: #1f2937; margin-bottom: 1rem;">Daftar Sesi Kuis Aktif</h3>
                <div class="table-container" style="border: 2px solid #374151; border-radius: 6px;">
                    <table class="table-materi">
                        <thead>
                            <tr>
                                <th style="width: 30%; font-weight: 700;">Nama Sesi Kuis</th>
                                <th style="width: 15%; font-weight: 700; text-align: center;">Kategori</th>
                                <th style="width: 15%; font-weight: 700; text-align: center;">Durasi</th>
                                <th style="width: 10%; font-weight: 700; text-align: center;">KKM</th>
                                <th style="width: 10%; font-weight: 700; text-align: center;">Jumlah Soal</th>
                                <th style="width: 20%; font-weight: 700; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($quizzes)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #6b7280; padding: 2.5rem;">
                                        Belum ada sesi kuis yang terdaftar. Buat sesi baru lewat form di atas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($quizzes as $quiz): ?>
                                    <?php
                                    // HITUNG jumlah soal asli secara dinamis dari tb_soal (Bukan dari jumlah_soal)
                                    try {
                                        $stmt_q_cnt = $pdo->prepare("SELECT COUNT(*) FROM tb_soal WHERE id_kuis = :id");
                                        $stmt_q_cnt->execute(['id' => $quiz['id_kuis']]);
                                        $jumlah_soal = $stmt_q_cnt->fetchColumn();
                                    } catch (PDOException $e) {
                                        $jumlah_soal = 0;
                                    }
                                    ?>
                                    <tr>
                                        <td style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($quiz['judul_kuis']) ?></td>
                                        <td style="text-align: center;">
                                            <span style="font-size: 0.75rem; background-color: #eff6ff; color: var(--accent-blue); padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700;">
                                                <?= htmlspecialchars($quiz['kategori_materi']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; font-weight: 600;"><?= htmlspecialchars($quiz['waktu_pengerjaan']) ?> Menit</td>
                                        <td style="text-align: center; font-weight: 700; color: #b45309;"><?= htmlspecialchars($quiz['nilai_lulus']) ?></td>
                                        <td style="text-align: center; font-weight: 800; color: var(--accent-blue);"><?= $jumlah_soal ?> Butir</td>
                                        <td style="text-align: center; display: flex; gap: 0.25rem; justify-content: center;">
                                            <a href="kelola_soal.php?tab=soal&id_kuis=<?= $quiz['id_kuis'] ?>" class="btn-sm btn-play" style="background-color: #2563eb; color: white;">Soal</a>
                                            <a href="kelola_soal.php?tab=kuis&edit_kuis_id=<?= $quiz['id_kuis'] ?>" class="btn-sm btn-edit">Edit</a>
                                            <a href="kelola_soal.php?tab=kuis&delete_kuis=<?= $quiz['id_kuis'] ?>" class="btn-sm btn-delete" onclick="return confirm('Menghapus kuis akan menghapus seluruh butir soal di dalamnya! Apakah Anda yakin?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ===================================================================
                 TAB 2: KELOLA SOAL (BUTIR SOAL PER KUIS)
                 =================================================================== -->
            <?php elseif ($tab_aktif === 'soal' && $id_kuis_aktif > 0): ?>

                <!-- Form Buat/Edit Butir Soal -->
                <div class="form-card" style="border: 2px solid #374151; border-radius: 6px; padding: 1.5rem; margin-bottom: 2.5rem; background: #ffffff;">
                    <div class="form-title" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.15rem; color: var(--accent-blue); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                        <?= $soal_edit_data ? 'Edit Soal Pilihan Ganda' : 'Tambah Soal Pilihan Ganda Baru' ?> 
                        ke <strong><?= htmlspecialchars($active_quiz_title) ?></strong>
                    </div>

                    <form action="kelola_soal.php?tab=soal&id_kuis=<?= $id_kuis_aktif ?>" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <input type="hidden" name="id_kuis" value="<?= $id_kuis_aktif ?>">
                        <?php if ($soal_edit_data): ?>
                            <input type="hidden" name="id_soal" value="<?= $soal_edit_data['id_soal'] ?>">
                        <?php endif; ?>

                        <!-- Text pertanyaan -->
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Pertanyaan Soal:</label>
                            <textarea name="pertanyaan" class="form-control" rows="3" placeholder="Tuliskan pertanyaan soal pilihan ganda di sini..." required><?= htmlspecialchars($soal_edit_data['pertanyaan'] ?? '') ?></textarea>
                        </div>

                        <!-- Opsi A - D -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569; color: var(--accent-blue);">Pilihan A:</label>
                                <input type="text" name="opsi_a" class="form-control" placeholder="Isi opsi A..." required value="<?= htmlspecialchars($soal_edit_data['opsi_a'] ?? '') ?>">
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569; color: var(--accent-blue);">Pilihan B:</label>
                                <input type="text" name="opsi_b" class="form-control" placeholder="Isi opsi B..." required value="<?= htmlspecialchars($soal_edit_data['opsi_b'] ?? '') ?>">
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569; color: var(--accent-blue);">Pilihan C:</label>
                                <input type="text" name="opsi_c" class="form-control" placeholder="Isi opsi C..." required value="<?= htmlspecialchars($soal_edit_data['opsi_c'] ?? '') ?>">
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569; color: var(--accent-blue);">Pilihan D:</label>
                                <input type="text" name="opsi_d" class="form-control" placeholder="Isi opsi D..." required value="<?= htmlspecialchars($soal_edit_data['opsi_d'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Kunci Jawaban Benar -->
                        <div style="display: flex; flex-direction: column; max-width: 250px;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Kunci Jawaban Benar:</label>
                            <select name="jawaban_benar" class="form-control" required>
                                <option value="">-- Pilih Jawaban --</option>
                                <option value="A" <?= isset($soal_edit_data) && $soal_edit_data['jawaban_benar'] === 'A' ? 'selected' : '' ?>>Opsi A</option>
                                <option value="B" <?= isset($soal_edit_data) && $soal_edit_data['jawaban_benar'] === 'B' ? 'selected' : '' ?>>Opsi B</option>
                                <option value="C" <?= isset($soal_edit_data) && $soal_edit_data['jawaban_benar'] === 'C' ? 'selected' : '' ?>>Opsi C</option>
                                <option value="D" <?= isset($soal_edit_data) && $soal_edit_data['jawaban_benar'] === 'D' ? 'selected' : '' ?>>Opsi D</option>
                            </select>
                        </div>

                        <div>
                            <button type="submit" name="save_soal" class="btn-sm btn-success" style="padding: 0.75rem 1.5rem; font-weight: 700; border: none; cursor: pointer;">
                                <?= $soal_edit_data ? 'Update Soal' : 'Tambahkan Soal Baru' ?>
                            </button>
                            <a href="kelola_soal.php?tab=soal&id_kuis=<?= $id_kuis_aktif ?>" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; padding: 0.75rem 1.5rem; font-weight: 700; text-align: center; border-radius: 4px; border: 2px solid #4b5563; margin-left: 0.5rem;">
                                Batal / Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tabel Daftar Soal -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: #1f2937;">Daftar Butir Soal Terdaftar</h3>
                    <span style="background-color: var(--accent-blue); color: white; padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.85rem; font-weight: 700;">
                        Total: <?= count($questions) ?> Soal PG
                    </span>
                </div>

                <div class="table-container" style="border: 2px solid #374151; border-radius: 6px;">
                    <table class="table-materi">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center; font-weight: 700;">No</th>
                                <th style="width: 40%; font-weight: 700;">Pertanyaan</th>
                                <th style="width: 35%; font-weight: 700;">Pilihan Jawaban</th>
                                <th style="width: 10%; text-align: center; font-weight: 700;">Kunci</th>
                                <th style="width: 10%; text-align: center; font-weight: 700;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($questions)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #6b7280; padding: 2.5rem;">
                                        Kuis ini belum memiliki butir soal. Silakan tambahkan soal lewat form di atas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($questions as $idx => $q): ?>
                                    <tr>
                                        <td style="text-align: center; font-weight: 700;"><?= $idx + 1 ?></td>
                                        <td style="font-weight: 500; color: #1e293b; line-height: 1.5;"><?= htmlspecialchars($q['pertanyaan']) ?></td>
                                        <td style="font-size: 0.85rem; color: #475569; line-height: 1.4;">
                                            <strong>A:</strong> <?= htmlspecialchars($q['opsi_a']) ?><br>
                                            <strong>B:</strong> <?= htmlspecialchars($q['opsi_b']) ?><br>
                                            <strong>C:</strong> <?= htmlspecialchars($q['opsi_c']) ?><br>
                                            <strong>D:</strong> <?= htmlspecialchars($q['opsi_d']) ?>
                                        </td>
                                        <td style="text-align: center; font-weight: 800; font-size: 1.05rem; color: #16a34a;">
                                            <span style="background-color: #d1fae5; padding: 0.25rem 0.6rem; border-radius: 4px; border: 1px solid #10b981;">
                                                <?= htmlspecialchars($q['jawaban_benar']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="kelola_soal.php?tab=soal&id_kuis=<?= $id_kuis_aktif ?>&edit_soal_id=<?= $q['id_soal'] ?>" class="btn-sm btn-edit">Edit</a>
                                            <a href="kelola_soal.php?tab=soal&id_kuis=<?= $id_kuis_aktif ?>&delete_soal=<?= $q['id_soal'] ?>" class="btn-sm btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus soal ini dari kuis?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </main>
    </div>

    <!-- Footer Bawah (Sesuai Storyboard) -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

</body>
</html>

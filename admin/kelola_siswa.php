<?php
/**
 * File: admin/kelola_siswa.php
 * Deskripsi: Halaman Pengelolaan Data Akun Siswa (CRUD).
 *            Memungkinkan guru/admin menambah, mengubah (termasuk mengganti password dengan hash),
 *            dan menghapus data akun siswa yang dapat mengakses aplikasi.
 */

// Memroteksi halaman ini agar hanya bisa diakses oleh guru yang sudah login
require_once '../includes/auth_admin.php';

// Memanggil koneksi database
require_once '../config.php';

$success_message = '';
$error_message = '';

// 1. PROSES TAMBAH / UPDATE AKUN SISWA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_siswa'])) {
    $id_siswa = isset($_POST['id_siswa']) ? intval($_POST['id_siswa']) : 0;
    $nama_siswa = trim($_POST['nama_siswa'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $nis = trim($_POST['nis'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nama_siswa) || empty($kelas) || empty($nis)) {
        $error_message = "Nama, Kelas, dan NIS (Username) wajib diisi!";
    } else {
        try {
            // Cek keunikan NIS (kecuali jika mengedit dirinya sendiri)
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM tb_siswa WHERE nis = :nis AND id_siswa != :id");
            $stmt_check->execute(['nis' => $nis, 'id' => $id_siswa]);
            $is_exists = $stmt_check->fetchColumn();

            if ($is_exists > 0) {
                $error_message = "NIS (Username) '$nis' sudah terdaftar untuk siswa lain!";
            } else {
                if ($id_siswa > 0) {
                    // PROSES EDIT SISWA
                    if (!empty($password)) {
                        // Jika mengganti password, hash password baru
                        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt_update = $pdo->prepare("
                            UPDATE tb_siswa 
                            SET nama_siswa = :nama, kelas = :kelas, nis = :nis, password = :pass 
                            WHERE id_siswa = :id
                        ");
                        $stmt_update->execute([
                            'nama' => $nama_siswa,
                            'kelas' => $kelas,
                            'nis' => $nis,
                            'pass' => $password_hashed,
                            'id' => $id_siswa
                        ]);
                    } else {
                        // Jika password dibiarkan kosong, jangan ubah password lama
                        $stmt_update = $pdo->prepare("
                            UPDATE tb_siswa 
                            SET nama_siswa = :nama, kelas = :kelas, nis = :nis 
                            WHERE id_siswa = :id
                        ");
                        $stmt_update->execute([
                            'nama' => $nama_siswa,
                            'kelas' => $kelas,
                            'nis' => $nis,
                            'id' => $id_siswa
                        ]);
                    }
                    $success_message = "Data akun siswa berhasil diperbarui!";
                } else {
                    // PROSES TAMBAH SISWA BARU
                    if (empty($password)) {
                        $error_message = "Password wajib diisi untuk akun siswa baru!";
                    } else {
                        // Hash password
                        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt_insert = $pdo->prepare("
                            INSERT INTO tb_siswa (nama_siswa, kelas, nis, password) 
                            VALUES (:nama, :kelas, :nis, :pass)
                        ");
                        $stmt_insert->execute([
                            'nama' => $nama_siswa,
                            'kelas' => $kelas,
                            'nis' => $nis,
                            'pass' => $password_hashed
                        ]);
                        $success_message = "Akun siswa baru berhasil ditambahkan!";
                    }
                }

                if (empty($error_message)) {
                    header("Location: kelola_siswa.php?success=" . urlencode($success_message));
                    exit();
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error database: " . $e->getMessage();
        }
    }
}

// 2. PROSES HAPUS AKUN SISWA (GET)
if (isset($_GET['delete_siswa_id'])) {
    $delete_id = intval($_GET['delete_siswa_id']);
    try {
        $stmt_delete = $pdo->prepare("DELETE FROM tb_siswa WHERE id_siswa = :id");
        $stmt_delete->execute(['id' => $delete_id]);
        $success_message = "Akun siswa berhasil dihapus!";
        header("Location: kelola_siswa.php?success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Gagal menghapus data siswa: " . $e->getMessage();
    }
}

// Menangkap parameter sukses redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// 3. READ DATA UNTUK FORM EDIT (GET)
$siswa_edit_data = null;
if (isset($_GET['edit_siswa_id'])) {
    $edit_id = intval($_GET['edit_siswa_id']);
    try {
        $stmt_get = $pdo->prepare("SELECT * FROM tb_siswa WHERE id_siswa = :id");
        $stmt_get->execute(['id' => $edit_id]);
        $siswa_edit_data = $stmt_get->fetch();
    } catch (PDOException $e) {
        $error_message = "Gagal memuat data edit: " . $e->getMessage();
    }
}

// 4. READ DAFTAR SEMUA SISWA
try {
    $students = $pdo->query("SELECT * FROM tb_siswa ORDER BY nama_siswa ASC")->fetchAll();
} catch (PDOException $e) {
    die("Error database saat memuat daftar siswa: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa - Nommensen Admin</title>
    
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
                    <li><a href="#">Kelola Soal</a></li>
                    <li><a href="laporan_nilai.php">Laporan Nilai</a></li>
                    <li><a href="pengaturan.php">Pengaturan</a></li>
                    <li><a href="kelola_siswa.php" class="active">Kelola Data Siswa</a></li>
                </ul>
            </div>
            <!-- Tombol Keluar Sesi -->
            <a href="logout.php" class="btn-logout-sidebar" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Keluar (Logout)</a>
        </aside>

        <!-- Area Konten Utama Kanan -->
        <main class="main-content">
            <div style="margin-bottom: 2rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 800; color: #111827;">Kelola Data Akun Siswa</h2>
                <p style="color: #6b7280; font-size: 0.95rem; margin-top: 0.25rem;">Guru dapat mendaftarkan, mengedit data kelas/username, atau menghapus akun siswa dari sini.</p>
            </div>

            <!-- Pesan Umpan Balik Sukses/Error -->
            <?php if (!empty($error_message)): ?>
                <div class="alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <!-- Form Tambah / Edit Siswa -->
            <div class="form-card" style="border: 2px solid #374151; border-radius: 6px; padding: 1.5rem; margin-bottom: 2.5rem;">
                <div class="form-title" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.15rem; color: var(--accent-blue); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <?= $siswa_edit_data ? 'Form Edit Akun Siswa' : 'Daftarkan Akun Siswa Baru' ?>
                </div>

                <form action="kelola_siswa.php" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <?php if ($siswa_edit_data): ?>
                        <input type="hidden" name="id_siswa" value="<?= $siswa_edit_data['id_siswa'] ?>">
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <!-- Nama Lengkap -->
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Nama Lengkap Siswa:</label>
                            <input type="text" name="nama_siswa" class="form-control" placeholder="Contoh: Budi Santoso" required value="<?= htmlspecialchars($siswa_edit_data['nama_siswa'] ?? '') ?>">
                        </div>

                        <!-- Kelas -->
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Kelas (Contoh: Kelas 7A / Kelas 8B):</label>
                            <input type="text" name="kelas" class="form-control" placeholder="Contoh: Kelas 7A" required value="<?= htmlspecialchars($siswa_edit_data['kelas'] ?? '') ?>">
                        </div>

                        <!-- NIS (Username) -->
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">NIS / Username Login:</label>
                            <input type="text" name="nis" class="form-control" placeholder="Contoh: 26001" required value="<?= htmlspecialchars($siswa_edit_data['nis'] ?? '') ?>">
                        </div>

                        <!-- Password -->
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">
                                Password: 
                                <?php if ($siswa_edit_data): ?>
                                    <span style="font-size: 0.75rem; color: #d97706; font-weight: 500;">(Kosongkan jika tidak diganti)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password..." <?= $siswa_edit_data ? '' : 'required' ?>>
                        </div>
                    </div>

                    <div>
                        <button type="submit" name="save_siswa" class="btn-sm btn-success" style="padding: 0.75rem 1.5rem; font-weight: 700; border: none; cursor: pointer;">
                            <?= $siswa_edit_data ? 'Update Akun Siswa' : 'Daftarkan Siswa' ?>
                        </button>
                        <?php if ($siswa_edit_data): ?>
                            <a href="kelola_siswa.php" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; padding: 0.75rem 1.5rem; font-weight: 700; text-align: center; border-radius: 4px; border: 2px solid #4b5563; margin-left: 0.5rem;">
                                Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel Akun Siswa Terdaftar -->
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: #1f2937; margin-bottom: 1rem;">Daftar Akun Siswa Terdaftar</h3>
            <div class="table-container" style="border: 2px solid #374151; border-radius: 6px;">
                <table class="table-materi">
                    <thead>
                        <tr>
                            <th style="width: 25%; font-weight: 700;">Nama Siswa</th>
                            <th style="width: 15%; font-weight: 700; text-align: center;">Kelas</th>
                            <th style="width: 20%; font-weight: 700; text-align: center;">NIS (Username)</th>
                            <th style="width: 25%; font-weight: 700; text-align: center;">Login Terakhir</th>
                            <th style="width: 15%; font-weight: 700; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #6b7280; padding: 2.5rem;">
                                    Belum ada akun siswa terdaftar di sistem. Silakan daftarkan lewat form di atas.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($student['nama_siswa']) ?></td>
                                    <td style="text-align: center; font-weight: 600; color: #475569;"><?= htmlspecialchars($student['kelas']) ?></td>
                                    <td style="text-align: center; font-family: monospace; font-size: 0.95rem; font-weight: 600;"><?= htmlspecialchars($student['nis']) ?></td>
                                    <td style="text-align: center; font-size: 0.85rem; color: #475569; font-weight: 500;">
                                        <?= $student['last_login'] ? date('d M Y - H:i', strtotime($student['last_login'])) . ' WIB' : '<span style="color:#9ca3af; font-style:italic;">Belum pernah login</span>' ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="kelola_siswa.php?edit_siswa_id=<?= $student['id_siswa'] ?>" class="btn-sm btn-edit">Edit</a>
                                        <a href="kelola_siswa.php?delete_siswa_id=<?= $student['id_siswa'] ?>" class="btn-sm btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus akun siswa ini beserta seluruh riwayat nilainya?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Footer Bawah (Sesuai Storyboard) -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

</body>
</html>

<?php
/**
 * File: admin/pengaturan.php
 * Deskripsi: Halaman Pengaturan Akun Profil Guru / Admin.
 *            Memungkinkan guru mengubah nama profil dan mengganti password akun dengan 
 *            validasi ketat (verifikasi password lama).
 */

// Memroteksi halaman ini agar hanya bisa diakses oleh guru yang sudah login
require_once '../includes/auth_admin.php';

// Memanggil koneksi database
require_once '../config.php';

$id_guru = $_SESSION['admin_id'];
$success_message = '';
$error_message = '';

// 1. Ambil data guru terbaru dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM tb_guru WHERE id_guru = :id");
    $stmt->execute(['id' => $id_guru]);
    $guru = $stmt->fetch();
} catch (PDOException $e) {
    die("Error database saat memuat profil guru: " . $e->getMessage());
}

if (!$guru) {
    header("Location: logout.php");
    exit();
}

// 2. PROSES UBAH PROFIL NAMA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama_guru = trim($_POST['nama_guru'] ?? '');

    if (empty($nama_guru)) {
        $error_message = "Nama lengkap guru wajib diisi!";
    } else {
        try {
            $stmt_update = $pdo->prepare("UPDATE tb_guru SET nama_guru = :nama WHERE id_guru = :id");
            $stmt_update->execute(['nama' => $nama_guru, 'id' => $id_guru]);
            
            // Perbarui nama di session agar langsung berubah di header/sidebar
            $_SESSION['admin_nama'] = $nama_guru;
            $success_message = "Nama profil berhasil diperbarui!";
            
            // Muat ulang data terbaru
            $guru['nama_guru'] = $nama_guru;
        } catch (PDOException $e) {
            $error_message = "Gagal memperbarui profil: " . $e->getMessage();
        }
    }
}

// 3. PROSES GANTI PASSWORD (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $pass_lama = trim($_POST['password_lama'] ?? '');
    $pass_baru = trim($_POST['password_baru'] ?? '');
    $pass_konf = trim($_POST['password_konfirmasi'] ?? '');

    if (empty($pass_lama) || empty($pass_baru) || empty($pass_konf)) {
        $error_message = "Semua kolom password wajib diisi!";
    } elseif ($pass_baru !== $pass_konf) {
        $error_message = "Konfirmasi password baru tidak cocok!";
    } elseif (strlen($pass_baru) < 6) {
        $error_message = "Password baru minimal terdiri dari 6 karakter!";
    } else {
        // Verifikasi password lama dari DB
        if (!password_verify($pass_lama, $guru['password'])) {
            $error_message = "Password lama yang Anda masukkan salah!";
        } else {
            // Hash password baru
            $pass_baru_hashed = password_hash($pass_baru, PASSWORD_DEFAULT);
            try {
                $stmt_pw = $pdo->prepare("UPDATE tb_guru SET password = :pass WHERE id_guru = :id");
                $stmt_pw->execute(['pass' => $pass_baru_hashed, 'id' => $id_guru]);
                
                $success_message = "Password akun berhasil diubah dengan sukses!";
            } catch (PDOException $e) {
                $error_message = "Gagal memperbarui password: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - Nommensen Admin</title>
    
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
                    <li><a href="pengaturan.php" class="active">Pengaturan</a></li>
                    <li><a href="kelola_siswa.php">Kelola Data Siswa</a></li>
                </ul>
            </div>
            <!-- Tombol Keluar Sesi -->
            <a href="logout.php" class="btn-logout-sidebar" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Keluar (Logout)</a>
        </aside>

        <!-- Area Konten Utama Kanan -->
        <main class="main-content">
            <div style="margin-bottom: 2rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 800; color: #111827;">Pengaturan Profil & Keamanan</h2>
                <p style="color: #6b7280; font-size: 0.95rem; margin-top: 0.25rem;">Kelola detail profil pribadi Anda dan ganti password akun administrator di sini.</p>
            </div>

            <!-- Pesan Umpan Balik Sukses/Error -->
            <?php if (!empty($error_message)): ?>
                <div class="alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; align-items: flex-start;">
                
                <!-- Card 1: Ubah Nama Profil -->
                <div class="form-card" style="border: 2px solid #374151; border-radius: 6px; padding: 1.5rem; background: #ffffff;">
                    <div class="form-title" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.1rem; color: var(--accent-blue); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                        Ubah Detail Profil
                    </div>
                    <form action="pengaturan.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">NIP (Username - Tidak dapat diubah):</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($guru['nip']) ?>" disabled style="background-color: #f1f5f9; color: #6b7280; cursor: not-allowed;">
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Nama Lengkap Guru:</label>
                            <input type="text" name="nama_guru" class="form-control" required value="<?= htmlspecialchars($guru['nama_guru']) ?>">
                        </div>
                        <button type="submit" name="update_profile" class="btn-sm btn-success" style="padding: 0.7rem; font-weight: 700; border: none; cursor: pointer;">
                            Simpan Perubahan Nama
                        </button>
                    </form>
                </div>

                <!-- Card 2: Ganti Password -->
                <div class="form-card" style="border: 2px solid #374151; border-radius: 6px; padding: 1.5rem; background: #ffffff;">
                    <div class="form-title" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.1rem; color: var(--accent-blue); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                        Ganti Password Keamanan
                    </div>
                    <form action="pengaturan.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Password Lama Anda:</label>
                            <input type="password" name="password_lama" class="form-control" required placeholder="Masukkan password lama...">
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Password Baru:</label>
                            <input type="password" name="password_baru" class="form-control" required placeholder="Minimal 6 karakter...">
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #475569;">Konfirmasi Password Baru:</label>
                            <input type="password" name="password_konfirmasi" class="form-control" required placeholder="Ulangi password baru...">
                        </div>
                        <button type="submit" name="change_password" class="btn-sm btn-success" style="padding: 0.7rem; font-weight: 700; border: none; cursor: pointer;">
                            Ganti Password Akun
                        </button>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <!-- Footer Bawah (Sesuai Storyboard) -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

</body>
</html>

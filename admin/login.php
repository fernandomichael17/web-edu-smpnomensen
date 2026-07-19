<?php
/**
 * File: admin/login.php
 * Deskripsi: Halaman login untuk guru/admin.
 *            Memvalidasi input NIP dan Password menggunakan database.
 *            Password diverifikasi dengan aman menggunakan password_verify().
 */

// Memulai session PHP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika guru sudah login sebelumnya, langsung arahkan ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Memanggil konfigurasi database
require_once '../config.php';

$error_message = '';

// Memproses data jika form dikirim (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi input form
    $nip = trim($_POST['nip'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validasi input kosong
    if (empty($nip) || empty($password)) {
        $error_message = "Username dan Password wajib diisi!";
    } else {
        try {
            // Query untuk mencari data guru berdasarkan NIP (berperan sebagai Username)
            $stmt = $pdo->prepare("SELECT * FROM tb_guru WHERE nip = :nip LIMIT 1");
            $stmt->execute(['nip' => $nip]);
            $guru = $stmt->fetch();

            // Jika guru ditemukan, lakukan verifikasi password hash
            if ($guru && password_verify($password, $guru['password'])) {
                // Regenerate session ID demi keamanan (mencegah Session Fixation)
                session_regenerate_id(true);

                // Update timestamp last_login di database
                $stmt_ll = $pdo->prepare("UPDATE tb_guru SET last_login = NOW() WHERE id_guru = :id");
                $stmt_ll->execute(['id' => $guru['id_guru']]);

                // Set session data login
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $guru['id_guru'];
                $_SESSION['admin_nip'] = $guru['nip'];
                $_SESSION['admin_nama'] = $guru['nama_guru'];

                // Redirect ke halaman dashboard admin
                header("Location: dashboard.php");
                exit();
            } else {
                // Pesan error jika kredensial tidak cocok
                $error_message = "Username atau Password salah!";
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi masalah sistem. Silakan coba beberapa saat lagi.";
        }
    }
}

// Menangkap parameter error dari redirect halaman lain
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'session_expired') {
        $error_message = "Sesi Anda telah berakhir atau Anda belum masuk. Silakan login.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Guru / Administrator - SMP Swasta Nommensen</title>
    
    <!-- Memanggil CSS utama -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* CSS Khusus Halaman Login Guru sesuai Storyboard */
        .login-card {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border: 2px solid #374151; /* Outline gelap sesuai gambar storyboard */
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #e5e7eb; /* Abu-abu terang sesuai gambar storyboard */
            border-bottom: 2px solid #374151;
            padding: 0.9rem 1rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            text-align: center;
            color: #111827;
        }

        .card-body {
            padding: 2.5rem 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem 1rem;
            font-size: 1rem;
            border: 2px solid #374151; /* Input border gelap sesuai storyboard */
            border-radius: 4px;
            outline: none;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--accent-blue);
        }

        .btn-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        /* Tombol login warna abu-abu gelap/hitam sesuai gambar storyboard */
        .btn-login {
            background-color: #4b5563;
            color: #ffffff;
            border: none;
            width: 100%;
            max-width: 180px;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-login:hover {
            background-color: #374151;
        }

        .forgot-password {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .back-link {
            display: inline-block;
            font-size: 0.95rem;
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 600;
            margin-top: 1rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: 0.85rem;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
    </style>
</head>
<body>

    <!-- Header Atas (Sesuai Storyboard) -->
    <header class="top-header">
        Login Guru / Administrator
    </header>

    <!-- Konten Utama Tengah (Sesuai Storyboard) -->
    <main class="main-container">
        <div class="login-card">
            <!-- Bagian Form Login Header -->
            <div class="card-header">
                Form Login
            </div>
            
            <div class="card-body">
                <!-- Tampilkan pesan error jika ada -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert-error">
                        <strong>Gagal: </strong> <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form action="login.php" method="POST">
                    <!-- NIP Guru bertindak sebagai Username -->
                    <div class="form-group">
                        <label for="nip">Username:</label>
                        <input type="text" id="nip" name="nip" class="form-control" placeholder="Masukkan username..." required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password..." required autocomplete="current-password">
                    </div>

                    <!-- Tombol Login (Sesuai Storyboard) -->
                    <div class="btn-container">
                        <button type="submit" class="btn btn-login">Login</button>
                    </div>
                </form>

                <!-- Lupa Password (Sesuai Storyboard) -->
                <div class="forgot-password">
                    Lupa Password? Hubungi Admin
                </div>
            </div>
        </div>

        <a href="../index.php" class="back-link">Kembali ke Beranda</a>
    </main>

    <!-- Footer Bawah (Sesuai Storyboard) -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

</body>
</html>

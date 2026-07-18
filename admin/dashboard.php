<?php
/**
 * File: admin/dashboard.php
 * Deskripsi: Halaman Dashboard Guru/Admin (Placeholder).
 *            Memerlukan autentikasi melalui auth_admin.php.
 */

// Memroteksi halaman ini agar hanya bisa diakses oleh guru yang sudah login
require_once '../includes/auth_admin.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - SMP Swasta Nommensen</title>
    
    <!-- Impor Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #f8fafc;
            --accent-blue: #1A3A5C;
            --accent-blue-hover: #10243b;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Sederhana */
        .sidebar {
            width: 260px;
            background-color: var(--accent-blue);
            color: #ffffff;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar-brand h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .sidebar-menu {
            list-style: none;
            flex-grow: 1;
        }

        .sidebar-menu li {
            margin-bottom: 0.75rem;
        }

        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            display: block;
            font-weight: 500;
            transition: all 0.2s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .btn-logout {
            background-color: #e11d48;
            color: #ffffff;
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .btn-logout:hover {
            background-color: #be123c;
        }

        /* Konten Utama */
        .main-content {
            flex-grow: 1;
            padding: 3rem;
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

        .welcome-card {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .welcome-card h2 {
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .welcome-card p {
            color: var(--text-muted);
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <!-- Sidebar navigasi -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h3>Nommensen Admin</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="#">Kelola Siswa</a></li>
                <li><a href="#">Kelola Materi</a></li>
                <li><a href="#">Kelola Kuis</a></li>
            </ul>
        </div>
        <a href="logout.php" class="btn-logout">Keluar (Logout)</a>
    </aside>

    <!-- Area Konten Utama -->
    <main class="main-content">
        <header class="header-content">
            <h1>Dashboard</h1>
            <div class="user-info">
                <strong><?= htmlspecialchars($_SESSION['admin_nama']) ?></strong> (NIP: <?= htmlspecialchars($_SESSION['admin_nip']) ?>)
            </div>
        </header>

        <section class="welcome-card">
            <h2>Selamat Datang Kembali, Guru!</h2>
            <p>Halaman utama administrasi SMP Swasta Nommensen berhasil diakses.</p>
            <p style="margin-top: 1rem;">Di sini Anda dapat mengelola data siswa, menyunting materi pembelajaran (Vocabulary, Grammar, Conversation), serta menyusun soal kuis dan memantau hasil kuis siswa.</p>
        </section>
    </main>

</body>
</html>

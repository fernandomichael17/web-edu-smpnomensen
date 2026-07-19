-- File: database.sql
-- Deskripsi: Skema database untuk Aplikasi Pembelajaran Bahasa Inggris Berbasis Multimedia

-- Membuat database (Silakan eksekusi di phpMyAdmin)
CREATE DATABASE IF NOT EXISTS db_smp_nomensen_english;
USE db_smp_nomensen_english;

-- 1. tb_siswa (Menyimpan data siswa)
CREATE TABLE tb_siswa (
    id_siswa INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(20) NOT NULL UNIQUE,
    nama_siswa VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Akan disimpan dalam bentuk hash
    kelas VARCHAR(50) NOT NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. tb_guru (Menyimpan data guru/admin)
CREATE TABLE tb_guru (
    id_guru INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) NOT NULL UNIQUE,
    nama_guru VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Akan disimpan dalam bentuk hash
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. tb_materi (Menyimpan konten materi belajar)
CREATE TABLE tb_materi (
    id_materi INT AUTO_INCREMENT PRIMARY KEY,
    kategori ENUM('Vocabulary', 'Grammar', 'Conversation') NOT NULL,
    judul_materi VARCHAR(150) NOT NULL,
    konten_teks TEXT, -- Menyimpan definisi, rumus, dan contoh kalimat
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_guru INT,
    FOREIGN KEY (id_guru) REFERENCES tb_guru(id_guru) ON DELETE SET NULL
);

-- 4. tb_audio (Menyimpan file audio untuk Vocabulary/lainnya)
CREATE TABLE tb_audio (
    id_audio INT AUTO_INCREMENT PRIMARY KEY,
    id_materi INT NOT NULL,
    file_audio VARCHAR(255) NOT NULL, -- Nama atau path file audio
    keterangan VARCHAR(255),
    FOREIGN KEY (id_materi) REFERENCES tb_materi(id_materi) ON DELETE CASCADE
);

-- 5. tb_video (Menyimpan file video untuk Conversation)
CREATE TABLE tb_video (
    id_video INT AUTO_INCREMENT PRIMARY KEY,
    id_materi INT NOT NULL,
    file_video VARCHAR(255) NOT NULL, -- Nama atau path file video
    keterangan VARCHAR(255),
    FOREIGN KEY (id_materi) REFERENCES tb_materi(id_materi) ON DELETE CASCADE
);

-- 6. tb_kuis (Menyimpan data sesi/kategori kuis)
CREATE TABLE tb_kuis (
    id_kuis INT AUTO_INCREMENT PRIMARY KEY,
    judul_kuis VARCHAR(150) NOT NULL,
    kategori_materi ENUM('Vocabulary', 'Grammar', 'Conversation', 'Campuran') NOT NULL,
    waktu_pengerjaan INT NOT NULL, -- durasi timer kuis (dalam menit)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_guru INT,
    FOREIGN KEY (id_guru) REFERENCES tb_guru(id_guru) ON DELETE SET NULL
);

-- 7. tb_soal (Menyimpan daftar soal untuk setiap kuis)
CREATE TABLE tb_soal (
    id_soal INT AUTO_INCREMENT PRIMARY KEY,
    id_kuis INT NOT NULL,
    pertanyaan TEXT NOT NULL,
    opsi_a VARCHAR(255) NOT NULL,
    opsi_b VARCHAR(255) NOT NULL,
    opsi_c VARCHAR(255) NOT NULL,
    opsi_d VARCHAR(255) NOT NULL,
    jawaban_benar ENUM('A', 'B', 'C', 'D') NOT NULL,
    FOREIGN KEY (id_kuis) REFERENCES tb_kuis(id_kuis) ON DELETE CASCADE
);

-- 8. tb_hasil (Menyimpan riwayat nilai siswa setelah mengerjakan kuis)
CREATE TABLE tb_hasil (
    id_hasil INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT NOT NULL,
    id_kuis INT NOT NULL,
    skor INT NOT NULL,
    jumlah_benar INT NOT NULL,
    jumlah_salah INT NOT NULL,
    waktu_selesai TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_siswa) REFERENCES tb_siswa(id_siswa) ON DELETE CASCADE,
    FOREIGN KEY (id_kuis) REFERENCES tb_kuis(id_kuis) ON DELETE CASCADE
);

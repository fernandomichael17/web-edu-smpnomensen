-- File: insert_dummy_vocabulary.sql
-- Deskripsi: Query SQL terpisah untuk menambahkan data dummy unit vocabulary (tb_materi) 
--            dan kata kosakata beserta audionya (tb_audio).
--            Jalankan query ini di phpMyAdmin pada tab "SQL" di database db_smp_nomensen_english.

-- Bersihkan data vocabulary lama jika ada untuk mencegah duplikasi id
DELETE FROM tb_audio WHERE id_materi IN (SELECT id_materi FROM tb_materi WHERE kategori = 'Vocabulary');
DELETE FROM tb_materi WHERE kategori = 'Vocabulary';

-- 1. Insert Unit Vocabulary ke tb_materi (id_guru diasumsikan 1 dari tb_guru dummy)
INSERT INTO tb_materi (id_materi, kategori, judul_materi, konten_teks, id_guru) VALUES
(1, 'Vocabulary', 'Unit 1: Things in Classroom', 'Daftar kosakata tentang benda-benda yang ada di dalam ruang kelas sekolah.', 1),
(2, 'Vocabulary', 'Unit 2: Common Animals', 'Daftar kosakata tentang nama-nama hewan yang umum ditemui sehari-hari.', 1);

-- 2. Insert Kata Kosakata ke tb_audio
-- Untuk Unit 1 (id_materi = 1)
INSERT INTO tb_audio (id_materi, file_audio, keterangan) VALUES
(1, 'book.mp3', 'Book|Buku'),
(1, 'pencil.mp3', 'Pencil|Pensil'),
(1, 'eraser.mp3', 'Eraser|Penghapus'),
(1, 'ruler.mp3', 'Ruler|Penggaris'),
(1, 'whiteboard.mp3', 'Whiteboard|Papan Tulis');

-- Untuk Unit 2 (id_materi = 2)
INSERT INTO tb_audio (id_materi, file_audio, keterangan) VALUES
(2, 'cat.mp3', 'Cat|Kucing'),
(2, 'dog.mp3', 'Dog|Anjing'),
(2, 'bird.mp3', 'Bird|Burung'),
(2, 'elephant.mp3', 'Elephant|Gajah'),
(2, 'lion.mp3', 'Lion|Singa');

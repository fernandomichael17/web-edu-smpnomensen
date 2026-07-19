-- File: insert_dummy_conversation.sql
-- Deskripsi: Menambahkan data dummy unit materi conversation (tb_materi) dan video terkait (tb_video)
--            yang kompatibel dengan modul pembelajaran conversation.
--            Jalankan query ini di phpMyAdmin pada tab "SQL" di database db_smp_nomensen_english.

-- Bersihkan data conversation lama jika ada
DELETE FROM tb_video WHERE id_materi IN (SELECT id_materi FROM tb_materi WHERE kategori = 'Conversation');
DELETE FROM tb_materi WHERE kategori = 'Conversation';

-- 1. Insert Unit Conversation ke tb_materi (id_guru diasumsikan 1 dari tb_guru dummy)
INSERT INTO tb_materi (id_materi, kategori, judul_materi, konten_teks, id_guru) VALUES
(5, 'Conversation', 'Unit 1: Greeting a Friend', 
'A: Hello, Budi! How are you today?
B: Hi, Fernando! I am doing great, thank you. How about you?
A: I am fine too. Where are you going?
B: I am going to the school library to borrow some English books.
A: That sounds interesting! Can I join you?
B: Sure, let''s go together!', 1);

-- 2. Insert Video Conversation ke tb_video
INSERT INTO tb_video (id_materi, file_video, keterangan) VALUES
(5, 'sample_conversation.mp4', 'Video Percakapan Menyapa Teman (Greetings)');

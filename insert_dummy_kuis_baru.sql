-- File: insert_dummy_kuis_baru.sql
-- Deskripsi: Menambahkan data dummy kuis baru (tb_kuis) berkategori Conversation
--            dan 5 soal pilihan ganda (tb_soal) untuk testing kelola kuis/soal.
--            Jalankan query ini di phpMyAdmin pada tab "SQL" di database db_smp_nomensen_english.

-- Bersihkan data kuis id = 2 jika ada
DELETE FROM tb_soal WHERE id_kuis = 2;
DELETE FROM tb_kuis WHERE id_kuis = 2;

-- 1. Insert Kuis ke tb_kuis (id_materi = 5 adalah materi Conversation Unit 1 dari minggu 6)
INSERT INTO tb_kuis (id_kuis, judul_kuis, kategori_materi, waktu_pengerjaan, id_materi, nilai_lulus, id_guru) VALUES
(2, 'Kuis Latihan 2: Greeting & Conversation', 'Conversation', 15, 5, 75, 1);

-- 2. Insert Soal ke tb_soal untuk id_kuis = 2
INSERT INTO tb_soal (id_kuis, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban_benar) VALUES
(2, 'Complete the greeting: "A: How are you today? - B: I am ___, thank you."', 'fine', 'sorry', 'welcome', 'sad', 'A'),
(2, 'What is the most polite response when someone says "Thank you"?', 'Good bye', 'You are welcome', 'Sorry', 'Hello', 'B'),
(2, 'Complete the dialogue: "A: Can I join you to the library? - B: ___."', 'Sure, let''s go together!', 'No, I am fine', 'Thank you', 'Good morning', 'A'),
(2, 'Where do students usually go to borrow English books in school?', 'School yard', 'School canteen', 'School library', 'Teacher room', 'C'),
(2, 'The phrase "Nice to meet you" is commonly used for ___.', 'greeting someone new', 'saying sorry', 'parting with someone', 'asking for help', 'A');

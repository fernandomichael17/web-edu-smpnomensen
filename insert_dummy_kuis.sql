-- File: insert_dummy_kuis.sql
-- Deskripsi: Menambahkan data dummy kuis (tb_kuis) dan 5 soal pilihan ganda (tb_soal)
--            untuk pengujian alur kuis siswa.
--            Jalankan query ini di phpMyAdmin pada tab "SQL" di database db_smp_nomensen_english.

-- Bersihkan kuis dummy lama jika ada untuk mencegah duplikasi id
DELETE FROM tb_soal WHERE id_kuis = 1;
DELETE FROM tb_kuis WHERE id_kuis = 1;

-- 1. Insert Kuis ke tb_kuis
-- id_kuis = 1, waktu_pengerjaan = 10 (menit), id_guru = 1 (admin dummy)
INSERT INTO tb_kuis (id_kuis, judul_kuis, kategori_materi, waktu_pengerjaan, id_guru) VALUES
(1, 'Kuis Latihan 1: Basic Vocabulary & Grammar', 'Campuran', 10, 1);

-- 2. Insert Soal ke tb_soal untuk id_kuis = 1
INSERT INTO tb_soal (id_kuis, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban_benar) VALUES
(1, 'What is the Indonesian meaning of the English word "Pencil"?', 'Buku', 'Pensil', 'Penghapus', 'Penggaris', 'B'),
(1, 'Complete the sentence: "She ___ to school by bus every morning."', 'go', 'goes', 'going', 'gone', 'B'),
(1, 'What is the English word for "Kucing"?', 'Dog', 'Cat', 'Bird', 'Lion', 'B'),
(1, 'Complete the sentence: "They ___ not like drinking coffee."', 'do', 'does', 'are', 'is', 'A'),
(1, 'What is the Indonesian meaning of the English word "Blackboard"?', 'Papan Tulis', 'Meja Kelas', 'Penghapus Papan', 'Lemari Buku', 'A');

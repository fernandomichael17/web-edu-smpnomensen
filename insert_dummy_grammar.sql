-- File: insert_dummy_grammar.sql
-- Deskripsi: Menambahkan data dummy unit materi grammar (tb_materi) dengan format JSON
--            yang kompatibel dengan aplikasi.
--            Jalankan query ini di phpMyAdmin pada tab "SQL" di database db_smp_nomensen_english.

-- Bersihkan data grammar lama jika ada
DELETE FROM tb_materi WHERE kategori = 'Grammar';

-- Insert data dummy Grammar 1: Simple Present Tense
INSERT INTO tb_materi (id_materi, kategori, judul_materi, konten_teks, id_guru) VALUES
(3, 'Grammar', 'Unit 1: Simple Present Tense', 
'{"definisi":"Simple Present Tense digunakan untuk menyatakan kebiasaan sehari-hari (habitual action), kebenaran umum (general truth), atau kejadian yang terjadi pada saat ini.","rumus":{"positif":"S + V1 (s\\/es) \\/ S + is\\/am\\/are + Noun\\/Adj","negatif":"S + do\\/does + not + V1 \\/ S + is\\/am\\/are + not + Noun\\/Adj","tanya":"Do\\/Does + S + V1? \\/ Is\\/Am\\/Are + S + Noun\\/Adj?"},"contoh":["She goes to school every day | Dia pergi ke sekolah setiap hari","They do not play football on Sundays | Mereka tidak bermain bola pada hari Minggu","Is he a doctor? | Apakah dia seorang dokter?"],"latihan":[{"pertanyaan":"He ___ (read) a newspaper every morning.","pilihan":["read","reads","reading","reader"],"jawaban":"reads"},{"pertanyaan":"We ___ not like spicy food.","pilihan":["do","does","are","is"],"jawaban":"do"}]}', 1);

-- Insert data dummy Grammar 2: Present Continuous Tense
INSERT INTO tb_materi (id_materi, kategori, judul_materi, konten_teks, id_guru) VALUES
(4, 'Grammar', 'Unit 2: Present Continuous Tense', 
'{"definisi":"Present Continuous Tense digunakan untuk menyatakan perbuatan yang sedang berlangsung pada saat dibicarakan sekarang.","rumus":{"positif":"S + is\\/am\\/are + V-ing","negatif":"S + is\\/am\\/are + not + V-ing","tanya":"Is\\/Am\\/Are + S + V-ing?"},"contoh":["I am writing an English letter now | Saya sedang menulis surat bahasa Inggris sekarang","They are not sleeping at this moment | Mereka tidak sedang tidur saat ini","Is she cooking in the kitchen? | Apakah dia sedang memasak di dapur?"],"latihan":[{"pertanyaan":"Look! The children ___ (play) in the garden.","pilihan":["play","plays","are playing","is playing"],"jawaban":"are playing"},{"pertanyaan":"What ___ you doing right now?","pilihan":["do","does","are","is"],"jawaban":"are"}]}', 1);

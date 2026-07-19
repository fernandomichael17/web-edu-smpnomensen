-- File: insert_dummy_siswa.sql
-- Deskripsi: Query SQL terpisah untuk menambahkan 1 akun siswa dummy ke tb_siswa.
--            Gunakan query ini di phpMyAdmin pada tab "SQL" di database db_smp_nomensen_english.

-- Username (NIS) untuk login: 26001
-- Password: siswa123
-- Kelas: VIII-A
-- Hash password dibuat menggunakan password_hash('siswa123', PASSWORD_DEFAULT) di PHP.
INSERT INTO tb_siswa (nis, nama_siswa, password, kelas) 
VALUES ('26001', 'Fernando Michael', '$2y$10$lUjTxJGUv2gsufQWPB3WO.fFmybPyCHEHSdsh5kHPb0//irXEx39G', 'VIII-A');

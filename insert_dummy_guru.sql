-- File: insert_dummy_guru.sql
-- Deskripsi: Query SQL terpisah untuk menambahkan 1 akun guru/admin dummy ke tb_guru.
--            Gunakan query ini di phpMyAdmin pada tab "SQL".

-- Password untuk akun ini adalah: admin123
-- Hash password dibuat menggunakan password_hash('admin123', PASSWORD_DEFAULT) di PHP.
INSERT INTO tb_guru (nip, nama_guru, password) 
VALUES ('19203040', 'Budi Setiawan, S.Pd.', '$2y$10$XwVmKbmjU1jQYpYDGKISyunbtbH3D4LzQW4GbTZlPOLdUd.37TlE6');

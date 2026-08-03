-- migrasi-password-plaintext.sql
-- Mengubah penyimpanan kata sandi dari hash bcrypt menjadi teks biasa (plaintext).
--
-- CATATAN PENTING:
-- 1. Hash bcrypt TIDAK dapat dibalik. Password lama TIDAK dapat dipulihkan
--    dari DB ini — migrasi ini MENIMPA semua kata sandi dengan nilai default.
-- 2. Jalankan sekali pada database target (device lain) SETELAH kode login
--    diperbarui ke perbandingan plaintext, lalu beri tahu pengguna password barunya.
-- 3. Aman dijalankan ulang: akun yang sudah plaintext tidak ikut ditimpa.

-- Semua admin menjadi 'admin123'
UPDATE `admin` SET `kata_sandi` = 'admin123' WHERE `kata_sandi` NOT IN ('admin123');

-- Semua pelanggan menjadi 'pelanggan123'
UPDATE `pelanggan` SET `kata_sandi` = 'pelanggan123' WHERE `kata_sandi` NOT IN ('pelanggan123');

-- Alternatif password unik per akun (mis. dari ID pelanggan):
-- UPDATE `pelanggan` SET `kata_sandi` = CONCAT('rknet-', RIGHT(`id`, 6))
--   WHERE `kata_sandi` NOT IN (CONCAT('rknet-', RIGHT(`id`, 6)));

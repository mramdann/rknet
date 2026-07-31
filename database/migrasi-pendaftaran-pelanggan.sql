-- Migrasi satu kali untuk pendaftaran akun pelanggan publik.
-- PREFLIGHT WAJIB: jalankan query berikut dan selesaikan semua hasil duplikat sebelum migrasi.
-- SELECT LOWER(TRIM(email)) AS email_normal, COUNT(*) AS jumlah
-- FROM pelanggan
-- GROUP BY LOWER(TRIM(email))
-- HAVING COUNT(*) > 1;

USE dbrknet;

ALTER TABLE pelanggan
    ADD CONSTRAINT uq_pelanggan_email UNIQUE (email),
    ALTER COLUMN status SET DEFAULT 'pending';

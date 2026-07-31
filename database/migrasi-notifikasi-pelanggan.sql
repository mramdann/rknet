-- Migrasi satu kali untuk database dbrknet yang sudah berjalan.
-- Tambah kolom pelanggan_id (NULL = broadcast, terisi = khusus pelanggan) dan
-- dibaca (0 = belum dibaca, 1 = sudah dibaca) di tabel notifikasi.
-- Fresh install sudah termasuk di schema.sql.
USE dbrknet;

ALTER TABLE notifikasi
    ADD COLUMN pelanggan_id VARCHAR(30) NULL AFTER isi,
    ADD CONSTRAINT fk_notifikasi_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
    ADD COLUMN dibaca TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

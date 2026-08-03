-- Migrasi satu kali (tidak idempoten) untuk database yang sudah menjalankan migrasi pembayaran bank.
USE dbrknet;

ALTER TABLE rekening_bank
    ADD COLUMN jenis VARCHAR(20) NOT NULL DEFAULT 'bank' AFTER id;

INSERT INTO rekening_bank (jenis, nama_bank, nomor_rekening, atas_nama, status) VALUES
    ('qris', 'QRIS', 'QRIS', 'CV. Rizky Win Solution', 'aktif');

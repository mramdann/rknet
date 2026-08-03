-- Migrasi satu kali untuk database dbrknet yang sudah berjalan.
USE dbrknet;

CREATE TABLE rekening_bank (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    jenis            VARCHAR(20)  NOT NULL DEFAULT 'bank',
    nama_bank        VARCHAR(100) NOT NULL,
    nomor_rekening   VARCHAR(60)  NOT NULL,
    atas_nama        VARCHAR(120) NOT NULL,
    status           VARCHAR(20)  NOT NULL DEFAULT 'aktif',
    CONSTRAINT uq_rekening_bank UNIQUE (nama_bank, nomor_rekening)
);

ALTER TABLE tagihan
    ADD COLUMN rekening_bank_id INT NULL AFTER paket_id,
    ADD COLUMN bukti_pembayaran VARCHAR(255) NULL AFTER status,
    ADD COLUMN catatan_verifikasi VARCHAR(255) NULL AFTER bukti_pembayaran,
    ADD COLUMN diajukan_pada DATETIME NULL AFTER catatan_verifikasi,
    ADD COLUMN diverifikasi_pada DATETIME NULL AFTER diajukan_pada,
    ADD COLUMN diverifikasi_oleh INT NULL AFTER diverifikasi_pada,
    ADD CONSTRAINT uq_tagihan_no_invoice UNIQUE (no_invoice),
    ADD CONSTRAINT fk_tagihan_rekening_bank FOREIGN KEY (rekening_bank_id) REFERENCES rekening_bank(id),
    ADD CONSTRAINT fk_tagihan_admin_verifikasi FOREIGN KEY (diverifikasi_oleh) REFERENCES admin(id);

INSERT INTO rekening_bank (id, jenis, nama_bank, nomor_rekening, atas_nama, status) VALUES
    (1, 'bank', 'BCA', '1234567890', 'CV. Rizky Win Solution', 'aktif'),
    (2, 'bank', 'Mandiri', '9876543210', 'CV. Rizky Win Solution', 'aktif'),
    (3, 'bank', 'BRI', '112233445566', 'CV. Rizky Win Solution', 'aktif'),
    (4, 'qris', 'QRIS', 'QRIS', 'CV. Rizky Win Solution', 'aktif');

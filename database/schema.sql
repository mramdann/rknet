-- schema.sql — struktur tabel dbrknet (Bahasa Indonesia). Jalankan sebelum seed.sql.
CREATE DATABASE IF NOT EXISTS dbrknet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dbrknet;

DROP TABLE IF EXISTS tagihan;
DROP TABLE IF EXISTS rekening_bank;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS paket;
DROP TABLE IF EXISTS prospek, area;
DROP TABLE IF EXISTS notifikasi;
DROP TABLE IF EXISTS pengaturan;
DROP TABLE IF EXISTS admin;

CREATE TABLE admin (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    peran       VARCHAR(50)  NOT NULL,
    kata_sandi  VARCHAR(255) NOT NULL
);

CREATE TABLE rekening_bank (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    jenis            VARCHAR(20)  NOT NULL DEFAULT 'bank',
    nama_bank        VARCHAR(100) NOT NULL,
    nomor_rekening   VARCHAR(60)  NOT NULL,
    atas_nama        VARCHAR(120) NOT NULL,
    status           VARCHAR(20)  NOT NULL DEFAULT 'aktif',
    CONSTRAINT uq_rekening_bank UNIQUE (nama_bank, nomor_rekening)
);

CREATE TABLE paket (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(120) NOT NULL,
    kecepatan   VARCHAR(30)  NOT NULL,
    harga       INT          NOT NULL,
    status      VARCHAR(20)  NOT NULL DEFAULT 'aktif'
);

CREATE TABLE pelanggan (
    id            VARCHAR(30) PRIMARY KEY,
    nama          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    hp            VARCHAR(30)  NOT NULL,
    alamat        VARCHAR(255) NULL,
    paket_id      INT          NULL,
    status        VARCHAR(20)  NOT NULL DEFAULT 'aktif',
    tgl_bergabung VARCHAR(20)  NOT NULL,
    kata_sandi    VARCHAR(255) NOT NULL,
    CONSTRAINT fk_pelanggan_paket FOREIGN KEY (paket_id) REFERENCES paket(id)
);

CREATE TABLE tagihan (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    no_invoice            VARCHAR(40)  NOT NULL,
    pelanggan_id          VARCHAR(30)  NOT NULL,
    paket_id              INT          NULL,
    rekening_bank_id      INT          NULL,
    harga                 INT          NOT NULL,
    tanggal               VARCHAR(20)  NOT NULL,
    status                VARCHAR(20)  NOT NULL DEFAULT 'menunggu',
    bukti_pembayaran      VARCHAR(255) NULL,
    catatan_verifikasi    VARCHAR(255) NULL,
    diajukan_pada         DATETIME     NULL,
    diverifikasi_pada     DATETIME     NULL,
    diverifikasi_oleh     INT          NULL,
    CONSTRAINT uq_tagihan_no_invoice UNIQUE (no_invoice),
    CONSTRAINT fk_tagihan_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
    CONSTRAINT fk_tagihan_paket FOREIGN KEY (paket_id) REFERENCES paket(id),
    CONSTRAINT fk_tagihan_rekening_bank FOREIGN KEY (rekening_bank_id) REFERENCES rekening_bank(id),
    CONSTRAINT fk_tagihan_admin_verifikasi FOREIGN KEY (diverifikasi_oleh) REFERENCES admin(id)
);

CREATE TABLE notifikasi (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    judul    VARCHAR(150) NOT NULL,
    isi      TEXT         NOT NULL,
    target   VARCHAR(80)  NOT NULL,
    tanggal  VARCHAR(20)  NOT NULL,
    status   VARCHAR(20)  NOT NULL DEFAULT 'draft'
);

CREATE TABLE pengaturan (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama_situs  VARCHAR(120) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    telepon     VARCHAR(40)  NOT NULL,
    alamat      VARCHAR(255) NOT NULL
);

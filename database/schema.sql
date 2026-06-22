-- schema.sql — struktur tabel dbstarlite (Bahasa Indonesia). Jalankan sebelum seed.sql.
CREATE DATABASE IF NOT EXISTS dbstarlite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dbstarlite;

DROP TABLE IF EXISTS tagihan;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS paket;
DROP TABLE IF EXISTS prospek;
DROP TABLE IF EXISTS area;
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
    CONSTRAINT fk_pelanggan_paket FOREIGN KEY (paket_id) REFERENCES paket(id)
);

CREATE TABLE tagihan (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    no_invoice    VARCHAR(40)  NOT NULL,
    pelanggan_id  VARCHAR(30)  NOT NULL,
    paket_id      INT          NULL,
    harga         INT          NOT NULL,
    tanggal       VARCHAR(20)  NOT NULL,
    status        VARCHAR(20)  NOT NULL DEFAULT 'menunggu',
    CONSTRAINT fk_tagihan_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
    CONSTRAINT fk_tagihan_paket FOREIGN KEY (paket_id) REFERENCES paket(id)
);

CREATE TABLE prospek (
    id        VARCHAR(20) PRIMARY KEY,
    nama      VARCHAR(100) NOT NULL,
    hp        VARCHAR(30)  NOT NULL,
    area      VARCHAR(120) NOT NULL,
    tanggal   VARCHAR(20)  NOT NULL,
    status    VARCHAR(20)  NOT NULL DEFAULT 'baru'
);

CREATE TABLE area (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    nama             VARCHAR(100) NOT NULL,
    kota             VARCHAR(100) NOT NULL,
    status           VARCHAR(20)  NOT NULL DEFAULT 'tercakup',
    jumlah_pelanggan INT          NOT NULL DEFAULT 0
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

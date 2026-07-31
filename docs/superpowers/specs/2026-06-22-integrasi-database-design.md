# Integrasi Database RKnet — Design

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Menghubungkan aplikasi (yang selama ini UI-only) ke database MySQL `dbrknet`. Pendekatan **bertahap**: spec ini = **Fase 1 (read-only)** — semua halaman menampilkan data nyata dari DB; aksi (tambah/edit/hapus/tandai) tetap mock. Fase 2 (CRUD nyata) = spec terpisah nanti.

## Goal

Mengganti sumber data dari array dummy di `*-config.php` menjadi query ke database `dbrknet`, tanpa mengubah markup/partial (output halaman identik). Skema tabel & kolom memakai Bahasa Indonesia.

## Constraints & Decisions

- **DB:** MySQL/MariaDB, database `dbrknet`, host `127.0.0.1`, **port 3382** (non-default, sejajar Apache 8282), user `root`, password kosong (default XAMPP).
- **Koneksi:** PDO (singleton) via `db.php` baru di root. Error handling: `PDO::ERRMODE_EXCEPTION`. Jika koneksi/tabel gagal → tampilkan **pesan error yang jelas** (tanpa fallback ke array dummy).
- **Read-only:** Fase 1 hanya `SELECT`. Tombol aksi (Tambah/Edit/Tandai Lunas/Simpan) tetap mock JS/GET seperti sekarang.
- **Markup tidak berubah:** config map hasil query ke **bentuk array yang sama** seperti sekarang, memakai **alias kolom SQL** agar key array persis (`no_invoice AS noInvoice`, dll). Partial/loop tidak disentuh.
- **Tanggal sebagai display string:** kolom tanggal (`tgl_bergabung`, `tanggal`) disimpan `VARCHAR` berisi string tampilan Indonesia ("15 Jun 2026") agar output identik tanpa logika format. (Bisa dimigrasi ke tipe `DATE` di fase lanjutan.)
- **Konten marketing landing tetap di `config.php`** (benefits, features, socials, paket landing) — copy statis, bukan data terkelola. Tidak masuk DB.
- **Feed notifikasi portal pelanggan** (`portal-config.php` `$daftarNotifikasi`, bentuk tipe/judul/isi/waktu) **tetap config** — feed presentasional personal, beda dari notifikasi broadcast admin. (Kandidat fase lanjutan.)
- Kode & komentar Bahasa Indonesia; output di-`htmlspecialchars()` (sudah di partial). Lint tiap `.php` disentuh.

## Struktur File

```
db.php                      # baru — koneksi PDO ke dbrknet (singleton)
database/
├── schema.sql              # baru — CREATE TABLE (6 tabel, Bahasa Indonesia)
└── seed.sql                # baru — INSERT data awal (dari dummy yang ada)
helpers.php                 # tetap (formatRupiah, badgeStatus)
admin-config.php            # ubah — arrays di-SELECT dari DB (alias ke key lama)
portal-config.php           # ubah — $pelanggan/$paketAktif/$daftarTransaksi/$paketTersedia dari DB
config.php                  # TIDAK berubah (copy landing)
CLAUDE.md                   # ubah — premis "UI-only, no database" diperbarui
```

## Koneksi (db.php)

```php
<?php
// db.php — koneksi tunggal (singleton) ke database dbrknet via PDO.
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host = '127.0.0.1';
    $port = '3382';
    $nama = 'dbrknet';
    $user = 'root';
    $sandi = '';

    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$nama;charset=utf8mb4",
            $user, $sandi,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        exit('<h2 style="font-family:sans-serif">Koneksi database gagal.</h2>'
           . '<p style="font-family:sans-serif">Pastikan MySQL berjalan di port 3382 dan database <code>dbrknet</code> sudah dibuat (jalankan <code>database/schema.sql</code> & <code>database/seed.sql</code>).</p>');
    }
    return $pdo;
}
```

## Skema (database/schema.sql) — nama tabel & kolom Bahasa Indonesia

```sql
CREATE DATABASE IF NOT EXISTS dbrknet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dbrknet;

-- Admin pengelola
CREATE TABLE admin (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    peran       VARCHAR(50)  NOT NULL,
    kata_sandi  VARCHAR(255) NOT NULL
);

-- Paket internet
CREATE TABLE paket (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(120) NOT NULL,
    kecepatan   VARCHAR(30)  NOT NULL,
    harga       INT          NOT NULL,
    status      VARCHAR(20)  NOT NULL DEFAULT 'aktif'
);

-- Pelanggan
CREATE TABLE pelanggan (
    id            VARCHAR(30) PRIMARY KEY,         -- mis. RKNET-2024-008812
    nama          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    hp            VARCHAR(30)  NOT NULL,
    alamat        VARCHAR(255) NULL,
    paket_id      INT          NULL,
    status        VARCHAR(20)  NOT NULL DEFAULT 'aktif',
    tgl_bergabung VARCHAR(20)  NOT NULL,           -- string tampilan "12 Jan 2024"
    CONSTRAINT fk_pelanggan_paket FOREIGN KEY (paket_id) REFERENCES paket(id)
);

-- Tagihan / transaksi
CREATE TABLE tagihan (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    no_invoice    VARCHAR(40)  NOT NULL,
    pelanggan_id  VARCHAR(30)  NOT NULL,
    paket_id      INT          NULL,
    harga         INT          NOT NULL,
    tanggal       VARCHAR(20)  NOT NULL,           -- string tampilan "15 Jun 2026"
    status        VARCHAR(20)  NOT NULL DEFAULT 'menunggu',
    CONSTRAINT fk_tagihan_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
    CONSTRAINT fk_tagihan_paket FOREIGN KEY (paket_id) REFERENCES paket(id)
);

-- Notifikasi broadcast admin
CREATE TABLE notifikasi (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    judul    VARCHAR(150) NOT NULL,
    isi      TEXT         NOT NULL,
    target   VARCHAR(80)  NOT NULL,
    tanggal  VARCHAR(20)  NOT NULL,
    status   VARCHAR(20)  NOT NULL DEFAULT 'draft'
);

-- Pengaturan situs (satu baris)
CREATE TABLE pengaturan (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama_situs  VARCHAR(120) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    telepon     VARCHAR(40)  NOT NULL,
    alamat      VARCHAR(255) NOT NULL
);
```

## Seed (database/seed.sql)

INSERT yang mereplikasi data dummy saat ini:
- `paket`: 3 paket admin (100/200/500 Mbps, harga 199000/100000/250000).
- `pelanggan`: 6 baris (`$daftarPelanggan`), `paket_id` dipetakan dari kecepatan.
- `tagihan`: 5 baris (`$daftarTagihan`), `pelanggan_id` & `paket_id` dipetakan dari nama/kecepatan.
- `notifikasi`: 5 baris (`$daftarNotifikasi` admin).
- `pengaturan`: 1 baris (`$pengaturan`).
- `admin`: 1 baris (`$admin`), `kata_sandi` = hash dummy (mis. `password_hash('admin123', PASSWORD_DEFAULT)` ditulis sebagai string statis di seed; login tetap mock di Fase 1).

Portal pelanggan memakai pelanggan `RKNET-2024-008812` (Dwi Anjasmoro) sebagai "yang sedang login", paket aktif = 200 Mbps. `$daftarTransaksi` portal di-seed sebagai tagihan milik pelanggan tsb (4 baris dari `portal-config.php`).

## Pemetaan config → DB (alias agar key array persis)

**admin-config.php** — tiap variabel diisi dari query (contoh kunci):
- `$admin`  = `SELECT nama, email, peran FROM admin LIMIT 1` (ambil baris pertama).
- `$daftarPaket` = `SELECT nama, kecepatan, harga, status, (SELECT COUNT(*) FROM pelanggan WHERE pelanggan.paket_id = paket.id) AS jumlahPelanggan FROM paket`.
- `$daftarPelanggan` = `SELECT pl.id, pl.nama, pl.email, pl.hp, pk.kecepatan AS paket, pl.status, pl.tgl_bergabung AS bergabung FROM pelanggan pl LEFT JOIN paket pk ON pk.id = pl.paket_id`.
- `$daftarTagihan` = `SELECT t.no_invoice AS noInvoice, pl.nama AS pelanggan, pk.kecepatan AS paket, t.harga, t.tanggal, t.status FROM tagihan t JOIN pelanggan pl ON pl.id = t.pelanggan_id LEFT JOIN paket pk ON pk.id = t.paket_id`.
- `$daftarNotifikasi` = `SELECT judul, isi, target, tanggal, status FROM notifikasi`.
- `$pengaturan` = `SELECT nama_situs AS namaSitus, email, telepon, alamat FROM pengaturan LIMIT 1`.
- `$statistik` = dihitung nyata: `totalPelanggan`=COUNT pelanggan, `pelangganAktif`=COUNT WHERE status='aktif', `pendapatanBulan`=SUM(harga) tagihan WHERE status='lunas', `tagihanPending`=COUNT tagihan WHERE status='menunggu'. (Angka jadi sesuai data seed, lebih kecil dari placeholder lama — ini perbaikan konsistensi, bukan bug.)

**portal-config.php**:
- `$pelanggan` = `SELECT id, nama, email, hp, alamat FROM pelanggan WHERE id = 'RKNET-2024-008812'`.
- `$paketAktif` = `SELECT pk.nama, pk.kecepatan, pk.harga, pk.status FROM pelanggan pl JOIN paket pk ON pk.id = pl.paket_id WHERE pl.id = 'RKNET-2024-008812'` + `masaAktif` (string statis "15 Juli 2026" — presentasional, tetap di config).
- `$daftarTransaksi` = `SELECT t.no_invoice AS noInvoice, pk.nama AS paket, pk.kecepatan AS kecepatan, t.harga, t.tanggal, t.status FROM tagihan t JOIN paket pk ON pk.id = t.paket_id WHERE t.pelanggan_id = 'RKNET-2024-008812' ORDER BY t.id DESC`.
- `$paketTersedia` = `SELECT nama, kecepatan, harga FROM paket` + flag `dipilih` (200 Mbps = true) & `fitur` ditambahkan di PHP (fitur = copy presentasional, tetap di config).
- `$daftarNotifikasi` (feed portal) = **tetap array statis** di config (presentasional).

## Out of Scope (Fase 1)

- Aksi tulis nyata (INSERT/UPDATE/DELETE), handler POST, redirect — itu **Fase 2**.
- Autentikasi nyata (login admin/pelanggan tetap mock).
- Konten marketing landing (`config.php`) & feed notifikasi portal → tetap config.
- Migrasi kolom tanggal ke tipe `DATE`.

## Catatan CLAUDE.md

Bagian "What this is" yang menyatakan *UI-only, no database* diperbarui: aplikasi kini punya lapisan DB read-only (`db.php`, `database/*.sql`); aksi tulis masih mock (Fase 2 berikutnya).

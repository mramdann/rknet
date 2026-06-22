# Integrasi Database Fase 1 (Read-only) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengganti sumber data array dummy di `admin-config.php` & `portal-config.php` dengan query read-only ke database `dbstarlite`, tanpa mengubah markup halaman.

**Architecture:** `db.php` baru menyediakan koneksi PDO singleton ke `dbstarlite`. Skema & seed di `database/*.sql`. Tiap config men-`SELECT` data dan memberi **alias kolom** agar key array identik dengan sebelumnya, sehingga partial/loop tidak berubah. Aksi tulis tetap mock (Fase 2).

**Tech Stack:** PHP native + PDO (MySQL), MySQL/MariaDB XAMPP @ port 3382, Bootstrap (tak tersentuh).

## Global Constraints

- DB: MySQL `dbstarlite`, host `127.0.0.1`, port `3382`, user `root`, password kosong.
- Koneksi PDO dengan `PDO::ERRMODE_EXCEPTION` + `FETCH_ASSOC`; koneksi gagal → pesan error jelas, tanpa fallback dummy.
- Fase 1 hanya `SELECT` (read-only). Tombol aksi tetap mock.
- Markup/partial TIDAK berubah — config map hasil query ke key array lama via alias SQL.
- Kolom tanggal disimpan VARCHAR berisi string tampilan ("15 Jun 2026").
- Tabel lead bernama `prospek` (hindari reserved word `lead`).
- Konten landing (`config.php`) & feed notifikasi portal tetap config.
- Kode & komentar Bahasa Indonesia. Lint tiap `.php`: `/d/WebServer/xampp82/php/php.exe -l <file>` → "No syntax errors detected".
- Import SQL & verifikasi browser dilakukan oleh user (Bash di sini ter-sandbox dari localhost & MySQL).
- Commit per task, prefix `feat(db):`.

---

### Task 1: Fondasi DB — koneksi + skema + seed

Buat helper koneksi PDO dan dua berkas SQL (skema + seed). Deliverable: database `dbstarlite` bisa dibuat & terisi, dan `db()` mengembalikan PDO.

**Files:**
- Create: `db.php`
- Create: `database/schema.sql`
- Create: `database/seed.sql`

**Interfaces:**
- Produces: fungsi global `db(): PDO` (singleton). Tabel: `admin, paket, pelanggan, tagihan, prospek, area, notifikasi, pengaturan`. Paket id 1=100Mbps, 2=200Mbps, 3=500Mbps (dipakai pemetaan FK di seed & config).

- [ ] **Step 1: Buat `db.php`**

```php
<?php
// db.php — koneksi tunggal (singleton) ke database dbstarlite via PDO.
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host  = '127.0.0.1';
    $port  = '3382';
    $nama  = 'dbstarlite';
    $user  = 'root';
    $sandi = '';

    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$nama;charset=utf8mb4",
            $user,
            $sandi,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        exit('<h2 style="font-family:sans-serif">Koneksi database gagal.</h2>'
           . '<p style="font-family:sans-serif">Pastikan MySQL berjalan di port 3382 dan database '
           . '<code>dbstarlite</code> sudah dibuat (jalankan <code>database/schema.sql</code> lalu '
           . '<code>database/seed.sql</code>).</p>');
    }
    return $pdo;
}
```

- [ ] **Step 2: Buat `database/schema.sql`**

```sql
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
```

- [ ] **Step 3: Hasilkan hash kata sandi admin (dipakai di seed)**

Login Fase 1 masih mock, tapi kolom `kata_sandi` NOT NULL diisi hash nyata. Hasilkan:

Run:
```bash
/d/WebServer/xampp82/php/php.exe -r "echo password_hash('admin123', PASSWORD_DEFAULT), PHP_EOL;"
```
Expected: string diawali `$2y$10$...` (panjang 60). Salin nilainya untuk dipakai di Step 4 (ganti `GANTI_HASH`).

- [ ] **Step 4: Buat `database/seed.sql`**

Tempel hash dari Step 3 menggantikan `GANTI_HASH`.

```sql
-- seed.sql — data awal dbstarlite (replika data dummy). Jalankan setelah schema.sql.
USE dbstarlite;

INSERT INTO paket (id, nama, kecepatan, harga, status) VALUES
(1, 'Paket 100 Mbps Starlite', '100 Mbps', 199000, 'aktif'),
(2, 'Paket 200 Mbps Starlite', '200 Mbps', 100000, 'aktif'),
(3, 'Paket 500 Mbps Starlite', '500 Mbps', 250000, 'aktif');

INSERT INTO pelanggan (id, nama, email, hp, alamat, paket_id, status, tgl_bergabung) VALUES
('STL-2024-008812', 'Dwi Anjasmoro', 'dwi.anjasmoro@gmail.com', '0811-7891-2233', 'Jl. Mawar No.12, Roa Malaka, Tambora, Jakarta Barat', 2, 'aktif', '12 Jan 2024'),
('STL-2024-008813', 'Siti Rahmawati', 'siti.rahma@gmail.com', '0812-3344-5566', NULL, 1, 'aktif', '03 Feb 2024'),
('STL-2024-008814', 'Budi Hartono', 'budi.hartono@gmail.com', '0813-7788-9900', NULL, 3, 'aktif', '21 Mar 2024'),
('STL-2024-008815', 'Maya Kusuma', 'maya.kusuma@gmail.com', '0814-1122-3344', NULL, 1, 'nonaktif', '09 Apr 2024'),
('STL-2024-008816', 'Agus Pratama', 'agus.pratama@gmail.com', '0815-5566-7788', NULL, 2, 'aktif', '17 Mei 2024'),
('STL-2024-008817', 'Indah Permata', 'indah.permata@gmail.com', '0816-9988-7766', NULL, 3, 'aktif', '28 Mei 2024');

-- Tagihan: 5 invoice (satu per pelanggan) + 3 riwayat milik 008812 (Apr/Mei/Jul)
INSERT INTO tagihan (id, no_invoice, pelanggan_id, paket_id, harga, tanggal, status) VALUES
(1, 'INV/2026/06/008812', 'STL-2024-008812', 2, 100000, '15 Jun 2026', 'lunas'),
(2, 'INV/2026/06/008813', 'STL-2024-008813', 1, 199000, '15 Jun 2026', 'lunas'),
(3, 'INV/2026/06/008814', 'STL-2024-008814', 3, 250000, '15 Jun 2026', 'menunggu'),
(4, 'INV/2026/06/008816', 'STL-2024-008816', 2, 100000, '16 Jun 2026', 'menunggu'),
(5, 'INV/2026/06/008817', 'STL-2024-008817', 3, 250000, '16 Jun 2026', 'lunas'),
(6, 'INV/2026/05/008812', 'STL-2024-008812', 2, 100000, '15 Mei 2026', 'lunas'),
(7, 'INV/2026/04/008812', 'STL-2024-008812', 2, 100000, '15 Apr 2026', 'lunas'),
(8, 'INV/2026/07/008812', 'STL-2024-008812', 2, 100000, '15 Jul 2026', 'menunggu');

INSERT INTO prospek (id, nama, hp, area, tanggal, status) VALUES
('LEAD-0451', 'Rizki Maulana', '0812-1111-2233', 'Cibinong, Bogor', '20 Jun 2026', 'baru'),
('LEAD-0452', 'Putri Lestari', '0813-4455-6677', 'Depok, Jawa Barat', '20 Jun 2026', 'dihubungi'),
('LEAD-0453', 'Hendra Wijaya', '0856-7788-9900', 'Bekasi, Jawa Barat', '19 Jun 2026', 'terjadwal'),
('LEAD-0454', 'Nadia Safira', '0878-1212-3434', 'Tangerang, Banten', '18 Jun 2026', 'selesai'),
('LEAD-0455', 'Bayu Saputra', '0821-5656-7878', 'Sleman, Yogyakarta', '17 Jun 2026', 'batal'),
('LEAD-0456', 'Citra Anggun', '0813-9090-1212', 'Bogor, Jawa Barat', '17 Jun 2026', 'baru');

INSERT INTO area (nama, kota, status, jumlah_pelanggan) VALUES
('Cibinong', 'Bogor', 'tercakup', 312),
('Depok Kota', 'Depok', 'tercakup', 458),
('Bekasi Barat', 'Bekasi', 'tercakup', 274),
('Sleman', 'Yogyakarta', 'segera', 0),
('Serpong', 'Tangerang', 'tercakup', 196),
('Cimahi', 'Bandung', 'segera', 0);

INSERT INTO notifikasi (judul, isi, target, tanggal, status) VALUES
('Pemeliharaan jaringan area Depok', 'Akan ada pemeliharaan 23 Jun 2026 pukul 01.00-03.00 WIB.', 'Pelanggan Depok', '20 Jun 2026', 'terkirim'),
('Promo upgrade 500 Mbps', 'Upgrade paket bulan ini diskon 30% untuk 3 bulan pertama.', 'Semua pelanggan', '18 Jun 2026', 'terkirim'),
('Pengingat jatuh tempo tagihan', 'Tagihan Juni jatuh tempo 15 Jun. Mohon segera lakukan pembayaran.', 'Pelanggan aktif', '14 Jun 2026', 'terkirim'),
('Selamat datang pelanggan baru', 'Draf sambutan untuk pelanggan yang baru bergabung.', 'Pelanggan baru', '12 Jun 2026', 'draft'),
('Survei kepuasan layanan Q2', 'Draf undangan mengisi survei kepuasan kuartal 2.', 'Semua pelanggan', '10 Jun 2026', 'draft');

INSERT INTO pengaturan (nama_situs, email, telepon, alamat) VALUES
('Starlite Indonesia', 'cs@starlite.id', '0804-1-555-666', 'Jl. Fiber Optik No. 1, Jakarta Selatan');

INSERT INTO admin (nama, email, peran, kata_sandi) VALUES
('Rangga Administrator', 'admin@starlite.id', 'Super Admin', 'GANTI_HASH');
```

- [ ] **Step 5: Lint `db.php`**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l db.php
```
Expected: "No syntax errors detected in db.php".

- [ ] **Step 6: Import database (dilakukan user)**

Bash di sini tidak bisa akses MySQL. Minta user menjalankan (atau via phpMyAdmin: Import `schema.sql` lalu `seed.sql`):
```
mysql -h 127.0.0.1 -P 3382 -u root < database/schema.sql
mysql -h 127.0.0.1 -P 3382 -u root < database/seed.sql
```
Verifikasi user: `dbstarlite` punya 8 tabel; `SELECT COUNT(*) FROM tagihan` = 8, `FROM pelanggan` = 6, `FROM prospek` = 6.

- [ ] **Step 7: Commit**

```bash
git add db.php database/schema.sql database/seed.sql
git commit -m "feat(db): koneksi PDO + skema & seed dbstarlite (8 tabel, bahasa indonesia)"
```

---

### Task 2: Rewire admin-config.php ke DB

Ganti seluruh array dummy di `admin-config.php` dengan query read-only. Markup admin tidak berubah karena alias kolom menyamai key lama.

**Files:**
- Modify: `admin-config.php` (ganti total isi)

**Interfaces:**
- Consumes: `db(): PDO` (Task 1); `formatRupiah()`, `badgeStatus()` (helpers.php).
- Produces: variabel global dengan bentuk identik seperti sebelumnya: `$admin`(nama,email,peran), `$statistik`(totalPelanggan,pelangganAktif,pendapatanBulan,tagihanPending), `$daftarPelanggan`([id,nama,email,hp,paket,status,bergabung]), `$daftarPaket`([nama,kecepatan,harga,jumlahPelanggan,status]), `$daftarTagihan`([noInvoice,pelanggan,paket,harga,tanggal,status]), `$daftarLead`([id,nama,hp,area,tanggal,status]), `$daftarArea`([nama,kota,status,jumlahPelanggan]), `$daftarNotifikasi`([judul,isi,target,tanggal,status]), `$pengaturan`([namaSitus,email,telepon,alamat]).

> **Catatan perubahan tampilan yang disengaja:** halaman admin transaksi kini menampilkan **8 baris** (semua invoice di DB, termasuk riwayat 008812), bukan 5. `$statistik` jadi angka nyata dari seed (totalPelanggan=6, pelangganAktif=5, pendapatanBulan=749000, tagihanPending=3). Dashboard "terbaru" tetap memakai `array_slice(...,0,5)`.

- [ ] **Step 1: Ganti seluruh isi `admin-config.php`**

```php
<?php
// admin-config.php — data portal admin, dibaca read-only dari database dbstarlite.
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()
require_once __DIR__ . '/db.php';        // db(): PDO

$pdo = db();

// Admin yang sedang login
$admin = $pdo->query("SELECT nama, email, peran FROM admin LIMIT 1")->fetch();

// Daftar paket + jumlah pelanggan aktif (subquery)
$daftarPaket = $pdo->query(
    "SELECT nama, kecepatan, harga, status,
            (SELECT COUNT(*) FROM pelanggan WHERE pelanggan.paket_id = paket.id) AS jumlahPelanggan
     FROM paket ORDER BY id"
)->fetchAll();

// Daftar pelanggan (paket = kecepatan paketnya)
$daftarPelanggan = $pdo->query(
    "SELECT pl.id, pl.nama, pl.email, pl.hp, pk.kecepatan AS paket, pl.status, pl.tgl_bergabung AS bergabung
     FROM pelanggan pl LEFT JOIN paket pk ON pk.id = pl.paket_id
     ORDER BY pl.id"
)->fetchAll();

// Daftar tagihan (gabung nama pelanggan & kecepatan paket)
$daftarTagihan = $pdo->query(
    "SELECT t.no_invoice AS noInvoice, pl.nama AS pelanggan, pk.kecepatan AS paket,
            t.harga, t.tanggal, t.status
     FROM tagihan t
     JOIN pelanggan pl ON pl.id = t.pelanggan_id
     LEFT JOIN paket pk ON pk.id = t.paket_id
     ORDER BY t.id"
)->fetchAll();

// Daftar lead / prospek cek jangkauan
$daftarLead = $pdo->query(
    "SELECT id, nama, hp, area, tanggal, status FROM prospek ORDER BY id"
)->fetchAll();

// Daftar area cakupan
$daftarArea = $pdo->query(
    "SELECT nama, kota, status, jumlah_pelanggan AS jumlahPelanggan FROM area ORDER BY id"
)->fetchAll();

// Daftar notifikasi broadcast (urut sesuai seed)
$daftarNotifikasi = $pdo->query(
    "SELECT judul, isi, target, tanggal, status FROM notifikasi ORDER BY id"
)->fetchAll();

// Pengaturan situs
$pengaturan = $pdo->query(
    "SELECT nama_situs AS namaSitus, email, telepon, alamat FROM pengaturan LIMIT 1"
)->fetch();

// Ringkasan statistik — dihitung nyata dari data
$statistik = [
    'totalPelanggan'  => (int) $pdo->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn(),
    'pelangganAktif'  => (int) $pdo->query("SELECT COUNT(*) FROM pelanggan WHERE status = 'aktif'")->fetchColumn(),
    'pendapatanBulan' => (int) $pdo->query("SELECT COALESCE(SUM(harga), 0) FROM tagihan WHERE status = 'lunas'")->fetchColumn(),
    'tagihanPending'  => (int) $pdo->query("SELECT COUNT(*) FROM tagihan WHERE status = 'menunggu'")->fetchColumn(),
];
```

- [ ] **Step 2: Lint**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l admin-config.php
```
Expected: "No syntax errors detected in admin-config.php".

- [ ] **Step 3: Verifikasi browser (user)**

Buka tiap halaman admin (`dashboard, pelanggan, paket, transaksi, lead, area, notifikasi, pengaturan`). Konfirmasi data tampil dari DB: dashboard kartu statistik menampilkan 6 / 5 / Rp749.000 / 3; tabel transaksi 8 baris; pelanggan 6 baris dengan paket benar; paket menampilkan jumlah pelanggan per paket.

- [ ] **Step 4: Commit**

```bash
git add admin-config.php
git commit -m "feat(db): admin-config baca data dari dbstarlite (read-only)"
```

---

### Task 3: Rewire portal-config.php ke DB

Ganti data pelanggan/paket/transaksi/paket-tersedia dengan query untuk pelanggan `STL-2024-008812`. Fitur paket, `masaAktif`, dan feed notifikasi portal tetap presentasional di config.

**Files:**
- Modify: `portal-config.php` (ganti bagian data; pertahankan `$daftarNotifikasi` feed sebagai array statis)

**Interfaces:**
- Consumes: `db(): PDO`; tabel `pelanggan`, `paket`, `tagihan`.
- Produces: `$pelanggan`([id,nama,email,hp,alamat]), `$paketAktif`([nama,kecepatan,harga,status,masaAktif]), `$daftarTransaksi`([noInvoice,paket,kecepatan,harga,tanggal,status]), `$paketTersedia`([nama,kecepatan,harga,fitur,dipilih]), `$daftarNotifikasi`(feed: [tipe,judul,isi,waktu]).

- [ ] **Step 1: Ganti seluruh isi `portal-config.php`**

```php
<?php
// portal-config.php — data portal pelanggan, dibaca read-only dari database dbstarlite.
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()
require_once __DIR__ . '/db.php';        // db(): PDO

$pdo = db();
$idPelanggan = 'STL-2024-008812';        // pelanggan yang "sedang login" (mock)

// Data pelanggan yang sedang login
$stmt = $pdo->prepare("SELECT id, nama, email, hp, alamat FROM pelanggan WHERE id = ?");
$stmt->execute([$idPelanggan]);
$pelanggan = $stmt->fetch();

// Paket internet yang sedang aktif (+ masa aktif presentasional)
$stmt = $pdo->prepare(
    "SELECT pk.nama, pk.kecepatan, pk.harga, pk.status
     FROM pelanggan pl JOIN paket pk ON pk.id = pl.paket_id
     WHERE pl.id = ?"
);
$stmt->execute([$idPelanggan]);
$paketAktif = $stmt->fetch();
$paketAktif['masaAktif'] = '15 Juli 2026';

// Riwayat transaksi pelanggan (urut sesuai seed: Jun, Mei, Apr, Jul)
$stmt = $pdo->prepare(
    "SELECT t.no_invoice AS noInvoice, pk.nama AS paket, pk.kecepatan AS kecepatan,
            t.harga, t.tanggal, t.status
     FROM tagihan t JOIN paket pk ON pk.id = t.paket_id
     WHERE t.pelanggan_id = ?
     ORDER BY t.id"
);
$stmt->execute([$idPelanggan]);
$daftarTransaksi = $stmt->fetchAll();

// Pilihan paket pada halaman "Pilih Paket" — harga dari DB, fitur & flag presentasional
$fiturPaket = [
    '100 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi'],
    '200 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Harga promo'],
    '500 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Prioritas jaringan'],
];
$paketTersedia = [];
foreach ($pdo->query("SELECT nama, kecepatan, harga FROM paket ORDER BY id") as $row) {
    $row['fitur']   = $fiturPaket[$row['kecepatan']] ?? [];
    $row['dipilih'] = ($row['kecepatan'] === '200 Mbps');
    $paketTersedia[] = $row;
}

// Feed notifikasi & informasi untuk panel offcanvas — presentasional (tetap statis)
$daftarNotifikasi = [
    ['tipe' => 'notifikasi', 'judul' => 'Pembayaran Berhasil', 'isi' => 'Tagihan INV/2026/06/008812 sebesar Rp100.000 telah dibayar.', 'waktu' => '15 Jun 2026, 09:14'],
    ['tipe' => 'informasi',  'judul' => 'Internet Aktif',       'isi' => 'Paket 200 Mbps Starlite aktif hingga 15 Juli 2026.', 'waktu' => '15 Jun 2026, 09:15'],
    ['tipe' => 'informasi',  'judul' => 'Promo Upgrade 500 Mbps', 'isi' => 'Nikmati internet 500 Mbps hanya Rp250.000/bulan. Unlimited!', 'waktu' => '10 Jun 2026, 12:00'],
    ['tipe' => 'notifikasi', 'judul' => 'Pemeliharaan Sistem',  'isi' => 'Pemeliharaan terjadwal 20 Jun 2026, 01:00-03:00 WIB.', 'waktu' => '08 Jun 2026, 17:30'],
];
```

- [ ] **Step 2: Lint**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l portal-config.php
```
Expected: "No syntax errors detected in portal-config.php".

- [ ] **Step 3: Verifikasi browser (user)**

Buka halaman portal (`dashboard, transaksi, invoice, paket, profil`). Konfirmasi: nama "Dwi Anjasmoro", paket aktif "Paket 200 Mbps Starlite", riwayat transaksi 4 baris urut Jun→Mei→Apr→Jul, halaman Pilih Paket menampilkan 3 paket dengan 200 Mbps tersorot, panel notifikasi tetap muncul.

- [ ] **Step 4: Commit**

```bash
git add portal-config.php
git commit -m "feat(db): portal-config baca data pelanggan dari dbstarlite (read-only)"
```

---

### Task 4: Perbarui CLAUDE.md

Premis "UI-only, no database" sudah tidak akurat. Perbarui agar mencerminkan lapisan DB read-only.

**Files:**
- Modify: `CLAUDE.md` (bagian "What this is" + "Running & checking")

**Interfaces:**
- Consumes: tidak ada (dokumentasi).
- Produces: tidak ada.

- [ ] **Step 1: Baca `CLAUDE.md` lalu perbarui kalimat premis**

Baca file. Pada bagian **What this is**, ganti kalimat:
> It is **UI-only**: every form, button, and "action" is a visual mock backed by dummy data — there is no database, authentication, or backend processing.

menjadi:
> Data dibaca **read-only dari database MySQL `dbstarlite`** (port 3382) lewat `db.php` (PDO); `admin-config.php` & `portal-config.php` menjalankan `SELECT` lalu memetakan hasil ke array yang dipakai partial. Konten marketing landing (`config.php`) tetap statis. **Aksi tulis (tambah/edit/hapus/tandai/login) masih mock** — INSERT/UPDATE/DELETE direncanakan di Fase 2. Skema & data awal ada di `database/schema.sql` + `database/seed.sql` (struktur Bahasa Indonesia; tabel lead bernama `prospek`).

- [ ] **Step 2: Tambah catatan DB di bagian "Running & checking"**

Setelah poin tentang lint PHP, tambahkan baris:
> - **Database:** import `database/schema.sql` lalu `database/seed.sql` ke MySQL `dbstarlite` (host 127.0.0.1, port 3382, user root tanpa password). Tanpa DB, halaman admin/portal menampilkan pesan "Koneksi database gagal." Bash di sini ter-sandbox dari MySQL — import & cek lewat phpMyAdmin/CLI di luar sesi.

- [ ] **Step 3: Commit**

```bash
git add CLAUDE.md
git commit -m "docs: perbarui CLAUDE.md untuk lapisan database read-only (fase 1)"
```

---

## Catatan Penutup

Setelah keempat task: aplikasi membaca semua data terkelola dari `dbstarlite`; markup tak berubah; aksi tulis tetap mock. Fase 2 (CRUD nyata: handler POST, INSERT/UPDATE/DELETE, login nyata) menjadi spec/plan terpisah berikutnya.

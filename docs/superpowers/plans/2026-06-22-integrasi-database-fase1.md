# Integrasi Database Fase 1 (Read-only) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Memindahkan sumber data admin dan portal dari array dummy ke MySQL `dbrknet` tanpa mengubah bentuk array yang dipakai markup.

**Architecture:** `db.php` menyediakan koneksi PDO tunggal. `admin-config.php` dan `portal-config.php` menjalankan SELECT lalu memetakan alias kolom SQL ke key lama. Konten marketing landing dan feed notifikasi portal tetap statis.

**Tech Stack:** PHP native, PDO MySQL, MySQL `dbrknet` pada `127.0.0.1:3382`.

## Global Constraints

- Fase 1 bersifat read-only; aksi tulis dan login masih mock pada tahap ini.
- Skema memakai enam tabel berbahasa Indonesia: `admin`, `paket`, `pelanggan`, `tagihan`, `notifikasi`, dan `pengaturan`.
- Query config menghasilkan bentuk array yang sama agar markup/partial tidak berubah.
- Kolom tanggal tetap berupa string tampilan Indonesia.
- Konten landing tidak dipindah ke DB; feed notifikasi portal tetap array presentasional.
- Jika koneksi atau query gagal, tampilkan pesan jelas tanpa fallback dummy dan tanpa stack trace.
- Lint setiap `.php`; verifikasi data melalui MySQL dan halaman melalui browser.

---

### Task 1: Fondasi DB, skema, dan seed

**Files:**
- Create: `db.php`
- Create: `database/schema.sql`
- Create: `database/seed.sql`

**Interfaces:**
- `db(): PDO` mengembalikan singleton dengan `PDO::ERRMODE_EXCEPTION` dan fetch mode associative.

- [ ] **Step 1:** Buat koneksi ke `dbrknet`, host `127.0.0.1`, port `3382`, user `root`, password kosong, charset `utf8mb4`.
- [ ] **Step 2:** Tangani kegagalan koneksi dengan HTTP 500 dan petunjuk menjalankan schema/seed.
- [ ] **Step 3:** Buat skema enam tabel berikut.

| Tabel | Kolom penting |
|---|---|
| `admin` | id, nama, email, peran, kata_sandi |
| `paket` | id, nama, kecepatan, harga, status |
| `pelanggan` | id, nama, email, hp, alamat, paket_id, status, tgl_bergabung |
| `tagihan` | id, no_invoice, pelanggan_id, paket_id, harga, tanggal, status |
| `notifikasi` | id, judul, isi, target, tanggal, status |
| `pengaturan` | id, nama_situs, email, telepon, alamat |

- [ ] **Step 4:** Tambahkan FK pelanggan ke paket serta tagihan ke pelanggan/paket.
- [ ] **Step 5:** Seed tiga paket, enam pelanggan, delapan tagihan, lima notifikasi, satu pengaturan, dan satu admin.
- [ ] **Step 6:** Import schema lalu seed; verifikasi jumlah tabel = 6, pelanggan = 6, dan tagihan = 8.
- [ ] **Step 7:** Lint `db.php` dan commit fondasi DB.

---

### Task 2: Rewire admin-config.php ke DB

**Files:**
- Modify: `admin-config.php`

**Data contract yang dipertahankan:**
- `$admin`: nama, email, peran.
- `$statistik`: totalPelanggan, pelangganAktif, pendapatanBulan, tagihanPending.
- `$daftarPelanggan`: id, nama, email, hp, paket, status, bergabung.
- `$daftarPaket`: nama, kecepatan, harga, jumlahPelanggan, status.
- `$daftarTagihan`: noInvoice, pelanggan, paket, harga, tanggal, status.
- `$daftarNotifikasi`: judul, isi, target, tanggal, status.
- `$pengaturan`: namaSitus, email, telepon, alamat.

- [ ] **Step 1:** Require `db.php` dan ambil koneksi.
- [ ] **Step 2:** Query admin yang sedang digunakan dan seluruh daftar dengan alias seperti `no_invoice AS noInvoice` serta `tgl_bergabung AS bergabung`.
- [ ] **Step 3:** Hitung jumlah pelanggan per paket melalui subquery COUNT.
- [ ] **Step 4:** Hitung statistik dari COUNT/SUM nyata di DB.
- [ ] **Step 5:** Pertahankan `helpers.php` dan escape di markup.
- [ ] **Step 6:** Lint lalu buka dashboard, pelanggan, paket, transaksi, notifikasi, dan pengaturan untuk memastikan data DB tampil.
- [ ] **Step 7:** Commit rewire admin.

---

### Task 3: Rewire portal-config.php ke DB

**Files:**
- Modify: `portal-config.php`

**Data contract yang dipertahankan:**
- `$pelanggan`: id, nama, email, hp, alamat.
- `$paketAktif`: nama, kecepatan, harga, masaAktif, status.
- `$daftarTransaksi`: noInvoice, paket, kecepatan, harga, tanggal, status.
- `$paketTersedia`: nama, kecepatan, harga, dipilih, fitur.
- `$daftarNotifikasi`: tetap array statis presentasional.

- [ ] **Step 1:** Require `db.php` dan pilih pelanggan demo `RKNET-2024-008812` pada tahap pra-auth.
- [ ] **Step 2:** Query data pelanggan dan paket aktif melalui join.
- [ ] **Step 3:** Query transaksi pelanggan tersebut, urutkan terbaru, dan pertahankan alias lama.
- [ ] **Step 4:** Query paket tersedia; tambahkan flag `dipilih` dan daftar fitur presentasional di PHP.
- [ ] **Step 5:** Pertahankan `masaAktif` dan feed notifikasi portal sebagai copy statis.
- [ ] **Step 6:** Lint dan verifikasi dashboard, transaksi, invoice, paket, serta profil portal.
- [ ] **Step 7:** Commit rewire portal.

---

### Task 4: Perbarui dokumentasi aktif

- [ ] **Step 1:** Perbarui `CLAUDE.md` bahwa data terkelola dibaca dari MySQL melalui `db.php`.
- [ ] **Step 2:** Dokumentasikan lokasi schema/seed, port DB, dan perilaku error.
- [ ] **Step 3:** Tegaskan bahwa fase ini masih read-only dan aksi nyata direncanakan di Fase 2.
- [ ] **Step 4:** Commit dokumentasi.

---

## Catatan Penutup

Setelah empat task, admin dan portal membaca data dari enam tabel MySQL dengan bentuk array tetap kompatibel terhadap markup. Landing marketing dan feed notifikasi portal tetap statis; autentikasi serta aksi tulis dilanjutkan pada Fase 2.

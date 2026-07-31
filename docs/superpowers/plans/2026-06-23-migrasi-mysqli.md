# Migrasi PDO ke mysqli Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengganti lapisan akses data dari PDO ke mysqli tanpa mengubah skema, data, SQL, markup, atau perilaku aplikasi.

**Architecture:** `db.php` memusatkan konstanta koneksi dan helper mysqli. Semua call-site baca memakai `kueri()`/`kueriSatu()`/`kueriNilai()`, sedangkan handler tulis memakai `eksekusi()`. Pagination tidak lagi menerima objek koneksi.

**Tech Stack:** PHP native, mysqli prepared statements, MySQL `dbrknet` pada port 3382.

## Global Constraints

- Gunakan mysqli dengan `MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT`.
- Setelan koneksi hanya berada di konstanta `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, dan `DB_NAME`.
- Semua parameter query di-bind; tipe `i`, `d`, atau `s` ditentukan otomatis.
- Error koneksi/query tampil sebagai kartu aman tanpa stack trace.
- Jangan ubah skema, seed, SQL bisnis, markup, auth, CSRF, atau alur PRG.
- Kode dan komentar domain memakai Bahasa Indonesia. Lint setiap `.php`.
- Verifikasi seluruh alur publik, guard, login/logout, CRUD, CSRF, pagination, dan portal; rollback data uji ke seed.

---

### Task 1: db.php, konstanta dan helper mysqli

**Files:**
- Rewrite: `db.php`

**Interfaces:**
- `db(): mysqli`
- `kueri(string $sql, array $params = []): array`
- `kueriSatu(string $sql, array $params = []): ?array`
- `kueriNilai(string $sql, array $params = [])`
- `eksekusi(string $sql, array $params = []): void`
- `pesanErrorDb(string $judul, string $detail): never`

- [ ] **Step 1:** Definisikan konstanta koneksi untuk `dbrknet` dan port 3382.
- [ ] **Step 2:** Buat singleton mysqli, set charset `utf8mb4`, dan tangani kegagalan koneksi.
- [ ] **Step 3:** Buat helper prepared statement dengan bind parameter by-reference.
- [ ] **Step 4:** Implementasikan helper baca satu/banyak/nilai dan helper tulis.
- [ ] **Step 5:** Pasang exception handler aman untuk kegagalan DB yang tidak tertangkap.
- [ ] **Step 6:** Lint dan uji helper terhadap query sederhana; commit.

---

### Task 2: Migrasi baca admin dan pagination

**Files:**
- Modify: `pagination.php`
- Modify: `admin-config.php`
- Modify: `admin/pelanggan.php`
- Modify: `admin/transaksi.php`
- Modify: `admin/notifikasi.php`
- Modify: `admin/login.php`

- [ ] **Step 1:** Ubah `ambilPaginasi()` agar tidak menerima `$pdo`; hitung total dengan `kueriNilai()` dan ambil baris dengan `kueri()`.
- [ ] **Step 2:** Ganti seluruh `$pdo->query/prepare` pada `admin-config.php` dengan helper yang sesuai.
- [ ] **Step 3:** Pertahankan alias dan bentuk data untuk admin, statistik, paket, pelanggan, tagihan, notifikasi, dan pengaturan.
- [ ] **Step 4:** Ubah tiga halaman daftar agar memanggil `ambilPaginasi($sqlBase, $sqlCount, $params)`.
- [ ] **Step 5:** Ubah login admin menjadi `kueriSatu()` tanpa mengubah verifikasi password/session.
- [ ] **Step 6:** Lint seluruh file; login dan buka dashboard, pelanggan, paket, transaksi, notifikasi, serta pengaturan.
- [ ] **Step 7:** Commit migrasi baca admin.

---

### Task 3: Migrasi handler tulis admin

**Files:**
- Modify: `admin/aksi-paket.php`
- Modify: `admin/aksi-notifikasi.php`
- Modify: `admin/aksi-pelanggan.php`
- Modify: `admin/aksi-transaksi.php`
- Modify: `admin/aksi-pengaturan.php`

- [ ] **Step 1:** Ganti INSERT/UPDATE/DELETE Paket dengan `eksekusi()` dan tangkap `mysqli_sql_exception` untuk FK.
- [ ] **Step 2:** Ganti aksi Notifikasi, Pelanggan, dan Transaksi dengan `eksekusi()`.
- [ ] **Step 3:** Pada Pengaturan, gunakan `eksekusi()` untuk profil/situs/password dan `kueriSatu()` untuk mengambil hash lama.
- [ ] **Step 4:** Pertahankan guard, CSRF, validasi, flash, dan redirect persis seperti sebelumnya.
- [ ] **Step 5:** Lint seluruh handler; uji tiap aksi dan rollback data uji.
- [ ] **Step 6:** Commit migrasi handler admin.

---

### Task 4: Migrasi portal

**Files:**
- Modify: `portal-config.php`
- Modify: `portal/transaksi.php`
- Modify: `portal/login.php`
- Modify: `portal/aksi-profil.php`
- Modify: `portal/aksi-paket.php`

- [ ] **Step 1:** Ganti query config portal dengan `kueri()`, `kueriSatu()`, dan `kueriNilai()` tanpa mengubah data presentasional.
- [ ] **Step 2:** Ubah pagination riwayat transaksi agar memakai signature helper baru.
- [ ] **Step 3:** Ubah login portal menjadi `kueriSatu()`.
- [ ] **Step 4:** Ubah aksi profil dan paket menjadi `eksekusi()`; gunakan `kueriSatu()` untuk verifikasi hash password.
- [ ] **Step 5:** Lint; login pelanggan, buka seluruh halaman portal, uji profil dan perubahan paket, lalu rollback.
- [ ] **Step 6:** Commit migrasi portal.

---

### Task 5: Verifikasi menyeluruh dan dokumentasi

- [ ] **Step 1:** Jalankan lint untuk semua PHP yang disentuh.
- [ ] **Step 2:** Jalankan sweep publik, guard, login/logout, CRUD, CSRF, pagination, dan portal.
- [ ] **Step 3:** Pastikan DB kembali ke data seed: enam pelanggan, delapan tagihan, paket pelanggan demo kembali ke ID 2, dan status data uji dipulihkan.
- [ ] **Step 4:** Spot-check dashboard admin dan portal dengan BrowserOS.
- [ ] **Step 5:** Perbarui `CLAUDE.md` dan `docs/DOKUMENTASI.md` agar menyebut mysqli serta helper baru.
- [ ] **Step 6:** Commit dokumentasi.

---

## Catatan Penutup

Setelah lima task, seluruh akses data memakai mysqli melalui helper dan setelan koneksi terpusat di `db.php`. Skema, data, SQL bisnis, serta markup tetap sama dan data uji selalu dikembalikan ke seed.

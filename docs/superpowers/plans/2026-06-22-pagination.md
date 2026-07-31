# Pagination Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Pagination sisi-server dengan `LIMIT/OFFSET`, page size 5, dan cari/filter via GET untuk tiga tabel admin serta riwayat transaksi portal.

**Architecture:** `pagination.php` menyediakan `halamanSaatIni()`, `ambilPaginasi()` untuk COUNT + LIMIT/OFFSET, dan `tampilPaginasi()` untuk nav Bootstrap. Tiap halaman daftar membangun WHERE dari `$_GET`, memanggil helper, lalu merender baris dan navigasi. Filter sisi-klien lama diganti form/link GET.

**Tech Stack:** PHP native (PDO prepared statements), MySQL `dbrknet` pada port 3382.

## Global Constraints

- Pagination sisi-server memakai `?hal=N` dan `PER_HALAMAN = 5`.
- `LIMIT/OFFSET` selalu integer hasil cast; nilai filter memakai bound params.
- Cari/filter memakai GET dan dipertahankan di link nav serta form.
- `?hal` di-clamp ke `1..totalHalaman`; filter kosong menampilkan semua.
- Output query string dan nilai form di-`htmlspecialchars()`.
- Kode dan komentar domain memakai Bahasa Indonesia. Lint setiap `.php`.
- Verifikasi dengan PowerShell HTTP dan BrowserOS. Commit per task dengan prefix `feat(paginasi):`.

---

### Task 1: Helper pagination.php dan wiring config

**Files:**
- Create: `pagination.php`
- Modify: `admin-config.php`
- Modify: `portal-config.php`

**Interfaces:**
- `halamanSaatIni(): int`
- `ambilPaginasi(PDO $pdo, string $sqlBase, string $sqlCount, array $params, int $perHalaman = PER_HALAMAN): array`
- Hasil: `baris`, `hal`, `totalHal`, dan `total`
- `tampilPaginasi(int $hal, int $totalHal, array $queryTambahan = []): void`

- [ ] **Step 1:** Buat helper COUNT, clamp halaman, LIMIT/OFFSET, dan pengambilan baris.
- [ ] **Step 2:** Render tombol Previous, nomor halaman, dan Next; jangan render bila hanya satu halaman.
- [ ] **Step 3:** Require helper dari config admin dan portal.
- [ ] **Step 4:** Lint dan commit helper.

---

### Task 2: Pelanggan, paginasi dan cari GET

**Files:**
- Modify: `admin/pelanggan.php`

- [ ] **Step 1:** Baca `?cari=` dan cari case-insensitive pada nama atau ID pelanggan.
- [ ] **Step 2:** Query pelanggan dan paket dengan COUNT/WHERE yang sama.
- [ ] **Step 3:** Render form GET, baris halaman aktif, empty state, dan nav yang mempertahankan `cari`.
- [ ] **Step 4:** Pertahankan form edit/toggle, lalu hapus JS pencarian sisi-klien.
- [ ] **Step 5:** Lint; uji `?hal=2` dan pencarian; commit.

---

### Task 3: Transaksi, paginasi dan filter status GET

**Files:**
- Modify: `admin/transaksi.php`

- [ ] **Step 1:** Baca `?status=` dengan nilai valid `lunas`, `menunggu`, atau kosong.
- [ ] **Step 2:** Query tagihan beserta pelanggan dan paket, termasuk `idTagihan`.
- [ ] **Step 3:** Render filter link GET, baris, empty state, dan nav yang mempertahankan status.
- [ ] **Step 4:** Pertahankan form Tandai Lunas dan hapus JS filter sisi-klien.
- [ ] **Step 5:** Lint; uji navigasi dan filter; commit.

---

### Task 4: Notifikasi, paginasi dan filter status GET

**Files:**
- Modify: `admin/notifikasi.php`
- Modify: `admin-config.php`

- [ ] **Step 1:** Hapus query `$daftarNotifikasi` dari config karena hanya dipakai halaman daftar.
- [ ] **Step 2:** Baca `?status=` dengan nilai valid `terkirim`, `draft`, atau kosong.
- [ ] **Step 3:** Query notifikasi langsung dari halaman dengan COUNT/WHERE yang sama.
- [ ] **Step 4:** Render filter link GET, tabel, empty state, dan nav; pertahankan form tulis/hapus.
- [ ] **Step 5:** Lint; uji filter draft; commit.

---

### Task 5: Portal riwayat transaksi

**Files:**
- Modify: `portal/transaksi.php`

- [ ] **Step 1:** Query tagihan hanya untuk pelanggan dari sesi, urutkan ID terbaru, lalu paginasi.
- [ ] **Step 2:** Render total transaksi, kartu transaksi, empty state, dan nav.
- [ ] **Step 3:** Pertahankan link invoice serta format rupiah/status.
- [ ] **Step 4:** Lint; login sebagai pelanggan demo dan verifikasi empat transaksi tetap tampil tanpa nav satu halaman; commit.

---

## Catatan Penutup

Setelah lima task, tabel admin Pelanggan, Transaksi, dan Notifikasi serta riwayat transaksi portal dipaginasi sisi-server. Dashboard dan kartu paket tidak berubah.

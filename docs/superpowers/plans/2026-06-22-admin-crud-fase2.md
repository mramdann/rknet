# Admin CRUD Fase 2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menjadikan semua aksi tulis admin nyata (INSERT/UPDATE/DELETE) dengan CSRF, flash, dan Post-Redirect-Get.

**Architecture:** `aksi.php` menyediakan CSRF dan flash. Tiap halaman daftar mem-POST form ke handler `admin/aksi-<entitas>.php` yang menjalankan `wajibLoginAdmin()` dan `cekCsrf()`, menulis ke DB dengan prepared statement, memanggil `setFlash()`, lalu redirect balik. Flash ditampilkan di `admin/partials/shell-open.php`.

**Tech Stack:** PHP native (PDO prepared statements, session CSRF, `password_hash`), MySQL `dbrknet` pada port 3382.

## Global Constraints

- Semua aksi memakai POST lalu redirect (PRG).
- Setiap form memiliki token dari `tokenCsrf()`; handler memverifikasi dengan `cekCsrf()`.
- Flash sukses/gagal disimpan di session dan tampil sekali.
- Semua query berparameter memakai prepared statement; output di-`htmlspecialchars()`.
- Paket dan notifikasi dapat dihapus dengan konfirmasi `confirm()`; pelanggan hanya dapat di-toggle aktif/nonaktif.
- Hapus paket terpakai harus menangkap `PDOException` dan menampilkan flash danger.
- Perubahan password admin memverifikasi password lama lalu menyimpan hash baru.
- Kode dan komentar domain memakai Bahasa Indonesia. Lint setiap `.php` yang disentuh.
- Verifikasi dengan lint, PowerShell HTTP, pemeriksaan DB, dan BrowserOS. Commit per task dengan prefix `feat(crud):`.

---

### Task 1: Infrastruktur aksi, flash, dan ID data

**Files:**
- Create: `aksi.php`
- Modify: `admin-config.php`
- Modify: `admin/partials/shell-open.php`

**Interfaces:**
- `tokenCsrf(): string`
- `cekCsrf(): void`
- `setFlash(string $tipe, string $pesan): void`
- `tampilFlash(): void`
- Query config menyediakan `$daftarPaket[].id`, `$daftarNotifikasi[].id`, `$daftarTagihan[].idTagihan`, dan `$daftarPelanggan[].alamat`.

- [ ] **Step 1:** Buat helper CSRF dan flash di `aksi.php`; gunakan `random_bytes(32)`, `hash_equals`, dan escape pesan flash.
- [ ] **Step 2:** Require `aksi.php` dari `admin-config.php`.
- [ ] **Step 3:** Tambahkan ID paket, ID notifikasi, ID tagihan, dan alamat pelanggan ke query terkait.
- [ ] **Step 4:** Panggil `tampilFlash()` di awal konten `admin/partials/shell-open.php`.
- [ ] **Step 5:** Lint ketiga file dan buka halaman transaksi setelah login untuk memastikan config tetap valid.
- [ ] **Step 6:** Commit infrastruktur.

---

### Task 2: Paket CRUD

**Files:**
- Create: `admin/aksi-paket.php`
- Modify: `admin/paket.php`

- [ ] **Step 1:** Implementasikan aksi `tambah`, `edit`, dan `hapus` untuk nama, kecepatan, harga, dan status.
- [ ] **Step 2:** Validasi field wajib dan harga numerik di server.
- [ ] **Step 3:** Tangkap kegagalan FK saat menghapus paket yang masih digunakan.
- [ ] **Step 4:** Ubah modal tambah/edit menjadi form POST dengan hidden `csrf`, `aksi`, dan `id`.
- [ ] **Step 5:** Tambahkan form hapus per kartu dengan konfirmasi.
- [ ] **Step 6:** Lint, uji tambah paket melalui HTTP, cek hasil, lalu bersihkan data uji.
- [ ] **Step 7:** Commit Paket CRUD.

---

### Task 3: Notifikasi tambah/hapus

**Files:**
- Create: `admin/aksi-notifikasi.php`
- Modify: `admin/notifikasi.php`

- [ ] **Step 1:** Implementasikan aksi `tambah` dengan judul, isi, target, tanggal tampilan hari ini, dan status `terkirim`.
- [ ] **Step 2:** Implementasikan aksi `hapus` berdasarkan ID.
- [ ] **Step 3:** Ubah modal tulis menjadi form POST dengan CSRF.
- [ ] **Step 4:** Tambahkan kolom Aksi dan form hapus per baris.
- [ ] **Step 5:** Hapus JS submit mock, tetapi pertahankan filter status yang masih digunakan.
- [ ] **Step 6:** Lint, uji tambah notifikasi, cek hasil, lalu bersihkan data uji.
- [ ] **Step 7:** Commit aksi Notifikasi.

---

### Task 4: Pelanggan edit dan toggle status

**Files:**
- Create: `admin/aksi-pelanggan.php`
- Modify: `admin/pelanggan.php`

- [ ] **Step 1:** Implementasikan aksi `edit` untuk nama, email, hp, dan alamat berdasarkan ID pelanggan.
- [ ] **Step 2:** Implementasikan aksi `toggle` untuk status `aktif` dan `nonaktif`.
- [ ] **Step 3:** Ubah modal detail menjadi form edit POST dengan CSRF dan ID hidden.
- [ ] **Step 4:** Tambahkan form toggle status dan pertahankan data atribut yang mengisi modal.
- [ ] **Step 5:** Lint, uji perubahan pelanggan melalui HTTP, cek DB, lalu kembalikan data seed.
- [ ] **Step 6:** Commit aksi Pelanggan.

---

### Task 5: Transaksi tandai lunas

**Files:**
- Create: `admin/aksi-transaksi.php`
- Modify: `admin/transaksi.php`

- [ ] **Step 1:** Implementasikan aksi `lunas` yang mengubah status tagihan berdasarkan ID.
- [ ] **Step 2:** Ubah tombol Tandai Lunas menjadi form POST dengan CSRF dan `idTagihan`.
- [ ] **Step 3:** Hapus listener JS mock dan pertahankan filter status.
- [ ] **Step 4:** Lint, uji perubahan status, cek DB, lalu kembalikan status seed.
- [ ] **Step 5:** Commit aksi Transaksi.

---

### Task 6: Pengaturan profil, password, dan situs

**Files:**
- Create: `admin/aksi-pengaturan.php`
- Modify: `admin/pengaturan.php`

- [ ] **Step 1:** Implementasikan aksi `profil` untuk nama dan email admin yang sedang login.
- [ ] **Step 2:** Implementasikan aksi `password`: verifikasi password lama, validasi panjang/konfirmasi, lalu simpan `password_hash()` baru.
- [ ] **Step 3:** Implementasikan aksi `situs` untuk nama situs, email, telepon, dan alamat pada baris pengaturan.
- [ ] **Step 4:** Ubah ketiga form menjadi POST dengan CSRF dan nilai `aksi` masing-masing.
- [ ] **Step 5:** Lint, uji perubahan info situs, cek DB, lalu kembalikan nilai seed.
- [ ] **Step 6:** Commit aksi Pengaturan.

---

## Catatan Penutup

Setelah enam task, seluruh aksi admin yang dipertahankan berjalan nyata dengan CSRF, flash, dan PRG. Sub-proyek berikutnya adalah Portal write untuk edit profil dan pilih/ubah paket.

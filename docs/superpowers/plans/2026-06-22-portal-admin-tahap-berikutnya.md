# Portal Admin Tahap Berikutnya Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Melengkapi portal admin RKnet dengan dua modul UI-only: Notifikasi dan Pengaturan, plus sidebar bergrup.

**Architecture:** Setiap halaman mengikuti pola shell admin yang sudah ada (`require admin-config.php` -> set `$judulHalaman`/`$menuAktif` -> include `shell-head`/`shell-open`/konten/`shell-close`). Data dummy ditambahkan ke `admin-config.php`; badge status notifikasi ditangani `badgeStatus()` di `helpers.php`. Tidak ada backend; aksi Tulis Notifikasi dan Simpan adalah mock JS/form.

**Tech Stack:** PHP native (XAMPP, port 8282), Bootstrap 5.3 + Bootstrap Icons (CDN), `assets/css/portal.css`. Tanpa framework/Composer/database.

## Global Constraints

- UI only: tidak ada auth nyata, database, atau pemrosesan form/aksi. Semua tombol = mock.
- Bahasa Indonesia untuk nama variabel/fungsi domain dan komentar. Keyword framework tetap Inggris.
- Escape semua output dinamis dengan `htmlspecialchars()`.
- Lint tiap `.php` yang disentuh: `/d/WebServer/xampp82/php/php.exe -l <file>`.
- Verifikasi di `http://localhost:8282/rknet/admin/<file>.php`.
- Setelah edit `assets/css/portal.css`, bump `?v=N` di setiap head yang merujuknya.
- Setiap task diakhiri commit dengan prefix `feat(admin):`.

---

### Task 1: Sidebar bergrup + CSS label

Ubah sidebar admin menjadi dua grup berlabel, UTAMA dan LAINNYA, dengan enam menu. Menu UTAMA berisi Dashboard, Pelanggan, Paket, dan Transaksi; menu LAINNYA berisi Notifikasi dan Pengaturan.

**Files:**
- Modify: `admin/partials/sidebar.php`
- Modify: `assets/css/portal.css`
- Modify: `admin/partials/shell-head.php`
- Modify: `portal/partials/shell-head.php`

**Interfaces:**
- Produces: kunci `$menuAktif` baru `'notifikasi'` dan `'pengaturan'`.

- [ ] **Step 1:** Ubah `$menuAdmin` menjadi struktur bergrup dan render loop grup/item.
- [ ] **Step 2:** Tambahkan `.portal-nav-label` untuk label uppercase kecil di sidebar.
- [ ] **Step 3:** Bump cache-bust `portal.css` di kedua shell head.
- [ ] **Step 4:** Lint semua PHP yang disentuh.
- [ ] **Step 5:** Verifikasi enam item, dua label grup, dan sorotan menu aktif di browser.
- [ ] **Step 6:** Commit sidebar, CSS, dan cache-bust.

---

### Task 2: Modul Notifikasi

Buat halaman Notifikasi: tabel judul, target, tanggal, dan badge status; filter Terkirim/Draft; serta modal mock "Tulis Notifikasi".

**Files:**
- Modify: `helpers.php` untuk status `terkirim|draft`
- Modify: `admin-config.php` untuk `$daftarNotifikasi`
- Create: `admin/notifikasi.php`

**Data contract:** `$daftarNotifikasi` berisi `judul`, `isi`, `target`, `tanggal`, dan `status`. Status valid adalah `terkirim` atau `draft`.

- [ ] **Step 1:** Tambahkan mapping `terkirim` dan `draft` ke `badgeStatus()` tanpa mengubah mapping lama.
- [ ] **Step 2:** Tambahkan minimal lima notifikasi dummy di `admin-config.php`, termasuk pengumuman pemeliharaan di Depok, promo paket, pengingat tagihan, sambutan pelanggan, dan survei.
- [ ] **Step 3:** Buat `admin/notifikasi.php` memakai shell standar, tabel responsif, filter status sisi-klien, dan modal tulis.
- [ ] **Step 4:** Escape seluruh data dinamis dan tampilkan empty state bila filter tidak memiliki hasil.
- [ ] **Step 5:** Lint `helpers.php`, `admin-config.php`, dan `admin/notifikasi.php`.
- [ ] **Step 6:** Verifikasi tabel, filter, modal, dan pesan sukses mock di browser.
- [ ] **Step 7:** Commit modul Notifikasi.

---

### Task 3: Modul Pengaturan

Buat halaman Pengaturan dengan tiga kartu form: Profil Admin, Ubah Password, dan Informasi Situs. Pada tahap UI ini semua submit hanya me-reload halaman.

**Files:**
- Modify: `admin-config.php` untuk `$pengaturan`
- Create: `admin/pengaturan.php`

**Data contract:** `$pengaturan` berisi `namaSitus`, `email`, `telepon`, dan `alamat`; profil admin memakai `$admin` yang sudah ada.

- [ ] **Step 1:** Tambahkan `$pengaturan` di `admin-config.php` dengan identitas RKnet.
- [ ] **Step 2:** Buat `admin/pengaturan.php` memakai shell standar.
- [ ] **Step 3:** Tampilkan form Profil Admin dengan nama, email, dan peran readonly.
- [ ] **Step 4:** Tampilkan form Ubah Password dengan password saat ini, password baru, dan konfirmasi.
- [ ] **Step 5:** Tampilkan form Informasi Situs dengan nama situs, email CS, telepon, dan alamat.
- [ ] **Step 6:** Lint `admin-config.php` dan `admin/pengaturan.php`.
- [ ] **Step 7:** Verifikasi seluruh nilai config, field readonly, dan tombol simpan di browser.
- [ ] **Step 8:** Commit modul Pengaturan.

---

## Catatan Penutup

Setelah tiga task selesai, sidebar LAINNYA terhubung ke Notifikasi dan Pengaturan. Tidak ada perubahan pada modul inti selain sidebar.

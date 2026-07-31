# Rebrand RKnet — Design

**Date:** 2026-06-23
**Status:** Approved design
**Scope:** Pastikan semua rujukan brand menggunakan "RKnet" — teks brand, kelas/var CSS `st-`→`rk-`, logo, nama database, email, dan folder/URL `/rknet/`.

## Goal

Mengganti identitas brand aplikasi menjadi RKnet secara menyeluruh: yang terlihat pengguna (teks, logo) dan yang internal yang diminta (kelas CSS, nama DB, email/login).

## Keputusan (disetujui)

- **Teks brand:** "RKnet"/"RKnet Indonesia" untuk nama paket, judul, alt, legal/footer/hero. Badan hukum **PT Integrasi Jaringan Ekosistem** dibiarkan karena bukan nama brand.
- **Kelas & variabel CSS:** `st-`→`rk-` untuk token turunan brand saja (bukan `stat-`/`list-`/`text-start`).
- **Logo:** `assets/img/rknet.jpeg` (logo utama RKnet) digunakan di semua tempat; `assets/img/rknet2.jpeg` (logo RWS/perusahaan) menggantikan `logo-weave.webp` di slot kedua (header login/portal). Kedua JPEG latar putih → di footer (latar gelap) logo diberi **chip putih membulat**.
- **Database:** `dbrknet` (buat DB baru + re-import; DB lama boleh ditinggal).
- **Email/login:** `@rknet.id`; login admin menjadi **`admin@rknet.id` / `admin123`**. Email pelanggan (gmail) dibiarkan.
- **Folder/URL:** `/rknet/`.

## A. Teks Brand (user-visible)

Gunakan "RKnet" di file non-CSS/non-data: `config.php` (`$site['name']`), `legal.php`, `partials/*` (navbar, footer, hero, features, redeem, modal-langganan, head), `admin/partials/shell-head.php` (title "Admin RKnet"), `portal/partials/shell-head.php` ("Portal Pelanggan RKnet"), `admin/login.php`/`portal/login.php` (judul), `admin/dashboard.php`, `admin/paket.php`, `portal/invoice.php`, sidebar brand alt. "RKnet Indonesia" digunakan untuk nama lengkap brand.

Data brand (di DB & seed): `paket.nama` "Paket X Mbps RKnet"; `pengaturan.nama_situs` "RKnet Indonesia". (Ditangani di Task DB via seed + UPDATE DB berjalan.)

## B. Kelas & Variabel CSS (st- → rk-)

Token yang diganti (regex batas-kata agar aman dari `text-start`, `:first-child`, `stat-`):

| Dari | Ke |
|---|---|
| `--st-` (prefix var: `--st-blue`, `--st-blue-dark`, `--st-blue-soft`, `--st-cyan`) | `--rk-` |
| `\bbtn-st\b` | `btn-rk` |
| `\btext-st\b` | `text-rk` |
| `\bst-navbar\b` | `rk-navbar` |
| `\bst-hero\b` (mencakup `st-hero-img`) | `rk-hero` |
| `\bst-section-soft\b` | `rk-section-soft` |
| `\bst-badge\b` | `rk-badge` |
| `\bst-modal-head\b` | `rk-modal-head` |
| `\bst-footer\b` | `rk-footer` |

Berlaku di `assets/css/style.css`, `assets/css/portal.css`, dan semua template yang memakainya. Setelah edit CSS, **bump `?v=`** di semua head (`partials/head.php`, `admin/partials/shell-head.php`, `portal/partials/shell-head.php`, dan yang lain yang merujuk style.css/portal.css).

**Larangan:** jangan mengganti `stat-*` (statistik), `list-*`, `text-start`/`text-sm-*`, `:first-child`, `input-group-text`. Verifikasi setelahnya: grep memastikan tak ada token rusak seperti `text-rkart`, `lirk-`, `firk-`.

## C. Logo

- Referensi logo (di navbar, footer, sidebar admin & portal, login admin & portal, invoice, legal) menggunakan `assets/img/rknet.jpeg` dan `alt="RKnet"`.
- Referensi `assets/img/logo-weave.webp` (slot kedua di header login & portal sidebar) → `assets/img/rknet2.jpeg`. `alt` → `alt="RWS Solution"`.
- **Footer** (latar `--rk-blue-dark`): bungkus logo dalam chip putih (`background:#fff;border-radius;padding`) agar JPEG latar-putih tampak disengaja.
- Ukuran (`height`) dipertahankan seperti sekarang; JPEG square/landscape tetap dibatasi tinggi.
- File lama `logo-weave.webp` boleh ditinggalkan (tak dirujuk lagi).

## D. Database & Email

- `db.php`: `const DB_NAME = 'dbrknet';` + komentar; pesan error sebut `dbrknet`.
- `database/schema.sql`: `CREATE DATABASE ... dbrknet; USE dbrknet;` + komentar.
- `database/seed.sql`: `USE dbrknet;`; `pengaturan.nama_situs`→'RKnet Indonesia'; `pengaturan.email`→'cs@rknet.id'; `admin.email`→'admin@rknet.id'; `paket.nama`→'…RKnet'.
- `database/dump-seed.ps1`: gunakan `dbrknet`.
- `admin/login.php`: placeholder & hint demo → `admin@rknet.id`.
- `admin-config.php`/`portal-config.php`: komentar menggunakan `dbrknet`.
- **Buat DB `dbrknet`** lalu import `schema.sql` + `seed.sql` (via PowerShell).

## E. Dokumentasi

`CLAUDE.md`, `docs/DOKUMENTASI.md`, memori proyek: RKnet, dbrknet, kredensial demo admin `admin@rknet.id`.

## Verifikasi

1. `php -l` tiap `.php`; grep memastikan tak ada referensi brand lama tersisa, dan tak ada token CSS rusak.
2. Buat & isi `dbrknet`; jalankan **E2E 39-cek** dengan login admin **`admin@rknet.id`**.
3. BrowserOS spot-check: landing (navbar/footer/hero), login (dua logo), admin & portal dashboard — brand & logo tampil benar.

## Out of Scope

- Rename folder proyek/URL `/rknet/`.
- Mengubah nama badan hukum PT Integrasi Jaringan Ekosistem.
- Mengonversi/meng-crop file logo (dipakai apa adanya; chip putih untuk footer).

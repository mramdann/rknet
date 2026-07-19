# Rebrand Starlite → RKnet — Design

**Date:** 2026-06-23
**Status:** Approved design
**Scope:** Ubah semua rujukan "Starlite" menjadi "RKnet" — teks brand, kelas/var CSS `st-`→`rk-`, logo, nama database, dan email. Folder/URL `/starlite/` tidak diubah.

## Goal

Mengganti identitas brand aplikasi dari Starlite ke RKnet secara menyeluruh: yang terlihat pengguna (teks, logo) dan yang internal yang diminta (kelas CSS, nama DB, email/login).

## Keputusan (disetujui)

- **Teks brand:** "Starlite"/"Starlite Indonesia" → "RKnet"/"RKnet Indonesia"; nama paket "…Starlite" → "…RKnet"; judul, alt, legal/footer/hero. Badan hukum **PT Integrasi Jaringan Ekosistem** dibiarkan (bukan "starlite").
- **Kelas & variabel CSS:** `st-`→`rk-` untuk token turunan Starlite saja (bukan `stat-`/`list-`/`text-start`).
- **Logo:** `assets/img/rknet.jpeg` (logo utama RKnet) menggantikan `logo-starlite.webp` di semua tempat; `assets/img/rknet2.jpeg` (logo RWS/perusahaan) menggantikan `logo-weave.webp` di slot kedua (header login/portal). Kedua JPEG latar putih → di footer (latar gelap) logo diberi **chip putih membulat**.
- **Database:** `dbstarlite` → `dbrknet` (buat DB baru + re-import; DB lama boleh ditinggal).
- **Email/login:** `@starlite.id` → `@rknet.id`; login admin jadi **`admin@rknet.id` / `admin123`**. Email pelanggan (gmail) dibiarkan.
- **Folder/URL** `/starlite/` **tetap**.

## A. Teks Brand (user-visible)

Ganti "Starlite" → "RKnet" di file non-CSS/non-data (grep case-insensitive, ~60 titik): `config.php` (`$site['name']`), `legal.php`, `partials/*` (navbar, footer, hero, features, redeem, coverage, modal-langganan, head), `cek-jangkauan.php`, `cek-jangkauan-config.php`, `cek-jangkauan.js`, `admin/partials/shell-head.php` (title "Admin Starlite"), `portal/partials/shell-head.php` ("Portal Pelanggan Starlite"), `admin/login.php`/`portal/login.php` (judul), `admin/dashboard.php`, `admin/paket.php`, `portal/invoice.php`, sidebar brand alt. "Starlite Indonesia" → "RKnet Indonesia".

Data brand (di DB & seed): `paket.nama` "Paket X Mbps Starlite" → "…RKnet"; `pengaturan.nama_situs` "Starlite Indonesia" → "RKnet Indonesia". (Ditangani di Task DB via seed + UPDATE DB berjalan.)

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

Berlaku di `assets/css/style.css`, `assets/css/portal.css`, dan semua template yang memakainya (~28 file). Setelah edit CSS, **bump `?v=`** di semua head (`partials/head.php`, `admin/partials/shell-head.php`, `portal/partials/shell-head.php`, `cek-jangkauan.php`, dan yang lain yang merujuk style.css/portal.css).

**Larangan:** jangan mengganti `stat-*` (statistik), `list-*`, `text-start`/`text-sm-*`, `:first-child`, `input-group-text`. Verifikasi setelahnya: grep memastikan tak ada token rusak seperti `text-rkart`, `lirk-`, `firk-`.

## C. Logo

- Referensi `assets/img/logo-starlite.webp` (di navbar, footer, sidebar admin & portal, login admin & portal, invoice, cek-jangkauan, legal) → `assets/img/rknet.jpeg`. `alt="Starlite"` → `alt="RKnet"`.
- Referensi `assets/img/logo-weave.webp` (slot kedua di header login & portal sidebar) → `assets/img/rknet2.jpeg`. `alt` → `alt="RWS Solution"`.
- **Footer** (latar `--rk-blue-dark`): bungkus logo dalam chip putih (`background:#fff;border-radius;padding`) agar JPEG latar-putih tampak disengaja.
- Ukuran (`height`) dipertahankan seperti sekarang; JPEG square/landscape tetap dibatasi tinggi.
- File lama `logo-starlite.webp`/`logo-weave.webp` boleh ditinggalkan (tak dirujuk lagi).

## D. Database & Email

- `db.php`: `const DB_NAME = 'dbrknet';` + komentar; pesan error sebut `dbrknet`.
- `database/schema.sql`: `CREATE DATABASE ... dbrknet; USE dbrknet;` + komentar.
- `database/seed.sql`: `USE dbrknet;`; `pengaturan.nama_situs`→'RKnet Indonesia'; `pengaturan.email`→'cs@rknet.id'; `admin.email`→'admin@rknet.id'; `paket.nama`→'…RKnet'.
- `database/dump-seed.ps1`: `dbstarlite`→`dbrknet`.
- `admin/login.php`: placeholder & hint demo → `admin@rknet.id`.
- `admin-config.php`/`portal-config.php`: komentar `dbstarlite`→`dbrknet`.
- **Buat DB `dbrknet`** lalu import `schema.sql` + `seed.sql` (via PowerShell).

## E. Dokumentasi

`CLAUDE.md`, `docs/DOKUMENTASI.md`, memori proyek: Starlite→RKnet, dbstarlite→dbrknet, kredensial demo admin→`admin@rknet.id`.

## Verifikasi

1. `php -l` tiap `.php`; grep memastikan tak ada `Starlite`/`logo-starlite`/`@starlite`/`dbstarlite` tersisa (kecuali spec/plan historis di `docs/superpowers/`), dan tak ada token CSS rusak.
2. Buat & isi `dbrknet`; jalankan **E2E 39-cek** dengan login admin **`admin@rknet.id`**.
3. BrowserOS spot-check: landing (navbar/footer/hero), login (dua logo), admin & portal dashboard — brand & logo tampil benar.

## Out of Scope

- Rename folder proyek/URL `/starlite/`.
- Mengubah nama badan hukum PT Integrasi Jaringan Ekosistem.
- Mengonversi/meng-crop file logo (dipakai apa adanya; chip putih untuk footer).

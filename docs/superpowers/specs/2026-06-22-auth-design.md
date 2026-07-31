# Auth RKnet — Design (Fase 2, Sub-proyek 1)

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Login nyata + session + proteksi halaman + logout untuk admin & pelanggan. Sub-proyek pertama dari Fase 2 (CRUD nyata). Aksi tulis CRUD = sub-proyek berikutnya.

## Goal

Mengganti login mock menjadi autentikasi nyata: verifikasi email+password ke database, session PHP, proteksi semua halaman ber-data, dan logout. Admin & pelanggan punya sesi terpisah.

## Constraints & Decisions

- Login admin & pelanggan **nyata** via **email + password**, diverifikasi dengan `password_verify()` ke DB.
- Tabel `pelanggan` ditambah kolom `kata_sandi VARCHAR(255)`; di-seed hash password demo.
- Session admin & pelanggan **terpisah** (key session beda) → bisa login di dua area sekaligus.
- **Guard lewat rantai config**: `admin-config.php`/`portal-config.php` memanggil guard di awal; halaman login tidak memuat config sehingga tetap bisa diakses.
- `$idPelanggan` di portal jadi **id pelanggan yang login** (bukan hardcode `RKNET-2024-008812`). `$admin` di admin diambil berdasarkan id session (bukan `LIMIT 1`).
- Login gagal → pesan generik di form (tidak membocorkan field mana yang salah). Akses tanpa login → redirect ke login.
- Kredensial demo ditampilkan sebagai hint di halaman login.
- **Tanpa** CSRF token (login low-risk; CSRF dibahas di sub-proyek Admin CRUD), **tanpa** registrasi/reset password (YAGNI).
- Kode & komentar Bahasa Indonesia. Lint tiap `.php`. Perlu re-import DB (ALTER + seed password).

## Struktur File

```
auth.php                    # baru — sesi, login/logout, guard (admin & pelanggan)
database/schema.sql         # ubah — tambah kolom kata_sandi di pelanggan
database/seed.sql           # ubah — seed kata_sandi pelanggan
admin-config.php            # ubah — require auth, wajibLoginAdmin(), $admin by id session
portal-config.php           # ubah — wajibLoginPelanggan(), $idPelanggan dari session
admin/login.php             # ubah — proses POST login nyata
portal/login.php            # ubah — proses POST login nyata
admin/logout.php            # baru — hapus sesi admin, redirect login
portal/logout.php           # baru — hapus sesi pelanggan, redirect login
admin/partials/sidebar.php  # ubah — link "Keluar" → logout.php
portal/partials/*           # ubah — link "Keluar" → logout.php (di shell/sidebar/topbar yang memuatnya)
```

## Komponen: auth.php

Fungsi (Bahasa Indonesia), semua memanggil `mulaiSesi()` lebih dulu bila perlu:

- `mulaiSesi(): void` — `session_start()` bila belum aktif.
- `loginAdmin(int $id): void` — set `$_SESSION['admin_id']`.
- `loginPelanggan(string $id): void` — set `$_SESSION['pelanggan_id']`.
- `idAdminSaatIni(): ?int` — kembalikan `$_SESSION['admin_id']` atau null.
- `idPelangganSaatIni(): ?string` — kembalikan `$_SESSION['pelanggan_id']` atau null.
- `wajibLoginAdmin(): void` — bila tak ada session admin → `header('Location: login.php')` + `exit`.
- `wajibLoginPelanggan(): void` — idem untuk pelanggan.
- `logoutAdmin(): void` / `logoutPelanggan(): void` — hapus key session terkait.

Catatan: guard memakai path relatif `login.php` karena dipanggil dari `admin/*.php` & `portal/*.php` (login.php ada di folder yang sama). Config (`admin-config.php`) di-`require` dari `admin/*.php`, jadi redirect relatif benar.

## Data Flow

**Login admin** (`admin/login.php`):
1. Form POST (email, password) ke `login.php`.
2. `require db.php` + `auth.php`. Query `SELECT id, kata_sandi FROM admin WHERE email = ?`.
3. `password_verify($passwordInput, $row['kata_sandi'])` → cocok: `loginAdmin($row['id'])` + redirect `dashboard.php`. Gagal: set `$pesanError`.
4. Render form (dengan `$pesanError` bila ada). Login.php **tidak** memuat `admin-config.php`.

**Login pelanggan** (`portal/login.php`): identik, tabel `pelanggan`, redirect `dashboard.php`.

**Akses halaman ber-data**: `admin/<x>.php` → `require admin-config.php` → di dalamnya `require auth.php; wajibLoginAdmin();` → bila belum login, redirect; bila sudah, lanjut query (`$admin` by `idAdminSaatIni()`).

**Logout**: klik "Keluar" → `logout.php` → `logoutAdmin()`/`logoutPelanggan()` → redirect `login.php`.

## Perubahan DB

`schema.sql`: tambah `kata_sandi VARCHAR(255) NOT NULL` pada tabel `pelanggan` (setelah `status` atau di akhir kolom).

`seed.sql`: setiap baris `INSERT INTO pelanggan` menyertakan `kata_sandi` = hash demo. Semua pelanggan pakai password demo `pelanggan123` (hash dibuat via `password_hash('pelanggan123', PASSWORD_DEFAULT)` saat implementasi, ditulis literal di seed). Admin tetap `admin123` (sudah ada).

Re-import: jalankan ulang `schema.sql` + `seed.sql` (drop & recreate) — dilakukan via PowerShell/phpMyAdmin.

## Perubahan config

`admin-config.php` (tambah di atas, sebelum query):
```php
require_once __DIR__ . '/auth.php';
wajibLoginAdmin();
```
lalu `$admin = SELECT nama, email, peran FROM admin WHERE id = ?` (id = `idAdminSaatIni()`).

`portal-config.php`:
```php
require_once __DIR__ . '/auth.php';
wajibLoginPelanggan();
$idPelanggan = idPelangganSaatIni();
```
(query lain tetap, hanya sumber id berubah dari hardcode ke session.)

## Error Handling

- Login gagal (email tak ada / password salah) → satu pesan generik "Email atau kata sandi salah." di form.
- Akses tanpa login → redirect ke `login.php` (tidak menampilkan konten).
- DB belum siap → pesan dari `db.php` (sudah ada).

## Out of Scope

- CSRF token, INSERT/UPDATE/DELETE (sub-proyek 2 Admin CRUD).
- Edit profil & pilih paket portal (sub-proyek 3 Portal write).
- Registrasi mandiri, reset/lupa password, "ingat saya", rate-limit.
- Hashing ulang admin (sudah ber-hash).

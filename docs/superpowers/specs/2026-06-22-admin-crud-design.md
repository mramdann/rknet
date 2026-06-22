# Admin CRUD Starlite — Design (Fase 2, Sub-proyek 2)

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Menjadikan aksi tulis admin nyata (INSERT/UPDATE/DELETE) dengan CSRF, flash message, dan pola Post-Redirect-Get. Lanjutan dari Auth (sub-1). Aksi portal (profil, pilih paket) = sub-proyek 3.

## Goal

Mengubah semua aksi admin yang masih mock menjadi operasi DB nyata: tambah/edit/hapus paket, area, notifikasi; edit & toggle status pelanggan; tandai lunas tagihan; tandai dihubungi lead; simpan pengaturan (profil admin, ubah password, info situs). Aman (CSRF + guard login) dan ramah (flash + PRG).

## Constraints & Decisions

- Semua aksi lewat **POST** ke handler khusus, lalu **redirect** balik ke halaman list (PRG) untuk cegah double-submit.
- **CSRF**: setiap form POST menyertakan token; handler verifikasi dengan `hash_equals`. Gagal → tolak.
- **Flash message**: hasil aksi (sukses/gagal) disimpan di session, ditampilkan sekali di atas konten admin.
- Handler diletakkan di folder `admin/` (bukan subfolder) agar redirect relatif `login.php`/`<list>.php` benar.
- Query pakai **prepared statement** (PDO). Guard `wajibLoginAdmin()` di tiap handler.
- **Hapus**: paket, area, notifikasi punya tombol Hapus (konfirmasi `confirm()` JS). Pelanggan **tidak** dihapus (punya FK tagihan) — hanya toggle aktif/nonaktif.
- Hapus paket yang masih dipakai (FK `pelanggan.paket_id`/`tagihan.paket_id`) → tangkap `PDOException`, flash error.
- Ubah password admin pakai `password_hash()` + verifikasi password lama dengan `password_verify()`.
- Validasi dasar server-side (field wajib tak kosong, harga numerik). Output di-`htmlspecialchars()`.
- Kode & komentar Bahasa Indonesia. Lint tiap `.php`. Verifikasi via PowerShell HTTP + BrowserOS.

## Struktur File

```
aksi.php                       # baru — tokenCsrf(), cekCsrf(), setFlash(), tampilFlash()
admin/partials/shell-open.php  # ubah — panggil tampilFlash() di atas konten
admin/aksi-paket.php           # baru — tambah/edit/hapus paket
admin/aksi-area.php            # baru — tambah/edit/hapus area
admin/aksi-notifikasi.php      # baru — tambah/hapus notifikasi
admin/aksi-pelanggan.php       # baru — edit + toggle status pelanggan
admin/aksi-transaksi.php       # baru — tandai lunas
admin/aksi-lead.php            # baru — tandai dihubungi
admin/aksi-pengaturan.php      # baru — simpan profil / ubah password / info situs
admin/paket.php                # ubah — form modal jadi POST nyata + tombol hapus + id
admin/area.php                 # ubah — idem
admin/notifikasi.php           # ubah — form tulis POST nyata + tombol hapus + id
admin/pelanggan.php            # ubah — form edit + toggle POST nyata + id
admin/transaksi.php            # ubah — tombol Tandai Lunas jadi form POST + id tagihan
admin/lead.php                 # ubah — tombol Tandai Dihubungi jadi form POST + id prospek
admin/pengaturan.php           # ubah — 3 form jadi POST nyata
```

## Komponen: aksi.php

Memuat `auth.php` untuk `mulaiSesi()`. Fungsi:
- `tokenCsrf(): string` — kembalikan `$_SESSION['csrf']`, buat dengan `bin2hex(random_bytes(32))` bila belum ada.
- `cekCsrf(): void` — bila request POST dan `hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')` gagal → `http_response_code(403)` + `exit('CSRF token tidak valid.')`.
- `setFlash(string $tipe, string $pesan): void` — `$_SESSION['flash'] = ['tipe'=>$tipe, 'pesan'=>$pesan]` (tipe: `success`|`danger`).
- `tampilFlash(): void` — bila ada flash, echo alert Bootstrap (`alert-<tipe>`) dengan `htmlspecialchars($pesan)`, lalu `unset`.

## Pola Handler (semua sama)

```php
<?php
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$aksi = $_POST['aksi'] ?? '';
// ... switch($aksi): prepared statement write ...
setFlash('success', 'Pesan hasil.');
header('Location: <list>.php');
exit;
```
Akses GET langsung ke handler (tanpa POST) → cekCsrf lolos (karena hanya cek saat POST), lalu `$aksi` kosong → redirect balik tanpa efek. (Aman.)

## Aksi per Entitas

**Paket** (`aksi-paket.php` → `paket.php`):
- `tambah`: INSERT (nama, kecepatan, harga, status).
- `edit`: UPDATE WHERE id = ?.
- `hapus`: DELETE WHERE id = ? (tangkap FK error → flash danger).
- `paket.php`: modal tambah/edit jadi `<form method=post action=aksi-paket.php>` dengan hidden `csrf`, `aksi`, `id` (saat edit). Tiap kartu tambah form hapus kecil (`confirm()`).

**Area** (`aksi-area.php` → `area.php`): tambah/edit/hapus (nama, kota, status). Sama polanya; area tanpa FK.

**Notifikasi** (`aksi-notifikasi.php` → `notifikasi.php`):
- `tambah`: INSERT (judul, isi, target, tanggal = tanggal hari ini string, status = 'terkirim').
- `hapus`: DELETE WHERE id = ?.
- `notifikasi.php`: tabel tambah kolom Aksi (tombol Hapus per baris), modal tulis jadi POST.

**Pelanggan** (`aksi-pelanggan.php` → `pelanggan.php`):
- `edit`: UPDATE nama, email, hp, alamat WHERE id = ?.
- `toggle`: UPDATE status = (aktif↔nonaktif) WHERE id = ?.
- `pelanggan.php`: modal detail dapat form edit nyata (field terisi via JS dari data-atribut) + tombol toggle status (form POST). id pelanggan (VARCHAR) jadi hidden field.

**Transaksi** (`aksi-transaksi.php` → `transaksi.php`):
- `lunas`: UPDATE status = 'lunas' WHERE id = ?.
- `transaksi.php`: tombol "Tandai Lunas" jadi `<form method=post>` dgn hidden `id` tagihan + `csrf`. (Hapus JS mock lama.) Perlu kolom `id` tagihan ikut di-query di `admin-config.php` (`t.id`).

**Lead** (`aksi-lead.php` → `lead.php`):
- `dihubungi`: UPDATE status = 'dihubungi' WHERE id = ?.
- `lead.php`: tombol "Tandai Dihubungi" jadi form POST dgn hidden id prospek + `csrf`. (Hapus JS mock.)

**Pengaturan** (`aksi-pengaturan.php` → `pengaturan.php`):
- `profil`: UPDATE admin SET nama, email WHERE id = (admin login).
- `password`: verifikasi password lama (`password_verify`) → bila cocok UPDATE kata_sandi = `password_hash(baru)`, else flash danger.
- `situs`: UPDATE pengaturan SET nama_situs, email, telepon, alamat WHERE id = 1.
- `pengaturan.php`: 3 form jadi POST nyata dgn `aksi` masing-masing + `csrf`.

## Perubahan admin-config.php

`$daftarTagihan` query tambah `t.id AS idTagihan` (dibutuhkan form tandai lunas). `$daftarPaket`, `$daftarArea`, `$daftarNotifikasi` query tambah `id` (dibutuhkan form edit/hapus). `$daftarLead` & `$daftarPelanggan` sudah punya `id`.

## Data Flow

Halaman list render form (hidden `csrf` = `tokenCsrf()`, `aksi`, `id`) → submit POST ke `aksi-<entitas>.php` → handler `wajibLoginAdmin()` + `cekCsrf()` → prepared statement → `setFlash()` → `header('Location: <entitas>.php')` → halaman list memuat ulang & `tampilFlash()` menampilkan hasil.

## Error Handling

- CSRF gagal → 403 "CSRF token tidak valid.".
- Field wajib kosong / harga bukan angka → flash danger + redirect balik (tanpa tulis).
- Password lama salah → flash danger "Password lama salah.".
- DELETE paket terpakai → tangkap `PDOException`, flash danger.
- Belum login → guard redirect ke login.

## Out of Scope

- Aksi portal pelanggan (edit profil, pilih paket) — sub-proyek 3.
- Hapus pelanggan (pakai toggle nonaktif).
- Hapus/edit tagihan & lead selain transisi status di atas.
- Pagination, audit log, validasi lanjutan (format email/regex ketat), upload berkas.

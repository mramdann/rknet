# Portal Write RKnet — Design (Fase 2, Sub-proyek 3)

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Menjadikan aksi tulis portal pelanggan nyata: edit profil (info akun + ganti password) dan pilih/ubah paket aktif. Sub-proyek terakhir Fase 2.

## Goal

Mengubah aksi portal yang masih mock menjadi operasi DB nyata untuk pelanggan yang sedang login: simpan perubahan info akun, ganti password, dan ubah paket aktif. Aman (CSRF + guard login) & ramah (flash + PRG). Reuse infrastruktur `aksi.php` dari sub-proyek Admin CRUD.

## Constraints & Decisions

- Aksi via POST ke handler `portal/aksi-<x>.php`, lalu redirect balik (PRG). Handler: `wajibLoginPelanggan()` + `cekCsrf()`.
- Semua operasi memakai id pelanggan dari sesi (`idPelangganSaatIni()`), bukan input klien.
- CSRF & flash reuse `aksi.php` (`tokenCsrf`, `cekCsrf`, `setFlash`, `tampilFlash`).
- Pilih paket = **UPDATE `pelanggan.paket_id`** ke paket terpilih (tanpa membuat tagihan).
- Ganti password pelanggan: verifikasi `kata_sandi` lama (`password_verify`) → `password_hash` baru (min 6 char, sama dengan konfirmasi).
- Query pakai prepared statement. Output di-`htmlspecialchars()`. Kode & komentar Bahasa Indonesia. Lint tiap `.php`.
- Verifikasi via PowerShell HTTP (login pelanggan → POST dgn CSRF → cek DB) + BrowserOS.

## Struktur File

```
portal-config.php             # ubah — require aksi.php; $paketTersedia query tambah id
portal/partials/shell-open.php # ubah — panggil tampilFlash() di atas konten
portal/aksi-profil.php        # baru — info akun + ganti password
portal/aksi-paket.php         # baru — pilih/ubah paket aktif
portal/profil.php             # ubah — 2 form jadi POST nyata
portal/paket.php              # ubah — kartu data-id + form Konfirmasi POST
```

## Pemetaan & Data Flow

**aksi-profil.php** (`$id = idPelangganSaatIni()`):
- `info`: UPDATE pelanggan SET nama, email, hp, alamat WHERE id = ?. Validasi nama/email/hp wajib.
- `password`: SELECT kata_sandi WHERE id = ? → `password_verify(lama)`; bila gagal flash danger; bila `strlen(baru) < 6` atau `baru !== konfirmasi` flash danger; else UPDATE kata_sandi = `password_hash(baru)`.
- Redirect `profil.php`.

**aksi-paket.php** (`$id = idPelangganSaatIni()`):
- `pilih`: validasi `paket_id` POST adalah angka & ada di tabel paket → UPDATE pelanggan SET paket_id = ? WHERE id = ?. Flash "Paket aktif berhasil diubah.". Redirect `paket.php`.

**portal-config.php**: tambah `require_once __DIR__ . '/aksi.php';`. Query `$paketTersedia` tambah `id`:
`SELECT id, nama, kecepatan, harga FROM paket ORDER BY id` (fitur & `dipilih` tetap dihitung di PHP; `dipilih` = id paket == paket_id pelanggan login, lebih akurat dari pencocokan kecepatan).

**portal/partials/shell-open.php**: panggil `tampilFlash()` tepat setelah `.portal-content` dibuka (seperti shell admin).

## Halaman

**profil.php**:
- Form **Info Akun**: `action=aksi-profil.php method=post`, hidden `csrf` + `aksi=info`; field name nama/email/hp/alamat (ID Pelanggan tetap readonly tanpa name). Tombol "Simpan Perubahan".
- Form **Keamanan/Ganti Password**: hidden `csrf` + `aksi=password`; field name lama/baru/konfirmasi. Tombol "Perbarui Password".

**paket.php**:
- Tiap kartu `.paket-pilih` tambah `data-id="<?= $p['id'] ?>"`.
- Ringkasan + tombol Konfirmasi dibungkus `<form method=post action=aksi-paket.php>` dengan hidden `csrf` + `aksi=pilih` + hidden `id` (`paketIdPilih`).
- JS `pilih(kartu)` yang ada diperluas: set `paketIdPilih.value = kartu.dataset.id`. Inisialisasi juga set id dari kartu terpilih awal. Tombol Konfirmasi jadi `type=submit` (bukan link).

## Error Handling

- CSRF gagal → 403 (dari `cekCsrf`).
- Field wajib kosong (info) → flash danger + redirect balik.
- Password lama salah / baru < 6 / tak sama konfirmasi → flash danger.
- `paket_id` tidak valid (bukan angka / tak ada) → flash danger, tanpa update.
- Belum login → guard redirect ke `login.php`.

## Out of Scope

- Pembuatan tagihan/invoice saat ganti paket.
- Keunikan email ketat / email sebagai identitas login (id pelanggan tetap PK).
- Pembatalan langganan, upgrade terjadwal, riwayat perubahan paket.

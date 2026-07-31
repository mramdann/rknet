# Portal Admin RKnet — UI Design (Inti)

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Portal admin (inti) — UI only, data dummy. Tampilan konsisten dengan portal pelanggan.

## Goal

Membangun portal admin RKnet untuk mengelola pelanggan, paket, dan transaksi, memakai PHP native + Bootstrap 5, gaya Modern Biru, reuse shell portal pelanggan.

## Constraints & Decisions

- Cakupan inti: Login admin + Dashboard + Pelanggan + Paket + Transaksi/Tagihan.
- Tampilan konsisten dengan portal pelanggan (reuse `assets/css/portal.css`).
- UI only: tombol Edit/Tandai Lunas/Toggle = aksi tampilan, data dummy.
- Kode: nama fungsi/variabel domain & komentar Bahasa Indonesia.
- `formatRupiah()` & `badgeStatus()` dipindah ke `helpers.php` (DRY, dipakai portal + admin).
- Akses via `admin/login.php`; link "Masuk sebagai Admin" di login pelanggan.
- Server: XAMPP port 8282.

## Struktur File

```
helpers.php             # formatRupiah(), badgeStatus() — dipakai bersama
admin-config.php        # data dummy: $admin, $statistik, $daftarPelanggan, $daftarPaket, $daftarTagihan
admin/
├── login.php
├── dashboard.php
├── pelanggan.php
├── paket.php
├── transaksi.php
└── partials/
    ├── shell-head.php
    ├── sidebar.php     # menu: Dashboard, Pelanggan, Paket, Transaksi
    ├── topbar.php      # nama admin + Keluar
    ├── shell-open.php
    └── shell-close.php
```

## Data Dummy (admin-config.php)

- `$admin` = [nama, email, peran]
- `$statistik` = [totalPelanggan, pelangganAktif, pendapatanBulan(int), tagihanPending]
- `$daftarPelanggan[]` = [id, nama, email, hp, paket, status('aktif'|'nonaktif'), bergabung]
- `$daftarPaket[]` = [nama, kecepatan, harga(int), jumlahPelanggan, status]
- `$daftarTagihan[]` = [noInvoice, pelanggan, paket, harga(int), tanggal, status('lunas'|'menunggu')]

## Halaman

1. **Login** — username + password → dashboard.
2. **Dashboard** — 4 kartu statistik + tabel pelanggan terbaru + tagihan terbaru.
3. **Pelanggan** — tabel + cari + Detail (modal) + toggle status (UI only).
4. **Paket** — kartu paket + jumlah pelanggan + Edit (modal, UI only).
5. **Transaksi** — tabel tagihan + filter status + Tandai Lunas (UI only).

## Shell

Reuse pola portal: sidebar putih + topbar + konten abu (`portal.css`). Sidebar admin punya menu sendiri; topbar menampilkan nama admin + Keluar.

## Out of Scope

- Auth nyata, database, proses Edit/Tandai Lunas.
- Modul Notifikasi dan Pengaturan (tahap berikutnya).

# Portal Admin RKnet — Tahap Berikutnya (Notifikasi, Pengaturan)

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Dua modul lanjutan portal admin — UI only, data dummy. Lanjutan dari admin inti yang sudah selesai.

## Goal

Melengkapi portal admin RKnet dengan dua modul tahap berikutnya yang sudah disebut di spec admin inti (baris "Out of Scope"): **Notifikasi dan Pengaturan**. Tetap PHP native + Bootstrap 5, gaya Modern Biru, reuse shell admin & `assets/css/portal.css`.

## Constraints & Decisions

- Lanjutan dari admin inti (Login, Dashboard, Pelanggan, Paket, Transaksi) yang sudah selesai.
- UI only: semua tombol (Tulis Notifikasi, Simpan Pengaturan) = aksi tampilan, data dummy. Tanpa auth nyata / database / proses backend.
- Kode: nama fungsi/variabel domain & komentar Bahasa Indonesia. Output dinamis di-`htmlspecialchars()`.
- Reuse `formatRupiah()` & `badgeStatus()` di `helpers.php`; tambah mapping badge notifikasi bila perlu.
- Sidebar dijadikan **bergrup**: label "UTAMA" (4 menu inti) + "LAINNYA" (2 menu baru).
- Cache-busting: setelah edit `portal.css`, bump `?v=N` di semua head yang merujuknya.
- Server: XAMPP port 8282. Lint tiap `.php` dengan `/d/WebServer/xampp82/php/php.exe -l`.

## Struktur File

```
admin-config.php          # + $daftarNotifikasi, $pengaturan
helpers.php               # badgeStatus(): tambah mapping status notifikasi
assets/css/portal.css     # + .portal-nav-label (label grup sidebar) — bump ?v=
admin/
├── notifikasi.php        # baru
├── pengaturan.php        # baru
└── partials/
    └── sidebar.php       # ubah: $menuAdmin jadi bergrup (UTAMA / LAINNYA)
```

## Sidebar Bergrup (admin/partials/sidebar.php)

`$menuAdmin` diubah jadi struktur bergrup:

```php
$menuAdmin = [
    'UTAMA' => [
        'dashboard' => ['Dashboard', 'bi-grid-1x2',      'dashboard.php'],
        'pelanggan' => ['Pelanggan', 'bi-people',        'pelanggan.php'],
        'paket'     => ['Paket',     'bi-box-seam',       'paket.php'],
        'transaksi' => ['Transaksi', 'bi-receipt-cutoff', 'transaksi.php'],
    ],
    'LAINNYA' => [
        'notifikasi'  => ['Notifikasi',  'bi-bell',              'notifikasi.php'],
        'pengaturan'  => ['Pengaturan',  'bi-gear',              'pengaturan.php'],
    ],
];
```

Render: loop grup → tampilkan `<li class="portal-nav-label">JUDUL</li>` lalu item-itemnya. `$menuAktif` tetap mencocokkan kunci item.

## Data Dummy (admin-config.php)

- `$daftarNotifikasi[]` = `[judul, isi, target, tanggal, status]` — status ∈ `terkirim|draft`; target mis. "Semua pelanggan", "Pelanggan aktif".
- `$pengaturan` = `[namaSitus, email, telepon, alamat]`; profil admin reuse `$admin`.

Isi minimal ~5 baris tiap daftar agar tabel/kartu terlihat nyata.

## badgeStatus() — mapping tambahan (helpers.php)

Tambah warna badge untuk status baru tanpa merusak yang lama:
- Notifikasi: `terkirim`→success, `draft`→secondary.

(Jika `badgeStatus()` saat ini hanya menangani lunas/menunggu/aktif/nonaktif, perluas dengan map array agar tetap satu fungsi.)

## Halaman

1. **Notifikasi** (`notifikasi.php`) — tabel notifikasi (judul, target, tanggal, badge status) dengan tab/filter **Terkirim** / **Draft**. Tombol **Tulis Notifikasi** buka modal (judul, isi, target). Semua mock.

2. **Pengaturan** (`pengaturan.php`) — halaman form bersusun kartu: (a) Profil admin (nama, email, peran — reuse `$admin`), (b) Ubah password (3 field), (c) Info situs (nama situs, email, telepon, alamat — `$pengaturan`). Tombol **Simpan** mock. Pola form mirip `portal/profil.php`.

## Shell

Tiap halaman ikuti pola admin standar:
```php
require __DIR__ . '/../admin-config.php';
$judulHalaman = '...'; $menuAktif = '...';
include __DIR__ . '/partials/shell-head.php';
include __DIR__ . '/partials/shell-open.php';
// ... konten ...
include __DIR__ . '/partials/shell-close.php';
```

## Urutan Implementasi

Per-modul berurutan dengan commit checkpoint tiap modul:
1. Sidebar bergrup + CSS label (fondasi navigasi 6 menu).
2. Notifikasi.
3. Pengaturan.

## Out of Scope

- Auth nyata, database, pemrosesan form/aksi apa pun.
- Modul lain di luar dua ini.

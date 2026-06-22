# Portal Admin Starlite — Tahap Berikutnya (Lead, Area, Notifikasi, Pengaturan)

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Empat modul lanjutan portal admin — UI only, data dummy. Lanjutan dari admin inti yang sudah selesai.

## Goal

Melengkapi portal admin Starlite dengan empat modul tahap berikutnya yang sudah disebut di spec admin inti (baris "Out of Scope"): **Lead Cek Jangkauan, Area, Notifikasi, Pengaturan**. Tetap PHP native + Bootstrap 5, gaya Modern Biru, reuse shell admin & `assets/css/portal.css`.

## Constraints & Decisions

- Lanjutan dari admin inti (Login, Dashboard, Pelanggan, Paket, Transaksi) yang sudah selesai.
- UI only: semua tombol (Tandai Dihubungi, Tambah/Edit Area, Tulis Notifikasi, Simpan Pengaturan) = aksi tampilan, data dummy. Tanpa auth nyata / database / proses backend.
- Kode: nama fungsi/variabel domain & komentar Bahasa Indonesia. Output dinamis di-`htmlspecialchars()`.
- Reuse `formatRupiah()` & `badgeStatus()` di `helpers.php`; tambah mapping badge baru bila perlu (status lead & area).
- Sidebar dijadikan **bergrup**: label "UTAMA" (4 menu inti) + "LAINNYA" (4 menu baru).
- Cache-busting: setelah edit `portal.css`, bump `?v=N` di semua head yang merujuknya.
- Server: XAMPP port 8282. Lint tiap `.php` dengan `/d/WebServer/xampp82/php/php.exe -l`.

## Struktur File

```
admin-config.php          # + $daftarLead, $daftarArea, $daftarNotifikasi, $pengaturan
helpers.php               # badgeStatus(): tambah mapping status lead & area
assets/css/portal.css     # + .portal-nav-label (label grup sidebar) — bump ?v=
admin/
├── lead.php              # baru
├── area.php              # baru
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
        'lead'        => ['Lead',        'bi-person-lines-fill', 'lead.php'],
        'area'        => ['Area',        'bi-geo-alt',           'area.php'],
        'notifikasi'  => ['Notifikasi',  'bi-bell',              'notifikasi.php'],
        'pengaturan'  => ['Pengaturan',  'bi-gear',              'pengaturan.php'],
    ],
];
```

Render: loop grup → tampilkan `<li class="portal-nav-label">JUDUL</li>` lalu item-itemnya. `$menuAktif` tetap mencocokkan kunci item.

## Data Dummy (admin-config.php)

- `$daftarLead[]` = `[id, nama, hp, area, tanggal, status]` — status ∈ `baru|dihubungi|terjadwal|selesai|batal`.
- `$daftarArea[]` = `[nama, kota, status, jumlahPelanggan]` — status ∈ `tercakup|segera`.
- `$daftarNotifikasi[]` = `[judul, isi, target, tanggal, status]` — status ∈ `terkirim|draft`; target mis. "Semua pelanggan", "Pelanggan aktif".
- `$pengaturan` = `[namaSitus, email, telepon, alamat]`; profil admin reuse `$admin`.

Isi minimal ~5 baris tiap daftar agar tabel/kartu terlihat nyata.

## badgeStatus() — mapping tambahan (helpers.php)

Tambah warna badge untuk status baru tanpa merusak yang lama:
- Lead: `baru`→info, `dihubungi`→primary, `terjadwal`→warning, `selesai`→success, `batal`→secondary.
- Area: `tercakup`→success, `segera`→warning.

(Jika `badgeStatus()` saat ini hanya menangani lunas/menunggu/aktif/nonaktif, perluas dengan map array agar tetap satu fungsi.)

## Halaman

1. **Lead** (`lead.php`) — tabel lead (nama, hp, area, tanggal, badge status) + filter status (dropdown/segmented, UI only via JS sederhana) + cari nama. Tombol **Detail** buka modal (info lengkap lead) dan **Tandai Dihubungi** (UI only). Pola tabel + modal sama seperti `pelanggan.php`.

2. **Area** (`area.php`) — daftar area sebagai kartu/tabel: nama area, kota, badge status (tercakup/segera), jumlah pelanggan. Tombol **Tambah Area** & **Edit** buka modal form (UI only). Pola sama seperti `paket.php`.

3. **Notifikasi** (`notifikasi.php`) — tabel notifikasi (judul, target, tanggal, badge status) dengan tab/filter **Terkirim** / **Draft**. Tombol **Tulis Notifikasi** buka modal (judul, isi, target). Semua mock.

4. **Pengaturan** (`pengaturan.php`) — halaman form bersusun kartu: (a) Profil admin (nama, email, peran — reuse `$admin`), (b) Ubah password (3 field), (c) Info situs (nama situs, email, telepon, alamat — `$pengaturan`). Tombol **Simpan** mock. Pola form mirip `portal/profil.php`.

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
1. Sidebar bergrup + CSS label (fondasi navigasi 8 menu).
2. Lead.
3. Area.
4. Notifikasi.
5. Pengaturan.

## Out of Scope

- Auth nyata, database, pemrosesan form/aksi apa pun.
- Integrasi lead dengan data form cek-jangkauan yang sebenarnya (lead di sini murni dummy).
- Modul lain di luar empat ini.

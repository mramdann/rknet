# Pagination Starlite — Design (sisi-server)

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Pagination sisi-server (LIMIT/OFFSET) untuk tabel daftar, dengan cari/filter dipindah ke server (GET). Berlaku untuk 4 tabel admin + 1 tabel portal.

## Goal

Membatasi tabel daftar menjadi per-halaman (5 baris) dengan navigasi halaman, dan menjadikan cari/filter sisi-server lewat query string, menggantikan filter sisi-klien yang ada.

## Constraints & Decisions

- Pagination **sisi-server**: query `LIMIT/OFFSET` + `COUNT` total; halaman via `?hal=N`.
- Cari/filter pindah ke **GET** (query string), terikat sebagai parameter prepared statement.
- **Page size = 5** (konstan), agar pagination terlihat dengan data seed kecil.
- Tabel yang dipaginasi: admin **pelanggan, transaksi, lead, notifikasi**; portal **riwayat transaksi**. Kartu (paket, area) tidak dipaginasi.
- Dashboard tidak berubah: kartu "Terbaru" tetap pakai array config (recent-N).
- `LIMIT`/`OFFSET` di-interpolasi sebagai integer hasil `(int)` (aman); filter value via bound params. Output di link/value di-`htmlspecialchars()`.
- `?hal` di luar rentang di-clamp ke `1..totalHalaman`. Filter kosong = tampil semua.
- Kode & komentar Bahasa Indonesia. Lint tiap `.php`. Verifikasi via PowerShell HTTP + BrowserOS.

## Struktur File

```
pagination.php                # baru — halamanSaatIni(), ambilPaginasi(), tampilPaginasi()
admin-config.php              # ubah — hapus $daftarLead & $daftarNotifikasi (pindah ke halaman; hanya dipakai di sana)
admin/pelanggan.php           # ubah — query paginasi+cari (GET), nav, hapus JS cari
admin/transaksi.php           # ubah — query paginasi+filter status (GET), nav, hapus JS filter
admin/lead.php                # ubah — query paginasi+cari+filter (GET), nav, hapus JS cari/filter
admin/notifikasi.php          # ubah — query paginasi+filter status (GET), nav, hapus JS filter
portal/transaksi.php          # ubah — query paginasi (GET), nav
```

## Komponen: pagination.php

```php
const PER_HALAMAN = 5;

function halamanSaatIni(): int  // max(1, (int)($_GET['hal'] ?? 1))

function ambilPaginasi(PDO $pdo, string $sqlBase, string $sqlCount, array $params, int $perHalaman = PER_HALAMAN): array
// 1. total = (int) prepared($sqlCount, $params)->fetchColumn()
// 2. totalHal = max(1, ceil(total / perHalaman))
// 3. hal = min(halamanSaatIni(), totalHal)
// 4. offset = (hal-1)*perHalaman
// 5. rows = prepared($sqlBase . " LIMIT $perHalaman OFFSET $offset", $params)->fetchAll()
// return ['baris'=>rows, 'hal'=>hal, 'totalHal'=>totalHal, 'total'=>total]

function tampilPaginasi(int $hal, int $totalHal, array $queryTambahan = []): void
// render <nav><ul class="pagination">: Prev, angka 1..totalHal, Next.
// tiap link: "?hal=N" + http_build_query($queryTambahan) digabung. Disabled pada batas.
// tidak render bila totalHal <= 1.
```

`$sqlBase` & `$sqlCount` memakai WHERE & params yang sama; `$sqlBase` tanpa `LIMIT` (ditambah helper). `$sqlCount` = `SELECT COUNT(*) ...` dengan WHERE sama.

## Pola per Halaman

Tiap halaman daftar:
1. `require pagination.php` (via admin-config / portal-config — lihat di bawah).
2. Baca param filter dari `$_GET` (cari/status), bangun klausa `WHERE` + array `$params`.
3. Panggil `ambilPaginasi()` → dapat baris halaman ini + meta.
4. Render baris (loop seperti sekarang, tapi dari `$hasil['baris']`).
5. Render form GET (cari/filter) yang mempertahankan nilai terpilih.
6. `tampilPaginasi($hasil['hal'], $hasil['totalHal'], $paramFilter)` di bawah tabel.

`pagination.php` di-`require_once` dari `admin-config.php` & `portal-config.php` (agar tersedia di semua halaman; ringan).

### admin/pelanggan.php
- Filter: `?cari=` → `WHERE LOWER(pl.nama) LIKE ? OR LOWER(pl.id) LIKE ?` (param `%cari%`).
- Query base: SELECT (id,nama,email,hp,alamat,paket kecepatan,status,bergabung) join paket, ORDER BY pl.id.
- Form GET dengan input `name="cari"` (value = cari saat ini). Hapus JS `#cariPelanggan`.

### admin/transaksi.php
- Filter: `?status=` (lunas|menunggu|"") → `WHERE t.status = ?` bila diisi.
- Query base: join pelanggan & paket, ORDER BY t.id, termasuk `t.id AS idTagihan`.
- Filter sebagai grup link (`?status=lunas` dst.), mempertahankan di nav. Hapus JS filter.

### admin/lead.php
- Filter: `?cari=` (nama/area LIKE) + `?status=` (baru/dihubungi/…). WHERE gabungan.
- Form GET: input cari + select status (submit on change / tombol). Hapus JS cari/filter.

### admin/notifikasi.php
- Filter: `?status=` (terkirim|draft|""). WHERE bila diisi.
- Filter grup link. Hapus JS filter.

### portal/transaksi.php
- Tanpa filter; hanya `?hal=`. Query tagihan WHERE pelanggan_id = (sesi), ORDER BY id.
- Karena butuh paginasi sendiri, halaman menjalankan query paginasi langsung (bukan `$daftarTransaksi` config). `$daftarTransaksi` config tetap untuk dashboard portal.

## admin-config.php

Hapus blok query `$daftarLead` & `$daftarNotifikasi` (kini di-query di halaman lead/notifikasi dengan paginasi; tidak dipakai halaman lain). `$daftarPelanggan` & `$daftarTagihan` tetap (dipakai dashboard); halaman pelanggan/transaksi memakai query paginasi sendiri. Tambah `require_once pagination.php`.

## Error Handling

- `?hal` non-numerik / <1 → 1; > totalHal → clamp ke totalHal.
- Total 0 → totalHal 1, tampil tabel kosong + pesan "tidak ada data".
- Filter value diteruskan ke `WHERE ... LIKE ?` via bound param (aman dari injeksi); ditampilkan ulang via `htmlspecialchars`.
- `tampilPaginasi` tidak muncul bila hanya 1 halaman.

## Out of Scope

- Paginasi kartu paket & area (jumlah kecil).
- Pengurutan kolom (sort) yang bisa diklik.
- Pagination dengan AJAX (pakai reload halaman biasa).
- Per-page size yang bisa dipilih pengguna.

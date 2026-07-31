# Transaksi Admin + Notifikasi Per Pelanggan — Design

**Date:** 2026-07-31
**Status:** Approved design
**Scope:** Admin dapat menerbitkan tagihan langsung dari halaman Transaksi (satu pelanggan atau semua pelanggan aktif), dengan notifikasi otomatis ke pelanggan terkait. Feed notifikasi portal berubah dari statis menjadi data nyata dari DB.

## Goal

Memberi admin kemampuan membuat transaksi baru (tagihan) melalui modal di `admin/transaksi.php` tanpa harus menunggu pelanggan. Setiap tagihan yang dibuat memicu notifikasi yang muncul di panel lonceng portal pelanggan terkait. Ini sekaligus menghapus sisa konten mock di portal (feed notifikasi statis), sehingga tinggal konten marketing landing di `config.php` yang bersifat presentasional.

## Constraints & Decisions

- Aksi via POST ke `admin/aksi-transaksi.php` dengan aksi `buat`, lalu redirect (PRG). Handler: `wajibLoginAdmin()` + `cekCsrf()`.
- Target: `jenis_target = satu` (satu pelanggan aktif) atau `semua` (semua pelanggan aktif). Pelanggan target harus berstatus `aktif`.
- **Paket & nominal tidak dipilih admin** — modal hanya berisi Tujuan, Pelanggan, dan Tanggal. Handler menagih tiap pelanggan dengan paket yang disubscribe (`pelanggan.paket_id`), asalkan paket itu berstatus `aktif`; harga = harga paket. Pelanggan tanpa paket aktif dilewati dan dicantumkan di flash. Tanggal default = hari ini, boleh diubah (input `date`, disimpan dalam format tampilan Indonesia `d MMM yyyy` seperti data lama).
- `no_invoice` mengikuti pola data lama `INV/YYYY/MM/<6 digit terakhir id>`; bila bertabrakan dengan `uq_tagihan_no_invoice`, diberi akhiran `-N`.
- Semua insert dibungkus transaksi DB (`begin_transaction`/`commit`/`rollback`) agar tagihan + notifikasi utuh bersamaan.
- Notifikasi per pelanggan memakai kolom baru `notifikasi.pelanggan_id` (nullable). Nilai `NULL` = broadcast (muncul untuk semua pelanggan); terisi = khusus satu pelanggan. Kolom `target` lama diisi nama pelanggan untuk baris perorangan.
- Feed portal (`portal-config.php::$daftarNotifikasi`) dibaca dari `notifikasi` (broadcast + milik sendiri), bukan lagi array statis. Bentuk tiap item tetap `['tipe','judul','isi','waktu']` agar `notif.php` tidak berubah.
- Prepared statement via `kueri()/kueriSatu()/eksekusi()`. Output di-`htmlspecialchars()`. Kode & komentar Bahasa Indonesia. Lint tiap `.php`.

## Struktur File

```
database/schema.sql                 # ubah — notifikasi tambah kolom pelanggan_id + FK; urutan DROP disesuaikan
database/migrasi-notifikasi-pelanggan.sql  # baru — migrasi satu kali untuk DB lama
helpers.php                         # ubah — tambah tanggalIndonesia()
portal-config.php                   # ubah — $daftarNotifikasi dari DB
admin/transaksi.php                 # ubah — tombol "Buat Transaksi" + modal (target/tanggal saja)
admin/aksi-transaksi.php            # ubah — aksi "buat" (tagih per paket subscribe pelanggan + notifikasi)
```

## Pemetaan & Data Flow

**aksi-transaksi.php `buat`:**
1. Validasi: `jenis_target` ∈ {`satu`, `semua`}; `tanggal` `Y-m-d` opsional (kosong → hari ini).
2. Resolusi daftar pelanggan target: `satu` → cek `pelanggan_id` ada & `aktif`; `semua` → `SELECT id, nama FROM pelanggan WHERE status='aktif'`.
3. Mulai transaksi; untuk tiap pelanggan:
   - Ambil paket subscribe: `JOIN paket ON pk.id = pl.paket_id AND pk.status='aktif'`. Bila tidak ada atau harga ≤ 0 → lewati, catat nama pelanggan.
   - Generate `no_invoice` unik (`INV/YYYY/MM/<6 digit>`; jika ada, `-2`, `-3`, …).
   - `INSERT tagihan (no_invoice, pelanggan_id, paket_id, harga, tanggal, status='menunggu')` — `paket_id` & `harga` dari paket subscribe pelanggan.
   - `INSERT notifikasi (judul='Tagihan baru diterbitkan', isi=<no_invoice + nominal>, pelanggan_id=<id>, target=<nama>, tanggal, status='terkirim')`.
4. Commit; flash `success` "N transaksi berhasil dibuat…" (dilewati disebutkan). Bila tak ada yang dibuat → flash `danger`. Bila error → rollback + flash `danger`.
5. Redirect `transaksi.php`.

**portal-config.php `$daftarNotifikasi`:**
`SELECT judul, isi, tanggal, pelanggan_id FROM notifikasi WHERE pelanggan_id IS NULL OR pelanggan_id = ? ORDER BY id DESC`. `tipe` = `notifikasi` bila `pelanggan_id` terisi, else `informasi`; `waktu` = `tanggal`.

**admin/transaksi.php:** tombol di header sebelah grup filter membuka modal `modalBuatTransaksi` berisi `jenis_target` (select), `pelanggan_id` (select, hanya aktif; disembunyikan saat `semua`), dan `tanggal` (date, default hari ini). JS kecil: toggle blok pelanggan + reset saat modal dibuka.

## Halaman

- **admin/transaksi.php**: tabel tetap; tambah tombol "Buat Transaksi" (kanan atas) + modal. Query `$daftarPelangganAktif`, `$daftarPaketAktif` di bagian atas.
- **portal (tanpa perubahan markup)**: `notif.php` tetap; feed kini nyata dari DB.

## Error Handling

- CSRF gagal → 403 (dari `cekCsrf`).
- Paket tidak valid / target tidak valid / tidak ada pelanggan aktif → flash danger, tanpa tulis.
- Duplikat `no_invoice` → akhiran `-N` otomatis.
- Error DB di tengah proses → rollback, flash danger, tidak ada data setengah.
- Belum login → guard redirect ke `admin/login.php`.

## Out of Scope

- Edit/hapus tagihan yang sudah diterbitkan.
- Notifikasi saat verifikasi/lunas/ditolak (status berubah).
- Pengiriman notifikasi selain lewat feed portal (email/WhatsApp).
- Penjadwalan tagihan otomatis bulanan.

## Fase Lanjutan (Read/Unread — diterapkan)

Feed portal kini mendukung read/unread tanpa tabel baru (satu tabel `notifikasi`):

- Kolom `notifikasi.dibaca TINYINT(1) NOT NULL DEFAULT 0` (0 = belum dibaca, 1 = dibaca); ditambahkan via `migrasi-notifikasi-pelanggan.sql` bersamaan dengan `pelanggan_id`. Broadcast dan notifikasi per pelanggan memakai status yang sama.
- `portal/aksi-notifikasi.php` (baru): POST `baca`/`baca_semua`, `wajibLoginPelanggan()` + `cekCsrf()`, balas JSON. `baca` membatasi `WHERE id = ? AND (pelanggan_id IS NULL OR pelanggan_id = ?)`.
- `portal/partials/topbar.php`: lonceng menampilkan `<span class="badge-notif" id="badgeNotif">` berisi jumlah belum dibaca (hilang saat 0).
- `portal/partials/notif.php`: tiap item ber-`data-id`, class `belum-dibaca`, label "Baru"; klik item / tombol "Tandai semua dibaca" memanggil fetch (CSRF dari `data-csrf` di panel) lalu memperbarui UI tanpa reload. JS menghitung badge dari id unik karena item dirender duplikat di tab Semua + Notifikasi/Informasi.
- CSS `portal.css`: `.badge-notif` (bg `#e0245e`) & `.notif-item.belum-dibaca` (bg `--rk-blue-soft`).
- Item baru dari aksi `buat` selalu `dibaca=0` (default), sehingga badge langsung tampil di portal pelanggan terkait.

## Fase Lanjutan (Target Multi-Pelanggan — diterapkan)

Modal "Tulis Notifikasi" di `admin/notifikasi.php` tidak lagi memakai select `target` statis:

- Checkbox "Kirim ke semua pelanggan" (default) → broadcast (`pelanggan_id NULL`, satu baris).
- Bila checkbox dimatikan → daftar checkbox pelanggan aktif muncul (scrollable, `pelanggan_id[]`); dipilih satu atau lebih.
- `admin/aksi-notifikasi.php` `tambah`: untuk target tertentu, validasi id ke pelanggan aktif, lalu insert **satu baris `notifikasi` per pelanggan** (masing-masing `pelanggan_id` terisi + `dibaca=0` default) dalam satu transaksi DB — memakai mekanisme yang sama seperti aksi `buat` transaksi, tanpa skema/tabel baru.
- Feed portal otomatis menyaring: hanya pelanggan yang dipilih yang melihat notifikasi (per-customer `dibaca` tetap per baris).

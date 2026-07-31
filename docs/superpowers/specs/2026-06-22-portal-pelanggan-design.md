# Portal Pelanggan RKnet — UI Design (Improve)

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Portal pelanggan (customer area) — UI only, data dummy. Admin area menyusul (spec terpisah).

## Goal

Membangun portal pelanggan RKnet/Weave versi improve berdasarkan video demo, memakai PHP native + Bootstrap 5, gaya Modern Biru (konsisten dengan landing page).

## Constraints & Decisions

- **Branding:** Sama dengan landing — logo RKnet + Weave, PT Integrasi Jaringan Ekosistem.
- **Stack:** PHP native (komponen via include), Bootstrap 5.3 CDN, Bootstrap Icons, Poppins.
- **Backend:** UI only. Tombol = navigasi antar halaman, data dummy dari `portal-config.php`.
- **Kode:** Nama fungsi/variabel domain & komentar pakai Bahasa Indonesia.
- **Wiring:** Tombol "Login" di navbar landing → `portal/login.php`.
- **Admin:** Folder `admin/` disiapkan sebagai placeholder, belum diisi.
- **Server:** XAMPP port 8282 → `http://localhost:8282/rknet/portal/...`.

## Struktur File

```
portal/
├── login.php          # login portal (standalone, tanpa shell)
├── dashboard.php      # area pelanggan (akun + paket aktif + ringkasan transaksi)
├── transaksi.php      # riwayat transaksi
├── invoice.php        # kuitansi/invoice detail
├── paket.php          # pilih & konfirmasi paket
└── partials/
    ├── shell-head.php  # <head> portal (Bootstrap + style + portal.css)
    ├── sidebar.php     # nav: Dashboard, Riwayat Transaksi, Invoice, Profil
    ├── topbar.php      # logo, nama pelanggan, lonceng notif, tombol akun
    ├── shell-open.php  # buka layout shell (sidebar + topbar + <main>)
    ├── shell-close.php # tutup layout + offcanvas notif + script
    └── notif.php       # isi offcanvas notifikasi (tab Semua/Notifikasi/Informasi)
portal-config.php       # data dummy + helper (formatRupiah, badgeStatus)
admin/.gitkeep          # placeholder area admin
assets/css/portal.css   # styling shell portal
```

## Data Dummy (portal-config.php)

- `$pelanggan`: id, nama (Dwi Anjasmoro), email, hp, alamat.
- `$paketAktif`: nama (Paket 200 Mbps RKnet), kecepatan, harga (100000), masaAktif (s/d Juli 2026), status.
- `$daftarTransaksi[]`: noInvoice, paket, harga, tanggal, status (lunas/menunggu).
- `$daftarNotifikasi[]`: tipe (notifikasi/informasi), judul, isi, waktu.
- `$paketTersedia[]`: dipakai halaman pilih paket (reuse konsep dari landing).
- Helper: `formatRupiah($angka)`, `badgeStatus($status)` (kembalikan kelas + label badge).

## Halaman

1. **Login** — kartu tengah, logo RKnet+Weave, field No. HP + PIN, tombol biru "Login" → dashboard, footer perusahaan.
2. **Dashboard** — banner sambutan; 2 kolom: Akun Anda (data pelanggan) + Paket Anda (paket aktif, masa aktif, tombol Perpanjang & Ubah Paket → paket.php); ringkasan transaksi terakhir (link ke transaksi.php).
3. **Riwayat Transaksi** — daftar kartu transaksi: paket, harga, tanggal, badge status, tombol "Lihat Tagihan" → invoice.php.
4. **Invoice/Kuitansi** — layout kuitansi: no. invoice, data pelanggan, rincian paket & biaya, total, status Lunas, tombol Cetak (window.print) & kembali.
5. **Pilih Paket** — kartu pilihan paket; paket 200 Mbps ter-highlight terpilih + masa aktif; tombol Konfirmasi → dashboard.
6. **Notifikasi** — offcanvas kanan dipicu lonceng di topbar; tab Semua/Notifikasi/Informasi; list dari `$daftarNotifikasi`.

## Sistem Visual

- Shell: sidebar putih kiri (lebar ~250px, collapsible/offcanvas di mobile) + topbar putih + area konten background abu lembut (`#f4f6fb`).
- Kartu `rounded-4`, shadow lembut, badge status berwarna (hijau=lunas, kuning=menunggu).
- Reuse variabel & `.btn-st` dari `assets/css/style.css`; tambahan shell di `assets/css/portal.css`.

## Verifikasi

UI statis → buka tiap halaman di `http://localhost:8282/rknet/portal/...`, cek render, navigasi antar halaman, offcanvas notif, responsif (sidebar jadi offcanvas di mobile).

## Out of Scope

- Proses login/auth nyata, database, pembayaran.
- Portal admin (spec terpisah nanti).

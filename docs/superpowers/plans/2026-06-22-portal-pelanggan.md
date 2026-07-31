# Portal Pelanggan RKnet — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:executing-plans. Steps use checkbox (`- [ ]`).

**Goal:** Bangun portal pelanggan (6 layar) versi improve, PHP native + Bootstrap 5, gaya Modern Biru, UI-only.

**Architecture:** Folder `portal/`. Halaman ber-shell (sidebar + topbar + main) via include partial; login standalone. Data dummy + helper di `portal-config.php` (nama & komentar Bahasa Indonesia). Reuse var/btn dari `assets/css/style.css`, shell di `assets/css/portal.css`.

**Tech Stack:** PHP 8.2 (XAMPP:8282), Bootstrap 5.3, Bootstrap Icons, Poppins.

## Global Constraints

- Nama fungsi/variabel domain & komentar: Bahasa Indonesia.
- UI only; tombol = navigasi antar halaman.
- Branding RKnet + Weave, PT Integrasi Jaringan Ekosistem.
- Reuse `:root` & `.btn-st` dari style.css; jangan duplikasi tanpa perlu.
- Path include relatif `__DIR__`.

## Data Contract (portal-config.php)

- `$pelanggan` = [id, nama, email, hp, alamat]
- `$paketAktif` = [nama, kecepatan, harga(int), masaAktif, status]
- `$daftarTransaksi[]` = [noInvoice, paket, kecepatan, harga(int), tanggal, status('lunas'|'menunggu')]
- `$daftarNotifikasi[]` = [tipe('notifikasi'|'informasi'), judul, isi, waktu]
- `$paketTersedia[]` = [nama, kecepatan, harga(int), fitur[], dipilih(bool)]
- `formatRupiah(int $angka): string`
- `badgeStatus(string $status): array` → ['kelas'=>..., 'label'=>...]

---

### Task 1: Konfigurasi data + CSS shell + head partial
Files: Create `portal-config.php`, `assets/css/portal.css`, `portal/partials/shell-head.php`, `admin/.gitkeep`.
- [ ] Buat `portal-config.php` (data dummy + `formatRupiah` + `badgeStatus`).
- [ ] Buat `portal/partials/shell-head.php` (head: Bootstrap, Icons, Poppins, ../assets/css/style.css, ../assets/css/portal.css).
- [ ] Buat `assets/css/portal.css` (layout shell: sidebar, topbar, konten).
- [ ] Buat `admin/.gitkeep`.
- [ ] `php -l portal-config.php`; commit.

### Task 2: Halaman Login (standalone)
Files: Create `portal/login.php`.
- [ ] Kartu login tengah, logo, field No. HP + PIN, tombol Login → dashboard.php, footer.
- [ ] `php -l`; cek browser; commit.

### Task 3: Shell partials (sidebar, topbar, open/close, notif)
Files: Create `portal/partials/{sidebar,topbar,shell-open,shell-close,notif}.php`.
- [ ] sidebar.php (nav, param `$aktif` untuk highlight menu), topbar.php (logo, nama pelanggan, lonceng→offcanvas, akun), shell-open.php, shell-close.php, notif.php (offcanvas + tab).
- [ ] `php -l` semua; commit.

### Task 4: Dashboard
Files: Create `portal/dashboard.php`.
- [ ] Banner sambutan; 2 kolom Akun Anda + Paket Anda; ringkasan transaksi.
- [ ] cek browser; commit.

### Task 5: Riwayat Transaksi
Files: Create `portal/transaksi.php`.
- [ ] Daftar kartu transaksi + badge status + tombol Lihat Tagihan → invoice.php.
- [ ] cek browser; commit.

### Task 6: Invoice/Kuitansi
Files: Create `portal/invoice.php`.
- [ ] Layout kuitansi + tombol Cetak (window.print) + Kembali.
- [ ] cek browser; commit.

### Task 7: Pilih Paket
Files: Create `portal/paket.php`.
- [ ] Kartu paket, highlight terpilih, masa aktif, tombol Konfirmasi → dashboard.
- [ ] cek browser; commit.

### Task 8: Wiring + cleanup
Files: Modify `partials/navbar.php` (Login → portal/login.php); Delete `docs/_player.html`.
- [ ] Sambungkan Login landing ke portal; hapus file player sementara; commit.

### Task 9: Polish + verifikasi responsif
Files: Modify `assets/css/portal.css`, `portal/partials/*`.
- [ ] Sidebar jadi offcanvas di mobile; cek semua halaman + navigasi; commit.

## Self-Review
- Coverage: Login(T2), shell+notif(T3), Dashboard(T4), Transaksi(T5), Invoice(T6), Paket(T7), wiring(T8), responsif(T9), data(T1). Lengkap.
- Bahasa Indonesia untuk identifier domain & komentar: diterapkan mulai T1.

# Transaksi Admin + Notifikasi Per Pelanggan Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Admin menerbitkan tagihan dari modal di `admin/transaksi.php` (satu pelanggan atau semua pelanggan aktif) + notifikasi otomatis ke pelanggan terkait; feed notifikasi portal menjadi data nyata dari DB.

**Architecture:** Reuse `aksi.php` (CSRF + flash) dan helper `kueri()/kueriSatu()/eksekusi()` (`db.php`). Aksi `buat` di `admin/aksi-transaksi.php` membungkus insert tagihan + notifikasi dalam transaksi DB. `notifikasi` mendapat kolom nullable `pelanggan_id` (NULL = broadcast, terisi = khusus pelanggan). Feed portal membaca `notifikasi` untuk pelanggan sesi.

**Tech Stack:** PHP native (mysqli prepared statements, session CSRF, flash PRG), MySQL `dbrknet` @ 3382.

## Global Constraints

- Aksi via POST ke `admin/aksi-transaksi.php` → redirect (PRG). Handler: `wajibLoginAdmin(); cekCsrf();`.
- Target pelanggan wajib berstatus `aktif`; paket wajib `aktif`.
- Nominal default = harga paket; tanggal default = hari ini (format tampilan Indonesia `d MMM yyyy`).
- `no_invoice` = `INV/YYYY/MM/<6 digit terakhir id>`, akhiran `-N` bila duplikat.
- Insert tagihan + notifikasi dibungkus transaksi (`begin_transaction`/`commit`/`rollback`).
- Prepared statement; output `htmlspecialchars()`. Kode/komentar Bahasa Indonesia. Lint tiap `.php`.
- Verifikasi: lint + BrowserOS (login admin → buat transaksi → cek portal pelanggan). Tidak commit tanpa diminta.

---

### Task 1: Database — kolom `notifikasi.pelanggan_id`

**Files:**
- Modify: `database/schema.sql`
- Create: `database/migrasi-notifikasi-pelanggan.sql`

**Interfaces:**
- Produces: kolom `notifikasi.pelanggan_id VARCHAR(30) NULL` + FK ke `pelanggan(id)`.

- [ ] **Step 1: Ubah `database/schema.sql`**

Pindahkan `DROP TABLE IF EXISTS notifikasi;` ke sebelum `DROP TABLE IF EXISTS pelanggan;` (karena FK baru). Ubah definisi tabel:

```sql
CREATE TABLE notifikasi (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    judul        VARCHAR(150) NOT NULL,
    isi          TEXT         NOT NULL,
    pelanggan_id VARCHAR(30)  NULL,
    target       VARCHAR(80)  NOT NULL,
    tanggal      VARCHAR(20)  NOT NULL,
    status       VARCHAR(20)  NOT NULL DEFAULT 'draft',
    CONSTRAINT fk_notifikasi_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id)
);
```

- [ ] **Step 2: Buat `database/migrasi-notifikasi-pelanggan.sql`**

```sql
-- Migrasi satu kali untuk database dbrknet yang sudah berjalan.
-- Tambah kolom pelanggan_id di notifikasi (NULL = broadcast, terisi = khusus pelanggan).
USE dbrknet;

ALTER TABLE notifikasi
    ADD COLUMN pelanggan_id VARCHAR(30) NULL AFTER isi,
    ADD CONSTRAINT fk_notifikasi_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id);
```

- [ ] **Step 3: Terapkan migrasi ke DB lokal**

```powershell
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbrknet -e "ALTER TABLE notifikasi ADD COLUMN pelanggan_id VARCHAR(30) NULL AFTER isi, ADD CONSTRAINT fk_notifikasi_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id);"
```

- [ ] **Step 4: Verifikasi kolom**

`SHOW COLUMNS FROM notifikasi;` → `pelanggan_id` ada.

---

### Task 2: Helper `tanggalIndonesia()` di `helpers.php`

- [ ] **Step 1: Tambah fungsi**

```php
if (!function_exists('tanggalIndonesia')) {
    /**
     * Format tanggal menjadi "d MMM yyyy" Bahasa Indonesia (kosong = hari ini).
     */
    function tanggalIndonesia(string $tanggal = ''): string
    {
        $bulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                  7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        $ts = $tanggal === '' ? time() : strtotime($tanggal);
        if ($ts === false) return $tanggal;
        return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}
```

---

### Task 3: Feed notifikasi portal dari DB (`portal-config.php`)

- [ ] **Step 1: Ganti blok `$daftarNotifikasi` statis**

```php
// Feed notifikasi & informasi untuk panel offcanvas — dari DB (broadcast + khusus pelanggan)
$daftarNotifikasi = [];
foreach (kueri(
    "SELECT judul, isi, tanggal, pelanggan_id FROM notifikasi
     WHERE pelanggan_id IS NULL OR pelanggan_id = ?
     ORDER BY id DESC",
    [$idPelanggan]
) as $row) {
    $daftarNotifikasi[] = [
        'tipe'  => $row['pelanggan_id'] !== null ? 'notifikasi' : 'informasi',
        'judul' => $row['judul'],
        'isi'   => $row['isi'],
        'waktu' => $row['tanggal'],
    ];
}
```

---

### Task 4: Modal "Buat Transaksi" di `admin/transaksi.php`

- [ ] **Step 1: Tambah query pelanggan & paket aktif di atas halaman**

```php
$daftarPelangganAktif = kueri("SELECT id, nama FROM pelanggan WHERE status = 'aktif' ORDER BY nama");
$daftarPaketAktif     = kueri("SELECT id, nama, kecepatan, harga FROM paket WHERE status = 'aktif' ORDER BY id");
```

- [ ] **Step 2: Tombol di header (sebelah grup filter)**

```html
<div class="d-flex flex-wrap gap-2">
  <div class="btn-group" role="group"> …filter lama… </div>
  <button class="btn btn-rk" type="button" data-bs-toggle="modal" data-bs-target="#modalBuatTransaksi">
    <i class="bi bi-plus-lg me-1"></i>Buat Transaksi</button>
</div>
```

- [ ] **Step 3: Modal** (sebelum `shell-close.php`)

`jenis_target` (select: `satu`/`semua`), blok `pelanggan_id` (select aktif, `.d-none` saat `semua`), `paket_id` (select aktif, option ber-`data-harga`), `harga` (number, terisi dari paket via JS), `tanggal` (date, default `date('Y-m-d')`). Form POST ke `aksi-transaksi.php` dengan hidden `csrf` + `aksi=buat`.

- [ ] **Step 4: JS kecil** (toggle blok pelanggan + isi harga dari paket).

---

### Task 5: Aksi `buat` di `admin/aksi-transaksi.php`

- [ ] **Step 1: Ubah validasi awal** agar `buat` lolos tanpa `id`:

```php
if (!in_array($aksi, ['terima', 'tolak', 'buat'], true)) {
    setFlash('danger', 'Aksi tidak valid.');
    header('Location: transaksi.php');
    exit;
}
```

- [ ] **Step 2: Blok `buat`** (sebelum parse `$id`):

Validasi paket & target → resolusi daftar pelanggan → `begin_transaction` → loop (generate `no_invoice`, insert tagihan status `menunggu`, insert notifikasi) → `commit`; catch → `rollback`. Flash sukses dengan jumlah. Redirect `transaksi.php`.

---

### Task 6: Lint & verifikasi

- [ ] **Step 1: Lint** semua file yang disentuh (`helpers.php`, `portal-config.php`, `admin/transaksi.php`, `admin/aksi-transaksi.php`).
- [ ] **Step 2: Verifikasi BrowserOS** — login admin → "Buat Transaksi" (satu pelanggan + semua aktif) → cek tabel transaksi & halaman notifikasi admin → login portal pelanggan → cek feed lonceng memuat notifikasi baru.

---

## Catatan Penutup

Setelah selesai: satu-satunya konten presentasional tersisa adalah landing `config.php`. Perbarui `CLAUDE.md` (kolom `notifikasi.pelanggan_id`, migrasi baru, feed portal dari DB, aksi `buat`).

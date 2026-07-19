# Migrasi PDO → mysqli Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ganti lapisan akses data dari PDO ke mysqli via helper di `db.php`, dengan setelan koneksi (termasuk port) di satu blok konstanta.

**Architecture:** `db.php` menyediakan koneksi mysqli singleton + helper `kueri/kueriSatu/kueriNilai/eksekusi` (prepared statement, bind otomatis). Semua call-site memakai helper (bentuk array param sama seperti PDO). `pagination.php` `ambilPaginasi()` melepas param `$pdo`.

**Tech Stack:** PHP native + **mysqli** (mysqlnd), MySQL `dbstarlite` @ 3382.

## Global Constraints

- Pakai **mysqli** (bukan PDO). Prepared statement dengan `bind_param` (tipe `i`/`d`/`s` otomatis, bind by-reference).
- Setelan koneksi = konstanta `DB_HOST/DB_PORT/DB_USER/DB_PASS/DB_NAME` di atas `db.php` (port `3382` satu tempat).
- Helper: `db(): mysqli`, `kueri($sql,$p=[]): array`, `kueriSatu($sql,$p=[]): ?array`, `kueriNilai($sql,$p=[])`, `eksekusi($sql,$p=[]): void`.
- `mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)`; koneksi gagal / tabel belum ada → `pesanErrorDb()` (kartu rapi).
- Tidak mengubah SQL, skema, data, atau markup. Bentuk array parameter di call-site tetap sama.
- Kode/komentar Bahasa Indonesia. Lint tiap `.php`: `/d/WebServer/xampp82/php/php.exe -l <file>`.
- Verifikasi: lint + suite E2E 39-cek + BrowserOS. Commit per task, prefix `refactor(db)`.
- Kredensial uji: admin `admin@starlite.id`/`admin123`, pelanggan `dwi.anjasmoro@gmail.com`/`pelanggan123`.

---

### Task 1: db.php — mysqli + konstanta + helper

**Files:**
- Modify: `db.php` (tulis ulang total)

**Interfaces:**
- Produces: `DB_HOST/DB_PORT/DB_USER/DB_PASS/DB_NAME`; `db(): mysqli`; `stmtSiap()`, `kueri()`, `kueriSatu()`, `kueriNilai()`, `eksekusi()`; `pesanErrorDb()`.

> Catatan: setelah task ini, halaman masih memakai `$pdo->...` sehingga akan error sampai Task 2-4 selesai. Verifikasi task ini = lint saja.

- [ ] **Step 1: Tulis ulang `db.php`**

```php
<?php
// db.php — koneksi mysqli ke dbstarlite + helper query. Setelan koneksi di satu tempat (konstanta di bawah).

const DB_HOST = '127.0.0.1';
const DB_PORT = 3382;
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'dbstarlite';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   // error mysqli sebagai exception

function db(): mysqli
{
    static $db = null;
    if ($db !== null) return $db;
    try {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $db->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $e) {
        pesanErrorDb('Koneksi database gagal.',
            'Pastikan MySQL berjalan di port ' . DB_PORT . ' dan database <code>' . DB_NAME . '</code> sudah dibuat.');
    }
    return $db;
}

// Siapkan & jalankan prepared statement (bind_param otomatis by-reference).
function stmtSiap(string $sql, array $params): mysqli_stmt
{
    $stmt = db()->prepare($sql);
    if ($params) {
        $tipe = '';
        foreach ($params as $p) {
            $tipe .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
        }
        $ref = [$tipe];
        foreach ($params as $i => $v) {
            $ref[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $ref);
    }
    $stmt->execute();
    return $stmt;
}

function kueri(string $sql, array $params = []): array
{
    return stmtSiap($sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}

function kueriSatu(string $sql, array $params = []): ?array
{
    $baris = stmtSiap($sql, $params)->get_result()->fetch_assoc();
    return $baris ?: null;
}

function kueriNilai(string $sql, array $params = [])
{
    $baris = stmtSiap($sql, $params)->get_result()->fetch_row();
    return $baris ? $baris[0] : null;
}

function eksekusi(string $sql, array $params = []): void
{
    stmtSiap($sql, $params);
}

// Pesan error database yang rapi (tanpa stack trace/path).
function pesanErrorDb(string $judul, string $detail): never
{
    http_response_code(500);
    exit('<div style="font-family:sans-serif;max-width:560px;margin:3rem auto;padding:1.5rem;'
       . 'border:1px solid #f0c0c0;border-radius:12px;background:#fff6f6;color:#7a1f1f">'
       . '<h2 style="margin:0 0 .5rem">' . $judul . '</h2>'
       . '<p style="margin:0">' . $detail . ' Jalankan <code>database/schema.sql</code> lalu '
       . '<code>database/seed.sql</code> ke database <code>dbstarlite</code>.</p></div>');
}

// Tangani error query tak tertangkap (mis. tabel belum dibuat) agar tampil rapi.
set_exception_handler(function (Throwable $e): void {
    if ($e instanceof mysqli_sql_exception) {
        pesanErrorDb('Database belum siap.', 'Tabel belum ada atau koneksi bermasalah.');
    }
    http_response_code(500);
    exit('<div style="font-family:sans-serif;margin:3rem">Terjadi kesalahan tak terduga.</div>');
});
```

- [ ] **Step 2: Lint**

Run: `/d/WebServer/xampp82/php/php.exe -l db.php`
Expected: "No syntax errors detected in db.php".

- [ ] **Step 3: Commit**

```bash
git add db.php
git commit -m "refactor(db): db.php pakai mysqli + konstanta setelan + helper kueri/eksekusi"
```

---

### Task 2: Migrasi baca admin (pagination + config + halaman + login)

**Files:**
- Modify: `pagination.php` (tulis ulang: lepas param `$pdo`)
- Modify: `admin-config.php` (tulis ulang: helper)
- Modify: `admin/pelanggan.php`, `admin/transaksi.php`, `admin/lead.php`, `admin/notifikasi.php` (satu baris `ambilPaginasi`)
- Modify: `admin/login.php` (kueriSatu)

**Interfaces:**
- Consumes: `kueri`, `kueriSatu`, `kueriNilai` (Task 1).
- Produces: `ambilPaginasi(string $sqlBase, string $sqlCount, array $params, int $perHalaman = PER_HALAMAN): array` (tanpa `$pdo`).

- [ ] **Step 1: Tulis ulang `pagination.php`**

```php
<?php
// pagination.php — helper paginasi sisi-server (LIMIT/OFFSET + nav).
require_once __DIR__ . '/db.php';   // kueri(), kueriNilai()

const PER_HALAMAN = 5;

function halamanSaatIni(): int
{
    $hal = (int) ($_GET['hal'] ?? 1);
    return $hal < 1 ? 1 : $hal;
}

function ambilPaginasi(string $sqlBase, string $sqlCount, array $params, int $perHalaman = PER_HALAMAN): array
{
    $total = (int) kueriNilai($sqlCount, $params);
    $totalHal = max(1, (int) ceil($total / $perHalaman));
    $hal = min(halamanSaatIni(), $totalHal);
    $offset = ($hal - 1) * $perHalaman;
    $baris = kueri($sqlBase . " LIMIT $perHalaman OFFSET $offset", $params);
    return ['baris' => $baris, 'hal' => $hal, 'totalHal' => $totalHal, 'total' => $total];
}

function tampilPaginasi(int $hal, int $totalHal, array $queryTambahan = []): void
{
    if ($totalHal <= 1) {
        return;
    }
    $tautan = function (int $h) use ($queryTambahan) {
        return '?' . http_build_query(array_merge($queryTambahan, ['hal' => $h]));
    };
    echo '<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center mb-0">';
    echo '<li class="page-item' . ($hal <= 1 ? ' disabled' : '') . '">'
       . '<a class="page-link" href="' . htmlspecialchars($tautan(max(1, $hal - 1))) . '">&laquo;</a></li>';
    for ($i = 1; $i <= $totalHal; $i++) {
        echo '<li class="page-item' . ($i === $hal ? ' active' : '') . '">'
           . '<a class="page-link" href="' . htmlspecialchars($tautan($i)) . '">' . $i . '</a></li>';
    }
    echo '<li class="page-item' . ($hal >= $totalHal ? ' disabled' : '') . '">'
       . '<a class="page-link" href="' . htmlspecialchars($tautan(min($totalHal, $hal + 1))) . '">&raquo;</a></li>';
    echo '</ul></nav>';
}
```

- [ ] **Step 2: Tulis ulang `admin-config.php`**

```php
<?php
// admin-config.php — data portal admin, dibaca read-only dari database dbstarlite.
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()
require_once __DIR__ . '/db.php';        // db(): mysqli + kueri()/kueriSatu()/kueriNilai()
require_once __DIR__ . '/auth.php';      // sesi & guard
require_once __DIR__ . '/aksi.php';      // CSRF & flash
require_once __DIR__ . '/pagination.php';  // paginasi

wajibLoginAdmin();                       // halaman admin wajib login

// Admin yang sedang login (berdasarkan sesi)
$admin = kueriSatu("SELECT nama, email, peran FROM admin WHERE id = ?", [idAdminSaatIni()]);

// Daftar paket + jumlah pelanggan aktif (subquery)
$daftarPaket = kueri(
    "SELECT id, nama, kecepatan, harga, status,
            (SELECT COUNT(*) FROM pelanggan WHERE pelanggan.paket_id = paket.id) AS jumlahPelanggan
     FROM paket ORDER BY id"
);

// Daftar pelanggan (paket = kecepatan paketnya)
$daftarPelanggan = kueri(
    "SELECT pl.id, pl.nama, pl.email, pl.hp, pl.alamat, pk.kecepatan AS paket, pl.status, pl.tgl_bergabung AS bergabung
     FROM pelanggan pl LEFT JOIN paket pk ON pk.id = pl.paket_id
     ORDER BY pl.id"
);

// Daftar tagihan (gabung nama pelanggan & kecepatan paket)
$daftarTagihan = kueri(
    "SELECT t.id AS idTagihan, t.no_invoice AS noInvoice, pl.nama AS pelanggan, pk.kecepatan AS paket,
            t.harga, t.tanggal, t.status
     FROM tagihan t
     JOIN pelanggan pl ON pl.id = t.pelanggan_id
     LEFT JOIN paket pk ON pk.id = t.paket_id
     ORDER BY t.id"
);

// Daftar area cakupan
$daftarArea = kueri(
    "SELECT id, nama, kota, status, jumlah_pelanggan AS jumlahPelanggan FROM area ORDER BY id"
);

// Pengaturan situs
$pengaturan = kueriSatu(
    "SELECT nama_situs AS namaSitus, email, telepon, alamat FROM pengaturan LIMIT 1"
);

// Ringkasan statistik — dihitung nyata dari data
$statistik = [
    'totalPelanggan'  => (int) kueriNilai("SELECT COUNT(*) FROM pelanggan"),
    'pelangganAktif'  => (int) kueriNilai("SELECT COUNT(*) FROM pelanggan WHERE status = 'aktif'"),
    'pendapatanBulan' => (int) kueriNilai("SELECT COALESCE(SUM(harga), 0) FROM tagihan WHERE status = 'lunas'"),
    'tagihanPending'  => (int) kueriNilai("SELECT COUNT(*) FROM tagihan WHERE status = 'menunggu'"),
];
```

- [ ] **Step 3: Perbarui pemanggilan `ambilPaginasi` (lepas `$pdo`) di 4 halaman**

Di masing-masing file, ubah baris pemanggil:
- `admin/pelanggan.php`: `$hasil = ambilPaginasi($pdo, $sqlBase, $sqlCount, $params);` → `$hasil = ambilPaginasi($sqlBase, $sqlCount, $params);`
- `admin/transaksi.php`: idem.
- `admin/lead.php`: idem.
- `admin/notifikasi.php`: idem.

- [ ] **Step 4: Perbarui `admin/login.php` (kueriSatu)**

Ganti:
```php
    $stmt = db()->prepare("SELECT id, kata_sandi FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch();
```
menjadi:
```php
    $row = kueriSatu("SELECT id, kata_sandi FROM admin WHERE email = ?", [$email]);
```

- [ ] **Step 5: Lint**

```bash
for f in pagination.php admin-config.php admin/pelanggan.php admin/transaksi.php admin/lead.php admin/notifikasi.php admin/login.php; do /d/WebServer/xampp82/php/php.exe -l "$f"; done
```
Expected: semua "No syntax errors detected".

- [ ] **Step 6: Verifikasi (PowerShell) — login admin & semua halaman admin render**

```powershell
$base="http://localhost:8282/starlite/admin"
$d=Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -UseBasicParsing -ErrorAction SilentlyContinue
Write-Output ("LOGIN=" + ($d.Content -match 'Selamat datang'))
foreach($p in 'dashboard','pelanggan','paket','transaksi','lead','area','notifikasi','pengaturan'){
  $r=Invoke-WebRequest "$base/$p.php" -WebSession $s -UseBasicParsing -ErrorAction SilentlyContinue
  Write-Output ("$p=" + ($r.StatusCode -eq 200))
}
Write-Output ("PAG=" + ((Invoke-WebRequest "$base/pelanggan.php?hal=2" -WebSession $s -UseBasicParsing).Content -match 'Indah Permata'))
```
Expected: `LOGIN=True`, tiap halaman `True`, `PAG=True`.

- [ ] **Step 7: Commit**

```bash
git add pagination.php admin-config.php admin/pelanggan.php admin/transaksi.php admin/lead.php admin/notifikasi.php admin/login.php
git commit -m "refactor(db): baca admin (config, paginasi, halaman, login) pakai helper mysqli"
```

---

### Task 3: Migrasi handler tulis admin (aksi-*.php)

**Files:**
- Modify: `admin/aksi-paket.php`, `admin/aksi-area.php`, `admin/aksi-notifikasi.php`, `admin/aksi-pelanggan.php`, `admin/aksi-transaksi.php`, `admin/aksi-lead.php`, `admin/aksi-pengaturan.php`

**Interfaces:**
- Consumes: `eksekusi`, `kueriSatu` (Task 1).

Semua handler: **hapus baris `$pdo = db();` / `$pdo  = db();` / `$pdo     = db();`** lalu ganti query seperti di bawah.

- [ ] **Step 1: `admin/aksi-paket.php`**

Ganti blok INSERT/UPDATE/DELETE + catch:
```php
    if ($aksi === 'tambah') {
        eksekusi("INSERT INTO paket (nama, kecepatan, harga, status) VALUES (?, ?, ?, ?)",
            [$nama, $kecepatan, (int) $harga, $status]);
        setFlash('success', 'Paket berhasil ditambahkan.');
    } else {
        eksekusi("UPDATE paket SET nama = ?, kecepatan = ?, harga = ?, status = ? WHERE id = ?",
            [$nama, $kecepatan, (int) $harga, $status, (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Paket berhasil diperbarui.');
    }
} elseif ($aksi === 'hapus') {
    try {
        eksekusi("DELETE FROM paket WHERE id = ?", [(int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Paket berhasil dihapus.');
    } catch (mysqli_sql_exception $e) {
        setFlash('danger', 'Paket tidak bisa dihapus, masih dipakai pelanggan atau tagihan.');
    }
}
```

- [ ] **Step 2: `admin/aksi-area.php`**

```php
    if ($aksi === 'tambah') {
        eksekusi("INSERT INTO area (nama, kota, status, jumlah_pelanggan) VALUES (?, ?, ?, 0)",
            [$nama, $kota, $status]);
        setFlash('success', 'Area berhasil ditambahkan.');
    } else {
        eksekusi("UPDATE area SET nama = ?, kota = ?, status = ? WHERE id = ?",
            [$nama, $kota, $status, (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Area berhasil diperbarui.');
    }
} elseif ($aksi === 'hapus') {
    eksekusi("DELETE FROM area WHERE id = ?", [(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Area berhasil dihapus.');
}
```

- [ ] **Step 3: `admin/aksi-notifikasi.php`**

Ganti INSERT & DELETE:
```php
    eksekusi("INSERT INTO notifikasi (judul, isi, target, tanggal, status) VALUES (?, ?, ?, ?, 'terkirim')",
        [$judul, $isi, $target, $tanggal]);
    setFlash('success', 'Notifikasi berhasil dikirim.');
} elseif ($aksi === 'hapus') {
    eksekusi("DELETE FROM notifikasi WHERE id = ?", [(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Notifikasi berhasil dihapus.');
}
```

- [ ] **Step 4: `admin/aksi-pelanggan.php`**

```php
    eksekusi("UPDATE pelanggan SET nama = ?, email = ?, hp = ?, alamat = ? WHERE id = ?",
        [$nama, $email, $hp, $alamat, $id]);
    setFlash('success', 'Data pelanggan berhasil diperbarui.');
} elseif ($aksi === 'toggle') {
    eksekusi("UPDATE pelanggan SET status = IF(status = 'aktif', 'nonaktif', 'aktif') WHERE id = ?", [$id]);
    setFlash('success', 'Status pelanggan berhasil diubah.');
}
```

- [ ] **Step 5: `admin/aksi-transaksi.php`**

```php
if (($_POST['aksi'] ?? '') === 'lunas') {
    eksekusi("UPDATE tagihan SET status = 'lunas' WHERE id = ?", [(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Tagihan ditandai lunas.');
}
```

- [ ] **Step 6: `admin/aksi-lead.php`**

```php
if (($_POST['aksi'] ?? '') === 'dihubungi') {
    eksekusi("UPDATE prospek SET status = 'dihubungi' WHERE id = ?", [$_POST['id'] ?? '']);
    setFlash('success', 'Lead ditandai sudah dihubungi.');
}
```

- [ ] **Step 7: `admin/aksi-pengaturan.php`**

Ganti tiga cabang (hapus `$pdo` juga):
```php
if ($aksi === 'profil') {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($nama === '' || $email === '') {
        setFlash('danger', 'Nama & email wajib diisi.');
    } else {
        eksekusi("UPDATE admin SET nama = ?, email = ? WHERE id = ?", [$nama, $email, $idAdmin]);
        setFlash('success', 'Profil admin berhasil disimpan.');
    }
} elseif ($aksi === 'password') {
    $lama       = $_POST['lama'] ?? '';
    $baru       = $_POST['baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $row = kueriSatu("SELECT kata_sandi FROM admin WHERE id = ?", [$idAdmin]);
    if (!$row || !password_verify($lama, $row['kata_sandi'])) {
        setFlash('danger', 'Password lama salah.');
    } elseif (strlen($baru) < 6 || $baru !== $konfirmasi) {
        setFlash('danger', 'Password baru minimal 6 karakter & harus sama dengan konfirmasi.');
    } else {
        eksekusi("UPDATE admin SET kata_sandi = ? WHERE id = ?", [password_hash($baru, PASSWORD_DEFAULT), $idAdmin]);
        setFlash('success', 'Password berhasil diperbarui.');
    }
} elseif ($aksi === 'situs') {
    $namaSitus = trim($_POST['nama_situs'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telepon   = trim($_POST['telepon'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');
    eksekusi("UPDATE pengaturan SET nama_situs = ?, email = ?, telepon = ?, alamat = ? WHERE id = 1",
        [$namaSitus, $email, $telepon, $alamat]);
    setFlash('success', 'Pengaturan situs berhasil disimpan.');
}
```

- [ ] **Step 8: Lint semua handler admin**

```bash
for f in admin/aksi-paket.php admin/aksi-area.php admin/aksi-notifikasi.php admin/aksi-pelanggan.php admin/aksi-transaksi.php admin/aksi-lead.php admin/aksi-pengaturan.php; do /d/WebServer/xampp82/php/php.exe -l "$f"; done
```
Expected: semua "No syntax errors detected".

- [ ] **Step 9: Verifikasi (PowerShell) — tandai lunas + rollback**

```powershell
$base="http://localhost:8282/starlite/admin"; $mysql="D:\WebServer\xampp82\mysql\bin\mysql.exe"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/transaksi.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-transaksi.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='lunas';id='3'} -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
& $mysql -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "SELECT status FROM tagihan WHERE id=3;"
& $mysql -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "UPDATE tagihan SET status='menunggu' WHERE id=3;"
```
Expected: status `lunas` (lalu dikembalikan).

- [ ] **Step 10: Commit**

```bash
git add admin/aksi-paket.php admin/aksi-area.php admin/aksi-notifikasi.php admin/aksi-pelanggan.php admin/aksi-transaksi.php admin/aksi-lead.php admin/aksi-pengaturan.php
git commit -m "refactor(db): handler tulis admin pakai eksekusi()/kueriSatu() mysqli"
```

---

### Task 4: Migrasi portal (config + halaman + login + handler)

**Files:**
- Modify: `portal-config.php` (tulis ulang)
- Modify: `portal/transaksi.php` (satu baris `ambilPaginasi`)
- Modify: `portal/login.php` (kueriSatu)
- Modify: `portal/aksi-profil.php`, `portal/aksi-paket.php`

**Interfaces:**
- Consumes: `kueri`, `kueriSatu`, `eksekusi`, `ambilPaginasi` (tanpa `$pdo`).

- [ ] **Step 1: Tulis ulang `portal-config.php`**

```php
<?php
// portal-config.php — data portal pelanggan, dibaca read-only dari database dbstarlite.
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()
require_once __DIR__ . '/db.php';        // db(): mysqli + kueri()/kueriSatu()
require_once __DIR__ . '/auth.php';      // sesi & guard
require_once __DIR__ . '/aksi.php';      // CSRF & flash
require_once __DIR__ . '/pagination.php';  // paginasi

wajibLoginPelanggan();                   // halaman portal wajib login

$idPelanggan = idPelangganSaatIni();     // pelanggan dari sesi

// Data pelanggan yang sedang login
$pelanggan = kueriSatu("SELECT id, nama, email, hp, alamat, paket_id FROM pelanggan WHERE id = ?", [$idPelanggan]);

// Paket internet yang sedang aktif (+ masa aktif presentasional)
$paketAktif = kueriSatu(
    "SELECT pk.nama, pk.kecepatan, pk.harga, pk.status
     FROM pelanggan pl JOIN paket pk ON pk.id = pl.paket_id
     WHERE pl.id = ?",
    [$idPelanggan]
);
$paketAktif['masaAktif'] = '15 Juli 2026';

// Riwayat transaksi pelanggan (urut sesuai seed: Jun, Mei, Apr, Jul)
$daftarTransaksi = kueri(
    "SELECT t.no_invoice AS noInvoice, pk.nama AS paket, pk.kecepatan AS kecepatan,
            t.harga, t.tanggal, t.status
     FROM tagihan t JOIN paket pk ON pk.id = t.paket_id
     WHERE t.pelanggan_id = ?
     ORDER BY t.id",
    [$idPelanggan]
);

// Pilihan paket pada halaman "Pilih Paket" — harga dari DB, fitur & flag presentasional
$fiturPaket = [
    '100 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi'],
    '200 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Harga promo'],
    '500 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Prioritas jaringan'],
];
$paketTersedia = [];
foreach (kueri("SELECT id, nama, kecepatan, harga FROM paket ORDER BY id") as $row) {
    $row['fitur']   = $fiturPaket[$row['kecepatan']] ?? [];
    $row['dipilih'] = ((int) $row['id'] === (int) ($pelanggan['paket_id'] ?? 0));
    $paketTersedia[] = $row;
}

// Feed notifikasi & informasi untuk panel offcanvas — presentasional (tetap statis)
$daftarNotifikasi = [
    ['tipe' => 'notifikasi', 'judul' => 'Pembayaran Berhasil', 'isi' => 'Tagihan INV/2026/06/008812 sebesar Rp100.000 telah dibayar.', 'waktu' => '15 Jun 2026, 09:14'],
    ['tipe' => 'informasi',  'judul' => 'Internet Aktif',       'isi' => 'Paket 200 Mbps Starlite aktif hingga 15 Juli 2026.', 'waktu' => '15 Jun 2026, 09:15'],
    ['tipe' => 'informasi',  'judul' => 'Promo Upgrade 500 Mbps', 'isi' => 'Nikmati internet 500 Mbps hanya Rp250.000/bulan. Unlimited!', 'waktu' => '10 Jun 2026, 12:00'],
    ['tipe' => 'notifikasi', 'judul' => 'Pemeliharaan Sistem',  'isi' => 'Pemeliharaan terjadwal 20 Jun 2026, 01:00-03:00 WIB.', 'waktu' => '08 Jun 2026, 17:30'],
];
```

- [ ] **Step 2: `portal/transaksi.php`**

Ubah: `$hasil = ambilPaginasi($pdo, $sqlBase, $sqlCount, [$idPelanggan]);` → `$hasil = ambilPaginasi($sqlBase, $sqlCount, [$idPelanggan]);`

- [ ] **Step 3: `portal/login.php`**

Ganti:
```php
    $stmt = db()->prepare("SELECT id, kata_sandi FROM pelanggan WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch();
```
menjadi:
```php
    $row = kueriSatu("SELECT id, kata_sandi FROM pelanggan WHERE email = ?", [$email]);
```

- [ ] **Step 4: `portal/aksi-profil.php`** (hapus `$pdo  = db();`)

```php
    eksekusi("UPDATE pelanggan SET nama = ?, email = ?, hp = ?, alamat = ? WHERE id = ?",
        [$nama, $email, $hp, $alamat, $id]);
    setFlash('success', 'Informasi akun berhasil disimpan.');
} elseif ($aksi === 'password') {
    $lama       = $_POST['lama'] ?? '';
    $baru       = $_POST['baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $row = kueriSatu("SELECT kata_sandi FROM pelanggan WHERE id = ?", [$id]);
    if (!$row || !password_verify($lama, $row['kata_sandi'])) {
        setFlash('danger', 'Password lama salah.');
    } elseif (strlen($baru) < 6 || $baru !== $konfirmasi) {
        setFlash('danger', 'Password baru minimal 6 karakter & harus sama dengan konfirmasi.');
    } else {
        eksekusi("UPDATE pelanggan SET kata_sandi = ? WHERE id = ?", [password_hash($baru, PASSWORD_DEFAULT), $id]);
        setFlash('success', 'Password berhasil diperbarui.');
    }
}
```

- [ ] **Step 5: `portal/aksi-paket.php`** (hapus `$pdo = db();`)

Ganti blok cek + update:
```php
    // Pastikan paket ada sebelum update
    if (!kueriSatu("SELECT id FROM paket WHERE id = ?", [(int) $paketId])) {
        setFlash('danger', 'Paket tidak ditemukan.');
        header('Location: paket.php');
        exit;
    }
    eksekusi("UPDATE pelanggan SET paket_id = ? WHERE id = ?", [(int) $paketId, $id]);
    setFlash('success', 'Paket aktif berhasil diubah.');
```

- [ ] **Step 6: Lint**

```bash
for f in portal-config.php portal/transaksi.php portal/login.php portal/aksi-profil.php portal/aksi-paket.php; do /d/WebServer/xampp82/php/php.exe -l "$f"; done
```
Expected: semua "No syntax errors detected".

- [ ] **Step 7: Verifikasi (PowerShell) — login pelanggan + ubah paket + rollback**

```powershell
$base="http://localhost:8282/starlite/portal"; $mysql="D:\WebServer\xampp82\mysql\bin\mysql.exe"
$d=Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='dwi.anjasmoro@gmail.com';kata_sandi='pelanggan123'} -SessionVariable s -UseBasicParsing -ErrorAction SilentlyContinue
Write-Output ("LOGIN=" + ($d.Content -match 'Dwi Anjasmoro'))
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/paket.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-paket.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='pilih';paket_id='3'} -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
& $mysql -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "SELECT paket_id FROM pelanggan WHERE id='STL-2024-008812';"
& $mysql -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "UPDATE pelanggan SET paket_id=2 WHERE id='STL-2024-008812';"
```
Expected: `LOGIN=True`, `paket_id`=`3` (lalu dikembalikan ke `2`).

- [ ] **Step 8: Commit**

```bash
git add portal-config.php portal/transaksi.php portal/login.php portal/aksi-profil.php portal/aksi-paket.php
git commit -m "refactor(db): portal (config, halaman, login, handler) pakai helper mysqli"
```

---

### Task 5: Verifikasi menyeluruh (E2E) + dokumentasi

**Files:**
- Modify: `CLAUDE.md` (PDO → mysqli)
- Modify: `docs/DOKUMENTASI.md` (arsitektur & keamanan: mysqli)

- [ ] **Step 1: Jalankan suite E2E 39-cek**

Jalankan dua sweep PowerShell dari sesi sebelumnya (publik/guard/login/logout/halaman + CRUD/CSRF/pagination). Expected: **39/39 PASS**, tak ada FAIL.

- [ ] **Step 2: Verifikasi DB kembali ke seed**

```powershell
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "SELECT (SELECT COUNT(*) FROM pelanggan) pel,(SELECT COUNT(*) FROM tagihan) tag,(SELECT paket_id FROM pelanggan WHERE id='STL-2024-008812') dwi,(SELECT status FROM pelanggan WHERE id='STL-2024-008815') maya;"
```
Expected: `6 8 2 nonaktif`.

- [ ] **Step 3: Perbarui `CLAUDE.md`**

Ganti setiap penyebutan "PDO" di bagian data menjadi mysqli, mis. "via `db.php` (PDO)" → "via `db.php` (mysqli, helper `kueri`/`kueriSatu`/`kueriNilai`/`eksekusi`)". Sesuaikan kalimat yang menyebut prepared statement PDO.

- [ ] **Step 4: Perbarui `docs/DOKUMENTASI.md`**

Di bagian Arsitektur & Keamanan, ganti "PDO singleton"/"prepared statement (PDO)" menjadi mysqli + helper; sebut konstanta setelan `DB_*` di `db.php`.

- [ ] **Step 5: Commit**

```bash
git add CLAUDE.md docs/DOKUMENTASI.md
git commit -m "docs: perbarui dokumentasi untuk lapisan data mysqli"
```

---

## Catatan Penutup

Setelah 5 task: seluruh akses data memakai mysqli via helper; setelan koneksi (port 3382) di satu blok konstanta `db.php`. Skema, data, SQL, dan markup tak berubah; E2E 39-cek tetap lulus. Data uji selalu di-rollback ke seed.
# Admin CRUD Fase 2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menjadikan semua aksi tulis admin nyata (INSERT/UPDATE/DELETE) dengan CSRF, flash, dan Post-Redirect-Get.

**Architecture:** `aksi.php` menyediakan CSRF + flash. Tiap halaman list mem-POST form ke handler `admin/aksi-<entitas>.php` yang melakukan `wajibLoginAdmin()` + `cekCsrf()`, menulis ke DB via prepared statement, `setFlash()`, lalu redirect balik (PRG). Flash ditampilkan di `admin/partials/shell-open.php`.

**Tech Stack:** PHP native (PDO prepared statements, session CSRF, password_hash), MySQL `dbstarlite` @ 3382.

## Global Constraints

- Aksi via POST ke `admin/aksi-<entitas>.php`, lalu redirect (PRG). Handler: `wajibLoginAdmin(); cekCsrf();`.
- CSRF: tiap form punya `<input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">`; `cekCsrf()` verifikasi `hash_equals` saat POST.
- Flash sukses/gagal via session, tampil sekali di atas konten admin.
- Prepared statement untuk semua query. Output di-`htmlspecialchars()`.
- Hapus: paket/area/notifikasi (konfirmasi `confirm()`); pelanggan hanya toggle aktif/nonaktif.
- Hapus paket terpakai (FK) → tangkap `PDOException` → flash danger.
- Ubah password admin: verifikasi lama (`password_verify`) lalu `password_hash` baru.
- Kode & komentar Bahasa Indonesia. Lint tiap `.php`: `/d/WebServer/xampp82/php/php.exe -l <file>`.
- Verifikasi: lint + uji HTTP via PowerShell (login → POST dgn csrf → cek DB) + BrowserOS. Commit per task, prefix `feat(crud):`.

---

### Task 1: Infrastruktur — aksi.php + flash di shell + id di config

Buat helper CSRF/flash, tampilkan flash di shell, dan tambahkan kolom id yang dibutuhkan form ke query `admin-config.php`.

**Files:**
- Create: `aksi.php`
- Modify: `admin-config.php` (require aksi.php; tambah id/alamat/idTagihan ke query)
- Modify: `admin/partials/shell-open.php` (panggil `tampilFlash()`)

**Interfaces:**
- Produces: `tokenCsrf(): string`, `cekCsrf(): void`, `setFlash(string $tipe, string $pesan): void`, `tampilFlash(): void`. Query config kini menyediakan: `$daftarPaket[].id`, `$daftarArea[].id`, `$daftarNotifikasi[].id`, `$daftarTagihan[].idTagihan`, `$daftarPelanggan[].alamat`.

- [ ] **Step 1: Buat `aksi.php`**

```php
<?php
// aksi.php — token CSRF & flash message untuk aksi tulis admin.
require_once __DIR__ . '/auth.php';   // mulaiSesi()

function tokenCsrf(): string
{
    mulaiSesi();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function cekCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    mulaiSesi();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('CSRF token tidak valid.');
    }
}

function setFlash(string $tipe, string $pesan): void
{
    mulaiSesi();
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function tampilFlash(): void
{
    mulaiSesi();
    if (empty($_SESSION['flash'])) {
        return;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $tipe = $f['tipe'] === 'danger' ? 'danger' : 'success';
    echo '<div class="alert alert-' . $tipe . ' alert-dismissible fade show" role="alert">'
       . htmlspecialchars($f['pesan'])
       . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>';
}
```

- [ ] **Step 2: Wire aksi.php + tambah kolom id di `admin-config.php`**

Tambahkan require setelah baris `require_once __DIR__ . '/auth.php';`:
```php
require_once __DIR__ . '/aksi.php';      // CSRF & flash
```

Ubah query `$daftarPaket` menjadi (tambah `id`):
```php
$daftarPaket = $pdo->query(
    "SELECT id, nama, kecepatan, harga, status,
            (SELECT COUNT(*) FROM pelanggan WHERE pelanggan.paket_id = paket.id) AS jumlahPelanggan
     FROM paket ORDER BY id"
)->fetchAll();
```

Ubah `$daftarPelanggan` (tambah `pl.alamat`):
```php
$daftarPelanggan = $pdo->query(
    "SELECT pl.id, pl.nama, pl.email, pl.hp, pl.alamat, pk.kecepatan AS paket, pl.status, pl.tgl_bergabung AS bergabung
     FROM pelanggan pl LEFT JOIN paket pk ON pk.id = pl.paket_id
     ORDER BY pl.id"
)->fetchAll();
```

Ubah `$daftarTagihan` (tambah `t.id AS idTagihan`):
```php
$daftarTagihan = $pdo->query(
    "SELECT t.id AS idTagihan, t.no_invoice AS noInvoice, pl.nama AS pelanggan, pk.kecepatan AS paket,
            t.harga, t.tanggal, t.status
     FROM tagihan t
     JOIN pelanggan pl ON pl.id = t.pelanggan_id
     LEFT JOIN paket pk ON pk.id = t.paket_id
     ORDER BY t.id"
)->fetchAll();
```

Ubah `$daftarArea` (tambah `id`):
```php
$daftarArea = $pdo->query(
    "SELECT id, nama, kota, status, jumlah_pelanggan AS jumlahPelanggan FROM area ORDER BY id"
)->fetchAll();
```

Ubah `$daftarNotifikasi` (tambah `id`):
```php
$daftarNotifikasi = $pdo->query(
    "SELECT id, judul, isi, target, tanggal, status FROM notifikasi ORDER BY id"
)->fetchAll();
```

- [ ] **Step 3: Tampilkan flash di `admin/partials/shell-open.php`**

Ubah baris pembuka konten:
```php
    <div class="portal-content">
```
menjadi:
```php
    <div class="portal-content">
      <?php tampilFlash(); ?>
```

- [ ] **Step 4: Lint**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l aksi.php
/d/WebServer/xampp82/php/php.exe -l admin-config.php
/d/WebServer/xampp82/php/php.exe -l admin/partials/shell-open.php
```
Expected: semua "No syntax errors detected".

- [ ] **Step 5: Verifikasi (PowerShell) — halaman admin tetap jalan**

Login & buka dashboard (pastikan tak ada error fatal akibat perubahan config):
```powershell
$base="http://localhost:8282/starlite/admin"
$r=Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue
$d=Invoke-WebRequest "$base/transaksi.php" -WebSession $s -UseBasicParsing -ErrorAction SilentlyContinue
Write-Output ("TRANSAKSI_OK=" + ($d.Content -match 'Tandai Lunas'))
```
Expected: `TRANSAKSI_OK=True` (halaman render).

- [ ] **Step 6: Commit**

```bash
git add aksi.php admin-config.php admin/partials/shell-open.php
git commit -m "feat(crud): infrastruktur aksi.php (CSRF, flash) + id kolom di admin-config"
```

---

### Task 2: Paket CRUD (tambah/edit/hapus)

**Files:**
- Create: `admin/aksi-paket.php`
- Modify: `admin/paket.php`

**Interfaces:**
- Consumes: `tokenCsrf()`, `cekCsrf()`, `setFlash()`, `wajibLoginAdmin()`, `db()`; `$daftarPaket[].id`.

- [ ] **Step 1: Buat `admin/aksi-paket.php`**

```php
<?php
// aksi-paket.php — tambah/edit/hapus paket (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$pdo  = db();
$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah' || $aksi === 'edit') {
    $nama      = trim($_POST['nama'] ?? '');
    $kecepatan = trim($_POST['kecepatan'] ?? '');
    $harga     = $_POST['harga'] ?? '';
    $status    = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';
    if ($nama === '' || $kecepatan === '' || !is_numeric($harga)) {
        setFlash('danger', 'Data paket tidak lengkap atau harga tidak valid.');
        header('Location: paket.php');
        exit;
    }
    if ($aksi === 'tambah') {
        $stmt = $pdo->prepare("INSERT INTO paket (nama, kecepatan, harga, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $kecepatan, (int) $harga, $status]);
        setFlash('success', 'Paket berhasil ditambahkan.');
    } else {
        $stmt = $pdo->prepare("UPDATE paket SET nama = ?, kecepatan = ?, harga = ?, status = ? WHERE id = ?");
        $stmt->execute([$nama, $kecepatan, (int) $harga, $status, (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Paket berhasil diperbarui.');
    }
} elseif ($aksi === 'hapus') {
    try {
        $stmt = $pdo->prepare("DELETE FROM paket WHERE id = ?");
        $stmt->execute([(int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Paket berhasil dihapus.');
    } catch (PDOException $e) {
        setFlash('danger', 'Paket tidak bisa dihapus, masih dipakai pelanggan atau tagihan.');
    }
}
header('Location: paket.php');
exit;
```

- [ ] **Step 2: Ubah kartu di `admin/paket.php` (tambah data-id + tombol Hapus)**

Ganti blok tombol Edit (`<button ... btn-edit-paket ...>...Edit Paket</button>`) menjadi tombol Edit (dengan `data-id`) + form Hapus:
```php
        <button type="button" class="btn btn-outline-primary w-100 btn-edit-paket"
          data-mode="edit"
          data-id="<?= $p['id'] ?>"
          data-nama="<?= htmlspecialchars($p['nama']) ?>"
          data-kecepatan="<?= htmlspecialchars($p['kecepatan']) ?>"
          data-harga="<?= $p['harga'] ?>"
          data-status="<?= htmlspecialchars($p['status']) ?>"
          data-bs-toggle="modal" data-bs-target="#modalEditPaket">
          <i class="bi bi-pencil me-1"></i>Edit Paket
        </button>
        <form method="post" action="aksi-paket.php" class="mt-2" onsubmit="return confirm('Hapus paket ini?')">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
```

- [ ] **Step 3: Ubah modal form di `admin/paket.php` jadi POST nyata**

Ganti `<form id="formPaket" class="row g-3">` ... s/d `</form>` (sebelum blok `#suksesPaket`) dengan form POST; dan hapus div `#suksesPaket`:
```php
          <form id="formPaket" method="post" action="aksi-paket.php" class="row g-3">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" id="paketAksi" value="tambah">
            <input type="hidden" name="id" id="paketId" value="">
            <div class="col-12">
              <label class="form-label fw-500 small">Nama Paket</label>
              <input type="text" name="nama" class="form-control" id="paketNama" placeholder="Paket ... Mbps Starlite" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Kecepatan</label>
              <input type="text" name="kecepatan" class="form-control" id="paketKecepatan" placeholder="100 Mbps" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Harga / bulan (Rp)</label>
              <input type="number" name="harga" class="form-control" id="paketHarga" placeholder="199000" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Status</label>
              <select name="status" class="form-select" id="paketStatus"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-st btn-lg">Simpan Paket</button>
            </div>
          </form>
```

- [ ] **Step 4: Ganti script modal di `admin/paket.php` (isi field by name, set aksi/id; hapus mock submit)**

Ganti seluruh blok `<script> ... </script>` dengan:
```php
  <script>
    // Isi modal sesuai mode (tambah / edit)
    document.getElementById('modalEditPaket').addEventListener('show.bs.modal', (e) => {
      const d = e.relatedTarget.dataset;
      const edit = d.mode === 'edit';
      document.getElementById('judulModalPaket').textContent = edit ? 'Edit Paket' : 'Tambah Paket';
      document.getElementById('paketAksi').value = edit ? 'edit' : 'tambah';
      document.getElementById('paketId').value = edit ? d.id : '';
      document.getElementById('paketNama').value = edit ? d.nama : '';
      document.getElementById('paketKecepatan').value = edit ? d.kecepatan : '';
      document.getElementById('paketHarga').value = edit ? d.harga : '';
      document.getElementById('paketStatus').value = edit ? d.status : 'aktif';
    });
  </script>
```

- [ ] **Step 5: Lint**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l admin/aksi-paket.php
/d/WebServer/xampp82/php/php.exe -l admin/paket.php
```
Expected: "No syntax errors detected".

- [ ] **Step 6: Verifikasi (PowerShell) — tambah lalu hapus paket**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok = ([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/paket.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-paket.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='tambah';nama='Paket Uji 1 Gbps';kecepatan='1 Gbps';harga='999000';status='aktif'} -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$p=Invoke-WebRequest "$base/paket.php" -WebSession $s -UseBasicParsing
Write-Output ("ADA_PAKET_BARU=" + ($p.Content -match 'Paket Uji 1 Gbps'))
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "DELETE FROM paket WHERE nama='Paket Uji 1 Gbps';"
```
Expected: `ADA_PAKET_BARU=True` (lalu baris uji dibersihkan).

- [ ] **Step 7: Commit**

```bash
git add admin/aksi-paket.php admin/paket.php
git commit -m "feat(crud): paket tambah/edit/hapus nyata (POST + CSRF + flash)"
```

---

### Task 3: Area CRUD (tambah/edit/hapus)

**Files:**
- Create: `admin/aksi-area.php`
- Modify: `admin/area.php`

**Interfaces:**
- Consumes: `tokenCsrf()`, `cekCsrf()`, `setFlash()`, `wajibLoginAdmin()`, `db()`; `$daftarArea[].id`.

- [ ] **Step 1: Buat `admin/aksi-area.php`**

```php
<?php
// aksi-area.php — tambah/edit/hapus area (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$pdo  = db();
$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah' || $aksi === 'edit') {
    $nama   = trim($_POST['nama'] ?? '');
    $kota   = trim($_POST['kota'] ?? '');
    $status = ($_POST['status'] ?? 'tercakup') === 'segera' ? 'segera' : 'tercakup';
    if ($nama === '' || $kota === '') {
        setFlash('danger', 'Nama area & kota wajib diisi.');
        header('Location: area.php');
        exit;
    }
    if ($aksi === 'tambah') {
        $stmt = $pdo->prepare("INSERT INTO area (nama, kota, status, jumlah_pelanggan) VALUES (?, ?, ?, 0)");
        $stmt->execute([$nama, $kota, $status]);
        setFlash('success', 'Area berhasil ditambahkan.');
    } else {
        $stmt = $pdo->prepare("UPDATE area SET nama = ?, kota = ?, status = ? WHERE id = ?");
        $stmt->execute([$nama, $kota, $status, (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Area berhasil diperbarui.');
    }
} elseif ($aksi === 'hapus') {
    $stmt = $pdo->prepare("DELETE FROM area WHERE id = ?");
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Area berhasil dihapus.');
}
header('Location: area.php');
exit;
```

- [ ] **Step 2: Ubah kartu di `admin/area.php` (data-id + tombol Hapus)**

Ganti blok tombol `<button ... btn-edit-area ...>...Edit Area</button>` menjadi:
```php
        <button type="button" class="btn btn-outline-primary w-100 btn-edit-area"
          data-mode="edit"
          data-id="<?= $a['id'] ?>"
          data-nama="<?= htmlspecialchars($a['nama']) ?>"
          data-kota="<?= htmlspecialchars($a['kota']) ?>"
          data-status="<?= htmlspecialchars($a['status']) ?>"
          data-bs-toggle="modal" data-bs-target="#modalEditArea">
          <i class="bi bi-pencil me-1"></i>Edit Area
        </button>
        <form method="post" action="aksi-area.php" class="mt-2" onsubmit="return confirm('Hapus area ini?')">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" value="<?= $a['id'] ?>">
          <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
```

- [ ] **Step 3: Ubah modal form di `admin/area.php` jadi POST + hapus #suksesArea**

Ganti `<form id="formArea" class="row g-3">` … `</form>` dan hapus div `#suksesArea`:
```php
          <form id="formArea" method="post" action="aksi-area.php" class="row g-3">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" id="areaAksi" value="tambah">
            <input type="hidden" name="id" id="areaId" value="">
            <div class="col-12">
              <label class="form-label fw-500 small">Nama Area</label>
              <input type="text" name="nama" class="form-control" id="areaNama" placeholder="cth. Cibinong" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Kota</label>
              <input type="text" name="kota" class="form-control" id="areaKota" placeholder="cth. Bogor" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Status</label>
              <select name="status" class="form-select" id="areaStatus">
                <option value="tercakup">Tercakup</option>
                <option value="segera">Segera</option>
              </select>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-st btn-lg">Simpan Area</button>
            </div>
          </form>
```

- [ ] **Step 4: Ganti script di `admin/area.php`**

```php
  <script>
    document.getElementById('modalEditArea').addEventListener('show.bs.modal', (e) => {
      const d = e.relatedTarget.dataset;
      const edit = d.mode === 'edit';
      document.getElementById('judulModalArea').textContent = edit ? 'Edit Area' : 'Tambah Area';
      document.getElementById('areaAksi').value = edit ? 'edit' : 'tambah';
      document.getElementById('areaId').value = edit ? d.id : '';
      document.getElementById('areaNama').value = edit ? d.nama : '';
      document.getElementById('areaKota').value = edit ? d.kota : '';
      document.getElementById('areaStatus').value = edit ? d.status : 'tercakup';
    });
  </script>
```

- [ ] **Step 5: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin/aksi-area.php
/d/WebServer/xampp82/php/php.exe -l admin/area.php
```
Expected: "No syntax errors detected".

- [ ] **Step 6: Verifikasi (PowerShell)**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/area.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-area.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='tambah';nama='Area Uji';kota='Kota Uji';status='segera'} -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$a=Invoke-WebRequest "$base/area.php" -WebSession $s -UseBasicParsing
Write-Output ("ADA_AREA=" + ($a.Content -match 'Area Uji'))
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "DELETE FROM area WHERE nama='Area Uji';"
```
Expected: `ADA_AREA=True`.

- [ ] **Step 7: Commit**

```bash
git add admin/aksi-area.php admin/area.php
git commit -m "feat(crud): area tambah/edit/hapus nyata (POST + CSRF + flash)"
```

---

### Task 4: Notifikasi (tambah/hapus)

**Files:**
- Create: `admin/aksi-notifikasi.php`
- Modify: `admin/notifikasi.php`

**Interfaces:**
- Consumes: `tokenCsrf()`, `cekCsrf()`, `setFlash()`, `wajibLoginAdmin()`, `db()`; `$daftarNotifikasi[].id`.

- [ ] **Step 1: Buat `admin/aksi-notifikasi.php`**

```php
<?php
// aksi-notifikasi.php — tambah/hapus notifikasi (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$pdo  = db();
$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah') {
    $judul  = trim($_POST['judul'] ?? '');
    $isi    = trim($_POST['isi'] ?? '');
    $target = trim($_POST['target'] ?? 'Semua pelanggan');
    if ($judul === '' || $isi === '') {
        setFlash('danger', 'Judul & isi notifikasi wajib diisi.');
        header('Location: notifikasi.php');
        exit;
    }
    // Tanggal hari ini dalam format tampilan Bahasa Indonesia
    $bulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
              7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $tanggal = date('d') . ' ' . $bulan[(int) date('n')] . ' ' . date('Y');
    $stmt = $pdo->prepare("INSERT INTO notifikasi (judul, isi, target, tanggal, status) VALUES (?, ?, ?, ?, 'terkirim')");
    $stmt->execute([$judul, $isi, $target, $tanggal]);
    setFlash('success', 'Notifikasi berhasil dikirim.');
} elseif ($aksi === 'hapus') {
    $stmt = $pdo->prepare("DELETE FROM notifikasi WHERE id = ?");
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Notifikasi berhasil dihapus.');
}
header('Location: notifikasi.php');
exit;
```

- [ ] **Step 2: Tambah kolom Aksi (Hapus) di tabel `admin/notifikasi.php`**

Ubah header tabel:
```php
          <tr><th>Judul</th><th>Target</th><th>Tanggal</th><th>Status</th><th class="text-end">Aksi</th></tr>
```
Tambah sel aksi sebelum `</tr>` tiap baris (setelah sel status):
```php
            <td class="text-end">
              <form method="post" action="aksi-notifikasi.php" onsubmit="return confirm('Hapus notifikasi ini?')">
                <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="id" value="<?= $n['id'] ?>">
                <button type="submit" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
```

- [ ] **Step 3: Ubah modal tulis jadi POST + hapus #suksesNotif & script mock**

Ganti `<form id="formNotif" class="row g-3">` … `</form>` dan hapus div `#suksesNotif`:
```php
          <form id="formNotif" method="post" action="aksi-notifikasi.php" class="row g-3">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" value="tambah">
            <div class="col-12">
              <label class="form-label fw-500 small">Judul</label>
              <input type="text" name="judul" class="form-control" placeholder="Judul notifikasi" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Target</label>
              <select name="target" class="form-select">
                <option>Semua pelanggan</option>
                <option>Pelanggan aktif</option>
                <option>Pelanggan baru</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Isi Pesan</label>
              <textarea name="isi" class="form-control" rows="3" placeholder="Tulis isi notifikasi..." required></textarea>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-st btn-lg">Kirim Notifikasi</button>
            </div>
          </form>
```

Pada blok `<script>`, hapus listener submit mock `formNotif` (yang `e.preventDefault()` + tampil sukses). Sisakan hanya logika filter `#filterNotif`.

- [ ] **Step 4: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin/aksi-notifikasi.php
/d/WebServer/xampp82/php/php.exe -l admin/notifikasi.php
```
Expected: "No syntax errors detected".

- [ ] **Step 5: Verifikasi (PowerShell)**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/notifikasi.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-notifikasi.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='tambah';judul='Notif Uji';isi='Isi uji';target='Semua pelanggan'} -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$n=Invoke-WebRequest "$base/notifikasi.php" -WebSession $s -UseBasicParsing
Write-Output ("ADA_NOTIF=" + ($n.Content -match 'Notif Uji'))
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "DELETE FROM notifikasi WHERE judul='Notif Uji';"
```
Expected: `ADA_NOTIF=True`.

- [ ] **Step 6: Commit**

```bash
git add admin/aksi-notifikasi.php admin/notifikasi.php
git commit -m "feat(crud): notifikasi tambah/hapus nyata (POST + CSRF + flash)"
```

---

### Task 5: Pelanggan (edit + toggle status)

**Files:**
- Create: `admin/aksi-pelanggan.php`
- Modify: `admin/pelanggan.php`

**Interfaces:**
- Consumes: `tokenCsrf()`, `cekCsrf()`, `setFlash()`, `wajibLoginAdmin()`, `db()`; `$daftarPelanggan[].alamat`.

- [ ] **Step 1: Buat `admin/aksi-pelanggan.php`**

```php
<?php
// aksi-pelanggan.php — edit data & toggle status pelanggan (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$pdo  = db();
$aksi = $_POST['aksi'] ?? '';
$id   = $_POST['id'] ?? '';

if ($aksi === 'edit') {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $hp     = trim($_POST['hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    if ($nama === '' || $email === '' || $hp === '') {
        setFlash('danger', 'Nama, email, dan No. HP wajib diisi.');
        header('Location: pelanggan.php');
        exit;
    }
    $stmt = $pdo->prepare("UPDATE pelanggan SET nama = ?, email = ?, hp = ?, alamat = ? WHERE id = ?");
    $stmt->execute([$nama, $email, $hp, $alamat, $id]);
    setFlash('success', 'Data pelanggan berhasil diperbarui.');
} elseif ($aksi === 'toggle') {
    $stmt = $pdo->prepare("UPDATE pelanggan SET status = IF(status = 'aktif', 'nonaktif', 'aktif') WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Status pelanggan berhasil diubah.');
}
header('Location: pelanggan.php');
exit;
```

- [ ] **Step 2: Tambah `data-alamat` di tombol Detail `admin/pelanggan.php`**

Pada `<button ... btn-detail ...>`, tambahkan atribut `data-alamat`:
```php
                data-alamat="<?= htmlspecialchars($p['alamat'] ?? '') ?>"
```
(letakkan setelah `data-status="..."`).

- [ ] **Step 3: Ganti isi modal detail jadi form edit + form toggle**

Ganti isi `<div class="modal-body p-4">` … `</div>` (yang berisi `#isiDetailPelanggan` + dua tombol statis) dengan:
```php
        <div class="modal-body p-4">
          <form method="post" action="aksi-pelanggan.php" class="row g-3">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="plId">
            <div class="col-12">
              <label class="form-label fw-500 small">ID Pelanggan</label>
              <input type="text" class="form-control" id="plIdTampil" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Nama</label>
              <input type="text" name="nama" class="form-control" id="plNama" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Email</label>
              <input type="email" name="email" class="form-control" id="plEmail" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">No. HP</label>
              <input type="text" name="hp" class="form-control" id="plHp" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Alamat</label>
              <input type="text" name="alamat" class="form-control" id="plAlamat">
            </div>
            <div class="col-12 d-grid">
              <button type="submit" class="btn btn-st"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
            </div>
          </form>
          <form method="post" action="aksi-pelanggan.php" class="mt-2">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" value="toggle">
            <input type="hidden" name="id" id="plIdToggle">
            <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-toggle-on me-1"></i>Ubah Status Aktif/Nonaktif</button>
          </form>
        </div>
```

- [ ] **Step 4: Ganti script modal di `admin/pelanggan.php` (isi form dari data-atribut)**

Pada blok `<script>`, ganti listener `modalDetailPelanggan` `show.bs.modal` (yang mengisi `#isiDetailPelanggan`) dengan:
```php
    // Isi form edit dari atribut tombol yang diklik
    document.getElementById('modalDetailPelanggan').addEventListener('show.bs.modal', (e) => {
      const d = e.relatedTarget.dataset;
      document.getElementById('plId').value = d.id;
      document.getElementById('plIdTampil').value = d.id;
      document.getElementById('plNama').value = d.nama;
      document.getElementById('plEmail').value = d.email;
      document.getElementById('plHp').value = d.hp;
      document.getElementById('plAlamat').value = d.alamat || '';
      document.getElementById('plIdToggle').value = d.id;
    });
```
(Biarkan listener pencarian `#cariPelanggan` apa adanya.)

- [ ] **Step 5: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin/aksi-pelanggan.php
/d/WebServer/xampp82/php/php.exe -l admin/pelanggan.php
```
Expected: "No syntax errors detected".

- [ ] **Step 6: Verifikasi (PowerShell) — toggle status Maya lalu kembalikan**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/pelanggan.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-pelanggan.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='toggle';id='STL-2024-008815'} -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "SELECT id,status FROM pelanggan WHERE id='STL-2024-008815';"
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "UPDATE pelanggan SET status='nonaktif' WHERE id='STL-2024-008815';"
```
Expected: status berubah `nonaktif`→`aktif` (lalu dikembalikan ke `nonaktif`).

- [ ] **Step 7: Commit**

```bash
git add admin/aksi-pelanggan.php admin/pelanggan.php
git commit -m "feat(crud): pelanggan edit & toggle status nyata (POST + CSRF + flash)"
```

---

### Task 6: Transaksi (tandai lunas)

**Files:**
- Create: `admin/aksi-transaksi.php`
- Modify: `admin/transaksi.php`

**Interfaces:**
- Consumes: `tokenCsrf()`, `cekCsrf()`, `setFlash()`, `wajibLoginAdmin()`, `db()`; `$daftarTagihan[].idTagihan`.

- [ ] **Step 1: Buat `admin/aksi-transaksi.php`**

```php
<?php
// aksi-transaksi.php — tandai tagihan lunas (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

if (($_POST['aksi'] ?? '') === 'lunas') {
    $stmt = db()->prepare("UPDATE tagihan SET status = 'lunas' WHERE id = ?");
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Tagihan ditandai lunas.');
}
header('Location: transaksi.php');
exit;
```

- [ ] **Step 2: Ganti tombol "Tandai Lunas" di `admin/transaksi.php` jadi form POST**

Ganti blok kolom aksi:
```php
            <td class="text-end kolom-aksi">
              <?php if ($t['status'] === 'menunggu'): ?>
                <button type="button" class="btn btn-sm btn-st btn-tandai-lunas"><i class="bi bi-check2 me-1"></i>Tandai Lunas</button>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
```
menjadi:
```php
            <td class="text-end kolom-aksi">
              <?php if ($t['status'] === 'menunggu'): ?>
                <form method="post" action="aksi-transaksi.php">
                  <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
                  <input type="hidden" name="aksi" value="lunas">
                  <input type="hidden" name="id" value="<?= $t['idTagihan'] ?>">
                  <button type="submit" class="btn btn-sm btn-st"><i class="bi bi-check2 me-1"></i>Tandai Lunas</button>
                </form>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
```

- [ ] **Step 3: Hapus JS mock tandai-lunas di `admin/transaksi.php`**

Pada blok `<script>`, hapus `const badgeLunas = ...;` dan seluruh listener `#tabelTagihan` `click` (tandai lunas). Sisakan hanya logika filter `#filterStatus`. Hapus juga baris PHP `$badgeLunas = badgeStatus('lunas');` di atas (tak lagi dipakai).

- [ ] **Step 4: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin/aksi-transaksi.php
/d/WebServer/xampp82/php/php.exe -l admin/transaksi.php
```
Expected: "No syntax errors detected".

- [ ] **Step 5: Verifikasi (PowerShell) — tandai lunas tagihan menunggu lalu kembalikan**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/transaksi.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-transaksi.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='lunas';id='3'} -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "SELECT id,status FROM tagihan WHERE id=3;"
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "UPDATE tagihan SET status='menunggu' WHERE id=3;"
```
Expected: tagihan id 3 status `lunas` (lalu dikembalikan ke `menunggu`).

- [ ] **Step 6: Commit**

```bash
git add admin/aksi-transaksi.php admin/transaksi.php
git commit -m "feat(crud): transaksi tandai lunas nyata (POST + CSRF + flash)"
```

---

### Task 7: Lead (tandai dihubungi)

**Files:**
- Create: `admin/aksi-lead.php`
- Modify: `admin/lead.php`

**Interfaces:**
- Consumes: `tokenCsrf()`, `cekCsrf()`, `setFlash()`, `wajibLoginAdmin()`, `db()`; `$daftarLead[].id`.

- [ ] **Step 1: Buat `admin/aksi-lead.php`**

```php
<?php
// aksi-lead.php — tandai lead dihubungi (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

if (($_POST['aksi'] ?? '') === 'dihubungi') {
    $stmt = db()->prepare("UPDATE prospek SET status = 'dihubungi' WHERE id = ?");
    $stmt->execute([$_POST['id'] ?? '']);
    setFlash('success', 'Lead ditandai sudah dihubungi.');
}
header('Location: lead.php');
exit;
```

- [ ] **Step 2: Ganti tombol "Tandai Dihubungi" di `admin/lead.php` jadi form POST**

Ganti blok:
```php
              <?php if ($l['status'] === 'baru'): ?>
                <button type="button" class="btn btn-sm btn-st btn-tandai-dihubungi"><i class="bi bi-telephone me-1"></i>Tandai Dihubungi</button>
              <?php endif; ?>
```
menjadi:
```php
              <?php if ($l['status'] === 'baru'): ?>
                <form method="post" action="aksi-lead.php" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
                  <input type="hidden" name="aksi" value="dihubungi">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($l['id']) ?>">
                  <button type="submit" class="btn btn-sm btn-st"><i class="bi bi-telephone me-1"></i>Tandai Dihubungi</button>
                </form>
              <?php endif; ?>
```

- [ ] **Step 3: Hapus JS mock tandai-dihubungi di `admin/lead.php`**

Pada blok `<script>`, hapus `const badgeDihubungi = ...;` dan listener `#tabelLead` `click` (tandai dihubungi). Sisakan logika cari + filter (`saringLead`) dan listener modal detail. Hapus juga baris PHP `$badgeDihubungi = badgeStatus('dihubungi');` di atas.

- [ ] **Step 4: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin/aksi-lead.php
/d/WebServer/xampp82/php/php.exe -l admin/lead.php
```
Expected: "No syntax errors detected".

- [ ] **Step 5: Verifikasi (PowerShell) — tandai LEAD-0451 dihubungi lalu kembalikan**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/lead.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-lead.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='dihubungi';id='LEAD-0451'} -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "SELECT id,status FROM prospek WHERE id='LEAD-0451';"
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "UPDATE prospek SET status='baru' WHERE id='LEAD-0451';"
```
Expected: LEAD-0451 status `dihubungi` (lalu dikembalikan ke `baru`).

- [ ] **Step 6: Commit**

```bash
git add admin/aksi-lead.php admin/lead.php
git commit -m "feat(crud): lead tandai dihubungi nyata (POST + CSRF + flash)"
```

---

### Task 8: Pengaturan (profil / password / info situs)

**Files:**
- Create: `admin/aksi-pengaturan.php`
- Modify: `admin/pengaturan.php`

**Interfaces:**
- Consumes: `tokenCsrf()`, `cekCsrf()`, `setFlash()`, `wajibLoginAdmin()`, `idAdminSaatIni()`, `db()`.

- [ ] **Step 1: Buat `admin/aksi-pengaturan.php`**

```php
<?php
// aksi-pengaturan.php — simpan profil admin / ubah password / info situs (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$pdo  = db();
$aksi = $_POST['aksi'] ?? '';
$idAdmin = idAdminSaatIni();

if ($aksi === 'profil') {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($nama === '' || $email === '') {
        setFlash('danger', 'Nama & email wajib diisi.');
    } else {
        $stmt = $pdo->prepare("UPDATE admin SET nama = ?, email = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $idAdmin]);
        setFlash('success', 'Profil admin berhasil disimpan.');
    }
} elseif ($aksi === 'password') {
    $lama       = $_POST['lama'] ?? '';
    $baru       = $_POST['baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $stmt = $pdo->prepare("SELECT kata_sandi FROM admin WHERE id = ?");
    $stmt->execute([$idAdmin]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($lama, $row['kata_sandi'])) {
        setFlash('danger', 'Password lama salah.');
    } elseif (strlen($baru) < 6 || $baru !== $konfirmasi) {
        setFlash('danger', 'Password baru minimal 6 karakter & harus sama dengan konfirmasi.');
    } else {
        $stmt = $pdo->prepare("UPDATE admin SET kata_sandi = ? WHERE id = ?");
        $stmt->execute([password_hash($baru, PASSWORD_DEFAULT), $idAdmin]);
        setFlash('success', 'Password berhasil diperbarui.');
    }
} elseif ($aksi === 'situs') {
    $namaSitus = trim($_POST['nama_situs'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telepon   = trim($_POST['telepon'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');
    $stmt = $pdo->prepare("UPDATE pengaturan SET nama_situs = ?, email = ?, telepon = ?, alamat = ? WHERE id = 1");
    $stmt->execute([$namaSitus, $email, $telepon, $alamat]);
    setFlash('success', 'Pengaturan situs berhasil disimpan.');
}
header('Location: pengaturan.php');
exit;
```

- [ ] **Step 2: Ubah 3 form di `admin/pengaturan.php` jadi POST nyata**

Form **Profil Admin**: ganti `<form action="pengaturan.php" method="get" class="row g-3">` (yang berisi Nama/Peran/Email) dengan:
```php
        <form action="aksi-pengaturan.php" method="post" class="row g-3">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="profil">
          <div class="col-md-6">
            <label class="form-label fw-500 small">Nama</label>
            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($admin['nama']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Peran</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($admin['peran']) ?>" readonly>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>">
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-st"><i class="bi bi-check2 me-1"></i>Simpan Profil</button>
          </div>
        </form>
```

Form **Ubah Password**: ganti form-nya dengan:
```php
        <form action="aksi-pengaturan.php" method="post" class="row g-3">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="password">
          <div class="col-12">
            <label class="form-label fw-500 small">Password Saat Ini</label>
            <input type="password" name="lama" class="form-control" placeholder="Masukkan password lama" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Password Baru</label>
            <input type="password" name="baru" class="form-control" placeholder="Minimal 6 karakter" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Konfirmasi Password Baru</label>
            <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password baru" required>
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-key me-1"></i>Perbarui Password</button>
          </div>
        </form>
```

Form **Informasi Situs**: ganti form-nya dengan:
```php
        <form action="aksi-pengaturan.php" method="post" class="row g-3">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="situs">
          <div class="col-md-6">
            <label class="form-label fw-500 small">Nama Situs</label>
            <input type="text" name="nama_situs" class="form-control" value="<?= htmlspecialchars($pengaturan['namaSitus']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Email CS</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($pengaturan['email']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Telepon</label>
            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($pengaturan['telepon']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Alamat</label>
            <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($pengaturan['alamat']) ?>">
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-st"><i class="bi bi-check2 me-1"></i>Simpan Pengaturan</button>
          </div>
        </form>
```

- [ ] **Step 3: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin/aksi-pengaturan.php
/d/WebServer/xampp82/php/php.exe -l admin/pengaturan.php
```
Expected: "No syntax errors detected".

- [ ] **Step 4: Verifikasi (PowerShell) — simpan info situs lalu kembalikan**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/pengaturan.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-pengaturan.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='situs';nama_situs='Starlite Uji';email='cs@starlite.id';telepon='0804-1-555-666';alamat='Jl. Fiber Optik No. 1, Jakarta Selatan'} -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "SELECT nama_situs FROM pengaturan WHERE id=1;"
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "UPDATE pengaturan SET nama_situs='Starlite Indonesia' WHERE id=1;"
```
Expected: `nama_situs` jadi `Starlite Uji` (lalu dikembalikan).

- [ ] **Step 5: Commit**

```bash
git add admin/aksi-pengaturan.php admin/pengaturan.php
git commit -m "feat(crud): pengaturan profil/password/situs nyata (POST + CSRF + flash)"
```

---

## Catatan Penutup

Setelah 8 task: semua aksi admin nyata (CRUD) dengan CSRF + flash + PRG. CLAUDE.md & memori perlu diperbarui (aksi admin tak lagi mock). Sub-proyek terakhir Fase 2: **Portal write** (edit profil, pilih/ubah paket).

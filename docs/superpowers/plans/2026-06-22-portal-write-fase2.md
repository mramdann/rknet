# Portal Write Fase 2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menjadikan aksi portal pelanggan nyata: edit info akun, ganti password, dan ubah paket aktif.

**Architecture:** Reuse `aksi.php` (CSRF + flash). Form portal mem-POST ke `portal/aksi-profil.php` / `portal/aksi-paket.php` yang `wajibLoginPelanggan()` + `cekCsrf()`, menulis ke DB untuk pelanggan dari sesi, `setFlash()`, lalu redirect (PRG). Flash tampil di `portal/partials/shell-open.php`.

**Tech Stack:** PHP native (PDO prepared statements, session CSRF, password_hash), MySQL `dbstarlite` @ 3382.

## Global Constraints

- Aksi via POST ke `portal/aksi-<x>.php`, lalu redirect (PRG). Handler: `wajibLoginPelanggan(); cekCsrf();`.
- Operasi memakai id pelanggan dari sesi (`idPelangganSaatIni()`), bukan input klien.
- CSRF tiap form (`tokenCsrf()`); flash via `setFlash`/`tampilFlash` (reuse `aksi.php`).
- Pilih paket = UPDATE `pelanggan.paket_id` (tanpa tagihan).
- Ganti password: `password_verify` lama → `password_hash` baru (min 6, sama konfirmasi).
- Prepared statement; output `htmlspecialchars()`. Kode/komentar Bahasa Indonesia. Lint tiap `.php`.
- Verifikasi: lint + HTTP PowerShell (login pelanggan → POST dgn CSRF → cek DB) + BrowserOS. Commit per task, prefix `feat(portal)`.
- Kredensial uji: `dwi.anjasmoro@gmail.com` / `pelanggan123` (id `STL-2024-008812`, paket_id awal 2).

---

### Task 1: Infrastruktur portal — aksi.php + flash + id paket

**Files:**
- Modify: `portal-config.php` (require aksi.php; `$pelanggan` tambah paket_id; `$paketTersedia` tambah id + `dipilih` by paket_id)
- Modify: `portal/partials/shell-open.php` (panggil `tampilFlash()`)

**Interfaces:**
- Consumes: `tampilFlash()`, `tokenCsrf()` (dari `aksi.php`).
- Produces: `$paketTersedia[].id`; `$pelanggan['paket_id']`; `$paketTersedia[].dipilih` = (id paket == paket_id pelanggan).

- [ ] **Step 1: Wire aksi.php + ubah query di `portal-config.php`**

Tambah require setelah `require_once __DIR__ . '/auth.php';`:
```php
require_once __DIR__ . '/aksi.php';      // CSRF & flash
```

Ubah query `$pelanggan` (tambah `paket_id`):
```php
$stmt = $pdo->prepare("SELECT id, nama, email, hp, alamat, paket_id FROM pelanggan WHERE id = ?");
```

Ganti blok `$paketTersedia` (tambah `id`, `dipilih` by paket_id):
```php
$paketTersedia = [];
foreach ($pdo->query("SELECT id, nama, kecepatan, harga FROM paket ORDER BY id") as $row) {
    $row['fitur']   = $fiturPaket[$row['kecepatan']] ?? [];
    $row['dipilih'] = ((int) $row['id'] === (int) ($pelanggan['paket_id'] ?? 0));
    $paketTersedia[] = $row;
}
```

- [ ] **Step 2: Tampilkan flash di `portal/partials/shell-open.php`**

Ubah:
```php
    <div class="portal-content">
```
menjadi:
```php
    <div class="portal-content">
      <?php tampilFlash(); ?>
```

- [ ] **Step 3: Lint**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l portal-config.php
/d/WebServer/xampp82/php/php.exe -l portal/partials/shell-open.php
```
Expected: "No syntax errors detected".

- [ ] **Step 4: Verifikasi (PowerShell) — portal tetap render**

```powershell
$base="http://localhost:8282/starlite/portal"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='dwi.anjasmoro@gmail.com';kata_sandi='pelanggan123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$p=Invoke-WebRequest "$base/paket.php" -WebSession $s -UseBasicParsing
Write-Output ("PAKET_OK=" + ($p.Content -match 'Pilih Paket Internet'))
```
Expected: `PAKET_OK=True`.

- [ ] **Step 5: Commit**

```bash
git add portal-config.php portal/partials/shell-open.php
git commit -m "feat(portal): wire aksi.php (CSRF/flash) + id paket di portal-config"
```

---

### Task 2: Profil (edit info akun + ganti password)

**Files:**
- Create: `portal/aksi-profil.php`
- Modify: `portal/profil.php`

**Interfaces:**
- Consumes: `wajibLoginPelanggan()`, `idPelangganSaatIni()`, `cekCsrf()`, `setFlash()`, `tokenCsrf()`, `db()`.

- [ ] **Step 1: Buat `portal/aksi-profil.php`**

```php
<?php
// aksi-profil.php — simpan info akun & ganti password pelanggan (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginPelanggan();
cekCsrf();

$pdo  = db();
$id   = idPelangganSaatIni();
$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'info') {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $hp     = trim($_POST['hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    if ($nama === '' || $email === '' || $hp === '') {
        setFlash('danger', 'Nama, email, dan No. HP wajib diisi.');
    } else {
        $stmt = $pdo->prepare("UPDATE pelanggan SET nama = ?, email = ?, hp = ?, alamat = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $hp, $alamat, $id]);
        setFlash('success', 'Informasi akun berhasil disimpan.');
    }
} elseif ($aksi === 'password') {
    $lama       = $_POST['lama'] ?? '';
    $baru       = $_POST['baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $stmt = $pdo->prepare("SELECT kata_sandi FROM pelanggan WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($lama, $row['kata_sandi'])) {
        setFlash('danger', 'Password lama salah.');
    } elseif (strlen($baru) < 6 || $baru !== $konfirmasi) {
        setFlash('danger', 'Password baru minimal 6 karakter & harus sama dengan konfirmasi.');
    } else {
        $stmt = $pdo->prepare("UPDATE pelanggan SET kata_sandi = ? WHERE id = ?");
        $stmt->execute([password_hash($baru, PASSWORD_DEFAULT), $id]);
        setFlash('success', 'Password berhasil diperbarui.');
    }
}
header('Location: profil.php');
exit;
```

- [ ] **Step 2: Ubah form Info Akun di `portal/profil.php` jadi POST**

Ganti `<form action="profil.php" method="get" class="row g-3">` (yang berisi Nama/ID/Email/HP/Alamat) dengan:
```php
        <form action="aksi-profil.php" method="post" class="row g-3">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="info">
          <div class="col-md-6">
            <label class="form-label fw-500 small">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($pelanggan['nama']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">ID Pelanggan</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($pelanggan['id']) ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($pelanggan['email']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">No. Handphone</label>
            <input type="text" name="hp" class="form-control" value="<?= htmlspecialchars($pelanggan['hp']) ?>">
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($pelanggan['alamat']) ?></textarea>
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-st"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
          </div>
        </form>
```

- [ ] **Step 3: Ubah form Ganti Password di `portal/profil.php` jadi POST**

Ganti `<form action="profil.php" method="get" class="row g-3">` (blok Keamanan, 3 input password) dengan:
```php
        <form action="aksi-profil.php" method="post" class="row g-3">
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

- [ ] **Step 4: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l portal/aksi-profil.php
/d/WebServer/xampp82/php/php.exe -l portal/profil.php
```
Expected: "No syntax errors detected".

- [ ] **Step 5: Verifikasi (PowerShell) — ubah hp lalu kembalikan**

```powershell
$base="http://localhost:8282/starlite/portal"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='dwi.anjasmoro@gmail.com';kata_sandi='pelanggan123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/profil.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-profil.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='info';nama='Dwi Anjasmoro';email='dwi.anjasmoro@gmail.com';hp='0811-0000-0000';alamat='Jl. Mawar No.12, Roa Malaka, Tambora, Jakarta Barat'} -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "SELECT hp FROM pelanggan WHERE id='STL-2024-008812';"
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "UPDATE pelanggan SET hp='0811-7891-2233' WHERE id='STL-2024-008812';"
```
Expected: hp `0811-0000-0000` (lalu dikembalikan).

- [ ] **Step 6: Commit**

```bash
git add portal/aksi-profil.php portal/profil.php
git commit -m "feat(portal): edit info akun & ganti password nyata (POST + CSRF + flash)"
```

---

### Task 3: Pilih/ubah paket aktif

**Files:**
- Create: `portal/aksi-paket.php`
- Modify: `portal/paket.php`

**Interfaces:**
- Consumes: `wajibLoginPelanggan()`, `idPelangganSaatIni()`, `cekCsrf()`, `setFlash()`, `tokenCsrf()`, `db()`; `$paketTersedia[].id`, `$paketTersedia[].dipilih`.

- [ ] **Step 1: Buat `portal/aksi-paket.php`**

```php
<?php
// aksi-paket.php — ubah paket aktif pelanggan (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginPelanggan();
cekCsrf();

$pdo = db();
$id  = idPelangganSaatIni();

if (($_POST['aksi'] ?? '') === 'pilih') {
    $paketId = $_POST['paket_id'] ?? '';
    if (!is_numeric($paketId)) {
        setFlash('danger', 'Paket tidak valid.');
        header('Location: paket.php');
        exit;
    }
    // Pastikan paket ada sebelum update
    $cek = $pdo->prepare("SELECT id FROM paket WHERE id = ?");
    $cek->execute([(int) $paketId]);
    if (!$cek->fetch()) {
        setFlash('danger', 'Paket tidak ditemukan.');
        header('Location: paket.php');
        exit;
    }
    $stmt = $pdo->prepare("UPDATE pelanggan SET paket_id = ? WHERE id = ?");
    $stmt->execute([(int) $paketId, $id]);
    setFlash('success', 'Paket aktif berhasil diubah.');
}
header('Location: paket.php');
exit;
```

- [ ] **Step 2: Tambah `data-id` ke kartu paket di `portal/paket.php`**

Pada `<div class="kartu kartu-pad paket-pilih h-100 ..." ...>`, tambahkan atribut `data-id`:
```php
      <div class="kartu kartu-pad paket-pilih h-100 <?= $p['dipilih'] ? 'terpilih' : '' ?>"
           data-id="<?= $p['id'] ?>"
           data-nama="<?= htmlspecialchars($p['nama']) ?>"
           data-harga="<?= formatRupiah($p['harga']) ?>"
           data-kecepatan="<?= htmlspecialchars($p['kecepatan']) ?>"
           tabindex="0">
```

- [ ] **Step 3: Bungkus ringkasan + Konfirmasi jadi form POST di `portal/paket.php`**

Ganti blok ringkasan (`<div class="kartu kartu-pad mt-4"> ... </div>`) dengan form berisi hidden id + tombol submit:
```php
  <!-- Ringkasan & konfirmasi -->
  <form method="post" action="aksi-paket.php" class="kartu kartu-pad mt-4">
    <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
    <input type="hidden" name="aksi" value="pilih">
    <input type="hidden" name="paket_id" id="paketIdPilih" value="">
    <div class="row align-items-center g-3">
      <div class="col-md-8">
        <div class="text-muted text-uppercase fw-600 mb-1" style="font-size:.72rem">Paket Dipilih</div>
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-wifi text-st fs-4"></i>
          <div>
            <div class="fw-700" id="ringkasNama">—</div>
            <div class="text-muted small">Masa aktif s/d <strong>15 Juli 2026</strong> · <span id="ringkasHarga">—</span>/bulan</div>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-md-end">
        <button type="submit" class="btn btn-st btn-lg w-100 w-md-auto px-4">Konfirmasi</button>
      </div>
    </div>
  </form>
```

- [ ] **Step 4: Perluas JS pemilih kartu di `portal/paket.php` (set hidden id)**

Ganti blok `<script> ... </script>` dengan:
```php
  <script>
    // Pilih paket: klik kartu -> tandai terpilih, perbarui ringkasan & id tersembunyi
    (function () {
      const kartuPaket = document.querySelectorAll('.paket-pilih');
      const namaEl  = document.getElementById('ringkasNama');
      const hargaEl = document.getElementById('ringkasHarga');
      const idEl    = document.getElementById('paketIdPilih');
      function perbaruiRingkasan(kartu) {
        namaEl.textContent  = kartu.dataset.nama;
        hargaEl.textContent = kartu.dataset.harga;
        idEl.value          = kartu.dataset.id;
      }
      function pilih(kartu) {
        kartuPaket.forEach(k => k.classList.remove('terpilih'));
        kartu.classList.add('terpilih');
        perbaruiRingkasan(kartu);
      }
      kartuPaket.forEach(kartu => kartu.addEventListener('click', () => pilih(kartu)));
      // Inisialisasi dari kartu yang sudah terpilih
      const awal = document.querySelector('.paket-pilih.terpilih') || kartuPaket[0];
      if (awal) perbaruiRingkasan(awal);
    })();
  </script>
```

- [ ] **Step 5: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l portal/aksi-paket.php
/d/WebServer/xampp82/php/php.exe -l portal/paket.php
```
Expected: "No syntax errors detected".

- [ ] **Step 6: Verifikasi (PowerShell) — ubah paket ke 500 Mbps (id 3) lalu kembalikan ke 2**

```powershell
$base="http://localhost:8282/starlite/portal"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='dwi.anjasmoro@gmail.com';kata_sandi='pelanggan123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$tok=([regex]'name="csrf" value="([^"]+)"').Match((Invoke-WebRequest "$base/paket.php" -WebSession $s -UseBasicParsing).Content).Groups[1].Value
Invoke-WebRequest "$base/aksi-paket.php" -Method POST -WebSession $s -Body @{csrf=$tok;aksi='pilih';paket_id='3'} -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "SELECT paket_id FROM pelanggan WHERE id='STL-2024-008812';"
& "D:\WebServer\xampp82\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3382 -u root -D dbstarlite -e "UPDATE pelanggan SET paket_id=2 WHERE id='STL-2024-008812';"
```
Expected: `paket_id` jadi `3` (lalu dikembalikan ke `2`).

- [ ] **Step 7: Commit**

```bash
git add portal/aksi-paket.php portal/paket.php
git commit -m "feat(portal): ubah paket aktif nyata (POST + CSRF + flash)"
```

---

## Catatan Penutup

Setelah 3 task: aksi portal nyata (edit profil, ganti password, ubah paket) — Fase 2 tuntas. CLAUDE.md & memori perlu diperbarui (semua aksi kini nyata; tidak ada lagi mock kecuali konten landing). Verifikasi akhir BrowserOS: login pelanggan → edit profil → ubah paket → cek dashboard.

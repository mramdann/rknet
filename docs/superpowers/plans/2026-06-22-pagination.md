# Pagination Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Pagination sisi-server (LIMIT/OFFSET, page size 5) + cari/filter via GET untuk 4 tabel admin & riwayat transaksi portal.

**Architecture:** `pagination.php` menyediakan `halamanSaatIni()`, `ambilPaginasi()` (COUNT + LIMIT/OFFSET), `tampilPaginasi()` (nav Bootstrap). Tiap halaman daftar membangun WHERE dari `$_GET`, memanggil `ambilPaginasi($pdo, ...)`, merender baris + nav. Filter sisi-klien lama diganti form/link GET.

**Tech Stack:** PHP native (PDO prepared statements), MySQL `dbstarlite` @ 3382.

## Global Constraints

- Pagination sisi-server: `?hal=N`, page size 5 (`PER_HALAMAN`). `LIMIT/OFFSET` integer hasil cast (aman); filter via bound params.
- Cari/filter via GET; nilai dipertahankan di link nav & form. Output di link/value `htmlspecialchars()`.
- `?hal` di-clamp ke `1..totalHalaman`. Filter kosong = semua.
- `$pdo` berasal dari admin-config/portal-config (sudah `db()`).
- Kode/komentar Bahasa Indonesia. Lint tiap `.php`: `/d/WebServer/xampp82/php/php.exe -l <file>`.
- Verifikasi: lint + HTTP PowerShell (`?hal=2`, `?cari=`, `?status=`) + BrowserOS. Commit per task, prefix `feat(paginasi)`.

---

### Task 1: Helper pagination.php + wire ke config

**Files:**
- Create: `pagination.php`
- Modify: `admin-config.php` (require_once pagination.php)
- Modify: `portal-config.php` (require_once pagination.php)

**Interfaces:**
- Produces: `PER_HALAMAN` (=5), `halamanSaatIni(): int`, `ambilPaginasi(PDO $pdo, string $sqlBase, string $sqlCount, array $params, int $perHalaman = PER_HALAMAN): array` → `['baris','hal','totalHal','total']`, `tampilPaginasi(int $hal, int $totalHal, array $queryTambahan = []): void`.

- [ ] **Step 1: Buat `pagination.php`**

```php
<?php
// pagination.php — helper paginasi sisi-server (LIMIT/OFFSET + nav).
const PER_HALAMAN = 5;

function halamanSaatIni(): int
{
    $hal = (int) ($_GET['hal'] ?? 1);
    return $hal < 1 ? 1 : $hal;
}

function ambilPaginasi(PDO $pdo, string $sqlBase, string $sqlCount, array $params, int $perHalaman = PER_HALAMAN): array
{
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetchColumn();

    $totalHal = max(1, (int) ceil($total / $perHalaman));
    $hal = min(halamanSaatIni(), $totalHal);
    $offset = ($hal - 1) * $perHalaman;

    $stmt = $pdo->prepare($sqlBase . " LIMIT $perHalaman OFFSET $offset");
    $stmt->execute($params);

    return ['baris' => $stmt->fetchAll(), 'hal' => $hal, 'totalHal' => $totalHal, 'total' => $total];
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

- [ ] **Step 2: require_once pagination.php di `admin-config.php`**

Setelah baris `require_once __DIR__ . '/aksi.php';` tambahkan:
```php
require_once __DIR__ . '/pagination.php';  // paginasi
```

- [ ] **Step 3: require_once pagination.php di `portal-config.php`**

Setelah baris `require_once __DIR__ . '/aksi.php';` tambahkan:
```php
require_once __DIR__ . '/pagination.php';  // paginasi
```

- [ ] **Step 4: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l pagination.php
/d/WebServer/xampp82/php/php.exe -l admin-config.php
/d/WebServer/xampp82/php/php.exe -l portal-config.php
```
Expected: "No syntax errors detected".

- [ ] **Step 5: Commit**

```bash
git add pagination.php admin-config.php portal-config.php
git commit -m "feat(paginasi): helper pagination.php + wire ke config"
```

---

### Task 2: Pelanggan — paginasi + cari (GET)

**Files:**
- Modify: `admin/pelanggan.php` (ganti seluruh isi)

**Interfaces:**
- Consumes: `ambilPaginasi`, `tampilPaginasi`, `$pdo` (admin-config), `badgeStatus`, `tokenCsrf`.

- [ ] **Step 1: Ganti seluruh isi `admin/pelanggan.php`**

```php
<?php
// pelanggan.php (admin) — daftar (paginasi + cari sisi-server), edit & toggle status.
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Pelanggan';
$menuAktif = 'pelanggan';

// Filter cari (server-side)
$cari = trim($_GET['cari'] ?? '');
$where = '';
$params = [];
if ($cari !== '') {
    $where = "WHERE LOWER(pl.nama) LIKE ? OR LOWER(pl.id) LIKE ?";
    $kunci = '%' . mb_strtolower($cari) . '%';
    $params = [$kunci, $kunci];
}
$sqlBase = "SELECT pl.id, pl.nama, pl.email, pl.hp, pl.alamat, pk.kecepatan AS paket, pl.status, pl.tgl_bergabung AS bergabung
            FROM pelanggan pl LEFT JOIN paket pk ON pk.id = pl.paket_id $where ORDER BY pl.id";
$sqlCount = "SELECT COUNT(*) FROM pelanggan pl $where";
$hasil = ambilPaginasi($pdo, $sqlBase, $sqlCount, $params);
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Manajemen Pelanggan</h5>
      <p class="text-muted small mb-0">Total <?= $hasil['total'] ?> pelanggan<?= $cari !== '' ? ' cocok' : ' terdaftar' ?>.</p>
    </div>
    <form method="get" class="input-group cari-pelanggan">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" name="cari" class="form-control" placeholder="Cari nama / ID pelanggan..." value="<?= htmlspecialchars($cari) ?>">
      <button class="btn btn-st" type="submit">Cari</button>
    </form>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>Pelanggan</th><th>Kontak</th><th>Paket</th><th>Bergabung</th><th>Status</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
          <?php foreach ($hasil['baris'] as $p): $b = badgeStatus($p['status']); ?>
          <tr>
            <td>
              <div class="fw-600"><?= htmlspecialchars($p['nama']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($p['id']) ?></div>
            </td>
            <td>
              <div class="small"><?= htmlspecialchars($p['email']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($p['hp']) ?></div>
            </td>
            <td><?= htmlspecialchars($p['paket']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($p['bergabung']) ?></td>
            <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-light btn-detail"
                data-nama="<?= htmlspecialchars($p['nama']) ?>"
                data-id="<?= htmlspecialchars($p['id']) ?>"
                data-email="<?= htmlspecialchars($p['email']) ?>"
                data-hp="<?= htmlspecialchars($p['hp']) ?>"
                data-alamat="<?= htmlspecialchars($p['alamat'] ?? '') ?>"
                data-bs-toggle="modal" data-bs-target="#modalDetailPelanggan">
                <i class="bi bi-eye"></i> Detail
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if ($hasil['total'] === 0): ?>
          <tr><td colspan="6" class="text-muted small text-center py-3">Tidak ada pelanggan yang cocok.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <?php tampilPaginasi($hasil['hal'], $hasil['totalHal'], $cari !== '' ? ['cari' => $cari] : []); ?>
    </div>
  </div>

  <!-- Modal detail/edit pelanggan -->
  <div class="modal fade" id="modalDetailPelanggan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header st-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0">Detail Pelanggan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
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
      </div>
    </div>
  </div>

  <script>
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
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>
```

- [ ] **Step 2: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin/pelanggan.php
```
Expected: "No syntax errors detected".

- [ ] **Step 3: Verifikasi (PowerShell) — total > 5 → ada 2 halaman; cari memfilter**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$h1=Invoke-WebRequest "$base/pelanggan.php" -WebSession $s -UseBasicParsing
Write-Output ("ADA_NAV=" + ($h1.Content -match 'pagination'))
$h2=Invoke-WebRequest "$base/pelanggan.php?hal=2" -WebSession $s -UseBasicParsing
Write-Output ("HAL2_ADA_INDAH=" + ($h2.Content -match 'Indah Permata'))
$hc=Invoke-WebRequest "$base/pelanggan.php?cari=budi" -WebSession $s -UseBasicParsing
Write-Output ("CARI_BUDI=" + (($hc.Content -match 'Budi Hartono') -and -not ($hc.Content -match 'Siti Rahmawati')))
```
Expected: `ADA_NAV=True`, `HAL2_ADA_INDAH=True` (6 pelanggan → hal 2 berisi baris ke-6 Indah), `CARI_BUDI=True`.

- [ ] **Step 4: Commit**

```bash
git add admin/pelanggan.php
git commit -m "feat(paginasi): pelanggan paginasi + cari sisi-server"
```

---

### Task 3: Transaksi — paginasi + filter status (GET)

**Files:**
- Modify: `admin/transaksi.php` (ganti seluruh isi)

**Interfaces:**
- Consumes: `ambilPaginasi`, `tampilPaginasi`, `$pdo`, `badgeStatus`, `formatRupiah`, `tokenCsrf`.

- [ ] **Step 1: Ganti seluruh isi `admin/transaksi.php`**

```php
<?php
// transaksi.php (admin) — daftar tagihan (paginasi + filter status sisi-server) + tandai lunas.
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Transaksi';
$menuAktif = 'transaksi';

// Filter status (server-side)
$status = $_GET['status'] ?? '';
$where = '';
$params = [];
if ($status === 'lunas' || $status === 'menunggu') {
    $where = "WHERE t.status = ?";
    $params = [$status];
}
$sqlBase = "SELECT t.id AS idTagihan, t.no_invoice AS noInvoice, pl.nama AS pelanggan, pk.kecepatan AS paket,
                   t.harga, t.tanggal, t.status
            FROM tagihan t
            JOIN pelanggan pl ON pl.id = t.pelanggan_id
            LEFT JOIN paket pk ON pk.id = t.paket_id
            $where ORDER BY t.id";
$sqlCount = "SELECT COUNT(*) FROM tagihan t $where";
$hasil = ambilPaginasi($pdo, $sqlBase, $sqlCount, $params);
$paramFilter = $status !== '' ? ['status' => $status] : [];
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Transaksi & Tagihan</h5>
      <p class="text-muted small mb-0">Kelola status pembayaran pelanggan.</p>
    </div>
    <div class="btn-group" role="group">
      <a href="?status=" class="btn btn-sm <?= $status === '' ? 'btn-st' : 'btn-outline-primary' ?>">Semua</a>
      <a href="?status=lunas" class="btn btn-sm <?= $status === 'lunas' ? 'btn-st' : 'btn-outline-primary' ?>">Lunas</a>
      <a href="?status=menunggu" class="btn btn-sm <?= $status === 'menunggu' ? 'btn-st' : 'btn-outline-primary' ?>">Menunggu</a>
    </div>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>No. Invoice</th><th>Pelanggan</th><th>Paket</th><th>Tanggal</th><th>Nominal</th><th>Status</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
          <?php foreach ($hasil['baris'] as $t): $b = badgeStatus($t['status']); ?>
          <tr>
            <td class="fw-500"><?= htmlspecialchars($t['noInvoice']) ?></td>
            <td><?= htmlspecialchars($t['pelanggan']) ?></td>
            <td><?= htmlspecialchars($t['paket']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($t['tanggal']) ?></td>
            <td class="fw-600"><?= formatRupiah($t['harga']) ?></td>
            <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
            <td class="text-end">
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
          </tr>
          <?php endforeach; ?>
          <?php if ($hasil['total'] === 0): ?>
          <tr><td colspan="7" class="text-muted small text-center py-3">Tidak ada transaksi pada filter ini.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <?php tampilPaginasi($hasil['hal'], $hasil['totalHal'], $paramFilter); ?>
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>
```

- [ ] **Step 2: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin/transaksi.php
```
Expected: "No syntax errors detected".

- [ ] **Step 3: Verifikasi (PowerShell) — 8 tagihan → 2 halaman; filter menunggu**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$h1=Invoke-WebRequest "$base/transaksi.php" -WebSession $s -UseBasicParsing
Write-Output ("ADA_NAV=" + ($h1.Content -match 'pagination'))
$hm=Invoke-WebRequest "$base/transaksi.php?status=menunggu" -WebSession $s -UseBasicParsing
Write-Output ("FILTER_MENUNGGU=" + (-not ($hm.Content -match 'Telah Dibayar')))
```
Expected: `ADA_NAV=True` (8 baris > 5), `FILTER_MENUNGGU=True` (tak ada baris lunas).

- [ ] **Step 4: Commit**

```bash
git add admin/transaksi.php
git commit -m "feat(paginasi): transaksi paginasi + filter status sisi-server"
```

---

### Task 4: Lead — paginasi + cari + filter (GET), pindah query dari config

**Files:**
- Modify: `admin/lead.php` (ganti seluruh isi)
- Modify: `admin-config.php` (hapus blok `$daftarLead`)

**Interfaces:**
- Consumes: `ambilPaginasi`, `tampilPaginasi`, `$pdo`, `badgeStatus`, `tokenCsrf`.

- [ ] **Step 1: Hapus blok `$daftarLead` di `admin-config.php`**

Hapus seluruh blok (komentar + query):
```php
// Daftar lead / prospek cek jangkauan
$daftarLead = $pdo->query(
    "SELECT id, nama, hp, area, tanggal, status FROM prospek ORDER BY id"
)->fetchAll();
```

- [ ] **Step 2: Ganti seluruh isi `admin/lead.php`**

```php
<?php
// lead.php (admin) — daftar prospek (paginasi + cari + filter status sisi-server) + tandai dihubungi.
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Lead';
$menuAktif = 'lead';

// Filter cari + status (server-side)
$cari   = trim($_GET['cari'] ?? '');
$status = $_GET['status'] ?? '';
$statusValid = ['baru', 'dihubungi', 'terjadwal', 'selesai', 'batal'];
$klausa = [];
$params = [];
if ($cari !== '') {
    $klausa[] = "(LOWER(nama) LIKE ? OR LOWER(area) LIKE ?)";
    $kunci = '%' . mb_strtolower($cari) . '%';
    $params[] = $kunci;
    $params[] = $kunci;
}
if (in_array($status, $statusValid, true)) {
    $klausa[] = "status = ?";
    $params[] = $status;
}
$where = $klausa ? ('WHERE ' . implode(' AND ', $klausa)) : '';
$sqlBase  = "SELECT id, nama, hp, area, tanggal, status FROM prospek $where ORDER BY id";
$sqlCount = "SELECT COUNT(*) FROM prospek $where";
$hasil = ambilPaginasi($pdo, $sqlBase, $sqlCount, $params);

// Param difilter untuk dipertahankan di link halaman
$paramFilter = [];
if ($cari !== '') $paramFilter['cari'] = $cari;
if (in_array($status, $statusValid, true)) $paramFilter['status'] = $status;
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Lead Cek Jangkauan</h5>
      <p class="text-muted small mb-0"><?= $hasil['total'] ?> calon pelanggan dari form cek jangkauan.</p>
    </div>
    <form method="get" class="d-flex flex-wrap gap-2">
      <div class="input-group cari-pelanggan">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="cari" class="form-control" placeholder="Cari nama / area..." value="<?= htmlspecialchars($cari) ?>">
      </div>
      <select name="status" class="form-select" style="max-width:180px" onchange="this.form.submit()">
        <option value="">Semua status</option>
        <?php foreach ($statusValid as $sv): ?>
        <option value="<?= $sv ?>" <?= $status === $sv ? 'selected' : '' ?>><?= ucfirst($sv) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-st">Cari</button>
    </form>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>Lead</th><th>No. HP</th><th>Area</th><th>Tanggal</th><th>Status</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
          <?php foreach ($hasil['baris'] as $l): $b = badgeStatus($l['status']); ?>
          <tr>
            <td>
              <div class="fw-600"><?= htmlspecialchars($l['nama']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($l['id']) ?></div>
            </td>
            <td><?= htmlspecialchars($l['hp']) ?></td>
            <td><?= htmlspecialchars($l['area']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($l['tanggal']) ?></td>
            <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-light btn-detail-lead"
                data-id="<?= htmlspecialchars($l['id']) ?>"
                data-nama="<?= htmlspecialchars($l['nama']) ?>"
                data-hp="<?= htmlspecialchars($l['hp']) ?>"
                data-area="<?= htmlspecialchars($l['area']) ?>"
                data-tanggal="<?= htmlspecialchars($l['tanggal']) ?>"
                data-status="<?= htmlspecialchars($b['label']) ?>"
                data-bs-toggle="modal" data-bs-target="#modalDetailLead">
                <i class="bi bi-eye"></i> Detail
              </button>
              <?php if ($l['status'] === 'baru'): ?>
                <form method="post" action="aksi-lead.php" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
                  <input type="hidden" name="aksi" value="dihubungi">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($l['id']) ?>">
                  <button type="submit" class="btn btn-sm btn-st"><i class="bi bi-telephone me-1"></i>Tandai Dihubungi</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if ($hasil['total'] === 0): ?>
          <tr><td colspan="6" class="text-muted small text-center py-3">Tidak ada lead yang cocok.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <?php tampilPaginasi($hasil['hal'], $hasil['totalHal'], $paramFilter); ?>
    </div>
  </div>

  <!-- Modal detail lead (diisi oleh JS) -->
  <div class="modal fade" id="modalDetailLead" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header st-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0">Detail Lead</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <ul class="list-unstyled mb-4 info-akun" id="isiDetailLead"></ul>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary flex-fill"><i class="bi bi-telephone me-1"></i>Hubungi</button>
            <button type="button" class="btn btn-outline-secondary flex-fill"><i class="bi bi-calendar-check me-1"></i>Jadwalkan</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Isi modal detail dari atribut tombol
    document.getElementById('modalDetailLead').addEventListener('show.bs.modal', (e) => {
      const d = e.relatedTarget.dataset;
      const baris = { 'ID Lead': d.id, 'Nama': d.nama, 'No. HP': d.hp, 'Area': d.area, 'Tanggal': d.tanggal, 'Status': d.status };
      document.getElementById('isiDetailLead').innerHTML =
        Object.entries(baris).map(([k, v]) => `<li><span>${k}</span><strong>${v}</strong></li>`).join('');
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>
```

- [ ] **Step 3: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin-config.php
/d/WebServer/xampp82/php/php.exe -l admin/lead.php
```
Expected: "No syntax errors detected".

- [ ] **Step 4: Verifikasi (PowerShell) — 6 lead → 2 halaman; filter status=baru**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$h1=Invoke-WebRequest "$base/lead.php" -WebSession $s -UseBasicParsing
Write-Output ("ADA_NAV=" + ($h1.Content -match 'pagination'))
$hb=Invoke-WebRequest "$base/lead.php?status=baru" -WebSession $s -UseBasicParsing
Write-Output ("FILTER_BARU=" + (($hb.Content -match 'Rizki Maulana') -and -not ($hb.Content -match 'Putri Lestari')))
```
Expected: `ADA_NAV=True` (6 > 5), `FILTER_BARU=True` (hanya status baru: Rizki & Citra, bukan Putri yang 'dihubungi').

- [ ] **Step 5: Commit**

```bash
git add admin-config.php admin/lead.php
git commit -m "feat(paginasi): lead paginasi + cari + filter status sisi-server"
```

---

### Task 5: Notifikasi — paginasi + filter status (GET), pindah query dari config

**Files:**
- Modify: `admin/notifikasi.php` (ganti seluruh isi)
- Modify: `admin-config.php` (hapus blok `$daftarNotifikasi`)

**Interfaces:**
- Consumes: `ambilPaginasi`, `tampilPaginasi`, `$pdo`, `badgeStatus`, `tokenCsrf`.

- [ ] **Step 1: Hapus blok `$daftarNotifikasi` di `admin-config.php`**

Hapus seluruh blok (komentar + query):
```php
// Daftar notifikasi broadcast (urut sesuai seed)
$daftarNotifikasi = $pdo->query(
    "SELECT id, judul, isi, target, tanggal, status FROM notifikasi ORDER BY id"
)->fetchAll();
```

- [ ] **Step 2: Ganti seluruh isi `admin/notifikasi.php`**

```php
<?php
// notifikasi.php (admin) — daftar (paginasi + filter status sisi-server) + tulis/hapus.
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Notifikasi';
$menuAktif = 'notifikasi';

// Filter status (server-side)
$status = $_GET['status'] ?? '';
$where = '';
$params = [];
if ($status === 'terkirim' || $status === 'draft') {
    $where = "WHERE status = ?";
    $params = [$status];
}
$sqlBase  = "SELECT id, judul, isi, target, tanggal, status FROM notifikasi $where ORDER BY id";
$sqlCount = "SELECT COUNT(*) FROM notifikasi $where";
$hasil = ambilPaginasi($pdo, $sqlBase, $sqlCount, $params);
$paramFilter = $status !== '' ? ['status' => $status] : [];
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Notifikasi</h5>
      <p class="text-muted small mb-0">Kelola pengumuman & broadcast ke pelanggan.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <div class="btn-group" role="group">
        <a href="?status=" class="btn btn-sm <?= $status === '' ? 'btn-st' : 'btn-outline-primary' ?>">Semua</a>
        <a href="?status=terkirim" class="btn btn-sm <?= $status === 'terkirim' ? 'btn-st' : 'btn-outline-primary' ?>">Terkirim</a>
        <a href="?status=draft" class="btn btn-sm <?= $status === 'draft' ? 'btn-st' : 'btn-outline-primary' ?>">Draft</a>
      </div>
      <button class="btn btn-st" type="button" data-bs-toggle="modal" data-bs-target="#modalTulisNotif">
        <i class="bi bi-plus-lg me-1"></i>Tulis Notifikasi</button>
    </div>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>Judul</th><th>Target</th><th>Tanggal</th><th>Status</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
          <?php foreach ($hasil['baris'] as $n): $b = badgeStatus($n['status']); ?>
          <tr>
            <td>
              <div class="fw-600"><?= htmlspecialchars($n['judul']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($n['isi']) ?></div>
            </td>
            <td><?= htmlspecialchars($n['target']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($n['tanggal']) ?></td>
            <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
            <td class="text-end">
              <form method="post" action="aksi-notifikasi.php" onsubmit="return confirm('Hapus notifikasi ini?')">
                <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="id" value="<?= $n['id'] ?>">
                <button type="submit" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if ($hasil['total'] === 0): ?>
          <tr><td colspan="5" class="text-muted small text-center py-3">Tidak ada notifikasi pada filter ini.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <?php tampilPaginasi($hasil['hal'], $hasil['totalHal'], $paramFilter); ?>
    </div>
  </div>

  <!-- Modal tulis notifikasi -->
  <div class="modal fade" id="modalTulisNotif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header st-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0">Tulis Notifikasi</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
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
        </div>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>
```

- [ ] **Step 3: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l admin-config.php
/d/WebServer/xampp82/php/php.exe -l admin/notifikasi.php
```
Expected: "No syntax errors detected".

- [ ] **Step 4: Verifikasi (PowerShell) — filter draft**

```powershell
$base="http://localhost:8282/starlite/admin"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='admin@starlite.id';kata_sandi='admin123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$hd=Invoke-WebRequest "$base/notifikasi.php?status=draft" -WebSession $s -UseBasicParsing
Write-Output ("FILTER_DRAFT=" + (($hd.Content -match 'Selamat datang pelanggan baru') -and -not ($hd.Content -match 'Promo upgrade 500')))
```
Expected: `FILTER_DRAFT=True` (hanya draft; "Promo upgrade" terkirim tak muncul).

- [ ] **Step 5: Commit**

```bash
git add admin-config.php admin/notifikasi.php
git commit -m "feat(paginasi): notifikasi paginasi + filter status sisi-server"
```

---

### Task 6: Portal riwayat transaksi — paginasi

**Files:**
- Modify: `portal/transaksi.php` (ganti seluruh isi)

**Interfaces:**
- Consumes: `ambilPaginasi`, `tampilPaginasi`, `$pdo`, `$idPelanggan` (portal-config), `badgeStatus`, `formatRupiah`.

- [ ] **Step 1: Ganti seluruh isi `portal/transaksi.php`**

```php
<?php
// transaksi.php — riwayat transaksi pelanggan (kartu, paginasi sisi-server).
require __DIR__ . '/../config.php';
require __DIR__ . '/../portal-config.php';
$judulHalaman = 'Riwayat Transaksi';
$menuAktif = 'transaksi';

$sqlBase = "SELECT t.no_invoice AS noInvoice, pk.nama AS paket, t.harga, t.tanggal, t.status
            FROM tagihan t JOIN paket pk ON pk.id = t.paket_id
            WHERE t.pelanggan_id = ? ORDER BY t.id";
$sqlCount = "SELECT COUNT(*) FROM tagihan t WHERE t.pelanggan_id = ?";
$hasil = ambilPaginasi($pdo, $sqlBase, $sqlCount, [$idPelanggan]);
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Riwayat Transaksi</h5>
      <p class="text-muted small mb-0">Semua tagihan & pembayaran paket internet Anda.</p>
    </div>
    <span class="badge bg-primary-subtle text-primary fs-6"><?= $hasil['total'] ?> Transaksi</span>
  </div>

  <div class="row g-3">
    <?php foreach ($hasil['baris'] as $t):
      $b = badgeStatus($t['status']);
      $lunas = $t['status'] === 'lunas'; ?>
    <div class="col-12">
      <div class="kartu kartu-pad transaksi-kartu">
        <div class="row align-items-center g-3">
          <div class="col-md-1 col-2">
            <div class="trx-ico <?= $lunas ? 'text-success bg-success-subtle' : 'text-warning bg-warning-subtle' ?>">
              <i class="bi <?= $lunas ? 'bi-check-circle-fill' : 'bi-hourglass-split' ?>"></i>
            </div>
          </div>
          <div class="col-md-4 col-10">
            <div class="fw-700"><?= htmlspecialchars($t['paket']) ?></div>
            <div class="text-muted small"><i class="bi bi-receipt me-1"></i><?= htmlspecialchars($t['noInvoice']) ?></div>
          </div>
          <div class="col-md-2 col-6">
            <div class="text-muted" style="font-size:.75rem">Tanggal</div>
            <div class="fw-500 small"><?= htmlspecialchars($t['tanggal']) ?></div>
          </div>
          <div class="col-md-2 col-6">
            <div class="text-muted" style="font-size:.75rem">Nominal</div>
            <div class="fw-700 text-st"><?= formatRupiah($t['harga']) ?></div>
          </div>
          <div class="col-md-3 col-12 text-md-end">
            <span class="badge <?= $b['kelas'] ?> mb-2 d-inline-block"><?= $b['label'] ?></span>
          </div>
        </div>
        <hr class="my-3">
        <div class="d-flex justify-content-end gap-2">
          <?php if (!$lunas): ?>
            <a href="invoice.php" class="btn btn-st btn-sm">Bayar Sekarang</a>
          <?php endif; ?>
          <a href="invoice.php" class="btn btn-outline-primary btn-sm">Lihat Tagihan</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php tampilPaginasi($hasil['hal'], $hasil['totalHal']); ?>

<?php include __DIR__ . '/partials/shell-close.php'; ?>
```

- [ ] **Step 2: Lint**

```bash
/d/WebServer/xampp82/php/php.exe -l portal/transaksi.php
```
Expected: "No syntax errors detected".

- [ ] **Step 3: Verifikasi (PowerShell) — Dwi punya 4 transaksi (≤5, jadi 1 halaman, nav tak muncul; tetap render)**

```powershell
$base="http://localhost:8282/starlite/portal"
Invoke-WebRequest "$base/login.php" -Method POST -Body @{email='dwi.anjasmoro@gmail.com';kata_sandi='pelanggan123'} -SessionVariable s -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
$h=Invoke-WebRequest "$base/transaksi.php" -WebSession $s -UseBasicParsing
Write-Output ("ADA_INVOICE=" + ($h.Content -match 'INV/2026/06/008812'))
Write-Output ("JUMLAH_TRX=" + ($h.Content -match '4 Transaksi'))
```
Expected: `ADA_INVOICE=True`, `JUMLAH_TRX=True` (Dwi 4 transaksi; nav tak muncul karena 1 halaman — sesuai `tampilPaginasi` yang skip bila totalHal=1).

- [ ] **Step 4: Commit**

```bash
git add portal/transaksi.php
git commit -m "feat(paginasi): portal riwayat transaksi paginasi sisi-server"
```

---

## Catatan Penutup

Setelah 6 task: 4 tabel admin + riwayat transaksi portal dipaginasi sisi-server dengan cari/filter via GET. Dashboard & kartu (paket/area) tak berubah. Lanjutan: **end-to-end test menyeluruh** seluruh aplikasi.

# Portal Admin Tahap Berikutnya Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Melengkapi portal admin Starlite dengan empat modul UI-only: Lead Cek Jangkauan, Area, Notifikasi, Pengaturan, plus sidebar bergrup.

**Architecture:** Setiap halaman mengikuti pola shell admin yang sudah ada (`require admin-config.php` → set `$judulHalaman`/`$menuAktif` → include `shell-head`/`shell-open`/konten/`shell-close`). Data dummy ditambahkan ke `admin-config.php`; badge status baru ke `badgeStatus()` di `helpers.php`. Tidak ada backend — semua aksi (Tandai Dihubungi, Tambah/Edit Area, Tulis Notifikasi, Simpan) adalah mock JS/form.

**Tech Stack:** PHP native (XAMPP, port 8282), Bootstrap 5.3 + Bootstrap Icons (CDN), `assets/css/portal.css`. Tanpa framework/Composer/database.

## Global Constraints

- UI only: tidak ada auth nyata, database, atau pemrosesan form/aksi. Semua tombol = mock.
- Bahasa Indonesia untuk nama variabel/fungsi domain & komentar. Keyword framework tetap Inggris.
- Escape semua output dinamis dengan `htmlspecialchars()`.
- Lint tiap `.php` yang disentuh: `/d/WebServer/xampp82/php/php.exe -l <file>` (harus "No syntax errors detected").
- Verifikasi = buka halaman di browser `http://localhost:8282/starlite/admin/<file>.php`. Bash tool tidak bisa akses localhost.
- Cache-busting: setelah edit `assets/css/portal.css`, bump `?v=N` di setiap head yang merujuknya (`admin/partials/shell-head.php`, `portal/partials/shell-head.php`).
- Setiap task diakhiri commit. Pesan commit Bahasa Indonesia, prefix `feat(admin):`.

---

### Task 1: Sidebar bergrup + CSS label

Ubah sidebar admin jadi dua grup berlabel (UTAMA / LAINNYA) dengan 8 menu, tambah style label, dan bump cache-bust. Halaman tujuan (`lead.php` dll.) belum ada — link-nya akan 404 sampai task berikutnya; itu wajar. Verifikasi task ini: sidebar render dengan dua label grup dan 8 item, menu lama tetap berfungsi.

**Files:**
- Modify: `admin/partials/sidebar.php`
- Modify: `assets/css/portal.css` (tambah `.portal-nav-label`)
- Modify: `admin/partials/shell-head.php` (bump `?v=4` → `?v=5`)
- Modify: `portal/partials/shell-head.php` (tambah `?v=5` pada `portal.css`)

**Interfaces:**
- Produces: kunci `$menuAktif` yang dipakai halaman baru → `'lead'`, `'area'`, `'notifikasi'`, `'pengaturan'` (selain `'dashboard'`/`'pelanggan'`/`'paket'`/`'transaksi'` yang sudah ada). Halaman baru menyetel salah satu kunci ini.

- [ ] **Step 1: Ganti `$menuAdmin` jadi struktur bergrup & render loop di `admin/partials/sidebar.php`**

Ganti seluruh isi file dengan:

```php
<?php
// sidebar.php (admin) — navigasi samping bergrup. $menuAktif menentukan menu yang disorot.
$menuAktif = $menuAktif ?? '';
// Menu dikelompokkan: label grup => [kunci => [judul, ikon, file]]
$menuAdmin = [
    'UTAMA' => [
        'dashboard' => ['Dashboard', 'bi-grid-1x2',      'dashboard.php'],
        'pelanggan' => ['Pelanggan', 'bi-people',        'pelanggan.php'],
        'paket'     => ['Paket',     'bi-box-seam',       'paket.php'],
        'transaksi' => ['Transaksi', 'bi-receipt-cutoff', 'transaksi.php'],
    ],
    'LAINNYA' => [
        'lead'       => ['Lead',       'bi-person-lines-fill', 'lead.php'],
        'area'       => ['Area',       'bi-geo-alt',           'area.php'],
        'notifikasi' => ['Notifikasi', 'bi-bell',              'notifikasi.php'],
        'pengaturan' => ['Pengaturan', 'bi-gear',              'pengaturan.php'],
    ],
];
?>
<aside class="portal-sidebar" id="portalSidebar">
  <div class="portal-brand">
    <img src="../assets/img/logo-starlite.webp" alt="Starlite">
    <span class="brand-divider"></span>
    <span class="badge bg-primary-subtle text-primary fw-600">ADMIN</span>
  </div>
  <ul class="portal-nav">
    <?php foreach ($menuAdmin as $grup => $items): ?>
      <li class="portal-nav-label"><?= htmlspecialchars($grup) ?></li>
      <?php foreach ($items as $kunci => $menu): ?>
        <li>
          <a href="<?= $menu[2] ?>" class="<?= $menuAktif === $kunci ? 'aktif' : '' ?>">
            <i class="bi <?= $menu[1] ?>"></i> <span><?= htmlspecialchars($menu[0]) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </ul>
  <div class="portal-sidebar-foot">
    <a href="login.php" class="d-flex align-items-center gap-2 text-decoration-none text-danger fw-500">
      <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
  </div>
</aside>
```

- [ ] **Step 2: Tambah style `.portal-nav-label` di `assets/css/portal.css`**

Setelah baris `.portal-nav a.aktif{...}` (baris 32), tambahkan:

```css
.portal-nav-label{
  list-style:none;padding:.5rem 1rem .25rem;margin-top:.5rem;
  font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#aab1c2
}
.portal-nav-label:first-child{margin-top:0}
```

- [ ] **Step 3: Bump cache-bust di kedua head**

Di `admin/partials/shell-head.php` baris 12, ubah `portal.css?v=4` → `portal.css?v=5`. (Biarkan `style.css?v=4`.)

Di `portal/partials/shell-head.php` baris 15, ubah `../assets/css/portal.css` → `../assets/css/portal.css?v=5`.

- [ ] **Step 4: Lint file PHP yang disentuh**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l admin/partials/sidebar.php
/d/WebServer/xampp82/php/php.exe -l admin/partials/shell-head.php
/d/WebServer/xampp82/php/php.exe -l portal/partials/shell-head.php
```
Expected: tiap file "No syntax errors detected".

- [ ] **Step 5: Verifikasi di browser**

Buka `http://localhost:8282/starlite/admin/dashboard.php`. Konfirmasi sidebar menampilkan label "UTAMA" di atas 4 menu lama dan "LAINNYA" di atas 4 menu baru; menu aktif tersorot benar. (Link LAINNYA akan 404 sampai task berikutnya — abaikan.)

- [ ] **Step 6: Commit**

```bash
git add admin/partials/sidebar.php assets/css/portal.css admin/partials/shell-head.php portal/partials/shell-head.php
git commit -m "feat(admin): sidebar bergrup (UTAMA/LAINNYA) + style label, cache-bust v5"
```

---

### Task 2: Modul Lead + perluasan badgeStatus

Tambah data lead, perluas `badgeStatus()` untuk SEMUA status baru (lead, area, notifikasi — sekali saja agar DRY), dan buat halaman Lead (tabel + cari + filter status + modal detail + tombol "Tandai Dihubungi" mock).

**Files:**
- Modify: `helpers.php` (perluas `badgeStatus()`)
- Modify: `admin-config.php` (tambah `$daftarLead`)
- Create: `admin/lead.php`

**Interfaces:**
- Consumes: `badgeStatus(string $status): array` mengembalikan `['kelas' => ..., 'label' => ...]`; pola shell admin dari `shell-head`/`shell-open`/`shell-close`.
- Produces: `$daftarLead` = array of `['id','nama','hp','area','tanggal','status']`, status ∈ `baru|dihubungi|terjadwal|selesai|batal`. `badgeStatus()` kini juga menangani status area (`tercakup|segera`) & notifikasi (`terkirim|draft`) — dipakai Task 3 & 4.

- [ ] **Step 1: Perluas `badgeStatus()` di `helpers.php`**

Ganti isi `match` (baris 20-26) sehingga mencakup status baru, tanpa menghapus yang lama:

```php
        return match ($status) {
            'lunas'      => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Telah Dibayar'],
            'menunggu'   => ['kelas' => 'bg-warning-subtle text-warning',     'label' => 'Menunggu Pembayaran'],
            'aktif'      => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Aktif'],
            'nonaktif'   => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => 'Nonaktif'],
            // Status lead
            'baru'       => ['kelas' => 'bg-info-subtle text-info',           'label' => 'Baru'],
            'dihubungi'  => ['kelas' => 'bg-primary-subtle text-primary',     'label' => 'Dihubungi'],
            'terjadwal'  => ['kelas' => 'bg-warning-subtle text-warning',     'label' => 'Terjadwal'],
            'selesai'    => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Selesai'],
            'batal'      => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => 'Batal'],
            // Status area
            'tercakup'   => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Tercakup'],
            'segera'     => ['kelas' => 'bg-warning-subtle text-warning',     'label' => 'Segera'],
            // Status notifikasi
            'terkirim'   => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Terkirim'],
            'draft'      => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => 'Draft'],
            default      => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => ucfirst($status)],
        };
```

- [ ] **Step 2: Tambah `$daftarLead` di `admin-config.php`**

Setelah blok `$daftarTagihan` (akhir file, baris 44), tambahkan:

```php

// Daftar lead dari form cek jangkauan (UI only)
$daftarLead = [
    ['id' => 'LEAD-0451', 'nama' => 'Rizki Maulana',  'hp' => '0812-1111-2233', 'area' => 'Cibinong, Bogor',      'tanggal' => '20 Jun 2026', 'status' => 'baru'],
    ['id' => 'LEAD-0452', 'nama' => 'Putri Lestari',  'hp' => '0813-4455-6677', 'area' => 'Depok, Jawa Barat',    'tanggal' => '20 Jun 2026', 'status' => 'dihubungi'],
    ['id' => 'LEAD-0453', 'nama' => 'Hendra Wijaya',  'hp' => '0856-7788-9900', 'area' => 'Bekasi, Jawa Barat',   'tanggal' => '19 Jun 2026', 'status' => 'terjadwal'],
    ['id' => 'LEAD-0454', 'nama' => 'Nadia Safira',   'hp' => '0878-1212-3434', 'area' => 'Tangerang, Banten',    'tanggal' => '18 Jun 2026', 'status' => 'selesai'],
    ['id' => 'LEAD-0455', 'nama' => 'Bayu Saputra',   'hp' => '0821-5656-7878', 'area' => 'Sleman, Yogyakarta',   'tanggal' => '17 Jun 2026', 'status' => 'batal'],
    ['id' => 'LEAD-0456', 'nama' => 'Citra Anggun',   'hp' => '0813-9090-1212', 'area' => 'Bogor, Jawa Barat',    'tanggal' => '17 Jun 2026', 'status' => 'baru'],
];
```

- [ ] **Step 3: Buat `admin/lead.php`**

```php
<?php
// lead.php (admin) — daftar lead cek jangkauan: cari, filter status, detail, tandai dihubungi (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Lead';
$menuAktif = 'lead';

// Kelas & label badge "dihubungi" dipakai JS saat tombol "Tandai Dihubungi"
$badgeDihubungi = badgeStatus('dihubungi');
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Lead Cek Jangkauan</h5>
      <p class="text-muted small mb-0"><?= count($daftarLead) ?> calon pelanggan dari form cek jangkauan.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <div class="input-group cari-pelanggan">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control" id="cariLead" placeholder="Cari nama / area...">
      </div>
      <select class="form-select" id="filterLead" style="max-width:180px">
        <option value="semua">Semua status</option>
        <option value="baru">Baru</option>
        <option value="dihubungi">Dihubungi</option>
        <option value="terjadwal">Terjadwal</option>
        <option value="selesai">Selesai</option>
        <option value="batal">Batal</option>
      </select>
    </div>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>Lead</th><th>No. HP</th><th>Area</th><th>Tanggal</th><th>Status</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody id="tabelLead">
          <?php foreach ($daftarLead as $l): $b = badgeStatus($l['status']); ?>
          <tr data-status="<?= htmlspecialchars($l['status']) ?>"
              data-cari="<?= htmlspecialchars(mb_strtolower($l['nama'] . ' ' . $l['area'])) ?>">
            <td>
              <div class="fw-600"><?= htmlspecialchars($l['nama']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($l['id']) ?></div>
            </td>
            <td><?= htmlspecialchars($l['hp']) ?></td>
            <td><?= htmlspecialchars($l['area']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($l['tanggal']) ?></td>
            <td class="kolom-status"><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
            <td class="text-end kolom-aksi">
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
                <button type="button" class="btn btn-sm btn-st btn-tandai-dihubungi"><i class="bi bi-telephone me-1"></i>Tandai Dihubungi</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="text-muted small text-center mt-3 mb-0 d-none" id="kosongLead">Tidak ada lead yang cocok.</p>
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
    const badgeDihubungi = <?= json_encode($badgeDihubungi) ?>;

    // Cari + filter status digabung
    const cariLead = document.getElementById('cariLead');
    const filterLead = document.getElementById('filterLead');
    function saringLead() {
      const kunci = cariLead.value.trim().toLowerCase();
      const status = filterLead.value;
      let terlihat = 0;
      document.querySelectorAll('#tabelLead tr').forEach(tr => {
        const cocok = tr.dataset.cari.includes(kunci) && (status === 'semua' || tr.dataset.status === status);
        tr.classList.toggle('d-none', !cocok);
        if (cocok) terlihat++;
      });
      document.getElementById('kosongLead').classList.toggle('d-none', terlihat > 0);
    }
    cariLead.addEventListener('input', saringLead);
    filterLead.addEventListener('change', saringLead);

    // Isi modal detail dari atribut tombol
    document.getElementById('modalDetailLead').addEventListener('show.bs.modal', (e) => {
      const d = e.relatedTarget.dataset;
      const baris = { 'ID Lead': d.id, 'Nama': d.nama, 'No. HP': d.hp, 'Area': d.area, 'Tanggal': d.tanggal, 'Status': d.status };
      document.getElementById('isiDetailLead').innerHTML =
        Object.entries(baris).map(([k, v]) => `<li><span>${k}</span><strong>${v}</strong></li>`).join('');
    });

    // Tandai dihubungi (UI only): ubah badge & hilangkan tombol
    document.getElementById('tabelLead').addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-tandai-dihubungi');
      if (!btn) return;
      const tr = btn.closest('tr');
      tr.dataset.status = 'dihubungi';
      tr.querySelector('.kolom-status').innerHTML = `<span class="badge ${badgeDihubungi.kelas}">${badgeDihubungi.label}</span>`;
      btn.remove();
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>
```

- [ ] **Step 4: Lint**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l helpers.php
/d/WebServer/xampp82/php/php.exe -l admin-config.php
/d/WebServer/xampp82/php/php.exe -l admin/lead.php
```
Expected: "No syntax errors detected" untuk ketiganya.

- [ ] **Step 5: Verifikasi di browser**

Buka `http://localhost:8282/starlite/admin/lead.php`. Konfirmasi: tabel 6 lead tampil dengan badge berwarna; ketik di "Cari" memfilter baris; ubah dropdown status memfilter; klik "Detail" membuka modal terisi; klik "Tandai Dihubungi" (baris status Baru) mengubah badge jadi "Dihubungi" dan tombol hilang.

- [ ] **Step 6: Commit**

```bash
git add helpers.php admin-config.php admin/lead.php
git commit -m "feat(admin): modul Lead cek jangkauan (cari, filter, detail, tandai dihubungi)"
```

---

### Task 3: Modul Area

Halaman Area sebagai kartu (mirip `paket.php`): nama area, kota, badge status (tercakup/segera), jumlah pelanggan, + tombol Tambah/Edit area (modal mock).

**Files:**
- Modify: `admin-config.php` (tambah `$daftarArea`)
- Create: `admin/area.php`

**Interfaces:**
- Consumes: `badgeStatus()` kini menangani `tercakup|segera` (dari Task 2); pola kartu+modal dari `paket.php`.
- Produces: `$daftarArea` = array of `['nama','kota','status','jumlahPelanggan']`, status ∈ `tercakup|segera`.

- [ ] **Step 1: Tambah `$daftarArea` di `admin-config.php`**

Setelah blok `$daftarLead` (akhir file), tambahkan:

```php

// Daftar area cakupan layanan (UI only)
$daftarArea = [
    ['nama' => 'Cibinong',  'kota' => 'Bogor',      'status' => 'tercakup', 'jumlahPelanggan' => 312],
    ['nama' => 'Depok Kota','kota' => 'Depok',      'status' => 'tercakup', 'jumlahPelanggan' => 458],
    ['nama' => 'Bekasi Barat','kota' => 'Bekasi',   'status' => 'tercakup', 'jumlahPelanggan' => 274],
    ['nama' => 'Sleman',    'kota' => 'Yogyakarta', 'status' => 'segera',   'jumlahPelanggan' => 0],
    ['nama' => 'Serpong',   'kota' => 'Tangerang',  'status' => 'tercakup', 'jumlahPelanggan' => 196],
    ['nama' => 'Cimahi',    'kota' => 'Bandung',    'status' => 'segera',   'jumlahPelanggan' => 0],
];
```

- [ ] **Step 2: Buat `admin/area.php`**

```php
<?php
// area.php (admin) — daftar area cakupan + tambah/edit area (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Area';
$menuAktif = 'area';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Area Cakupan</h5>
      <p class="text-muted small mb-0"><?= count($daftarArea) ?> area terdaftar.</p>
    </div>
    <button class="btn btn-st" type="button" data-bs-toggle="modal" data-bs-target="#modalEditArea"
      data-mode="tambah"><i class="bi bi-plus-lg me-1"></i>Tambah Area</button>
  </div>

  <div class="row g-3">
    <?php foreach ($daftarArea as $a): $b = badgeStatus($a['status']); ?>
    <div class="col-md-6 col-xl-4">
      <div class="kartu kartu-pad h-100">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge bg-primary-subtle text-primary"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($a['kota']) ?></span>
          <span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span>
        </div>
        <h6 class="fw-700 mb-1"><?= htmlspecialchars($a['nama']) ?></h6>
        <div class="d-flex align-items-center gap-2 text-muted small mb-3">
          <i class="bi bi-people-fill text-st"></i> <?= number_format($a['jumlahPelanggan'], 0, ',', '.') ?> pelanggan
        </div>
        <button type="button" class="btn btn-outline-primary w-100 btn-edit-area"
          data-mode="edit"
          data-nama="<?= htmlspecialchars($a['nama']) ?>"
          data-kota="<?= htmlspecialchars($a['kota']) ?>"
          data-status="<?= htmlspecialchars($a['status']) ?>"
          data-bs-toggle="modal" data-bs-target="#modalEditArea">
          <i class="bi bi-pencil me-1"></i>Edit Area
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Modal tambah/edit area (UI only) -->
  <div class="modal fade" id="modalEditArea" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header st-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0" id="judulModalArea">Edit Area</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form id="formArea" class="row g-3">
            <div class="col-12">
              <label class="form-label fw-500 small">Nama Area</label>
              <input type="text" class="form-control" id="areaNama" placeholder="cth. Cibinong" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Kota</label>
              <input type="text" class="form-control" id="areaKota" placeholder="cth. Bogor" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Status</label>
              <select class="form-select" id="areaStatus">
                <option value="tercakup">Tercakup</option>
                <option value="segera">Segera</option>
              </select>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-st btn-lg">Simpan Area</button>
            </div>
          </form>
          <div id="suksesArea" class="alert alert-success text-center mb-0 mt-3 d-none">
            <i class="bi bi-check-circle-fill me-1"></i> Area berhasil disimpan.
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Isi modal sesuai mode (tambah / edit)
    document.getElementById('modalEditArea').addEventListener('show.bs.modal', (e) => {
      const d = e.relatedTarget.dataset;
      const edit = d.mode === 'edit';
      document.getElementById('judulModalArea').textContent = edit ? 'Edit Area' : 'Tambah Area';
      document.getElementById('areaNama').value = edit ? d.nama : '';
      document.getElementById('areaKota').value = edit ? d.kota : '';
      document.getElementById('areaStatus').value = edit ? d.status : 'tercakup';
      document.getElementById('formArea').classList.remove('d-none');
      document.getElementById('suksesArea').classList.add('d-none');
    });

    // Simpan (UI only)
    document.getElementById('formArea').addEventListener('submit', (e) => {
      e.preventDefault();
      document.getElementById('formArea').classList.add('d-none');
      document.getElementById('suksesArea').classList.remove('d-none');
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>
```

- [ ] **Step 3: Lint**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l admin-config.php
/d/WebServer/xampp82/php/php.exe -l admin/area.php
```
Expected: "No syntax errors detected".

- [ ] **Step 4: Verifikasi di browser**

Buka `http://localhost:8282/starlite/admin/area.php`. Konfirmasi: 6 kartu area tampil dengan badge kota & status; klik "Tambah Area" membuka modal kosong berjudul "Tambah Area"; klik "Edit Area" membuka modal terisi berjudul "Edit Area"; submit menampilkan pesan sukses.

- [ ] **Step 5: Commit**

```bash
git add admin-config.php admin/area.php
git commit -m "feat(admin): modul Area cakupan (kartu, tambah/edit modal)"
```

---

### Task 4: Modul Notifikasi

Halaman Notifikasi: tabel notifikasi (judul, target, tanggal, badge status) + filter tab Terkirim/Draft + tombol "Tulis Notifikasi" (modal mock).

**Files:**
- Modify: `admin-config.php` (tambah `$daftarNotifikasi`)
- Create: `admin/notifikasi.php`

**Interfaces:**
- Consumes: `badgeStatus()` kini menangani `terkirim|draft` (dari Task 2); pola filter btn-group dari `transaksi.php`, pola modal form dari `paket.php`.
- Produces: `$daftarNotifikasi` = array of `['judul','isi','target','tanggal','status']`, status ∈ `terkirim|draft`.

- [ ] **Step 1: Tambah `$daftarNotifikasi` di `admin-config.php`**

Setelah blok `$daftarArea` (akhir file), tambahkan:

```php

// Daftar notifikasi / broadcast ke pelanggan (UI only)
$daftarNotifikasi = [
    ['judul' => 'Pemeliharaan jaringan area Depok', 'isi' => 'Akan ada pemeliharaan 23 Jun 2026 pukul 01.00-03.00 WIB.', 'target' => 'Pelanggan Depok', 'tanggal' => '20 Jun 2026', 'status' => 'terkirim'],
    ['judul' => 'Promo upgrade 500 Mbps',           'isi' => 'Upgrade paket bulan ini diskon 30% untuk 3 bulan pertama.', 'target' => 'Semua pelanggan',  'tanggal' => '18 Jun 2026', 'status' => 'terkirim'],
    ['judul' => 'Pengingat jatuh tempo tagihan',    'isi' => 'Tagihan Juni jatuh tempo 15 Jun. Mohon segera lakukan pembayaran.', 'target' => 'Pelanggan aktif', 'tanggal' => '14 Jun 2026', 'status' => 'terkirim'],
    ['judul' => 'Selamat datang pelanggan baru',    'isi' => 'Draf sambutan untuk pelanggan yang baru bergabung.', 'target' => 'Pelanggan baru',   'tanggal' => '12 Jun 2026', 'status' => 'draft'],
    ['judul' => 'Survei kepuasan layanan Q2',       'isi' => 'Draf undangan mengisi survei kepuasan kuartal 2.', 'target' => 'Semua pelanggan',  'tanggal' => '10 Jun 2026', 'status' => 'draft'],
];
```

- [ ] **Step 2: Buat `admin/notifikasi.php`**

```php
<?php
// notifikasi.php (admin) — daftar notifikasi + filter terkirim/draft + tulis baru (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Notifikasi';
$menuAktif = 'notifikasi';
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
      <div class="btn-group" role="group" id="filterNotif">
        <button type="button" class="btn btn-sm btn-st" data-filter="semua">Semua</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="terkirim">Terkirim</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="draft">Draft</button>
      </div>
      <button class="btn btn-st" type="button" data-bs-toggle="modal" data-bs-target="#modalTulisNotif">
        <i class="bi bi-plus-lg me-1"></i>Tulis Notifikasi</button>
    </div>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>Judul</th><th>Target</th><th>Tanggal</th><th>Status</th></tr>
        </thead>
        <tbody id="tabelNotif">
          <?php foreach ($daftarNotifikasi as $n): $b = badgeStatus($n['status']); ?>
          <tr data-status="<?= htmlspecialchars($n['status']) ?>">
            <td>
              <div class="fw-600"><?= htmlspecialchars($n['judul']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($n['isi']) ?></div>
            </td>
            <td><?= htmlspecialchars($n['target']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($n['tanggal']) ?></td>
            <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="text-muted small text-center mt-3 mb-0 d-none" id="kosongNotif">Tidak ada notifikasi pada filter ini.</p>
    </div>
  </div>

  <!-- Modal tulis notifikasi (UI only) -->
  <div class="modal fade" id="modalTulisNotif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header st-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0">Tulis Notifikasi</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form id="formNotif" class="row g-3">
            <div class="col-12">
              <label class="form-label fw-500 small">Judul</label>
              <input type="text" class="form-control" placeholder="Judul notifikasi" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Target</label>
              <select class="form-select">
                <option>Semua pelanggan</option>
                <option>Pelanggan aktif</option>
                <option>Pelanggan baru</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Isi Pesan</label>
              <textarea class="form-control" rows="3" placeholder="Tulis isi notifikasi..." required></textarea>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-st btn-lg">Kirim Notifikasi</button>
            </div>
          </form>
          <div id="suksesNotif" class="alert alert-success text-center mb-0 mt-3 d-none">
            <i class="bi bi-check-circle-fill me-1"></i> Notifikasi berhasil dikirim.
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Filter berdasarkan status
    const tombolFilter = document.querySelectorAll('#filterNotif button');
    tombolFilter.forEach(btn => btn.addEventListener('click', () => {
      tombolFilter.forEach(b => { b.classList.remove('btn-st'); b.classList.add('btn-outline-primary'); });
      btn.classList.add('btn-st'); btn.classList.remove('btn-outline-primary');
      const filter = btn.dataset.filter;
      let terlihat = 0;
      document.querySelectorAll('#tabelNotif tr').forEach(tr => {
        const cocok = filter === 'semua' || tr.dataset.status === filter;
        tr.classList.toggle('d-none', !cocok);
        if (cocok) terlihat++;
      });
      document.getElementById('kosongNotif').classList.toggle('d-none', terlihat > 0);
    }));

    // Tulis notifikasi (UI only)
    document.getElementById('formNotif').addEventListener('submit', (e) => {
      e.preventDefault();
      document.getElementById('formNotif').classList.add('d-none');
      document.getElementById('suksesNotif').classList.remove('d-none');
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>
```

- [ ] **Step 3: Lint**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l admin-config.php
/d/WebServer/xampp82/php/php.exe -l admin/notifikasi.php
```
Expected: "No syntax errors detected".

- [ ] **Step 4: Verifikasi di browser**

Buka `http://localhost:8282/starlite/admin/notifikasi.php`. Konfirmasi: 5 notifikasi tampil; filter Terkirim/Draft menyaring baris; "Tulis Notifikasi" membuka modal; submit menampilkan pesan sukses.

- [ ] **Step 5: Commit**

```bash
git add admin-config.php admin/notifikasi.php
git commit -m "feat(admin): modul Notifikasi (tabel, filter terkirim/draft, tulis modal)"
```

---

### Task 5: Modul Pengaturan

Halaman Pengaturan: tiga kartu form — Profil Admin (`$admin`), Ubah Password, Info Situs (`$pengaturan`). Pola form mirip `portal/profil.php`, semua mock.

**Files:**
- Modify: `admin-config.php` (tambah `$pengaturan`)
- Create: `admin/pengaturan.php`

**Interfaces:**
- Consumes: `$admin` = `['nama','email','peran']` (sudah ada); pola form dari `portal/profil.php`.
- Produces: `$pengaturan` = `['namaSitus','email','telepon','alamat']`.

- [ ] **Step 1: Tambah `$pengaturan` di `admin-config.php`**

Setelah blok `$daftarNotifikasi` (akhir file), tambahkan:

```php

// Pengaturan situs (UI only)
$pengaturan = [
    'namaSitus' => 'Starlite Indonesia',
    'email'     => 'cs@starlite.id',
    'telepon'   => '0804-1-555-666',
    'alamat'    => 'Jl. Fiber Optik No. 1, Jakarta Selatan',
];
```

- [ ] **Step 2: Buat `admin/pengaturan.php`**

```php
<?php
// pengaturan.php (admin) — profil admin, ubah password, info situs (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Pengaturan';
$menuAktif = 'pengaturan';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="mb-4">
    <h5 class="fw-700 mb-1">Pengaturan</h5>
    <p class="text-muted small mb-0">Kelola profil admin & informasi situs.</p>
  </div>

  <div class="row g-4">
    <!-- Profil admin -->
    <div class="col-lg-7">
      <div class="kartu kartu-pad h-100">
        <h6 class="fw-700 mb-3"><i class="bi bi-person-vcard text-st me-2"></i>Profil Admin</h6>
        <form action="pengaturan.php" method="get" class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-500 small">Nama</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($admin['nama']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Peran</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($admin['peran']) ?>" readonly>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>">
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-st"><i class="bi bi-check2 me-1"></i>Simpan Profil</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Ubah password -->
    <div class="col-lg-5">
      <div class="kartu kartu-pad h-100">
        <h6 class="fw-700 mb-3"><i class="bi bi-shield-lock text-st me-2"></i>Ubah Password</h6>
        <form action="pengaturan.php" method="get" class="row g-3">
          <div class="col-12">
            <label class="form-label fw-500 small">Password Saat Ini</label>
            <input type="password" class="form-control" placeholder="Masukkan password lama">
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Password Baru</label>
            <input type="password" class="form-control" placeholder="Minimal 6 karakter">
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" placeholder="Ulangi password baru">
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-key me-1"></i>Perbarui Password</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Info situs -->
    <div class="col-12">
      <div class="kartu kartu-pad">
        <h6 class="fw-700 mb-3"><i class="bi bi-globe text-st me-2"></i>Informasi Situs</h6>
        <form action="pengaturan.php" method="get" class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-500 small">Nama Situs</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($pengaturan['namaSitus']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Email CS</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($pengaturan['email']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Telepon</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($pengaturan['telepon']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Alamat</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($pengaturan['alamat']) ?>">
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-st"><i class="bi bi-check2 me-1"></i>Simpan Pengaturan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>
```

- [ ] **Step 3: Lint**

Run:
```bash
/d/WebServer/xampp82/php/php.exe -l admin-config.php
/d/WebServer/xampp82/php/php.exe -l admin/pengaturan.php
```
Expected: "No syntax errors detected".

- [ ] **Step 4: Verifikasi di browser**

Buka `http://localhost:8282/starlite/admin/pengaturan.php`. Konfirmasi: tiga kartu (Profil Admin, Ubah Password, Informasi Situs) terisi nilai dari config; field "Peran" readonly; tombol simpan ada di tiap kartu (submit me-reload halaman — mock).

- [ ] **Step 5: Commit**

```bash
git add admin-config.php admin/pengaturan.php
git commit -m "feat(admin): modul Pengaturan (profil admin, ubah password, info situs)"
```

---

## Catatan Penutup

Setelah kelima task selesai, sidebar LAINNYA terhubung penuh ke 4 halaman baru. Tidak ada perubahan pada modul inti selain sidebar. Modul di luar empat ini (mis. integrasi lead nyata) tetap out of scope.

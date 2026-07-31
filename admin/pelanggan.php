<?php
// pelanggan.php (admin) - daftar, edit, dan persetujuan status pelanggan.
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Pelanggan';
$menuAktif = 'pelanggan';

// Filter cari (server-side)
$cari = isset($_GET['cari']) && is_string($_GET['cari']) ? trim($_GET['cari']) : '';
if (mb_strlen($cari) > 150) {
    $cari = mb_substr($cari, 0, 150);
}
$where = '';
$params = [];
if ($cari !== '') {
    $where = "WHERE LOWER(pl.nama) LIKE ? OR LOWER(pl.id) LIKE ?";
    $kunci = '%' . mb_strtolower($cari) . '%';
    $params = [$kunci, $kunci];
}
$sqlBase = "SELECT pl.id, pl.nama, pl.email, pl.hp, pl.alamat, pk.kecepatan AS paket, pl.status, pl.tgl_bergabung AS bergabung
            FROM pelanggan pl LEFT JOIN paket pk ON pk.id = pl.paket_id $where
            ORDER BY CASE WHEN pl.status = 'pending' THEN 0 ELSE 1 END, pl.id DESC";
$sqlCount = "SELECT COUNT(*) FROM pelanggan pl $where";
$hasil = ambilPaginasi($sqlBase, $sqlCount, $params);

// Data untuk lembar cetak: SEMUA pelanggan (hormati ?cari=), tanpa paginasi.
$daftarCetak = kueri($sqlBase, $params);
$jumlahAktifCetak = count(array_filter($daftarCetak, static fn(array $p): bool => $p['status'] === 'aktif'));
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4 no-print">
    <div>
      <h5 class="fw-700 mb-1">Manajemen Pelanggan</h5>
      <p class="text-muted small mb-0">Total <?= $hasil['total'] ?> pelanggan<?= $cari !== '' ? ' cocok' : ' terdaftar' ?>.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <form method="get" class="input-group cari-pelanggan">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="cari" class="form-control" placeholder="Cari nama / ID pelanggan..." value="<?= htmlspecialchars($cari) ?>" maxlength="150">
        <button class="btn btn-rk" type="submit">Cari</button>
      </form>
      <button onclick="window.print()" class="btn btn-outline-primary"><i class="bi bi-printer me-1"></i>Cetak</button>
    </div>
  </div>

  <div class="kartu kartu-pad no-print">
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
              <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                <button type="button" class="btn btn-sm btn-light btn-detail"
                data-nama="<?= htmlspecialchars($p['nama']) ?>"
                data-id="<?= htmlspecialchars($p['id']) ?>"
                data-email="<?= htmlspecialchars($p['email']) ?>"
                data-hp="<?= htmlspecialchars($p['hp']) ?>"
                data-alamat="<?= htmlspecialchars($p['alamat'] ?? '') ?>"
                data-status="<?= htmlspecialchars($p['status']) ?>"
                data-bs-toggle="modal" data-bs-target="#modalDetailPelanggan">
                  <i class="bi bi-eye"></i> Detail
                </button>
                <?php
                  $aksiStatus = match ($p['status']) {
                      'pending' => [
                          ['aktif', 'Setujui', 'btn-outline-success', 'bi-check-circle', ''],
                          ['nonaktif', 'Tolak', 'btn-outline-danger', 'bi-x-circle', 'Tolak pendaftaran pelanggan ini?'],
                      ],
                      'aktif' => [['nonaktif', 'Nonaktifkan', 'btn-outline-danger', 'bi-person-dash', 'Nonaktifkan akun pelanggan ini?']],
                      'nonaktif' => [['aktif', 'Aktifkan', 'btn-outline-success', 'bi-person-check', '']],
                      default => [],
                  };
                ?>
                <?php foreach ($aksiStatus as [$target, $label, $kelas, $ikon, $konfirmasi]): ?>
                  <form method="post" action="aksi-pelanggan.php" <?= $konfirmasi !== '' ? 'onsubmit="return confirm(\'' . htmlspecialchars($konfirmasi, ENT_QUOTES) . '\')"' : '' ?>>
                    <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
                    <input type="hidden" name="aksi" value="status">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                    <input type="hidden" name="status_saat_ini" value="<?= htmlspecialchars($p['status']) ?>">
                    <input type="hidden" name="status_tujuan" value="<?= htmlspecialchars($target) ?>">
                    <button type="submit" class="btn btn-sm <?= $kelas ?>"><i class="bi <?= $ikon ?>"></i> <?= $label ?></button>
                  </form>
                <?php endforeach; ?>
              </div>
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
        <div class="modal-header rk-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0">Detail Pelanggan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form method="post" action="aksi-pelanggan.php" class="row g-3">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="plId">
            <div class="col-12">
              <label class="form-label fw-500 small" for="plIdTampil">ID Pelanggan</label>
              <input type="text" class="form-control" id="plIdTampil" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small" for="plStatus">Status</label>
              <input type="text" class="form-control" id="plStatus" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small" for="plNama">Nama</label>
              <input type="text" name="nama" class="form-control" id="plNama" maxlength="100" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small" for="plEmail">Email</label>
              <input type="email" name="email" class="form-control" id="plEmail" maxlength="150" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small" for="plHp">No. HP</label>
              <input type="text" name="hp" class="form-control" id="plHp" maxlength="30" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small" for="plAlamat">Alamat</label>
              <input type="text" name="alamat" class="form-control" id="plAlamat" maxlength="255">
            </div>
            <div class="col-12 d-grid">
              <button type="submit" class="btn btn-rk"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
            </div>
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
      document.getElementById('plStatus').value = d.status;
      document.getElementById('plNama').value = d.nama;
      document.getElementById('plEmail').value = d.email;
      document.getElementById('plHp').value = d.hp;
      document.getElementById('plAlamat').value = d.alamat || '';
    });
  </script>

  <!-- Lembar cetak: seluruh pelanggan (hormati ?cari=), tidak terpengaruh paginasi -->
  <div class="print-sheet">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 pb-3 mb-3 border-bottom">
      <div>
        <h5 class="fw-700 mb-1">Laporan Data Pelanggan</h5>
        <p class="text-muted small mb-0">
          RKnet Indonesia • <?= tanggalIndonesia() ?>
          <?= $cari !== '' ? ' • Pencarian: ' . htmlspecialchars($cari) : '' ?>
        </p>
      </div>
      <div class="text-end">
        <div class="fw-600">Total <?= count($daftarCetak) ?> pelanggan</div>
        <div class="text-muted small"><?= $jumlahAktifCetak ?> aktif • <?= count($daftarCetak) - $jumlahAktifCetak ?> nonaktif/pending</div>
      </div>
    </div>
    <table class="table align-middle mb-0">
      <thead>
        <tr><th style="width:50px">No</th><th>ID</th><th>Nama</th><th>Kontak</th><th>Paket</th><th>Bergabung</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach ($daftarCetak as $p): $b = badgeStatus($p['status']); ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($p['id']) ?></td>
          <td class="fw-600"><?= htmlspecialchars($p['nama']) ?></td>
          <td>
            <div class="small"><?= htmlspecialchars($p['email']) ?></div>
            <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($p['hp']) ?></div>
          </td>
          <td><?= htmlspecialchars($p['paket']) ?></td>
          <td class="text-muted small"><?= htmlspecialchars($p['bergabung']) ?></td>
          <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if ($daftarCetak === []): ?>
        <tr><td colspan="7" class="text-muted small text-center py-3">Tidak ada data pelanggan.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

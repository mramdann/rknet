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
$hasil = ambilPaginasi($sqlBase, $sqlCount, $params);
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
      <button class="btn btn-rk" type="submit">Cari</button>
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
              <button type="submit" class="btn btn-rk"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
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

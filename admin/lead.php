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
$hasil = ambilPaginasi($sqlBase, $sqlCount, $params);

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
      <button type="submit" class="btn btn-rk">Cari</button>
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
                  <button type="submit" class="btn btn-sm btn-rk"><i class="bi bi-telephone me-1"></i>Tandai Dihubungi</button>
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
        <div class="modal-header rk-modal-head text-white border-0">
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

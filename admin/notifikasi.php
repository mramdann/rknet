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
$hasil = ambilPaginasi($sqlBase, $sqlCount, $params);
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
        <a href="?status=" class="btn btn-sm <?= $status === '' ? 'btn-rk' : 'btn-outline-primary' ?>">Semua</a>
        <a href="?status=terkirim" class="btn btn-sm <?= $status === 'terkirim' ? 'btn-rk' : 'btn-outline-primary' ?>">Terkirim</a>
        <a href="?status=draft" class="btn btn-sm <?= $status === 'draft' ? 'btn-rk' : 'btn-outline-primary' ?>">Draft</a>
      </div>
      <button class="btn btn-rk" type="button" data-bs-toggle="modal" data-bs-target="#modalTulisNotif">
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
        <div class="modal-header rk-modal-head text-white border-0">
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
              <button type="submit" class="btn btn-rk btn-lg">Kirim Notifikasi</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

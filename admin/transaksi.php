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
$hasil = ambilPaginasi($sqlBase, $sqlCount, $params);
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

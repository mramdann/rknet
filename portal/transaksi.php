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

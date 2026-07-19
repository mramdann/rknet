<?php
// invoice.php — kuitansi/tagihan satu transaksi (UI only). Memakai transaksi terbaru sebagai contoh.
require __DIR__ . '/../config.php';
require __DIR__ . '/../portal-config.php';
$judulHalaman = 'Invoice';
$menuAktif = 'invoice';

// Ambil satu transaksi sebagai isi kuitansi (transaksi terbaru)
$trx = $daftarTransaksi[0];
$badge = badgeStatus($trx['status']);
$lunas = $trx['status'] === 'lunas';

// Rincian biaya — harga sudah termasuk PPN 11%
$total   = $trx['harga'];
$dpp     = (int) round($total / 1.11);   // dasar pengenaan pajak
$ppn     = $total - $dpp;                 // nilai PPN 11%
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <a href="transaksi.php" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    <div class="d-flex gap-2">
      <?php if (!$lunas): ?><a href="#" class="btn btn-rk"><i class="bi bi-credit-card me-1"></i>Bayar Sekarang</a><?php endif; ?>
      <button onclick="window.print()" class="btn btn-outline-primary"><i class="bi bi-printer me-1"></i>Cetak / Unduh</button>
    </div>
  </div>

  <div class="kartu kartu-pad invoice-sheet mx-auto">
    <!-- Kepala kuitansi -->
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 pb-3 border-bottom">
      <div class="d-flex align-items-center gap-2">
        <img src="../assets/img/rknet.jpeg" alt="RKnet" height="32">
        <span class="brand-divider" style="display:inline-block;width:1px;height:24px;background:rgba(6,37,110,.2)"></span>
        <img src="../assets/img/rknet2.jpeg" alt="RWS Solution" height="24">
      </div>
      <div class="text-end">
        <h5 class="fw-800 text-rk mb-1">INVOICE</h5>
        <div class="small text-muted"><?= htmlspecialchars($trx['noInvoice']) ?></div>
        <span class="badge <?= $badge['kelas'] ?> mt-1"><?= $badge['label'] ?></span>
      </div>
    </div>

    <!-- Ditagihkan kepada & penerbit -->
    <div class="row g-4 py-4">
      <div class="col-sm-6">
        <div class="text-muted text-uppercase fw-600 mb-2" style="font-size:.72rem">Ditagihkan Kepada</div>
        <div class="fw-700"><?= htmlspecialchars($pelanggan['nama']) ?></div>
        <div class="small text-muted"><?= htmlspecialchars($pelanggan['id']) ?></div>
        <div class="small text-muted"><?= htmlspecialchars($pelanggan['alamat']) ?></div>
        <div class="small text-muted"><?= htmlspecialchars($pelanggan['hp']) ?></div>
      </div>
      <div class="col-sm-6 text-sm-end">
        <div class="text-muted text-uppercase fw-600 mb-2" style="font-size:.72rem">Diterbitkan Oleh</div>
        <div class="fw-700">PT Integrasi Jaringan Ekosistem</div>
        <div class="small text-muted">Jl. Tiang Bendera V No.20, Jakarta Barat</div>
        <div class="small text-muted">+62811789111</div>
        <div class="small text-muted mt-2">Tanggal: <?= htmlspecialchars($trx['tanggal']) ?></div>
      </div>
    </div>

    <!-- Rincian item -->
    <div class="table-responsive">
      <table class="table tabel-portal mb-0">
        <thead>
          <tr><th>Deskripsi</th><th>Periode</th><th class="text-end">Jumlah</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div class="fw-600"><?= htmlspecialchars($trx['paket']) ?></div>
              <div class="text-muted small">Langganan internet <?= htmlspecialchars($trx['kecepatan']) ?> — Unlimited</div>
            </td>
            <td class="text-muted">1 Bulan</td>
            <td class="text-end fw-600"><?= formatRupiah($dpp) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Total -->
    <div class="row justify-content-end pt-3">
      <div class="col-sm-6 col-md-5">
        <div class="d-flex justify-content-between py-1 small"><span class="text-muted">Subtotal</span><span><?= formatRupiah($dpp) ?></span></div>
        <div class="d-flex justify-content-between py-1 small"><span class="text-muted">PPN 11%</span><span><?= formatRupiah($ppn) ?></span></div>
        <hr class="my-2">
        <div class="d-flex justify-content-between py-1"><span class="fw-700">Total</span><span class="fw-800 text-rk fs-5"><?= formatRupiah($total) ?></span></div>
      </div>
    </div>

    <div class="text-center text-muted small mt-4 pt-3 border-top">
      Terima kasih telah berlangganan RKnet Indonesia. Kuitansi ini sah & dihasilkan otomatis oleh sistem.
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

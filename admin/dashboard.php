<?php
// dashboard.php (admin) — ringkasan statistik & data terbaru.
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Dashboard';
$menuAktif = 'dashboard';

// Definisi kartu statistik: [label, nilai, ikon, warna]
$kartuStatistik = [
    ['Total Pelanggan',     number_format($statistik['totalPelanggan'], 0, ',', '.'), 'bi-people-fill',      'biru'],
    ['Pelanggan Aktif',     number_format($statistik['pelangganAktif'], 0, ',', '.'), 'bi-person-check-fill', 'hijau'],
    ['Pendapatan Bln Ini',  formatRupiah($statistik['pendapatanBulan']),              'bi-cash-stack',       'ungu'],
    ['Tagihan Pending',     (string) $statistik['tagihanPending'],                    'bi-receipt',          'oranye'],
];
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="mb-4">
    <h5 class="fw-700 mb-1">Selamat datang, <?= htmlspecialchars(explode(' ', $admin['nama'])[0]) ?> 👋</h5>
    <p class="text-muted small mb-0">Ringkasan layanan RKnet hari ini.</p>
  </div>

  <!-- Kartu statistik -->
  <div class="row g-3 mb-4">
    <?php foreach ($kartuStatistik as $k): ?>
    <div class="col-md-6 col-xl-3">
      <div class="kartu kartu-pad stat-kartu">
        <div class="stat-ikon stat-<?= $k[3] ?>"><i class="bi <?= $k[2] ?>"></i></div>
        <div>
          <div class="stat-nilai fw-800"><?= htmlspecialchars($k[1]) ?></div>
          <div class="text-muted small"><?= htmlspecialchars($k[0]) ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <!-- Pelanggan terbaru -->
    <div class="col-xl-7">
      <div class="kartu kartu-pad h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="fw-700 mb-0"><i class="bi bi-people text-st me-2"></i>Pelanggan Terbaru</h6>
          <a href="pelanggan.php" class="small text-st text-decoration-none fw-500">Lihat semua <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0 tabel-portal">
            <thead><tr><th>Nama</th><th>Paket</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach (array_slice($daftarPelanggan, 0, 5) as $p): $b = badgeStatus($p['status']); ?>
              <tr>
                <td>
                  <div class="fw-600"><?= htmlspecialchars($p['nama']) ?></div>
                  <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($p['id']) ?></div>
                </td>
                <td><?= htmlspecialchars($p['paket']) ?></td>
                <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tagihan terbaru -->
    <div class="col-xl-5">
      <div class="kartu kartu-pad h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="fw-700 mb-0"><i class="bi bi-receipt-cutoff text-st me-2"></i>Tagihan Terbaru</h6>
          <a href="transaksi.php" class="small text-st text-decoration-none fw-500">Lihat semua <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0 tabel-portal">
            <thead><tr><th>Pelanggan</th><th>Nominal</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach (array_slice($daftarTagihan, 0, 5) as $t): $b = badgeStatus($t['status']); ?>
              <tr>
                <td class="fw-600"><?= htmlspecialchars($t['pelanggan']) ?></td>
                <td><?= formatRupiah($t['harga']) ?></td>
                <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

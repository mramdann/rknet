<?php
// dashboard.php — halaman utama area pelanggan: akun, paket aktif, ringkasan transaksi.
require __DIR__ . '/../config.php';
require __DIR__ . '/../portal-config.php';
$judulHalaman = 'Dashboard';
$menuAktif = 'dashboard';
$badgePaket = badgeStatus($paketAktif['status']);
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <!-- Banner sambutan -->
  <div class="kartu dashboard-banner mb-4 overflow-hidden">
    <div class="kartu-pad d-flex justify-content-between align-items-center">
      <div>
        <p class="mb-1 opacity-75 small">Selamat datang kembali,</p>
        <h4 class="fw-700 mb-2 text-white"><?= htmlspecialchars($pelanggan['nama']) ?> 👋</h4>
        <p class="mb-0 opacity-75 small">Pantau paket internet & tagihan Anda di sini.</p>
      </div>
      <i class="bi bi-house-door-fill d-none d-md-block" style="font-size:5rem;opacity:.35"></i>
    </div>
  </div>

  <div class="row g-4">
    <!-- Kolom kiri: Akun Anda -->
    <div class="col-lg-6">
      <div class="kartu kartu-pad h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="fw-700 mb-0"><i class="bi bi-person-circle text-rk me-2"></i>Akun Anda</h6>
          <span class="badge <?= $badgePaket['kelas'] ?>"><?= $badgePaket['label'] ?></span>
        </div>
        <ul class="list-unstyled mb-0 info-akun">
          <li><span>ID Pelanggan</span><strong><?= htmlspecialchars($pelanggan['id']) ?></strong></li>
          <li><span>Nama</span><strong><?= htmlspecialchars($pelanggan['nama']) ?></strong></li>
          <li><span>Email</span><strong><?= htmlspecialchars($pelanggan['email']) ?></strong></li>
          <li><span>No. Handphone</span><strong><?= htmlspecialchars($pelanggan['hp']) ?></strong></li>
          <li><span>Alamat</span><strong class="text-end"><?= htmlspecialchars($pelanggan['alamat']) ?></strong></li>
        </ul>
      </div>
    </div>

    <!-- Kolom kanan: Paket Anda -->
    <div class="col-lg-6">
      <div class="kartu kartu-pad h-100 d-flex flex-column">
        <h6 class="fw-700 mb-3"><i class="bi bi-wifi text-rk me-2"></i>Paket Anda</h6>
        <div class="paket-aktif-box mb-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="fw-700 fs-5"><?= htmlspecialchars($paketAktif['nama']) ?></div>
              <div class="text-muted small">Kecepatan hingga <?= htmlspecialchars($paketAktif['kecepatan']) ?></div>
            </div>
            <div class="text-end">
              <div class="fw-800 text-rk fs-5"><?= formatRupiah($paketAktif['harga']) ?></div>
              <div class="text-muted" style="font-size:.75rem">per bulan</div>
            </div>
          </div>
          <hr class="my-3">
          <div class="d-flex align-items-center gap-2 small text-muted">
            <i class="bi bi-calendar-check text-success"></i> Aktif sampai <strong class="text-dark"><?= htmlspecialchars($paketAktif['masaAktif']) ?></strong>
          </div>
        </div>
        <div class="mt-auto d-flex gap-2">
          <a href="paket.php" class="btn btn-rk flex-fill">Perpanjang Paket</a>
          <a href="paket.php" class="btn btn-outline-primary flex-fill">Ubah Paket</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Ringkasan transaksi terakhir -->
  <div class="kartu kartu-pad mt-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h6 class="fw-700 mb-0"><i class="bi bi-clock-history text-rk me-2"></i>Transaksi Terakhir</h6>
      <a href="transaksi.php" class="small text-rk text-decoration-none fw-500">Lihat semua <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>No. Invoice</th><th>Paket</th><th>Tanggal</th><th>Nominal</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($daftarTransaksi, 0, 3) as $t):
            $b = badgeStatus($t['status']); ?>
          <tr>
            <td class="fw-500"><?= htmlspecialchars($t['noInvoice']) ?></td>
            <td><?= htmlspecialchars($t['paket'] ?? 'Paket tidak tersedia') ?></td>
            <td class="text-muted"><?= htmlspecialchars($t['tanggal']) ?></td>
            <td class="fw-600"><?= formatRupiah($t['harga']) ?></td>
            <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
            <td class="text-end"><a href="invoice.php?id=<?= (int) $t['idTagihan'] ?>" class="btn btn-sm btn-light">Detail</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

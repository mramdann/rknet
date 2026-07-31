<?php
// sidebar.php (admin) — navigasi samping bergrup. $menuAktif menentukan menu yang disorot.
$menuAktif = $menuAktif ?? '';
// Menu dikelompokkan: label grup => [kunci => [judul, ikon, file]]
$menuAdmin = [
    'UTAMA' => [
        'dashboard' => ['Dashboard', 'bi-grid-1x2',      'dashboard.php'],
        'pelanggan' => ['Pelanggan', 'bi-people',        'pelanggan.php'],
        'paket'     => ['Paket',     'bi-box-seam',       'paket.php'],
        'rekening'  => ['Rekening & QRIS', 'bi-bank',      'rekening.php'],
        'transaksi' => ['Transaksi', 'bi-receipt-cutoff', 'transaksi.php'],
    ],
    'LAINNYA' => [
        'notifikasi' => ['Notifikasi', 'bi-bell',              'notifikasi.php'],
        'pengaturan' => ['Pengaturan', 'bi-gear',              'pengaturan.php'],
    ],
];
?>
<aside class="portal-sidebar" id="portalSidebar">
  <div class="portal-brand">
    <img src="../assets/img/rknet.jpeg" alt="RKnet">
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
    <a href="logout.php" class="d-flex align-items-center gap-2 text-decoration-none text-danger fw-500">
      <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
  </div>
</aside>

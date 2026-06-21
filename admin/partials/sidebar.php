<?php
// sidebar.php (admin) — navigasi samping. $menuAktif menentukan menu yang disorot.
$menuAktif = $menuAktif ?? '';
$menuAdmin = [
    'dashboard' => ['Dashboard', 'bi-grid-1x2',      'dashboard.php'],
    'pelanggan' => ['Pelanggan', 'bi-people',        'pelanggan.php'],
    'paket'     => ['Paket',     'bi-box-seam',       'paket.php'],
    'transaksi' => ['Transaksi', 'bi-receipt-cutoff', 'transaksi.php'],
];
?>
<aside class="portal-sidebar" id="portalSidebar">
  <div class="portal-brand">
    <img src="../assets/img/logo-starlite.webp" alt="Starlite">
    <span class="brand-divider"></span>
    <span class="badge bg-primary-subtle text-primary fw-600">ADMIN</span>
  </div>
  <ul class="portal-nav">
    <?php foreach ($menuAdmin as $kunci => $menu): ?>
      <li>
        <a href="<?= $menu[2] ?>" class="<?= $menuAktif === $kunci ? 'aktif' : '' ?>">
          <i class="bi <?= $menu[1] ?>"></i> <span><?= htmlspecialchars($menu[0]) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <div class="portal-sidebar-foot">
    <a href="login.php" class="d-flex align-items-center gap-2 text-decoration-none text-danger fw-500">
      <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
  </div>
</aside>

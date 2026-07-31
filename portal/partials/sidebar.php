<?php
// sidebar.php — navigasi samping portal. Variabel $menuAktif menentukan menu yang disorot.
$menuAktif = $menuAktif ?? '';
// Daftar menu: kunci => [label, ikon, tautan]
$menuPortal = [
    'dashboard' => ['Dashboard',        'bi-grid-1x2',        'dashboard.php'],
    'transaksi' => ['Riwayat Transaksi', 'bi-clock-history',   'transaksi.php'],
    'paket'     => ['Pilih Paket',       'bi-wifi',            'paket.php'],
    'profil'    => ['Profil',            'bi-person',          'profil.php'],
];
?>
<aside class="portal-sidebar" id="portalSidebar">
  <div class="portal-brand">
    <img src="../assets/img/rknet.jpeg" alt="RKnet" class="logo-sidebar-rk">
  </div>
  <ul class="portal-nav">
    <?php foreach ($menuPortal as $kunci => $menu): ?>
      <li>
        <a href="<?= $menu[2] ?>" class="<?= $menuAktif === $kunci ? 'aktif' : '' ?>">
          <i class="bi <?= $menu[1] ?>"></i> <span><?= htmlspecialchars($menu[0]) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <div class="portal-sidebar-foot">
    <a href="logout.php" class="portal-nav-foot d-flex align-items-center gap-2 text-decoration-none text-danger fw-500">
      <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
  </div>
</aside>

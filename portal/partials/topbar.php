<?php
// topbar.php — header portal: judul halaman, lonceng notifikasi, akun pelanggan.
// Ambil inisial nama untuk avatar, contoh "Dwi Anjasmoro" -> "DA".
$inisial = '';
foreach (explode(' ', trim($pelanggan['nama'])) as $kata) {
    if ($kata !== '') $inisial .= mb_substr($kata, 0, 1);
}
$inisial = mb_strtoupper(mb_substr($inisial, 0, 2));
?>
<header class="portal-topbar">
  <div class="kiri">
    <button class="topbar-ico btn-sidebar" id="btnSidebar" type="button" aria-label="Menu">
      <i class="bi bi-list"></i>
    </button>
    <h6 class="mb-0 fw-700"><?= htmlspecialchars($judulHalaman ?? 'Portal Pelanggan') ?></h6>
  </div>
  <div class="kanan">
    <!-- Lonceng notifikasi membuka offcanvas (badge tampil bila ada yang belum dibaca) -->
    <button class="topbar-ico" type="button" data-bs-toggle="offcanvas" data-bs-target="#panelNotif" aria-label="Notifikasi">
      <i class="bi bi-bell"></i>
      <?php if ($jumlahNotifikasiBelumDibaca > 0): ?>
        <span class="badge-notif" id="badgeNotif"><?= $jumlahNotifikasiBelumDibaca > 9 ? '9+' : $jumlahNotifikasiBelumDibaca ?></span>
      <?php endif; ?>
    </button>
    <div class="dropdown">
      <button class="topbar-user btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="ava"><?= htmlspecialchars($inisial) ?></span>
        <span class="d-none d-sm-flex flex-column text-start lh-1">
          <span class="fw-600 small"><?= htmlspecialchars($pelanggan['nama']) ?></span>
          <span class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($pelanggan['id']) ?></span>
        </span>
        <i class="bi bi-chevron-down small text-muted ms-1"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
        <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-grid-1x2 me-2"></i>Dashboard</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
      </ul>
    </div>
  </div>
</header>

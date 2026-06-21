<?php
// topbar.php (admin) — header: judul halaman + identitas admin.
$inisial = '';
foreach (explode(' ', trim($admin['nama'])) as $kata) {
    if ($kata !== '') $inisial .= mb_substr($kata, 0, 1);
}
$inisial = mb_strtoupper(mb_substr($inisial, 0, 2));
?>
<header class="portal-topbar">
  <div class="kiri">
    <button class="topbar-ico btn-sidebar" id="btnSidebar" type="button" aria-label="Menu">
      <i class="bi bi-list"></i>
    </button>
    <h6 class="mb-0 fw-700"><?= htmlspecialchars($judulHalaman ?? 'Admin') ?></h6>
  </div>
  <div class="kanan">
    <div class="dropdown">
      <button class="topbar-user btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="ava"><?= htmlspecialchars($inisial) ?></span>
        <span class="d-none d-sm-flex flex-column text-start lh-1">
          <span class="fw-600 small"><?= htmlspecialchars($admin['nama']) ?></span>
          <span class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($admin['peran']) ?></span>
        </span>
        <i class="bi bi-chevron-down small text-muted ms-1"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($admin['email']) ?></span></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="login.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
      </ul>
    </div>
  </div>
</header>

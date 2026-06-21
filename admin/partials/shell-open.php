<?php
// shell-open.php (admin) — membuka kerangka layout admin (sidebar + topbar + konten).
// Halaman wajib memuat admin-config.php serta menetapkan $judulHalaman & $menuAktif.
?>
<body class="portal">
<div class="portal-wrap">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <div class="portal-main">
    <?php include __DIR__ . '/topbar.php'; ?>
    <div class="portal-content">

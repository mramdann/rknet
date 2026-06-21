<?php
// shell-open.php — membuka kerangka layout portal (sidebar + topbar + area konten).
// Halaman wajib sudah memuat config.php & portal-config.php serta menetapkan
// $judulHalaman dan $menuAktif sebelum meng-include file ini.
?>
<body class="portal">
<div class="portal-wrap">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <div class="portal-main">
    <?php include __DIR__ . '/topbar.php'; ?>
    <div class="portal-content">

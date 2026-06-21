<?php
// cek-jangkauan.php — halaman cek ketersediaan jaringan + form pendaftaran berpeta (UI only).
require __DIR__ . '/config.php';
require __DIR__ . '/cek-jangkauan-config.php';

// Kata kunci kota terjangkau dikirim ke JS untuk logika cek (dummy)
$keywordTerjangkau = daftarKeywordTerjangkau($areaTerjangkau);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cek Jangkauan — Starlite Indonesia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Leaflet 1.9.4 -->
  <link href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="assets/css/style.css?v=4" rel="stylesheet">
</head>
<body>
  <!-- Header sederhana -->
  <nav class="navbar bg-white shadow-sm sticky-top py-3">
    <div class="container">
      <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
        <img src="assets/img/logo-starlite.webp" alt="Starlite" height="32">
        <span class="brand-divider" style="background:rgba(6,37,110,.25)"></span>
        <img src="assets/img/logo-weave.webp" alt="Weave" height="26">
      </a>
      <div class="d-flex gap-2">
        <a href="portal/login.php" class="btn btn-outline-primary"><i class="bi bi-person-circle me-1"></i>Login</a>
        <a href="index.php" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Beranda</a>
      </div>
    </div>
  </nav>

  <!-- Hero: cari alamat -->
  <header class="jangkauan-hero text-white text-center">
    <div class="container py-5">
      <h1 class="fw-800 mb-2">Apakah area Anda dalam jangkauan Starlite?</h1>
      <p class="opacity-75 mb-4">Yuk, cek alamat Anda di sini!</p>
      <div class="cari-wrap mx-auto position-relative">
        <div class="input-group input-group-lg shadow">
          <input type="text" id="inputAlamat" class="form-control border-0" placeholder="Masukkan alamat Anda" autocomplete="off">
          <button class="btn btn-st px-4" id="btnCekJangkauan" type="button" disabled>Cek Ketersediaan</button>
        </div>
        <!-- Dropdown hasil autocomplete -->
        <div id="dropdownAlamat" class="dropdown-alamat d-none"></div>
      </div>
    </div>
  </header>

  <!-- Daftar area terjangkau -->
  <section class="section-area py-4">
    <div class="container">
      <h5 class="fw-700 mb-0">Daftar area jangkauan saat ini</h5>
    </div>
  </section>
  <section class="pb-5">
    <div class="container">
      <?php foreach ($areaTerjangkau as $provinsi => $daftarKota): ?>
      <div class="mb-4">
        <h6 class="fw-700 text-uppercase text-st mb-3"><?= htmlspecialchars($provinsi) ?></h6>
        <div class="row g-2">
          <?php foreach ($daftarKota as $kota): ?>
          <div class="col-md-4 col-sm-6">
            <div class="area-item"><i class="bi bi-geo-alt-fill text-st me-2"></i><?= htmlspecialchars($kota) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <hr class="mt-4 mb-0">
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <!-- Data untuk JS -->
  <script>
    window.KEYWORD_TERJANGKAU = <?= json_encode($keywordTerjangkau) ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="assets/js/cek-jangkauan.js?v=1"></script>
</body>
</html>

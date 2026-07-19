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
  <title>Cek Jangkauan — RKnet Indonesia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Leaflet 1.9.4 -->
  <link href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="assets/css/style.css?v=6" rel="stylesheet">
</head>
<body>
  <!-- Header sederhana -->
  <nav class="navbar bg-white shadow-sm sticky-top py-3">
    <div class="container">
      <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
        <img src="assets/img/rknet.jpeg" alt="RKnet" height="32">
        <span class="brand-divider" style="background:rgba(6,37,110,.25)"></span>
        <img src="assets/img/rknet2.jpeg" alt="RWS Solution" height="26">
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
      <h1 class="fw-800 mb-2">Apakah area Anda dalam jangkauan RKnet?</h1>
      <p class="opacity-75 mb-4">Yuk, cek alamat Anda di sini!</p>
      <div class="cari-wrap mx-auto position-relative">
        <div class="input-group input-group-lg shadow">
          <input type="text" id="inputAlamat" class="form-control border-0" placeholder="Masukkan alamat Anda" autocomplete="off">
          <button class="btn btn-rk px-4" id="btnCekJangkauan" type="button" disabled>Cek Ketersediaan</button>
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
        <h6 class="fw-700 text-uppercase text-rk mb-3"><?= htmlspecialchars($provinsi) ?></h6>
        <div class="row g-2">
          <?php foreach ($daftarKota as $kota): ?>
          <div class="col-md-4 col-sm-6">
            <div class="area-item"><i class="bi bi-geo-alt-fill text-rk me-2"></i><?= htmlspecialchars($kota) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <hr class="mt-4 mb-0">
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <!-- Modal hasil cek ketersediaan (isi diatur oleh JS) -->
  <div class="modal fade" id="modalHasil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden text-center">
        <div class="modal-body p-4 p-md-5">
          <div id="hasilIkon" class="hasil-ikon mb-3"></div>
          <h4 id="hasilJudul" class="fw-700 mb-2"></h4>
          <p id="hasilPesan" class="text-muted mb-4"></p>
          <button type="button" class="btn btn-rk btn-lg w-100" id="btnIsiData">Isi Data</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Isi Data: form pendaftaran + peta penanda lokasi -->
  <div class="modal fade" id="modalIsiData" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header rk-modal-head text-white border-0">
          <div>
            <h5 class="modal-title fw-700 mb-0">Silahkan Isi Data Anda</h5>
            <small class="opacity-75">Tim kami akan menghubungi Anda untuk pemasangan.</small>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form id="formIsiData" class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-500 small">Nama Lengkap</label>
              <input type="text" class="form-control" placeholder="Nama Lengkap Kamu" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Email <span class="text-muted">(Opsional)</span></label>
              <input type="email" class="form-control" placeholder="emailkamu@domain.com">
            </div>

            <div class="col-md-12">
              <label class="form-label fw-500 small">Nomor Telepon</label>
              <input type="tel" class="form-control" id="inputTelepon" placeholder="08xxxxxxxxxx" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-500 small">Jadwal Tanggal Pemasangan <span class="text-muted">(Opsional)</span></label>
              <input type="date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Jadwal Jam Pemasangan <span class="text-muted">(Opsional)</span></label>
              <select class="form-select">
                <option value="" selected>Pilih Jam Pemasangan</option>
                <option>08:00 - 10:00</option>
                <option>10:00 - 12:00</option>
                <option>13:00 - 15:00</option>
                <option>15:00 - 17:00</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-500 small">Provinsi</label>
              <select class="form-select" id="selProvinsi"><option value="">Memuat...</option></select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Kota / Kabupaten</label>
              <select class="form-select" id="selKota" disabled><option value="">Pilih Kota Pemasangan</option></select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Kecamatan</label>
              <select class="form-select" id="selKecamatan" disabled><option value="">Pilih Kecamatan Pemasangan</option></select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Desa / Kelurahan</label>
              <select class="form-select" id="selKelurahan" disabled><option value="">Pilih Desa / Kelurahan</option></select>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-500 small">Kode Pos</label>
              <input type="text" class="form-control" inputmode="numeric" placeholder="Kode Pos">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-500 small">RW <span class="text-muted">(Opsional)</span></label>
              <input type="text" class="form-control" inputmode="numeric" placeholder="001">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-500 small">RT <span class="text-muted">(Opsional)</span></label>
              <input type="text" class="form-control" inputmode="numeric" placeholder="001">
            </div>

            <div class="col-12">
              <label class="form-label fw-500 small">Arahkan Pin Lokasi ke Titik Alamat Pemasangan Anda</label>
              <div class="input-group mb-2">
                <input type="text" class="form-control" id="inputCariPeta" placeholder="Cari alamat pemasangan">
                <button class="btn btn-rk" type="button" id="btnCariPeta"><i class="bi bi-search me-1"></i>Cari</button>
              </div>
              <div id="petaPemasangan" class="peta-pemasangan"></div>
              <small class="text-muted" id="koordinatTerpilih">Koordinat: -</small>
            </div>

            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-rk btn-lg">Kirim Pendaftaran</button>
            </div>
          </form>
          <div id="suksesIsiData" class="alert alert-success text-center mb-0 mt-3 d-none">
            <i class="bi bi-check-circle-fill me-1"></i> Terima kasih! Data Anda telah kami terima. Tim RKnet akan segera menghubungi Anda.
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Data untuk JS -->
  <script>
    window.KEYWORD_TERJANGKAU = <?= json_encode($keywordTerjangkau) ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="assets/js/cek-jangkauan.js?v=5"></script>
</body>
</html>

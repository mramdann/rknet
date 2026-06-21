<?php
// login.php — halaman login portal pelanggan (UI only, tombol langsung ke dashboard)
require __DIR__ . '/../config.php';
$judulHalaman = 'Login';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<body class="portal portal-auth">
  <div class="auth-wrap">
    <div class="auth-kartu kartu">
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center gap-2 mb-3">
          <img src="../assets/img/logo-starlite.webp" alt="Starlite" height="34">
          <span class="auth-divider"></span>
          <img src="../assets/img/logo-weave.webp" alt="Weave" height="28">
        </div>
        <h4 class="fw-700 mb-1">Masuk ke Akun Anda</h4>
        <p class="text-muted small mb-0">Portal Pelanggan Starlite Indonesia</p>
      </div>

      <!-- Form login (UI only) -> arahkan ke dashboard -->
      <form action="dashboard.php" method="get" class="auth-form">
        <div class="mb-3">
          <label class="form-label fw-500">Nomor Handphone</label>
          <div class="input-group input-group-lg">
            <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
            <input type="text" class="form-control" placeholder="08xxxxxxxxxx" value="0811-7891-2233">
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label fw-500">PIN / Password</label>
          <div class="input-group input-group-lg">
            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" placeholder="Masukkan PIN" value="123456">
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="ingatSaya" checked>
            <label class="form-check-label small" for="ingatSaya">Ingat saya</label>
          </div>
          <a href="#" class="small text-st text-decoration-none">Lupa PIN?</a>
        </div>
        <button type="submit" class="btn btn-st w-100 btn-lg">Login</button>
      </form>

      <p class="text-center small text-muted mt-4 mb-0">
        Belum punya akun? <a href="../index.php#paket" class="text-st text-decoration-none fw-500">Berlangganan</a>
      </p>
    </div>

    <p class="text-center small text-muted mt-3">
      PT Integrasi Jaringan Ekosistem &middot; Layanan Pelanggan +62811789111
    </p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

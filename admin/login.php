<?php
// login.php (admin) — halaman login admin (UI only, langsung ke dashboard).
$judulHalaman = 'Login Admin';
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
          <span class="badge bg-primary-subtle text-primary fw-600">ADMIN</span>
        </div>
        <h4 class="fw-700 mb-1">Panel Admin Starlite</h4>
        <p class="text-muted small mb-0">Masuk untuk mengelola layanan</p>
      </div>

      <form action="dashboard.php" method="get" class="auth-form">
        <div class="mb-3">
          <label class="form-label fw-500">Username</label>
          <div class="input-group input-group-lg">
            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" placeholder="Username admin" value="admin">
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-500">Password</label>
          <div class="input-group input-group-lg">
            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" placeholder="Masukkan password" value="admin123">
          </div>
        </div>
        <button type="submit" class="btn btn-st w-100 btn-lg">Masuk</button>
      </form>

      <p class="text-center small text-muted mt-4 mb-0">
        <a href="../portal/login.php" class="text-st text-decoration-none">&larr; Login Pelanggan</a>
      </p>
    </div>
    <p class="text-center small text-muted mt-3">PT Integrasi Jaringan Ekosistem</p>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// login.php — login portal pelanggan nyata: verifikasi email + password ke DB.
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';

$pesanError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $sandi = $_POST['kata_sandi'] ?? '';
    $row = kueriSatu("SELECT id, kata_sandi FROM pelanggan WHERE email = ?", [$email]);
    if ($row && password_verify($sandi, $row['kata_sandi'])) {
        loginPelanggan($row['id']);
        header('Location: dashboard.php');
        exit;
    }
    $pesanError = 'Email atau kata sandi salah.';
}
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
          <img src="../assets/img/logo-starlite.webp" alt="RKnet" height="34">
          <span class="auth-divider"></span>
          <img src="../assets/img/logo-weave.webp" alt="Weave" height="28">
        </div>
        <h4 class="fw-700 mb-1">Masuk ke Akun Anda</h4>
        <p class="text-muted small mb-0">Portal Pelanggan RKnet Indonesia</p>
      </div>

      <?php if ($pesanError !== ''): ?>
        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($pesanError) ?></div>
      <?php endif; ?>

      <form action="login.php" method="post" class="auth-form">
        <div class="mb-3">
          <label class="form-label fw-500">Email</label>
          <div class="input-group input-group-lg">
            <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label fw-500">Password</label>
          <div class="input-group input-group-lg">
            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
            <input type="password" name="kata_sandi" class="form-control" placeholder="Masukkan password" required>
          </div>
        </div>
        <button type="submit" class="btn btn-rk w-100 btn-lg mt-3">Login</button>
      </form>

      <p class="text-center small text-muted mt-3 mb-1">Demo: <code>dwi.anjasmoro@gmail.com</code> / <code>pelanggan123</code></p>
      <p class="text-center small text-muted mt-2 mb-1">
        Belum punya akun? <a href="../index.php#paket" class="text-rk text-decoration-none fw-500">Berlangganan</a>
      </p>
      <p class="text-center small mb-0">
        <a href="../admin/login.php" class="text-muted text-decoration-none"><i class="bi bi-shield-lock me-1"></i>Masuk sebagai Admin</a>
      </p>
    </div>

    <p class="text-center small text-muted mt-3">
      PT Integrasi Jaringan Ekosistem &middot; Layanan Pelanggan +62811789111
    </p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

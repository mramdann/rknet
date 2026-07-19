<?php
// login.php (admin) — login nyata: verifikasi email + password ke DB.
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';

$pesanError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $sandi = $_POST['kata_sandi'] ?? '';
    $row = kueriSatu("SELECT id, kata_sandi FROM admin WHERE email = ?", [$email]);
    if ($row && password_verify($sandi, $row['kata_sandi'])) {
        loginAdmin((int) $row['id']);
        header('Location: dashboard.php');
        exit;
    }
    $pesanError = 'Email atau kata sandi salah.';
}
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
          <img src="../assets/img/rknet.jpeg" alt="RKnet" height="34">
          <span class="badge bg-primary-subtle text-primary fw-600">ADMIN</span>
        </div>
        <h4 class="fw-700 mb-1">Panel Admin RKnet</h4>
        <p class="text-muted small mb-0">Masuk untuk mengelola layanan</p>
      </div>

      <?php if ($pesanError !== ''): ?>
        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($pesanError) ?></div>
      <?php endif; ?>

      <form action="login.php" method="post" class="auth-form">
        <div class="mb-3">
          <label class="form-label fw-500">Email</label>
          <div class="input-group input-group-lg">
            <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="admin@rknet.id" required>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-500">Password</label>
          <div class="input-group input-group-lg">
            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
            <input type="password" name="kata_sandi" class="form-control" placeholder="Masukkan password" required>
          </div>
        </div>
        <button type="submit" class="btn btn-rk w-100 btn-lg">Masuk</button>
      </form>

      <p class="text-center small text-muted mt-3 mb-0">Demo: <code>admin@rknet.id</code> / <code>admin123</code></p>
      <p class="text-center small text-muted mt-2 mb-0">
        <a href="../portal/login.php" class="text-rk text-decoration-none">&larr; Login Pelanggan</a>
      </p>
    </div>
    <p class="text-center small text-muted mt-3">PT Integrasi Jaringan Ekosistem</p>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

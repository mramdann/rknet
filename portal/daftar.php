<?php
// daftar.php - formulir pendaftaran publik tanpa guard portal.
require __DIR__ . '/../db.php';
require __DIR__ . '/../aksi.php';
require __DIR__ . '/../helpers.php';

$paketAktif = kueri("SELECT id, nama, kecepatan, harga FROM paket WHERE status = 'aktif' ORDER BY id");
$judulHalaman = 'Daftar Akun';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<body class="portal portal-auth">
  <div class="auth-wrap">
    <div class="auth-kartu auth-kartu-daftar kartu">
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center gap-2 mb-3">
          <img src="../assets/img/rknet.jpeg" alt="RKnet" class="auth-logo-rk">
          <span class="auth-divider"></span>
          <img src="../assets/img/rknet2.jpeg" alt="RWS Solution" class="auth-logo-rws">
        </div>
        <h4 class="fw-700 mb-1">Daftar Akun Pelanggan</h4>
        <p class="text-muted small mb-0">Akun dapat digunakan setelah disetujui admin RKnet.</p>
      </div>

      <?php tampilFlash(); ?>
      <?php if (!$paketAktif): ?>
        <div class="alert alert-warning small">Belum ada paket aktif. Pendaftaran sementara tidak tersedia.</div>
      <?php endif; ?>

      <form action="aksi-daftar.php" method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
        <div class="col-md-6">
          <label class="form-label fw-500" for="namaDaftar">Nama lengkap</label>
          <input type="text" name="nama" class="form-control" id="namaDaftar" maxlength="100" autocomplete="name" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-500" for="emailDaftar">Email</label>
          <input type="email" name="email" class="form-control" id="emailDaftar" maxlength="150" autocomplete="email" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-500" for="hpDaftar">No. handphone</label>
          <input type="tel" name="hp" class="form-control" id="hpDaftar" maxlength="30" autocomplete="tel" placeholder="08xxxxxxxxxx" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-500" for="paketDaftar">Paket</label>
          <select name="paket_id" class="form-select" id="paketDaftar" required <?= !$paketAktif ? 'disabled' : '' ?>>
            <option value="">Pilih paket aktif</option>
            <?php foreach ($paketAktif as $paket): ?>
              <option value="<?= (int) $paket['id'] ?>"><?= htmlspecialchars($paket['nama']) ?> - <?= htmlspecialchars($paket['kecepatan']) ?> (<?= formatRupiah((int) $paket['harga']) ?>/bulan)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-500" for="alamatDaftar">Alamat pemasangan</label>
          <textarea name="alamat" class="form-control" id="alamatDaftar" rows="3" maxlength="255" autocomplete="street-address" required></textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-500" for="sandiDaftar">Password</label>
          <input type="password" name="kata_sandi" class="form-control" id="sandiDaftar" minlength="8" maxlength="128" autocomplete="new-password" required>
          <div class="form-text">Minimal 8 karakter.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-500" for="konfirmasiDaftar">Konfirmasi password</label>
          <input type="password" name="konfirmasi_sandi" class="form-control" id="konfirmasiDaftar" minlength="8" maxlength="128" autocomplete="new-password" required>
        </div>
        <div class="col-12 d-grid mt-4">
          <button type="submit" class="btn btn-rk btn-lg" <?= !$paketAktif ? 'disabled' : '' ?>>Kirim Pendaftaran</button>
        </div>
      </form>

      <p class="text-center small text-muted mt-3 mb-0">Sudah punya akun? <a href="login.php" class="text-rk text-decoration-none fw-500">Login</a></p>
    </div>
    <p class="text-center small text-muted mt-3">PT Integrasi Jaringan Ekosistem &middot; Layanan Pelanggan +62811789111</p>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

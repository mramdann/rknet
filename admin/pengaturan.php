<?php
// pengaturan.php (admin) — profil admin, ubah password, info situs (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Pengaturan';
$menuAktif = 'pengaturan';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="mb-4">
    <h5 class="fw-700 mb-1">Pengaturan</h5>
    <p class="text-muted small mb-0">Kelola profil admin & informasi situs.</p>
  </div>

  <div class="row g-4">
    <!-- Profil admin -->
    <div class="col-lg-7">
      <div class="kartu kartu-pad h-100">
        <h6 class="fw-700 mb-3"><i class="bi bi-person-vcard text-rk me-2"></i>Profil Admin</h6>
        <form action="aksi-pengaturan.php" method="post" class="row g-3">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="profil">
          <div class="col-md-6">
            <label class="form-label fw-500 small">Nama</label>
            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($admin['nama']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Peran</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($admin['peran']) ?>" readonly>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>">
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-rk"><i class="bi bi-check2 me-1"></i>Simpan Profil</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Ubah password -->
    <div class="col-lg-5">
      <div class="kartu kartu-pad h-100">
        <h6 class="fw-700 mb-3"><i class="bi bi-shield-lock text-rk me-2"></i>Ubah Password</h6>
        <form action="aksi-pengaturan.php" method="post" class="row g-3">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="password">
          <div class="col-12">
            <label class="form-label fw-500 small">Password Saat Ini</label>
            <input type="password" name="lama" class="form-control" placeholder="Masukkan password lama" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Password Baru</label>
            <input type="password" name="baru" class="form-control" placeholder="Minimal 6 karakter" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Konfirmasi Password Baru</label>
            <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password baru" required>
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-key me-1"></i>Perbarui Password</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Info situs -->
    <div class="col-12">
      <div class="kartu kartu-pad">
        <h6 class="fw-700 mb-3"><i class="bi bi-globe text-rk me-2"></i>Informasi Situs</h6>
        <form action="aksi-pengaturan.php" method="post" class="row g-3">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="situs">
          <div class="col-md-6">
            <label class="form-label fw-500 small">Nama Situs</label>
            <input type="text" name="nama_situs" class="form-control" value="<?= htmlspecialchars($pengaturan['namaSitus']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Email CS</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($pengaturan['email']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Telepon</label>
            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($pengaturan['telepon']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Alamat</label>
            <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($pengaturan['alamat']) ?>">
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-rk"><i class="bi bi-check2 me-1"></i>Simpan Pengaturan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

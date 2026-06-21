<?php
// profil.php — halaman profil pelanggan: ubah data akun & ganti password (UI only).
require __DIR__ . '/../config.php';
require __DIR__ . '/../portal-config.php';
$judulHalaman = 'Profil';
$menuAktif = 'profil';
$badge = badgeStatus($paketAktif['status']);

// Inisial nama untuk avatar, contoh "Dwi Anjasmoro" -> "DA"
$inisial = '';
foreach (explode(' ', trim($pelanggan['nama'])) as $kata) {
    if ($kata !== '') $inisial .= mb_substr($kata, 0, 1);
}
$inisial = mb_strtoupper(mb_substr($inisial, 0, 2));
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="mb-4">
    <h5 class="fw-700 mb-1">Profil Saya</h5>
    <p class="text-muted small mb-0">Kelola informasi akun & keamanan Anda.</p>
  </div>

  <!-- Header profil: avatar + identitas -->
  <div class="kartu kartu-pad mb-4 profil-header">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <span class="profil-ava"><?= htmlspecialchars($inisial) ?></span>
      <div class="flex-grow-1">
        <div class="fw-700 fs-5"><?= htmlspecialchars($pelanggan['nama']) ?></div>
        <div class="text-muted small"><?= htmlspecialchars($pelanggan['id']) ?> &middot; <?= htmlspecialchars($pelanggan['email']) ?></div>
      </div>
      <span class="badge <?= $badge['kelas'] ?>">Pelanggan <?= $badge['label'] ?></span>
    </div>
  </div>

  <div class="row g-4">
    <!-- Informasi Akun (editable) -->
    <div class="col-lg-7">
      <div class="kartu kartu-pad h-100">
        <h6 class="fw-700 mb-3"><i class="bi bi-person-vcard text-st me-2"></i>Informasi Akun</h6>
        <!-- Form UI only: kembali ke halaman ini setelah "simpan" -->
        <form action="profil.php" method="get" class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-500 small">Nama Lengkap</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($pelanggan['nama']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">ID Pelanggan</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($pelanggan['id']) ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($pelanggan['email']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">No. Handphone</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($pelanggan['hp']) ?>">
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Alamat</label>
            <textarea class="form-control" rows="2"><?= htmlspecialchars($pelanggan['alamat']) ?></textarea>
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-st"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Keamanan: ganti password -->
    <div class="col-lg-5">
      <div class="kartu kartu-pad h-100">
        <h6 class="fw-700 mb-3"><i class="bi bi-shield-lock text-st me-2"></i>Keamanan</h6>
        <form action="profil.php" method="get" class="row g-3">
          <div class="col-12">
            <label class="form-label fw-500 small">Password Saat Ini</label>
            <input type="password" class="form-control" placeholder="Masukkan password lama">
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Password Baru</label>
            <input type="password" class="form-control" placeholder="Minimal 6 karakter">
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" placeholder="Ulangi password baru">
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-key me-1"></i>Perbarui Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

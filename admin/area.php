<?php
// area.php (admin) — daftar area cakupan + tambah/edit area (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Area';
$menuAktif = 'area';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Area Cakupan</h5>
      <p class="text-muted small mb-0"><?= count($daftarArea) ?> area terdaftar.</p>
    </div>
    <button class="btn btn-st" type="button" data-bs-toggle="modal" data-bs-target="#modalEditArea"
      data-mode="tambah"><i class="bi bi-plus-lg me-1"></i>Tambah Area</button>
  </div>

  <div class="row g-3">
    <?php foreach ($daftarArea as $a): $b = badgeStatus($a['status']); ?>
    <div class="col-md-6 col-xl-4">
      <div class="kartu kartu-pad h-100">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge bg-primary-subtle text-primary"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($a['kota']) ?></span>
          <span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span>
        </div>
        <h6 class="fw-700 mb-1"><?= htmlspecialchars($a['nama']) ?></h6>
        <div class="d-flex align-items-center gap-2 text-muted small mb-3">
          <i class="bi bi-people-fill text-st"></i> <?= number_format($a['jumlahPelanggan'], 0, ',', '.') ?> pelanggan
        </div>
        <button type="button" class="btn btn-outline-primary w-100 btn-edit-area"
          data-mode="edit"
          data-nama="<?= htmlspecialchars($a['nama']) ?>"
          data-kota="<?= htmlspecialchars($a['kota']) ?>"
          data-status="<?= htmlspecialchars($a['status']) ?>"
          data-bs-toggle="modal" data-bs-target="#modalEditArea">
          <i class="bi bi-pencil me-1"></i>Edit Area
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Modal tambah/edit area (UI only) -->
  <div class="modal fade" id="modalEditArea" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header st-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0" id="judulModalArea">Edit Area</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form id="formArea" class="row g-3">
            <div class="col-12">
              <label class="form-label fw-500 small">Nama Area</label>
              <input type="text" class="form-control" id="areaNama" placeholder="cth. Cibinong" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Kota</label>
              <input type="text" class="form-control" id="areaKota" placeholder="cth. Bogor" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Status</label>
              <select class="form-select" id="areaStatus">
                <option value="tercakup">Tercakup</option>
                <option value="segera">Segera</option>
              </select>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-st btn-lg">Simpan Area</button>
            </div>
          </form>
          <div id="suksesArea" class="alert alert-success text-center mb-0 mt-3 d-none">
            <i class="bi bi-check-circle-fill me-1"></i> Area berhasil disimpan.
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Isi modal sesuai mode (tambah / edit)
    document.getElementById('modalEditArea').addEventListener('show.bs.modal', (e) => {
      const d = e.relatedTarget.dataset;
      const edit = d.mode === 'edit';
      document.getElementById('judulModalArea').textContent = edit ? 'Edit Area' : 'Tambah Area';
      document.getElementById('areaNama').value = edit ? d.nama : '';
      document.getElementById('areaKota').value = edit ? d.kota : '';
      document.getElementById('areaStatus').value = edit ? d.status : 'tercakup';
      document.getElementById('formArea').classList.remove('d-none');
      document.getElementById('suksesArea').classList.add('d-none');
    });

    // Simpan (UI only)
    document.getElementById('formArea').addEventListener('submit', (e) => {
      e.preventDefault();
      document.getElementById('formArea').classList.add('d-none');
      document.getElementById('suksesArea').classList.remove('d-none');
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

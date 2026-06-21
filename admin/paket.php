<?php
// paket.php (admin) — daftar & edit paket (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Paket';
$menuAktif = 'paket';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Manajemen Paket</h5>
      <p class="text-muted small mb-0"><?= count($daftarPaket) ?> paket aktif.</p>
    </div>
    <button class="btn btn-st" type="button" data-bs-toggle="modal" data-bs-target="#modalEditPaket"
      data-mode="tambah"><i class="bi bi-plus-lg me-1"></i>Tambah Paket</button>
  </div>

  <div class="row g-3">
    <?php foreach ($daftarPaket as $p): $b = badgeStatus($p['status']); ?>
    <div class="col-md-6 col-xl-4">
      <div class="kartu kartu-pad h-100">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($p['kecepatan']) ?></span>
          <span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span>
        </div>
        <h6 class="fw-700 mb-1"><?= htmlspecialchars($p['nama']) ?></h6>
        <div class="display-6 fw-800 text-st mb-2"><?= formatRupiah($p['harga']) ?><small class="fs-6 text-muted fw-400">/bln</small></div>
        <div class="d-flex align-items-center gap-2 text-muted small mb-3">
          <i class="bi bi-people-fill text-st"></i> <?= number_format($p['jumlahPelanggan'], 0, ',', '.') ?> pelanggan aktif
        </div>
        <button type="button" class="btn btn-outline-primary w-100 btn-edit-paket"
          data-mode="edit"
          data-nama="<?= htmlspecialchars($p['nama']) ?>"
          data-kecepatan="<?= htmlspecialchars($p['kecepatan']) ?>"
          data-harga="<?= $p['harga'] ?>"
          data-bs-toggle="modal" data-bs-target="#modalEditPaket">
          <i class="bi bi-pencil me-1"></i>Edit Paket
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Modal tambah/edit paket (UI only) -->
  <div class="modal fade" id="modalEditPaket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header st-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0" id="judulModalPaket">Edit Paket</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form id="formPaket" class="row g-3">
            <div class="col-12">
              <label class="form-label fw-500 small">Nama Paket</label>
              <input type="text" class="form-control" id="paketNama" placeholder="Paket ... Mbps Starlite" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Kecepatan</label>
              <input type="text" class="form-control" id="paketKecepatan" placeholder="100 Mbps" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Harga / bulan (Rp)</label>
              <input type="number" class="form-control" id="paketHarga" placeholder="199000" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Status</label>
              <select class="form-select"><option>Aktif</option><option>Nonaktif</option></select>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-st btn-lg">Simpan Paket</button>
            </div>
          </form>
          <div id="suksesPaket" class="alert alert-success text-center mb-0 mt-3 d-none">
            <i class="bi bi-check-circle-fill me-1"></i> Paket berhasil disimpan.
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Isi modal sesuai mode (tambah / edit)
    document.getElementById('modalEditPaket').addEventListener('show.bs.modal', (e) => {
      const d = e.relatedTarget.dataset;
      const edit = d.mode === 'edit';
      document.getElementById('judulModalPaket').textContent = edit ? 'Edit Paket' : 'Tambah Paket';
      document.getElementById('paketNama').value = edit ? d.nama : '';
      document.getElementById('paketKecepatan').value = edit ? d.kecepatan : '';
      document.getElementById('paketHarga').value = edit ? d.harga : '';
      // Reset tampilan sukses
      document.getElementById('formPaket').classList.remove('d-none');
      document.getElementById('suksesPaket').classList.add('d-none');
    });

    // Simpan (UI only)
    document.getElementById('formPaket').addEventListener('submit', (e) => {
      e.preventDefault();
      document.getElementById('formPaket').classList.add('d-none');
      document.getElementById('suksesPaket').classList.remove('d-none');
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

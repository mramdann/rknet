<?php
// rekening.php (admin) - daftar dan CRUD metode pembayaran.
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Rekening & QRIS';
$menuAktif = 'rekening';
$daftarRekening = kueri(
    "SELECT id, jenis, nama_bank AS namaBank, nomor_rekening AS nomorRekening, atas_nama AS atasNama, status
     FROM rekening_bank ORDER BY id DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Rekening & QRIS</h5>
      <p class="text-muted small mb-0"><?= count($daftarRekening) ?> metode pembayaran.</p>
    </div>
    <button class="btn btn-rk" type="button" data-bs-toggle="modal" data-bs-target="#modalRekening" data-mode="tambah">
      <i class="bi bi-plus-lg me-1"></i>Tambah Metode
    </button>
  </div>

  <div class="row g-3">
    <?php foreach ($daftarRekening as $rekening): $badge = badgeStatus($rekening['status']); ?>
    <div class="col-md-6 col-xl-4">
      <div class="kartu kartu-pad h-100">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <span class="badge bg-primary-subtle text-primary"><i class="bi <?= $rekening['jenis'] === 'qris' ? 'bi-qr-code' : 'bi-bank' ?> me-1"></i><?= htmlspecialchars($rekening['namaBank']) ?></span>
          <span class="badge <?= $badge['kelas'] ?>"><?= $badge['label'] ?></span>
        </div>
        <?php if ($rekening['jenis'] === 'qris'): ?>
          <div class="text-center mb-3">
            <img src="../assets/img/qris.jpeg" alt="Kode QRIS RKnet" class="img-fluid rounded-3 border" style="width:100%;max-width:260px;height:auto">
          </div>
        <?php else: ?>
          <div class="text-muted small mb-1">Nomor Rekening</div>
          <div class="fw-800 fs-5 text-rk mb-3"><?= htmlspecialchars($rekening['nomorRekening']) ?></div>
        <?php endif; ?>
        <div class="text-muted small mb-1">Atas Nama</div>
        <div class="fw-600 mb-3"><?= htmlspecialchars($rekening['atasNama']) ?></div>
        <button type="button" class="btn btn-outline-primary w-100"
          data-mode="edit"
          data-id="<?= (int) $rekening['id'] ?>"
          data-jenis="<?= htmlspecialchars($rekening['jenis']) ?>"
          data-nama-bank="<?= htmlspecialchars($rekening['namaBank']) ?>"
          data-nomor-rekening="<?= htmlspecialchars($rekening['nomorRekening']) ?>"
          data-atas-nama="<?= htmlspecialchars($rekening['atasNama']) ?>"
          data-status="<?= htmlspecialchars($rekening['status']) ?>"
          data-bs-toggle="modal" data-bs-target="#modalRekening">
          <i class="bi bi-pencil me-1"></i>Edit Metode
        </button>
        <form method="post" action="aksi-rekening.php" class="mt-2" onsubmit="return confirm('Hapus metode pembayaran ini? Metode yang pernah dipakai akan dinonaktifkan.')">
          <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" value="<?= (int) $rekening['id'] ?>">
          <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$daftarRekening): ?>
    <div class="col-12"><div class="alert alert-light border text-muted mb-0">Belum ada metode pembayaran.</div></div>
    <?php endif; ?>
  </div>

  <div class="modal fade" id="modalRekening" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header rk-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0" id="judulModalRekening">Tambah Metode Pembayaran</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form method="post" action="aksi-rekening.php" class="row g-3">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" id="rekeningAksi" value="tambah">
            <input type="hidden" name="id" id="rekeningId" value="">
            <div class="col-12">
              <label class="form-label fw-500 small" for="rekeningJenis">Jenis</label>
              <select name="jenis" class="form-select" id="rekeningJenis" required>
                <option value="bank">Bank</option>
                <option value="qris">QRIS</option>
              </select>
            </div>
            <div class="col-12" id="grupNamaBank">
              <label class="form-label fw-500 small" for="rekeningNamaBank">Nama Bank</label>
              <input type="text" name="nama_bank" class="form-control" id="rekeningNamaBank" maxlength="100" required>
            </div>
            <div class="col-12" id="grupNomorRekening">
              <label class="form-label fw-500 small" for="rekeningNomor">Nomor Rekening</label>
              <input type="text" name="nomor_rekening" class="form-control" id="rekeningNomor" maxlength="60" required>
            </div>
            <div class="col-12 text-center d-none" id="pratinjauQris">
              <img src="../assets/img/qris.jpeg" alt="Pratinjau kode QRIS RKnet" class="img-fluid rounded-3 border" style="width:100%;max-width:280px;height:auto">
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small" for="rekeningAtasNama">Atas Nama</label>
              <input type="text" name="atas_nama" class="form-control" id="rekeningAtasNama" maxlength="120" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small" for="rekeningStatus">Status</label>
              <select name="status" class="form-select" id="rekeningStatus" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-rk btn-lg">Simpan Metode Pembayaran</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    const pilihanJenis = document.getElementById('rekeningJenis');
    const namaBank = document.getElementById('rekeningNamaBank');
    const nomorRekening = document.getElementById('rekeningNomor');
    const ubahTampilanJenis = () => {
      const qris = pilihanJenis.value === 'qris';
      document.getElementById('grupNamaBank').classList.toggle('d-none', qris);
      document.getElementById('grupNomorRekening').classList.toggle('d-none', qris);
      document.getElementById('pratinjauQris').classList.toggle('d-none', !qris);
      namaBank.required = !qris;
      nomorRekening.required = !qris;
      namaBank.disabled = qris;
      nomorRekening.disabled = qris;
    };

    pilihanJenis.addEventListener('change', ubahTampilanJenis);
    document.getElementById('modalRekening').addEventListener('show.bs.modal', (event) => {
      const data = event.relatedTarget.dataset;
      const edit = data.mode === 'edit';
      document.getElementById('judulModalRekening').textContent = edit ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran';
      document.getElementById('rekeningAksi').value = edit ? 'edit' : 'tambah';
      document.getElementById('rekeningId').value = edit ? data.id : '';
      pilihanJenis.value = edit ? data.jenis : 'bank';
      namaBank.value = edit ? data.namaBank : '';
      nomorRekening.value = edit ? data.nomorRekening : '';
      document.getElementById('rekeningAtasNama').value = edit ? data.atasNama : '';
      document.getElementById('rekeningStatus').value = edit ? data.status : 'aktif';
      ubahTampilanJenis();
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

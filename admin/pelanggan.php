<?php
// pelanggan.php (admin) — daftar & detail pelanggan (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Pelanggan';
$menuAktif = 'pelanggan';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Manajemen Pelanggan</h5>
      <p class="text-muted small mb-0">Total <?= count($daftarPelanggan) ?> pelanggan terdaftar.</p>
    </div>
    <div class="input-group cari-pelanggan">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" id="cariPelanggan" placeholder="Cari nama / ID pelanggan...">
    </div>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>Pelanggan</th><th>Kontak</th><th>Paket</th><th>Bergabung</th><th>Status</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody id="tabelPelanggan">
          <?php foreach ($daftarPelanggan as $p): $b = badgeStatus($p['status']); ?>
          <tr data-cari="<?= htmlspecialchars(mb_strtolower($p['nama'] . ' ' . $p['id'])) ?>">
            <td>
              <div class="fw-600"><?= htmlspecialchars($p['nama']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($p['id']) ?></div>
            </td>
            <td>
              <div class="small"><?= htmlspecialchars($p['email']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($p['hp']) ?></div>
            </td>
            <td><?= htmlspecialchars($p['paket']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($p['bergabung']) ?></td>
            <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-light btn-detail"
                data-nama="<?= htmlspecialchars($p['nama']) ?>"
                data-id="<?= htmlspecialchars($p['id']) ?>"
                data-email="<?= htmlspecialchars($p['email']) ?>"
                data-hp="<?= htmlspecialchars($p['hp']) ?>"
                data-paket="<?= htmlspecialchars($p['paket']) ?>"
                data-bergabung="<?= htmlspecialchars($p['bergabung']) ?>"
                data-status="<?= htmlspecialchars($b['label']) ?>"
                data-alamat="<?= htmlspecialchars($p['alamat'] ?? '') ?>"
                data-bs-toggle="modal" data-bs-target="#modalDetailPelanggan">
                <i class="bi bi-eye"></i> Detail
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="text-muted small text-center mt-3 mb-0 d-none" id="kosongPelanggan">Tidak ada pelanggan yang cocok.</p>
    </div>
  </div>

  <!-- Modal detail pelanggan (diisi oleh JS) -->
  <div class="modal fade" id="modalDetailPelanggan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header st-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0">Detail Pelanggan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form method="post" action="aksi-pelanggan.php" class="row g-3">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="plId">
            <div class="col-12">
              <label class="form-label fw-500 small">ID Pelanggan</label>
              <input type="text" class="form-control" id="plIdTampil" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Nama</label>
              <input type="text" name="nama" class="form-control" id="plNama" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">Email</label>
              <input type="email" name="email" class="form-control" id="plEmail" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">No. HP</label>
              <input type="text" name="hp" class="form-control" id="plHp" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Alamat</label>
              <input type="text" name="alamat" class="form-control" id="plAlamat">
            </div>
            <div class="col-12 d-grid">
              <button type="submit" class="btn btn-st"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
            </div>
          </form>
          <form method="post" action="aksi-pelanggan.php" class="mt-2">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" value="toggle">
            <input type="hidden" name="id" id="plIdToggle">
            <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-toggle-on me-1"></i>Ubah Status Aktif/Nonaktif</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Pencarian pelanggan (filter baris tabel)
    const inputCari = document.getElementById('cariPelanggan');
    inputCari.addEventListener('input', () => {
      const kunci = inputCari.value.trim().toLowerCase();
      let terlihat = 0;
      document.querySelectorAll('#tabelPelanggan tr').forEach(tr => {
        const cocok = tr.dataset.cari.includes(kunci);
        tr.classList.toggle('d-none', !cocok);
        if (cocok) terlihat++;
      });
      document.getElementById('kosongPelanggan').classList.toggle('d-none', terlihat > 0);
    });

    // Isi form edit dari atribut tombol yang diklik
    document.getElementById('modalDetailPelanggan').addEventListener('show.bs.modal', (e) => {
      const d = e.relatedTarget.dataset;
      document.getElementById('plId').value = d.id;
      document.getElementById('plIdTampil').value = d.id;
      document.getElementById('plNama').value = d.nama;
      document.getElementById('plEmail').value = d.email;
      document.getElementById('plHp').value = d.hp;
      document.getElementById('plAlamat').value = d.alamat || '';
      document.getElementById('plIdToggle').value = d.id;
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

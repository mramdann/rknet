<?php
// notifikasi.php (admin) — daftar notifikasi + filter terkirim/draft + tulis baru (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Notifikasi';
$menuAktif = 'notifikasi';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Notifikasi</h5>
      <p class="text-muted small mb-0">Kelola pengumuman & broadcast ke pelanggan.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <div class="btn-group" role="group" id="filterNotif">
        <button type="button" class="btn btn-sm btn-st" data-filter="semua">Semua</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="terkirim">Terkirim</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="draft">Draft</button>
      </div>
      <button class="btn btn-st" type="button" data-bs-toggle="modal" data-bs-target="#modalTulisNotif">
        <i class="bi bi-plus-lg me-1"></i>Tulis Notifikasi</button>
    </div>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>Judul</th><th>Target</th><th>Tanggal</th><th>Status</th></tr>
        </thead>
        <tbody id="tabelNotif">
          <?php foreach ($daftarNotifikasi as $n): $b = badgeStatus($n['status']); ?>
          <tr data-status="<?= htmlspecialchars($n['status']) ?>">
            <td>
              <div class="fw-600"><?= htmlspecialchars($n['judul']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= htmlspecialchars($n['isi']) ?></div>
            </td>
            <td><?= htmlspecialchars($n['target']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($n['tanggal']) ?></td>
            <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="text-muted small text-center mt-3 mb-0 d-none" id="kosongNotif">Tidak ada notifikasi pada filter ini.</p>
    </div>
  </div>

  <!-- Modal tulis notifikasi (UI only) -->
  <div class="modal fade" id="modalTulisNotif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header st-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0">Tulis Notifikasi</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form id="formNotif" class="row g-3">
            <div class="col-12">
              <label class="form-label fw-500 small">Judul</label>
              <input type="text" class="form-control" placeholder="Judul notifikasi" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Target</label>
              <select class="form-select">
                <option>Semua pelanggan</option>
                <option>Pelanggan aktif</option>
                <option>Pelanggan baru</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Isi Pesan</label>
              <textarea class="form-control" rows="3" placeholder="Tulis isi notifikasi..." required></textarea>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-st btn-lg">Kirim Notifikasi</button>
            </div>
          </form>
          <div id="suksesNotif" class="alert alert-success text-center mb-0 mt-3 d-none">
            <i class="bi bi-check-circle-fill me-1"></i> Notifikasi berhasil dikirim.
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Filter berdasarkan status
    const tombolFilter = document.querySelectorAll('#filterNotif button');
    tombolFilter.forEach(btn => btn.addEventListener('click', () => {
      tombolFilter.forEach(b => { b.classList.remove('btn-st'); b.classList.add('btn-outline-primary'); });
      btn.classList.add('btn-st'); btn.classList.remove('btn-outline-primary');
      const filter = btn.dataset.filter;
      let terlihat = 0;
      document.querySelectorAll('#tabelNotif tr').forEach(tr => {
        const cocok = filter === 'semua' || tr.dataset.status === filter;
        tr.classList.toggle('d-none', !cocok);
        if (cocok) terlihat++;
      });
      document.getElementById('kosongNotif').classList.toggle('d-none', terlihat > 0);
    }));

    // Tulis notifikasi (UI only)
    document.getElementById('formNotif').addEventListener('submit', (e) => {
      e.preventDefault();
      document.getElementById('formNotif').classList.add('d-none');
      document.getElementById('suksesNotif').classList.remove('d-none');
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

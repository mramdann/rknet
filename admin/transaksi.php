<?php
// transaksi.php (admin) — daftar tagihan + filter status + tandai lunas (UI only).
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Transaksi';
$menuAktif = 'transaksi';

// Kelas & label badge lunas dipakai JS saat "Tandai Lunas"
$badgeLunas = badgeStatus('lunas');
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Transaksi & Tagihan</h5>
      <p class="text-muted small mb-0">Kelola status pembayaran pelanggan.</p>
    </div>
    <!-- Filter status -->
    <div class="btn-group" role="group" id="filterStatus">
      <button type="button" class="btn btn-sm btn-st" data-filter="semua">Semua</button>
      <button type="button" class="btn btn-sm btn-outline-primary" data-filter="lunas">Lunas</button>
      <button type="button" class="btn btn-sm btn-outline-primary" data-filter="menunggu">Menunggu</button>
    </div>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>No. Invoice</th><th>Pelanggan</th><th>Paket</th><th>Tanggal</th><th>Nominal</th><th>Status</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody id="tabelTagihan">
          <?php foreach ($daftarTagihan as $t): $b = badgeStatus($t['status']); ?>
          <tr data-status="<?= htmlspecialchars($t['status']) ?>">
            <td class="fw-500"><?= htmlspecialchars($t['noInvoice']) ?></td>
            <td><?= htmlspecialchars($t['pelanggan']) ?></td>
            <td><?= htmlspecialchars($t['paket']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($t['tanggal']) ?></td>
            <td class="fw-600"><?= formatRupiah($t['harga']) ?></td>
            <td class="kolom-status"><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
            <td class="text-end kolom-aksi">
              <?php if ($t['status'] === 'menunggu'): ?>
                <button type="button" class="btn btn-sm btn-st btn-tandai-lunas"><i class="bi bi-check2 me-1"></i>Tandai Lunas</button>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="text-muted small text-center mt-3 mb-0 d-none" id="kosongTagihan">Tidak ada transaksi pada filter ini.</p>
    </div>
  </div>

  <script>
    const badgeLunas = <?= json_encode($badgeLunas) ?>;

    // Filter berdasarkan status
    const tombolFilter = document.querySelectorAll('#filterStatus button');
    tombolFilter.forEach(btn => btn.addEventListener('click', () => {
      tombolFilter.forEach(b => { b.classList.remove('btn-st'); b.classList.add('btn-outline-primary'); });
      btn.classList.add('btn-st'); btn.classList.remove('btn-outline-primary');
      const filter = btn.dataset.filter;
      let terlihat = 0;
      document.querySelectorAll('#tabelTagihan tr').forEach(tr => {
        const cocok = filter === 'semua' || tr.dataset.status === filter;
        tr.classList.toggle('d-none', !cocok);
        if (cocok) terlihat++;
      });
      document.getElementById('kosongTagihan').classList.toggle('d-none', terlihat > 0);
    }));

    // Tandai lunas (UI only): ubah badge & hilangkan tombol
    document.getElementById('tabelTagihan').addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-tandai-lunas');
      if (!btn) return;
      const tr = btn.closest('tr');
      tr.dataset.status = 'lunas';
      tr.querySelector('.kolom-status').innerHTML = `<span class="badge ${badgeLunas.kelas}">${badgeLunas.label}</span>`;
      tr.querySelector('.kolom-aksi').innerHTML = '<span class="text-muted small">—</span>';
    });
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

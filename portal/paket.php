<?php
// paket.php — pilih & konfirmasi paket internet (UI only).
require __DIR__ . '/../config.php';
require __DIR__ . '/../portal-config.php';
$judulHalaman = 'Pilih Paket';
$menuAktif = 'paket';
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="mb-4">
    <h5 class="fw-700 mb-1">Pilih Paket Internet</h5>
    <p class="text-muted small mb-0">Pilih paket untuk memperpanjang atau mengubah langganan Anda.</p>
  </div>

  <div class="row g-3">
    <?php foreach ($paketTersedia as $i => $p): ?>
    <div class="col-md-4">
      <!-- Kartu paket dapat dipilih; data dipakai oleh JS untuk ringkasan -->
      <div class="kartu kartu-pad paket-pilih h-100 <?= $p['dipilih'] ? 'terpilih' : '' ?>"
           data-nama="<?= htmlspecialchars($p['nama']) ?>"
           data-harga="<?= formatRupiah($p['harga']) ?>"
           data-kecepatan="<?= htmlspecialchars($p['kecepatan']) ?>"
           tabindex="0">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($p['kecepatan']) ?></span>
          <span class="paket-cek"><i class="bi bi-check-circle-fill"></i></span>
        </div>
        <div class="fw-700"><?= htmlspecialchars($p['nama']) ?></div>
        <div class="display-6 fw-800 text-st my-2"><?= formatRupiah($p['harga']) ?><small class="fs-6 text-muted fw-400">/bln</small></div>
        <ul class="list-unstyled small mb-0">
          <?php foreach ($p['fitur'] as $f): ?>
          <li class="mb-1"><i class="bi bi-check2 text-success me-1"></i><?= htmlspecialchars($f) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Ringkasan & konfirmasi -->
  <div class="kartu kartu-pad mt-4">
    <div class="row align-items-center g-3">
      <div class="col-md-8">
        <div class="text-muted text-uppercase fw-600 mb-1" style="font-size:.72rem">Paket Dipilih</div>
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-wifi text-st fs-4"></i>
          <div>
            <div class="fw-700" id="ringkasNama">—</div>
            <div class="text-muted small">Masa aktif s/d <strong>15 Juli 2026</strong> · <span id="ringkasHarga">—</span>/bulan</div>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-md-end">
        <a href="dashboard.php" class="btn btn-st btn-lg w-100 w-md-auto px-4">Konfirmasi</a>
      </div>
    </div>
  </div>

  <script>
    // Pilih paket: klik kartu -> tandai terpilih & perbarui ringkasan
    (function () {
      const kartuPaket = document.querySelectorAll('.paket-pilih');
      const namaEl  = document.getElementById('ringkasNama');
      const hargaEl = document.getElementById('ringkasHarga');
      function perbaruiRingkasan(kartu) {
        namaEl.textContent  = kartu.dataset.nama;
        hargaEl.textContent = kartu.dataset.harga;
      }
      function pilih(kartu) {
        kartuPaket.forEach(k => k.classList.remove('terpilih'));
        kartu.classList.add('terpilih');
        perbaruiRingkasan(kartu);
      }
      kartuPaket.forEach(kartu => kartu.addEventListener('click', () => pilih(kartu)));
      // Inisialisasi ringkasan dari kartu yang sudah terpilih
      const awal = document.querySelector('.paket-pilih.terpilih') || kartuPaket[0];
      if (awal) perbaruiRingkasan(awal);
    })();
  </script>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

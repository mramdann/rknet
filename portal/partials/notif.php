<?php
// notif.php — panel offcanvas notifikasi & informasi (data dari $daftarNotifikasi).

/**
 * Tampilkan satu baris notifikasi sebagai HTML.
 */
function tampilkanNotif(array $n): void
{
    $ikon = $n['tipe'] === 'informasi' ? 'bi-info-circle' : 'bi-bell';
    $warna = $n['tipe'] === 'informasi' ? 'text-rk' : 'text-success';
    ?>
    <div class="notif-item d-flex gap-3 py-3 border-bottom">
      <div class="notif-ico <?= $warna ?>"><i class="bi <?= $ikon ?>"></i></div>
      <div class="flex-grow-1">
        <div class="fw-600 small"><?= htmlspecialchars($n['judul']) ?></div>
        <p class="text-muted small mb-1"><?= htmlspecialchars($n['isi']) ?></p>
        <span class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($n['waktu']) ?></span>
      </div>
    </div>
    <?php
}
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="panelNotif" style="width:380px">
  <div class="offcanvas-header border-bottom">
    <h6 class="offcanvas-title fw-700">Notifikasi &amp; Informasi</h6>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="nav nav-tabs nav-fill px-2 pt-2" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabSemua">Semua</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabNotif">Notifikasi</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabInfo">Informasi</button></li>
    </ul>
    <div class="tab-content px-3">
      <div class="tab-pane fade show active" id="tabSemua">
        <?php foreach ($daftarNotifikasi as $n) tampilkanNotif($n); ?>
      </div>
      <div class="tab-pane fade" id="tabNotif">
        <?php foreach ($daftarNotifikasi as $n) if ($n['tipe'] === 'notifikasi') tampilkanNotif($n); ?>
      </div>
      <div class="tab-pane fade" id="tabInfo">
        <?php foreach ($daftarNotifikasi as $n) if ($n['tipe'] === 'informasi') tampilkanNotif($n); ?>
      </div>
    </div>
  </div>
</div>

<?php
// notif.php — panel offcanvas notifikasi & informasi (data dari $daftarNotifikasi, DB-backed).
// Item belum dibaca bisa diklik untuk menandai dibaca (fetch POST ke aksi-notifikasi.php).

/**
 * Tampilkan satu baris notifikasi sebagai HTML.
 */
function tampilkanNotif(array $n): void
{
    $ikon  = $n['tipe'] === 'informasi' ? 'bi-info-circle' : 'bi-bell';
    $warna = $n['tipe'] === 'informasi' ? 'text-rk' : 'text-success';
    $belumDibaca = !($n['dibaca'] ?? false);
    ?>
    <div class="notif-item d-flex gap-3 py-3 border-bottom<?= $belumDibaca ? ' belum-dibaca' : '' ?>"
         data-id="<?= (int) $n['id'] ?>" role="button" tabindex="0" aria-label="Klik untuk menandai dibaca">
      <div class="notif-ico <?= $warna ?>"><i class="bi <?= $ikon ?>"></i></div>
      <div class="flex-grow-1">
        <div class="fw-600 small notif-judul"><?= htmlspecialchars($n['judul']) ?></div>
        <p class="text-muted small mb-1"><?= htmlspecialchars($n['isi']) ?></p>
        <span class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($n['waktu']) ?></span>
      </div>
      <?php if ($belumDibaca): ?>
        <span class="badge bg-primary-subtle text-primary align-self-center notif-label-baru">Baru</span>
      <?php endif; ?>
    </div>
    <?php
}
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="panelNotif"
     data-csrf="<?= htmlspecialchars(tokenCsrf()) ?>" style="width:380px">
  <div class="offcanvas-header border-bottom">
    <h6 class="offcanvas-title fw-700">Notifikasi &amp; Informasi</h6>
    <div class="d-flex align-items-center gap-2">
      <button type="button" id="tandaiSemuaBaca" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-check2-all me-1"></i>Tandai semua dibaca</button>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>
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
<script>
  // Tandai notifikasi dibaca lewat fetch (POST + CSRF), lalu perbarui UI tanpa reload.
  (function () {
    const panel = document.getElementById('panelNotif');
    if (!panel) return;
    const csrf = panel.dataset.csrf;
    const badge = document.getElementById('badgeNotif');

    async function kirim(aksi, id) {
      const body = new URLSearchParams({ csrf: csrf, aksi: aksi, id: id });
      try {
        await fetch('aksi-notifikasi.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body.toString()
        });
      } catch (e) { /* jaringan bermasalah: biarkan status di UI tetap sesuai */ }
    }

    function tandaiItem(item) {
      if (!item || !item.classList.contains('belum-dibaca')) return;
      // Bersihkan semua salinan id yang sama (tab Semua & Informasi)
      const id = item.dataset.id;
      panel.querySelectorAll('.notif-item[data-id="' + id + '"]').forEach((el) => {
        el.classList.remove('belum-dibaca');
        const label = el.querySelector('.notif-label-baru');
        if (label) label.remove();
      });
      perbaruiBadge();
    }

    function perbaruiBadge() {
      if (!badge) return;
      const idUnik = new Set();
      panel.querySelectorAll('.notif-item.belum-dibaca').forEach((el) => idUnik.add(el.dataset.id));
      const sisa = idUnik.size;
      badge.classList.toggle('d-none', sisa === 0);
      if (sisa > 0) badge.textContent = sisa > 9 ? '9+' : String(sisa);
    }

    // Klik item: tandai dibaca
    panel.addEventListener('click', (ev) => {
      const item = ev.target.closest('.notif-item');
      if (!item) return;
      ev.preventDefault();
      const id = item.dataset.id;
      tandaiItem(item);
      if (id) kirim('baca', id);
    });
    // Dukungan keyboard (Enter/Space)
    panel.addEventListener('keydown', (ev) => {
      if (ev.key !== 'Enter' && ev.key !== ' ') return;
      const item = ev.target.closest('.notif-item');
      if (!item) return;
      ev.preventDefault();
      const id = item.dataset.id;
      tandaiItem(item);
      if (id) kirim('baca', id);
    });

    const tombolSemua = document.getElementById('tandaiSemuaBaca');
    if (tombolSemua) {
      tombolSemua.addEventListener('click', () => {
        panel.querySelectorAll('.notif-item.belum-dibaca').forEach(tandaiItem);
        kirim('baca_semua', '');
      });
    }
  })();
</script>

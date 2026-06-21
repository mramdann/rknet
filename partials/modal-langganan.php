<!-- Modal form berlangganan (UI only). Paket terisi otomatis dari tombol yang diklik. -->
<div class="modal fade" id="modalLangganan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 overflow-hidden">
      <div class="modal-header st-modal-head text-white border-0">
        <div>
          <h5 class="modal-title fw-700 mb-0">Berlangganan Starlite</h5>
          <small class="opacity-75">Isi data Anda, tim kami akan menghubungi Anda.</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formLangganan" class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-500 small">Nama Lengkap</label>
            <input type="text" class="form-control" placeholder="Nama Anda" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-500 small">No. Handphone</label>
            <input type="tel" class="form-control" placeholder="08xxxxxxxxxx" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Email</label>
            <input type="email" class="form-control" placeholder="email@contoh.com" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Alamat Pemasangan</label>
            <textarea class="form-control" rows="2" placeholder="Alamat lengkap" required></textarea>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Pilih Paket</label>
            <select class="form-select" id="pilihPaketLangganan">
              <?php foreach ($site['packages'] as $p): ?>
              <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?> — <?= htmlspecialchars($p['price']) ?>/bln</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 d-grid mt-2">
            <button type="submit" class="btn btn-st btn-lg">Kirim Permintaan</button>
          </div>
        </form>
        <div id="suksesLangganan" class="alert alert-success text-center mb-0 mt-3 d-none">
          <i class="bi bi-check-circle-fill me-1"></i> Terima kasih! Permintaan berlangganan Anda telah kami terima.
        </div>
      </div>
    </div>
  </div>
</div>

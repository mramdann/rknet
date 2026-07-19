<!-- Modal form redeem voucher Folaplay (UI only). -->
<div class="modal fade" id="modalRedeem" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 overflow-hidden">
      <div class="modal-header rk-modal-head text-white border-0">
        <div>
          <h5 class="modal-title fw-700 mb-0">Redeem Voucher Folaplay</h5>
          <small class="opacity-75">Masukkan kode voucher Anda untuk menukarkannya.</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formRedeem" class="row g-3">
          <div class="col-12 text-center mb-1">
            <i class="bi bi-ticket-perforated text-rk" style="font-size:2.5rem"></i>
          </div>
          <div class="col-12">
            <label class="form-label fw-500 small">Kode Voucher</label>
            <input type="text" class="form-control form-control-lg text-center text-uppercase" placeholder="XXXX-XXXX-XXXX" required>
          </div>
          <div class="col-12 d-grid mt-2">
            <button type="submit" class="btn btn-rk btn-lg">Redeem Sekarang</button>
          </div>
        </form>
        <div id="suksesRedeem" class="alert alert-success text-center mb-0 mt-3 d-none">
          <i class="bi bi-check-circle-fill me-1"></i> Voucher berhasil ditukarkan! Nikmati internet gratis Anda.
        </div>
      </div>
    </div>
  </div>
</div>

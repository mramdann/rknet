<section id="coverage" class="section st-section-soft">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-5">
        <span class="badge st-badge mb-2">CEK JANGKAUAN</span>
        <h2 class="fw-700">Sudah Terjangkau di Area Anda?</h2>
        <p class="text-muted">Masukkan data Anda untuk mengecek ketersediaan jaringan fiber Starlite di lokasi Anda. Gratis &amp; instan.</p>
        <ul class="list-unstyled small text-muted mb-0">
          <li class="mb-2"><i class="bi bi-check-circle-fill text-st me-2"></i>Pengecekan ketersediaan gratis</li>
          <li class="mb-2"><i class="bi bi-check-circle-fill text-st me-2"></i>Tim kami menghubungi Anda untuk pemasangan</li>
          <li class="mb-2"><i class="bi bi-check-circle-fill text-st me-2"></i>Gratis biaya instalasi</li>
        </ul>
      </div>
      <div class="col-lg-7">
        <div class="kartu-form p-4 p-md-5">
          <!-- Form cek jangkauan (UI only): tampilkan hasil dummy tanpa proses backend -->
          <form id="formJangkauan" class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-500 small">Nama Lengkap</label>
              <input type="text" class="form-control form-control-lg" placeholder="Nama Anda" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500 small">No. Handphone</label>
              <input type="tel" class="form-control form-control-lg" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Alamat Lengkap</label>
              <textarea class="form-control form-control-lg" rows="2" placeholder="Jalan, kelurahan, kecamatan, kota" required></textarea>
            </div>
            <div class="col-12 d-grid">
              <button type="submit" class="btn btn-st btn-lg">Cek Ketersediaan <i class="bi bi-search ms-1"></i></button>
            </div>
          </form>
          <!-- Hasil pengecekan (dummy) -->
          <div id="hasilJangkauan" class="alert alert-success d-flex align-items-start gap-2 mt-3 mb-0 d-none" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>
              <strong>Area Anda terjangkau!</strong><br>
              <span class="small">Jaringan fiber Starlite tersedia di lokasi Anda. Tim kami akan segera menghubungi Anda.</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

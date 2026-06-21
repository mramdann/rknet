<section id="paket" class="section st-section-soft">
  <div class="container">
    <div class="text-center mb-5">
      <span class="badge st-badge mb-2">UNLIMITED PACKAGE</span>
      <h2 class="fw-700">Pilih Paket Internet Kamu</h2>
      <p class="text-muted">Semua paket bebas FUP, unlimited, dan harga sudah termasuk PPN.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <?php foreach ($site['packages'] as $p): ?>
      <div class="col-md-6 col-lg-4">
        <div class="package-card h-100 <?= $p['featured'] ? 'featured' : '' ?>">
          <?php if ($p['featured']): ?><span class="badge bg-warning text-dark mb-2">Terpopuler</span><?php endif; ?>
          <h5 class="fw-700"><?= htmlspecialchars($p['name']) ?></h5>
          <div class="display-6 fw-800 text-st"><?= htmlspecialchars($p['price']) ?><small class="fs-6 text-muted fw-400"><?= htmlspecialchars($p['period']) ?></small></div>
          <ul class="list-unstyled my-4">
            <?php foreach ($p['features'] as $f): ?>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-st me-2"></i><?= htmlspecialchars($f) ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="#" class="btn btn-st w-100">Berlangganan Sekarang</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

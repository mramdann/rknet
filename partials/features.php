<section class="section bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-700">Kenapa Pilih Starlite?</h2>
    </div>
    <div class="row g-4 text-center">
      <?php foreach ($site['features'] as $f): ?>
      <div class="col-6 col-md-3">
        <div class="feature-item h-100">
          <div class="feature-ico"><i class="bi <?= htmlspecialchars($f['icon']) ?>"></i></div>
          <h6 class="fw-600 mt-3"><?= htmlspecialchars($f['title']) ?></h6>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

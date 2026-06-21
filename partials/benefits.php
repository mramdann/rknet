<section class="section bg-white">
  <div class="container">
    <div class="row g-3 justify-content-center">
      <?php foreach ($site['benefits'] as $b): ?>
      <div class="col-6 col-md-4 col-lg">
        <div class="benefit-card text-center h-100">
          <i class="bi <?= htmlspecialchars($b['icon']) ?>"></i>
          <p class="mb-0 mt-2 fw-500"><?= htmlspecialchars($b['text']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

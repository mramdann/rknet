<section id="hero" class="st-hero">
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <button data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
      <button data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
      <button data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    </div>
    <div class="carousel-inner">
      <?php foreach (['hero-1','hero-2','hero-3'] as $i => $img): ?>
      <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
        <img src="assets/img/<?= $img ?>.webp" class="d-block w-100 st-hero-img" alt="Banner RKnet <?= $i+1 ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span></button>
    <button class="carousel-control-next" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span></button>
  </div>
</section>

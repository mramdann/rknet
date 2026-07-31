<footer class="rk-footer text-white pt-5 pb-4">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5">
        <div class="footer-brand d-flex align-items-center gap-2 mb-3">
          <img src="assets/img/rknet.jpeg" alt="RKnet" class="footer-logo footer-logo-rk">
        </div>
        <p class="text-white-50 mb-1"><?= htmlspecialchars($site['company']) ?></p>
      </div>
      <div class="col-lg-4">
        <h6 class="fw-600">Alamat</h6>
        <p class="text-white-50"><?= htmlspecialchars($site['address']) ?></p>
        <h6 class="fw-600 mt-3">Telepon</h6>
        <a class="text-white text-decoration-none" href="tel:<?= htmlspecialchars($site['phone']) ?>"><?= htmlspecialchars($site['phone']) ?></a>
        <h6 class="fw-600 mt-3">Email</h6>
        <a class="text-white text-decoration-none" href="mailto:<?= htmlspecialchars($site['email']) ?>"><?= htmlspecialchars($site['email']) ?></a>
      </div>
      <div class="col-lg-3">
        <h6 class="fw-600">Social Media</h6>
        <div class="d-flex gap-2 mb-3">
          <?php foreach ($site['socials'] as $s): ?>
          <a href="<?= htmlspecialchars($s['url']) ?>" class="social-ico"><i class="bi <?= htmlspecialchars($s['icon']) ?>"></i></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <hr class="border-light opacity-25 my-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
      <small class="text-white-50">Copyright © <?= date('Y') ?> <?= htmlspecialchars($site['name']) ?> Indonesia. Dev by <span class="text-white">Maulida Hafizh</span>.</small>
      <div class="d-flex gap-3">
        <a href="legal.php?dok=terms" class="text-white-50 text-decoration-none small">Terms &amp; Conditions</a>
        <a href="legal.php?dok=privacy" class="text-white-50 text-decoration-none small">Privacy Policy</a>
        <a href="legal.php?dok=refund" class="text-white-50 text-decoration-none small">Refund Policy</a>
      </div>
    </div>
  </div>
</footer>

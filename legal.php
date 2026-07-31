<?php
// legal.php — halaman dokumen hukum (Syarat, Privasi, Refund) dalam satu file via ?dok=.
require __DIR__ . '/config.php';

// Daftar dokumen yang tersedia
$dokumenTersedia = [
    'terms' => [
        'judul' => 'Syarat & Ketentuan',
        'isi'   => [
            'Dengan berlangganan layanan RKnet Indonesia, Anda menyetujui seluruh syarat dan ketentuan yang berlaku. Layanan diberikan oleh PT Integrasi Jaringan Ekosistem.',
            'Pelanggan wajib memberikan data yang benar saat pendaftaran. Penyalahgunaan layanan, termasuk pemakaian di luar ketentuan wajar, dapat mengakibatkan penangguhan akun.',
            'Tarif, kecepatan, dan masa berlaku paket mengikuti ketentuan yang tertera pada halaman paket. Perusahaan berhak memperbarui ketentuan sewaktu-waktu dengan pemberitahuan.',
            'Layanan internet bersifat unlimited tanpa FUP sesuai paket yang dipilih. Gangguan jaringan akan ditangani sesuai prosedur layanan pelanggan.',
        ],
    ],
    'privacy' => [
        'judul' => 'Kebijakan Privasi',
        'isi'   => [
            'Kami menghormati privasi Anda. Data pribadi yang dikumpulkan (nama, nomor handphone, email, dan alamat) hanya digunakan untuk keperluan layanan, penagihan, dan komunikasi.',
            'Kami tidak menjual atau membagikan data pribadi Anda kepada pihak ketiga tanpa persetujuan, kecuali diwajibkan oleh hukum yang berlaku.',
            'Data disimpan secara aman dan hanya dapat diakses oleh pihak yang berwenang. Anda berhak meminta perubahan atau penghapusan data melalui layanan pelanggan.',
            'Dengan menggunakan situs ini, Anda menyetujui pengumpulan dan penggunaan data sesuai kebijakan ini.',
        ],
    ],
    'refund' => [
        'judul' => 'Kebijakan Pengembalian Dana',
        'isi'   => [
            'Pengembalian dana dapat diajukan apabila layanan tidak dapat diaktifkan karena alasan teknis dari pihak kami.',
            'Permohonan refund diajukan maksimal 7 hari sejak pembayaran melalui layanan pelanggan dengan menyertakan nomor invoice.',
            'Dana yang telah digunakan untuk masa langganan berjalan tidak dapat dikembalikan. Pengembalian diproses dalam 7–14 hari kerja.',
            'Biaya instalasi yang telah dikerjakan tidak termasuk dalam pengembalian dana.',
        ],
    ],
];

// Tentukan dokumen aktif (default: terms)
$dok = $_GET['dok'] ?? 'terms';
if (!isset($dokumenTersedia[$dok])) $dok = 'terms';
$dokumen = $dokumenTersedia[$dok];
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/head.php'; ?>
<body>
  <!-- Header sederhana untuk halaman legal -->
  <nav class="navbar bg-white shadow-sm sticky-top py-3">
    <div class="container">
      <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
        <img src="assets/img/rknet.jpeg" alt="RKnet" class="logo-legal-rk">
      </a>
      <a href="index.php" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda</a>
    </div>
  </nav>

  <!-- Judul dokumen -->
  <header class="legal-hero text-white py-5">
    <div class="container">
      <h1 class="fw-700 mb-1"><?= htmlspecialchars($dokumen['judul']) ?></h1>
      <p class="mb-0 opacity-75 small">Terakhir diperbarui: Juni 2026 &middot; RKnet Indonesia</p>
    </div>
  </header>

  <main class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <!-- Navigasi antar dokumen -->
        <div class="d-flex flex-wrap gap-2 mb-4">
          <?php foreach ($dokumenTersedia as $kunci => $d): ?>
            <a href="legal.php?dok=<?= $kunci ?>"
               class="btn btn-sm <?= $kunci === $dok ? 'btn-rk' : 'btn-outline-primary' ?>">
              <?= htmlspecialchars($d['judul']) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <!-- Isi dokumen -->
        <div class="kartu-form p-4 p-md-5">
          <?php foreach ($dokumen['isi'] as $i => $paragraf): ?>
            <h6 class="fw-700 text-rk mb-2"><?= $i + 1 ?>. Ketentuan</h6>
            <p class="text-muted"><?= htmlspecialchars($paragraf) ?></p>
          <?php endforeach; ?>
          <p class="text-muted small mb-0 mt-4 pt-3 border-top">
            Pertanyaan seputar dokumen ini? Hubungi layanan pelanggan kami di <strong><?= htmlspecialchars($site['phone']) ?></strong>.
          </p>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// invoice.php - rincian satu tagihan milik pelanggan yang sedang login.
require __DIR__ . '/../config.php';
require __DIR__ . '/../portal-config.php';
$judulHalaman = 'Invoice';
$menuAktif = 'transaksi';

$idTagihan = isset($_GET['id']) && is_string($_GET['id'])
    ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;
if ($idTagihan === false) {
    http_response_code(404);
    exit('Tagihan tidak ditemukan.');
}

$trx = kueriSatu(
    "SELECT t.id AS idTagihan, t.no_invoice AS noInvoice, t.harga, t.tanggal, t.status,
            t.catatan_verifikasi AS catatanVerifikasi, t.diajukan_pada AS diajukanPada,
            pk.nama AS paket, pk.kecepatan,
            rb.id AS idRekening, rb.jenis AS jenisRekening, rb.nama_bank AS namaBank, rb.nomor_rekening AS nomorRekening,
            rb.atas_nama AS atasNama
     FROM tagihan t
     LEFT JOIN paket pk ON pk.id = t.paket_id
     LEFT JOIN rekening_bank rb ON rb.id = t.rekening_bank_id
     WHERE t.id = ? AND t.pelanggan_id = ?",
    [(int) $idTagihan, $idPelanggan]
);
if ($trx === null) {
    http_response_code(404);
    exit('Tagihan tidak ditemukan.');
}

$badge = badgeStatus($trx['status']);
$total = (int) $trx['harga'];
$dpp = (int) round($total / 1.11);
$ppn = $total - $dpp;
$dapatMengunggah = in_array($trx['status'], ['menunggu', 'ditolak'], true);
$daftarRekeningAktif = $dapatMengunggah
    ? kueri(
        "SELECT id, jenis, nama_bank AS namaBank, nomor_rekening AS nomorRekening, atas_nama AS atasNama
         FROM rekening_bank WHERE status = 'aktif' ORDER BY id"
    )
    : [];
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <a href="transaksi.php" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    <button onclick="window.print()" class="btn btn-outline-primary"><i class="bi bi-printer me-1"></i>Cetak / Unduh</button>
  </div>

  <div class="kartu kartu-pad invoice-sheet mx-auto">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 pb-3 border-bottom">
      <div class="d-flex align-items-center gap-2">
        <img src="../assets/img/rknet.jpeg" alt="RKnet" class="logo-invoice-rk">
        <span class="brand-divider logo-invoice-divider"></span>
        <img src="../assets/img/rknet2.jpeg" alt="RWS Solution" class="logo-invoice-rws">
      </div>
      <div class="text-end">
        <h5 class="fw-800 text-rk mb-1">INVOICE</h5>
        <div class="small text-muted"><?= htmlspecialchars($trx['noInvoice']) ?></div>
        <span class="badge <?= $badge['kelas'] ?> mt-1"><?= $badge['label'] ?></span>
      </div>
    </div>

    <div class="row g-4 py-4">
      <div class="col-sm-6">
        <div class="text-muted text-uppercase fw-600 mb-2" style="font-size:.72rem">Ditagihkan Kepada</div>
        <div class="fw-700"><?= htmlspecialchars($pelanggan['nama']) ?></div>
        <div class="small text-muted"><?= htmlspecialchars($pelanggan['id']) ?></div>
        <div class="small text-muted"><?= htmlspecialchars($pelanggan['alamat'] ?? '') ?></div>
        <div class="small text-muted"><?= htmlspecialchars($pelanggan['hp']) ?></div>
      </div>
      <div class="col-sm-6 text-sm-end">
        <div class="text-muted text-uppercase fw-600 mb-2" style="font-size:.72rem">Diterbitkan Oleh</div>
        <div class="fw-700">CV. Rizky Win Solution</div>
        <div class="small text-muted">Jl. Tiang Bendera V No.20, Jakarta Barat</div>
        <div class="small text-muted">083815256355</div>
        <div class="small text-muted mt-2">Tanggal: <?= htmlspecialchars($trx['tanggal']) ?></div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table tabel-portal mb-0">
        <thead>
          <tr><th>Deskripsi</th><th>Periode</th><th class="text-end">Jumlah</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div class="fw-600"><?= htmlspecialchars($trx['paket'] ?? 'Paket tidak tersedia') ?></div>
              <div class="text-muted small">Langganan internet <?= htmlspecialchars($trx['kecepatan'] ?? '-') ?> - Unlimited</div>
            </td>
            <td class="text-muted">1 Bulan</td>
            <td class="text-end fw-600"><?= formatRupiah($dpp) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="row justify-content-end pt-3">
      <div class="col-sm-6 col-md-5">
        <div class="d-flex justify-content-between py-1 small"><span class="text-muted">Subtotal</span><span><?= formatRupiah($dpp) ?></span></div>
        <div class="d-flex justify-content-between py-1 small"><span class="text-muted">PPN 11%</span><span><?= formatRupiah($ppn) ?></span></div>
        <hr class="my-2">
        <div class="d-flex justify-content-between py-1"><span class="fw-700">Total</span><span class="fw-800 text-rk fs-5"><?= formatRupiah($total) ?></span></div>
      </div>
    </div>

    <?php if ($trx['status'] === 'ditolak'): ?>
      <div class="alert alert-danger mt-4 mb-0">
        <div class="fw-700"><i class="bi bi-exclamation-circle me-1"></i>Bukti pembayaran ditolak</div>
        <div class="small mt-1"><?= htmlspecialchars($trx['catatanVerifikasi'] ?? 'Silakan unggah bukti pembayaran pengganti.') ?></div>
      </div>
    <?php endif; ?>

    <?php if ($dapatMengunggah): ?>
      <div class="border rounded-3 p-3 mt-4 no-print">
        <h6 class="fw-700 mb-3">Kirim Bukti Pembayaran</h6>
        <?php if ($daftarRekeningAktif):
          $idPilihan = (int) ($trx['idRekening'] ?? 0);
          $idAktif = array_map(static fn(array $rekening): int => (int) $rekening['id'], $daftarRekeningAktif);
          if (!in_array($idPilihan, $idAktif, true)) {
              $idPilihan = (int) $daftarRekeningAktif[0]['id'];
          }
        ?>
          <form method="post" action="aksi-pembayaran.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" value="unggah">
            <input type="hidden" name="id_tagihan" value="<?= (int) $trx['idTagihan'] ?>">
            <div class="mb-3">
              <label class="form-label fw-600" for="pilihanMetode">Pilih metode pembayaran</label>
              <select name="rekening_bank_id" class="form-select" id="pilihanMetode" required>
                <?php foreach ($daftarRekeningAktif as $rekening): ?>
                  <option value="<?= (int) $rekening['id'] ?>"
                    data-jenis="<?= htmlspecialchars($rekening['jenis']) ?>"
                    data-nama="<?= htmlspecialchars($rekening['namaBank']) ?>"
                    data-nomor="<?= htmlspecialchars($rekening['nomorRekening']) ?>"
                    data-pemilik="<?= htmlspecialchars($rekening['atasNama']) ?>"
                    <?= (int) $rekening['id'] === $idPilihan ? 'selected' : '' ?>>
                    <?= $rekening['jenis'] === 'qris'
                        ? 'QRIS'
                        : htmlspecialchars($rekening['namaBank']) . ' - ' . htmlspecialchars($rekening['nomorRekening']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="bg-light border rounded-3 p-3 mb-3" id="detailMetode">
              <div class="d-flex justify-content-between gap-3"><span class="text-muted">Metode</span><strong id="detailNamaMetode"></strong></div>
              <div class="d-flex justify-content-between gap-3" id="barisNomorRekening"><span class="text-muted">Nomor Rekening</span><strong id="detailNomorRekening"></strong></div>
              <div class="text-center py-3 d-none" id="gambarQris">
                <img src="../assets/img/qris.jpeg" alt="Kode QRIS untuk pembayaran RKnet" class="img-fluid rounded-3 border" style="width:100%;max-width:320px;height:auto">
              </div>
              <div class="d-flex justify-content-between gap-3"><span class="text-muted">Atas Nama</span><strong class="text-end" id="detailAtasNama"></strong></div>
              <hr class="my-2">
              <div class="d-flex justify-content-between gap-3"><span class="text-muted">Bayar Tepat</span><strong class="text-rk"><?= formatRupiah($total) ?></strong></div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-600" for="buktiPembayaran">Bukti pembayaran</label>
              <input type="file" name="bukti_pembayaran" class="form-control" id="buktiPembayaran" accept="image/jpeg,image/png,image/webp,application/pdf" required>
              <div class="form-text">Format JPG, PNG, WebP, atau PDF. Ukuran maksimal 5 MiB.</div>
            </div>
            <button type="submit" class="btn btn-rk"><i class="bi bi-cloud-arrow-up me-1"></i>Kirim untuk Verifikasi</button>
          </form>
        <?php else: ?>
          <div class="alert alert-warning mb-0">
            Belum ada metode pembayaran aktif. Pengunggahan bukti dinonaktifkan; silakan hubungi dukungan RKnet.
          </div>
          <button type="button" class="btn btn-secondary mt-3" disabled>Kirim Bukti Pembayaran</button>
        <?php endif; ?>
      </div>
    <?php elseif ($trx['status'] === 'verifikasi'): ?>
      <div class="alert alert-info mt-4 mb-0">
        <div class="fw-700"><i class="bi bi-hourglass-split me-1"></i>Menunggu verifikasi admin</div>
        <div class="small mt-1">Bukti diajukan pada <?= htmlspecialchars($trx['diajukanPada'] ?? '-') ?>.</div>
        <?php if ($trx['namaBank'] !== null): ?>
          <div class="small mt-2">Tujuan: <strong><?= $trx['jenisRekening'] === 'qris'
              ? 'QRIS'
              : htmlspecialchars($trx['namaBank']) . ' ' . htmlspecialchars($trx['nomorRekening'] ?? '') ?></strong>, atas nama <?= htmlspecialchars($trx['atasNama'] ?? '') ?>.</div>
        <?php endif; ?>
      </div>
    <?php elseif ($trx['status'] === 'lunas' && $trx['namaBank'] !== null): ?>
      <div class="alert alert-success mt-4 mb-0">
        Pembayaran melalui <strong><?= $trx['jenisRekening'] === 'qris'
            ? 'QRIS'
            : htmlspecialchars($trx['namaBank']) . ' ' . htmlspecialchars($trx['nomorRekening'] ?? '') ?></strong> telah diterima.
      </div>
    <?php endif; ?>

    <div class="text-center text-muted small mt-4 pt-3 border-top">
      Terima kasih telah berlangganan RKnet Indonesia. Kuitansi ini sah dan dihasilkan otomatis oleh sistem.
    </div>
  </div>

  <?php if ($daftarRekeningAktif): ?>
  <script>
    const pilihanMetode = document.getElementById('pilihanMetode');
    const tampilkanMetode = () => {
      const rekening = pilihanMetode.options[pilihanMetode.selectedIndex];
      const qris = rekening.dataset.jenis === 'qris';
      document.getElementById('detailNamaMetode').textContent = qris ? 'QRIS' : rekening.dataset.nama;
      document.getElementById('detailNomorRekening').textContent = rekening.dataset.nomor;
      document.getElementById('detailAtasNama').textContent = rekening.dataset.pemilik;
      document.getElementById('barisNomorRekening').classList.toggle('d-none', qris);
      document.getElementById('gambarQris').classList.toggle('d-none', !qris);
    };
    pilihanMetode.addEventListener('change', tampilkanMetode);
    tampilkanMetode();
  </script>
  <?php endif; ?>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

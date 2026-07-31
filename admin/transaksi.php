<?php
// transaksi.php (admin) - daftar tagihan dan verifikasi bukti pembayaran.
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Transaksi';
$menuAktif = 'transaksi';

// Filter status (server-side)
$status = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
$where = '';
$params = [];
if (in_array($status, ['menunggu', 'verifikasi', 'ditolak', 'lunas'], true)) {
    $where = "WHERE t.status = ?";
    $params = [$status];
}
$sqlBase = "SELECT t.id AS idTagihan, t.no_invoice AS noInvoice, pl.nama AS pelanggan, pk.kecepatan AS paket,
                   t.harga, t.tanggal, t.status, t.bukti_pembayaran AS buktiPembayaran,
                   t.catatan_verifikasi AS catatanVerifikasi, t.diajukan_pada AS diajukanPada,
                   rb.jenis AS jenisRekening, rb.nama_bank AS namaBank, rb.nomor_rekening AS nomorRekening
            FROM tagihan t
            JOIN pelanggan pl ON pl.id = t.pelanggan_id
            LEFT JOIN paket pk ON pk.id = t.paket_id
            LEFT JOIN rekening_bank rb ON rb.id = t.rekening_bank_id
            $where ORDER BY t.id DESC";
$sqlCount = "SELECT COUNT(*) FROM tagihan t $where";
$hasil = ambilPaginasi($sqlBase, $sqlCount, $params);
$paramFilter = $status !== '' ? ['status' => $status] : [];
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
    <div>
      <h5 class="fw-700 mb-1">Transaksi & Tagihan</h5>
      <p class="text-muted small mb-0">Kelola status pembayaran pelanggan.</p>
    </div>
    <div class="btn-group" role="group">
      <a href="?status=" class="btn btn-sm <?= $status === '' ? 'btn-rk' : 'btn-outline-primary' ?>">Semua</a>
      <a href="?status=menunggu" class="btn btn-sm <?= $status === 'menunggu' ? 'btn-rk' : 'btn-outline-primary' ?>">Menunggu</a>
      <a href="?status=verifikasi" class="btn btn-sm <?= $status === 'verifikasi' ? 'btn-rk' : 'btn-outline-primary' ?>">Verifikasi</a>
      <a href="?status=ditolak" class="btn btn-sm <?= $status === 'ditolak' ? 'btn-rk' : 'btn-outline-primary' ?>">Ditolak</a>
      <a href="?status=lunas" class="btn btn-sm <?= $status === 'lunas' ? 'btn-rk' : 'btn-outline-primary' ?>">Lunas</a>
    </div>
  </div>

  <div class="kartu kartu-pad">
    <div class="table-responsive">
      <table class="table align-middle mb-0 tabel-portal">
        <thead>
          <tr><th>No. Invoice</th><th>Pelanggan</th><th>Paket</th><th>Pembayaran</th><th>Nominal</th><th>Status</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
          <?php foreach ($hasil['baris'] as $t): $b = badgeStatus($t['status']); ?>
          <tr>
            <td class="fw-500"><?= htmlspecialchars($t['noInvoice']) ?></td>
            <td><?= htmlspecialchars($t['pelanggan']) ?></td>
            <td><?= htmlspecialchars($t['paket'] ?? 'Paket tidak tersedia') ?></td>
            <td class="small">
              <div class="text-muted"><?= htmlspecialchars($t['tanggal']) ?></div>
              <?php if ($t['namaBank'] !== null): ?>
                <div class="fw-500 mt-1">
                  <?= $t['jenisRekening'] === 'qris'
                      ? 'QRIS'
                      : htmlspecialchars($t['namaBank']) . ' ' . htmlspecialchars($t['nomorRekening'] ?? '') ?>
                </div>
              <?php endif; ?>
              <?php if ($t['diajukanPada'] !== null): ?>
                <div class="text-muted">Diajukan <?= htmlspecialchars($t['diajukanPada']) ?></div>
              <?php endif; ?>
              <?php if ($t['buktiPembayaran'] !== null): ?>
                <a href="bukti-pembayaran.php?id=<?= (int) $t['idTagihan'] ?>" target="_blank" rel="noopener" class="text-decoration-none">
                  <i class="bi bi-paperclip me-1"></i>Lihat bukti
                </a>
              <?php endif; ?>
            </td>
            <td class="fw-600"><?= formatRupiah($t['harga']) ?></td>
            <td>
              <span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span>
              <?php if ($t['status'] === 'ditolak' && $t['catatanVerifikasi'] !== null): ?>
                <div class="small text-danger mt-1"><?= htmlspecialchars($t['catatanVerifikasi']) ?></div>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <?php if ($t['status'] === 'verifikasi'): ?>
                <form method="post" action="aksi-transaksi.php" class="mb-2" onsubmit="return confirm('Terima bukti dan tandai tagihan ini lunas?')">
                  <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
                  <input type="hidden" name="aksi" value="terima">
                  <input type="hidden" name="id" value="<?= (int) $t['idTagihan'] ?>">
                  <button type="submit" class="btn btn-sm btn-success w-100"><i class="bi bi-check2 me-1"></i>Terima</button>
                </form>
                <form method="post" action="aksi-transaksi.php" class="d-flex flex-column gap-1">
                  <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
                  <input type="hidden" name="aksi" value="tolak">
                  <input type="hidden" name="id" value="<?= (int) $t['idTagihan'] ?>">
                  <input type="text" name="catatan" class="form-control form-control-sm" maxlength="255" placeholder="Alasan penolakan" aria-label="Alasan penolakan" required>
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i>Tolak</button>
                </form>
              <?php else: ?>
                <span class="text-muted small">-</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if ($hasil['total'] === 0): ?>
          <tr><td colspan="7" class="text-muted small text-center py-3">Tidak ada transaksi pada filter ini.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <?php tampilPaginasi($hasil['hal'], $hasil['totalHal'], $paramFilter); ?>
    </div>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

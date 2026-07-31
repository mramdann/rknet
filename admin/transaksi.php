<?php
// transaksi.php (admin) - daftar tagihan dan verifikasi bukti pembayaran.
require __DIR__ . '/../admin-config.php';
$judulHalaman = 'Transaksi';
$menuAktif = 'transaksi';

// Filter status/bulan/tahun (server-side). Tanggal disimpan "d MMM yyyy", jadi
// bulan dicocokkan lewat CASE nama bulan Indonesia dan tahun lewat RIGHT(tanggal, 4).
$status = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
$bulan  = isset($_GET['bulan']) && ctype_digit((string) $_GET['bulan']) ? (int) $_GET['bulan'] : 0;
$tahun  = isset($_GET['tahun']) && ctype_digit((string) $_GET['tahun']) ? (int) $_GET['tahun'] : 0;

$ekspresiBulan = "CASE
    WHEN t.tanggal LIKE '% Jan %' THEN 1
    WHEN t.tanggal LIKE '% Feb %' THEN 2
    WHEN t.tanggal LIKE '% Mar %' THEN 3
    WHEN t.tanggal LIKE '% Apr %' THEN 4
    WHEN t.tanggal LIKE '% Mei %' THEN 5
    WHEN t.tanggal LIKE '% Jun %' THEN 6
    WHEN t.tanggal LIKE '% Jul %' THEN 7
    WHEN t.tanggal LIKE '% Agu %' THEN 8
    WHEN t.tanggal LIKE '% Sep %' THEN 9
    WHEN t.tanggal LIKE '% Okt %' THEN 10
    WHEN t.tanggal LIKE '% Nov %' THEN 11
    WHEN t.tanggal LIKE '% Des %' THEN 12
    ELSE 0 END";

$kondisi = [];
$params  = [];
if (in_array($status, ['menunggu', 'verifikasi', 'ditolak', 'lunas'], true)) {
    $kondisi[] = "t.status = ?";
    $params[] = $status;
}
if ($bulan >= 1 && $bulan <= 12) {
    $kondisi[] = "$ekspresiBulan = ?";
    $params[] = $bulan;
}
if ($tahun >= 2000 && $tahun <= 2100) {
    $kondisi[] = "RIGHT(t.tanggal, 4) = ?";
    $params[] = (string) $tahun;
}
$where = $kondisi !== [] ? 'WHERE ' . implode(' AND ', $kondisi) : '';

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
$paramFilter = array_filter([
    'status' => $status,
    'bulan'  => $bulan >= 1 && $bulan <= 12 ? $bulan : '',
    'tahun'  => $tahun >= 2000 && $tahun <= 2100 ? $tahun : '',
], static fn($v) => $v !== '');

// Data untuk lembar cetak: semua transaksi sesuai filter, tanpa paginasi.
$sqlCetak = "SELECT t.id AS idTagihan, t.no_invoice AS noInvoice, pl.nama AS pelanggan, pk.kecepatan AS paket,
                    t.harga, t.tanggal, t.status,
                    rb.jenis AS jenisRekening, rb.nama_bank AS namaBank, rb.nomor_rekening AS nomorRekening
             FROM tagihan t
             JOIN pelanggan pl ON pl.id = t.pelanggan_id
             LEFT JOIN paket pk ON pk.id = t.paket_id
             LEFT JOIN rekening_bank rb ON rb.id = t.rekening_bank_id
             $where ORDER BY t.id DESC";
$daftarCetak = kueri($sqlCetak, $params);
$totalNominalCetak = array_sum(array_map(static fn(array $t): int => (int) $t['harga'], $daftarCetak));

// Nama bulan & daftar tahun untuk dropdown filter
$namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
              7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
$daftarTahun = array_map('intval', array_column(kueri(
    "SELECT DISTINCT CAST(RIGHT(tanggal, 4) AS UNSIGNED) AS tahun FROM tagihan ORDER BY tahun DESC"
), 'tahun'));
$bagianPeriode = [];
if ($bulan >= 1 && $bulan <= 12)   $bagianPeriode[] = $namaBulan[$bulan];
if ($tahun >= 2000 && $tahun <= 2100) $bagianPeriode[] = (string) $tahun;
$labelPeriode = implode(' ', $bagianPeriode);

// Data untuk modal "Buat Transaksi"
$daftarPelangganAktif = kueri("SELECT id, nama FROM pelanggan WHERE status = 'aktif' ORDER BY nama");

// Opsi filter status (label pendek untuk dropdown)
$statusPendek = [
    'menunggu'   => 'Menunggu',
    'verifikasi' => 'Verifikasi',
    'ditolak'    => 'Ditolak',
    'lunas'      => 'Lunas',
];
?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/shell-head.php'; ?>
<?php include __DIR__ . '/partials/shell-open.php'; ?>

  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4 no-print">
    <div>
      <h5 class="fw-700 mb-1">Transaksi & Tagihan</h5>
      <p class="text-muted small mb-0">Kelola status pembayaran pelanggan.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <form method="get" class="d-flex flex-wrap gap-2 align-items-center">
        <select name="status" class="form-select form-select-sm" style="width:auto" aria-label="Status" onchange="this.form.submit()">
          <option value="">Semua status</option>
          <?php foreach ($statusPendek as $nilai => $label): ?>
            <option value="<?= $nilai ?>" <?= $status === $nilai ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="bulan" class="form-select form-select-sm" style="width:auto" aria-label="Bulan" onchange="this.form.submit()">
          <option value="">Semua bulan</option>
          <?php foreach ($namaBulan as $no => $nama): ?>
            <option value="<?= $no ?>" <?= $bulan === $no ? 'selected' : '' ?>><?= htmlspecialchars($nama) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="tahun" class="form-select form-select-sm" style="width:auto" aria-label="Tahun" onchange="this.form.submit()">
          <option value="">Semua tahun</option>
          <?php foreach ($daftarTahun as $th): ?>
            <option value="<?= $th ?>" <?= $tahun === $th ? 'selected' : '' ?>><?= $th ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($status !== '' || $bulan !== 0 || $tahun !== 0): ?>
          <a href="transaksi.php" class="btn btn-sm btn-light">Reset</a>
        <?php endif; ?>
      </form>
      <button class="btn btn-rk" type="button" data-bs-toggle="modal" data-bs-target="#modalBuatTransaksi">
        <i class="bi bi-plus-lg me-1"></i>Buat Transaksi</button>
      <button onclick="window.print()" class="btn btn-outline-primary"><i class="bi bi-printer me-1"></i>Cetak</button>
    </div>
  </div>

  <div class="kartu kartu-pad no-print">
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

  <!-- Modal buat transaksi -->
  <div class="modal fade" id="modalBuatTransaksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header rk-modal-head text-white border-0">
          <h5 class="modal-title fw-700 mb-0">Buat Transaksi Baru</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-4">
          <form method="post" action="aksi-transaksi.php" class="row g-3">
            <input type="hidden" name="csrf" value="<?= tokenCsrf() ?>">
            <input type="hidden" name="aksi" value="buat">
            <div class="col-12">
              <label class="form-label fw-500 small">Tujuan</label>
              <select name="jenis_target" id="jenisTarget" class="form-select">
                <option value="satu">Satu pelanggan</option>
                <option value="semua">Semua pelanggan aktif</option>
              </select>
            </div>
            <div class="col-12" id="blokPilihPelanggan">
              <label class="form-label fw-500 small">Pelanggan</label>
              <select name="pelanggan_id" class="form-select">
                <option value="">— Pilih pelanggan —</option>
                <?php foreach ($daftarPelangganAktif as $p): ?>
                  <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nama']) ?> (<?= htmlspecialchars($p['id']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-500 small">Tanggal</label>
              <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-12">
              <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>Paket & nominal otomatis mengikuti paket yang disubscribe tiap pelanggan.</p>
            </div>
            <div class="col-12 d-grid mt-2">
              <button type="submit" class="btn btn-rk btn-lg">Buat Transaksi</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Modal buat transaksi: tampilkan pilihan pelanggan hanya saat "satu pelanggan".
    (function () {
      const jenisTarget   = document.getElementById('jenisTarget');
      const blokPelanggan = document.getElementById('blokPilihPelanggan');
      const ubahTarget = () => blokPelanggan.classList.toggle('d-none', jenisTarget.value !== 'satu');
      jenisTarget.addEventListener('change', ubahTarget);
      document.getElementById('modalBuatTransaksi').addEventListener('shown.bs.modal', () => {
        document.querySelector('#modalBuatTransaksi select[name="pelanggan_id"]').value = '';
        ubahTarget();
      });
      ubahTarget();
    })();
  </script>

  <!-- Lembar cetak: semua transaksi sesuai filter ?status=, tidak terpengaruh paginasi -->
  <div class="print-sheet">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 pb-3 mb-3 border-bottom">
      <div>
        <h5 class="fw-700 mb-1">Laporan Transaksi & Tagihan</h5>
        <p class="text-muted small mb-0">
          RKnet Indonesia • <?= tanggalIndonesia() ?>
          <?= $status !== '' ? ' • Status: ' . badgeStatus($status)['label'] : '' ?>
          <?= $labelPeriode !== '' ? ' • Periode: ' . htmlspecialchars($labelPeriode) : '' ?>
        </p>
      </div>
      <div class="text-end">
        <div class="fw-600"><?= count($daftarCetak) ?> transaksi</div>
        <div class="text-muted small">Total <?= formatRupiah($totalNominalCetak) ?></div>
      </div>
    </div>
    <table class="table align-middle mb-0">
      <thead>
        <tr><th style="width:50px">No</th><th>No. Invoice</th><th>Pelanggan</th><th>Paket</th><th>Tanggal</th><th class="text-end">Nominal</th><th>Pembayaran</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach ($daftarCetak as $t): $b = badgeStatus($t['status']); ?>
        <tr>
          <td><?= $no++ ?></td>
          <td class="fw-500"><?= htmlspecialchars($t['noInvoice']) ?></td>
          <td><?= htmlspecialchars($t['pelanggan']) ?></td>
          <td><?= htmlspecialchars($t['paket'] ?? 'Paket tidak tersedia') ?></td>
          <td class="text-muted small"><?= htmlspecialchars($t['tanggal']) ?></td>
          <td class="text-end fw-600"><?= formatRupiah($t['harga']) ?></td>
          <td class="small">
            <?= $t['namaBank'] !== null
                ? ($t['jenisRekening'] === 'qris' ? 'QRIS' : htmlspecialchars($t['namaBank']) . ' ' . htmlspecialchars($t['nomorRekening'] ?? ''))
                : '-' ?>
          </td>
          <td><span class="badge <?= $b['kelas'] ?>"><?= $b['label'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if ($daftarCetak === []): ?>
        <tr><td colspan="8" class="text-muted small text-center py-3">Tidak ada data transaksi.</td></tr>
        <?php endif; ?>
      </tbody>
      <?php if ($daftarCetak !== []): ?>
      <tfoot>
        <tr>
          <td colspan="5" class="text-end fw-700">Total</td>
          <td class="text-end fw-700"><?= formatRupiah($totalNominalCetak) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>

<?php include __DIR__ . '/partials/shell-close.php'; ?>

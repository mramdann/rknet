<?php
// portal-config.php — data portal pelanggan, dibaca read-only dari database dbstarlite.
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()
require_once __DIR__ . '/db.php';        // db(): PDO
require_once __DIR__ . '/auth.php';      // sesi & guard
require_once __DIR__ . '/aksi.php';      // CSRF & flash

wajibLoginPelanggan();                   // halaman portal wajib login

$pdo = db();
$idPelanggan = idPelangganSaatIni();     // pelanggan dari sesi

// Data pelanggan yang sedang login
$stmt = $pdo->prepare("SELECT id, nama, email, hp, alamat, paket_id FROM pelanggan WHERE id = ?");
$stmt->execute([$idPelanggan]);
$pelanggan = $stmt->fetch();

// Paket internet yang sedang aktif (+ masa aktif presentasional)
$stmt = $pdo->prepare(
    "SELECT pk.nama, pk.kecepatan, pk.harga, pk.status
     FROM pelanggan pl JOIN paket pk ON pk.id = pl.paket_id
     WHERE pl.id = ?"
);
$stmt->execute([$idPelanggan]);
$paketAktif = $stmt->fetch();
$paketAktif['masaAktif'] = '15 Juli 2026';

// Riwayat transaksi pelanggan (urut sesuai seed: Jun, Mei, Apr, Jul)
$stmt = $pdo->prepare(
    "SELECT t.no_invoice AS noInvoice, pk.nama AS paket, pk.kecepatan AS kecepatan,
            t.harga, t.tanggal, t.status
     FROM tagihan t JOIN paket pk ON pk.id = t.paket_id
     WHERE t.pelanggan_id = ?
     ORDER BY t.id"
);
$stmt->execute([$idPelanggan]);
$daftarTransaksi = $stmt->fetchAll();

// Pilihan paket pada halaman "Pilih Paket" — harga dari DB, fitur & flag presentasional
$fiturPaket = [
    '100 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi'],
    '200 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Harga promo'],
    '500 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Prioritas jaringan'],
];
$paketTersedia = [];
foreach ($pdo->query("SELECT id, nama, kecepatan, harga FROM paket ORDER BY id") as $row) {
    $row['fitur']   = $fiturPaket[$row['kecepatan']] ?? [];
    $row['dipilih'] = ((int) $row['id'] === (int) ($pelanggan['paket_id'] ?? 0));
    $paketTersedia[] = $row;
}

// Feed notifikasi & informasi untuk panel offcanvas — presentasional (tetap statis)
$daftarNotifikasi = [
    ['tipe' => 'notifikasi', 'judul' => 'Pembayaran Berhasil', 'isi' => 'Tagihan INV/2026/06/008812 sebesar Rp100.000 telah dibayar.', 'waktu' => '15 Jun 2026, 09:14'],
    ['tipe' => 'informasi',  'judul' => 'Internet Aktif',       'isi' => 'Paket 200 Mbps Starlite aktif hingga 15 Juli 2026.', 'waktu' => '15 Jun 2026, 09:15'],
    ['tipe' => 'informasi',  'judul' => 'Promo Upgrade 500 Mbps', 'isi' => 'Nikmati internet 500 Mbps hanya Rp250.000/bulan. Unlimited!', 'waktu' => '10 Jun 2026, 12:00'],
    ['tipe' => 'notifikasi', 'judul' => 'Pemeliharaan Sistem',  'isi' => 'Pemeliharaan terjadwal 20 Jun 2026, 01:00-03:00 WIB.', 'waktu' => '08 Jun 2026, 17:30'],
];

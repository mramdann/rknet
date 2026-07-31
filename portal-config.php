<?php
// portal-config.php — data portal pelanggan, dibaca read-only dari database dbrknet.
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()
require_once __DIR__ . '/db.php';        // db(): mysqli + kueri()/kueriSatu()
require_once __DIR__ . '/auth.php';      // sesi & guard
require_once __DIR__ . '/aksi.php';      // CSRF & flash
require_once __DIR__ . '/pagination.php';  // paginasi

wajibLoginPelanggan();                   // halaman portal wajib login

$idPelanggan = idPelangganSaatIni();     // pelanggan dari sesi

// Data pelanggan yang sedang login
$pelanggan = kueriSatu("SELECT id, nama, email, hp, alamat, paket_id FROM pelanggan WHERE id = ?", [$idPelanggan]);

// Paket internet yang sedang aktif (+ masa aktif presentasional)
$paketAktif = kueriSatu(
    "SELECT pk.nama, pk.kecepatan, pk.harga, pk.status
     FROM pelanggan pl JOIN paket pk ON pk.id = pl.paket_id
     WHERE pl.id = ?",
    [$idPelanggan]
);
$paketAktif['masaAktif'] = '15 Juli 2026';

// Riwayat transaksi pelanggan, terbaru lebih dahulu.
$daftarTransaksi = kueri(
    "SELECT t.id AS idTagihan, t.no_invoice AS noInvoice, pk.nama AS paket, pk.kecepatan AS kecepatan,
             t.harga, t.tanggal, t.status
     FROM tagihan t LEFT JOIN paket pk ON pk.id = t.paket_id
     WHERE t.pelanggan_id = ?
     ORDER BY t.id DESC",
    [$idPelanggan]
);

// Pilihan paket pada halaman "Pilih Paket" — harga dari DB, fitur & flag presentasional
$fiturPaket = [
    '100 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi'],
    '200 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Harga promo'],
    '500 Mbps' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Prioritas jaringan'],
];
$paketTersedia = [];
foreach (kueri("SELECT id, nama, kecepatan, harga FROM paket WHERE status = 'aktif' ORDER BY id") as $row) {
    $row['fitur']   = $fiturPaket[$row['kecepatan']] ?? [];
    $row['dipilih'] = ((int) $row['id'] === (int) ($pelanggan['paket_id'] ?? 0));
    $paketTersedia[] = $row;
}

// Feed notifikasi & informasi untuk panel offcanvas — dari DB (broadcast + khusus pelanggan)
$daftarNotifikasi = [];
foreach (kueri(
    "SELECT id AS idNotifikasi, judul, isi, tanggal, pelanggan_id, dibaca
     FROM notifikasi
     WHERE pelanggan_id IS NULL OR pelanggan_id = ?
     ORDER BY id DESC",
    [$idPelanggan]
) as $row) {
    $daftarNotifikasi[] = [
        'id'     => (int) $row['idNotifikasi'],
        'tipe'   => $row['pelanggan_id'] !== null ? 'notifikasi' : 'informasi',
        'judul'  => $row['judul'],
        'isi'    => $row['isi'],
        'waktu'  => $row['tanggal'],
        'dibaca' => (int) $row['dibaca'] === 1,
    ];
}
$jumlahNotifikasiBelumDibaca = count(array_filter($daftarNotifikasi, static fn(array $n): bool => !$n['dibaca']));

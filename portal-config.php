<?php
// portal-config.php — data dummy untuk portal pelanggan (UI only)
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()

// Data pelanggan yang sedang login (contoh)
$pelanggan = [
    'id'     => 'STL-2024-008812',
    'nama'   => 'Dwi Anjasmoro',
    'email'  => 'dwi.anjasmoro@gmail.com',
    'hp'     => '0811-7891-2233',
    'alamat' => 'Jl. Mawar No.12, Roa Malaka, Tambora, Jakarta Barat',
];

// Paket internet yang sedang aktif
$paketAktif = [
    'nama'      => 'Paket 200 Mbps Starlite',
    'kecepatan' => '200 Mbps',
    'harga'     => 100000,
    'masaAktif' => '15 Juli 2026',
    'status'    => 'aktif',
];

// Riwayat transaksi pelanggan (terbaru di atas)
$daftarTransaksi = [
    ['noInvoice' => 'INV/2026/06/008812', 'paket' => 'Paket 200 Mbps Starlite', 'kecepatan' => '200 Mbps', 'harga' => 100000, 'tanggal' => '15 Jun 2026', 'status' => 'lunas'],
    ['noInvoice' => 'INV/2026/05/008812', 'paket' => 'Paket 200 Mbps Starlite', 'kecepatan' => '200 Mbps', 'harga' => 100000, 'tanggal' => '15 Mei 2026', 'status' => 'lunas'],
    ['noInvoice' => 'INV/2026/04/008812', 'paket' => 'Paket 200 Mbps Starlite', 'kecepatan' => '200 Mbps', 'harga' => 100000, 'tanggal' => '15 Apr 2026', 'status' => 'lunas'],
    ['noInvoice' => 'INV/2026/07/008812', 'paket' => 'Paket 200 Mbps Starlite', 'kecepatan' => '200 Mbps', 'harga' => 100000, 'tanggal' => '15 Jul 2026', 'status' => 'menunggu'],
];

// Daftar notifikasi & informasi untuk panel offcanvas
$daftarNotifikasi = [
    ['tipe' => 'notifikasi', 'judul' => 'Pembayaran Berhasil', 'isi' => 'Tagihan INV/2026/06/008812 sebesar Rp100.000 telah dibayar.', 'waktu' => '15 Jun 2026, 09:14'],
    ['tipe' => 'informasi',  'judul' => 'Internet Aktif',       'isi' => 'Paket 200 Mbps Starlite aktif hingga 15 Juli 2026.', 'waktu' => '15 Jun 2026, 09:15'],
    ['tipe' => 'informasi',  'judul' => 'Promo Upgrade 500 Mbps', 'isi' => 'Nikmati internet 500 Mbps hanya Rp250.000/bulan. Unlimited!', 'waktu' => '10 Jun 2026, 12:00'],
    ['tipe' => 'notifikasi', 'judul' => 'Pemeliharaan Sistem',  'isi' => 'Pemeliharaan terjadwal 20 Jun 2026, 01:00-03:00 WIB.', 'waktu' => '08 Jun 2026, 17:30'],
];

// Pilihan paket pada halaman "Pilih Paket"
$paketTersedia = [
    ['nama' => 'Paket 100 Mbps Starlite', 'kecepatan' => '100 Mbps', 'harga' => 199000, 'fitur' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi'], 'dipilih' => false],
    ['nama' => 'Paket 200 Mbps Starlite', 'kecepatan' => '200 Mbps', 'harga' => 100000, 'fitur' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Harga promo'], 'dipilih' => true],
    ['nama' => 'Paket 500 Mbps Starlite', 'kecepatan' => '500 Mbps', 'harga' => 250000, 'fitur' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Prioritas jaringan'], 'dipilih' => false],
];


<?php
// admin-config.php — data dummy untuk portal admin (UI only)
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()

// Admin yang sedang login
$admin = [
    'nama'  => 'Rangga Administrator',
    'email' => 'admin@starlite.id',
    'peran' => 'Super Admin',
];

// Ringkasan statistik untuk dashboard
$statistik = [
    'totalPelanggan'  => 1284,
    'pelangganAktif'  => 1156,
    'pendapatanBulan' => 142800000,
    'tagihanPending'  => 73,
];

// Daftar pelanggan (contoh)
$daftarPelanggan = [
    ['id' => 'STL-2024-008812', 'nama' => 'Dwi Anjasmoro', 'email' => 'dwi.anjasmoro@gmail.com', 'hp' => '0811-7891-2233', 'paket' => '200 Mbps', 'status' => 'aktif',    'bergabung' => '12 Jan 2024'],
    ['id' => 'STL-2024-008813', 'nama' => 'Siti Rahmawati', 'email' => 'siti.rahma@gmail.com',    'hp' => '0812-3344-5566', 'paket' => '100 Mbps', 'status' => 'aktif',    'bergabung' => '03 Feb 2024'],
    ['id' => 'STL-2024-008814', 'nama' => 'Budi Hartono',   'email' => 'budi.hartono@gmail.com',  'hp' => '0813-7788-9900', 'paket' => '500 Mbps', 'status' => 'aktif',    'bergabung' => '21 Mar 2024'],
    ['id' => 'STL-2024-008815', 'nama' => 'Maya Kusuma',    'email' => 'maya.kusuma@gmail.com',   'hp' => '0814-1122-3344', 'paket' => '100 Mbps', 'status' => 'nonaktif', 'bergabung' => '09 Apr 2024'],
    ['id' => 'STL-2024-008816', 'nama' => 'Agus Pratama',   'email' => 'agus.pratama@gmail.com',  'hp' => '0815-5566-7788', 'paket' => '200 Mbps', 'status' => 'aktif',    'bergabung' => '17 Mei 2024'],
    ['id' => 'STL-2024-008817', 'nama' => 'Indah Permata',  'email' => 'indah.permata@gmail.com', 'hp' => '0816-9988-7766', 'paket' => '500 Mbps', 'status' => 'aktif',    'bergabung' => '28 Mei 2024'],
];

// Daftar paket beserta jumlah pelanggan aktif
$daftarPaket = [
    ['nama' => 'Paket 100 Mbps Starlite', 'kecepatan' => '100 Mbps', 'harga' => 199000, 'jumlahPelanggan' => 412, 'status' => 'aktif'],
    ['nama' => 'Paket 200 Mbps Starlite', 'kecepatan' => '200 Mbps', 'harga' => 100000, 'jumlahPelanggan' => 638, 'status' => 'aktif'],
    ['nama' => 'Paket 500 Mbps Starlite', 'kecepatan' => '500 Mbps', 'harga' => 250000, 'jumlahPelanggan' => 234, 'status' => 'aktif'],
];

// Daftar tagihan / transaksi
$daftarTagihan = [
    ['noInvoice' => 'INV/2026/06/008812', 'pelanggan' => 'Dwi Anjasmoro', 'paket' => '200 Mbps', 'harga' => 100000, 'tanggal' => '15 Jun 2026', 'status' => 'lunas'],
    ['noInvoice' => 'INV/2026/06/008813', 'pelanggan' => 'Siti Rahmawati', 'paket' => '100 Mbps', 'harga' => 199000, 'tanggal' => '15 Jun 2026', 'status' => 'lunas'],
    ['noInvoice' => 'INV/2026/06/008814', 'pelanggan' => 'Budi Hartono',   'paket' => '500 Mbps', 'harga' => 250000, 'tanggal' => '15 Jun 2026', 'status' => 'menunggu'],
    ['noInvoice' => 'INV/2026/06/008816', 'pelanggan' => 'Agus Pratama',   'paket' => '200 Mbps', 'harga' => 100000, 'tanggal' => '16 Jun 2026', 'status' => 'menunggu'],
    ['noInvoice' => 'INV/2026/06/008817', 'pelanggan' => 'Indah Permata',  'paket' => '500 Mbps', 'harga' => 250000, 'tanggal' => '16 Jun 2026', 'status' => 'lunas'],
];

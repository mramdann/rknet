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

// Daftar lead dari form cek jangkauan (UI only)
$daftarLead = [
    ['id' => 'LEAD-0451', 'nama' => 'Rizki Maulana',  'hp' => '0812-1111-2233', 'area' => 'Cibinong, Bogor',      'tanggal' => '20 Jun 2026', 'status' => 'baru'],
    ['id' => 'LEAD-0452', 'nama' => 'Putri Lestari',  'hp' => '0813-4455-6677', 'area' => 'Depok, Jawa Barat',    'tanggal' => '20 Jun 2026', 'status' => 'dihubungi'],
    ['id' => 'LEAD-0453', 'nama' => 'Hendra Wijaya',  'hp' => '0856-7788-9900', 'area' => 'Bekasi, Jawa Barat',   'tanggal' => '19 Jun 2026', 'status' => 'terjadwal'],
    ['id' => 'LEAD-0454', 'nama' => 'Nadia Safira',   'hp' => '0878-1212-3434', 'area' => 'Tangerang, Banten',    'tanggal' => '18 Jun 2026', 'status' => 'selesai'],
    ['id' => 'LEAD-0455', 'nama' => 'Bayu Saputra',   'hp' => '0821-5656-7878', 'area' => 'Sleman, Yogyakarta',   'tanggal' => '17 Jun 2026', 'status' => 'batal'],
    ['id' => 'LEAD-0456', 'nama' => 'Citra Anggun',   'hp' => '0813-9090-1212', 'area' => 'Bogor, Jawa Barat',    'tanggal' => '17 Jun 2026', 'status' => 'baru'],
];

// Daftar area cakupan layanan (UI only)
$daftarArea = [
    ['nama' => 'Cibinong',    'kota' => 'Bogor',      'status' => 'tercakup', 'jumlahPelanggan' => 312],
    ['nama' => 'Depok Kota',  'kota' => 'Depok',      'status' => 'tercakup', 'jumlahPelanggan' => 458],
    ['nama' => 'Bekasi Barat','kota' => 'Bekasi',     'status' => 'tercakup', 'jumlahPelanggan' => 274],
    ['nama' => 'Sleman',      'kota' => 'Yogyakarta', 'status' => 'segera',   'jumlahPelanggan' => 0],
    ['nama' => 'Serpong',     'kota' => 'Tangerang',  'status' => 'tercakup', 'jumlahPelanggan' => 196],
    ['nama' => 'Cimahi',      'kota' => 'Bandung',    'status' => 'segera',   'jumlahPelanggan' => 0],
];

// Daftar notifikasi / broadcast ke pelanggan (UI only)
$daftarNotifikasi = [
    ['judul' => 'Pemeliharaan jaringan area Depok', 'isi' => 'Akan ada pemeliharaan 23 Jun 2026 pukul 01.00-03.00 WIB.', 'target' => 'Pelanggan Depok', 'tanggal' => '20 Jun 2026', 'status' => 'terkirim'],
    ['judul' => 'Promo upgrade 500 Mbps',           'isi' => 'Upgrade paket bulan ini diskon 30% untuk 3 bulan pertama.', 'target' => 'Semua pelanggan',  'tanggal' => '18 Jun 2026', 'status' => 'terkirim'],
    ['judul' => 'Pengingat jatuh tempo tagihan',    'isi' => 'Tagihan Juni jatuh tempo 15 Jun. Mohon segera lakukan pembayaran.', 'target' => 'Pelanggan aktif', 'tanggal' => '14 Jun 2026', 'status' => 'terkirim'],
    ['judul' => 'Selamat datang pelanggan baru',    'isi' => 'Draf sambutan untuk pelanggan yang baru bergabung.', 'target' => 'Pelanggan baru',   'tanggal' => '12 Jun 2026', 'status' => 'draft'],
    ['judul' => 'Survei kepuasan layanan Q2',       'isi' => 'Draf undangan mengisi survei kepuasan kuartal 2.', 'target' => 'Semua pelanggan',  'tanggal' => '10 Jun 2026', 'status' => 'draft'],
];

// Pengaturan situs (UI only)
$pengaturan = [
    'namaSitus' => 'Starlite Indonesia',
    'email'     => 'cs@starlite.id',
    'telepon'   => '0804-1-555-666',
    'alamat'    => 'Jl. Fiber Optik No. 1, Jakarta Selatan',
];

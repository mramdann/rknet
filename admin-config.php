<?php
// admin-config.php — data portal admin, dibaca read-only dari database dbrknet.
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()
require_once __DIR__ . '/db.php';        // db(): mysqli + kueri()/kueriSatu()/kueriNilai()
require_once __DIR__ . '/auth.php';      // sesi & guard
require_once __DIR__ . '/aksi.php';      // CSRF & flash
require_once __DIR__ . '/pagination.php';  // paginasi

wajibLoginAdmin();                       // halaman admin wajib login

// Admin yang sedang login (berdasarkan sesi)
$admin = kueriSatu("SELECT nama, email, peran FROM admin WHERE id = ?", [idAdminSaatIni()]);

// Daftar paket + jumlah pelanggan aktif (subquery)
$daftarPaket = kueri(
    "SELECT id, nama, kecepatan, harga, status,
            (SELECT COUNT(*) FROM pelanggan WHERE pelanggan.paket_id = paket.id) AS jumlahPelanggan
     FROM paket ORDER BY id"
);

// Daftar pelanggan (paket = kecepatan paketnya)
$daftarPelanggan = kueri(
    "SELECT pl.id, pl.nama, pl.email, pl.hp, pl.alamat, pk.kecepatan AS paket, pl.status, pl.tgl_bergabung AS bergabung
     FROM pelanggan pl LEFT JOIN paket pk ON pk.id = pl.paket_id
     ORDER BY pl.id"
);

// Daftar tagihan (gabung nama pelanggan & kecepatan paket)
$daftarTagihan = kueri(
    "SELECT t.id AS idTagihan, t.no_invoice AS noInvoice, pl.nama AS pelanggan, pk.kecepatan AS paket,
            t.harga, t.tanggal, t.status
     FROM tagihan t
     JOIN pelanggan pl ON pl.id = t.pelanggan_id
     LEFT JOIN paket pk ON pk.id = t.paket_id
     ORDER BY t.id"
);

// Daftar area cakupan
$daftarArea = kueri(
    "SELECT id, nama, kota, status, jumlah_pelanggan AS jumlahPelanggan FROM area ORDER BY id"
);

// Pengaturan situs
$pengaturan = kueriSatu(
    "SELECT nama_situs AS namaSitus, email, telepon, alamat FROM pengaturan LIMIT 1"
);

// Ringkasan statistik — dihitung nyata dari data
$statistik = [
    'totalPelanggan'  => (int) kueriNilai("SELECT COUNT(*) FROM pelanggan"),
    'pelangganAktif'  => (int) kueriNilai("SELECT COUNT(*) FROM pelanggan WHERE status = 'aktif'"),
    'pendapatanBulan' => (int) kueriNilai("SELECT COALESCE(SUM(harga), 0) FROM tagihan WHERE status = 'lunas'"),
    'tagihanPending'  => (int) kueriNilai("SELECT COUNT(*) FROM tagihan WHERE status = 'menunggu'"),
];

<?php
// admin-config.php — data portal admin, dibaca read-only dari database dbstarlite.
require_once __DIR__ . '/helpers.php';   // formatRupiah(), badgeStatus()
require_once __DIR__ . '/db.php';        // db(): PDO

$pdo = db();

// Admin yang sedang login
$admin = $pdo->query("SELECT nama, email, peran FROM admin LIMIT 1")->fetch();

// Daftar paket + jumlah pelanggan aktif (subquery)
$daftarPaket = $pdo->query(
    "SELECT nama, kecepatan, harga, status,
            (SELECT COUNT(*) FROM pelanggan WHERE pelanggan.paket_id = paket.id) AS jumlahPelanggan
     FROM paket ORDER BY id"
)->fetchAll();

// Daftar pelanggan (paket = kecepatan paketnya)
$daftarPelanggan = $pdo->query(
    "SELECT pl.id, pl.nama, pl.email, pl.hp, pk.kecepatan AS paket, pl.status, pl.tgl_bergabung AS bergabung
     FROM pelanggan pl LEFT JOIN paket pk ON pk.id = pl.paket_id
     ORDER BY pl.id"
)->fetchAll();

// Daftar tagihan (gabung nama pelanggan & kecepatan paket)
$daftarTagihan = $pdo->query(
    "SELECT t.no_invoice AS noInvoice, pl.nama AS pelanggan, pk.kecepatan AS paket,
            t.harga, t.tanggal, t.status
     FROM tagihan t
     JOIN pelanggan pl ON pl.id = t.pelanggan_id
     LEFT JOIN paket pk ON pk.id = t.paket_id
     ORDER BY t.id"
)->fetchAll();

// Daftar lead / prospek cek jangkauan
$daftarLead = $pdo->query(
    "SELECT id, nama, hp, area, tanggal, status FROM prospek ORDER BY id"
)->fetchAll();

// Daftar area cakupan
$daftarArea = $pdo->query(
    "SELECT nama, kota, status, jumlah_pelanggan AS jumlahPelanggan FROM area ORDER BY id"
)->fetchAll();

// Daftar notifikasi broadcast (urut sesuai seed)
$daftarNotifikasi = $pdo->query(
    "SELECT judul, isi, target, tanggal, status FROM notifikasi ORDER BY id"
)->fetchAll();

// Pengaturan situs
$pengaturan = $pdo->query(
    "SELECT nama_situs AS namaSitus, email, telepon, alamat FROM pengaturan LIMIT 1"
)->fetch();

// Ringkasan statistik — dihitung nyata dari data
$statistik = [
    'totalPelanggan'  => (int) $pdo->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn(),
    'pelangganAktif'  => (int) $pdo->query("SELECT COUNT(*) FROM pelanggan WHERE status = 'aktif'")->fetchColumn(),
    'pendapatanBulan' => (int) $pdo->query("SELECT COALESCE(SUM(harga), 0) FROM tagihan WHERE status = 'lunas'")->fetchColumn(),
    'tagihanPending'  => (int) $pdo->query("SELECT COUNT(*) FROM tagihan WHERE status = 'menunggu'")->fetchColumn(),
];

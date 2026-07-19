<?php
// aksi-notifikasi.php — tambah/hapus notifikasi (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah') {
    $judul  = trim($_POST['judul'] ?? '');
    $isi    = trim($_POST['isi'] ?? '');
    $target = trim($_POST['target'] ?? 'Semua pelanggan');
    if ($judul === '' || $isi === '') {
        setFlash('danger', 'Judul & isi notifikasi wajib diisi.');
        header('Location: notifikasi.php');
        exit;
    }
    // Tanggal hari ini dalam format tampilan Bahasa Indonesia
    $bulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
              7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $tanggal = date('d') . ' ' . $bulan[(int) date('n')] . ' ' . date('Y');
    eksekusi("INSERT INTO notifikasi (judul, isi, target, tanggal, status) VALUES (?, ?, ?, ?, 'terkirim')",
        [$judul, $isi, $target, $tanggal]);
    setFlash('success', 'Notifikasi berhasil dikirim.');
} elseif ($aksi === 'hapus') {
    eksekusi("DELETE FROM notifikasi WHERE id = ?", [(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Notifikasi berhasil dihapus.');
}
header('Location: notifikasi.php');
exit;

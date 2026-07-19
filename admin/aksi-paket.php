<?php
// aksi-paket.php — tambah/edit/hapus paket (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah' || $aksi === 'edit') {
    $nama      = trim($_POST['nama'] ?? '');
    $kecepatan = trim($_POST['kecepatan'] ?? '');
    $harga     = $_POST['harga'] ?? '';
    $status    = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';
    if ($nama === '' || $kecepatan === '' || !is_numeric($harga)) {
        setFlash('danger', 'Data paket tidak lengkap atau harga tidak valid.');
        header('Location: paket.php');
        exit;
    }
    if ($aksi === 'tambah') {
        eksekusi("INSERT INTO paket (nama, kecepatan, harga, status) VALUES (?, ?, ?, ?)",
            [$nama, $kecepatan, (int) $harga, $status]);
        setFlash('success', 'Paket berhasil ditambahkan.');
    } else {
        eksekusi("UPDATE paket SET nama = ?, kecepatan = ?, harga = ?, status = ? WHERE id = ?",
            [$nama, $kecepatan, (int) $harga, $status, (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Paket berhasil diperbarui.');
    }
} elseif ($aksi === 'hapus') {
    try {
        eksekusi("DELETE FROM paket WHERE id = ?", [(int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Paket berhasil dihapus.');
    } catch (mysqli_sql_exception $e) {
        setFlash('danger', 'Paket tidak bisa dihapus, masih dipakai pelanggan atau tagihan.');
    }
}
header('Location: paket.php');
exit;

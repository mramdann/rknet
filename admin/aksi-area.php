<?php
// aksi-area.php — tambah/edit/hapus area (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah' || $aksi === 'edit') {
    $nama   = trim($_POST['nama'] ?? '');
    $kota   = trim($_POST['kota'] ?? '');
    $status = ($_POST['status'] ?? 'tercakup') === 'segera' ? 'segera' : 'tercakup';
    if ($nama === '' || $kota === '') {
        setFlash('danger', 'Nama area & kota wajib diisi.');
        header('Location: area.php');
        exit;
    }
    if ($aksi === 'tambah') {
        eksekusi("INSERT INTO area (nama, kota, status, jumlah_pelanggan) VALUES (?, ?, ?, 0)",
            [$nama, $kota, $status]);
        setFlash('success', 'Area berhasil ditambahkan.');
    } else {
        eksekusi("UPDATE area SET nama = ?, kota = ?, status = ? WHERE id = ?",
            [$nama, $kota, $status, (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Area berhasil diperbarui.');
    }
} elseif ($aksi === 'hapus') {
    eksekusi("DELETE FROM area WHERE id = ?", [(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Area berhasil dihapus.');
}
header('Location: area.php');
exit;

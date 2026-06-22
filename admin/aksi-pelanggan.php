<?php
// aksi-pelanggan.php — edit data & toggle status pelanggan (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$pdo  = db();
$aksi = $_POST['aksi'] ?? '';
$id   = $_POST['id'] ?? '';

if ($aksi === 'edit') {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $hp     = trim($_POST['hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    if ($nama === '' || $email === '' || $hp === '') {
        setFlash('danger', 'Nama, email, dan No. HP wajib diisi.');
        header('Location: pelanggan.php');
        exit;
    }
    $stmt = $pdo->prepare("UPDATE pelanggan SET nama = ?, email = ?, hp = ?, alamat = ? WHERE id = ?");
    $stmt->execute([$nama, $email, $hp, $alamat, $id]);
    setFlash('success', 'Data pelanggan berhasil diperbarui.');
} elseif ($aksi === 'toggle') {
    $stmt = $pdo->prepare("UPDATE pelanggan SET status = IF(status = 'aktif', 'nonaktif', 'aktif') WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Status pelanggan berhasil diubah.');
}
header('Location: pelanggan.php');
exit;

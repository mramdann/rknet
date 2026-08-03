<?php
// aksi-pengaturan.php — simpan profil admin / ubah password / info situs (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$aksi    = $_POST['aksi'] ?? '';
$idAdmin = idAdminSaatIni();

if ($aksi === 'profil') {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($nama === '' || $email === '') {
        setFlash('danger', 'Nama & email wajib diisi.');
    } else {
        eksekusi("UPDATE admin SET nama = ?, email = ? WHERE id = ?", [$nama, $email, $idAdmin]);
        setFlash('success', 'Profil admin berhasil disimpan.');
    }
} elseif ($aksi === 'password') {
    $lama       = $_POST['lama'] ?? '';
    $baru       = $_POST['baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $row = kueriSatu("SELECT kata_sandi FROM admin WHERE id = ?", [$idAdmin]);
    if (!$row || $lama !== $row['kata_sandi']) {
        setFlash('danger', 'Password lama salah.');
    } elseif (strlen($baru) < 6 || $baru !== $konfirmasi) {
        setFlash('danger', 'Password baru minimal 6 karakter & harus sama dengan konfirmasi.');
    } else {
        eksekusi("UPDATE admin SET kata_sandi = ? WHERE id = ?", [$baru, $idAdmin]);
        setFlash('success', 'Password berhasil diperbarui.');
    }
} elseif ($aksi === 'situs') {
    $namaSitus = trim($_POST['nama_situs'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telepon   = trim($_POST['telepon'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');
    eksekusi("UPDATE pengaturan SET nama_situs = ?, email = ?, telepon = ?, alamat = ? WHERE id = 1",
        [$namaSitus, $email, $telepon, $alamat]);
    setFlash('success', 'Pengaturan situs berhasil disimpan.');
}
header('Location: pengaturan.php');
exit;

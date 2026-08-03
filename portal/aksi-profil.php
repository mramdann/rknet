<?php
// aksi-profil.php — simpan info akun & ganti password pelanggan (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginPelanggan();
cekCsrf();

$id   = idPelangganSaatIni();
$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'info') {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $hp     = trim($_POST['hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    if ($nama === '' || $email === '' || $hp === '') {
        setFlash('danger', 'Nama, email, dan No. HP wajib diisi.');
    } else {
        try {
            eksekusi("UPDATE pelanggan SET nama = ?, email = ?, hp = ?, alamat = ? WHERE id = ?",
                [$nama, $email, $hp, $alamat, $id]);
            setFlash('success', 'Informasi akun berhasil disimpan.');
        } catch (mysqli_sql_exception $e) {
            setFlash('danger', $e->getCode() === 1062
                ? 'Email sudah digunakan oleh akun lain.'
                : 'Informasi akun gagal disimpan. Silakan coba lagi.');
        }
    }
} elseif ($aksi === 'password') {
    $lama       = $_POST['lama'] ?? '';
    $baru       = $_POST['baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $row = kueriSatu("SELECT kata_sandi FROM pelanggan WHERE id = ?", [$id]);
    if (!$row || $lama !== $row['kata_sandi']) {
        setFlash('danger', 'Password lama salah.');
    } elseif (strlen($baru) < 6 || $baru !== $konfirmasi) {
        setFlash('danger', 'Password baru minimal 6 karakter & harus sama dengan konfirmasi.');
    } else {
        eksekusi("UPDATE pelanggan SET kata_sandi = ? WHERE id = ?", [$baru, $id]);
        setFlash('success', 'Password berhasil diperbarui.');
    }
}
header('Location: profil.php');
exit;

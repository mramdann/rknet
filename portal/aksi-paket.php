<?php
// aksi-paket.php — ubah paket aktif pelanggan (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginPelanggan();
cekCsrf();

$id  = idPelangganSaatIni();

if (($_POST['aksi'] ?? '') === 'pilih') {
    $paketId = $_POST['paket_id'] ?? '';
    if (!is_numeric($paketId)) {
        setFlash('danger', 'Paket tidak valid.');
        header('Location: paket.php');
        exit;
    }
    // Pastikan paket ada sebelum update
    if (!kueriSatu("SELECT id FROM paket WHERE id = ?", [(int) $paketId])) {
        setFlash('danger', 'Paket tidak ditemukan.');
        header('Location: paket.php');
        exit;
    }
    eksekusi("UPDATE pelanggan SET paket_id = ? WHERE id = ?", [(int) $paketId, $id]);
    setFlash('success', 'Paket aktif berhasil diubah.');
}
header('Location: paket.php');
exit;

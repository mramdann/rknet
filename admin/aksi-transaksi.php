<?php
// aksi-transaksi.php — tandai tagihan lunas (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

if (($_POST['aksi'] ?? '') === 'lunas') {
    $stmt = db()->prepare("UPDATE tagihan SET status = 'lunas' WHERE id = ?");
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Tagihan ditandai lunas.');
}
header('Location: transaksi.php');
exit;

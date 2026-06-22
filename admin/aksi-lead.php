<?php
// aksi-lead.php — tandai lead dihubungi (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

if (($_POST['aksi'] ?? '') === 'dihubungi') {
    $stmt = db()->prepare("UPDATE prospek SET status = 'dihubungi' WHERE id = ?");
    $stmt->execute([$_POST['id'] ?? '']);
    setFlash('success', 'Lead ditandai sudah dihubungi.');
}
header('Location: lead.php');
exit;

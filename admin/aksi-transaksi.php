<?php
// aksi-transaksi.php - terima atau tolak bukti pembayaran.
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}
cekCsrf();

$aksi = isset($_POST['aksi']) && is_string($_POST['aksi']) ? $_POST['aksi'] : '';
$id = isset($_POST['id']) && is_string($_POST['id'])
    ? filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;

if (!in_array($aksi, ['terima', 'tolak'], true) || $id === false) {
    setFlash('danger', 'Aksi atau ID tagihan tidak valid.');
    header('Location: transaksi.php');
    exit;
}

if ($aksi === 'tolak') {
    $catatan = isset($_POST['catatan']) && is_string($_POST['catatan']) ? trim($_POST['catatan']) : '';
    if ($catatan === '' || mb_strlen($catatan) > 255) {
        setFlash('danger', 'Alasan penolakan wajib diisi dan maksimal 255 karakter.');
        header('Location: transaksi.php');
        exit;
    }
    $stmt = stmtSiap(
        "UPDATE tagihan
         SET status = 'ditolak', catatan_verifikasi = ?, diverifikasi_pada = NOW(), diverifikasi_oleh = ?
         WHERE id = ? AND status = 'verifikasi'",
        [$catatan, (int) idAdminSaatIni(), (int) $id]
    );
    $pesanSukses = 'Bukti pembayaran ditolak.';
} else {
    $stmt = stmtSiap(
        "UPDATE tagihan
         SET status = 'lunas', catatan_verifikasi = NULL, diverifikasi_pada = NOW(), diverifikasi_oleh = ?
         WHERE id = ? AND status = 'verifikasi'
           AND rekening_bank_id IS NOT NULL AND bukti_pembayaran IS NOT NULL",
        [(int) idAdminSaatIni(), (int) $id]
    );
    $pesanSukses = 'Bukti pembayaran diterima dan tagihan dinyatakan lunas.';
}

setFlash(
    $stmt->affected_rows === 1 ? 'success' : 'danger',
    $stmt->affected_rows === 1 ? $pesanSukses : 'Status tagihan sudah berubah atau bukti tidak dapat diverifikasi.'
);
header('Location: transaksi.php');
exit;

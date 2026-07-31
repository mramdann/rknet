<?php
// bukti-pembayaran.php - sajikan bukti pembayaran hanya kepada admin terautentikasi.
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
wajibLoginAdmin();

function buktiTidakDitemukan(): never
{
    http_response_code(404);
    exit('Bukti pembayaran tidak ditemukan.');
}

$id = isset($_GET['id']) && is_string($_GET['id'])
    ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;
if ($id === false) {
    buktiTidakDitemukan();
}

$tagihan = kueriSatu("SELECT bukti_pembayaran AS namaFile FROM tagihan WHERE id = ?", [(int) $id]);
$namaFile = $tagihan['namaFile'] ?? null;
if (!is_string($namaFile)
    || basename($namaFile) !== $namaFile
    || !preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|webp|pdf)\z/', $namaFile)) {
    buktiTidakDitemukan();
}

$lokasi = __DIR__ . '/../storage/bukti-pembayaran/' . $namaFile;
if (!is_file($lokasi) || !is_readable($lokasi)) {
    buktiTidakDitemukan();
}

$ekstensi = pathinfo($namaFile, PATHINFO_EXTENSION);
$tipeMime = [
    'jpg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
    'pdf' => 'application/pdf',
][$ekstensi] ?? null;
if ($tipeMime === null) {
    buktiTidakDitemukan();
}

header('Cache-Control: no-store, private');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . $tipeMime);
header('Content-Length: ' . filesize($lokasi));
$disposisi = $ekstensi === 'pdf' ? 'attachment' : 'inline';
header('Content-Disposition: ' . $disposisi . '; filename="bukti-pembayaran.' . $ekstensi . '"');
readfile($lokasi);
exit;

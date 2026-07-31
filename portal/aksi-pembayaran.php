<?php
// aksi-pembayaran.php - unggah bukti pembayaran untuk tagihan milik pelanggan.
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginPelanggan();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}
cekCsrf();

$aksi = isset($_POST['aksi']) && is_string($_POST['aksi']) ? $_POST['aksi'] : '';
$idTagihan = isset($_POST['id_tagihan']) && is_string($_POST['id_tagihan'])
    ? filter_var($_POST['id_tagihan'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;
$idRekening = isset($_POST['rekening_bank_id']) && is_string($_POST['rekening_bank_id'])
    ? filter_var($_POST['rekening_bank_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;

if ($aksi !== 'unggah' || $idTagihan === false || $idRekening === false) {
    setFlash('danger', 'Data pembayaran tidak valid.');
    header('Location: transaksi.php');
    exit;
}

$urlInvoice = 'invoice.php?id=' . (int) $idTagihan;
$tagihan = kueriSatu(
    "SELECT status, bukti_pembayaran AS buktiLama
     FROM tagihan WHERE id = ? AND pelanggan_id = ?",
    [(int) $idTagihan, idPelangganSaatIni()]
);
if ($tagihan === null) {
    setFlash('danger', 'Tagihan tidak ditemukan.');
    header('Location: transaksi.php');
    exit;
}
if (!in_array($tagihan['status'], ['menunggu', 'ditolak'], true)) {
    setFlash('danger', 'Tagihan ini tidak menerima unggahan bukti pembayaran.');
    header('Location: ' . $urlInvoice);
    exit;
}

$rekeningAktif = (int) kueriNilai(
    "SELECT COUNT(*) FROM rekening_bank WHERE id = ? AND status = 'aktif'",
    [(int) $idRekening]
) === 1;
if (!$rekeningAktif) {
    setFlash('danger', 'Rekening tujuan tidak aktif atau tidak ditemukan.');
    header('Location: ' . $urlInvoice);
    exit;
}

$unggahan = $_FILES['bukti_pembayaran'] ?? null;
if (!is_array($unggahan)
    || !isset($unggahan['error'], $unggahan['size'], $unggahan['tmp_name'])
    || !is_int($unggahan['error'])
    || !is_int($unggahan['size'])
    || !is_string($unggahan['tmp_name'])) {
    setFlash('danger', 'Berkas bukti pembayaran tidak valid.');
    header('Location: ' . $urlInvoice);
    exit;
}
if ($unggahan['error'] !== UPLOAD_ERR_OK) {
    $pesan = $unggahan['error'] === UPLOAD_ERR_NO_FILE
        ? 'Bukti pembayaran wajib dipilih.'
        : 'Bukti pembayaran gagal diunggah. Pastikan ukurannya tidak melebihi 5 MiB.';
    setFlash('danger', $pesan);
    header('Location: ' . $urlInvoice);
    exit;
}
if ($unggahan['size'] <= 0 || $unggahan['size'] > 5 * 1024 * 1024 || !is_uploaded_file($unggahan['tmp_name'])) {
    setFlash('danger', 'Bukti pembayaran tidak valid atau melebihi batas 5 MiB.');
    header('Location: ' . $urlInvoice);
    exit;
}

$tipeMime = (new finfo(FILEINFO_MIME_TYPE))->file($unggahan['tmp_name']);
$ekstensiDiizinkan = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
];
if (!is_string($tipeMime) || !isset($ekstensiDiizinkan[$tipeMime])) {
    setFlash('danger', 'Format bukti harus JPG, PNG, WebP, atau PDF.');
    header('Location: ' . $urlInvoice);
    exit;
}
if (str_starts_with($tipeMime, 'image/')) {
    $infoGambar = @getimagesize($unggahan['tmp_name']);
    if ($infoGambar === false || ($infoGambar['mime'] ?? '') !== $tipeMime) {
        setFlash('danger', 'Isi berkas gambar tidak valid.');
        header('Location: ' . $urlInvoice);
        exit;
    }
}

$direktori = __DIR__ . '/../storage/bukti-pembayaran';
if (!is_dir($direktori) || !is_writable($direktori)) {
    setFlash('danger', 'Penyimpanan bukti pembayaran belum siap. Hubungi dukungan.');
    header('Location: ' . $urlInvoice);
    exit;
}

$namaBaru = bin2hex(random_bytes(16)) . '.' . $ekstensiDiizinkan[$tipeMime];
$lokasiBaru = $direktori . '/' . $namaBaru;
if (!move_uploaded_file($unggahan['tmp_name'], $lokasiBaru)) {
    setFlash('danger', 'Bukti pembayaran gagal disimpan.');
    header('Location: ' . $urlInvoice);
    exit;
}

try {
    $stmt = stmtSiap(
        "UPDATE tagihan
         SET rekening_bank_id = ?, bukti_pembayaran = ?, status = 'verifikasi',
             catatan_verifikasi = NULL, diajukan_pada = NOW(),
             diverifikasi_pada = NULL, diverifikasi_oleh = NULL
         WHERE id = ? AND pelanggan_id = ? AND status IN ('menunggu', 'ditolak')",
        [(int) $idRekening, $namaBaru, (int) $idTagihan, idPelangganSaatIni()]
    );
    if ($stmt->affected_rows !== 1) {
        @unlink($lokasiBaru);
        setFlash('danger', 'Status tagihan sudah berubah. Muat ulang halaman sebelum mencoba lagi.');
        header('Location: ' . $urlInvoice);
        exit;
    }
} catch (Throwable $e) {
    @unlink($lokasiBaru);
    setFlash('danger', 'Bukti pembayaran gagal diajukan. Silakan coba lagi.');
    header('Location: ' . $urlInvoice);
    exit;
}

$buktiLama = $tagihan['buktiLama'] ?? null;
if ($tagihan['status'] === 'ditolak'
    && is_string($buktiLama)
    && basename($buktiLama) === $buktiLama
    && preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|webp|pdf)\z/', $buktiLama)) {
    $lokasiLama = $direktori . '/' . $buktiLama;
    if (is_file($lokasiLama)) {
        @unlink($lokasiLama);
    }
}

setFlash('success', 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi admin.');
header('Location: ' . $urlInvoice);
exit;

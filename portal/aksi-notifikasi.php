<?php
// aksi-notifikasi.php — tandai notifikasi dibaca (baca / baca_semua), POST + CSRF, balas JSON.
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginPelanggan();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}
cekCsrf();

$idPelanggan = idPelangganSaatIni();
$aksi = isset($_POST['aksi']) && is_string($_POST['aksi']) ? $_POST['aksi'] : '';

if ($aksi === 'baca') {
    // Hanya notifikasi broadcast atau milik pelanggan yang sedang login
    $idNotif = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($idNotif > 0) {
        eksekusi(
            "UPDATE notifikasi SET dibaca = 1
             WHERE id = ? AND (pelanggan_id IS NULL OR pelanggan_id = ?)",
            [$idNotif, $idPelanggan]
        );
    }
} elseif ($aksi === 'baca_semua') {
    eksekusi(
        "UPDATE notifikasi SET dibaca = 1
         WHERE pelanggan_id IS NULL OR pelanggan_id = ?",
        [$idPelanggan]
    );
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true]);
exit;

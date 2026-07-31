<?php
// aksi-notifikasi.php — tambah/hapus notifikasi (POST, CSRF, redirect).
require __DIR__ . '/../db.php';
require __DIR__ . '/../helpers.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();
cekCsrf();

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah') {
    $judul = trim($_POST['judul'] ?? '');
    $isi   = trim($_POST['isi'] ?? '');
    if ($judul === '' || $isi === '') {
        setFlash('danger', 'Judul & isi notifikasi wajib diisi.');
        header('Location: notifikasi.php');
        exit;
    }
    $tanggal = tanggalIndonesia();
    $semua   = isset($_POST['target_mode']) && $_POST['target_mode'] === 'semua';

    if ($semua) {
        // Broadcast: satu baris dengan pelanggan_id NULL (muncul untuk semua pelanggan).
        eksekusi(
            "INSERT INTO notifikasi (judul, isi, pelanggan_id, target, tanggal, status) VALUES (?, ?, NULL, 'Semua pelanggan', ?, 'terkirim')",
            [$judul, $isi, $tanggal]
        );
        setFlash('success', 'Notifikasi berhasil dikirim ke semua pelanggan.');
    } else {
        // Target satu atau lebih pelanggan: satu baris per pelanggan (pelanggan_id terisi).
        $daftarId = array_values(array_filter(array_map('trim', (array) ($_POST['pelanggan_id'] ?? []))));
        if (!$daftarId) {
            setFlash('danger', 'Pilih minimal satu pelanggan target.');
            header('Location: notifikasi.php');
            exit;
        }
        $idAktif = array_map(static fn($r) => $r['id'], kueri("SELECT id FROM pelanggan WHERE status = 'aktif'"));
        $daftarId = array_values(array_intersect($daftarId, $idAktif));
        if (!$daftarId) {
            setFlash('danger', 'Pilih minimal satu pelanggan aktif.');
            header('Location: notifikasi.php');
            exit;
        }
        $mysqli = db();
        $mysqli->begin_transaction();
        try {
            foreach ($daftarId as $idPelanggan) {
                $nama = kueriNilai("SELECT nama FROM pelanggan WHERE id = ?", [$idPelanggan]);
                eksekusi(
                    "INSERT INTO notifikasi (judul, isi, pelanggan_id, target, tanggal, status) VALUES (?, ?, ?, ?, ?, 'terkirim')",
                    [$judul, $isi, $idPelanggan, $nama, $tanggal]
                );
            }
            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            setFlash('danger', 'Gagal mengirim notifikasi. Data tidak disimpan.');
            header('Location: notifikasi.php');
            exit;
        }
        setFlash('success', 'Notifikasi berhasil dikirim ke ' . count($daftarId) . ' pelanggan.');
    }
} elseif ($aksi === 'hapus') {
    eksekusi("DELETE FROM notifikasi WHERE id = ?", [(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Notifikasi berhasil dihapus.');
}
header('Location: notifikasi.php');
exit;

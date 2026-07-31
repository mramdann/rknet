<?php
// aksi-rekening.php - tambah, edit, dan hapus metode pembayaran.
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

if ($aksi === 'tambah' || $aksi === 'edit') {
    $jenis = isset($_POST['jenis']) && is_string($_POST['jenis']) ? $_POST['jenis'] : '';
    $namaBank = isset($_POST['nama_bank']) && is_string($_POST['nama_bank']) ? trim($_POST['nama_bank']) : '';
    $nomorRekening = isset($_POST['nomor_rekening']) && is_string($_POST['nomor_rekening']) ? trim($_POST['nomor_rekening']) : '';
    $atasNama = isset($_POST['atas_nama']) && is_string($_POST['atas_nama']) ? trim($_POST['atas_nama']) : '';
    $status = isset($_POST['status']) && is_string($_POST['status']) ? $_POST['status'] : '';

    if (!in_array($jenis, ['bank', 'qris'], true) || $atasNama === '' || !in_array($status, ['aktif', 'nonaktif'], true)) {
        setFlash('danger', 'Data metode pembayaran wajib diisi dengan benar.');
        header('Location: rekening.php');
        exit;
    }
    if ($jenis === 'qris') {
        $namaBank = 'QRIS';
        $nomorRekening = 'QRIS';
    } elseif ($namaBank === '' || $nomorRekening === '') {
        setFlash('danger', 'Nama bank dan nomor rekening wajib diisi.');
        header('Location: rekening.php');
        exit;
    }
    if (mb_strlen($namaBank) > 100 || mb_strlen($nomorRekening) > 60 || mb_strlen($atasNama) > 120) {
        setFlash('danger', 'Data metode pembayaran melebihi panjang yang diizinkan.');
        header('Location: rekening.php');
        exit;
    }
    if ($aksi === 'edit' && $id === false) {
        setFlash('danger', 'ID rekening tidak valid.');
        header('Location: rekening.php');
        exit;
    }

    try {
        if ($aksi === 'tambah') {
            eksekusi(
                "INSERT INTO rekening_bank (jenis, nama_bank, nomor_rekening, atas_nama, status) VALUES (?, ?, ?, ?, ?)",
                [$jenis, $namaBank, $nomorRekening, $atasNama, $status]
            );
            setFlash('success', 'Metode pembayaran berhasil ditambahkan.');
        } else {
            if (!(int) kueriNilai("SELECT COUNT(*) FROM rekening_bank WHERE id = ?", [(int) $id])) {
                setFlash('danger', 'Metode pembayaran tidak ditemukan.');
                header('Location: rekening.php');
                exit;
            }
            eksekusi(
                "UPDATE rekening_bank SET jenis = ?, nama_bank = ?, nomor_rekening = ?, atas_nama = ?, status = ? WHERE id = ?",
                [$jenis, $namaBank, $nomorRekening, $atasNama, $status, (int) $id]
            );
            setFlash('success', 'Metode pembayaran berhasil diperbarui.');
        }
    } catch (mysqli_sql_exception $e) {
        $pesan = $e->getCode() === 1062
            ? ($jenis === 'qris' ? 'QRIS sudah terdaftar.' : 'Nama bank dan nomor rekening tersebut sudah terdaftar.')
            : 'Metode pembayaran gagal disimpan.';
        setFlash('danger', $pesan);
    }
} elseif ($aksi === 'hapus') {
    if ($id === false || !(int) kueriNilai("SELECT COUNT(*) FROM rekening_bank WHERE id = ?", [(int) $id])) {
        setFlash('danger', 'Metode pembayaran tidak ditemukan.');
        header('Location: rekening.php');
        exit;
    }

    $sudahDipakai = (int) kueriNilai("SELECT COUNT(*) FROM tagihan WHERE rekening_bank_id = ?", [(int) $id]) > 0;
    if ($sudahDipakai) {
        eksekusi("UPDATE rekening_bank SET status = 'nonaktif' WHERE id = ?", [(int) $id]);
        setFlash('success', 'Metode pembayaran pernah dipakai pada tagihan sehingga dinonaktifkan, bukan dihapus.');
    } else {
        try {
            eksekusi("DELETE FROM rekening_bank WHERE id = ?", [(int) $id]);
            setFlash('success', 'Metode pembayaran berhasil dihapus.');
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1451) {
                eksekusi("UPDATE rekening_bank SET status = 'nonaktif' WHERE id = ?", [(int) $id]);
                setFlash('success', 'Metode pembayaran sedang dipakai pada tagihan sehingga dinonaktifkan, bukan dihapus.');
            } else {
                setFlash('danger', 'Metode pembayaran gagal dihapus.');
            }
        }
    }
} else {
    setFlash('danger', 'Aksi rekening tidak didukung.');
}

header('Location: rekening.php');
exit;

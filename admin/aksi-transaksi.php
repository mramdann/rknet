<?php
// aksi-transaksi.php - terima/tolak bukti pembayaran, atau buat transaksi baru.
require __DIR__ . '/../db.php';
require __DIR__ . '/../helpers.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';
wajibLoginAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}
cekCsrf();

$aksi = isset($_POST['aksi']) && is_string($_POST['aksi']) ? $_POST['aksi'] : '';

if (!in_array($aksi, ['terima', 'tolak', 'buat'], true)) {
    setFlash('danger', 'Aksi tidak valid.');
    header('Location: transaksi.php');
    exit;
}

// Buat transaksi: terbitkan tagihan + notifikasi otomatis ke pelanggan target.
// Paket & nominal mengikuti paket yang disubscribe tiap pelanggan (pelanggan.paket_id).
if ($aksi === 'buat') {
    $jenisTarget = isset($_POST['jenis_target']) && is_string($_POST['jenis_target']) ? $_POST['jenis_target'] : '';
    $tanggal     = isset($_POST['tanggal']) && is_string($_POST['tanggal']) ? trim($_POST['tanggal']) : '';

    if ($tanggal !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) !== 1) {
        setFlash('danger', 'Tanggal tidak valid.');
        header('Location: transaksi.php');
        exit;
    }

    // Resolusi daftar pelanggan target
    if ($jenisTarget === 'satu') {
        $idPelangganTarget = trim((string) ($_POST['pelanggan_id'] ?? ''));
        $pelangganTarget = kueriSatu(
            "SELECT id, nama FROM pelanggan WHERE id = ? AND status = 'aktif'",
            [$idPelangganTarget]
        );
        if ($pelangganTarget === null) {
            setFlash('danger', 'Pelanggan tidak ditemukan atau tidak aktif.');
            header('Location: transaksi.php');
            exit;
        }
        $daftarTarget = [$pelangganTarget];
    } elseif ($jenisTarget === 'semua') {
        $daftarTarget = kueri("SELECT id, nama FROM pelanggan WHERE status = 'aktif' ORDER BY nama");
    } else {
        setFlash('danger', 'Jenis target tidak valid.');
        header('Location: transaksi.php');
        exit;
    }
    if (!$daftarTarget) {
        setFlash('danger', 'Tidak ada pelanggan aktif untuk ditagih.');
        header('Location: transaksi.php');
        exit;
    }

    // Tanggal tampilan Indonesia + bagian tahun/bulan untuk nomor invoice
    $tglDisplay = $tanggal !== '' ? tanggalIndonesia($tanggal) : tanggalIndonesia();
    $tahun      = $tanggal !== '' ? (int) substr($tanggal, 0, 4) : (int) date('Y');
    $bulan      = $tanggal !== '' ? (int) substr($tanggal, 5, 2) : (int) date('m');
    $labelBulan = str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);

    $mysqli = db();
    $mysqli->begin_transaction();
    try {
        $jumlah = 0;
        $lewat  = [];   // pelanggan yang paketnya tidak aktif/tersedia
        foreach ($daftarTarget as $pl) {
            $paketPl = kueriSatu(
                "SELECT pk.id AS paketId, pk.harga
                 FROM pelanggan pl
                 JOIN paket pk ON pk.id = pl.paket_id AND pk.status = 'aktif'
                 WHERE pl.id = ?",
                [$pl['id']]
            );
            if ($paketPl === null || (int) $paketPl['harga'] <= 0) {
                $lewat[] = $pl['nama'];
                continue;
            }
            $paketId = (int) $paketPl['paketId'];
            $harga   = (int) $paketPl['harga'];

            $noInvoice = 'INV/' . $tahun . '/' . $labelBulan . '/' . substr($pl['id'], -6);
            $i = 2;
            while (kueriNilai("SELECT 1 FROM tagihan WHERE no_invoice = ?", [$noInvoice])) {
                $noInvoice = 'INV/' . $tahun . '/' . $labelBulan . '/' . substr($pl['id'], -6) . '-' . $i;
                $i++;
            }
            eksekusi(
                "INSERT INTO tagihan (no_invoice, pelanggan_id, paket_id, harga, tanggal, status) VALUES (?, ?, ?, ?, ?, 'menunggu')",
                [$noInvoice, $pl['id'], $paketId, $harga, $tglDisplay]
            );
            $judulNotif = 'Tagihan baru diterbitkan';
            $isiNotif   = 'Tagihan ' . $noInvoice . ' sebesar Rp' . number_format($harga, 0, ',', '.')
                        . ' telah dibuat. Silakan lakukan pembayaran melalui portal RKnet.';
            eksekusi(
                "INSERT INTO notifikasi (judul, isi, pelanggan_id, target, tanggal, status) VALUES (?, ?, ?, ?, ?, 'terkirim')",
                [$judulNotif, $isiNotif, $pl['id'], $pl['nama'], $tglDisplay]
            );
            $jumlah++;
        }
        $mysqli->commit();
        if ($jumlah > 0) {
            $pesan = $jumlah . ' transaksi berhasil dibuat. Notifikasi terkirim ke pelanggan terkait.';
            if ($lewat) {
                $pesan .= ' Dilewati (tanpa paket aktif): ' . implode(', ', $lewat) . '.';
            }
            setFlash('success', $pesan);
        } else {
            setFlash('danger', 'Tidak ada transaksi yang dibuat. Pastikan pelanggan memiliki paket aktif.');
        }
    } catch (Throwable $e) {
        $mysqli->rollback();
        setFlash('danger', 'Gagal membuat transaksi. Data tidak disimpan.');
    }
    header('Location: transaksi.php');
    exit;
}

$id = isset($_POST['id']) && is_string($_POST['id'])
    ? filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;
if ($id === false) {
    setFlash('danger', 'ID tagihan tidak valid.');
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

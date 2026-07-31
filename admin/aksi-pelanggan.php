<?php
// aksi-pelanggan.php - edit data dan transisi status eksplisit pelanggan.
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../aksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}
wajibLoginAdmin();
cekCsrf();

function inputPelanggan(string $nama): ?string
{
    return isset($_POST[$nama]) && is_string($_POST[$nama]) ? trim($_POST[$nama]) : null;
}

function kembaliPelanggan(string $tipe, string $pesan): never
{
    setFlash($tipe, $pesan);
    header('Location: pelanggan.php');
    exit;
}

$aksi = inputPelanggan('aksi');
$id = inputPelanggan('id');
if ($aksi === null || $id === null || $id === '' || mb_strlen($id) > 30) {
    kembaliPelanggan('danger', 'Permintaan pelanggan tidak valid.');
}

if ($aksi === 'edit') {
    $nama = inputPelanggan('nama');
    $email = inputPelanggan('email');
    $hp = inputPelanggan('hp');
    $alamat = inputPelanggan('alamat');
    $email = $email === null ? null : strtolower($email);

    if ($nama === null || $email === null || $hp === null || $alamat === null) {
        kembaliPelanggan('danger', 'Data pelanggan tidak valid.');
    }
    if ($nama === '' || mb_strlen($nama) > 100) {
        kembaliPelanggan('danger', 'Nama wajib diisi dan maksimal 100 karakter.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        kembaliPelanggan('danger', 'Alamat email tidak valid atau terlalu panjang.');
    }
    if (!preg_match('/^[0-9+().\s-]{8,30}$/', $hp) || mb_strlen($hp) > 30) {
        kembaliPelanggan('danger', 'Nomor handphone harus berisi 8-30 karakter yang wajar.');
    }
    if (mb_strlen($alamat) > 255) {
        kembaliPelanggan('danger', 'Alamat maksimal 255 karakter.');
    }
    if (kueriSatu("SELECT id FROM pelanggan WHERE id = ?", [$id]) === null) {
        kembaliPelanggan('danger', 'Pelanggan tidak ditemukan.');
    }

    try {
        eksekusi(
            "UPDATE pelanggan SET nama = ?, email = ?, hp = ?, alamat = ? WHERE id = ?",
            [$nama, $email, $hp, $alamat, $id]
        );
    } catch (mysqli_sql_exception $e) {
        kembaliPelanggan('danger', $e->getCode() === 1062
            ? 'Email sudah digunakan oleh pelanggan lain.'
            : 'Data pelanggan gagal diperbarui.');
    }
    kembaliPelanggan('success', 'Data pelanggan berhasil diperbarui.');
}

if ($aksi === 'status') {
    $statusSaatIni = inputPelanggan('status_saat_ini');
    $statusTujuan = inputPelanggan('status_tujuan');
    $transisi = [
        'pending' => ['aktif', 'nonaktif'],
        'aktif' => ['nonaktif'],
        'nonaktif' => ['aktif'],
    ];
    if ($statusSaatIni === null || $statusTujuan === null
        || !isset($transisi[$statusSaatIni])
        || !in_array($statusTujuan, $transisi[$statusSaatIni], true)) {
        kembaliPelanggan('danger', 'Perubahan status pelanggan tidak diizinkan.');
    }

    try {
        $stmt = stmtSiap(
            "UPDATE pelanggan SET status = ? WHERE id = ? AND status = ?",
            [$statusTujuan, $id, $statusSaatIni]
        );
    } catch (mysqli_sql_exception $e) {
        kembaliPelanggan('danger', 'Status pelanggan gagal diperbarui.');
    }
    if ($stmt->affected_rows !== 1) {
        kembaliPelanggan('danger', 'Status pelanggan sudah berubah atau pelanggan tidak ditemukan. Muat ulang halaman.');
    }

    $pesan = match ([$statusSaatIni, $statusTujuan]) {
        ['pending', 'aktif'] => 'Pendaftaran pelanggan berhasil disetujui.',
        ['pending', 'nonaktif'] => 'Pendaftaran pelanggan ditolak.',
        ['aktif', 'nonaktif'] => 'Akun pelanggan berhasil dinonaktifkan.',
        ['nonaktif', 'aktif'] => 'Akun pelanggan berhasil diaktifkan.',
    };
    kembaliPelanggan('success', $pesan);
}

kembaliPelanggan('danger', 'Aksi pelanggan tidak dikenali.');

<?php
// aksi-daftar.php - validasi dan simpan pendaftaran pelanggan publik.
require __DIR__ . '/../db.php';
require __DIR__ . '/../aksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}
cekCsrf();

function inputDaftar(string $nama): ?string
{
    return isset($_POST[$nama]) && is_string($_POST[$nama]) ? trim($_POST[$nama]) : null;
}

$nama = inputDaftar('nama');
$email = inputDaftar('email');
$hp = inputDaftar('hp');
$alamat = inputDaftar('alamat');
$paketIdInput = inputDaftar('paket_id');
$kataSandi = isset($_POST['kata_sandi']) && is_string($_POST['kata_sandi']) ? $_POST['kata_sandi'] : null;
$konfirmasi = isset($_POST['konfirmasi_sandi']) && is_string($_POST['konfirmasi_sandi']) ? $_POST['konfirmasi_sandi'] : null;
$email = $email === null ? null : strtolower($email);

$pesan = '';
if ($nama === null || $email === null || $hp === null || $alamat === null || $paketIdInput === null || $kataSandi === null || $konfirmasi === null) {
    $pesan = 'Data pendaftaran tidak valid.';
} elseif ($nama === '' || mb_strlen($nama) > 100) {
    $pesan = 'Nama wajib diisi dan maksimal 100 karakter.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $pesan = 'Alamat email tidak valid atau terlalu panjang.';
} elseif (!preg_match('/^[0-9+().\s-]{8,30}$/', $hp) || mb_strlen($hp) > 30) {
    $pesan = 'Nomor handphone harus berisi 8-30 karakter yang wajar.';
} elseif ($alamat === '' || mb_strlen($alamat) > 255) {
    $pesan = 'Alamat pemasangan wajib diisi dan maksimal 255 karakter.';
} elseif (!ctype_digit($paketIdInput) || (int) $paketIdInput < 1) {
    $pesan = 'Paket yang dipilih tidak valid.';
} elseif (strlen($kataSandi) < 8 || strlen($kataSandi) > 128) {
    $pesan = 'Password harus terdiri dari 8-128 karakter.';
} elseif ($kataSandi !== $konfirmasi) {
    $pesan = 'Konfirmasi password tidak sama.';
}

$paketId = (int) $paketIdInput;
if ($pesan === '' && kueriSatu("SELECT id FROM paket WHERE id = ? AND status = 'aktif'", [$paketId]) === null) {
    $pesan = 'Paket tidak ditemukan atau sudah tidak aktif.';
}
if ($pesan !== '') {
    setFlash('danger', $pesan);
    header('Location: daftar.php');
    exit;
}

$bulanIndonesia = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tanggalDaftar = date('d') . ' ' . $bulanIndonesia[(int) date('n')] . ' ' . date('Y');
$hash = password_hash($kataSandi, PASSWORD_DEFAULT);
$kunciDidapat = false;
$berhasil = false;
$pesanGagal = '';

try {
    $kunciDidapat = (int) kueriNilai("SELECT GET_LOCK('rknet_id_pelanggan', 5)") === 1;
    if (!$kunciDidapat) {
        $pesanGagal = 'Pendaftaran sedang sibuk. Silakan coba beberapa saat lagi.';
    } else {
        $maksimum = (int) kueriNilai(
            "SELECT COALESCE(MAX(CAST(RIGHT(id, 6) AS UNSIGNED)), 0) FROM pelanggan WHERE id REGEXP '^RKNET-[0-9]{4}-[0-9]{6}$'"
        );
        if ($maksimum >= 999999) {
            $pesanGagal = 'Nomor pelanggan sudah mencapai batas. Silakan hubungi admin RKnet.';
        } else {
            $idPelanggan = sprintf('RKNET-%s-%06d', date('Y'), $maksimum + 1);
            eksekusi(
                "INSERT INTO pelanggan (id, nama, email, hp, alamat, paket_id, status, tgl_bergabung, kata_sandi)
                 VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)",
                [$idPelanggan, $nama, $email, $hp, $alamat, $paketId, $tanggalDaftar, $hash]
            );
            $berhasil = true;
        }
    }
} catch (mysqli_sql_exception $e) {
    $pesanGagal = $e->getCode() === 1062
        ? 'Email sudah terdaftar. Silakan gunakan email lain atau login.'
        : 'Pendaftaran gagal disimpan. Silakan coba lagi.';
} catch (Throwable $e) {
    $pesanGagal = 'Pendaftaran gagal diproses. Silakan coba lagi.';
} finally {
    if ($kunciDidapat) {
        try {
            kueriNilai("SELECT RELEASE_LOCK('rknet_id_pelanggan')");
        } catch (Throwable $e) {
            // Koneksi MySQL akan melepaskan advisory lock jika pelepasan eksplisit gagal.
        }
    }
}

if ($berhasil) {
    setFlash('success', 'Pendaftaran berhasil. Akun Anda harus disetujui admin sebelum dapat login.');
    header('Location: login.php');
    exit;
}

setFlash('danger', $pesanGagal ?: 'Pendaftaran gagal diproses. Silakan coba lagi.');
header('Location: daftar.php');
exit;

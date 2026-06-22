<?php
// db.php — koneksi tunggal (singleton) ke database dbstarlite via PDO.
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host  = '127.0.0.1';
    $port  = '3382';
    $nama  = 'dbstarlite';
    $user  = 'root';
    $sandi = '';

    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$nama;charset=utf8mb4",
            $user,
            $sandi,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        pesanErrorDb('Koneksi database gagal.',
            'Pastikan MySQL berjalan di port 3382 dan database <code>dbstarlite</code> sudah dibuat.');
    }
    return $pdo;
}

// Tampilkan pesan error database yang rapi (tanpa membocorkan stack trace/path).
function pesanErrorDb(string $judul, string $detail): never
{
    http_response_code(500);
    exit('<div style="font-family:sans-serif;max-width:560px;margin:3rem auto;padding:1.5rem;'
       . 'border:1px solid #f0c0c0;border-radius:12px;background:#fff6f6;color:#7a1f1f">'
       . '<h2 style="margin:0 0 .5rem">' . $judul . '</h2>'
       . '<p style="margin:0">' . $detail . ' Jalankan <code>database/schema.sql</code> lalu '
       . '<code>database/seed.sql</code> ke database <code>dbstarlite</code>.</p></div>');
}

// Tangani error query yang tak tertangkap (mis. tabel belum dibuat) agar tampil rapi.
set_exception_handler(function (Throwable $e): void {
    if ($e instanceof PDOException) {
        pesanErrorDb('Database belum siap.', 'Tabel belum ada atau koneksi bermasalah.');
    }
    http_response_code(500);
    exit('<div style="font-family:sans-serif;margin:3rem">Terjadi kesalahan tak terduga.</div>');
});

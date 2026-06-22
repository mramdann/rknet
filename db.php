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
        http_response_code(500);
        exit('<h2 style="font-family:sans-serif">Koneksi database gagal.</h2>'
           . '<p style="font-family:sans-serif">Pastikan MySQL berjalan di port 3382 dan database '
           . '<code>dbstarlite</code> sudah dibuat (jalankan <code>database/schema.sql</code> lalu '
           . '<code>database/seed.sql</code>).</p>');
    }
    return $pdo;
}

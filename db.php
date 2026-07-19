<?php
// db.php — koneksi mysqli ke dbrknet + helper query. Setelan koneksi di satu tempat (konstanta di bawah).

const DB_HOST = '127.0.0.1';
const DB_PORT = 3382;
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'dbrknet';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   // error mysqli sebagai exception

function db(): mysqli
{
    static $db = null;
    if ($db !== null) return $db;
    try {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $db->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $e) {
        pesanErrorDb('Koneksi database gagal.',
            'Pastikan MySQL berjalan di port ' . DB_PORT . ' dan database <code>' . DB_NAME . '</code> sudah dibuat.');
    }
    return $db;
}

// Siapkan & jalankan prepared statement (bind_param otomatis by-reference).
function stmtSiap(string $sql, array $params): mysqli_stmt
{
    $stmt = db()->prepare($sql);
    if ($params) {
        $tipe = '';
        foreach ($params as $p) {
            $tipe .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
        }
        $ref = [$tipe];
        foreach ($params as $i => $v) {
            $ref[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $ref);
    }
    $stmt->execute();
    return $stmt;
}

function kueri(string $sql, array $params = []): array
{
    return stmtSiap($sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}

function kueriSatu(string $sql, array $params = []): ?array
{
    $baris = stmtSiap($sql, $params)->get_result()->fetch_assoc();
    return $baris ?: null;
}

function kueriNilai(string $sql, array $params = [])
{
    $baris = stmtSiap($sql, $params)->get_result()->fetch_row();
    return $baris ? $baris[0] : null;
}

function eksekusi(string $sql, array $params = []): void
{
    stmtSiap($sql, $params);
}

// Pesan error database yang rapi (tanpa stack trace/path).
function pesanErrorDb(string $judul, string $detail): never
{
    http_response_code(500);
    exit('<div style="font-family:sans-serif;max-width:560px;margin:3rem auto;padding:1.5rem;'
       . 'border:1px solid #f0c0c0;border-radius:12px;background:#fff6f6;color:#7a1f1f">'
       . '<h2 style="margin:0 0 .5rem">' . $judul . '</h2>'
       . '<p style="margin:0">' . $detail . ' Jalankan <code>database/schema.sql</code> lalu '
       . '<code>database/seed.sql</code> ke database <code>dbrknet</code>.</p></div>');
}

// Tangani error query tak tertangkap (mis. tabel belum dibuat) agar tampil rapi.
set_exception_handler(function (Throwable $e): void {
    if ($e instanceof mysqli_sql_exception) {
        pesanErrorDb('Database belum siap.', 'Tabel belum ada atau koneksi bermasalah.');
    }
    http_response_code(500);
    exit('<div style="font-family:sans-serif;margin:3rem">Terjadi kesalahan tak terduga.</div>');
});

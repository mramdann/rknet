# Migrasi PDO → mysqli — Design

**Date:** 2026-06-23
**Status:** Approved design
**Scope:** Ganti lapisan akses data dari PDO ke **mysqli** melalui fungsi helper di `db.php`, dan pusatkan setelan koneksi (termasuk port) sebagai konstanta di satu tempat. Tanpa mengubah skema/data atau tampilan.

## Goal

Semua query memakai **mysqli** (prepared statement) alih-alih PDO, dengan call-site tetap ringkas lewat helper. Setelan host/port/user/pass/nama DB berada di satu blok konstanta di `db.php`.

## Constraints & Decisions

- Pakai **mysqli** (bukan PDO). Prepared statement dengan `bind_param` (tipe otomatis).
- Setelan koneksi = **konstanta** `DB_HOST/DB_PORT/DB_USER/DB_PASS/DB_NAME` di atas `db.php`; port `3382` tak lagi terduplikasi (dipakai koneksi & pesan error).
- Helper di `db.php`: `db()`, `kueri()`, `kueriSatu()`, `kueriNilai()`, `eksekusi()`. Call-site memakai bentuk array parameter yang sama seperti PDO sebelumnya.
- Error mysqli sebagai **exception** (`mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)`); koneksi gagal / tabel belum ada → kartu pesan rapi (`pesanErrorDb`), tanpa bocor stack trace (perilaku sama seperti sekarang).
- Bind param via **referensi** (`call_user_func_array`) agar aman di semua PHP 8.
- Kode & komentar Bahasa Indonesia. Lint tiap `.php`. Verifikasi: suite E2E 39-cek + BrowserOS.
- Tidak mengubah SQL, skema, data, atau markup.

## Struktur File (yang berubah)

```
db.php                       # tulis ulang: konstanta + koneksi mysqli + helper
pagination.php               # ambilPaginasi() lepas param $pdo; pakai helper
admin-config.php             # $pdo->... -> kueri/kueriSatu/kueriNilai
portal-config.php            # idem (+ foreach query -> foreach kueri)
admin/pelanggan.php          # ambilPaginasi($sqlBase,$sqlCount,$params)  (lepas $pdo)
admin/transaksi.php          # idem
admin/lead.php               # idem
admin/notifikasi.php         # idem
portal/transaksi.php         # idem
admin/login.php  portal/login.php               # kueriSatu()
admin/aksi-*.php (7)  portal/aksi-*.php (2)      # eksekusi()/kueriSatu(); FK catch mysqli
```

## Komponen: db.php (baru)

```php
const DB_HOST = '127.0.0.1';
const DB_PORT = 3382;
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'dbstarlite';

function db(): mysqli
// singleton; mysqli_report(EXCEPTION+STRICT); new mysqli(HOST,USER,PASS,NAME,PORT);
// set_charset('utf8mb4'); gagal koneksi -> pesanErrorDb('Koneksi database gagal.', ...port/nama)

function stmtSiap(string $sql, array $params): mysqli_stmt
// prepare; bila $params: tipe = gabungan 'i'|'d'|'s' per nilai; bind via referensi (call_user_func_array); execute

function kueri(string $sql, array $params = []): array        // get_result()->fetch_all(MYSQLI_ASSOC)
function kueriSatu(string $sql, array $params = []): ?array    // get_result()->fetch_assoc() ?: null
function kueriNilai(string $sql, array $params = [])           // get_result()->fetch_row()[0] ?? null
function eksekusi(string $sql, array $params = []): void       // hanya execute (INSERT/UPDATE/DELETE)

function pesanErrorDb(string $judul, string $detail): never    // kartu HTML rapi (dipertahankan)
// set_exception_handler: mysqli_sql_exception (mis. tabel belum ada) -> pesanErrorDb('Database belum siap.', ...)
```

Deteksi tipe: `is_int($v)?'i':(is_float($v)?'d':'s')`. Nilai `NULL` di-bind sebagai `'s'` (mysqli mengirim NULL dengan benar). Semua parameter yang ada di kode saat ini berupa string/int → aman.

## Pemetaan Call-site (pola)

| PDO sekarang | mysqli (helper) |
|---|---|
| `$pdo = db();` | *(dihapus)* |
| `$pdo->query($sql)->fetchAll()` | `kueri($sql)` |
| `$stmt=$pdo->prepare($sql); $stmt->execute($p); $stmt->fetchAll()` | `kueri($sql, $p)` |
| `...->execute($p); ...->fetch()` | `kueriSatu($sql, $p)` |
| `$pdo->query($sql)->fetchColumn()` | `kueriNilai($sql)` |
| `$stmt=$pdo->prepare($ins); $stmt->execute($p);` (tulis) | `eksekusi($ins, $p)` |
| `foreach ($pdo->query($sql) as $row)` | `foreach (kueri($sql) as $row)` |
| `ambilPaginasi($pdo, $b, $c, $p)` | `ambilPaginasi($b, $c, $p)` |
| `catch (PDOException $e)` | `catch (mysqli_sql_exception $e)` |

`pagination.php`: `ambilPaginasi(string $sqlBase, string $sqlCount, array $params, int $perHalaman = PER_HALAMAN)` — hitung total via `kueriNilai($sqlCount,$params)`, ambil baris via `kueri($sqlBase." LIMIT $n OFFSET $o", $params)`. `require_once db.php` ditambahkan agar helper pasti tersedia.

## Error Handling

- Koneksi gagal → `pesanErrorDb('Koneksi database gagal.', 'Pastikan MySQL berjalan di port '.DB_PORT.' ...')`.
- Query gagal tak tertangkap (mis. tabel belum ada) → `set_exception_handler` menangkap `mysqli_sql_exception` → `pesanErrorDb('Database belum siap.', ...)`.
- Hapus paket ber-FK → `try/catch (mysqli_sql_exception)` di `aksi-paket.php` → flash danger.
- Validasi & alur lain tak berubah.

## Verifikasi

1. `php -l` tiap file yang disentuh.
2. Jalankan **suite E2E 39-cek** (publik/guard/login/logout/CRUD/CSRF/pagination) — harus tetap 39/39.
3. BrowserOS spot-check (dashboard admin & portal, satu aksi tulis).
4. Rollback data uji ke seed.

## Out of Scope

- Perubahan skema, data, SQL, atau markup.
- Mengubah `seed.sql`/`schema.sql`.
- Menambah fitur DB baru.

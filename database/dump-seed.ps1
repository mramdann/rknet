# dump-seed.ps1 — regenerasi database/seed.sql dari kondisi DB `dbrknet` terkini.
# Pakai: jalankan dari mana saja -> `powershell -File database\dump-seed.ps1`
# Menghasilkan seed.sql (data-only) yang dijalankan SETELAH schema.sql.
$ErrorActionPreference = 'Stop'

$dump = 'D:\WebServer\xampp82\mysql\bin\mysqldump.exe'
$seed = Join-Path $PSScriptRoot 'seed.sql'
# Urutan tabel aman FK: paket -> pelanggan -> tagihan, sisanya bebas.
$tabel = 'paket', 'pelanggan', 'tagihan', 'prospek', 'area', 'notifikasi', 'pengaturan', 'admin'

$header = @(
    '-- seed.sql - data awal dbrknet (regenerasi dari DB via database/dump-seed.ps1).',
    '-- Jalankan SETELAH schema.sql.',
    'USE dbrknet;',
    'SET FOREIGN_KEY_CHECKS=0;',
    ''
)
# --compact: hanya INSERT (tanpa LOCK/komentar); --complete-insert: sertakan nama kolom.
$body = & $dump -h 127.0.0.1 -P 3382 -u root --no-create-info --complete-insert --skip-extended-insert --compact --no-tablespaces dbrknet @tabel
$footer = @('', 'SET FOREIGN_KEY_CHECKS=1;')

$isi = (($header + $body + $footer) -join "`r`n") + "`r`n"
$utf8 = New-Object System.Text.UTF8Encoding($false)   # UTF-8 tanpa BOM
[System.IO.File]::WriteAllText($seed, $isi, $utf8)
Write-Output "seed.sql diperbarui: $seed"

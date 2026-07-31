# Rebrand RKnet Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Terapkan identitas RKnet secara menyeluruh: teks brand, kelas/var CSS `st-`→`rk-`, logo, nama DB `dbrknet`, dan email `@rknet.id`.

**Architecture:** Perubahan bertarget per kategori (teks, CSS, logo, DB/email, docs) memakai skrip PowerShell (UTF-8 tanpa BOM) dengan pola aman (regex batas-kata untuk CSS), plus edit tertarget. Verifikasi: lint + grep sisa + E2E 39-cek (login admin baru) + BrowserOS.

**Tech Stack:** PHP native, mysqli, MySQL `dbrknet` @ 3382, Bootstrap 5, PowerShell untuk bulk-edit & DB.

## Global Constraints

- Ganti hanya di file aplikasi (php/css/js/sql) — **jangan** sentuh `docs/superpowers/**` (catatan historis) & `.git`.
- CSS: rename token turunan brand saja via **regex batas-kata** (`\b`); jangan rusak `text-start`, `:first-child`, `stat-`, `list-`.
- Tulis file dengan **UTF-8 tanpa BOM** (`[System.Text.UTF8Encoding]::new($false)`).
- Folder/URL menggunakan `/rknet/`; PT Integrasi Jaringan Ekosistem tak diubah.
- Login admin baru: `admin@rknet.id` / `admin123`.
- Lint tiap `.php` disentuh. Commit per task, prefix `rebrand`.
- Verifikasi via PowerShell HTTP + BrowserOS; data DB di-rollback ke seed bila diubah saat uji.

---

### Task 1: Teks brand RKnet

**Files:** `config.php`, `legal.php`, `partials/{footer,features,modal-langganan,navbar,hero,redeem,head}.php`, `admin/login.php`, `admin/dashboard.php`, `admin/paket.php`, `admin/partials/{shell-head,sidebar}.php`, `portal/login.php`, `portal/invoice.php`, `portal/partials/{shell-head,sidebar}.php`, `portal-config.php`

- [ ] **Step 1: Gunakan kata "RKnet" (case-sensitive) di file terdaftar**

`"RKnet"` unik & aman. Nama lengkap brand menjadi "RKnet Indonesia".

```powershell
$root="D:\WebServer\xampp82\htdocs\rknet"
$files=@('config.php','legal.php','partials\footer.php','partials\features.php','partials\modal-langganan.php',
 'partials\navbar.php','partials\hero.php','partials\redeem.php','partials\head.php',
 'admin\login.php','admin\dashboard.php','admin\paket.php','admin\partials\shell-head.php','admin\partials\sidebar.php',
 'portal\login.php','portal\invoice.php','portal\partials\shell-head.php','portal\partials\sidebar.php',
 'portal-config.php')
$u=[System.Text.UTF8Encoding]::new($false)
foreach($f in $files){ $p=Join-Path $root $f; $t=[IO.File]::ReadAllText($p); [IO.File]::WriteAllText($p,$t,$u) }
Write-Output "OK teks brand"
```

- [ ] **Step 2: Lint semua PHP yang disentuh**

```bash
for f in config.php legal.php partials/footer.php partials/features.php partials/modal-langganan.php partials/navbar.php partials/hero.php partials/redeem.php partials/head.php admin/login.php admin/dashboard.php admin/paket.php admin/partials/shell-head.php admin/partials/sidebar.php portal/login.php portal/invoice.php portal/partials/shell-head.php portal/partials/sidebar.php portal-config.php; do /d/WebServer/xampp82/php/php.exe -l "$f"; done
```
Expected: semua "No syntax errors detected".

- [ ] **Step 3: Verifikasi identitas RKnet di file aplikasi**

```bash
grep -rn "RKnet" --include=*.php --include=*.js config.php legal.php partials admin portal portal-config.php assets/js
```
Expected: seluruh keluaran menggunakan identitas RKnet.

- [ ] **Step 4: Commit**

```bash
git add config.php legal.php partials admin portal portal-config.php
git commit -m "rebrand: teks brand RKnet"
```

---

### Task 2: Kelas & variabel CSS st- → rk- + chip logo footer + cache-bust

**Files:** `assets/css/style.css`, `assets/css/portal.css`, semua template `.php` yang memakai kelas tsb, head yang merujuk CSS (`?v=`).

- [ ] **Step 1: Rename token CSS (regex batas-kata) di CSS + template**

```powershell
$root="D:\WebServer\xampp82\htdocs\rknet"
$u=[System.Text.UTF8Encoding]::new($false)
# file: semua php/css di root+admin+portal+partials + 2 css + js; KECUALI docs & database & .git
$targets = Get-ChildItem -Path $root -Recurse -Include *.php,*.css,*.js |
  Where-Object { $_.FullName -notmatch '\\(docs|database|\.git)\\' }
$peta = [ordered]@{
  '--st-'                 = '--rk-'
  '\bbtn-st\b'            = 'btn-rk'
  '\btext-st\b'           = 'text-rk'
  '\bst-navbar\b'         = 'rk-navbar'
  '\bst-hero\b'           = 'rk-hero'
  '\bst-section-soft\b'   = 'rk-section-soft'
  '\bst-badge\b'          = 'rk-badge'
  '\bst-modal-head\b'     = 'rk-modal-head'
  '\bst-footer\b'         = 'rk-footer'
}
foreach($file in $targets){
  $t=[IO.File]::ReadAllText($file.FullName); $asli=$t
  foreach($k in $peta.Keys){ $t=[regex]::Replace($t,$k,$peta[$k]) }
  if($t -ne $asli){ [IO.File]::WriteAllText($file.FullName,$t,$u) }
}
Write-Output "OK rename CSS token"
```

- [ ] **Step 2: Tambah chip putih untuk logo footer di `assets/css/style.css`**

Cari selektor `.footer-logo` bila ada; jika tidak, tambahkan aturan baru (footer berlatar `--rk-blue-dark`, logo JPEG latar-putih perlu chip):
```css
.footer-logo{background:#fff;padding:.35rem .6rem;border-radius:.6rem;display:inline-block}
```
Tambahkan tepat setelah blok `.rk-footer{...}` di style.css.

- [ ] **Step 3: Bump cache-bust `style.css`/`portal.css` `?v` → `?v=6`**

```powershell
$root="D:\WebServer\xampp82\htdocs\rknet"; $u=[System.Text.UTF8Encoding]::new($false)
foreach($f in 'partials\head.php','admin\partials\shell-head.php','portal\partials\shell-head.php'){
  $p=Join-Path $root $f; $t=[IO.File]::ReadAllText($p)
  $t=[regex]::Replace($t,'(style|portal)\.css\?v=\d+','$1.css?v=6')
  [IO.File]::WriteAllText($p,$t,$u)
}
Write-Output "OK cache-bust v6"
```

- [ ] **Step 4: Verifikasi tak ada token rusak & tak ada `st-`/`--st-` tersisa**

```bash
echo "--- token rusak (harus kosong): ---"
grep -rnE "text-rkart|lirk-|firk-|btn-start|--st-|\bbtn-st\b|\btext-st\b|\bst-navbar\b|\bst-hero\b|\bst-footer\b|\bst-badge\b|\bst-modal-head\b|\bst-section-soft\b" --include=*.php --include=*.css --include=*.js assets admin portal partials *.php || echo BERSIH
echo "--- pastikan text-start & first-child utuh: ---"
grep -rn "text-start\|first-child" assets/css/portal.css | head
```
Expected: `BERSIH` untuk token rusak/`st-`; `text-start`/`first-child` masih utuh.

- [ ] **Step 5: Lint template PHP yang berubah (sampel) + commit**

```bash
/d/WebServer/xampp82/php/php.exe -l index.php && /d/WebServer/xampp82/php/php.exe -l admin/dashboard.php && /d/WebServer/xampp82/php/php.exe -l portal/dashboard.php
git add -A ':!docs' ':!database'
git commit -m "rebrand: kelas & var CSS st- -> rk- + chip logo footer + cache-bust v6"
```

---

### Task 3: Logo — rknet.jpeg (utama) & rknet2.jpeg (slot kedua)

**Files:** semua yang merujuk logo utama / `logo-weave.webp` (navbar, footer, sidebar admin & portal, login admin & portal, invoice, legal).

- [ ] **Step 1: Ganti src & alt logo**

```powershell
$root="D:\WebServer\xampp82\htdocs\rknet"; $u=[System.Text.UTF8Encoding]::new($false)
$targets = Get-ChildItem -Path $root -Recurse -Include *.php |
  Where-Object { $_.FullName -notmatch '\\(docs|database|\.git)\\' }
foreach($file in $targets){
  $t=[IO.File]::ReadAllText($file.FullName); $asli=$t
  $t=$t.Replace('logo-weave.webp','rknet2.jpeg')
  $t=$t.Replace('logo-weave.webp','rknet2.jpeg')
  $t=$t.Replace('alt="Weave"','alt="RWS Solution"')
  if($t -ne $asli){ [IO.File]::WriteAllText($file.FullName,$t,$u) }
}
Write-Output "OK logo swap"
```

- [ ] **Step 2: Verifikasi tak ada rujukan logo lama tersisa**

```bash
grep -rn "logo-weave\|alt=\"Weave\"" --include=*.php . | grep -v docs/ || echo "BERSIH (logo)"
```
Expected: `BERSIH`.

- [ ] **Step 3: Lint + commit**

```bash
/d/WebServer/xampp82/php/php.exe -l partials/footer.php && /d/WebServer/xampp82/php/php.exe -l portal/login.php && /d/WebServer/xampp82/php/php.exe -l admin/login.php
git add -A ':!docs' ':!database'
git commit -m "rebrand: ganti logo ke rknet.jpeg (utama) & rknet2.jpeg (slot kedua)"
```

---

### Task 4: Database dbrknet + email @rknet.id

**Files:** `db.php`, `database/schema.sql`, `database/seed.sql`, `database/dump-seed.ps1`, `admin/login.php`, `admin-config.php`, `portal-config.php`.

- [ ] **Step 1: Gunakan `dbrknet` di kode & SQL**

```powershell
$root="D:\WebServer\xampp82\htdocs\rknet"; $u=[System.Text.UTF8Encoding]::new($false)
foreach($f in 'db.php','database\schema.sql','database\seed.sql','database\dump-seed.ps1','admin-config.php','portal-config.php'){
  $p=Join-Path $root $f; $t=[IO.File]::ReadAllText($p); [IO.File]::WriteAllText($p,$t,$u)
}
Write-Output "OK db name"
```

- [ ] **Step 2: Gunakan email `@rknet.id` + nama_situs + paket RKnet di `seed.sql`, dan email di `admin/login.php`**

```powershell
$root="D:\WebServer\xampp82\htdocs\rknet"; $u=[System.Text.UTF8Encoding]::new($false)
$seed=Join-Path $root 'database\seed.sql'; $t=[IO.File]::ReadAllText($seed)
$t=$t.Replace('RKnet Indonesia','RKnet Indonesia').Replace('Mbps RKnet','Mbps RKnet')
[IO.File]::WriteAllText($seed,$t,$u)
$lg=Join-Path $root 'admin\login.php'; $t=[IO.File]::ReadAllText($lg); [IO.File]::WriteAllText($lg,$t,$u)
Write-Output "OK email/seed"
```

- [ ] **Step 3: Buat DB `dbrknet` & import schema + seed**

```powershell
$mysql="D:\WebServer\xampp82\mysql\bin\mysql.exe"; $dir="D:\WebServer\xampp82\htdocs\rknet\database"
Get-Content -Raw "$dir\schema.sql" | & $mysql -h 127.0.0.1 -P 3382 -u root; Write-Output "SCHEMA=$LASTEXITCODE"
Get-Content -Raw "$dir\seed.sql"   | & $mysql -h 127.0.0.1 -P 3382 -u root; Write-Output "SEED=$LASTEXITCODE"
& $mysql -h 127.0.0.1 -P 3382 -u root -D dbrknet -e "SELECT (SELECT COUNT(*) FROM pelanggan) pel,(SELECT email FROM admin WHERE id=1) adm,(SELECT nama_situs FROM pengaturan WHERE id=1) situs,(SELECT nama FROM paket WHERE id=1) pkt;"
```
Expected: `SCHEMA=0`, `SEED=0`, `pel=6`, `adm=admin@rknet.id`, `situs=RKnet Indonesia`, `pkt=Paket 100 Mbps RKnet`.

- [ ] **Step 4: Lint + verifikasi login admin baru**

```powershell
/d/WebServer/xampp82/php/php.exe -l db.php
$d=Invoke-WebRequest "http://localhost:8282/rknet/admin/login.php" -Method POST -Body @{email='admin@rknet.id';kata_sandi='admin123'} -SessionVariable s -UseBasicParsing -ErrorAction SilentlyContinue
Write-Output ("LOGIN_ADMIN_BARU=" + ($d.Content -match 'Selamat datang'))
```
Expected: `LOGIN_ADMIN_BARU=True`.

- [ ] **Step 5: Commit**

```bash
git add db.php database/schema.sql database/seed.sql database/dump-seed.ps1 admin/login.php admin-config.php portal-config.php
git commit -m "rebrand: database dbrknet + email @rknet.id (login admin@rknet.id)"
```

---

### Task 5: Dokumentasi + verifikasi E2E menyeluruh

**Files:** `CLAUDE.md`, `docs/DOKUMENTASI.md`, memori proyek.

- [ ] **Step 1: Perbarui CLAUDE.md & DOKUMENTASI.md**

Gunakan (via editor): "RKnet Indonesia"/"RKnet"; `dbrknet`; kredensial demo admin `admin@rknet.id`; sebut brand dua-logo RKnet + RWS. (Nama brand di judul/naratif.)

- [ ] **Step 2: E2E 39-cek (login admin `admin@rknet.id`)**

Jalankan dua sweep E2E dari sesi sebelumnya dengan `admin@rknet.id` di skrip. Expected: **39/39 PASS**. Rollback data uji ke seed.

- [ ] **Step 3: Verifikasi brand RKnet di aplikasi**

```bash
grep -rin "RKnet" --include=*.php --include=*.css --include=*.js --include=*.sql . | grep -v "docs/"
```
Expected: semua rujukan brand di kode aplikasi menggunakan RKnet.

- [ ] **Step 4: BrowserOS spot-check**

Buka landing (navbar+footer+hero), `portal/login.php` (dua logo RKnet+RWS), admin & portal dashboard. Konfirmasi brand "RKnet", logo tampil, gaya utuh (kelas rk- bekerja).

- [ ] **Step 5: Commit**

```bash
git add CLAUDE.md docs/DOKUMENTASI.md
git commit -m "docs: perbarui dokumentasi untuk rebrand RKnet"
```

---

## Catatan Penutup

Setelah 5 task: brand RKnet menyeluruh (teks, CSS rk-, logo, DB dbrknet, email @rknet.id), E2E 39/39 tetap lulus. Folder `/rknet/` digunakan dan badan hukum PT tak diubah. `rknet.zip` perlu diregenerasi bila ingin dibagikan.

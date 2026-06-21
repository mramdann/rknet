# Starlite Landing Page UI Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun landing page Starlite Indonesia (provider internet FTTH) versi improve/redesign memakai PHP native + Bootstrap 5, gaya "Modern Biru".

**Architecture:** PHP native dengan komponen via `include`. `index.php` meng-include partial per section. Konten (benefit, paket, fitur) disimpan sebagai array di `config.php`; partial meng-loop data. Styling: Bootstrap 5.3 CDN + `assets/css/style.css`. Aset logo & hero memakai gambar asli dari starliteindonesia.com.

**Tech Stack:** PHP 8.2 (XAMPP), Bootstrap 5.3 (CDN), Bootstrap Icons 1.11 (CDN), Google Fonts (Poppins), vanilla JS.

## Global Constraints

- PHP native saja — tanpa framework, tanpa Composer.
- Bootstrap 5.3 via CDN; jangan pakai jQuery (Bootstrap 5 bundle sudah cukup).
- UI saja — form & tombol bersifat tampilan/anchor, belum ada proses backend.
- Dijalankan di XAMPP: `http://localhost/starlite`.
- Semua partial di-include relatif terhadap `__DIR__`.
- Escape output dinamis dengan `htmlspecialchars()`.
- Gaya visual: biru primer `#0B5ED7`, biru tua `#06256E`, dominan putih, kartu `rounded-4`, shadow lembut, font Poppins.

---

### Task 1: Scaffold proyek, config, head, dan skeleton index

**Files:**
- Create: `config.php`
- Create: `partials/head.php`
- Create: `index.php`
- Create: `assets/css/style.css`
- Create: `assets/js/main.js`

**Interfaces:**
- Produces: `$site` (array config global) berisi key: `name`, `phone`, `company`, `address`, `benefits[]`, `packages[]`, `features[]`, `socials[]`. Partial lain membaca `$site`.

- [ ] **Step 1: Buat `config.php`**

```php
<?php
// config.php — sumber konten landing page (UI only)
$site = [
    'name'    => 'Starlite',
    'phone'   => '+62811789111',
    'company' => 'PT Integrasi Jaringan Ekosistem',
    'address' => 'Jalan Tiang Bendera V No.20 Roa Malaka, Tambora, Jakarta Barat',

    'benefits' => [
        ['icon' => 'bi-calendar-check', 'text' => 'Gratis 1 bulan'],
        ['icon' => 'bi-tools',          'text' => 'Gratis biaya instalasi'],
        ['icon' => 'bi-infinity',       'text' => 'Bebas FUP - Internet UNLIMITED'],
        ['icon' => 'bi-router',         'text' => 'Termasuk biaya sewa modem'],
        ['icon' => 'bi-receipt',        'text' => 'Harga sudah termasuk PPN'],
    ],

    'packages' => [
        [
            'name'     => 'Unlimited 30 Mbps',
            'speed'    => '30 Mbps',
            'price'    => 'Rp199.000',
            'period'   => '/bulan',
            'features' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Harga termasuk PPN'],
            'featured' => false,
        ],
        [
            'name'     => 'Unlimited 50 Mbps',
            'speed'    => '50 Mbps',
            'price'    => 'Rp299.000',
            'period'   => '/bulan',
            'features' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Gratis 1 bulan'],
            'featured' => true,
        ],
        [
            'name'     => 'Unlimited 100 Mbps',
            'speed'    => '100 Mbps',
            'price'    => 'Rp399.000',
            'period'   => '/bulan',
            'features' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Prioritas jaringan'],
            'featured' => false,
        ],
    ],

    'features' => [
        ['icon' => 'bi-house-wifi', 'title' => 'Wireless Home Network'],
        ['icon' => 'bi-speedometer2', 'title' => 'High Speed Internet'],
        ['icon' => 'bi-wifi',        'title' => 'Stable Internet Connection'],
        ['icon' => 'bi-broadcast',   'title' => 'Pure Fiber Network'],
    ],

    'socials' => [
        ['icon' => 'bi-instagram', 'url' => '#'],
        ['icon' => 'bi-facebook',  'url' => '#'],
        ['icon' => 'bi-whatsapp',  'url' => 'https://wa.me/62811789111'],
    ],
];
```

- [ ] **Step 2: Buat `partials/head.php`**

```php
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starlite Indonesia — Internet Fiber Unlimited</title>
    <meta name="description" content="Starlite — Internet fiber rumah unlimited, bebas FUP, gratis instalasi.">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
```

- [ ] **Step 3: Buat `index.php` (skeleton dulu, partial section ditambah di task berikutnya)**

```php
<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<?php include __DIR__ . '/partials/head.php'; ?>
<body>
    <!-- navbar  -> Task 2 -->
    <!-- hero     -> Task 3 -->
    <!-- benefits -> Task 4 -->
    <!-- package  -> Task 5 -->
    <!-- redeem   -> Task 6 -->
    <!-- features -> Task 7 -->
    <!-- footer   -> Task 8 -->
    <main class="container py-5"><h1>Starlite — scaffold OK</h1></main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
```

- [ ] **Step 4: Buat `assets/css/style.css` (variabel & base)**

```css
:root{
  --st-blue:#0B5ED7;
  --st-blue-dark:#06256E;
  --st-blue-soft:#E8F0FE;
  --st-cyan:#36C5F0;
}
*{scroll-behavior:smooth}
body{font-family:'Poppins',system-ui,sans-serif;color:#1c2434}
.btn-st{background:var(--st-blue);color:#fff;border-radius:50rem;padding:.6rem 1.5rem;font-weight:600;border:0;transition:.2s}
.btn-st:hover{background:var(--st-blue-dark);color:#fff;transform:translateY(-2px)}
.section{padding:5rem 0}
.text-st{color:var(--st-blue)}
```

- [ ] **Step 5: Buat `assets/js/main.js` (kosong dulu + komentar)**

```js
// main.js — interaksi UI (navbar scroll, dll) ditambah di Task 9
```

- [ ] **Step 6: Cek sintaks PHP**

Run: `php -l config.php && php -l index.php && php -l partials/head.php`
Expected: `No syntax errors detected` untuk ketiga file.

- [ ] **Step 7: Verifikasi di browser**

Buka `http://localhost/starlite` → tampil heading "Starlite — scaffold OK", Bootstrap & font ter-load (cek Network tab tidak ada 404 untuk CSS).

- [ ] **Step 8: Init git & commit**

```bash
git init
printf "docs/superpowers/specs/\n" > /dev/null  # (no ignore needed)
git add .
git commit -m "feat: scaffold starlite landing (config, head, index skeleton)"
```

---

### Task 2: Navbar

**Files:**
- Create: `partials/navbar.php`
- Modify: `index.php` (ganti komentar navbar dengan include)
- Modify: `assets/css/style.css` (style navbar)
- Modify: `assets/img/` (download logo asli)

**Interfaces:**
- Consumes: `$site['name']`.

- [ ] **Step 1: Download logo asli ke `assets/img/`**

```bash
mkdir -p assets/img
curl -L "https://starliteindonesia.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2Flogo-starlite.3ac98462.webp&w=640&q=75" -o assets/img/logo-starlite.webp
curl -L "https://starliteindonesia.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2Flogo-weave.cec1df59.webp&w=828&q=75" -o assets/img/logo-weave.webp
```
Jika URL `_next/image` gagal, fallback ambil sumber webp langsung:
`curl -L "https://starliteindonesia.com/_next/static/media/logo-starlite.3ac98462.webp" -o assets/img/logo-starlite.webp`

- [ ] **Step 2: Buat `partials/navbar.php`**

```php
<nav class="navbar navbar-expand-lg fixed-top st-navbar py-3">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="#">
      <img src="assets/img/logo-starlite.webp" alt="Starlite" height="34">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
        <li class="nav-item"><a class="nav-link" href="#paket">Paket</a></li>
        <li class="nav-item"><a class="nav-link" href="#coverage">Cek Jangkauan</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-person-circle"></i> Login</a></li>
        <li class="nav-item"><a class="btn btn-st" href="#paket">Berlangganan Sekarang <i class="bi bi-arrow-right"></i></a></li>
      </ul>
    </div>
  </div>
</nav>
```

- [ ] **Step 3: Include di `index.php`**

Ganti `<!-- navbar  -> Task 2 -->` dengan:
```php
<?php include __DIR__ . '/partials/navbar.php'; ?>
```

- [ ] **Step 4: Tambah style navbar di `style.css`**

```css
.st-navbar{background:transparent;transition:.3s}
.st-navbar.scrolled{background:#fff;box-shadow:0 4px 24px rgba(6,37,110,.08)}
.st-navbar .nav-link{font-weight:500;color:#1c2434}
.st-navbar .nav-link:hover{color:var(--st-blue)}
body{padding-top:0}
```

- [ ] **Step 5: Cek sintaks & browser**

Run: `php -l partials/navbar.php`
Expected: `No syntax errors detected`.
Browser: navbar tampil fixed di atas, logo asli muncul, tombol CTA biru pill, toggler muncul di mobile (resize ≤ 992px).

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat: add navbar with original logo"
```

---

### Task 3: Hero carousel (banner asli)

**Files:**
- Create: `partials/hero.php`
- Modify: `index.php`
- Modify: `assets/css/style.css`
- Modify: `assets/img/` (download 3 banner)

**Interfaces:** —

- [ ] **Step 1: Download 3 banner hero asli**

```bash
curl -L "https://codify.obs.ap-southeast-4.myhuaweicloud.com/IJE-FTTH_09122024/devel/assets/banner-1780541680774-03101-WEB_desktop.webp" -o assets/img/hero-1.webp
curl -L "https://codify.obs.ap-southeast-4.myhuaweicloud.com/IJE-FTTH_09122024/devel/assets/banner-1744340388153-021010-Desktop_hero_banner_(1).webp" -o assets/img/hero-2.webp
curl -L "https://codify.obs.ap-southeast-4.myhuaweicloud.com/IJE-FTTH_09122024/devel/assets/banner-1747363423438-32310-B_Desktop_hero_banner.webp" -o assets/img/hero-3.webp
```
Verifikasi file > 10KB (`ls -la assets/img/`). Jika gagal/0 byte, catat dan gunakan placeholder gradient sementara.

- [ ] **Step 2: Buat `partials/hero.php`**

```php
<section id="hero" class="st-hero">
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <button data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
      <button data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
      <button data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    </div>
    <div class="carousel-inner">
      <?php foreach (['hero-1','hero-2','hero-3'] as $i => $img): ?>
      <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
        <img src="assets/img/<?= $img ?>.webp" class="d-block w-100 st-hero-img" alt="Banner Starlite <?= $i+1 ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span></button>
    <button class="carousel-control-next" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span></button>
  </div>
</section>
```

- [ ] **Step 3: Include di `index.php`** (ganti `<!-- hero -> Task 3 -->`)

```php
<?php include __DIR__ . '/partials/hero.php'; ?>
```

- [ ] **Step 4: Style hero di `style.css`**

```css
.st-hero{margin-top:0}
.st-hero-img{height:auto;max-height:640px;object-fit:cover}
@media(max-width:768px){.st-hero-img{max-height:360px}}
```

- [ ] **Step 5: Cek & browser**

Run: `php -l partials/hero.php`
Expected: `No syntax errors detected`.
Browser: 3 banner tampil bergantian (auto-slide), indikator & panah berfungsi.

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat: add hero carousel with original banners"
```

---

### Task 4: Benefits

**Files:**
- Create: `partials/benefits.php`
- Modify: `index.php`, `assets/css/style.css`

**Interfaces:**
- Consumes: `$site['benefits']` (array of `['icon','text']`).

- [ ] **Step 1: Buat `partials/benefits.php`**

```php
<section class="section bg-white">
  <div class="container">
    <div class="row g-3 justify-content-center">
      <?php foreach ($site['benefits'] as $b): ?>
      <div class="col-6 col-md-4 col-lg">
        <div class="benefit-card text-center h-100">
          <i class="bi <?= htmlspecialchars($b['icon']) ?>"></i>
          <p class="mb-0 mt-2 fw-500"><?= htmlspecialchars($b['text']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Include di `index.php`** (ganti `<!-- benefits -> Task 4 -->`)

```php
<?php include __DIR__ . '/partials/benefits.php'; ?>
```

- [ ] **Step 3: Style di `style.css`**

```css
.benefit-card{background:var(--st-blue-soft);border-radius:1rem;padding:1.5rem 1rem;transition:.2s}
.benefit-card:hover{transform:translateY(-4px);box-shadow:0 10px 30px rgba(11,94,215,.12)}
.benefit-card i{font-size:2rem;color:var(--st-blue)}
.fw-500{font-weight:500}
```

- [ ] **Step 4: Cek & browser**

Run: `php -l partials/benefits.php`
Expected: `No syntax errors detected`.
Browser: 5 kartu benefit tampil sejajar (desktop), 2 kolom (mobile), ikon biru muncul.

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat: add benefits section"
```

---

### Task 5: Paket Unlimited

**Files:**
- Create: `partials/package.php`
- Modify: `index.php`, `assets/css/style.css`

**Interfaces:**
- Consumes: `$site['packages']` (array of `['name','speed','price','period','features','featured']`).

- [ ] **Step 1: Buat `partials/package.php`**

```php
<section id="paket" class="section st-section-soft">
  <div class="container">
    <div class="text-center mb-5">
      <span class="badge st-badge mb-2">UNLIMITED PACKAGE</span>
      <h2 class="fw-700">Pilih Paket Internet Kamu</h2>
      <p class="text-muted">Semua paket bebas FUP, unlimited, dan harga sudah termasuk PPN.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <?php foreach ($site['packages'] as $p): ?>
      <div class="col-md-6 col-lg-4">
        <div class="package-card h-100 <?= $p['featured'] ? 'featured' : '' ?>">
          <?php if ($p['featured']): ?><span class="badge bg-warning text-dark mb-2">Terpopuler</span><?php endif; ?>
          <h5 class="fw-700"><?= htmlspecialchars($p['name']) ?></h5>
          <div class="display-6 fw-800 text-st"><?= htmlspecialchars($p['price']) ?><small class="fs-6 text-muted fw-400"><?= htmlspecialchars($p['period']) ?></small></div>
          <ul class="list-unstyled my-4">
            <?php foreach ($p['features'] as $f): ?>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-st me-2"></i><?= htmlspecialchars($f) ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="#" class="btn btn-st w-100">Berlangganan Sekarang</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Include di `index.php`** (ganti `<!-- package -> Task 5 -->`)

```php
<?php include __DIR__ . '/partials/package.php'; ?>
```

- [ ] **Step 3: Style di `style.css`**

```css
.st-section-soft{background:linear-gradient(180deg,#fff,var(--st-blue-soft))}
.st-badge{background:var(--st-blue);color:#fff;border-radius:50rem;padding:.4rem 1rem;letter-spacing:1px}
.fw-700{font-weight:700}.fw-800{font-weight:800}
.package-card{background:#fff;border-radius:1.25rem;padding:2rem;box-shadow:0 8px 30px rgba(6,37,110,.06);transition:.25s;border:2px solid transparent}
.package-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(11,94,215,.15)}
.package-card.featured{border-color:var(--st-blue);transform:scale(1.03)}
```

- [ ] **Step 4: Cek & browser**

Run: `php -l partials/package.php`
Expected: `No syntax errors detected`.
Browser: 3 kartu paket, kartu "featured" menonjol (border biru + scale), tombol full-width.

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat: add unlimited package section"
```

---

### Task 6: Redeem voucher

**Files:**
- Create: `partials/redeem.php`
- Modify: `index.php`, `assets/css/style.css`

**Interfaces:** —

- [ ] **Step 1: Buat `partials/redeem.php`**

```php
<section class="section">
  <div class="container">
    <div class="redeem-banner row align-items-center g-4">
      <div class="col-lg-8">
        <h3 class="fw-700 text-white mb-2">Punya Voucher Folaplay?</h3>
        <p class="text-white-50 mb-0">Redeem sekarang & nikmati internet gratis dari Starlite.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="#" class="btn btn-light btn-redeem fw-600">Redeem Sekarang <i class="bi bi-ticket-perforated"></i></a>
      </div>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Include di `index.php`** (ganti `<!-- redeem -> Task 6 -->`)

```php
<?php include __DIR__ . '/partials/redeem.php'; ?>
```

- [ ] **Step 3: Style di `style.css`**

```css
.redeem-banner{background:linear-gradient(120deg,var(--st-blue),var(--st-blue-dark));border-radius:1.5rem;padding:3rem 2.5rem;box-shadow:0 20px 50px rgba(6,37,110,.25)}
.btn-redeem{border-radius:50rem;padding:.7rem 1.6rem;color:var(--st-blue)}
.btn-redeem:hover{color:var(--st-blue-dark)}
```

- [ ] **Step 4: Cek & browser**

Run: `php -l partials/redeem.php`
Expected: `No syntax errors detected`.
Browser: banner gradient biru dengan teks putih + tombol redeem terang.

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat: add redeem voucher banner"
```

---

### Task 7: Features (4 ikon)

**Files:**
- Create: `partials/features.php`
- Modify: `index.php`, `assets/css/style.css`

**Interfaces:**
- Consumes: `$site['features']` (array of `['icon','title']`).

- [ ] **Step 1: Buat `partials/features.php`**

```php
<section class="section bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-700">Kenapa Pilih Starlite?</h2>
    </div>
    <div class="row g-4 text-center">
      <?php foreach ($site['features'] as $f): ?>
      <div class="col-6 col-md-3">
        <div class="feature-item h-100">
          <div class="feature-ico"><i class="bi <?= htmlspecialchars($f['icon']) ?>"></i></div>
          <h6 class="fw-600 mt-3"><?= htmlspecialchars($f['title']) ?></h6>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Include di `index.php`** (ganti `<!-- features -> Task 7 -->`)

```php
<?php include __DIR__ . '/partials/features.php'; ?>
```

- [ ] **Step 3: Style di `style.css`**

```css
.feature-ico{width:84px;height:84px;margin:0 auto;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--st-blue-soft);transition:.25s}
.feature-ico i{font-size:2.2rem;color:var(--st-blue)}
.feature-item:hover .feature-ico{background:var(--st-blue);transform:translateY(-5px)}
.feature-item:hover .feature-ico i{color:#fff}
```

- [ ] **Step 4: Cek & browser**

Run: `php -l partials/features.php`
Expected: `No syntax errors detected`.
Browser: 4 ikon lingkaran, hover berubah biru, teks judul muncul.

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat: add features section"
```

---

### Task 8: Footer

**Files:**
- Create: `partials/footer.php`
- Modify: `index.php`, `assets/css/style.css`

**Interfaces:**
- Consumes: `$site['company']`, `$site['address']`, `$site['phone']`, `$site['socials']`, `$site['name']`.

- [ ] **Step 1: Buat `partials/footer.php`**

```php
<footer class="st-footer text-white pt-5 pb-4">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5">
        <img src="assets/img/logo-starlite.webp" alt="Starlite" height="36" class="mb-3 footer-logo">
        <p class="text-white-50 mb-1"><?= htmlspecialchars($site['company']) ?></p>
      </div>
      <div class="col-lg-4">
        <h6 class="fw-600">Address</h6>
        <p class="text-white-50"><?= htmlspecialchars($site['address']) ?></p>
        <h6 class="fw-600 mt-3">Phone</h6>
        <a class="text-white text-decoration-none" href="tel:<?= htmlspecialchars($site['phone']) ?>"><?= htmlspecialchars($site['phone']) ?></a>
      </div>
      <div class="col-lg-3">
        <h6 class="fw-600">Social Media</h6>
        <div class="d-flex gap-2 mb-3">
          <?php foreach ($site['socials'] as $s): ?>
          <a href="<?= htmlspecialchars($s['url']) ?>" class="social-ico"><i class="bi <?= htmlspecialchars($s['icon']) ?>"></i></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <hr class="border-light opacity-25 my-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
      <small class="text-white-50">Copyright © 2025 <?= htmlspecialchars($site['name']) ?> Indonesia.</small>
      <div class="d-flex gap-3">
        <a href="#" class="text-white-50 text-decoration-none small">Terms &amp; Conditions</a>
        <a href="#" class="text-white-50 text-decoration-none small">Privacy Policy</a>
        <a href="#" class="text-white-50 text-decoration-none small">Refund Policy</a>
      </div>
    </div>
  </div>
</footer>
```

- [ ] **Step 2: Include di `index.php`**

Ganti `<!-- footer -> Task 8 -->` dengan include footer, lalu **hapus** baris placeholder `<main class="container py-5">...</main>`.
```php
<?php include __DIR__ . '/partials/footer.php'; ?>
```

- [ ] **Step 3: Style di `style.css`**

```css
.st-footer{background:var(--st-blue-dark)}
.footer-logo{filter:brightness(0) invert(1)}
.social-ico{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:#fff;transition:.2s}
.social-ico:hover{background:var(--st-blue);color:#fff}
```

- [ ] **Step 4: Cek & browser**

Run: `php -l partials/footer.php`
Expected: `No syntax errors detected`.
Browser: footer biru tua, logo putih (inverted), alamat/telepon/sosmed & link legal tampil; placeholder "scaffold OK" sudah hilang.

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat: add footer"
```

---

### Task 9: Polish — navbar scroll JS, spacing hero, responsif final

**Files:**
- Modify: `assets/js/main.js`
- Modify: `assets/css/style.css`

**Interfaces:** —

- [ ] **Step 1: Tambah efek navbar scroll di `assets/js/main.js`**

```js
// main.js — interaksi UI
const nav = document.querySelector('.st-navbar');
const onScroll = () => {
  if (window.scrollY > 40) nav.classList.add('scrolled');
  else nav.classList.remove('scrolled');
};
window.addEventListener('scroll', onScroll);
onScroll();
```

- [ ] **Step 2: Pastikan navbar fixed tidak menutupi hero — tambah di `style.css`**

```css
.st-navbar.scrolled .nav-link{color:#1c2434}
@media(max-width:991px){
  .st-navbar{background:#fff;box-shadow:0 4px 24px rgba(6,37,110,.08)}
  .navbar-collapse{padding-top:.5rem}
}
```

- [ ] **Step 3: Verifikasi responsif menyeluruh**

Buka `http://localhost/starlite`:
- Desktop: scroll dari atas → navbar transparan menjadi putih + shadow.
- Resize ≤ 768px: hamburger berfungsi, semua section rapi (benefit 2 kolom, paket menumpuk, footer menumpuk), tidak ada horizontal scroll.

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "feat: add navbar scroll effect and responsive polish"
```

---

## Self-Review

- **Spec coverage:** Navbar (T2), Hero carousel banner asli (T3), Benefits 5 poin (T4), Paket Unlimited (T5), Redeem Folaplay (T6), 4 Features (T7), Footer lengkap (T8), gaya Modern Biru + responsif (T1/T9), logo asli (T2). Semua section spec tercakup.
- **Placeholder scan:** Tidak ada TODO/TBD; setiap step berisi kode lengkap.
- **Type consistency:** Key array `$site` konsisten antara `config.php` (T1) dan konsumennya (T4–T8): `benefits[icon,text]`, `packages[name,speed,price,period,features,featured]`, `features[icon,title]`, `socials[icon,url]`.

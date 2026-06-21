# Starlite Indonesia — Landing Page UI (Improve / Redesign)

**Date:** 2026-06-22
**Status:** Approved design
**Scope:** Landing page (home) only — UI/frontend only, no backend logic yet.

## Goal

Membangun ulang landing page Starlite Indonesia (provider internet FTTH) versi
"improve/redesign" menggunakan **PHP native + Bootstrap 5**, dengan gaya visual
**Modern Biru** (bersih, modern, mempertahankan identitas biru brand).

Referensi konten & aset: https://starliteindonesia.com/

## Constraints & Decisions

- **Fidelity:** Improve/redesign — ambil struktur & konten asli, UI ditingkatkan.
- **Scope:** Landing page (home) saja. Halaman lain (Login, Cek Jangkauan, Redeem) menyusul.
- **Backend:** UI saja. Form belum diproses; tombol bersifat tampilan/anchor.
- **Stack:** PHP native (komponen via `include`), Bootstrap 5.3 (CDN), Bootstrap Icons, CSS custom.
- **Aset:** Logo Starlite & Weave + 3 banner hero **memakai gambar asli** (di-download ke `assets/img/`).
- **Server:** Dijalankan via XAMPP — `http://localhost/starlite`.

## Arsitektur & Struktur File

```
starlite/
├── index.php                 # halaman utama, meng-include partials
├── config.php                # data konten (benefit, paket, fitur) sebagai array PHP
├── partials/
│   ├── head.php              # <head>, meta, Bootstrap + CSS custom + Google Fonts
│   ├── navbar.php            # logo, Cek Jangkauan, Login, CTA Berlangganan
│   ├── hero.php              # Bootstrap Carousel (3 banner asli) + overlay CTA
│   ├── benefits.php          # 5 poin benefit (loop dari config)
│   ├── package.php           # kartu paket Unlimited (loop dari config)
│   ├── redeem.php            # banner Redeem voucher Folaplay
│   ├── features.php          # 4 ikon fitur (loop dari config)
│   └── footer.php            # info perusahaan, alamat, kontak, sosmed, legal
└── assets/
    ├── css/style.css         # custom styling di atas Bootstrap
    ├── js/main.js            # interaksi kecil (navbar scroll, carousel)
    └── img/                  # logo & banner hero (aset asli)
```

**Prinsip:** Konten (benefit, paket, fitur) disimpan sebagai array di `config.php`;
partial meng-loop data tersebut. Memisahkan konten dari markup → mudah diubah dan
menjadi jembatan menuju backend nanti.

## Section Landing Page

1. **Navbar** — Logo Starlite + Weave; link "Cek Jangkauan"; tombol "Login";
   CTA "Berlangganan Sekarang". Sticky; transparan → solid saat scroll.
2. **Hero** — Bootstrap Carousel 3 banner asli, overlay teks + tombol CTA.
3. **Benefits** — 5 poin: Gratis 1 bulan; Gratis biaya instalasi; Bebas FUP –
   Internet UNLIMITED; Termasuk biaya sewa modem; Harga sudah termasuk PPN. Kartu + ikon.
4. **Paket Unlimited** — Kartu paket (harga, fitur, tombol Berlangganan). Data dari config.
5. **Redeem** — Banner ajakan redeem voucher Folaplay + tombol "Redeem Sekarang".
6. **Features** — 4 ikon: Wireless Home Network; High Speed Internet; Stable
   Internet Connection; Pure Fiber Network. Grid.
7. **Footer** — PT Integrasi Jaringan Ekosistem; alamat (Jalan Tiang Bendera V No.20,
   Roa Malaka, Tambora, Jakarta Barat); telepon (+62811789111); sosmed; link legal
   (Terms & Conditions, Privacy Policy, Refund Policy).

## Sistem Visual (Modern Biru)

- **Framework:** Bootstrap 5.3 (CDN) + Bootstrap Icons + `assets/css/style.css`.
- **Palet:** Biru primer (~`#0B5ED7`), biru tua (~`#06256E`) untuk gradient, aksen
  biru muda/cyan, netral abu-abu lembut, dominan putih.
- **Tipografi:** Google Fonts (Poppins/Inter) — heading tebal, body bersih.
- **Komponen:** kartu `rounded-4`, shadow lembut, gradient halus (hero & CTA),
  hover transition, spacing lega.
- **Responsif:** mobile-first dengan grid Bootstrap.

## Aset Asli (sumber)

- Logo Starlite: `/_next/static/media/logo-starlite.*.webp`
- Logo Weave: `/_next/static/media/logo-weave.*.webp`
- Banner hero (3) dari CDN Huawei OBS (`codify.obs.ap-southeast-4.myhuaweicloud.com/IJE-FTTH_09122024/...`).

## Verifikasi

UI statis → verifikasi manual: buka `http://localhost/starlite` di browser,
cek render tiap section, carousel berjalan, dan responsif (desktop & mobile).

## Out of Scope (sekarang)

- Pemrosesan form (cek jangkauan, login, redeem).
- Halaman selain landing page.
- Database / autentikasi.

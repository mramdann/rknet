# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A redesigned/rebuilt UI for **Starlite Indonesia** (a fiber/FTTH ISP, brands "Starlite" + "Weave"). Plain **PHP (native, no framework, no Composer) + Bootstrap 5** served from XAMPP. It is **UI-only**: every form, button, and "action" is a visual mock backed by dummy data — there is no database, authentication, or backend processing. Visual style is "Modern Biru" (blue, rounded cards, soft shadows, Poppins).

## Running & checking

- **Serve:** XAMPP Apache, **port 8282**. Open `http://localhost:8282/starlite/`. (The port is non-default — it's set in `apache/conf/httpd.conf` as `Listen 8282`.)
- **There is no build step, no test suite, no linter config.** The only automated check is PHP syntax linting:
  ```bash
  /d/WebServer/xampp82/php/php.exe -l path/to/file.php
  ```
  Use the full path to the XAMPP PHP binary (`php` is not on PATH). Lint every `.php` you touch.
- **Verification = open the page in a browser** and confirm it renders and behaves. There is nothing to assert against in code.
- The Bash tool is sandboxed away from localhost; check Apache/ports by reading config, and verify pages through a browser, not `curl localhost`.

## Three areas, one shared style

The app is three parallel sections plus standalone pages, each with its own `*-config.php` holding all dummy data:

| Area | Entry | Data file | Notes |
|------|-------|-----------|-------|
| **Landing** | `index.php` | `config.php` (`$site`) | Sections in `partials/` (navbar, hero, benefits, package, redeem, features, coverage, footer) + modals (`modal-langganan.php`, `modal-redeem.php`). |
| **Customer portal** | `portal/*.php` | `portal-config.php` | Login, dashboard, transaksi, invoice, paket, profil. Authenticated-looking area. |
| **Admin portal** | `admin/*.php` | `admin-config.php` | Login, dashboard, pelanggan, paket, transaksi. Manages the same entities. |
| Standalone | `cek-jangkauan.php`, `legal.php` | `cek-jangkauan-config.php` | Coverage check (with Leaflet map) and legal docs. |

`helpers.php` holds `formatRupiah()` and `badgeStatus()`, shared by both portal and admin configs via `require_once` (guarded with `function_exists`). Put any cross-area helper here, not in a single config.

## Page composition pattern (portal & admin)

Pages are assembled from PHP includes, not a template engine. A portal/admin page looks like:

```php
require __DIR__ . '/../portal-config.php';   // or admin-config.php
$judulHalaman = 'Dashboard';   // shown in <title> and topbar
$menuAktif    = 'dashboard';   // which sidebar item is highlighted
include __DIR__ . '/partials/shell-head.php';   // <head>
include __DIR__ . '/partials/shell-open.php';   // <body>, sidebar, topbar, opens content
//   ... page content ...
include __DIR__ . '/partials/shell-close.php';  // closes layout + offcanvas + scripts
```

`portal/partials/` and `admin/partials/` each have their own copy of this shell (`shell-head`, `sidebar`, `topbar`, `shell-open`, `shell-close`). They share the visual styling in `assets/css/portal.css`. Login pages are standalone (no shell). The landing uses a simpler pattern (`partials/head.php` + section includes in `index.php`).

## CSS / JS cache-busting — IMPORTANT

Stylesheet/script `<link>`/`<script>` tags carry a manual version query, e.g. `style.css?v=4`, `cek-jangkauan.js?v=4`. The dev browser aggressively caches these files, so **after editing `assets/css/*.css` or `assets/js/*.js`, bump the `?v=N` everywhere that references it** or the change won't load. Different heads reference different versions (e.g. `partials/head.php`, `cek-jangkauan.php`, `admin/partials/shell-head.php` each have their own `?v=`).

## Conventions

- **Bahasa Indonesia for code:** domain variable/function names and all comments are written in Indonesian (`$pelanggan`, `$paketAktif`, `formatRupiah()`, `// hitung total tagihan`). Framework keywords stay English. Match this in any new code.
- **Dummy data lives in `*-config.php`** as PHP arrays; partials loop over them. To change content (packages, customers, areas), edit the config, not the markup.
- **Escape dynamic output** with `htmlspecialchars()`.
- Bootstrap 5.3 + Bootstrap Icons + Leaflet are loaded from CDN; there are no local copies and no package manager.

## External services (used client-side, no API keys)

`cek-jangkauan.php` mirrors the real site's coverage flow and depends on three external services from JS:
- **Leaflet 1.9.4 + OpenStreetMap tiles** — the draggable location-pin map.
- **Nominatim** (`nominatim.openstreetmap.org`) — address autocomplete & map search geocoding.
- **emsifa wilayah API** (`emsifa.com/api-wilayah-indonesia`) — cascading Provinsi→Kota→Kecamatan→Kelurahan dropdowns.

Coverage "in/out of range" is a dummy string match against covered-city keywords from `cek-jangkauan-config.php`; OTP and form submits are mocked.

## Specs & plans

Design specs and implementation plans for completed/ongoing work live in `docs/superpowers/specs/` and `docs/superpowers/plans/`. Check there before redesigning a feature.

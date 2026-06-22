<?php
// aksi.php — token CSRF & flash message untuk aksi tulis admin.
require_once __DIR__ . '/auth.php';   // mulaiSesi()

function tokenCsrf(): string
{
    mulaiSesi();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function cekCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    mulaiSesi();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('CSRF token tidak valid.');
    }
}

function setFlash(string $tipe, string $pesan): void
{
    mulaiSesi();
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function tampilFlash(): void
{
    mulaiSesi();
    if (empty($_SESSION['flash'])) {
        return;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $tipe = $f['tipe'] === 'danger' ? 'danger' : 'success';
    echo '<div class="alert alert-' . $tipe . ' alert-dismissible fade show" role="alert">'
       . htmlspecialchars($f['pesan'])
       . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>';
}

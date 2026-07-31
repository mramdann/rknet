<?php
// auth.php — sesi, login/logout, dan guard untuk admin & pelanggan.

function mulaiSesi(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function loginAdmin(int $id): void
{
    mulaiSesi();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $id;
}

function loginPelanggan(string $id): void
{
    mulaiSesi();
    session_regenerate_id(true);
    $_SESSION['pelanggan_id'] = $id;
}

function idAdminSaatIni(): ?int
{
    mulaiSesi();
    return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
}

function idPelangganSaatIni(): ?string
{
    mulaiSesi();
    return $_SESSION['pelanggan_id'] ?? null;
}

function wajibLoginAdmin(): void
{
    if (idAdminSaatIni() === null) {
        header('Location: login.php');
        exit;
    }
}

function wajibLoginPelanggan(): void
{
    $id = idPelangganSaatIni();
    if ($id === null) {
        header('Location: login.php');
        exit;
    }

    if (!function_exists('kueriSatu')) {
        require_once __DIR__ . '/db.php';
    }
    $pelanggan = kueriSatu("SELECT status FROM pelanggan WHERE id = ?", [$id]);
    if ($pelanggan === null || $pelanggan['status'] !== 'aktif') {
        unset($_SESSION['pelanggan_id']);
        $status = in_array($pelanggan['status'] ?? '', ['pending', 'nonaktif'], true)
            ? $pelanggan['status']
            : 'sesi';
        header('Location: login.php?status=' . rawurlencode($status));
        exit;
    }
}

function logoutAdmin(): void
{
    mulaiSesi();
    unset($_SESSION['admin_id']);
}

function logoutPelanggan(): void
{
    mulaiSesi();
    unset($_SESSION['pelanggan_id']);
}

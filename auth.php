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
    $_SESSION['admin_id'] = $id;
}

function loginPelanggan(string $id): void
{
    mulaiSesi();
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
    if (idPelangganSaatIni() === null) {
        header('Location: login.php');
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

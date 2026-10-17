<?php
/**
 * includes/session.php
 * Sesi PHP, token CSRF, dan pesan notifikasi sekali pakai (flash).
 */

declare(strict_types=1);

/* ==========================================================================
 * SESI, TOKEN CSRF, DAN PESAN NOTIFIKASI (FLASH)
 * ========================================================================== */

function mulaiSesi(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function ambilTokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function tokenCsrfValid(string $token): bool
{
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function aturFlash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

/**
 * Ambil pesan notifikasi sekali pakai, lalu hapus dari sesi.
 */
function ambilFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

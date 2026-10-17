<?php
/**
 * includes/helpers.php
 * Konstanta aplikasi dan fungsi bantu umum (escape output, baca input, filter).
 */

declare(strict_types=1);

/* ==========================================================================
 * KONSTANTA & PENGATURAN
 * ========================================================================== */

const JUDUL_MAKS = 100;
const DAFTAR_FILTER = ['semua', 'belum', 'selesai'];

date_default_timezone_set('Asia/Jakarta');

/* ==========================================================================
 * HELPER UMUM
 * ========================================================================== */

/**
 * Escape teks sebelum dicetak ke HTML (mencegah XSS).
 */
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

/**
 * Ambil nilai teks dari $_POST / $_GET secara aman.
 * Mengembalikan string kosong jika kunci tidak ada atau bukan string.
 */
function inputTeks(array $sumber, string $kunci): string
{
    $nilai = $sumber[$kunci] ?? '';

    return is_string($nilai) ? trim($nilai) : '';
}

/**
 * Ubah nilai filter apa pun menjadi salah satu nilai yang diizinkan.
 */
function normalisasiFilter(string $filter): string
{
    return in_array($filter, DAFTAR_FILTER, true) ? $filter : 'semua';
}

/**
 * Filter aktif yang dibaca dari URL (?filter=...) via metode GET.
 */
function ambilFilterAktif(): string
{
    return normalisasiFilter(inputTeks($_GET, 'filter'));
}

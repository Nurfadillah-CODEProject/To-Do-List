<?php
/**
 * config.php
 * Pengaturan koneksi database. Nilai bawaan sesuai XAMPP
 * (pengguna "root", tanpa kata sandi).
 *
 * Jika MySQL Anda memakai kredensial lain, JANGAN ubah file ini. Buat file
 * config.local.php di folder yang sama (sudah diabaikan oleh Git) yang
 * mengembalikan array dengan kunci yang ingin diganti, contoh:
 *
 *   <?php
 *   return ['pengguna' => 'admin', 'sandi' => 'rahasia'];
 */

declare(strict_types=1);

$konfigurasi = [
    'host' => 'localhost',
    'port' => 3306,
    'nama_db' => 'todolist_db',
    'pengguna' => 'root',
    'sandi' => '',
    'charset' => 'utf8mb4',
];

if (is_file(__DIR__ . '/config.local.php')) {
    $konfigurasi = array_merge($konfigurasi, require __DIR__ . '/config.local.php');
}

return $konfigurasi;

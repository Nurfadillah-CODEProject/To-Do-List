-- ============================================================================
-- database/todolist_db.sql
-- Skema database aplikasi To-Do List (MySQL / MariaDB, cocok untuk XAMPP).
--
-- Cara pakai:
--   1. Nyalakan Apache dan MySQL di XAMPP Control Panel.
--   2. Buka http://localhost/phpmyadmin
--   3. Klik tab "Import", pilih file ini, lalu klik "Import".
--
-- File ini aman dijalankan berulang kali (tidak menimpa data yang sudah ada).
-- Tabel dibuat kosong, tanpa data contoh.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS todolist_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE todolist_db;

-- Tabel tugas: satu baris = satu tugas.
CREATE TABLE IF NOT EXISTS tugas (
    id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    judul   VARCHAR(100) NOT NULL,
    selesai TINYINT(1)   NOT NULL DEFAULT 0,                  -- 0 = belum, 1 = selesai
    dibuat  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_selesai (selesai)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Tabel dibiarkan kosong: daftar tugas baru muncul setelah pengguna menambahkannya.

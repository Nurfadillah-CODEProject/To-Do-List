# To-Do List PHP + MySQL

Aplikasi web daftar tugas yang dibangun dengan **PHP**, **MySQL (PDO)**, **Bootstrap 5**, dan **struktur data array**. Tugas disimpan permanen di tabel MySQL, lalu dibaca ke dalam array PHP untuk diproses dan ditampilkan.

| | |
|---|---|
| **Nama** | Alfian Nurfadillah |
| **NPM** | 40621100095 |

## Fitur

- Menambah tugas baru melalui form input (dengan validasi)
- Mengedit judul tugas lewat modal Bootstrap (aksi **POST**)
- Menandai tugas selesai / belum dengan ikon centang (aksi **POST**)
- Menghapus tugas dengan ikon hapus dan dialog konfirmasi (aksi **POST**)
- Dua kotak statistik **Tugas Selesai** dan **Tugas Belum Selesai** yang juga berfungsi sebagai filter (aksi **GET**, `?filter=...`)
- Notifikasi toast setelah setiap aksi dan animasi saat daftar tugas muncul
- Daftar tugas kosong saat pertama kali dibuka (tanpa data contoh)
- Kolom input tidak memunculkan riwayat atau saran pengetikan browser (`autocomplete="off"`)
- Data tersimpan di database MySQL, sehingga tetap ada walaupun browser atau server dimatikan

## Teknologi

- PHP 8.1 atau lebih baru dengan ekstensi `pdo_mysql` dan `mbstring` (sudah aktif di XAMPP)
- MySQL / MariaDB, diakses lewat **PDO** dengan **prepared statement**
- Bootstrap 5.3 dan Bootstrap Icons sebagai kerangka tampilan
- Animate.css (animasi muncul), SweetAlert2 (dialog konfirmasi dan notifikasi toast), dan font Plus Jakarta Sans
- Tema warna "Stone & Teal" (netral hangat dengan satu aksen teal) yang semua warnanya diatur lewat variabel CSS di bagian atas `assets/css/style.css`
- Semua pustaka tampilan dimuat via CDN sehingga perlu koneksi internet; tanpa SweetAlert2 aplikasi tetap berjalan dengan alert Bootstrap dan `confirm()` bawaan browser

## Struktur Folder

```
todolist/
├── index.php                  # Halaman utama (alur program + template HTML)
├── config.php                 # Pengaturan koneksi database (bawaan XAMPP)
├── includes/
│   ├── helpers.php            # Konstanta + fungsi bantu umum
│   ├── session.php            # Sesi, token CSRF, pesan notifikasi (flash)
│   ├── database.php           # Koneksi PDO + operasi SQL (CRUD)
│   ├── logic.php              # Validasi, filter, statistik, pemrosesan aksi POST
│   └── views.php              # Fungsi tampilan (HTML + komponen Bootstrap)
├── assets/
│   ├── css/style.css          # CSS tambahan di atas Bootstrap
│   └── js/app.js              # JavaScript halaman (toast, konfirmasi hapus, modal edit)
├── database/
│   └── todolist_db.sql        # Skema database (tabel kosong, tanpa data contoh)
├── .gitignore
└── README.md
```

## Cara Menjalankan

1. Pasang **XAMPP** lalu nyalakan modul **Apache** dan **MySQL** di XAMPP Control Panel.
2. Salin seluruh folder proyek ke `C:\xampp\htdocs\todolist`.
3. Buat database: buka **http://localhost/phpmyadmin**, klik tab **Import**, pilih file `database/todolist_db.sql`, lalu klik **Import**. Tabel yang terbentuk kosong.
4. Buka browser dan akses **http://localhost/todolist**

Pengaturan bawaan di `config.php` adalah host `localhost`, pengguna `root`, tanpa kata sandi, dan database `todolist_db`. Jika MySQL Anda memakai kredensial lain, buat file `config.local.php` (tidak ikut di-commit ke Git):

```php
<?php
return ['pengguna' => 'admin', 'sandi' => 'rahasia'];
```

### Jika muncul pesan "Database belum siap"

| Pesan | Penyebab dan solusi |
|---|---|
| MySQL belum menyala | Nyalakan modul MySQL di XAMPP Control Panel |
| Database belum dibuat / Tabel 'tugas' belum ada | Import `database/todolist_db.sql` lewat phpMyAdmin |
| Username atau password MySQL salah | Periksa `config.php` atau buat `config.local.php` |

## Struktur Database

Database `todolist_db`, tabel `tugas`:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `INT UNSIGNED`, primary key, auto increment | Identitas tugas |
| `judul` | `VARCHAR(100)`, not null | Judul tugas |
| `selesai` | `TINYINT(1)`, default `0` | `0` = belum, `1` = selesai |
| `dibuat` | `TIMESTAMP`, default waktu sekarang | Waktu tugas dibuat |

## Struktur Data Array

Setiap baris tabel dibaca menjadi array asosiatif, dan seluruh tugas dikumpulkan dalam satu array indeks. Array inilah yang dipakai untuk filter, statistik, dan tampilan:

```php
$daftar = [
    ['id' => 1, 'judul' => 'Contoh tugas A', 'selesai' => false, 'dibuat' => '2026-10-17 08:30:00'],
    ['id' => 3, 'judul' => 'Contoh tugas B', 'selesai' => true,  'dibuat' => '2026-10-17 09:15:00'],
];
```

## Daftar Fungsi (folder `includes/`)

| File | Fungsi | Tugas |
|---|---|---|
| `helpers.php` | `e()` | Escape teks sebelum dicetak ke HTML (anti-XSS) |
| | `inputTeks()` | Membaca nilai teks dari `$_POST` / `$_GET` dengan aman |
| | `normalisasiFilter()` / `ambilFilterAktif()` | Membatasi filter ke nilai yang diizinkan, membaca `?filter=` |
| `session.php` | `mulaiSesi()` | Memulai sesi PHP |
| | `ambilTokenCsrf()` / `tokenCsrfValid()` | Membuat dan memeriksa token CSRF |
| | `aturFlash()` / `ambilFlash()` | Pesan notifikasi sekali pakai |
| `database.php` | `koneksiDatabase()` | Membuat koneksi PDO ke MySQL |
| | `jalankanQuery()` | Menjalankan satu query dengan prepared statement |
| | `pesanGalatDatabase()` | Mengubah galat database menjadi petunjuk yang mudah dipahami |
| | `muatTugas()` | `SELECT` seluruh tugas ke dalam array |
| | `adaTugas()` | Memeriksa apakah id tugas ada |
| | `tambahTugas()` / `ubahJudul()` / `ubahStatus()` / `hapusTugas()` | `INSERT` / `UPDATE` / `UPDATE` / `DELETE` |
| `logic.php` | `validasiJudul()` / `ambilJudulValid()` | Memvalidasi input judul |
| | `filterTugas()` | Menyaring array berdasarkan status |
| | `hitungStatistik()` | Menghitung total, selesai, dan belum dari array |
| | `pastikanTugasAda()` | Memeriksa id tugas sebelum diubah atau dihapus |
| | `prosesAksi()` / `alihkanKeIndeks()` | Menangani aksi POST lalu redirect (pola PRG) |
| `views.php` | `fieldTersembunyi()` | Input tersembunyi yang dipakai ulang semua form |
| | `tampilkanStatistik()` / `tampilkanKosong()` | Kotak statistik dan tampilan kosong |
| | `tampilkanDaftar()` / `tampilkanItem()` | Mencetak daftar tugas |
| | `tampilkanModalEdit()` | Modal edit tugas |
| | `tampilkanHalamanGalat()` | Halaman galat database yang ramah |

## Best Practice yang Diterapkan

- **Standar penulisan PSR-12**: indentasi 4 spasi, nama fungsi `camelCase`, type hint, dan `declare(strict_types=1)`
- **Fungsi reusable** dan tanpa duplikasi kode; setiap fungsi punya satu tanggung jawab, dan setiap file di `includes/` mewakili satu kelompok tanggung jawab
- **Pemisahan berkas**: PHP, CSS, dan JavaScript berada di file terpisah; `app.js` murni JavaScript dan membaca data PHP lewat atribut `data-*`
- **Anti SQL injection**: semua query memakai PDO prepared statement, tidak ada input pengguna yang digabung langsung ke string SQL
- **Keamanan lain**: output di-escape dengan `htmlspecialchars` (anti-XSS), token **CSRF** pada setiap form POST, dan input divalidasi
- **Penanganan galat**: PDO dalam mode exception; detail teknis hanya dicatat ke log server, pengguna melihat petunjuk yang ramah
- **Kredensial aman**: pengaturan pribadi ditaruh di `config.local.php` yang diabaikan Git
- **Metode HTTP yang tepat**: perubahan data memakai **POST**, penyaringan tampilan memakai **GET**
- **Pola Post/Redirect/Get**: refresh halaman tidak mengirim ulang form

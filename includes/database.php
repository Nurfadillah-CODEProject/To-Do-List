<?php
/**
 * includes/database.php
 * Semua urusan database: koneksi PDO, penjalan query, pesan galat,
 * dan operasi SQL untuk tabel `tugas` (lihat database/todolist_db.sql).
 */

declare(strict_types=1);

/* ==========================================================================
 * KONEKSI DATABASE (PDO MySQL)
 * ========================================================================== */

/**
 * Buat koneksi PDO ke MySQL memakai pengaturan di config.php.
 * Galat koneksi dilempar sebagai PDOException dan ditangani di index.php.
 */
function koneksiDatabase(): PDO
{
    $konfig = require __DIR__ . '/../config.php';

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $konfig['host'],
        $konfig['port'],
        $konfig['nama_db'],
        $konfig['charset']
    );

    return new PDO($dsn, $konfig['pengguna'], $konfig['sandi'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

/**
 * Jalankan satu query dengan prepared statement (anti SQL injection).
 * Semua nilai dari pengguna HARUS lewat $parameter, bukan digabung ke string SQL.
 */
function jalankanQuery(PDO $pdo, string $sql, array $parameter = []): PDOStatement
{
    $pernyataan = $pdo->prepare($sql);
    $pernyataan->execute($parameter);

    return $pernyataan;
}

/**
 * Ubah galat database menjadi petunjuk yang mudah dipahami pengguna.
 * Detail teknis tidak ditampilkan di layar, hanya dicatat ke log server.
 */
function pesanGalatDatabase(PDOException $galat): string
{
    $sqlState = (string) $galat->getCode();
    $kodeDriver = (int) ($galat->errorInfo[1] ?? $galat->getCode());
    $pesan = $galat->getMessage();

    if ($kodeDriver === 1146 || $sqlState === '42S02' || str_contains($pesan, "doesn't exist")) {
        return "Tabel 'tugas' belum ada. Import file database/todolist_db.sql lewat phpMyAdmin.";
    }
    if ($kodeDriver === 1049 || str_contains($pesan, 'Unknown database')) {
        return 'Database belum dibuat. Import file database/todolist_db.sql lewat phpMyAdmin.';
    }
    if ($kodeDriver === 2002 || str_contains($pesan, 'refused')) {
        return 'MySQL belum menyala. Nyalakan modul MySQL di XAMPP Control Panel.';
    }
    if ($kodeDriver === 1045 || str_contains($pesan, 'Access denied')) {
        return 'Username atau password MySQL salah. Periksa pengaturan di config.php.';
    }

    return 'Tidak dapat memproses data di database. Periksa pengaturan di config.php.';
}

/* ==========================================================================
 * DATA TUGAS (OPERASI SQL)
 * Setiap fungsi menerima koneksi PDO dan menjalankan satu query.
 * ========================================================================== */

/**
 * Baca seluruh tugas dari tabel MySQL ke dalam array.
 * Nilai dari database diubah ke tipe PHP yang tepat (int, string, bool).
 */
function muatTugas(PDO $pdo): array
{
    $baris = jalankanQuery($pdo, 'SELECT id, judul, selesai, dibuat FROM tugas ORDER BY id ASC')->fetchAll();

    return array_map(
        fn(array $b): array => [
            'id' => (int) $b['id'],
            'judul' => (string) $b['judul'],
            'selesai' => (bool) $b['selesai'],
            'dibuat' => (string) $b['dibuat'],
        ],
        $baris
    );
}

/**
 * Cek apakah tugas dengan id tertentu ada di database.
 */
function adaTugas(PDO $pdo, int $id): bool
{
    return jalankanQuery($pdo, 'SELECT 1 FROM tugas WHERE id = :id LIMIT 1', ['id' => $id])
        ->fetchColumn() !== false;
}

/**
 * Tambah satu tugas baru (status awal: belum selesai).
 */
function tambahTugas(PDO $pdo, string $judul): void
{
    jalankanQuery($pdo, 'INSERT INTO tugas (judul) VALUES (:judul)', ['judul' => $judul]);
}

/**
 * Balik status tugas: belum -> selesai, atau selesai -> belum.
 */
function ubahStatus(PDO $pdo, int $id): void
{
    jalankanQuery($pdo, 'UPDATE tugas SET selesai = 1 - selesai WHERE id = :id', ['id' => $id]);
}

/**
 * Ganti judul tugas dengan judul baru.
 */
function ubahJudul(PDO $pdo, int $id, string $judulBaru): void
{
    jalankanQuery($pdo, 'UPDATE tugas SET judul = :judul WHERE id = :id', [
        'judul' => $judulBaru,
        'id' => $id,
    ]);
}

/**
 * Hapus tugas dari database.
 */
function hapusTugas(PDO $pdo, int $id): void
{
    jalankanQuery($pdo, 'DELETE FROM tugas WHERE id = :id', ['id' => $id]);
}

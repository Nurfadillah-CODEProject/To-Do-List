<?php
/**
 * includes/logic.php
 * Validasi input, logika array (filter dan statistik), serta pemrosesan aksi POST.
 *
 * Struktur data: setiap baris tabel dibaca menjadi array asosiatif
 * ['id' => int, 'judul' => string, 'selesai' => bool, 'dibuat' => string]
 * lalu seluruh tugas dikumpulkan dalam satu array indeks ($daftar).
 */

declare(strict_types=1);

/* ==========================================================================
 * VALIDASI DAN LOGIKA ARRAY
 * ========================================================================== */

/**
 * Validasi judul. Mengembalikan pesan galat, atau null jika judul valid.
 */
function validasiJudul(string $judul): ?string
{
    if ($judul === '') {
        return 'Judul tugas tidak boleh kosong.';
    }
    if (mb_strlen($judul) > JUDUL_MAKS) {
        return 'Judul tugas maksimal ' . JUDUL_MAKS . ' karakter.';
    }

    return null;
}

/**
 * Ambil sebagian tugas dari array sesuai filter: semua | belum | selesai.
 */
function filterTugas(array $daftar, string $filter): array
{
    if ($filter === 'semua') {
        return $daftar;
    }

    $targetSelesai = ($filter === 'selesai');

    return array_values(array_filter(
        $daftar,
        fn(array $tugas): bool => (bool) $tugas['selesai'] === $targetSelesai
    ));
}

/**
 * Hitung total, jumlah selesai, dan jumlah belum selesai dari array.
 */
function hitungStatistik(array $daftar): array
{
    $total = count($daftar);
    $selesai = count(array_filter(
        $daftar,
        fn(array $tugas): bool => (bool) $tugas['selesai']
    ));

    return [
        'total' => $total,
        'selesai' => $selesai,
        'belum' => $total - $selesai,
    ];
}

/* ==========================================================================
 * PEMROSESAN AKSI (POST)
 * Pola Post/Redirect/Get: setelah POST diproses, browser dialihkan ke GET
 * sehingga refresh halaman tidak mengirim ulang form.
 * ========================================================================== */

function alihkanKeIndeks(string $filter): never
{
    $url = 'index.php';
    if ($filter !== 'semua') {
        $url .= '?filter=' . urlencode($filter);
    }

    header('Location: ' . $url);
    exit;
}

/**
 * Ambil judul dari form lalu validasi.
 * Jika tidak valid, atur pesan galat dan kembalikan null.
 */
function ambilJudulValid(): ?string
{
    $judul = inputTeks($_POST, 'judul');
    $galat = validasiJudul($judul);

    if ($galat !== null) {
        aturFlash('danger', $galat);
        return null;
    }

    return $judul;
}

/**
 * Pastikan tugas dengan id tertentu ada. Jika tidak, atur pesan galat.
 */
function pastikanTugasAda(PDO $pdo, int $id): bool
{
    if (adaTugas($pdo, $id)) {
        return true;
    }

    aturFlash('danger', 'Tugas tidak ditemukan.');

    return false;
}

/**
 * Baca aksi dari form (tambah | edit | ubah | hapus), jalankan, lalu alihkan.
 */
function prosesAksi(PDO $pdo): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $filter = normalisasiFilter(inputTeks($_POST, 'filter'));

    if (!tokenCsrfValid(inputTeks($_POST, 'token'))) {
        aturFlash('danger', 'Permintaan tidak valid. Silakan coba lagi.');
        alihkanKeIndeks($filter);
    }

    $aksi = inputTeks($_POST, 'aksi');
    $id = (int) inputTeks($_POST, 'id');

    switch ($aksi) {
        case 'tambah':
            $judul = ambilJudulValid();
            if ($judul !== null) {
                tambahTugas($pdo, $judul);
                aturFlash('success', 'Tugas baru berhasil ditambahkan.');
            }
            break;

        case 'edit':
            $judul = ambilJudulValid();
            if ($judul !== null && pastikanTugasAda($pdo, $id)) {
                ubahJudul($pdo, $id, $judul);
                aturFlash('success', 'Tugas berhasil diperbarui.');
            }
            break;

        case 'ubah':
            if (pastikanTugasAda($pdo, $id)) {
                ubahStatus($pdo, $id);
                aturFlash('success', 'Status tugas diperbarui.');
            }
            break;

        case 'hapus':
            if (pastikanTugasAda($pdo, $id)) {
                hapusTugas($pdo, $id);
                aturFlash('success', 'Tugas berhasil dihapus.');
            }
            break;

        default:
            aturFlash('danger', 'Aksi tidak dikenal.');
    }

    alihkanKeIndeks($filter);
}

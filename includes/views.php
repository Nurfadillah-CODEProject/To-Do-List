<?php
/**
 * includes/views.php
 * Fungsi tampilan: mencetak HTML dan komponen Bootstrap.
 * Semua output dari pengguna di-escape dengan e().
 */

declare(strict_types=1);

/* ==========================================================================
 * FUNGSI TAMPILAN (OUTPUT HTML + KOMPONEN BOOTSTRAP)
 * ========================================================================== */

/**
 * Input tersembunyi yang dipakai ulang oleh semua form (tambah, edit, ubah, hapus).
 */
function fieldTersembunyi(string $aksi, string $token, string $filter, ?int $id = null): string
{
    $html = '<input type="hidden" name="aksi" value="' . e($aksi) . '">'
        . '<input type="hidden" name="token" value="' . e($token) . '">'
        . '<input type="hidden" name="filter" value="' . e($filter) . '">';

    if ($id !== null) {
        $html .= '<input type="hidden" name="id" value="' . $id . '">';
    }

    return $html;
}

/**
 * Dua kotak statistik (Done / On Progress).
 * Kotak juga berfungsi sebagai filter (GET): klik untuk menyaring,
 * klik lagi pada kotak yang aktif untuk menampilkan semua tugas.
 */
function tampilkanStatistik(string $aktif, array $statistik): void
{
    $kotak = [
        'selesai' => ['Tugas Selesai', $statistik['selesai']],
        'belum' => ['Tugas Belum Selesai', $statistik['belum']],
    ];

    echo '<div class="row g-3 mb-3">';
    foreach ($kotak as $kunci => [$label, $jumlah]) {
        $aktifIni = ($kunci === $aktif);
        printf(
            '<div class="col-12 col-sm-6"><a href="%s" class="kotak-statistik %s"'
            . ' title="Klik untuk menyaring tugas"><span class="label-statistik">%s</span>'
            . '<span class="angka-statistik">%d</span></a></div>',
            e($aktifIni ? 'index.php' : 'index.php?filter=' . $kunci),
            $aktifIni ? 'aktif' : '',
            e($label),
            $jumlah
        );
    }
    echo '</div>';
}

/**
 * Tampilan saat tidak ada tugas yang bisa ditampilkan.
 */
function tampilkanKosong(string $filter): void
{
    $pesan = [
        'semua' => 'Belum ada tugas. Tambahkan tugas pertamamu di atas!',
        'belum' => 'Tidak ada tugas yang sedang dikerjakan. Kerja bagus!',
        'selesai' => 'Belum ada tugas yang selesai.',
    ];

    echo '<div class="text-center text-muted py-5">'
        . '<i class="bi bi-inbox fs-1 d-block mb-2"></i>'
        . e($pesan[$filter]) . '</div>';
}

/**
 * Tampilkan satu tugas: judul di kiri, ikon hapus / edit / selesai di kanan.
 */
function tampilkanItem(array $tugas, string $filter, string $token, int $urutan = 0): void
{
    $id = (int) $tugas['id'];
    $judul = (string) $tugas['judul'];
    $selesai = (bool) $tugas['selesai'];
    $jeda = min($urutan * 60, 400); // jeda animasi bertahap (ms), maksimal 400 ms
    ?>
    <li class="item-tugas animate__animated animate__fadeInUp <?= $selesai ? 'selesai' : '' ?>"
        style="animation-delay: <?= $jeda ?>ms">
        <span class="judul-tugas"><?= e($judul) ?></span>

        <div class="aksi-tugas">
            <!-- Hapus (POST): konfirmasi dikelola oleh script di index.php -->
            <form method="post" action="index.php" class="form-hapus" data-judul="<?= e($judul) ?>">
                <?= fieldTersembunyi('hapus', $token, $filter, $id) ?>
                <button type="submit" class="btn-ikon ikon-hapus" title="Hapus" aria-label="Hapus tugas">
                    <i class="bi bi-trash3"></i>
                </button>
            </form>

            <!-- Edit: membuka modal Bootstrap, data diisi lewat atribut data-* -->
            <button type="button" class="btn-ikon ikon-edit" title="Edit" aria-label="Edit tugas"
                    data-bs-toggle="modal" data-bs-target="#modalEdit"
                    data-id="<?= $id ?>" data-judul="<?= e($judul) ?>">
                <i class="bi bi-pencil-square"></i>
            </button>

            <!-- Tandai selesai / belum (POST) -->
            <form method="post" action="index.php">
                <?= fieldTersembunyi('ubah', $token, $filter, $id) ?>
                <button type="submit" class="btn-ikon ikon-selesai"
                        title="<?= $selesai ? 'Tandai belum selesai' : 'Tandai selesai' ?>"
                        aria-label="<?= $selesai ? 'Tandai belum selesai' : 'Tandai selesai' ?>">
                    <i class="bi <?= $selesai ? 'bi-check-circle-fill' : 'bi-check-circle' ?>"></i>
                </button>
            </form>
        </div>
    </li>
    <?php
}

/**
 * Tampilkan seluruh daftar tugas (atau pesan kosong jika tidak ada).
 */
function tampilkanDaftar(array $daftar, string $filter, string $token): void
{
    if (count($daftar) === 0) {
        tampilkanKosong($filter);
        return;
    }

    echo '<ul class="list-unstyled m-0">';
    foreach (array_values($daftar) as $urutan => $tugas) {
        tampilkanItem($tugas, $filter, $token, $urutan);
    }
    echo '</ul>';
}

/**
 * Modal Bootstrap untuk mengedit judul tugas (satu modal dipakai semua tugas).
 */
function tampilkanModalEdit(string $token, string $filter): void
{
    ?>
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="judulModalEdit" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="index.php" class="modal-content border-0 shadow" autocomplete="off">
                <?= fieldTersembunyi('edit', $token, $filter, 0) ?>
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-bold" id="judulModalEdit">Edit tugas</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-0">
                    <input type="text" name="judul" id="editJudul" class="form-control form-control-lg" autocomplete="off"
                           maxlength="<?= JUDUL_MAKS ?>" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-simpan">Save</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Tampilkan halaman galat yang rapi, lalu hentikan program.
 */
function tampilkanHalamanGalat(PDOException $galat): never
{
    error_log('[todolist] ' . $galat->getMessage());
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Database belum siap</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5" style="max-width: 640px;">
            <div class="alert alert-danger shadow-sm" role="alert">
                <h1 class="h4 alert-heading">Database belum siap</h1>
                <p class="mb-0"><?= e(pesanGalatDatabase($galat)) ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

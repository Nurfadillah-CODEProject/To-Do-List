<?php
/**
 * index.php — halaman utama To-Do List.
 *
 * Alur program (pemrograman terstruktur):
 *   1. Muat fungsi dari folder includes/ dan mulai sesi
 *   2. Hubungkan ke database MySQL (PDO)
 *   3. Proses aksi POST (tambah / edit / ubah status / hapus) lalu redirect
 *   4. Baca data dari tabel MySQL ke dalam array
 *   5. Siapkan data tampilan, lalu cetak HTML
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/logic.php';
require_once __DIR__ . '/includes/views.php';

mulaiSesi();

try {
    $pdo = koneksiDatabase();
    prosesAksi($pdo);
    $filter = ambilFilterAktif();                                   // GET: ?filter=
    $semuaTugas = muatTugas($pdo);                                  // array dari MySQL
} catch (PDOException $galat) {
    tampilkanHalamanGalat($galat);
}

$statistik = hitungStatistik($semuaTugas);
$tugasTampil = array_reverse(filterTugas($semuaTugas, $filter));    // terbaru di atas
$token = ambilTokenCsrf();
$flash = ambilFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <title>My To Do List</title>

    <!-- Font, Bootstrap 5, Bootstrap Icons, dan Animate.css (CDN) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<main class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">

            <!-- Container utama: seluruh isi aplikasi berada di dalam kartu ini -->
            <div class="card kartu-todo">
                <div class="card-body p-4 p-md-5">

                    <!-- Kepala -->
                    <header class="kepala mb-4 animate__animated animate__fadeInDown">
                        <span class="logo" aria-hidden="true"><i class="bi bi-check2-all"></i></span>
                        <div>
                            <h1 class="judul-halaman">My To Do List</h1>
                            <p class="subjudul">Catat tugasmu, selesaikan satu per satu.</p>
                        </div>
                    </header>

                    <!-- Notifikasi (diganti toast SweetAlert2 oleh assets/js/app.js jika tersedia) -->
                    <?php if ($flash !== null) : ?>
                        <div id="notifikasi" class="alert alert-<?= e($flash['tipe']) ?> alert-dismissible fade show" role="alert"
                             data-tipe="<?= e($flash['tipe']) ?>" data-pesan="<?= e($flash['pesan']) ?>">
                            <?= e($flash['pesan']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form tambah tugas (autocomplete dimatikan: tanpa riwayat/saran saat mengetik) -->
                    <form method="post" action="index.php" class="form-tambah mb-3" autocomplete="off">
                        <?= fieldTersembunyi('tambah', $token, $filter) ?>
                        <input type="text" name="judul" placeholder="Masukkan to do list"
                               aria-label="Judul tugas baru" maxlength="<?= JUDUL_MAKS ?>"
                               autocomplete="off" required autofocus>
                        <button type="submit" class="btn-simpan">Save</button>
                    </form>

                    <!-- Statistik (sekaligus filter via GET) dan daftar tugas -->
                    <?php tampilkanStatistik($filter, $statistik); ?>
                    <?php tampilkanDaftar($tugasTampil, $filter, $token); ?>

                </div>
            </div>

            <footer class="text-center small mt-4" style="color: var(--tinta-lembut);">
                Dibuat oleh Alfian Nurfadillah &middot; NPM 40621100095
            </footer>
        </div>
    </div>
</main>

<?php tampilkanModalEdit($token, $filter); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>

/**
 * assets/js/app.js
 * Perilaku halaman: toast notifikasi, konfirmasi hapus, dan isi modal edit.
 * File ini murni JavaScript; data dari PHP dibaca lewat atribut data-* di HTML.
 */

// Jika CDN SweetAlert2 gagal dimuat, halaman tetap jalan dengan alert Bootstrap dan confirm() bawaan.
var adaSwal = typeof Swal !== 'undefined';

// Notifikasi: tampilkan sebagai toast di pojok kanan atas.
var kotakNotifikasi = document.getElementById('notifikasi');

if (kotakNotifikasi && adaSwal) {
    var tipe = kotakNotifikasi.getAttribute('data-tipe');
    var pesan = kotakNotifikasi.getAttribute('data-pesan');

    kotakNotifikasi.remove();
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: tipe === 'success' ? 'success' : 'error',
        title: pesan,
        showConfirmButton: false,
        timer: 2800,
        timerProgressBar: true
    });
}

// Konfirmasi hapus: form dikirim hanya jika pengguna menyetujui.
document.querySelectorAll('.form-hapus').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!adaSwal) {
            if (confirm('Hapus tugas ini?')) {
                form.submit();
            }
            return;
        }

        Swal.fire({
            title: 'Hapus tugas ini?',
            text: form.getAttribute('data-judul'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#b9374f',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function (hasil) {
            if (hasil.isConfirmed) {
                form.submit();
            }
        });
    });
});

// Isi form modal dengan data tugas yang diklik, lalu fokuskan kolom judul.
var modalEdit = document.getElementById('modalEdit');

modalEdit.addEventListener('show.bs.modal', function (event) {
    var tombol = event.relatedTarget;
    modalEdit.querySelector('input[name="id"]').value = tombol.getAttribute('data-id');
    document.getElementById('editJudul').value = tombol.getAttribute('data-judul');
});

modalEdit.addEventListener('shown.bs.modal', function () {
    document.getElementById('editJudul').focus();
});

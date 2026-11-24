# Akses sekolah melalui SISCOM

Portal memakai db_portalsiscom dan SISCOM memakai sis_com pada instance MySQL yang sama. Pasang migrasi portal dahulu, deploy SISCOM, kemudian jalankan dari direktori SISCOM:

    bash scripts/akses-pendaftaran-sekolah.sh
    php artisan sekolah:cek-akses

Skrip bash memeriksa akun database aktif SISCOM dan memberikan izin baca direktori sekolah, tulis pada kolom aktivasi dan identitas sekolah, serta insert tiket masuk. Sesi dan kata sandi dikelola masing-masing aplikasi. Gunakan HTTPS serta cookie sesi khusus host untuk kedua domain.

## Pengguna klien lama

1. Super Admin membuka **Pengguna → Hubungkan sekolah** pada akun penanggung jawab yang berhak menjadi Kepala Sekolah.
2. Verifikasi NPSN dan masukkan kode portal jika sekolah sudah aktif. Untuk sekolah tanpa kode, isi nama sekolah, akhir masa sewa, dan modul untuk menerbitkan kode SIS. Untuk sekolah yang tercantum dalam antrean, periksa pemilik akun pendaftar dan gunakan konfirmasi di bawah.
3. Klien memilih **Masuk dengan SISCOM** di portal. Setelah login dengan email dan kata sandi SISCOM, pilih sekolah. Nama sekolah, NPSN dan kode tampil sebelum masuk.

## Sekolah baru

Sekolah membuka https://sityoyitsolutions.com/portal#daftar, mengisi NPSN, data sekolah dan akun penanggung jawab, lalu menyimpan kode yang muncul. Sekolah menunggu verifikasi.

Super Admin membuka **Pendaftaran Sekolah → Menunggu verifikasi**, mencocokkan identitas dan kontak pendaftar dengan sekolah, lalu menetapkan modul dan akhir masa sewa. Pilih klien kontrak jika sudah ada. Untuk pendaftar baru, pilih **Sekolah baru · gunakan akun portal pendaftar**. Setelah dikonfirmasi, sampaikan hasil kepada penanggung jawab. Pendaftar masuk dengan kode sekolah dan akun portal. Pada pendaftaran lama, Super Admin juga dapat melengkapi NPSN saat verifikasi.

NPSN dan kode sekolah disimpan terpisah. Kode lama tetap berlaku. Data ujian menggunakan db_ujiansis.

# Konfirmasi sekolah melalui SISCOM

Sekolah mengajukan pendaftaran di https://sityoyitsolutions.com/portal#daftar dan menyimpan kode sekolah yang muncul. Pemilik layanan login di https://siscom.sityoyitsolutions.com/login dengan akun web SISCOM yang berperan super_admin. Hak tersebut mengikuti pengelolaan Client. Akun terminal VPS dan akun MySQL memiliki fungsi terpisah.

## Pemasangan di VPS

SISCOM memakai database sis_com dan portal memakai db_portalsiscom pada instance MySQL yang sama. Aplikasi CBT memakai db_ujiansis. Setelah PR digabung ke master, jalankan di direktori repo SISCOM:

```bash
bash deploy.sh
bash scripts/akses-pendaftaran-sekolah.sh
php artisan sekolah:cek-akses
```

Skrip akses memeriksa hak akun MySQL aktif milik SISCOM. Hanya jika izin kurang, skrip memakai sudo mysql untuk memberikan SELECT dan UPDATE atas kolom active, until_date dan modules pada tabel db_portalsiscom.schools. Jika MySQL dikelola oleh DBA terpisah, jalankan pernyataan GRANT yang ditampilkan melalui akun administratif MySQL pada server database yang sama. Perintah cek-akses menanyakan email login SISCOM dan menampilkan apakah akun itu boleh menyetujui pendaftaran. Pengaturan ini tidak membuat akun atau mengganti peran pengguna.

## Pemakaian

1. Buka menu **Pendaftaran Sekolah** pada sidebar admin SISCOM.
2. Buka sekolah pada daftar **Menunggu verifikasi**. Cocokkan nama dan kontak dengan klien serta pesanannya. Jika klien belum terdaftar, gunakan **Tambah Client** dahulu.
3. Pilih klien SISCOM, tentukan akhir masa sewa dan centang modul sesuai kontrak.
4. Klik **Konfirmasi dan aktifkan sekolah**. Daftar riwayat mencatat operator, klien, kode sekolah, tanggal, dan modul. Sekolah kini dapat masuk ke portal dengan akun yang dibuat saat pendaftaran.
5. Sampaikan konfirmasi ke penanggung jawab melalui kontak yang terdaftar. Versi ini tidak mengirim pesan otomatis.

Riwayat hanya memuat persetujuan melalui SISCOM. Sekolah yang sudah diaktifkan atau disuspensi dengan Artisan tidak menjadi pendaftaran baru. Seluruh perubahan saat persetujuan dilakukan dalam satu transaksi MySQL di kedua schema menggunakan tabel InnoDB. CBT tidak diperlukan dalam alur ini.
